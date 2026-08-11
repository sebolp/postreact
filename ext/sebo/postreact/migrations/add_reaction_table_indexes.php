<?php

namespace sebo\postreact\migrations;

/**
 * Add indexes to the reaction table.
 *
 * All reaction lookups are keyed by topic_id (viewforum/search counts,
 * notifications) or post_id (viewtopic batch). The table previously had no
 * indexes on either, so every lookup was a full scan.
 */
class add_reaction_table_indexes extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\sebo\postreact\migrations\install_data_2_5_2'];
	}

	public function update_schema()
	{
		return [
			'add_index' => [
				$this->table_prefix . 'sebo_postreact_table' => [
					'topic_id_idx' => ['topic_id'],
					'post_id_idx'  => ['post_id'],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_keys' => [
				$this->table_prefix . 'sebo_postreact_table' => ['topic_id_idx', 'post_id_idx'],
			],
		];
	}
}
