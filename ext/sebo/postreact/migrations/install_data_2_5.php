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

class install_data_2_5 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists(
			$this->table_prefix . 'sebo_postreact_icon',
			'icon_order'
		);
	}

	public static function depends_on()
	{
		return ['\sebo\postreact\migrations\install_data_2_3'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'sebo_postreact_icon' => [
					'icon_order' => ['UINT', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'sebo_postreact_icon' => [
					'icon_order',
				],
			],
		];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'init_icon_order']]],
		];
	}

	/**
	 * Initialize icon_order based on current icon_id ordering.
	 */
	public function init_icon_order()
	{
		$sql = 'SELECT icon_id FROM ' . $this->table_prefix . 'sebo_postreact_icon ORDER BY icon_id ASC';
		$result = $this->db->sql_query($sql);

		$order = 1;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->db->sql_query('UPDATE ' . $this->table_prefix . 'sebo_postreact_icon
				SET icon_order = ' . $order . '
				WHERE icon_id = ' . (int) $row['icon_id']);
			$order++;
		}
		$this->db->sql_freeresult($result);
	}
}
