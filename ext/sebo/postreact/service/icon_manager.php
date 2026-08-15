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
 * Centralised icon reading with per-request caching.
 *
 * Used by the listeners, the ACP controller and the react controller
 * so the same SELECT is not duplicated across the extension.
 */
class icon_manager
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	protected $table_prefix;

	/** @var array|null */
	protected $icons_cache = null;

	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		$table_prefix
	)
	{
		$this->db = $db;
		$this->table_prefix = $table_prefix;
	}

	/**
	 * Return all icons ordered by icon_order, cached for the current request.
	 *
	 * @param bool $decode_emoji Whether to html_entity_decode the icon_emoji field
	 *                           (needed for display, not needed for raw comparisons)
	 * @return array
	 */
	public function get_icons($decode_emoji = false)
	{
		if ($this->icons_cache === null)
		{
			$data_ico = [];
			$sql_array = [
				'SELECT'	=> '*',
				'FROM'		=> [$this->table_prefix . 'sebo_postreact_icon' => ''],
				'ORDER_BY'	=> 'icon_order ASC, icon_id ASC',
			];
			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query($sql);

			if ($result)
			{
				while ($row = $this->db->sql_fetchrow($result))
				{
					$data_ico[] = $row;
				}
				$this->db->sql_freeresult($result);
			}

			$this->icons_cache = $data_ico;
		}

		if ($decode_emoji)
		{
			return array_map(function ($icon)
			{
				$icon['icon_emoji'] = html_entity_decode($icon['icon_emoji']);
				return $icon;
			}, $this->icons_cache);
		}

		return $this->icons_cache;
	}

	/**
	 * Force the next get_icons() call to re-query the database.
	 * Useful after inserting/deleting/reordering icons within the same request.
	 */
	public function reset_cache()
	{
		$this->icons_cache = null;
	}
}
