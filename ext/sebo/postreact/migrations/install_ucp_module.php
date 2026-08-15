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

class install_ucp_module extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\sebo\postreact\migrations\add_user_notify_mode'];
	}

	public function update_data()
	{
		return [
			['module.add', ['ucp', 'UCP_PREFS', [
					'module_basename'	=> '\sebo\postreact\ucp\main_module',
					'module_langname'	=> 'UCP_POSTREACT_TITLE',
					'module_mode'		=> 'settings',
					'module_auth'		=> '',
			]]],
		];
	}

	public function revert_data()
	{
		return [
			['module.remove', ['ucp', 'UCP_PREFS', [
					'module_basename'	=> '\sebo\postreact\ucp\main_module',
					'module_langname'	=> 'UCP_POSTREACT_TITLE',
					'module_mode'		=> 'settings',
					'module_auth'		=> '',
			]]],
		];
	}
}
