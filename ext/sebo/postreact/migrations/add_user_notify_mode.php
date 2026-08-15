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

class add_user_notify_mode extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists(
			USERS_TABLE,
			'user_postreact_notify_mode'
		);
	}

	public static function depends_on()
	{
		return ['\sebo\postreact\migrations\install_data_2_5_3'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				USERS_TABLE => [
					'user_postreact_notify_mode' => ['UINT:1', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				USERS_TABLE => [
					'user_postreact_notify_mode',
				],
			],
		];
	}
}
