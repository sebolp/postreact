<?php
/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class react_controller
{
	protected $db;
	protected $user;
	protected $language;
	protected $request;
	protected $table_prefix;
	protected $php_ext;
	protected $notification_manager;
	protected $main_listener;
	protected $config;

	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		$user,
		\phpbb\language\language $language,
		\phpbb\request\request_interface $request,
		$table_prefix,
		$php_ext,
		\phpbb\notification\manager $notification_manager,
		\sebo\postreact\event\main_listener $main_listener,
		\phpbb\config\config $config
	)
	{
		$this->db = $db;
		$this->user = $user;
		$this->language = $language;
		$this->request = $request;
		$this->table_prefix = $table_prefix;
		$this->php_ext = $php_ext;
		$this->notification_manager = $notification_manager;
		$this->main_listener = $main_listener;
		$this->config = $config;
	}

	private function check_existing_reaction($user_id, $post_id)
	{
		$sql_array = [
			'SELECT'	=> 'COUNT(*) AS total_reactions',
			'FROM'		=> [$this->table_prefix . 'sebo_postreact_table' => ''],
			'WHERE'		=> 'user_id = ' . (int) $user_id . ' AND post_id = ' . (int) $post_id,
		];

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return (int) $row['total_reactions'];
	}

	private function remove_reaction($user_id, $post_id, $topic_id, $icon_id)
	{
		$sql = 'SELECT icon_id, react_time FROM ' . $this->table_prefix . 'sebo_postreact_table
				WHERE user_id = ' . (int) $user_id . '
				AND post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);
		$existing_reaction = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$existing_reaction)
		{
			return $this->send_json_response(false, 'No reaction to remove');
		}

		$removed_icon_id = $existing_reaction['icon_id'];

		$sql = 'DELETE FROM ' . $this->table_prefix . 'sebo_postreact_table
				WHERE user_id = ' . (int) $user_id . '
				AND post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);

		if ($result)
		{
			$this->main_listener->handle_postreact_notification($post_id, $topic_id, $icon_id, 'remove');

			$reaction_data = $this->get_reaction_data($post_id);
			$new_count = isset($reaction_data['counts'][$removed_icon_id]) ? $reaction_data['counts'][$removed_icon_id] : 0;

			return $this->send_json_response(true, $this->language->lang('DELETED_VALUE'), [
				'action'		=> 'removed',
				'new_count'		=> $new_count,
				'icon_id'		=> $removed_icon_id,
				'post_id'		=> $post_id,
				'reaction_data'	=> $reaction_data,
				'user_data'		=> [
					'username'		=> $this->user->data['username'],
					'user_colour'	=> $this->user->data['user_colour'],
				],
			]);
		}
		else
		{
			return $this->send_json_response(false, 'Error removing reaction');
		}
	}

	private function add_reaction($user_id, $post_id, $topic_id, $icon_id)
	{
		$time = time();
		$data = [
			'postreact_id'	=> null,
			'topic_id'		=> (int) $topic_id,
			'post_id'		=> (int) $post_id,
			'user_id'		=> (int) $user_id,
			'icon_id'		=> (int) $icon_id,
			'react_time'	=> (int) $time,
		];

		$sql = 'INSERT INTO ' . $this->table_prefix . 'sebo_postreact_table ' .
			   $this->db->sql_build_array('INSERT', $data);
		$result = $this->db->sql_query($sql);

		if ($result)
		{
			$this->main_listener->handle_postreact_notification($post_id, $topic_id, $icon_id, 'add');

			$reaction_data = $this->get_reaction_data($post_id);
			$icon_data = $this->get_icon_data($icon_id);
			$new_count = isset($reaction_data['counts'][$icon_id]) ? $reaction_data['counts'][$icon_id] : 1;

			return $this->send_json_response(true, $this->language->lang('INSERTED_VALUE'), [
				'action'			=> 'added',
				'post_id'			=> $post_id,
				'icon_id'			=> $icon_id,
				'new_count'			=> $new_count,
				'icon_url'			=> $icon_data['icon_url'],
				'icon_width'		=> $icon_data['icon_width'],
				'icon_height'		=> $icon_data['icon_height'],
				'icon_alt'			=> $icon_data['icon_alt'],
				'reaction_data'		=> $reaction_data,
				'reacted_language'	=> $this->language->lang('ALREADY_REACTED'),
				'user_data'			=> [
					'username'		=> $this->user->data['username'],
					'user_colour'	=> $this->user->data['user_colour'],
				],
			]);
		}
		else
		{
			return $this->send_json_response(false, $this->language->lang('NOT_INSERTED_VALUE'));
		}
	}

	private function get_reaction_data($post_id)
	{
		$sql_array = [
			'SELECT'	=> 'r.icon_id, COUNT(*) as count',
			'FROM'		=> [$this->table_prefix . 'sebo_postreact_table' => 'r'],
			'WHERE'		=> 'r.post_id = ' . (int) $post_id,
			'GROUP_BY'	=> 'r.icon_id',
		];

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		$counts = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$counts[$row['icon_id']] = (int) $row['count'];
		}
		$this->db->sql_freeresult($result);

		$user_details = [];
		foreach ($counts as $icon_id => $count)
		{
			$sql_array = [
				'SELECT'	=> 'u.username, u.user_colour',
				'FROM'		=> [$this->table_prefix . 'sebo_postreact_table' => 'r'],
				'LEFT_JOIN'	=> [
					[
						'FROM'	=> [$this->table_prefix . 'users' => 'u'],
						'ON'	=> 'r.user_id = u.user_id',
					],
				],
				'WHERE'		=> 'r.post_id = ' . (int) $post_id . ' AND r.icon_id = ' . (int) $icon_id,
				'ORDER_BY'	=> 'r.react_time ASC',
			];

			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query($sql);

			$users = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$users[] = [
					'username'		=> $row['username'],
					'user_colour'	=> $row['user_colour'],
				];
			}
			$this->db->sql_freeresult($result);

			$user_details[$icon_id] = $users;
		}

		return [
			'counts'		=> $counts,
			'user_details'	=> $user_details,
		];
	}

	private function get_icon_data($icon_id)
	{
		$sql_array = [
			'SELECT'	=> '*',
			'FROM'		=> [$this->table_prefix . 'sebo_postreact_icon' => ''],
			'WHERE'		=> 'icon_id = ' . (int) $icon_id,
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
			'success'	=> $success,
			'message'	=> $message,
		], $data);

		return new JsonResponse($response_data);
	}

	public function handle()
	{
		if (!$this->request->is_ajax())
		{
			throw new \phpbb\exception\http_exception(403, 'NO_DIRECT_ACCESS');
		}

		// Deny anonymous users
		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			return $this->send_json_response(false, $this->language->lang('LOGIN_REQUIRED'));
		}

		$post_id	= $this->request->variable('post_id', 0);
		$topic_id	= $this->request->variable('topic_id', 0);
		$icon_id	= $this->request->variable('icon_id', 0);
		$user_id	= (int) $this->user->data['user_id'];

		$existing_reaction = $this->check_existing_reaction($user_id, $post_id);

		if ($existing_reaction > 0)
		{
			return $this->remove_reaction($user_id, $post_id, $topic_id, $icon_id);
		}
		else
		{
			$sql_array = [
				'SELECT'	=> 'poster_id',
				'FROM'		=> [$this->table_prefix . 'posts' => ''],
				'WHERE'		=> 'post_id = ' . (int) $post_id,
			];

			$sql_check_poster = $this->db->sql_build_query('SELECT', $sql_array);
			$result_check_poster = $this->db->sql_query($sql_check_poster);
			$row_check_poster = $this->db->sql_fetchrow($result_check_poster);
			$this->db->sql_freeresult($result_check_poster);

			if ($row_check_poster)
			{
				$config_self_react = isset($this->config['sebo_postreact_self_react']) ? (int) $this->config['sebo_postreact_self_react'] : 0;
				if ($config_self_react === 1 && (int) $row_check_poster['poster_id'] === $user_id)
				{
					return $this->send_json_response(false, $this->language->lang('CANNOT_SELF_REACT'));
				}
			}

			return $this->add_reaction($user_id, $post_id, $topic_id, $icon_id);
		}
	}
}
