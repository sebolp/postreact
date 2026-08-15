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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use sebo\postreact\service\icon_manager;
use sebo\postreact\service\reaction_count_cache;

/**
 * Handles the custom "sebo_user_reactions" search: injecting results,
 * setting the page title, and annotating regular search results with
 * PostReaction icons.
 */
class search_listener implements EventSubscriberInterface
{
	/** @var \phpbb\language\language */
	protected $language;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	protected $user;
	/** @var \phpbb\request\request */
	protected $request;
	/** @var \phpbb\template\template */
	protected $template;
	protected $table_prefix;
	/** @var \phpbb\auth\auth */
	protected $auth;
	protected $php_ext;
	protected $phpbb_root_path;
	/** @var icon_manager */
	protected $icon_manager;
	/** @var reaction_count_cache */
	protected $reaction_count_cache;
	/** @var \phpbb\config\config */
	protected $config;

	public static function getSubscribedEvents()
	{
		return [
			'core.search_modify_param_after'   => 'search_user_reactions_title',
			'core.search_backend_search_after' => 'search_user_reactions',
			'core.search_modify_rowset'        => 'preload_search_reactions',
			'core.search_modify_tpl_ary'       => 'search_edit',
		];
	}

	public function __construct(
		\phpbb\language\language $language,
		\phpbb\db\driver\driver_interface $db,
		$user,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		$table_prefix,
		\phpbb\auth\auth $auth,
		$php_ext,
		$phpbb_root_path,
		icon_manager $icon_manager,
		reaction_count_cache $reaction_count_cache,
		\phpbb\config\config $config
	)
	{
		$this->language = $language;
		$this->db = $db;
		$this->user = $user;
		$this->request = $request;
		$this->template = $template;
		$this->table_prefix = $table_prefix;
		$this->auth = $auth;
		$this->php_ext = $php_ext;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->icon_manager = $icon_manager;
		$this->reaction_count_cache = $reaction_count_cache;
		$this->config = $config;
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

	/**
	 * core.search_modify_rowset exposes the full result rowset before the
	 * per-row template loop, so every topic referenced in the search
	 * results can be batch-loaded in one query instead of one per row.
	 */
	public function preload_search_reactions($event)
	{
		$rowset = isset($event['rowset']) ? $event['rowset'] : [];
		if (empty($rowset))
		{
			return;
		}

		$topic_ids = [];
		$post_ids = [];
		foreach ($rowset as $row)
		{
			if (isset($row['topic_id']))
			{
				$topic_ids[] = (int) $row['topic_id'];
			}
			if (isset($row['post_id']))
			{
				$post_ids[] = (int) $row['post_id'];
			}
		}

		if (!empty($topic_ids))
		{
			$this->reaction_count_cache->load_topic_counts($topic_ids);
		}

		if (!empty($post_ids))
		{
			$this->reaction_count_cache->load_post_reactions($post_ids);
		}
	}

	public function search_edit($event)
	{
		$row = $event['row'];
		$post_id = isset($row['post_id']) ? (int) $row['post_id'] : 0;

		$reactions = $this->reaction_count_cache->get_post_reactions($post_id);
		$icon_counts = $reactions['counts'];
		$reactors = $reactions['reactors'];

		// Merge username/colour details onto each reactor for the popup,
		// same shape assign_to_template() uses for the viewtopic popup
		$reactors_with_details = [];
		foreach ($reactors as $icon_id => $entries)
		{
			foreach ($entries as $entry)
			{
				$reactors_with_details[$icon_id][] = [
					'user_id'     => $entry['user_id'],
					'username'    => $entry['username'],
					'user_colour' => $entry['user_colour'],
				];
			}
		}

		// template
		$event['tpl_ary'] = array_merge($event['tpl_ary'], [
			'ICONS'					=> $this->icon_manager->get_icons(),
			'ICON_COUNTS'			=> $icon_counts,
			'REACTORS'				=> $reactors_with_details,
			'POST_ID'				=> $post_id,
			'POSTREACT_FLOAT_CLASS'	=> ((int) $this->config['sebo_postreact_display_position'] === 1) ? 'left' : 'right',
			'PERM_W'				=> $this->auth->acl_get('u_new_sebo_postreact'),
			'PERM_R'				=> $this->auth->acl_get('u_new_sebo_postreact_view'),
		]);
	}
}
