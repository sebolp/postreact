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

class fix_role_permissions extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\sebo\postreact\migrations\install_data_2_5_3'];
	}

	public function update_data()
	{
		return [
			['permission.permission_set', ['ROLE_USER_FULL', 'u_new_sebo_postreact']],
			['permission.permission_set', ['ROLE_USER_STANDARD', 'u_new_sebo_postreact']],
			['permission.permission_set', ['ROLE_USER_FULL', 'u_new_sebo_postreact_view']],
			['permission.permission_set', ['ROLE_USER_STANDARD', 'u_new_sebo_postreact_view']],
		];
	}

	public function revert_data()
	{
		// Intentionally a no-op: reverting would deny a permission that
		// install_data.php already claims (incorrectly) to have granted.
		return [];
	}
}
