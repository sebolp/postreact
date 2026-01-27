<?php
/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\notification\type;

class postreact_notification extends \phpbb\notification\type\base
{
	/** @var \phpbb\controller\helper */
	protected $helper;
	/** @var \phpbb\user_loader */
	protected $user_loader;
	/** @var \phpbb\config\config */
	protected $config;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	protected $table_prefix;

	// === Edit: do not consent notification group
	public $groupByItem = false;

	public function set_controller_helper(\phpbb\controller\helper $helper)
	{
		$this->helper = $helper;
	}

	public function set_table_prefix($table_prefix)
	{
		$this->table_prefix = $table_prefix;
	}

	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	public function get_type()
	{
		return 'sebo.postreact.notification.type.postreact_notification';
	}

	public static $notification_option = [
		'lang'		=> 'NOTIFICATION_TYPE_POSTREACT',
		'group'		=> 'NOTIFICATION_GROUP_POSTING',
	];

	public function is_available()
	{
		return true;
	}

	/**
	 * ID Item
	 * must be (INT), otherwise ajax error
	 */
	public static function get_item_id($data)
	{
		return isset($data['item_id']) ? (int) $data['item_id'] : (isset($data['PR_N_item_id']) ? (int) $data['PR_N_item_id'] : 0);
	}

	public static function get_item_parent_id($data)
	{
		return isset($data['item_parent_id']) ? (int) $data['item_parent_id'] : 0;
	}

	public function find_users_for_notification($data, $options = [])
	{
		$user_id = isset($data['user_id']) ? $data['user_id'] : (isset($data['PR_N_user_id']) ? $data['PR_N_user_id'] : 0);
		return $this->check_user_notification_options([$user_id], $options);
	}

	public function users_to_query()
	{
		$sender_id = $this->get_data('sender_id');
		if (!$sender_id) {
			$sender_id = $this->get_data('PR_N_sender_id');
		}
		return [$sender_id];
	}

	public function get_avatar()
	{
		$sender_id = $this->get_data('sender_id');
		if (!$sender_id) {
			$sender_id = $this->get_data('PR_N_sender_id');
		}
		return $this->user_loader->get_avatar($sender_id, false, true);
	}

	public function get_title()
	{
		$extra = $this->get_data('extra_data');

		$sender_id  = 0;
		$post_title = '';
		$icon       = '';

		if (!empty($extra))
		{
			$sender_id  = $this->get_data('sender_id');
			$post_title = isset($extra['post_title']) ? $extra['post_title'] : '';
			$icon       = isset($extra['icon']) ? $extra['icon'] : '';
		}
		else
		{
			$sender_id  = $this->get_data('PR_N_sender_id');
			$post_title = $this->get_data('PR_N_post_title');
			$icon       = $this->get_data('PR_N_icon');
		}

		$username = $this->user_loader->get_username($sender_id, 'no_profile');

		// --- NEW LOGIC: Retrieve Emoji from DB ---
		$emoji = '';

		$filename = basename($icon);

		if ($filename)
		{
			$sql_array = [
				'SELECT' => 'icon_emoji',
				'FROM'   => [
					$this->table_prefix . 'sebo_postreact_icon' => 'i',
				],
				'WHERE'  => "icon_url LIKE '%" . $this->db->sql_escape($filename) . "'",
			];

			$sql = $this->db->sql_build_query('SELECT', $sql_array);

			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				$emoji = html_entity_decode($row['icon_emoji']);
			}
		}

		$hidden_emoji = '';
		if ($emoji !== '')
		{
			$hidden_emoji = '<span style="display:none;">' . $emoji . ' </span>';
		}

		return $hidden_emoji . '<img src="' . $icon . '" style="width:32px !important;height:32px !important;"> '
			. $this->language->lang('SEBO_POSTREACT_NOTIFICATION', $username, $post_title);
	}

	public function get_url()
	{
		$post_id = $this->get_data('item_id');
		if (!$post_id)
		{
			$post_id = $this->get_data('PR_N_post_id');
		}
		return append_sid($this->phpbb_root_path . 'viewtopic.' . $this->php_ext, "p={$post_id}#p{$post_id}");
	}

	public function get_email_template()
	{
		return '@sebo_postreact/postreact_notify';
	}

	public function get_email_template_variables()
	{
		$extra = $this->get_data('extra_data');
		$sender_id = 0;
		$post_title = '';

		if (!empty($extra)) {
			$sender_id  = $this->get_data('sender_id');
			$post_title = isset($extra['post_title']) ? $extra['post_title'] : '';
		} else {
			$sender_id  = $this->get_data('PR_N_sender_id');
			$post_title = $this->get_data('PR_N_post_title');
		}

		$username = htmlspecialchars_decode($this->user_loader->get_username($this->get_data('sender_id'), 'username'));
		$url = generate_board_url() . $this->get_url();
		$sitename = isset($this->config['sitename']) ? $this->config['sitename'] : 'Forum';

		return [
			'AUTHOR_NAME'           => $username,
			'POST_SUBJECT'          => $post_title,
			'TOPIC_TITLE'           => $post_title,
			'U_TOPIC'               => $url,
			'U_NEW_POST'            => $url,
			'U_STOP_WATCHING_TOPIC' => $url,
			'U_FORUM'               => generate_board_url(),
			'SITENAME'              => $sitename,
		];
	}

	public function create_insert_array($data, $pre_create_data = [])
	{
		$this->set_data('item_id', $data['PR_N_post_id']);
		$this->set_data('user_id', $data['PR_N_user_id']);
		$this->set_data('item_parent_id', $data['PR_N_topic_id']);
		$this->set_data('sender_id', $data['PR_N_sender_id']);

		$this->set_data('extra_data', [
			'username'   => isset($data['PR_N_username']) ? $data['PR_N_username'] : '',
			'post_title' => isset($data['PR_N_post_title']) ? $data['PR_N_post_title'] : '',
			'icon'       => isset($data['PR_N_icon']) ? $data['PR_N_icon'] : '',
		]);

		parent::create_insert_array($data, $pre_create_data);
	}
}
