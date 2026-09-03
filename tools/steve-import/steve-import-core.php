<?php
/**
 * Shared core logic for the steve/postreactions -> sebo/postreact importer.
 *
 * NOT part of the PostReaction extension, and not meant to be requested
 * directly - it is require()'d by import-steve-reactions.php (CLI) and
 * import-steve-reactions-web.php (browser), so the two front-ends can
 * never drift out of sync with each other.
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
		'dry_run'           => (bool) $dry_run,
		'old_table'         => null,
		'icon_log'          => [],
		'total_legacy_rows' => 0,
		'unique_pairs'      => 0,
		'deduped_count'     => 0,
		'skipped_existing'  => 0,
		'skipped_unmapped'  => [],
		'inserted_count'    => 0,
	];

	// ---------------------------------------------------------------
	// Step 1 - locate the legacy table
	// ---------------------------------------------------------------

	$report['old_table'] = steve_import_find_legacy_table($db, $old_table_override);

	// ---------------------------------------------------------------
	// Step 2 - resolve / create the icon mapping
	// ---------------------------------------------------------------

	$icon_map = [];

	$existing_lookups = [
		'1f44d.png' => 'like.png',
		'1f60d.png' => 'love.png',
		'1f602.png' => 'laugh.png',
		'1f62d.png' => 'cry.png',
		'1f621.png' => 'angry.png',
		'1f641.png' => 'sad.png',
		'1f62f.png' => 'surprise.png',
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

	$placeholder_specs = [
		'1f44e.png' => ['alt' => 'Dislike',        'emoji' => '&#128078;'],
		'1f642.png' => ['alt' => 'Neutral',         'emoji' => '&#128578;'],
		'1f611.png' => ['alt' => 'Expressionless',  'emoji' => '&#128529;'],
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
	// per user/post pair - sebo/postreact allows only one reaction
	// per post, steve/postreactions allowed several)
	// ---------------------------------------------------------------

	$sql = 'SELECT reaction_user_id, post_id, topic_id, reaction_file_name, reaction_time
			FROM ' . $report['old_table'] . '
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
	// Step 4 - map to icons, skip unmapped filenames and existing
	// reactions
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

	foreach ($latest_per_pair as $key => $row)
	{
		if (isset($existing_keys[$key]))
		{
			$report['skipped_existing']++;
			continue;
		}

		$filename = $row['reaction_file_name'];

		if (!isset($icon_map[$filename]))
		{
			$report['skipped_unmapped'][$filename] = ($report['skipped_unmapped'][$filename] ?? 0) + 1;
			continue;
		}

		$to_insert[] = [
			'topic_id'   => (int) $row['topic_id'],
			'post_id'    => (int) $row['post_id'],
			'user_id'    => (int) $row['reaction_user_id'],
			'icon_id'    => (int) $icon_map[$filename],
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
	// legacy steve/postreactions schema.
	$sql = "SELECT TABLE_NAME, COUNT(*) AS matches
			FROM information_schema.COLUMNS
			WHERE TABLE_SCHEMA = DATABASE()
				AND COLUMN_NAME IN ('reaction_user_id', 'reaction_file_name')
			GROUP BY TABLE_NAME
			HAVING matches = 2";

	try
	{
		$result = $db->sql_query($sql);
	}
	catch (\Throwable $e)
	{
		throw new steve_import_error('Auto-detection query failed (this only works on MySQL/MariaDB). Specify the table name explicitly instead.');
	}

	$candidates = [];
	while ($row = $db->sql_fetchrow($result))
	{
		$candidates[] = $row['TABLE_NAME'];
	}
	$db->sql_freeresult($result);

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
