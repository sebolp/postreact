<?php
/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\migrations;

/**
 * Enforces one reaction per user per post at the database level.
 *
 * The extension's whole add/remove toggle is built on the assumption that
 * (user_id, post_id) is unique - check_existing_reaction() counts rows to
 * decide between insert and delete. Nothing enforced that, and
 * add_reaction_table_indexes only added non-unique indexes, so two
 * concurrent requests could both pass the count check and both insert.
 *
 * The resulting duplicate rows are not cosmetic: the count shown on the
 * post read 2 for a single user's single reaction, and the "already
 * reacted" wrapper in the viewtopic template was emitted once per row.
 *
 * Runs after dedupe_reactions, which clears existing duplicates - without
 * that step the index creation below would fail on any board the race has
 * already touched.
 */
class add_unique_user_post_index extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_index_exists(
			$this->table_prefix . 'sebo_postreact_table',
			'user_post_unique'
		);
	}

	public static function depends_on()
	{
		return ['\sebo\postreact\migrations\dedupe_reactions'];
	}

	public function update_schema()
	{
		return [
			'add_unique_index' => [
				$this->table_prefix . 'sebo_postreact_table' => [
					'user_post_unique' => ['user_id', 'post_id'],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			// drop_keys, not drop_index: drop_index is not a change type
			// perform_schema_changes() understands, so it would silently
			// do nothing.
			'drop_keys' => [
				$this->table_prefix . 'sebo_postreact_table' => [
					'user_post_unique',
				],
			],
		];
	}
}
