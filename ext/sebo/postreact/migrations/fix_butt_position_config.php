<?php

namespace sebo\postreact\migrations;

/**
 * Fix install_data_2_5_2: the sebo_postreact_butt_position config.add was
 * placed inside update_schema(), where the phpBB migrator ignores config
 * operations (they belong in update_data()). The config was therefore
 * never created, producing an undefined-index notice on the PostReaction
 * ACP page. config.add is a no-op if the config already exists, so this
 * is safe on installs where the config was created by other means.
 */
class fix_butt_position_config extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\sebo\postreact\migrations\install_data_2_5_2'];
	}

	public function update_data()
	{
		return [
			['config.add', ['sebo_postreact_butt_position', 'up']],
		];
	}

	public function revert_data()
	{
		return [
			['config.remove', ['sebo_postreact_butt_position']],
		];
	}
}
