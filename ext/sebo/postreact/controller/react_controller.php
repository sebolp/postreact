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
use sebo\postreact\service\notification_helper;
use sebo\postreact\service\reaction_count_cache;

class react_controller
{
	protected $db;
	protected $user;
	protected $language;
	protected $request;
	protected $table_prefix;
	protected $php_ext;
	protected $notification_manager;
	/** @var notification_helper */
	protected $notification_helper;
	protected $config;
	/** @var reaction_count_cache */
	protected $reaction_count_cache;
	/** @var \phpbb\auth\auth */
	protected $auth;

	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		$user,
		\phpbb\language\language $language,
		\phpbb\request\request_interface $request,
		$table_prefix,
		$php_ext,
		\phpbb\notification\manager $notification_manager,
		notification_helper $notification_helper,
		\phpbb\config\config $config,
		reaction_count_cache $reaction_count_cache,
		\phpbb\auth\auth $auth
	)
	{
		$this->db = $db;
		$this->user = $user;
		$this->language = $language;
		$this->request = $request;
		$this->table_prefix = $table_prefix;
		$this->php_ext = $php_ext;
		$this->notification_manager = $notification_manager;
		$this->notification_helper = $notification_helper;
		$this->config = $config;
		$this->reaction_count_cache = $reaction_count_cache;
		$this->auth = $auth;
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
		$sql = 'SELECT postreact_id, icon_id, react_time FROM ' . $this->table_prefix . 'sebo_postreact_table
				WHERE user_id = ' . (int) $user_id . '
				AND post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);
		$existing_reaction = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$existing_reaction)
		{
			return $this->send_json_response(false, 'PR_NO_REACTION_TO_REMOVE');
		}

		$removed_icon_id = $existing_reaction['icon_id'];
		$removed_postreact_id = (int) $existing_reaction['postreact_id'];

		$sql = 'DELETE FROM ' . $this->table_prefix . 'sebo_postreact_table
				WHERE user_id = ' . (int) $user_id . '
				AND post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);

		if ($result)
		{
			$this->notification_helper->handle_postreact_notification($post_id, $topic_id, $icon_id, 'remove', $removed_postreact_id);
			$this->reaction_count_cache->purge_topic_counts($topic_id);

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
			return $this->send_json_response(false, 'PR_ERROR_REMOVING_REACTION');
		}
	}

	private function add_reaction($user_id, $post_id, $topic_id, $icon_id)
	{
		// Reject icon_ids that don't exist or are disabled - previously
		// any integer was accepted and stored as-is
		$icon_data = $this->get_icon_data($icon_id);
		if (!$icon_data || (int) $icon_data['status'] !== 1)
		{
			return $this->send_json_response(false, $this->language->lang('NOT_INSERTED_VALUE'));
		}

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
			$postreact_id = (int) $this->db->sql_nextid();

			$this->notification_helper->handle_postreact_notification($post_id, $topic_id, $icon_id, 'add', $postreact_id);
			$this->reaction_count_cache->purge_topic_counts($topic_id);

			$reaction_data = $this->get_reaction_data($post_id);
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

		/* Token check */
		$token = $this->request->variable('token', '');
		if (!check_link_hash($token, 'postreact_ajax'))
		{
			throw new \phpbb\exception\http_exception(403, 'FORM_INVALID');
		}

		// Deny anonymous users
		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			return $this->send_json_response(false, $this->language->lang('LOGIN_TO_REACT'));
		}

		// Enforce the "can react" permission server-side: the template
		// hides the button for users without it, but the AJAX route itself
		// must not trust that
		if (!$this->auth->acl_get('u_new_sebo_postreact'))
		{
			return $this->send_json_response(false, $this->language->lang('LOGIN_TO_REACT'));
		}

		$post_id	= $this->request->variable('post_id', 0);
		$topic_id	= $this->request->variable('topic_id', 0);
		$icon_id	= $this->request->variable('icon_id', 0);
		$user_id	= (int) $this->user->data['user_id'];

		// check forum access per user
		$sql_array = [
			'SELECT'	=> 'p.poster_id, p.forum_id, p.post_approved',
			'FROM'		=> [$this->table_prefix . 'posts' => 'p'],
			'WHERE'		=> 'p.post_id = ' . (int) $post_id,
		];

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$post_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		// post must exist
		if (!$post_data)
		{
			return $this->send_json_response(false, 'NO_POST');
		}

		$forum_id = (int) $post_data['forum_id'];
		$post_approved = (int) $post_data['post_approved'];

		// check f_read
		if (!$this->auth->acl_get('f_read', $forum_id))
		{
			return $this->send_json_response(false, 'NO_VIEW_FORUM');
		}

		// if not approved user must have m_approve
		if ($post_approved === 0 && !$this->auth->acl_get('m_approve', $forum_id))
		{
			return $this->send_json_response(false, 'NO_POST');
		}

		$existing_reaction = $this->check_existing_reaction($user_id, $post_id);

		if ($existing_reaction > 0)
		{
			return $this->remove_reaction($user_id, $post_id, $topic_id, $icon_id);
		}
		else
		{
			$config_self_react = isset($this->config['sebo_postreact_self_react']) ? (int) $this->config['sebo_postreact_self_react'] : 0;
			if ($config_self_react === 1 && (int) $post_data['poster_id'] === $user_id)
			{
				return $this->send_json_response(false, $this->language->lang('CANNOT_SELF_REACT'));
			}

			return $this->add_reaction($user_id, $post_id, $topic_id, $icon_id);
		}
	}
}
