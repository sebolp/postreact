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

/**
 * Collects and displays "reactions sent" / "reactions received" stats
 * on a user's memberlist profile page.
 */
class profile_listener implements EventSubscriberInterface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	protected $table_prefix;
	/** @var \phpbb\template\template */
	protected $template;

	/**
	 * Data collected in edit_view_profile(), consumed in assign_edit_view_profile().
	 * Both events fire during the same request for the same listener instance.
	 *
	 * @var array
	 */
	protected $profile_data = [];

	public static function getSubscribedEvents()
	{
		return [
			'core.memberlist_view_profile'                      => 'edit_view_profile',
			'core.memberlist_modify_view_profile_template_vars' => 'assign_edit_view_profile',
		];
	}

	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		$table_prefix,
		\phpbb\template\template $template
	)
	{
		$this->db = $db;
		$this->table_prefix = $table_prefix;
		$this->template = $template;
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
