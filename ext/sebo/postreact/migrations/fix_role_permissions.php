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
 * install_data.php's permission_set() calls for ROLE_USER_FULL and
 * ROLE_USER_STANDARD passed 1 as the 3rd positional argument, which is
 * $type (expects 'role'/'group'), not $has_permission (4th argument,
 * defaults to true). Those roles were therefore left with
 * u_new_sebo_postreact(_view) denied instead of granted on every install
 * that ran the original migration. This re-applies the correct calls;
 * permission_set() is safe to call again with the intended values.
 */
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
