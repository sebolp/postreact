<?php
/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, sebo
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\migrations;

class install_data_2_1_1 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\sebo\postreact\migrations\install_data_2_1'];
	}

	public function update_data()
	{
		return [
			['config.add', ['sebo_postreact_display_position', 0]],
		];
	}

	public function revert_data()
	{
		return [
			['config.remove', ['sebo_postreact_display_position']],
		];
	}
}
