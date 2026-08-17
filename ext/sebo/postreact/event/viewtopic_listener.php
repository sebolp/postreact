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

/**
 * Assigns PostReaction data (icons, counts, reactors, permissions) to each
 * post row rendered in viewtopic.
 *
 * Reactions for the whole page are batch-loaded once (core.viewtopic_get_post_data,
 * which exposes the full post_list before the per-post loop runs) instead of
 * running 2-3 queries per post. Any caller that triggers
 * core.viewtopic_modify_post_row without that preload event firing first
 * (e.g. a print view or a moderation tool reusing the event outside the
 * normal viewtopic flow) still gets correct data: missing post_ids are
 * tracked individually and loaded lazily on first access, rather than
 * relying on a single "have we loaded anything yet" flag.
 */
class viewtopic_listener implements EventSubscriberInterface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	protected $user;
	protected $table_prefix;
	/** @var \phpbb\auth\auth */
	protected $auth;
	/** @var \phpbb\config\config */
	protected $config;
	/** @var icon_manager */
	protected $icon_manager;
	/** @var \phpbb\template\template */
	protected $template;

	/** @var array [post_id => bool] which post_ids have already been batch-loaded */
	protected $loaded_post_ids = [];
	/** @var array [post_id => [reaction row, ...]] */
	protected $reactions_cache = [];
	/** @var array [post_id => [icon_id => icon_id, ...]] the viewer's own reactions */
	protected $my_reactions_cache = [];
	/** @var array [user_id => [group_id, username, user_colour]] reactor details cache */
	protected $user_data_detailed = [];

	public static function getSubscribedEvents()
	{
		return [
			'core.viewtopic_assign_template_vars_before' => 'preload_icons',
			'core.viewtopic_get_post_data'                => 'preload_reactions',
			'core.viewtopic_modify_post_row'              => 'assign_to_template',
		];
	}

	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		$user,
		$table_prefix,
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		icon_manager $icon_manager,
		\phpbb\template\template $template
	)
	{
		$this->db = $db;
		$this->user = $user;
		$this->table_prefix = $table_prefix;
		$this->auth = $auth;
		$this->config = $config;
		$this->icon_manager = $icon_manager;
		$this->template = $template;
	}

	/**
	 * Warm the icon cache once before the topic is rendered
	 */
	public function preload_icons($event)
	{
		$this->icon_manager->get_icons();
		// Generate token
        $this->template->assign_var('POSTREACT_CSRF_TOKEN', generate_link_hash('postreact_ajax'));
	}

	/**
	 * Batch-load reactions, reactor details and the viewer's own reactions
	 * for the whole page (post_list), instead of per-post.
	 */
	public function preload_reactions($event)
	{
		$post_list = isset($event['post_list']) ? $event['post_list'] : [];
		$this->load_reactions($post_list);
	}

	/**
	 * @param array $post_ids
	 */
	protected function load_reactions(array $post_ids)
	{
		$missing = [];
		foreach ($post_ids as $post_id)
		{
			$post_id = (int) $post_id;
			if (!isset($this->loaded_post_ids[$post_id]))
			{
				$missing[] = $post_id;
			}
		}

		if (empty($missing))
		{
			return;
		}

		foreach ($missing as $post_id)
		{
			$this->loaded_post_ids[$post_id] = true;
			if (!isset($this->reactions_cache[$post_id]))
			{
				$this->reactions_cache[$post_id] = [];
			}
		}

		// All reactions for the missing posts, one query
		$sql_array = [
			'SELECT' => '*',
			'FROM'   => [$this->table_prefix . 'sebo_postreact_table' => ''],
			'WHERE'  => $this->db->sql_in_set('post_id', $missing),
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		$unique_user_ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$post_id = (int) $row['post_id'];
			$this->reactions_cache[$post_id][] = $row;
			$unique_user_ids[(int) $row['user_id']] = true;
		}
		$this->db->sql_freeresult($result);

		// Reactor user details for the whole page, one query
		if (!empty($unique_user_ids))
		{
			// only fetch users we don't already have cached from a previous batch
			$missing_user_ids = array_diff(array_keys($unique_user_ids), array_keys($this->user_data_detailed));
			if (!empty($missing_user_ids))
			{
				$sql_array = [
					'SELECT' => 'user_id, group_id, username, user_colour',
					'FROM'   => [USERS_TABLE => ''],
					'WHERE'  => $this->db->sql_in_set('user_id', $missing_user_ids),
				];
				$sql = $this->db->sql_build_query('SELECT', $sql_array);
				$result = $this->db->sql_query($sql);

				while ($row_user = $this->db->sql_fetchrow($result))
				{
					$this->user_data_detailed[$row_user['user_id']] = [
						'group_id'    => $row_user['group_id'],
						'username'    => $row_user['username'],
						'user_colour' => $row_user['user_colour'],
					];
				}
				$this->db->sql_freeresult($result);
			}
		}

		// The viewer's own reactions for the missing posts, one query
		// (skipped for guests/bots - it previously ran pointlessly for ANONYMOUS)
		$user_id_logged = (int) $this->user->data['user_id'];
		if ($user_id_logged != ANONYMOUS)
		{
			$sql_array = [
				'SELECT' => 'post_id, icon_id',
				'FROM'   => [$this->table_prefix . 'sebo_postreact_table' => ''],
				'WHERE'  => 'user_id = ' . $user_id_logged . ' AND ' . $this->db->sql_in_set('post_id', $missing),
			];
			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$post_id = (int) $row['post_id'];
				if (!isset($this->my_reactions_cache[$post_id]))
				{
					$this->my_reactions_cache[$post_id] = [];
				}
				$this->my_reactions_cache[$post_id][] = [
					'post_id' => $row['post_id'],
					'icon_id' => $row['icon_id'],
				];
			}
			$this->db->sql_freeresult($result);
		}
	}

	public function assign_to_template($event)
	{
		$postrow = $event['postrow'];
		$row = $event['row'];
		$my_pid = (int) $row['post_id'];
		$my_tid = (int) $row['topic_id'];

		// Lazy fallback for any caller that fires this event without the
		// page-level preload (tracked per post_id, not a single latch, so
		// it still works correctly for every post in that scenario)
		if (!isset($this->loaded_post_ids[$my_pid]))
		{
			$this->load_reactions([$my_pid]);
		}

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
			$user_ids_list[$icon_id][] = [
				'user_id' => $user_id,
				'post_id' => $post_id
			];
		}
		// ##
		// merge username, colour and group to user_id, from the page-level cache
		$user_ids_with_details = [];
		foreach ($user_ids_list as $icon_id => $entries)
		{
			foreach ($entries as $entry)
			{
				$user_id = $entry['user_id'];
				if (isset($this->user_data_detailed[$user_id]))
				{
					$user_ids_with_details[$icon_id][] = [
						'user_id' => $user_id,
						'username' => $this->user_data_detailed[$user_id]['username'],
						'group_id' => $this->user_data_detailed[$user_id]['group_id'],
						'user_colour' => $this->user_data_detailed[$user_id]['user_colour'],
						'post_id' => $entry['post_id'],
					];
				}
			}
		}
		// ##
		// mark your choice, from the page-level cache
		$check = isset($this->my_reactions_cache[$my_pid]) ? $this->my_reactions_cache[$my_pid] : [];
		// ##
		// template
		$event['post_row'] = array_merge($event['post_row'], [
			'POSTREACT_FLOAT_CLASS' => ((int) $this->config['sebo_postreact_display_position'] === 1) ? 'left' : 'right',
			'PERM_W'		=> $this->auth->acl_get('u_new_sebo_postreact'),
			'PERM_R'		=> $this->auth->acl_get('u_new_sebo_postreact_view'),
			'N_REACTIONS'   => $total_match_count,
			'ICONS'			=> $this->icon_manager->get_icons(),
			'MY_PID'		=> $my_pid,
			'MY_TID'		=> $my_tid,
			'ICON_COUNTS'   => $icon_counts,
			'ICON_CHECK'	=> $check,
			'REACTORS'	  	=> $user_ids_with_details,
			'SELF_REACT'	=> $this->config['sebo_postreact_self_react'],
			'BUTT_POSITION' => $this->config['sebo_postreact_butt_position'] ?? 'up',
		]);
	}
}
