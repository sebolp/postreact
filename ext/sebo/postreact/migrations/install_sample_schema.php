<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, sebo, https://www.fiatpandaclub.org - Thanks Chris1278!
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\migrations;

class install_sample_schema extends \phpbb\db\migration\migration
{
 '`t' * ($matches[0].Length / 4) public static function depends_on()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) return ['\phpbb\db\migration\data\v330\v330'];
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Update database schema.
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * https://area51.phpbb.com/docs/dev/3.2.x/migrations/schema_changes.html
 "`t" * ($matches[0].Length / 4)  *  add_tables: Add tables
 "`t" * ($matches[0].Length / 4)  *  drop_tables: Drop tables
 "`t" * ($matches[0].Length / 4)  *  add_columns: Add columns to a table
 "`t" * ($matches[0].Length / 4)  *  drop_columns: Removing/Dropping columns
 "`t" * ($matches[0].Length / 4)  *  change_columns: Column changes (only type, not name)
 "`t" * ($matches[0].Length / 4)  *  add_primary_keys: adding primary keys
 "`t" * ($matches[0].Length / 4)  *  add_unique_index: adding an unique index
 "`t" * ($matches[0].Length / 4)  *  add_index: adding an index (can be column:index_size if you need to provide size)
 "`t" * ($matches[0].Length / 4)  *  drop_keys: Dropping keys
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * This sample migration adds a new column to the users table.
 "`t" * ($matches[0].Length / 4)  * It also adds an example of a new table that can hold new data.
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @return array Array of schema changes
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function update_schema()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) return [
 '`t' * ($matches[0].Length / 4) 'add_tables'        => [
 '`t' * ($matches[0].Length / 4) $this->table_prefix . 'sebo_postreact_table'    => [
 '`t' * ($matches[0].Length / 4) 'COLUMNS'       => [
 '`t' * ($matches[0].Length / 4) 'postreact_id'          => ['UINT', null, 'auto_increment'],
 '`t' * ($matches[0].Length / 4) 'topic_id'              => ['UINT', 0],
 '`t' * ($matches[0].Length / 4) 'post_id'               => ['UINT', 0],
 '`t' * ($matches[0].Length / 4) 'user_id'               => ['UINT', 0],
 '`t' * ($matches[0].Length / 4) 'icon_id'               => ['UINT', 0],
 '`t' * ($matches[0].Length / 4) 'react_time'            => ['UINT:11', 0],
 '`t' * ($matches[0].Length / 4) ],
 '`t' * ($matches[0].Length / 4) 'PRIMARY_KEY'   => 'postreact_id',
 '`t' * ($matches[0].Length / 4) ],
 '`t' * ($matches[0].Length / 4) $this->table_prefix . 'sebo_postreact_icon' => [
 '`t' * ($matches[0].Length / 4) 'COLUMNS'       => [
 '`t' * ($matches[0].Length / 4) 'id'            => ['UINT', null, 'auto_increment'],
 '`t' * ($matches[0].Length / 4) 'icon_id'           => ['UINT', 0],
 '`t' * ($matches[0].Length / 4) 'icon_url'          => ['VCHAR:200', ''],
 '`t' * ($matches[0].Length / 4) 'icon_width'        => ['UINT', 0],
 '`t' * ($matches[0].Length / 4) 'icon_height'       => ['UINT', 0],
 '`t' * ($matches[0].Length / 4) 'icon_alt'          => ['VCHAR:100', ''],
 '`t' * ($matches[0].Length / 4) 'status'            => ['UINT:1', 0],
 '`t' * ($matches[0].Length / 4) 'active'            => ['UINT:1', 0],
 '`t' * ($matches[0].Length / 4) ],
 '`t' * ($matches[0].Length / 4) 'PRIMARY_KEY'   => 'id',
 '`t' * ($matches[0].Length / 4) ],
 '`t' * ($matches[0].Length / 4) ],
 '`t' * ($matches[0].Length / 4) ];
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Revert database schema changes. This method is almost always required
 "`t" * ($matches[0].Length / 4)  * to revert the changes made above by update_schema.
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * https://area51.phpbb.com/docs/dev/3.2.x/migrations/schema_changes.html
 "`t" * ($matches[0].Length / 4)  *  add_tables: Add tables
 "`t" * ($matches[0].Length / 4)  *  drop_tables: Drop tables
 "`t" * ($matches[0].Length / 4)  *  add_columns: Add columns to a table
 "`t" * ($matches[0].Length / 4)  *  drop_columns: Removing/Dropping columns
 "`t" * ($matches[0].Length / 4)  *  change_columns: Column changes (only type, not name)
 "`t" * ($matches[0].Length / 4)  *  add_primary_keys: adding primary keys
 "`t" * ($matches[0].Length / 4)  *  add_unique_index: adding an unique index
 "`t" * ($matches[0].Length / 4)  *  add_index: adding an index (can be column:index_size if you need to provide size)
 "`t" * ($matches[0].Length / 4)  *  drop_keys: Dropping keys
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * This sample migration removes the column that was added the users table in update_schema.
 "`t" * ($matches[0].Length / 4)  * It also removes the table that was added in update_schema.
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @return array Array of schema changes
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function revert_schema()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) return [
 '`t' * ($matches[0].Length / 4) 'drop_tables'       => [
 '`t' * ($matches[0].Length / 4) $this->table_prefix . 'sebo_postreact_table',
 '`t' * ($matches[0].Length / 4) $this->table_prefix . 'sebo_postreact_icon',
 '`t' * ($matches[0].Length / 4) ],
 '`t' * ($matches[0].Length / 4) ];
 '`t' * ($matches[0].Length / 4) }
}
