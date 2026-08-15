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
 * Assigns PostReaction icon summaries (sorted by count) to each topic row
 * rendered in viewforum.
 *
 * core.viewforum_modify_topicrow does not expose the full topic list, so
 * the first row still falls back to a single-topic lazy load in
 * reaction_count_cache; core.viewforum_topic_row_after (which fires right
 * after, and DOES expose the full page rowset) is used to preload every
 * other topic on the page in one query, so rows 2..N are served from cache.
 */
class viewforum_listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;
	/** @var icon_manager */
	protected $icon_manager;
	/** @var reaction_count_cache */
	protected $reaction_count_cache;
	protected $page_preloaded = false;

	public static function getSubscribedEvents()
	{
		return [
			'core.viewforum_modify_page_title' => 'preload_icons',
			'core.viewforum_modify_topicrow'   => 'viewforum_edit',
			'core.viewforum_topic_row_after'   => 'preload_page_counts',
		];
	}

	public function __construct(
		\phpbb\auth\auth $auth,
		icon_manager $icon_manager,
		reaction_count_cache $reaction_count_cache
	)
	{
		$this->auth = $auth;
		$this->icon_manager = $icon_manager;
		$this->reaction_count_cache = $reaction_count_cache;
	}

	/**
	 * Warm the icon cache once before the forum is rendered
	 */
	public function preload_icons($event)
	{
		$this->icon_manager->get_icons();
	}

	/**
	 * core.viewforum_topic_row_after exposes the full page rowset (keyed by
	 * topic_id) on every call, so the first time it fires we can batch-load
	 * counts for every topic on the page in one query, benefiting every row
	 * after the current one.
	 */
	public function preload_page_counts($event)
	{
		if ($this->page_preloaded)
		{
			return;
		}
		$this->page_preloaded = true;

		$rowset = isset($event['rowset']) ? $event['rowset'] : [];
		if (!empty($rowset))
		{
			$this->reaction_count_cache->load_topic_counts(array_keys($rowset));
		}
	}

	public function viewforum_edit($event)
	{
		$topicrow = $event['topicrow'];
		$row = $event['row'];
		$topic_id = (int) $row['topic_id'];

		$icon_counts = $this->reaction_count_cache->get_topic_counts($topic_id);

		// ##
		// sort by number
		$icons_with_counts = [];
		foreach ($this->icon_manager->get_icons() as $icon)
		{
			$icon_id = $icon['icon_id'];
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
}
