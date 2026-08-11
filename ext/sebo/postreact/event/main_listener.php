<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\event;

/**
 * @ignore
 */

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * PostReaction Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'						   => 'load_language_on_setup',
			'core.viewtopic_assign_template_vars_before' => 'preload_icons',
			'core.viewtopic_get_post_data'             => 'preload_reactions',
			'core.viewtopic_modify_post_row'            => 'assign_to_template',
			'core.viewforum_modify_page_title'          => 'preload_icons',
			'core.viewforum_modify_topicrow'			=> 'viewforum_edit',
			'core.search_modify_tpl_ary'				=> 'search_edit',
			'core.search_modify_rowset'                 => 'preload_search_reactions',
			'core.permissions'						  => 'add_permissions',
			'core.memberlist_view_profile'			  => 'edit_view_profile',
			'core.memberlist_modify_view_profile_template_vars' => 'assign_edit_view_profile',
			'core.search_modify_param_after'        => 'search_user_reactions_title',
			'core.search_backend_search_after'      => 'search_user_reactions',
		];
	}
	/* @var \phpbb\language\language */
	protected $language;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	/** @var */
	protected $user;
	/** @var \phpbb\request\request */
	protected $request;
	/** @var \phpbb\template\template */
	protected $template;
	protected $table_prefix;
	/** @var auth */
	protected $auth;
	/** @var \phpbb\notification\manager */
	protected $notification_manager;
	/** @var php_ext */
	protected $php_ext;
	/** @var profile */
	protected $profile_data;
	/** @var config */
	protected $config;
	/** @var string phpBB root path */
	protected $phpbb_root_path;
	/** @var icons */
	protected $icons_cache = null;
	protected $reactions_loaded = false;
	protected $reactions_cache = [];
	protected $users_cache = [];
	protected $my_reactions_cache = [];
	protected $cache;
	protected $topic_counts_cache = [];

	const CACHE_PREFIX = '_sebo_postreact_topic_counts_';
	const CACHE_REGISTRY_KEY = '_sebo_postreact_cached_topics';
	const CACHE_TTL = 300;
	/**
	 * Constructor
	 */
	public function __construct(
		\phpbb\language\language $language,
		\phpbb\db\driver\driver_interface $db,
		$user,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		$table_prefix,
		\phpbb\auth\auth $auth,
		\phpbb\notification\manager $notification_manager,
		$php_ext,
		\phpbb\config\config $config,
		$phpbb_root_path,
		\phpbb\cache\driver\driver_interface $cache
	)
	{
		$this->language = $language;
		$this->db	   = $db;
		$this->user	 = $user;
		$this->request  = $request;
		$this->template = $template;
		$this->table_prefix = $table_prefix;
		$this->auth = $auth;
		$this->notification_manager   = $notification_manager;
		$this->php_ext = $php_ext;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->cache = $cache;
	}
	/**
	 * Load common language files during user setup
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'sebo/postreact',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Handle custom search results injection
	 *
	 * @param \phpbb\event\data $event The event object
	 */
	public function search_user_reactions($event)
	{
		// We use request variable here because search_id might be reset in search.php logic
		if ($this->request->variable('search_id', '') !== 'sebo_user_reactions')
		{
			return;
		}

		$user_id = $this->request->variable('u', 0);
		$icon_id = $this->request->variable('icon_id', 0);
		$mode    = $this->request->variable('mode', 'sent');

		if (!$user_id)
		{
			return;
		}

		$sql_array = [
			'SELECT'    => 'pr.post_id',
			'FROM'      => [
				$this->table_prefix . 'sebo_postreact_table' => 'pr',
			],
			'LEFT_JOIN' => [
				[
					'FROM'  => [$this->table_prefix . 'posts' => 'p'],
					'ON'    => 'pr.post_id = p.post_id',
				],
			],
			'WHERE'     => 'p.post_id IS NOT NULL',
			'ORDER_BY'  => 'p.post_time DESC',
		];

		if ($mode === 'received')
		{
			$sql_array['WHERE'] .= ' AND p.poster_id = ' . (int) $user_id;
		}
		else
		{
			$sql_array['WHERE'] .= ' AND pr.user_id = ' . (int) $user_id;
		}

		if ($icon_id > 0)
		{
			$sql_array['WHERE'] .= ' AND pr.icon_id = ' . (int) $icon_id;
		}

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		$id_ary = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$id_ary[] = (int) $row['post_id'];
		}
		$this->db->sql_freeresult($result);

		if (!empty($id_ary))
		{
			// Inject IDs into the event data
			$event['id_ary'] = $id_ary;
			$event['total_match_count'] = count($id_ary);
			$event['show_results'] = 'posts';
		}
	}

	/**
	 * Set the page title for the custom search using the correct event
	 *
	 * @param \phpbb\event\data $event The event object
	 */
	public function search_user_reactions_title($event)
	{
		// Check the search_id from the event data
		if ($event['search_id'] !== 'sebo_user_reactions')
		{
			return;
		}

		$mode    = $this->request->variable('mode', 'sent');
		$user_id = $this->request->variable('u', 0);
		$icon_id = $this->request->variable('icon_id', 0);

		// Default username
		$username = $this->language->lang('GUEST');
		$icon_emoji = '';

		// 1. Retrieve Username
		if ($user_id > 0)
		{
			if ($user_id == $this->user->data['user_id'])
			{
				$username = $this->user->data['username'];
			}
			else
			{
				$sql = 'SELECT username
						FROM ' . USERS_TABLE . '
						WHERE user_id = ' . (int) $user_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row)
				{
					$username = $row['username'];
				}
			}
		}

		// 2. Retrieve Emoji
		if ($icon_id > 0)
		{
			$sql = 'SELECT icon_emoji
					FROM ' . $this->table_prefix . 'sebo_postreact_icon
					WHERE icon_id = ' . (int) $icon_id;
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				$icon_emoji = html_entity_decode($row['icon_emoji']);
			}
		}

		// 3. Set Title into the event variable
		// FIX: Variable order swapped to match language string: "PostReact %s given by %s"
		// 1st arg: Emoji, 2nd arg: Username
		if ($mode === 'received')
		{
			$title = $this->language->lang('SEARCH_USER_REACTIONS_RECEIVED', $icon_emoji, $username);
		}
		else
		{
			$title = $this->language->lang('SEARCH_USER_REACTIONS', $icon_emoji, $username);
		}

		// Assign title to the event
		$event['l_search_title'] = $title;

		// edit last navlink
		$this->template->alter_block_array('navlinks', [
			'FORUM_NAME'		=> $title,
			'U_VIEW_FORUM'		=> append_sid($this->phpbb_root_path . 'search.' . $this->php_ext, "search_id=sebo_user_reactions&u=$user_id&icon_id=$icon_id&mode=$mode"),
		], true, 'change');
	}

	public function handle_postreact_notification($post_id, $topic_id, $icon_id, $action)
	{
		switch ($action)
		{
			case 'remove':
				$this->remove_postreact_notification($post_id);
				break;

			case 'add':
				$this->add_postreact_notification($post_id, $topic_id, $icon_id);
				break;

			default:
				break;
		}
	}

	public function add_postreact_notification($post_id, $topic_id, $icon_id)
	{
		$user_id_logged = (int) $this->user->data['user_id'];

		// get post infos
		$sql_array = [
			'SELECT'    => 'p.poster_id AS poster_id_clean, p.post_subject AS post_post_title, p.post_id, u.username_clean AS poster_name_clean, u.user_id, u.user_colour AS poster_user_colour',
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

		// make array
		$pr_notification_data = [
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

	public function remove_postreact_notification($post_id)
	{
		$user_id_logged = (int) $this->user->data['user_id'];

		$this->notification_manager->delete_notifications(
			'sebo.postreact.notification.type.postreact_notification',
			(int) $post_id,
			false,
			$user_id_logged
		);
	}

	public function add_notification($notification_data, $notification_type_name = 'sebo.postreact.notification.type.postreact_notification')
	{
		$result = $this->notification_manager->add_notifications($notification_type_name, $notification_data);
	}

	/**
	 * Preload icons cache on page init events
	 */
	public function preload_icons($event)
	{
		$this->grab_icons();
	}

	public function grab_icons()
	{
		// ##
		// grab icons from cache
		if ($this->icons_cache !== null)
		{
			return $this->icons_cache;
		}

		// take the line corresponds to post_id
		$data_ico = [];
		$sql_array = [
			'SELECT'    => '*',
			'FROM'      => [$this->table_prefix . 'sebo_postreact_icon' => ''],
			'ORDER_BY'	=> 'icon_order ASC',
		];
		$sql_ico = $this->db->sql_build_query('SELECT', $sql_array);
		$result_ico = $this->db->sql_query($sql_ico);
		if ($result_ico)
		{
			while ($my_icons = $this->db->sql_fetchrow($result_ico))
			{
				$data_ico[] = $my_icons;
			}
			$this->db->sql_freeresult($result_ico);
		}
		$this->icons_cache = $data_ico;
		return $this->icons_cache;
	}
	/**
	 * Batch-load reactions for the whole page of posts (one query per data
	 * set instead of one per post).
	 *
	 * @param \phpbb\event\data $event The event object
	 */
	public function preload_reactions($event)
	{
		$post_list = $event['post_list'];
		if (!empty($post_list))
		{
			$this->load_reactions($post_list);
		}
	}

	/**
	 * Load reactions, reactor user details and the current user's own
	 * reactions for a set of post IDs into the per-request caches.
	 *
	 * @param array $post_ids
	 */
	protected function load_reactions(array $post_ids)
	{
		if ($this->reactions_loaded)
		{
			return;
		}
		$this->reactions_loaded = true;

		$post_ids = array_values(array_unique(array_filter(array_map('intval', $post_ids))));
		if (empty($post_ids))
		{
			return;
		}

		$in_set = $this->db->sql_in_set('post_id', $post_ids);

		// 1. All reactions for the page's posts (one query instead of one per post)
		$sql = 'SELECT *
			FROM ' . $this->table_prefix . 'sebo_postreact_table
			WHERE ' . $in_set . '
			ORDER BY postreact_id ASC';
		$result = $this->db->sql_query($sql);

		$user_ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->reactions_cache[(int) $row['post_id']][] = $row;
			$user_ids[(int) $row['user_id']] = true;
		}
		$this->db->sql_freeresult($result);

		// 2. Reactor user details, one query for the whole page
		if (!empty($user_ids))
		{
			$sql = 'SELECT user_id, group_id, username, user_colour
				FROM ' . USERS_TABLE . '
				WHERE ' . $this->db->sql_in_set('user_id', array_keys($user_ids));
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->users_cache[(int) $row['user_id']] = $row;
			}
			$this->db->sql_freeresult($result);
		}

		// 3. Which reactions the logged-in user has given. Skipped for
		// guests/bots (ANONYMOUS) — the original code ran this query for them
		// pointlessly.
		$user_id_logged = (int) $this->user->data['user_id'];
		if ($user_id_logged > ANONYMOUS)
		{
			$sql = 'SELECT post_id, icon_id
				FROM ' . $this->table_prefix . 'sebo_postreact_table
				WHERE user_id = ' . $user_id_logged . ' AND ' . $in_set;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->my_reactions_cache[(int) $row['post_id']][] = [
					'post_id' => (int) $row['post_id'],
					'icon_id' => (int) $row['icon_id'],
				];
			}
			$this->db->sql_freeresult($result);
		}
	}

	public function assign_to_template($event)
	{
		$postrow = $event['postrow'];
		$row = $event['row'];
		// ##
		//
		$my_pid = (int) $row['post_id'];
		$my_tid = (int) $row['topic_id'];

		// Batched load (page-scoped via core.viewtopic_get_post_data); lazy
		// single-post fallback covers any other caller of this event.
		if (!$this->reactions_loaded)
		{
			$this->load_reactions([$my_pid]);
		}

		// Reactions on this post, read from the page-wide cache
		$data = isset($this->reactions_cache[$my_pid]) ? $this->reactions_cache[$my_pid] : [];
		// total reaction count
		$total_match_count = count($data);
		// ##
		// count icon_id and users_ids
		$icon_counts = [];
		$user_ids_list = [];
		foreach ($data as $record)
		{
			$icon_id = $record['icon_id'];
			$user_id = $record['user_id'];
			$post_id = $record['post_id'];
			if (!isset($icon_counts[$icon_id]))
			{
				$icon_counts[$icon_id] = 0;
				$user_ids_list[$icon_id] = [];
			}
			$icon_counts[$icon_id]++;
			// Create an associative array for each entry with user_id and post_id
			$user_ids_list[$icon_id][] = [
				'user_id' => $user_id,
				'post_id' => $post_id
			];
		}
		// ##
		// search users_table information from user_id (preloaded for the page)
		$user_data_detailed = [];
		foreach ($user_ids_list as $icon_id => $entries)
		{
			foreach ($entries as $entry)
			{
				$user_id = $entry['user_id'];
				if (!isset($user_data_detailed[$user_id]) && isset($this->users_cache[$user_id]))
				{
					$user_data_detailed[$user_id] = $this->users_cache[$user_id];
				}
			}
		}
		// merge username, colour and group to user_id
		$user_ids_with_details = [];
		foreach ($user_ids_list as $icon_id => $entries)
		{
			foreach ($entries as $entry)
			{
				$user_id = $entry['user_id'];
				if (isset($user_data_detailed[$user_id]))
				{
					$user_ids_with_details[$icon_id][] = [
						'user_id' => $user_id,
						'username' => $user_data_detailed[$user_id]['username'],
						'group_id' => $user_data_detailed[$user_id]['group_id'],
						'user_colour' => $user_data_detailed[$user_id]['user_colour'],
						'post_id' => $entry['post_id'],  // Include post_id
					];
				}
			}
		}
		// ##
		// mark your choice (preloaded for the page; empty for guests/bots)
		$check = isset($this->my_reactions_cache[$my_pid]) ? $this->my_reactions_cache[$my_pid] : [];
		// ##
		// template
		$event['post_row'] = array_merge($event['post_row'], [
			'POSTREACT_FLOAT_CLASS' => ((int) $this->config['sebo_postreact_display_position'] === 1) ? 'left' : 'right',
			'PERM_W'		=> $this->auth->acl_get('u_new_sebo_postreact'),
			'PERM_R'		=> $this->auth->acl_get('u_new_sebo_postreact_view'),
			'N_REACTIONS'   => $total_match_count,
			'ICONS'			=> $this->grab_icons(),
			'MY_PID'		=> (int) $my_pid,
			'MY_TID'		=> (int) $my_tid,
			'ICON_COUNTS'   => $icon_counts,
			'ICON_CHECK'	=> $check,
			'REACTORS'	  	=> $user_ids_with_details,
			'SELF_REACT'	=> $this->config['sebo_postreact_self_react'],
			'BUTT_POSITION' => $this->config['sebo_postreact_butt_position'] ?? 'up',
		]);
	}

	/**
	 * Batch-load per-topic reaction counts for a set of topic IDs.
	 *
	 * Reads are served from the per-request cache first, then the phpBB cache
	 * (per-topic, invalidated on react add/remove), and only falls through to
	 * the database for topics seen for the first time — two queries total for
	 * all missing topics, not two per topic.
	 *
	 * @param array $topic_ids
	 */
	protected function load_topic_counts(array $topic_ids)
	{
		$missing = [];
		foreach ($topic_ids as $topic_id)
		{
			$topic_id = (int) $topic_id;
			if (isset($this->topic_counts_cache[$topic_id]))
			{
				continue;
			}

			$cached = $this->cache->get(self::CACHE_PREFIX . $topic_id);
			if ($cached !== false)
			{
				$this->topic_counts_cache[$topic_id] = $cached;
				continue;
			}

			$missing[] = $topic_id;
		}

		if (empty($missing))
		{
			return;
		}

		// All reactions for the missing topics, one query
		$sql = 'SELECT topic_id, post_id, icon_id
			FROM ' . $this->table_prefix . 'sebo_postreact_table
			WHERE ' . $this->db->sql_in_set('topic_id', $missing);
		$result = $this->db->sql_query($sql);

		$rows_by_topic = [];
		$post_ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows_by_topic[(int) $row['topic_id']][] = $row;
			$post_ids[(int) $row['post_id']] = true;
		}
		$this->db->sql_freeresult($result);

		// Existence check for the referenced posts, one query
		$existing_posts = [];
		if (!empty($post_ids))
		{
			$sql = 'SELECT post_id
				FROM ' . $this->table_prefix . 'posts
				WHERE ' . $this->db->sql_in_set('post_id', array_keys($post_ids));
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$existing_posts[(int) $row['post_id']] = true;
			}
			$this->db->sql_freeresult($result);
		}

		// Count per icon per topic (same semantics as the old per-topic code:
		// reactions on hard-deleted posts are excluded)
		foreach ($rows_by_topic as $topic_id => $rows)
		{
			$icon_counts = [];
			foreach ($rows as $row)
			{
				if (!isset($existing_posts[(int) $row['post_id']]))
				{
					continue;
				}
				$icon_id = (int) $row['icon_id'];
				$icon_counts[$icon_id] = isset($icon_counts[$icon_id]) ? $icon_counts[$icon_id] + 1 : 1;
			}

			$this->topic_counts_cache[$topic_id] = $icon_counts;
			$this->cache->put(self::CACHE_PREFIX . $topic_id, $icon_counts, self::CACHE_TTL);
			$this->remember_cached_topic($topic_id);
		}

		// Topics with no reactions must be cached too — otherwise every page
		// load re-queries all of them
		foreach ($missing as $topic_id)
		{
			if (!isset($this->topic_counts_cache[$topic_id]))
			{
				$this->topic_counts_cache[$topic_id] = [];
				$this->cache->put(self::CACHE_PREFIX . $topic_id, [], self::CACHE_TTL);
				$this->remember_cached_topic($topic_id);
			}
		}
	}

	/**
	 * Per-topic reaction counts, [icon_id => count]. Lazy single-topic load
	 * for any caller that bypassed a preload hook.
	 *
	 * @param int $topic_id
	 * @return array
	 */
	protected function get_topic_counts($topic_id)
	{
		$topic_id = (int) $topic_id;
		if (!isset($this->topic_counts_cache[$topic_id]))
		{
			$this->load_topic_counts([$topic_id]);
		}

		return isset($this->topic_counts_cache[$topic_id]) ? $this->topic_counts_cache[$topic_id] : [];
	}

	/**
	 * Invalidate the cached counts for one topic (called when a reaction is
	 * added or removed).
	 *
	 * @param int $topic_id
	 */
	public function purge_topic_counts($topic_id)
	{
		$topic_id = (int) $topic_id;

		$this->cache->destroy(self::CACHE_PREFIX . $topic_id);
		unset($this->topic_counts_cache[$topic_id]);

		$registry = $this->cache->get(self::CACHE_REGISTRY_KEY);
		if (is_array($registry))
		{
			$registry = array_values(array_diff($registry, [$topic_id]));
			$this->cache->put(self::CACHE_REGISTRY_KEY, $registry, 0);
		}
	}

	/**
	 * Invalidate every cached topic count (called by the ACP sync/purge
	 * actions, which delete reaction rows wholesale).
	 */
	public function purge_all_topic_counts()
	{
		$registry = $this->cache->get(self::CACHE_REGISTRY_KEY);
		if (is_array($registry))
		{
			foreach ($registry as $topic_id)
			{
				$this->cache->destroy(self::CACHE_PREFIX . (int) $topic_id);
			}
		}

		$this->cache->destroy(self::CACHE_REGISTRY_KEY);
		$this->topic_counts_cache = [];
	}

	protected function remember_cached_topic($topic_id)
	{
		$registry = $this->cache->get(self::CACHE_REGISTRY_KEY);
		if (!is_array($registry))
		{
			$registry = [];
		}

		if (!in_array($topic_id, $registry, true))
		{
			$registry[] = $topic_id;
			$this->cache->put(self::CACHE_REGISTRY_KEY, $registry, 0);
		}
	}

	/**
	 * Batch-load reaction counts for every topic in a search result rowset
	 * (fires once per results page, before the per-row template loop).
	 *
	 * @param \phpbb\event\data $event The event object
	 */
	public function preload_search_reactions($event)
	{
		$topic_ids = [];
		foreach ($event['rowset'] as $row)
		{
			if (!empty($row['topic_id']))
			{
				$topic_ids[(int) $row['topic_id']] = true;
			}
		}

		if (!empty($topic_ids))
		{
			$this->load_topic_counts(array_keys($topic_ids));
		}
	}

	public function viewforum_edit($event)
	{
		$topic_id = (int) $event['row']['topic_id'];

		// Per-topic icon counts, served from the page/cache layer — zero
		// queries on a warm path (was two queries per topic row).
		$icon_counts = $this->get_topic_counts($topic_id);

		$icons_with_counts = [];
		foreach ($this->grab_icons() as $icon)
		{
			$icon_id = (int) $icon['icon_id'];
			if (isset($icon_counts[$icon_id]))
			{
				$icons_with_counts[] = [
					'icon_url' => $icon['icon_url'],
					'icon_alt' => $icon['icon_alt'],
					'icon_id' => $icon_id,
					'count' => $icon_counts[$icon_id]
				];
			}
		}
		// sort for count DESC
		usort($icons_with_counts, function ($a, $b)
		{
			return $b['count'] - $a['count'];
		});
		// ##
		// template
		$event['topic_row'] = array_merge($event['topic_row'], [
			'ICONS'		 => $icons_with_counts,
			'PERM_W'		=> $this->auth->acl_get('u_new_sebo_postreact'),
			'PERM_R'		=> $this->auth->acl_get('u_new_sebo_postreact_view')
		]);
	}
	public function search_edit($event)
	{
		$row = $event['row'];
		$topic_id = isset($row['topic_id']) ? (int) $row['topic_id'] : 0;

		// Per-topic icon counts, preloaded for the whole result rowset via
		// core.search_modify_rowset (zero queries per result row).
		$icon_counts = $this->get_topic_counts($topic_id);

		$data_ico = $this->grab_icons();
		// Step 1: make a new array with icon_id key
		$data_ico_assoc = [];
		foreach ($data_ico as $icon)
		{
			$data_ico_assoc[$icon['icon_id']] = $icon;
		}
		// Step 2: build one entry per icon with its count for this topic
		$new_array = [];
		foreach ($icon_counts as $icon_id => $count)
		{
			if (isset($data_ico_assoc[$icon_id]))
			{
				$icon_info = $data_ico_assoc[$icon_id];
				$new_array[$icon_id] = [
					'icon_id'  => $icon_info['icon_id'],
					'icon_url' => $icon_info['icon_url'],
					'icon_alt' => $icon_info['icon_alt'],
					'topic_id' => $topic_id,
					'count'    => (string) $count,
				];
			}
		}
		// Remove the associative key and reset the numeric index
		$array_with_counts = array_values($new_array);
		// ##
		// template
		$event['tpl_ary'] = array_merge($event['tpl_ary'], [
			'ICONS'		 => $array_with_counts,
			'PERM_W'		=> $this->auth->acl_get('u_new_sebo_postreact'),
			'PERM_R'		=> $this->auth->acl_get('u_new_sebo_postreact_view')
		]);
	}


	public function add_permissions($event)
	{
		$permissions = $event['permissions'];
		$permissions['u_new_sebo_postreact'] = ['lang' => 'ACL_U_NEW_SEBO_POSTREACT', 'cat' => 'post'];
		$permissions['u_new_sebo_postreact_view'] = ['lang' => 'ACL_U_NEW_SEBO_POSTREACT_VIEW', 'cat' => 'post'];
		$event['permissions'] = $permissions;
	}
	/**
	 * Edit profile for reaction count
	 */
	public function edit_view_profile($event)
	{
		$user_id = (int) $event['member']['user_id'];
		$this->profile_data['user_id'] = $user_id;
		// *
		// Reactions sent
		$sql_array = [
			'SELECT' => 'icon_id, COUNT(*) AS icon_count',
			'FROM'   => [$this->table_prefix . 'sebo_postreact_table' => ''],
			'WHERE'  => 'user_id = ' . (int) $user_id,
			'GROUP_BY' => 'icon_id',
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$icon_counts = [];
		$icon_ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$icon_counts[$row['icon_id']] = $row['icon_count'];
			$icon_ids[] = (int) $row['icon_id'];
		}
		$this->db->sql_freeresult($result);
		$icons = [];
		if (!empty($icon_ids))
		{
			// Query for active icons (status = 1)
			$sql_array = [
				'SELECT'   => 'icon_id, icon_url, icon_width, icon_height, icon_alt',
				'FROM'     => [$this->table_prefix . 'sebo_postreact_icon' => ''],
				'WHERE'    => $this->db->sql_in_set('icon_id', $icon_ids) . ' AND status = 1',
			];
			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$id = (int) $row['icon_id'];
				if (isset($icon_counts[$id]))
				{
					$icons[] = [
						'ICON_ID'	 => $id,
						'ICON_COUNT'  => $icon_counts[$id],
						'ICON_URL'	=> $row['icon_url'],
						'ICON_WIDTH'  => $row['icon_width'],
						'ICON_HEIGHT' => $row['icon_height'],
						'ICON_ALT'	=> $row['icon_alt'],
						'USER_ID'     => $this->profile_data['user_id'],
					];
				}
			}
			$this->db->sql_freeresult($result);
		}
		// assign
		$this->profile_data['icons'] = $icons;
		// *
		// Reactions received
		$sql_array = [
			'SELECT'   => 'pr.icon_id, COUNT(*) AS icon_count',
			'FROM'     => [$this->table_prefix . 'sebo_postreact_table' => 'pr'],
			'LEFT_JOIN' => [
				[
					'FROM' => [$this->table_prefix . 'posts' => 'p'],
					'ON'   => 'pr.post_id = p.post_id',
				],
			],
			'WHERE'    => 'p.poster_id = ' . (int) $user_id,
			'GROUP_BY' => 'pr.icon_id',
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$received_icon_counts = [];
		$received_icon_ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$received_icon_counts[$row['icon_id']] = $row['icon_count'];
			$received_icon_ids[] = (int) $row['icon_id'];
		}
		$this->db->sql_freeresult($result);
		// grab
		$received_icons = [];
		if (!empty($received_icon_ids))
		{
			$sql_array = [
				'SELECT' => 'icon_id, icon_url, icon_width, icon_height, icon_alt',
				'FROM'   => [$this->table_prefix . 'sebo_postreact_icon' => ''],
				'WHERE'  => $this->db->sql_in_set('icon_id', $received_icon_ids) . ' AND status = 1',
			];

			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$id = (int) $row['icon_id'];
				if (isset($received_icon_counts[$id]))
				{
					$received_icons[] = [
						'ICON_ID'	 => $id,
						'ICON_COUNT'  => $received_icon_counts[$id],
						'ICON_URL'	=> $row['icon_url'],
						'ICON_WIDTH'  => $row['icon_width'],
						'ICON_HEIGHT' => $row['icon_height'],
						'ICON_ALT'	=> $row['icon_alt'],
						'USER_ID'     => $this->profile_data['user_id'],
					];
				}
			}
			$this->db->sql_freeresult($result);
		}
		// Assign
		$this->profile_data['icons_received'] = $received_icons;
	}
	public function assign_edit_view_profile($event)
	{
		if (!empty($this->profile_data))
		{
			// Assign reaction sent
			if (!empty($this->profile_data['icons']))
			{
				foreach ($this->profile_data['icons'] as $icon)
				{
					$this->template->assign_block_vars('user_reactions', [
						'ICON_ID'	 => $icon['ICON_ID'],
						'ICON_COUNT'  => $icon['ICON_COUNT'],
						'ICON_URL'	=> $icon['ICON_URL'],
						'ICON_WIDTH'  => $icon['ICON_WIDTH'],
						'ICON_HEIGHT' => $icon['ICON_HEIGHT'],
						'ICON_ALT'	=> $icon['ICON_ALT'],
						'USER_ID'     => $this->profile_data['user_id'],
					]);
				}
			}
			// Assign reaction received
			if (!empty($this->profile_data['icons_received']))
			{
				foreach ($this->profile_data['icons_received'] as $icon)
				{
					$this->template->assign_block_vars('user_reactions_received', [
						'ICON_ID'	 => $icon['ICON_ID'],
						'ICON_COUNT'  => $icon['ICON_COUNT'],
						'ICON_URL'	=> $icon['ICON_URL'],
						'ICON_WIDTH'  => $icon['ICON_WIDTH'],
						'ICON_HEIGHT' => $icon['ICON_HEIGHT'],
						'ICON_ALT'	=> $icon['ICON_ALT'],
						'USER_ID'     => $this->profile_data['user_id'],
					]);
				}
			}
		}
	}
}
