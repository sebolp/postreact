<?php
	namespace sebo\postreact\controller;

	use phpbb\json_response;

	class react_controller
	{
	protected $db;
	protected $user;
	protected $request;
	protected $table_prefix;
	protected $php_ext;
	protected $notification_manager;
	protected $main_listener;

	public function __construct
	(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user,
		\phpbb\request\request_interface $request,
		$table_prefix,
		$php_ext,
		\phpbb\notification\manager $notification_manager,
		\sebo\postreact\event\main_listener $main_listener
	)
	{
		$this->db = $db;
		$this->user = $user;
		$this->request = $request;
		$this->table_prefix = $table_prefix;
		$this->php_ext = $php_ext;
		$this->notification_manager = $notification_manager;
		$this->main_listener = $main_listener;
	}

	// PRIVATE
	// *******
	private function send_basic_info()
	{
		global $phpbb_root_path, $phpEx;

		$info1 = $phpbb_root_path;
		$info2 = $phpEx;
		$this->send_json_response(true, $info1, $info2);
	}

	private function check_existing_reaction($user_id, $post_id)
	{
		$sql_array = [
			'SELECT'	=> 'COUNT(*) AS total_reactions',
			'FROM'	  => [$this->table_prefix . 'sebo_postreact_table' => ''],
			'WHERE'	 => 'user_id = ' . (int) $user_id . ' AND post_id = ' . (int) $post_id,
		];

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return (int) $row['total_reactions'];
	}

	private function remove_reaction($user_id, $post_id, $topic_id, $icon_id)
	{
		// get existing icon_id and react_time
		$sql = 'SELECT icon_id, react_time FROM ' . $this->table_prefix . 'sebo_postreact_table
				WHERE user_id = ' . (int) $user_id . '
				AND post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);
		$existing_reaction = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$existing_reaction)
		{
			$this->send_json_response(false, 'No reaction to remove');
			return;
		}

		$removed_icon_id = $existing_reaction['icon_id'];
		$react_time = $existing_reaction['react_time'];

		// Remove all reactions
		$sql = 'DELETE FROM ' . $this->table_prefix . 'sebo_postreact_table
				WHERE user_id = ' . (int) $user_id . '
				AND post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);

		if ($result)
		{
			// Passa tutto al main_listener per gestire le notifiche
			$this->main_listener->handle_postreact_notification($post_id, $topic_id, $icon_id, 'remove');

			// Get updated data
			$reaction_data = $this->get_reaction_data($post_id);
			$new_count = isset($reaction_data['counts'][$removed_icon_id]) ? $reaction_data['counts'][$removed_icon_id] : 0;

			$this->send_json_response(true, $this->user->lang('DELETED_VALUE'), [
				'action'		=> 'removed',
				'new_count'	 => $new_count,
				'icon_id'	   => $removed_icon_id,
				'post_id'	   => $post_id,
				'reaction_data' => $reaction_data,
				'user_data'	 => array(
					'username'	  => $this->user->data['username'],
					'user_colour'   => $this->user->data['user_colour']
				)
			]);
		}
		else
		{
			$this->send_json_response(false, 'Error removing reaction');
		}
	}

	private function add_reaction($user_id, $post_id, $topic_id, $icon_id)
	{
		$time = time();
		$data = [
			'postreact_id'  => null,
			'topic_id'	  => (int) $topic_id,
			'post_id'	   => (int) $post_id,
			'user_id'	   => (int) $user_id,
			'icon_id'	   => (int) $icon_id,
			'react_time'	=> (int) $time,
		];

		// sql query di phpbb
		$sql = 'INSERT INTO ' . $this->table_prefix . 'sebo_postreact_table ' .
			   $this->db->sql_build_array('INSERT', $data);
		$result = $this->db->sql_query($sql);

		if ($result)
		{
			// Add notify
			// *************************
			$this->main_listener->handle_postreact_notification($post_id, $topic_id, $icon_id, 'add');

			$reaction_data = $this->get_reaction_data($post_id);
			$icon_data = $this->get_icon_data($icon_id);
			$new_count = isset($reaction_data['counts'][$icon_id]) ? $reaction_data['counts'][$icon_id] : 1;

			$this->send_json_response(true, $this->user->lang('INSERTED_VALUE'), [
				'action'		=> 'added',
				'post_id'	   => $post_id,
				'icon_id'	   => $icon_id,
				'new_count'	 => $new_count,
				'icon_url'	  => $icon_data['icon_url'],
				'icon_width'	=> $icon_data['icon_width'],
				'icon_height'   => $icon_data['icon_height'],
				'icon_alt'	  => $icon_data['icon_alt'],
				'reaction_data' => $reaction_data,
				'reacted_language'  => $this->user->lang('ALREADY_REACTED'),
				'user_data' => array(
					'username'	  => $this->user->data['username'],
					'user_colour'   => $this->user->data['user_colour']
				)
			]);
		}
		else
		{
			$this->send_json_response(false, $this->user->lang('NOT_INSERTED_VALUE'));
		}
	}

	private function get_reaction_data($post_id)
	{
		// Get reaction count
		$sql_array = [
			'SELECT'	=> 'r.icon_id, COUNT(*) as count',
			'FROM'	  => [$this->table_prefix . 'sebo_postreact_table' => 'r'],
			'WHERE'	 => 'r.post_id = ' . (int) $post_id,
			'GROUP_BY'  => 'r.icon_id'
		];

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		$counts = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$counts[$row['icon_id']] = (int) $row['count'];
		}
		$this->db->sql_freeresult($result);

		// get user detail by icon
		$user_details = [];
		foreach ($counts as $icon_id => $count)
		{
			$sql_array = [
				'SELECT'	=> 'u.username, u.user_colour',
				'FROM'	  => [$this->table_prefix . 'sebo_postreact_table' => 'r'],
				'LEFT_JOIN' => [
					[
						'FROM'  => [$this->table_prefix . 'users' => 'u'],
						'ON'	=> 'r.user_id = u.user_id'
					]
				],
				'WHERE'	 => 'r.post_id = ' . (int) $post_id . ' AND r.icon_id = ' . (int) $icon_id,
				'ORDER_BY'  => 'r.react_time ASC'
			];

			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query($sql);

			$users = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$users[] = [
					'username'	  => $row['username'],
					'user_colour'   => $row['user_colour']
				];
			}
			$this->db->sql_freeresult($result);

			$user_details[$icon_id] = $users;
		}

		return [
			'counts' => $counts,
			'user_details' => $user_details
		];
	}

	private function get_icon_data($icon_id)
	{
		$sql_array = [
			'SELECT'	=> '*',
			'FROM'	  => [$this->table_prefix . 'sebo_postreact_icon' => ''],
			'WHERE'	 => 'icon_id = ' . (int) $icon_id,
		];

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	private function send_json_response($success, $message, $data = [])
	{
		$response_data = array_merge([
			'success' => $success,
			'message' => $message
		], $data);

		$json_response = new json_response();
		$json_response->send($response_data);
	}

	// public
	// ******

	public function handle()
	{
		if (!$this->request->is_ajax())
		{
			trigger_error('NO_DIRECT_ACCESS');
		}

		$post_id = $this->request->variable('post_id', 0);
		$topic_id = $this->request->variable('topic_id', 0);
		$icon_id = $this->request->variable('icon_id', 0);
		$user_id = (int) $this->user->data['user_id'];

		// already reacted?
		$existing_reaction = $this->check_existing_reaction($user_id, $post_id);

		if ($existing_reaction > 0)
		{
			// remove
			$this->remove_reaction($user_id, $post_id, $topic_id, $icon_id);
		}
		else
		{
			// add
			$this->add_reaction($user_id, $post_id, $topic_id, $icon_id);
		}
	}
}
