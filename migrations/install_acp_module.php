<?php
/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\migrations;

class install_acp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['sebo_postreact']);
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
