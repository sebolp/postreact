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

class install_data extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\sebo\postreact\migrations\install_schema'];
	}

	public function update_data()
	{
		return [
			// Add new config table settings
			['config.add', ['sebo_postreact', 0]],
			// Add new permissions
			['permission.add', ['u_new_sebo_postreact']],
			['permission.add', ['u_new_sebo_postreact_view']],
			// Set our new permissions
			['permission.permission_set', ['ROLE_USER_FULL', 'u_new_sebo_postreact', 1]],
			['permission.permission_set', ['ROLE_USER_STANDARD', 'u_new_sebo_postreact', 1]],
			['permission.permission_set', ['REGISTERED', 'u_new_sebo_postreact', 'group', 1]],
			['permission.permission_set', ['ADMINISTRATORS', 'u_new_sebo_postreact', 'group', 1]],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_new_sebo_postreact', 'group', 1]],
			['permission.permission_set', ['NEWLY_REGISTERED', 'u_new_sebo_postreact', 'group', false]],
			['permission.permission_set', ['BOTS', 'u_new_sebo_postreact', 'group', false]],
			['permission.permission_set', ['REGISTERED_COPPA', 'u_new_sebo_postreact', 'group', false]],
			// All view
			['permission.permission_set', ['ROLE_USER_FULL', 'u_new_sebo_postreact_view', 1]],
			['permission.permission_set', ['ROLE_USER_STANDARD', 'u_new_sebo_postreact_view', 1]],
			['permission.permission_set', ['REGISTERED', 'u_new_sebo_postreact_view', 'group', 1]],
			['permission.permission_set', ['ADMINISTRATORS', 'u_new_sebo_postreact_view', 'group', 1]],
			['permission.permission_set', ['GLOBAL_MODERATORS', 'u_new_sebo_postreact_view', 'group', 1]],
			['permission.permission_set', ['NEWLY_REGISTERED', 'u_new_sebo_postreact_view', 'group', 1]],
			['permission.permission_set', ['BOTS', 'u_new_sebo_postreact_view', 'group', 1]],
			['permission.permission_set', ['REGISTERED_COPPA', 'u_new_sebo_postreact_view', 'group', 1]],

			['custom', [[$this, 'table_pr_install']]],
		];
	}

	public function revert_data()
	{
		return [
			['config.remove', ['sebo_postreact', 0]],
			['permission.remove', ['u_new_sebo_postreact']],
			['permission.remove', ['u_new_sebo_postreact_view']],
		];
	}

	public function table_pr_install()
	{
		$data = [
				[
					'id'            => 1,
					'icon_id'       => 1,
					'icon_url'      => 'ext/sebo/postreact/styles/all/img/like.png',
					'icon_width'    => 32,
					'icon_height'   => 32,
					'icon_alt'      => 'Like',
					'status'        => '1',
					'active'        => '0',
				],
				[
					'id'            => 2,
					'icon_id'       => 2,
					'icon_url'      => 'ext/sebo/postreact/styles/all/img/heart.png',
					'icon_width'    => 32,
					'icon_height'   => 32,
					'icon_alt'      => 'Heart',
					'status'        => '1',
					'active'        => '0',
				],
				[
					'id'            => 3,
					'icon_id'       => 3,
					'icon_url'      => 'ext/sebo/postreact/styles/all/img/laugh.png',
					'icon_width'    => 32,
					'icon_height'   => 32,
					'icon_alt'      => 'Laught',
					'status'        => '1',
					'active'        => '0',
				],
				[
					'id'            => 4,
					'icon_id'       => 4,
					'icon_url'      => 'ext/sebo/postreact/styles/all/img/sad.png',
					'icon_width'    => 32,
					'icon_height'   => 32,
					'icon_alt'      => 'Sad',
					'status'        => '1',
					'active'        => '0',
				],
				[
					'id'            => 5,
					'icon_id'       => 5,
					'icon_url'      => 'ext/sebo/postreact/styles/all/img/angry.png',
					'icon_width'    => 32,
					'icon_height'   => 32,
					'icon_alt'      => 'Angry',
					'status'        => '1',
					'active'        => '0',
				],
				[
					'id'            => 6,
					'icon_id'       => 6,
					'icon_url'      => 'ext/sebo/postreact/styles/all/img/surprise.png',
					'icon_width'    => 32,
					'icon_height'   => 32,
					'icon_alt'      => 'Suprise',
					'status'        => '1',
					'active'        => '0',
				],
				[
					'id'            => 7,
					'icon_id'       => 7,
					'icon_url'      => 'ext/sebo/postreact/styles/all/img/sunglasses.png',
					'icon_width'    => 32,
					'icon_height'   => 32,
					'icon_alt'      => 'Cool',
					'status'        => '1',
					'active'        => '0',
				],
				[
					'id'            => 8,
					'icon_id'       => 8,
					'icon_url'      => 'ext/sebo/postreact/styles/all/img/love.png',
					'icon_width'    => 32,
					'icon_height'   => 32,
					'icon_alt'      => 'Love',
					'status'        => '0',
					'active'        => '0',
				],
				[
					'id'            => 9,
					'icon_id'       => 9,
					'icon_url'      => 'ext/sebo/postreact/styles/all/img/skype/worker.png',
					'icon_width'    => 32,
					'icon_height'   => 32,
					'icon_alt'      => 'Worker',
					'status'        => '1',
					'active'        => '0',
				],
				[
					'id'            => 10,
					'icon_id'       => 10,
					'icon_url'      => 'ext/sebo/postreact/styles/all/img/skype/lol.png',
					'icon_width'    => 32,
					'icon_height'   => 32,
					'icon_alt'      => 'LOL',
					'status'        => '1',
					'active'        => '0',
				],
				[
					'id'            => 11,
					'icon_id'       => 11,
					'icon_url'      => 'ext/sebo/postreact/styles/all/img/skype/party.png',
					'icon_width'    => 32,
					'icon_height'   => 32,
					'icon_alt'      => 'Party',
					'status'        => '1',
					'active'        => '0',
				],
				[
					'id'            => 12,
					'icon_id'       => 12,
					'icon_url'      => 'ext/sebo/postreact/styles/all/img/skype/mechanic.png',
					'icon_width'    => 32,
					'icon_height'   => 32,
					'icon_alt'      => 'Mechanic',
					'status'        => '1',
					'active'        => '0',
				],
				[
					'id'            => 13,
					'icon_id'       => 13,
					'icon_url'      => 'ext/sebo/postreact/styles/all/img/skype/cry.png',
					'icon_width'    => 32,
					'icon_height'   => 32,
					'icon_alt'      => 'Cry',
					'status'        => '1',
					'active'        => '0',
				],
				[
					'id'            => 14,
					'icon_id'       => 14,
					'icon_url'      => 'ext/sebo/postreact/styles/all/img/skype/censored.png',
					'icon_width'    => 32,
					'icon_height'   => 32,
					'icon_alt'      => 'Censored',
					'status'        => '1',
					'active'        => '0',
				],
				[
					'id'            => 15,
					'icon_id'       => 15,
					'icon_url'      => 'ext/sebo/postreact/styles/all/img/telegram/waving.webp',
					'icon_width'    => 32,
					'icon_height'   => 32,
					'icon_alt'      => 'Hello!',
					'status'        => '1',
					'active'        => '0',
				],
		];
		$sql_ary = [
			'SELECT'        => 'COUNT(*) AS total_rows',
			'FROM'          => [$this->table_prefix . 'sebo_postreact_icon' => ''],
		];
		$result = $this->db->sql_build_query('SELECT', $sql_ary);
		$result_check = $this->db->sql_query($result);
		$row_check = $this->db->sql_fetchrow($result_check);
		$this->db->sql_freeresult($result_check);
		if ($row_check['total_rows'] == 0)
		{
		$this->db->sql_multi_insert($this->table_prefix . 'sebo_postreact_icon', $data);
		}
	}
}
