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
 * Collapses duplicate (user_id, post_id) reaction rows down to one.
 *
 * add_reaction() decides between insert and remove with a read followed by
 * a write, with no transaction and no unique constraint, so two concurrent
 * requests on the same post could both observe "no reaction" and both
 * insert. Boards that hit that race carry duplicate rows today.
 *
 * This has to run before the unique index is added, or the index creation
 * fails on exactly the boards that need it. phpbb\db\migrator runs
 * update_schema() before update_data() within a single migration, so the
 * cleanup cannot live in the same migration as the index - hence the
 * separate migration, which this one is depended on by.
 *
 * The earliest row is kept: it is the user's first reaction, so it is the
 * one any notification was created against.
 */
class dedupe_reactions extends \phpbb\db\migration\migration
{
	/**
	 * Considered done once no (user_id, post_id) pair occurs more than once.
	 * Self-healing: a board that somehow regains a duplicate will run this
	 * again, and a clean board skips the scan's write path entirely.
	 */
	public function effectively_installed()
	{
		$sql = 'SELECT user_id, post_id
			FROM ' . $this->table_prefix . 'sebo_postreact_table
			GROUP BY user_id, post_id
			HAVING COUNT(*) > 1';
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return !$row;
	}

	public static function depends_on()
	{
		return ['\sebo\postreact\migrations\widen_emoji_column_2_6_2'];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'remove_duplicate_reactions']]],
		];
	}

	public function revert_data()
	{
		// Intentionally a no-op: the removed rows are duplicates and
		// cannot be reconstructed.
		return [];
	}

	/**
	 * Delete every reaction row that is not the earliest for its
	 * (user_id, post_id) pair.
	 */
	public function remove_duplicate_reactions()
	{
		$sql = 'SELECT user_id, post_id, MIN(postreact_id) AS keep_id
			FROM ' . $this->table_prefix . 'sebo_postreact_table
			GROUP BY user_id, post_id
			HAVING COUNT(*) > 1';
		$result = $this->db->sql_query($sql);

		// Collect the groups before issuing any further query, so no query
		// runs while this result set is still open.
		$groups = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$groups[] = $row;
		}
		$this->db->sql_freeresult($result);

		if (empty($groups))
		{
			return;
		}

		$delete_ids = [];
		foreach ($groups as $row)
		{
			$sql_members = 'SELECT postreact_id
				FROM ' . $this->table_prefix . 'sebo_postreact_table
				WHERE user_id = ' . (int) $row['user_id'] . '
					AND post_id = ' . (int) $row['post_id'] . '
					AND postreact_id <> ' . (int) $row['keep_id'];
			$result_members = $this->db->sql_query($sql_members);
			while ($member = $this->db->sql_fetchrow($result_members))
			{
				$delete_ids[] = (int) $member['postreact_id'];
			}
			$this->db->sql_freeresult($result_members);
		}

		if (empty($delete_ids))
		{
			return;
		}

		// Delete by explicit primary key list rather than a self-joining
		// DELETE, which MySQL accepts but PostgreSQL, Oracle and SQLite
		// reject. Batched so the IN list stays a sane length.
		// $delete_ids holds the ids to remove, so the set is an inclusive
		// IN list - negating it would delete exactly the rows to keep.
		foreach (array_chunk($delete_ids, 500) as $chunk)
		{
			$sql_delete = 'DELETE FROM ' . $this->table_prefix . 'sebo_postreact_table
				WHERE ' . $this->db->sql_in_set('postreact_id', $chunk);
			$this->db->sql_query($sql_delete);
		}
	}
}
