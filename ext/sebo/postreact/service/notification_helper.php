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

	const NOTIFICATION_TYPE = 'sebo.postreact.notification.type.postreact_notification';

	/**
	 * Per-reaction notifications use even item ids and "once per post"
	 * notifications use odd ones, so the two id spaces can never overlap.
	 */
	public static function get_reaction_item_id($postreact_id)
	{
		return ((int) $postreact_id) * 2;
	}

	public static function get_post_item_id($post_id)
	{
		return ((int) $post_id) * 2 + 1;
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

		// Don't notify users about their own reactions
		if ($user_id_logged === (int) $row_post['poster_id_clean'])
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

		$notification_item_id = ($notify_mode === 1)
			? self::get_post_item_id($post_id)
			: self::get_reaction_item_id($postreact_id);

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
		// Per-reaction notification (no-op if the author uses "once per post")
		if ($postreact_id > 0)
		{
			$this->notification_manager->delete_notifications(
				self::NOTIFICATION_TYPE,
				self::get_reaction_item_id($postreact_id)
			);
		}

		// "Once per post" notification: drop it only when no reaction is left
		if (!$this->post_has_reactions($post_id))
		{
			$this->notification_manager->delete_notifications(
				self::NOTIFICATION_TYPE,
				self::get_post_item_id($post_id)
			);
		}
	}

	public function add_notification($notification_data, $notification_type_name = 'sebo.postreact.notification.type.postreact_notification')
	{
		$this->notification_manager->add_notifications($notification_type_name, $notification_data);
	}

	/**
	 * Whether at least one reaction row still exists for the post.
	 */
	protected function post_has_reactions($post_id)
	{
		$sql = 'SELECT COUNT(*) AS total
			FROM ' . $this->table_prefix . 'sebo_postreact_table
			WHERE post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total > 0;
	}

	/**
	 * Delete every notification (per-reaction and once-per-post) related
	 * to the given posts. Must run BEFORE the reaction rows are deleted,
	 * because the reaction ids are read from the table.
	 *
	 * @param array $post_ids
	 */
	public function delete_for_posts(array $post_ids)
	{
		$post_ids = array_map('intval', $post_ids);
		if (empty($post_ids))
		{
			return;
		}

		$item_ids = [];
		foreach ($post_ids as $post_id)
		{
			$item_ids[] = self::get_post_item_id($post_id);
		}

		$sql = 'SELECT postreact_id
			FROM ' . $this->table_prefix . 'sebo_postreact_table
			WHERE ' . $this->db->sql_in_set('post_id', $post_ids);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$item_ids[] = self::get_reaction_item_id($row['postreact_id']);
		}
		$this->db->sql_freeresult($result);

		foreach (array_chunk($item_ids, 500) as $chunk)
		{
			$this->notification_manager->delete_notifications(self::NOTIFICATION_TYPE, $chunk);
		}
	}

	/**
	 * Delete the notifications of reactions whose post no longer exists
	 * (used by the ACP "synchronize" action, before the orphan rows are removed).
	 */
	public function delete_orphans()
	{
		$sql = 'SELECT DISTINCT r.post_id
			FROM ' . $this->table_prefix . 'sebo_postreact_table r
			LEFT JOIN ' . POSTS_TABLE . ' p
				ON r.post_id = p.post_id
			WHERE p.post_id IS NULL';
		$result = $this->db->sql_query($sql);
		$post_ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$post_ids[] = (int) $row['post_id'];
		}
		$this->db->sql_freeresult($result);

		$this->delete_for_posts($post_ids);
	}

	/**
	 * Delete every notification of this type (used by the ACP purge).
	 * Direct SQL on purpose: the manager's purge_notifications() would also
	 * drop the users' subscription settings for this type.
	 */
	public function delete_all()
	{
		$sql = 'DELETE FROM ' . NOTIFICATIONS_TABLE . '
			WHERE notification_type_id IN (
				SELECT notification_type_id
				FROM ' . NOTIFICATION_TYPES_TABLE . "
				WHERE notification_type_name = '" . $this->db->sql_escape(self::NOTIFICATION_TYPE) . "'
			)";
		$this->db->sql_query($sql);
	}
}
