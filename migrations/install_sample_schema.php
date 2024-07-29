<?php
/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\migrations;

class install_sample_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'sebo_postreact_table');
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v320\v320'];
	}

	/**
	 * Update database schema.
	 *
	 * https://area51.phpbb.com/docs/dev/3.2.x/migrations/schema_changes.html
	 *	add_tables: Add tables
	 *	drop_tables: Drop tables
	 *	add_columns: Add columns to a table
	 *	drop_columns: Removing/Dropping columns
	 *	change_columns: Column changes (only type, not name)
	 *	add_primary_keys: adding primary keys
	 *	add_unique_index: adding an unique index
	 *	add_index: adding an index (can be column:index_size if you need to provide size)
	 *	drop_keys: Dropping keys
	 *
	 * This sample migration adds a new column to the users table.
	 * It also adds an example of a new table that can hold new data.
	 *
	 * @return array Array of schema changes
	 */
	public function update_schema()
	{
		return [
			'add_tables'		=> [
				$this->table_prefix . 'sebo_postreact_table'	=> [
					'COLUMNS'		=> [
						'postreact_id'			=> ['UINT', null, 'auto_increment'],
						'topic_id'				=> ['UINT', 0],
						'post_id'				=> ['UINT', 0],
						'user_id'				=> ['UINT', 0],
						'icon_id'				=> ['UINT', 0],
						'react_time'			=> ['UINT:11', 0],
					],
					'PRIMARY_KEY'	=> 'postreact_id',
				],
				$this->table_prefix . 'sebo_postreact_icon'	=> [
					'COLUMNS'		=> [
						'id'			=> ['UINT', null, 'auto_increment'],
						'icon_id'			=> ['UINT', 0],
						'icon_url'			=> ['VCHAR:200', ''],
						'icon_width'		=> ['UINT', 0],
						'icon_height'		=> ['UINT', 0],
						'icon_alt'			=> ['VCHAR:100', ''],
						'status'			=> ['UINT:1', 0],
						'active'			=> ['UINT:1', 0],
					],
					'PRIMARY_KEY'	=> 'id',
				],
			],
			//'add_columns'	=> [
			//	$this->table_prefix . 'users'			=> [
			//		'user_postreact'				=> ['UINT', 0],
			//	],
			//],
		];
	}

	/**
	 * Revert database schema changes. This method is almost always required
	 * to revert the changes made above by update_schema.
	 *
	 * https://area51.phpbb.com/docs/dev/3.2.x/migrations/schema_changes.html
	 *	add_tables: Add tables
	 *	drop_tables: Drop tables
	 *	add_columns: Add columns to a table
	 *	drop_columns: Removing/Dropping columns
	 *	change_columns: Column changes (only type, not name)
	 *	add_primary_keys: adding primary keys
	 *	add_unique_index: adding an unique index
	 *	add_index: adding an index (can be column:index_size if you need to provide size)
	 *	drop_keys: Dropping keys
	 *
	 * This sample migration removes the column that was added the users table in update_schema.
	 * It also removes the table that was added in update_schema.
	 *
	 * @return array Array of schema changes
	 */
	public function revert_schema()
	{
		return [
			//'drop_columns'	=> [
			//	$this->table_prefix . 'users'			=> [
			//		'user_postreact',
			//	],
			//],
			'drop_tables'		=> [
				$this->table_prefix . 'sebo_postreact_table',
				$this->table_prefix . 'sebo_postreact_icon',
			],
		];
	}
}
