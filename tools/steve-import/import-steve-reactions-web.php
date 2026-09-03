<?php
/**
 * Standalone, opt-in importer: steve/postreactions legacy data -> sebo/postreact
 * Browser front-end. See import-steve-reactions.php for the CLI version -
 * both share the same logic, in steve-import-core.php.
 *
 * IMPORTANT: this script is NOT part of the PostReaction extension and is
 * never bundled, loaded or executed by it. Copy it (together with
 * steve-import-core.php) into your phpBB root, next to common.php, visit
 * it in a browser while logged in as an administrator, and DELETE IT
 * (or at least move it out of the webroot) once you're done. Leaving a
 * database-writing script reachable by URL indefinitely is not a good
 * idea, however well it's protected.
 *
 * Protection in place:
 *  - Requires a real phpBB admin session (checked via $auth, the same
 *    permission system the ACP uses) - not a hand-rolled password.
 *  - CSRF-protected: a random, single-use token is generated per admin
 *    and stored server-side (in phpBB's cache, keyed to the admin's
 *    user_id) when the page loads, and must come back unchanged on
 *    every submit. This is deliberately self-contained rather than
 *    reusing phpBB's own add_form_key()/check_form_key() - that
 *    implementation has changed across 3.3.x point releases, and this
 *    page renders plain HTML rather than going through the template
 *    engine those functions assume.
 *  - Always previews first (dry run) - the actual write only happens
 *    after an explicit second confirmation submit, and the token is
 *    invalidated immediately after a real write so the form can't be
 *    resubmitted twice.
 *
 * @copyright (c) 2026, sebo, https://www.fiatpandaclub.org
 * @license   GNU General Public License, version 2 (GPL-2.0)
 */

define('IN_PHPBB', true);

$phpbb_root_path = __DIR__ . '/';
$phpEx = 'php';

if (!is_file($phpbb_root_path . 'common.' . $phpEx))
{
	die('common.php not found next to this script. Copy import-steve-reactions-web.php into your phpBB root directory (where common.php lives).');
}

require($phpbb_root_path . 'common.' . $phpEx);

if (!is_file(__DIR__ . '/steve-import-core.php'))
{
	die('steve-import-core.php not found next to this script. Copy both files together.');
}
require(__DIR__ . '/steve-import-core.php');

/** @var \phpbb\db\driver\driver_interface $db */
/** @var \phpbb\cache\driver\driver_interface $cache */
/** @var \phpbb\request\request $request */
/** @var \phpbb\user $user */
/** @var \phpbb\auth\auth $auth */

// Best-effort only: some hosts lock these down and silently ignore the
// change, which is fine - it just means a very large import might need
// the CLI version instead (see the instructions).
@set_time_limit(0);
@ini_set('memory_limit', '512M');

// -----------------------------------------------------------------------
// Auth: require a real, logged-in phpBB administrator
// -----------------------------------------------------------------------

$user->session_begin();
$auth->acl($user->data);
$user->setup();

function steve_import_render_page($title, $body_html)
{
	header('Content-Type: text/html; charset=utf-8');
	echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">';
	echo '<title>' . htmlspecialchars($title) . '</title>';
	echo '<style>
		body { font-family: -apple-system, Segoe UI, Arial, sans-serif; max-width: 780px; margin: 2em auto; padding: 0 1em; color: #222; line-height: 1.5; }
		h1 { font-size: 1.4em; }
		.box { border: 1px solid #ccc; border-radius: 6px; padding: 1em 1.4em; margin: 1em 0; background: #fafafa; }
		.error { border-color: #d9534f; background: #fdecea; color: #a12622; }
		.ok { border-color: #4caf50; background: #eef8ee; }
		label { display: block; margin: 0.6em 0 0.2em; font-weight: 600; }
		input[type=text] { width: 100%; padding: 0.4em; box-sizing: border-box; }
		button { margin-top: 1em; padding: 0.6em 1.2em; font-size: 1em; cursor: pointer; }
		code { background: #eee; padding: 0.1em 0.3em; border-radius: 3px; }
		ul { margin: 0.3em 0; }
	</style></head><body>';
	echo '<h1>' . htmlspecialchars($title) . '</h1>';
	echo $body_html;
	echo '</body></html>';
}

if (!$auth->acl_get('a_'))
{
	steve_import_render_page('steve/postreactions importer', '
		<div class="box error">
			<p>You need to be logged in as a phpBB administrator to use this page.</p>
			<p><a href="' . htmlspecialchars(append_sid("{$phpbb_root_path}ucp.{$phpEx}", 'mode=login')) . '">Log in</a>, then reload this page.</p>
		</div>');
	exit;
}

// -----------------------------------------------------------------------
// Self-contained CSRF token: random, single-use, stored server-side in
// the cache keyed to this admin's user_id.
// -----------------------------------------------------------------------

$csrf_cache_key = '_steve_import_csrf_' . (int) $user->data['user_id'];

function steve_import_new_csrf_token($cache, $csrf_cache_key)
{
	$token = bin2hex(random_bytes(32));
	$cache->put($csrf_cache_key, $token, 1800);
	return $token;
}

function steve_import_check_csrf_token($cache, $csrf_cache_key, $submitted)
{
	$expected = $cache->get($csrf_cache_key);
	return $expected !== false && $submitted !== '' && hash_equals($expected, (string) $submitted);
}

// -----------------------------------------------------------------------
// Handle the form
// -----------------------------------------------------------------------

$old_table_override = trim($request->variable('old_table', ''));
$submitted_token = $request->variable('csrf_token', '');
$wants_preview = $request->is_set_post('preview');
$wants_confirm = $request->is_set_post('confirm');

$body = '';
$report = null;

if (($wants_preview || $wants_confirm) && !steve_import_check_csrf_token($cache, $csrf_cache_key, $submitted_token))
{
	$body .= '<div class="box error"><p>This form has expired or was not submitted from this page. Reload the page and try again.</p></div>';
	$wants_preview = $wants_confirm = false;
}

if ($wants_preview || $wants_confirm)
{
	try
	{
		$report = steve_import_run($db, $cache, $table_prefix, $old_table_override ?: null, !$wants_confirm);

		$body .= '<div class="box ' . ($wants_confirm ? 'ok' : '') . '">';
		$body .= '<p><strong>' . ($wants_confirm ? 'Import complete.' : 'Preview (nothing written yet)') . '</strong></p>';
		$body .= '<p>Legacy table: <code>' . htmlspecialchars($report['old_table']) . '</code></p>';

		if (!empty($report['icon_log']))
		{
			$body .= '<p>Icon mapping:</p><ul>';
			foreach ($report['icon_log'] as $line)
			{
				$body .= '<li>' . htmlspecialchars($line) . '</li>';
			}
			$body .= '</ul>';
		}

		$body .= '<p>' . (int) $report['total_legacy_rows'] . ' legacy row(s) found, ' . (int) $report['unique_pairs'] . ' unique (user, post) pair(s)';
		if ($report['deduped_count'] > 0)
		{
			$body .= ' (' . (int) $report['deduped_count'] . ' older duplicate reaction(s) dropped, keeping the latest each time)';
		}
		$body .= '.</p>';

		if ($report['skipped_existing'] > 0)
		{
			$body .= '<p>' . (int) $report['skipped_existing'] . ' pair(s) skipped: already have a reaction in sebo/postreact (not overwritten).</p>';
		}

		if (!empty($report['skipped_unmapped']))
		{
			$body .= '<p>Skipped, unmapped filenames:</p><ul>';
			foreach ($report['skipped_unmapped'] as $filename => $count)
			{
				$body .= '<li>' . (int) $count . '&times; <code>' . htmlspecialchars($filename) . '</code></li>';
			}
			$body .= '</ul>';
		}

		$body .= '<p><strong>' . (int) $report['inserted_count'] . ' reaction(s) ' . ($wants_confirm ? 'inserted.' : 'ready to import.') . '</strong></p>';

		if ($wants_confirm)
		{
			$body .= '<p>Reaction-count cache purged. Any placeholder icons created above are disabled - assign them an image and enable them from ACP whenever you\'re ready.</p>';
		}

		$body .= '</div>';
	}
	catch (steve_import_error $e)
	{
		$body .= '<div class="box error"><p>' . htmlspecialchars($e->getMessage()) . '</p></div>';
	}
}

if ($wants_confirm && $report !== null)
{
	// The write just happened (or failed cleanly) - burn the token so
	// this exact form can't be submitted a second time, and don't show
	// any further form on this page load.
	$cache->destroy($csrf_cache_key);
}
else
{
	// Either the very first page load, or after a preview: show a form
	// with a fresh token. If we just showed a successful preview with
	// reactions ready, this form is the "confirm and write" step;
	// otherwise it's the initial "preview" step.
	$token = steve_import_new_csrf_token($cache, $csrf_cache_key);

	if ($wants_preview && $report !== null && $report['inserted_count'] > 0)
	{
		$body .= '<div class="box">
			<form method="post">
				<input type="hidden" name="old_table" value="' . htmlspecialchars($old_table_override) . '">
				<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">
				<button type="submit" name="confirm" value="1">Confirm - write ' . (int) $report['inserted_count'] . ' reaction(s) to the database</button>
			</form>
		</div>';
	}
	else
	{
		$body .= '<div class="box">
			<form method="post">
				<label for="old_table">Legacy table name (optional)</label>
				<input type="text" id="old_table" name="old_table" value="' . htmlspecialchars($old_table_override) . '" placeholder="leave empty to auto-detect">
				<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">
				<button type="submit" name="preview" value="1">Preview (dry run)</button>
			</form>
		</div>';
	}
}

steve_import_render_page('steve/postreactions -> sebo/postreact importer', $body);
