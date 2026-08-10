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

class install_data_2_5_2 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\sebo\postreact\migrations\install_data_2_5'];
	}

	public function update_schema()
	{
		return [
			['config.add', ['sebo_postreact_butt_position', 'up']],
		];
	}

	public function revert_schema()
	{
		return [
			['config.remove', ['sebo_postreact_butt_position']],
		];
	}
}
