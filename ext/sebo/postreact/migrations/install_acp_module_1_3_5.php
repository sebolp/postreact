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

class install_acp_module_1_3_5 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\sebo\postreact\migrations\install_acp_module'];
	}

	public function update_data()
	{
		return [
			['module.add', ['acp', 'ACP_POSTREACT_TITLE', [
					'module_basename'	=> '\sebo\postreact\acp\purge_module',
					'module_langname'	=> 'ACP_POSTREACT_PURGE',
					'module_mode'		=> 'settings',
					'module_auth'		=> 'ext_sebo/postreact && acl_a_board',
			]]],
		];
	}

	public function revert_schema()
	{
		return [
			['module.remove', ['acp', 'ACP_POSTREACT_TITLE', [
					'module_basename'	=> '\sebo\postreact\acp\purge_module',
					'module_langname'	=> 'ACP_POSTREACT_PURGE',
					'module_mode'		=> 'settings',
					'module_auth'		=> 'ext_sebo/postreact && acl_a_board',
			]]],
		];
	}
}
