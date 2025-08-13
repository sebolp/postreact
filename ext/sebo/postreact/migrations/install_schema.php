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

class install_schema extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330'];
	}

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
		];
	}

	public function revert_schema()
	{
    $sql = 'SELECT COUNT(*) AS total_rows
            FROM ' . $this->table_prefix . 'sebo_postreact_table';
    $result = $this->db->sql_query($sql);
    $total_rows = (int) $this->db->sql_fetchfield('total_rows');
    $this->db->sql_freeresult($result);
		if ($total_rows === 0)
		{
			return [
				'remove_tables' => [
					$this->table_prefix . 'sebo_postreact_table',
				],
			];
		}
    return [];
	}
}
