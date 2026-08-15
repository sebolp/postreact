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
 * Batch-loading, two-level cache (per-request + phpBB cache.driver) for
 * per-topic reaction counts, keyed by icon_id.
 *
 * Used by viewforum_listener, search_listener (topic rows), acp_controller
 * (bulk invalidation on sync/purge) and react_controller (invalidation on
 * add/remove reaction). Centralised here because all four need the same
 * "one query for many topics, not one query per topic" behaviour and the
 * same invalidation logic.
 */
class reaction_count_cache
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	protected $table_prefix;
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var array [topic_id => [icon_id => count]] for the current request */
	protected $topic_counts_cache = [];

	/** @var array [post_id => ['counts' => [icon_id => count], 'reactors' => [icon_id => [...]]]] for the current request */
	protected $post_reactions_cache = [];

	const CACHE_PREFIX = '_sebo_postreact_topic_counts_';
	const CACHE_REGISTRY_KEY = '_sebo_postreact_cached_topics';
	const CACHE_TTL = 300;

	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		$table_prefix,
		\phpbb\cache\driver\driver_interface $cache
	)
	{
		$this->db = $db;
		$this->table_prefix = $table_prefix;
		$this->cache = $cache;
	}

	/**
	 * Batch-load per-topic reaction counts for a set of topic IDs.
	 *
	 * Reads are served from the per-request cache first, then the phpBB
	 * cache (per-topic, invalidated on react add/remove), and only fall
	 * through to the database for topics seen for the first time — two
	 * queries total for all missing topics, not two per topic.
	 *
	 * @param array $topic_ids
	 */
	public function load_topic_counts(array $topic_ids)
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
		$sql_array = [
			'SELECT' => 'topic_id, post_id, icon_id',
			'FROM'   => [$this->table_prefix . 'sebo_postreact_table' => ''],
			'WHERE'  => $this->db->sql_in_set('topic_id', $missing),
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		$rows_by_topic = [];
		$post_ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows_by_topic[(int) $row['topic_id']][] = $row;
			$post_ids[(int) $row['post_id']] = true;
		}
		$this->db->sql_freeresult($result);

		// Existence check for the referenced posts, one query (reactions on
		// hard-deleted posts must be excluded, same semantics as before)
		$existing_posts = [];
		if (!empty($post_ids))
		{
			$sql_array = [
				'SELECT' => 'post_id',
				'FROM'   => [$this->table_prefix . 'posts' => ''],
				'WHERE'  => $this->db->sql_in_set('post_id', array_keys($post_ids)),
			];
			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$existing_posts[(int) $row['post_id']] = true;
			}
			$this->db->sql_freeresult($result);
		}

		// Count per icon per topic
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

		// Topics with no reactions must be cached too, otherwise every page
		// load re-queries them
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
	public function get_topic_counts($topic_id)
	{
		$topic_id = (int) $topic_id;
		if (!isset($this->topic_counts_cache[$topic_id]))
		{
			$this->load_topic_counts([$topic_id]);
		}

		return isset($this->topic_counts_cache[$topic_id]) ? $this->topic_counts_cache[$topic_id] : [];
	}

	/**
	 * Batch-load per-post reaction counts and reactor details (username,
	 * user_colour) for a set of post IDs, one query for all of them.
	 *
	 * Per-request only, no phpBB cache.driver persistence: unlike
	 * load_topic_counts() this carries reactor identity, not just a count,
	 * and is used on pages with a naturally small, page-bound post_id set
	 * (search results), where the extra cache layer wouldn't pay off.
	 *
	 * @param array $post_ids
	 */
	public function load_post_reactions(array $post_ids)
	{
		$missing = [];
		foreach ($post_ids as $post_id)
		{
			$post_id = (int) $post_id;
			if (!isset($this->post_reactions_cache[$post_id]))
			{
				$missing[] = $post_id;
				$this->post_reactions_cache[$post_id] = [
					'counts'	=> [],
					'reactors'	=> [],
				];
			}
		}

		if (empty($missing))
		{
			return;
		}

		$sql_array = [
			'SELECT'    => 'r.post_id, r.icon_id, r.user_id, u.username, u.user_colour',
			'FROM'      => [$this->table_prefix . 'sebo_postreact_table' => 'r'],
			'LEFT_JOIN' => [
				[
					'FROM' => [USERS_TABLE => 'u'],
					'ON'   => 'r.user_id = u.user_id',
				],
			],
			'WHERE'     => $this->db->sql_in_set('r.post_id', $missing),
			'ORDER_BY'  => 'r.react_time ASC',
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$post_id = (int) $row['post_id'];
			$icon_id = (int) $row['icon_id'];

			if (!isset($this->post_reactions_cache[$post_id]['counts'][$icon_id]))
			{
				$this->post_reactions_cache[$post_id]['counts'][$icon_id] = 0;
				$this->post_reactions_cache[$post_id]['reactors'][$icon_id] = [];
			}

			$this->post_reactions_cache[$post_id]['counts'][$icon_id]++;
			$this->post_reactions_cache[$post_id]['reactors'][$icon_id][] = [
				'user_id'		=> (int) $row['user_id'],
				'username'		=> $row['username'],
				'user_colour'	=> $row['user_colour'],
			];
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Per-post reaction counts and reactor details:
	 * ['counts' => [icon_id => count], 'reactors' => [icon_id => [...]]].
	 * Lazy single-post load for any caller that bypassed a preload hook.
	 *
	 * @param int $post_id
	 * @return array
	 */
	public function get_post_reactions($post_id)
	{
		$post_id = (int) $post_id;
		if (!isset($this->post_reactions_cache[$post_id]))
		{
			$this->load_post_reactions([$post_id]);
		}

		return $this->post_reactions_cache[$post_id];
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
}
