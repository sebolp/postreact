<?php
/**
 * Standalone, opt-in importer: steve/postreactions legacy data -> sebo/postreact
 * CLI front-end. See import-steve-reactions-web.php for the browser version -
 * both share the same logic, in steve-import-core.php.
 *
 * IMPORTANT: this script is NOT part of the PostReaction extension and is
 * never bundled, loaded or executed by it. It is a one-off, manually-run
 * CLI utility for admins who are migrating a forum away from the old
 * (now defunct) steve/postreactions extension and want to keep the
 * historical reactions. Copy it (together with steve-import-core.php)
 * anywhere outside ext/sebo/postreact, e.g. next to your phpBB
 * common.php, and run it once by hand.
 *
 * Usage (from the phpBB root directory):
 *   php import-steve-reactions.php [--old-table=phpbb_reactions] [--dry-run]
 *
 *   --old-table=NAME  Skip auto-detection and use this legacy table name.
 *   --dry-run         Report what would happen, write nothing to the DB.
 *
 * What it does - see the docblock at the top of steve-import-core.php's
 * steve_import_run() for the full explanation (table auto-detection,
 * dedup rule, icon mapping, safety checks). Safe to re-run: already
 * imported rows and already-created icons are detected and skipped.
 *
 * @copyright (c) 2026, sebo, https://www.fiatpandaclub.org
 * @license   GNU General Public License, version 2 (GPL-2.0)
 */

define('IN_PHPBB', true);

if (php_sapi_name() !== 'cli')
{
	die("This script must be run from the command line (php import-steve-reactions.php).\n");
}

$phpbb_root_path = __DIR__ . '/';
$phpEx = 'php';

if (!is_file($phpbb_root_path . 'common.' . $phpEx))
{
	die("common.php not found next to this script. Copy import-steve-reactions.php into your phpBB root directory (where common.php lives) and run it from there.\n");
}

require($phpbb_root_path . 'common.' . $phpEx);

if (!is_file(__DIR__ . '/steve-import-core.php'))
{
	die("steve-import-core.php not found next to this script. Copy both files together.\n");
}
require(__DIR__ . '/steve-import-core.php');

/** @var \phpbb\db\driver\driver_interface $db */
/** @var \phpbb\cache\driver\driver_interface $cache */

// Best-effort only, mainly relevant if a custom php.ini restricts the
// CLI SAPI too - by default CLI already has no execution time limit.
@set_time_limit(0);
@ini_set('memory_limit', '512M');

$options = getopt('', ['old-table::', 'dry-run']);
$dry_run = isset($options['dry-run']);
$old_table_override = isset($options['old-table']) ? trim($options['old-table']) : null;

echo "=== steve/postreactions -> sebo/postreact importer ===\n";
echo $dry_run ? "Mode: DRY RUN (no changes will be written)\n\n" : "Mode: LIVE\n\n";

try
{
	$report = steve_import_run($db, $cache, $table_prefix, $old_table_override, $dry_run);
}
catch (steve_import_error $e)
{
	die($e->getMessage() . "\n");
}

echo "Legacy table: {$report['old_table']}\n";

echo "\nResolving icon mapping...\n";
foreach ($report['icon_log'] as $line)
{
	echo "  {$line}\n";
}

echo "\n{$report['total_legacy_rows']} legacy rows found, {$report['unique_pairs']} unique (user, post) pairs";
echo $report['deduped_count'] > 0 ? " ({$report['deduped_count']} older duplicate reactions dropped, keeping the latest each time).\n" : ".\n";

echo "\nPreparing rows to insert...\n";
if ($report['skipped_existing'] > 0)
{
	echo "  {$report['skipped_existing']} pairs skipped: already have a reaction in sebo/postreact (not overwritten).\n";
}
foreach ($report['skipped_unmapped'] as $filename => $count)
{
	echo "  WARNING: {$count} reaction(s) using unmapped file '{$filename}' skipped (unknown filename, not part of the legacy set this script knows about).\n";
}

echo "\n{$report['inserted_count']} reaction(s) ready to import.\n";

if ($dry_run)
{
	echo "\nDry run - nothing written. Re-run without --dry-run to apply.\n";
	exit(0);
}

if ($report['inserted_count'] === 0)
{
	echo "\nNothing to insert.\n";
	exit(0);
}

echo "\nInserted {$report['inserted_count']} reaction(s).\n";
echo "Reaction-count cache purged.\n";
echo "\nDone. Any placeholder icons created above are disabled - assign them an image and enable them from ACP whenever you're ready.\n";
