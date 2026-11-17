<?php
/**
 * Shared core logic for the steve/reactions -> sebo/postreact importer.
 *
 * NOT part of the PostReaction extension, and not meant to be requested
 * directly - it is require()'d by import-steve-reactions.php (CLI) and
 * import-steve-reactions-web.php (browser), so the two front-ends can
 * never drift out of sync with each other.
 *
 * Background, confirmed against the real steve/reactions 0.9.8 source
 * (earlier versions of this importer were built from a third-party port
 * of unknown accuracy - this note records what changed once the real
 * extension was available to check against):
 *  - The real extension ships its reaction images as .svg, not .png.
 *    Both extensions are still matched here, since data coming from the
 *    third-party port (which did use .png) may exist too.
 *  - steve/reactions only ever allows ONE reaction per user per post
 *    (reacting again updates the existing row) - same as sebo/postreact.
 *    The "keep only the latest per user/post pair" logic below is kept
 *    as a harmless safety net rather than because it's expected to
 *    actually find anything to deduplicate.
 *  - The default install seeds exactly 10 reaction types; several other
 *    reaction images ship in the extension without being part of that
 *    default set, and the type list itself is a real, admin-editable
 *    table (steve_reaction_types) - meaning a given forum may have
 *    additional, fully custom reaction types an admin added by hand.
 *    Any filename this importer doesn't already recognise is therefore
 *    treated as a legitimate custom type, not an error: it gets its own
 *    new, disabled placeholder icon (image to be assigned in ACP),
 *    exactly like the handful of known-but-unmatched default types.
 *  - steve/reactions can apparently also target private messages and
 *    chat messages (pm_id / chat_msg_id columns), not just forum posts.
 *    This importer intentionally only ever deals with post reactions -
 *    rows without a post_id are left alone.
 *  - Every real reaction row also carries its own denormalised
 *    reaction_type_title, copied at the moment the reaction was made -
 *    this is the most trustworthy source for a custom type's label
 *    (more so than a live types table, which could have since renamed
 *    or deleted that type) and is checked first, where the column
 *    exists.
 *
 * @copyright (c) 2026, sebo, https://www.fiatpandaclub.org
 * @license   GNU General Public License, version 2 (GPL-2.0)
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * Thrown for anything that should stop the import and be shown to the
 * admin as a plain, non-technical message (both front-ends catch this).
 */
class steve_import_error extends \RuntimeException
{
}

/**
 * Runs the full import (or a dry run of it) and returns a report array.
 *
 * @param \phpbb\db\driver\driver_interface $db
 * @param \phpbb\cache\driver\driver_interface $cache
 * @param string $table_prefix
 * @param string|null $old_table_override
 * @param bool $dry_run
 * @return array Report - see the keys assigned below.
 * @throws steve_import_error
 */
function steve_import_run($db, $cache, $table_prefix, $old_table_override, $dry_run)
{
	$icon_table = $table_prefix . 'sebo_postreact_icon';
	$reaction_table = $table_prefix . 'sebo_postreact_table';

	$report = [
		'dry_run'              => (bool) $dry_run,
		'old_table'            => null,
		'types_table'          => null,
		'icon_log'             => [],
		'total_legacy_rows'    => 0,
		'unique_pairs'         => 0,
		'deduped_count'        => 0,
		'skipped_existing'     => 0,
		'skipped_invalid'      => 0,
		'custom_icons_created' => [],
		'inserted_count'       => 0,
	];

	// ---------------------------------------------------------------
	// Step 1 - locate the legacy table(s)
	// ---------------------------------------------------------------

	$report['old_table'] = steve_import_find_legacy_table($db, $old_table_override);

	// Does this specific legacy table have the per-row denormalised
	// title? Real steve/reactions data always does; data coming from
	// the third-party port it was first cross-checked against does not
	// - so this is checked rather than assumed.
	$has_row_title = steve_import_table_has_column($db, $report['old_table'], 'reaction_type_title');

	// Optional companion table listing the actual reaction types
	// (steve/reactions calls it steve_reaction_types) - if we can find
	// it, we get the real, admin-chosen title for any custom reaction
	// type instead of having to guess one from its filename.
	$report['types_table'] = steve_import_find_types_table($db);
	$type_titles = $report['types_table']
		? steve_import_load_type_titles($db, $report['types_table'])
		: [];

	// ---------------------------------------------------------------
	// Step 2 - resolve the icon mapping for the reaction types we
	// already know about
	// ---------------------------------------------------------------

	$icon_map = [];

	// Filenames with an exact or approved equivalent already in
	// sebo/postreact's own default icon set - matched by the default
	// image filename, so it still works even if the icon was moved or
	// renamed by the admin.
	$existing_lookups = [
		'1f44d.svg' => 'like.png',     '1f44d.png' => 'like.png',
		'1f60d.svg' => 'love.png',     '1f60d.png' => 'love.png',
		'1f602.svg' => 'laugh.png',    '1f602.png' => 'laugh.png',
		'1f62d.svg' => 'cry.png',      '1f62d.png' => 'cry.png',
		'1f621.svg' => 'angry.png',    '1f621.png' => 'angry.png',
		'1f641.svg' => 'sad.png',      '1f641.png' => 'sad.png',
		'1f62f.svg' => 'surprise.png', '1f62f.png' => 'surprise.png',
		// 'OMG.png' isn't part of real steve/reactions - it only shows
		// up in data coming from a particular third-party port of it.
		// Mapped here too since it costs nothing and such data might
		// still be encountered.
		'OMG.png'   => 'surprise.png',
	];

	foreach ($existing_lookups as $legacy_filename => $icon_filename)
	{
		$icon_id = steve_import_find_existing_icon_id($db, $icon_table, $icon_filename);

		if ($icon_id === null)
		{
			$report['icon_log'][] = "WARNING: no existing icon found for '{$icon_filename}' - reactions using '{$legacy_filename}' will be skipped.";
			continue;
		}

		$icon_map[$legacy_filename] = $icon_id;
	}

	// Default steve/reactions types with no equivalent in sebo/postreact
	// at all - each gets its own new, disabled placeholder icon (real
	// titles confirmed from steve/reactions' own language file).
	$placeholder_specs = [
		'1f44e.svg' => ['alt' => 'Dislike', 'emoji' => '&#128078;'],
		'1f44e.png' => ['alt' => 'Dislike', 'emoji' => '&#128078;'],
		'1f642.svg' => ['alt' => 'Happy',   'emoji' => '&#128578;'],
		'1f642.png' => ['alt' => 'Happy',   'emoji' => '&#128578;'],
		'1f611.svg' => ['alt' => 'Neutral', 'emoji' => '&#128529;'],
		'1f611.png' => ['alt' => 'Neutral', 'emoji' => '&#128529;'],
	];

	foreach ($placeholder_specs as $legacy_filename => $spec)
	{
		[$icon_id, $log_line] = steve_import_find_or_create_placeholder_icon($db, $icon_table, $spec['alt'], $spec['emoji'], $dry_run);
		$icon_map[$legacy_filename] = $icon_id;
		if ($log_line !== null)
		{
			$report['icon_log'][] = $log_line;
		}
	}

	// ---------------------------------------------------------------
	// Step 3 - read the legacy data and deduplicate (keep the latest
	// per user/post pair - see the file-level note on why this is a
	// safety net rather than an expected necessity). Rows with no
	// post_id are private-message/chat reactions, if this install ever
	// has any - intentionally left alone.
	// ---------------------------------------------------------------

	$sql = 'SELECT reaction_user_id, post_id, topic_id, reaction_file_name, reaction_time'
		. ($has_row_title ? ', reaction_type_title' : '') . '
			FROM ' . $report['old_table'] . '
			WHERE post_id > 0
			ORDER BY reaction_time ASC';
	$result = $db->sql_query($sql);

	$latest_per_pair = []; // "user_id-post_id" => row

	while ($row = $db->sql_fetchrow($result))
	{
		$report['total_legacy_rows']++;
		$key = $row['reaction_user_id'] . '-' . $row['post_id'];
		// Rows are read ORDER BY reaction_time ASC, so the last one
		// written to $latest_per_pair for a given key is always the
		// most recent - simplest possible "keep the latest" rule.
		$latest_per_pair[$key] = $row;
	}
	$db->sql_freeresult($result);

	$report['unique_pairs'] = count($latest_per_pair);
	$report['deduped_count'] = $report['total_legacy_rows'] - $report['unique_pairs'];

	// ---------------------------------------------------------------
	// Step 4 - map to icons (creating a placeholder on the fly for any
	// reaction type this importer doesn't already know about - see the
	// file-level note), skip existing reactions
	// ---------------------------------------------------------------

	// Existing (user_id, post_id) pairs already in sebo/postreact -
	// never overwrite live data.
	$existing_keys = [];
	$sql = 'SELECT user_id, post_id FROM ' . $reaction_table;
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$existing_keys[$row['user_id'] . '-' . $row['post_id']] = true;
	}
	$db->sql_freeresult($result);

	$to_insert = [];
	$label_icon_cache = []; // resolved label => icon_id, for the "unknown filename" path only

	foreach ($latest_per_pair as $key => $row)
	{
		if (isset($existing_keys[$key]))
		{
			$report['skipped_existing']++;
			continue;
		}

		$filename = trim($row['reaction_file_name']);

		if ($filename === '')
		{
			$report['skipped_invalid']++;
			continue;
		}

		if (isset($icon_map[$filename]))
		{
			// A filename we already have a fixed, correct mapping for
			// (an existing sebo/postreact icon, or an already-resolved
			// default placeholder) - always wins over any per-row
			// title, even if an admin renamed the type in steve/
			// reactions itself: the underlying image is still the same
			// emoji we already know how to map.
			$icon_id = $icon_map[$filename];
		}
		else
		{
			$row_title = ($has_row_title && isset($row['reaction_type_title'])) ? trim($row['reaction_type_title']) : '';
			$label = $row_title !== '' ? $row_title : steve_import_resolve_custom_label($filename, $type_titles);

			if (isset($label_icon_cache[$label]))
			{
				$icon_id = $label_icon_cache[$label];
			}
			else
			{
				[$icon_id, $log_line] = steve_import_find_or_create_placeholder_icon($db, $icon_table, $label, steve_import_codepoint_emoji_entity($filename) ?? '', $dry_run);
				$label_icon_cache[$label] = $icon_id;

				if ($log_line !== null)
				{
					$report['icon_log'][] = $log_line;
				}
				if (!in_array($label, $report['custom_icons_created'], true))
				{
					$report['custom_icons_created'][] = $label;
				}
			}
		}

		$to_insert[] = [
			'topic_id'   => (int) $row['topic_id'],
			'post_id'    => (int) $row['post_id'],
			'user_id'    => (int) $row['reaction_user_id'],
			'icon_id'    => (int) $icon_id,
			'react_time' => (int) $row['reaction_time'],
		];
	}

	$report['inserted_count'] = count($to_insert);

	// ---------------------------------------------------------------
	// Step 5 - insert + purge cache
	// ---------------------------------------------------------------

	if ($dry_run || empty($to_insert))
	{
		return $report;
	}

	$db->sql_multi_insert($reaction_table, $to_insert);

	// Purge the reaction-count cache (same keys as
	// reaction_count_cache::purge_all_topic_counts(), replicated here
	// since that service is not exposed to standalone scripts outside
	// the extension).
	$registry = $cache->get('_sebo_postreact_cached_topics');
	if (is_array($registry))
	{
		foreach ($registry as $topic_id)
		{
			$cache->destroy('_sebo_postreact_topic_counts_' . (int) $topic_id);
		}
	}
	$cache->destroy('_sebo_postreact_cached_topics');

	return $report;
}

function steve_import_find_legacy_table($db, $override)
{
	if ($override !== null && $override !== '')
	{
		if (!$db->sql_table_exists($override))
		{
			throw new steve_import_error("Table '{$override}' does not exist.");
		}

		return $override;
	}

	// Auto-detect: find a table that has both reaction_user_id and
	// reaction_file_name columns - that combination is unique to the
	// legacy steve/reactions schema (and to the third-party port it was
	// initially cross-checked against).
	$candidates = steve_import_tables_with_columns($db, ['reaction_user_id', 'reaction_file_name']);

	if (count($candidates) === 0)
	{
		throw new steve_import_error('No legacy reactions table found automatically. If you know its name, specify it explicitly. If it truly does not exist, there is nothing to import.');
	}

	if (count($candidates) > 1)
	{
		throw new steve_import_error('Several candidate tables found (' . implode(', ', $candidates) . '). Specify the right one explicitly.');
	}

	return $candidates[0];
}

/**
 * Best-effort: find a companion table listing the actual reaction types
 * (steve/reactions' own steve_reaction_types table). Returns null rather
 * than throwing if none is found or the lookup query isn't supported -
 * this is a nice-to-have for better labels, not a requirement.
 */
function steve_import_find_types_table($db)
{
	try
	{
		$candidates = steve_import_tables_with_columns($db, ['reaction_type_id', 'reaction_type_enable']);
	}
	catch (\Throwable $e)
	{
		return null;
	}

	return count($candidates) === 1 ? $candidates[0] : null;
}

/**
 * @return array filename => title, for every row in the given types
 *               table that has both a filename and a title set.
 */
function steve_import_load_type_titles($db, $types_table)
{
	$titles = [];

	$sql = 'SELECT reaction_file_name, reaction_type_title FROM ' . $types_table;
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['reaction_file_name'] !== '' && $row['reaction_type_title'] !== '')
		{
			$titles[$row['reaction_file_name']] = $row['reaction_type_title'];
		}
	}
	$db->sql_freeresult($result);

	return $titles;
}

/**
 * Does the given table have a column with this exact name?
 */
function steve_import_table_has_column($db, $table_name, $column_name)
{
	try
	{
		$sql = "SELECT 1
				FROM information_schema.COLUMNS
				WHERE TABLE_SCHEMA = DATABASE()
					AND TABLE_NAME = '" . $db->sql_escape($table_name) . "'
					AND COLUMN_NAME = '" . $db->sql_escape($column_name) . "'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		return (bool) $row;
	}
	catch (\Throwable $e)
	{
		return false;
	}
}

/**
 * Find every table in the current database whose columns include ALL of
 * the given column names.
 *
 * @return string[] table names
 */
function steve_import_tables_with_columns($db, array $column_names)
{
	$placeholders = implode(',', array_map(function ($c) use ($db) {
		return "'" . $db->sql_escape($c) . "'";
	}, $column_names));

	$sql = "SELECT TABLE_NAME, COUNT(*) AS matches
			FROM information_schema.COLUMNS
			WHERE TABLE_SCHEMA = DATABASE()
				AND COLUMN_NAME IN ({$placeholders})
			GROUP BY TABLE_NAME
			HAVING matches = " . count($column_names);

	try
	{
		$result = $db->sql_query($sql);
	}
	catch (\Throwable $e)
	{
		throw new steve_import_error('Table auto-detection query failed (this only works on MySQL/MariaDB). Specify the table name explicitly instead.');
	}

	$candidates = [];
	while ($row = $db->sql_fetchrow($result))
	{
		$candidates[] = $row['TABLE_NAME'];
	}
	$db->sql_freeresult($result);

	return $candidates;
}

/**
 * A handful of extra images ship with steve/reactions without being part
 * of its default 10-type install - if an admin added one of these as a
 * custom type by hand and we don't have a real title for it from the
 * types table, this gives a nicer label than the raw filename.
 */
function steve_import_known_extra_labels()
{
	return [
		'1f37a' => 'Beer',
		'1f47b' => 'Ghost',
	];
}

/**
 * Best label to use for a reaction filename this importer doesn't
 * already recognise: the real title from the types table if we have
 * one, then a small list of known extras, then a readable guess derived
 * from the filename itself.
 */
function steve_import_resolve_custom_label($filename, array $type_titles)
{
	if (isset($type_titles[$filename]) && $type_titles[$filename] !== '')
	{
		return $type_titles[$filename];
	}

	$base = pathinfo($filename, PATHINFO_FILENAME);
	$known_extras = steve_import_known_extra_labels();
	if (isset($known_extras[$base]))
	{
		return $known_extras[$base];
	}

	// Derive something readable from the filename: "heart-flames.svg"
	// -> "Heart Flames", "my_custom_icon.png" -> "My Custom Icon".
	$label = str_replace(['-', '_'], ' ', $base);
	$label = trim(preg_replace('/\s+/', ' ', $label));

	return $label !== '' ? ucwords($label) : $filename;
}

/**
 * If the filename (without extension) looks like a single Unicode code
 * point in hex (the convention both steve/reactions and its third-party
 * port use for the built-in emoji), return the matching HTML numeric
 * entity - so a custom type using this same convention still gets a
 * correct emoji, without needing it hardcoded. Returns null otherwise
 * (e.g. for a non-emoji custom image like "heart-flames.svg").
 */
function steve_import_codepoint_emoji_entity($filename)
{
	$base = pathinfo($filename, PATHINFO_FILENAME);

	if (!preg_match('/^[0-9a-f]{4,6}$/i', $base))
	{
		return null;
	}

	$codepoint = hexdec($base);

	return $codepoint > 0 ? '&#' . $codepoint . ';' : null;
}

/**
 * Find the icon_id of an existing icon by matching the trailing filename
 * of its icon_url (works whether the images still live under the
 * extension's styles dir or were moved to images/sebo_postreact/reactions
 * by install_data_2_3, and even if the admin swapped folders again).
 */
function steve_import_find_existing_icon_id($db, $icon_table, $filename)
{
	$sql = 'SELECT icon_id FROM ' . $icon_table . "
			WHERE icon_url LIKE '%" . $db->sql_escape($filename) . "'
			ORDER BY icon_id ASC";
	$result = $db->sql_query_limit($sql, 1);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	return $row ? (int) $row['icon_id'] : null;
}

/**
 * Find an icon previously created by this script (matched by icon_alt),
 * or create it disabled with no image assigned yet.
 *
 * @return array [icon_id, log line or null]
 */
function steve_import_find_or_create_placeholder_icon($db, $icon_table, $alt, $emoji_entity, $dry_run)
{
	$sql = 'SELECT icon_id FROM ' . $icon_table . "
			WHERE icon_alt = '" . $db->sql_escape($alt) . "'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if ($row)
	{
		return [(int) $row['icon_id'], null];
	}

	if ($dry_run)
	{
		// Fake id so the rest of the dry-run can still report counts.
		return [-1, "[dry-run] would create placeholder icon '{$alt}' (disabled, no image)"];
	}

	$sql = 'SELECT MAX(icon_id) AS max_id, MAX(icon_order) AS max_order FROM ' . $icon_table;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$new_id = ((int) $row['max_id']) + 1;
	$new_order = ((int) $row['max_order']) + 1;

	$sql = 'INSERT INTO ' . $icon_table . ' ' . $db->sql_build_array('INSERT', [
		'icon_id'     => $new_id,
		'icon_url'    => '',
		'icon_width'  => 0,
		'icon_height' => 0,
		'icon_alt'    => $alt,
		'status'      => 0,
		'icon_order'  => $new_order,
		'icon_emoji'  => $emoji_entity,
	]);
	$db->sql_query($sql);

	return [$new_id, "Created placeholder icon '{$alt}' (icon_id {$new_id}, disabled, image still to be assigned in ACP)"];
}
