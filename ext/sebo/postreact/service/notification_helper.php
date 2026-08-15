<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\service;

/**
 * Handles creation/removal of PostReaction notifications.
 * Extracted from the old main_listener so the react_controller does not
 * need to depend on an event listener to trigger notifications.
 *
 * Notifications are tracked per-reaction via the reaction table's
 * auto-increment postreact_id, unless the post author opted into
 * "notify me once per post" (user_postreact_notify_mode = 1), in which
 * case post_id is used instead so phpBB naturally deduplicates them.
 */
class notification_helper
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	protected $table_prefix;
	protected $user;
	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		$table_prefix,
		$user,
		\phpbb\notification\manager $notification_manager
	)
	{
		$this->db = $db;
		$this->table_prefix = $table_prefix;
		$this->user = $user;
		$this->notification_manager = $notification_manager;
	}

	/**
	 * @param int    $post_id
	 * @param int    $topic_id
	 * @param int    $icon_id
	 * @param string $action       'add' or 'remove'
	 * @param int    $postreact_id Row id from sebo_postreact_table for this
	 *                             specific reaction (0 if unknown)
	 */
	public function handle_postreact_notification($post_id, $topic_id, $icon_id, $action, $postreact_id = 0)
	{
		switch ($action)
		{
			case 'remove':
				$this->remove_postreact_notification($post_id, $postreact_id);
				break;

			case 'add':
				$this->add_postreact_notification($post_id, $topic_id, $icon_id, $postreact_id);
				break;

			default:
				break;
		}
	}

	public function add_postreact_notification($post_id, $topic_id, $icon_id, $postreact_id = 0)
	{
		$user_id_logged = (int) $this->user->data['user_id'];

		// get post infos, including the author's notification preference
		$sql_array = [
			'SELECT'    => 'p.poster_id AS poster_id_clean, p.post_subject AS post_post_title, p.post_id, u.username_clean AS poster_name_clean, u.user_id, u.user_colour AS poster_user_colour, u.user_postreact_notify_mode',
			'FROM'      => [$this->table_prefix . 'posts' => 'p'],
			'LEFT_JOIN' => [
				[
					'FROM' => [USERS_TABLE => 'u'],
					'ON'   => 'p.poster_id = u.user_id',
				],
			],
			'WHERE'     => 'p.post_id = ' . (int) $post_id,
		];
		$sql_post = $this->db->sql_build_query('SELECT', $sql_array);
		$result_post = $this->db->sql_query($sql_post);
		$row_post = $this->db->sql_fetchrow($result_post);
		$this->db->sql_freeresult($result_post);

		if (!$row_post)
		{
			return;
		}

		// get icon infos
		$sql_array = [
			'SELECT' => '*',
			'FROM'   => [$this->table_prefix . 'sebo_postreact_icon' => ''],
			'WHERE'  => 'icon_id = ' . (int) $icon_id,
		];
		$sql_icon = $this->db->sql_build_query('SELECT', $sql_array);
		$result_icon = $this->db->sql_query($sql_icon);
		$row_icon = $this->db->sql_fetchrow($result_icon);
		$this->db->sql_freeresult($result_icon);

		if (!$row_icon)
		{
			return;
		}

		// Check the post author's notification preference:
		// 0 = every reaction (default), 1 = single notification per post
		$notify_mode = isset($row_post['user_postreact_notify_mode']) ? (int) $row_post['user_postreact_notify_mode'] : 0;

		// In single mode, use post_id as the item_id so phpBB deduplicates
		// per post. In "every reaction" mode, use the actual postreact_id
		// so each reaction gets (and can later remove) its own notification.
		$notification_item_id = ($notify_mode === 1) ? (int) $post_id : (int) $postreact_id;

		// make array
		$pr_notification_data = [
			'postreact_id'    => $notification_item_id,
			'PR_N_item_id'    => (int) $post_id,
			'PR_N_username'   => $row_post['poster_name_clean'],
			'PR_N_post_title' => $row_post['post_post_title'],
			'PR_N_user_id'    => (int) $row_post['poster_id_clean'],
			'PR_N_sender_id'  => $user_id_logged,
			'PR_N_post_id'    => (int) $post_id,
			'PR_N_topic_id'   => (int) $topic_id,
			'PR_N_icon'       => $row_icon['icon_url']
		];

		$this->add_notification($pr_notification_data);
	}

	public function remove_postreact_notification($post_id, $postreact_id = 0)
	{
		// Look up the post author's notification preference: the controller
		// only knows the reactor's identity, not the post owner whose
		// notification is being managed.
		$sql_array = [
			'SELECT'    => 'u.user_postreact_notify_mode',
			'FROM'      => [$this->table_prefix . 'posts' => 'p'],
			'LEFT_JOIN' => [
				[
					'FROM' => [USERS_TABLE => 'u'],
					'ON'   => 'p.poster_id = u.user_id',
				],
			],
			'WHERE'     => 'p.post_id = ' . (int) $post_id,
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$notify_mode = ($row && isset($row['user_postreact_notify_mode'])) ? (int) $row['user_postreact_notify_mode'] : 0;

		if ($notify_mode === 1)
		{
			// Single mode: the notification is keyed by post_id and other
			// reactions on the same post may still exist, so it is not
			// deleted here (it will simply go unread/expire naturally).
			return;
		}

		if ($postreact_id <= 0)
		{
			// No specific reaction id available (e.g. an older caller);
			// nothing safe to delete.
			return;
		}

		// Every mode: delete the specific notification by postreact_id
		$this->notification_manager->delete_notifications(
			'sebo.postreact.notification.type.postreact_notification',
			(int) $postreact_id
		);
	}

	public function add_notification($notification_data, $notification_type_name = 'sebo.postreact.notification.type.postreact_notification')
	{
		$this->notification_manager->add_notifications($notification_type_name, $notification_data);
	}
}
