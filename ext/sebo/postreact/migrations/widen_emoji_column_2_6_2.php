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

class widen_emoji_column_2_6_2 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return ['\sebo\postreact\migrations\add_user_notify_mode'];
	}

	public function update_schema()
	{
		return [
			'change_columns' => [
				$this->table_prefix . 'sebo_postreact_icon' => [
					'icon_emoji' => ['VCHAR_UNI', 255],
					'icon_url'   => ['VCHAR_UNI', 200],
					'icon_alt'   => ['VCHAR_UNI', 100],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'change_columns' => [
				$this->table_prefix . 'sebo_postreact_icon' => [
					'icon_emoji' => ['VCHAR', 50],
					'icon_url'   => ['VCHAR', 200],
					'icon_alt'   => ['VCHAR', 100],
				],
			],
		];
	}
}