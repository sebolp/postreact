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

class install_acp_module extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\sebo\postreact\migrations\install_sample_data'];
	}

	public function update_data()
	{
		return [

			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_POSTREACT_TITLE'
			]],
			['module.add', [
				'acp',
				'ACP_POSTREACT_TITLE',
				[
					'module_basename'	=> '\sebo\postreact\acp\main_module',
					'modes'				=> ['settings'],
				],
			]],
		];
	}
}
