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

class add_reaction_table_indexes extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_index_exists(
			$this->table_prefix . 'sebo_postreact_table',
			'topic_id_idx'
		);
	}

	public static function depends_on()
	{
		return ['\sebo\postreact\migrations\install_data_2_5_3'];
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
			'drop_index' => [
				$this->table_prefix . 'sebo_postreact_table' => [
					'topic_id_idx',
					'post_id_idx',
				],
			],
		];
	}
}
