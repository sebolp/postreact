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

		$icon_ids = [];

		// Fetch all icon IDs into an array to avoid executing queries inside the fetch loop
		while ($row = $this->db->sql_fetchrow($result))
		{
			$icon_ids[] = $row['icon_id'];
		}
		
		// Free the result set immediately after fetching
		$this->db->sql_freeresult($result);

		if (!empty($icon_ids))
		{
			$order = 1;

			// Iterate through the array in memory and update records using sql_build_array
			foreach ($icon_ids as $icon_id)
			{
				$sql_ary = [
					'icon_order'	=> $order,
				];

				$sql = 'UPDATE ' . $this->table_prefix . 'sebo_postreact_icon
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE icon_id = ' . (int) $icon_id;
					
				$this->db->sql_query($sql);

				$order++;
			}
		}
	}
}
