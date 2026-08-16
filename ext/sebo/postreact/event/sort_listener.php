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
 * Adds a "Sort by: Reactions" option to the native viewtopic post sort
 * dropdown (Display / Sort by / Direction), reusing phpBB's own sort
 * mechanism instead of a custom UI. core.viewtopic_gen_sort_selects_before
 * fires before gen_sort_selects() renders the dropdown and before
 * sort_by_sql[$sort_key] is used to build the post ORDER BY, so adding a
 * key here is enough: pagination, direction (asc/desc) and persistence
 * across pages are all handled by the existing core flow, unchanged for
 * the 'a'/'t'/'s' keys.
 *
 * The 'r' key's SQL is a correlated subquery counting rows in
 * sebo_postreact_table for each post, since the reaction count is not a
 * column on the posts table.
 */
class sort_listener implements EventSubscriberInterface
{
	/** @var string */
	protected $table_prefix;
	/** @var \phpbb\language\language */
	protected $language;

	public static function getSubscribedEvents()
	{
		return [
			'core.viewtopic_gen_sort_selects_before' => 'add_reaction_sort_option',
		];
	}

	public function __construct($table_prefix, \phpbb\language\language $language)
	{
		$this->table_prefix = $table_prefix;
		$this->language = $language;
	}

	public function add_reaction_sort_option($event)
	{
		$sort_by_text = $event['sort_by_text'];
		$sort_by_sql = $event['sort_by_sql'];
		$join_user_sql = $event['join_user_sql'];

		// This listener can fire before core.user_setup has loaded our
		// common language file on some paths, so load it explicitly here
		// rather than relying on language_listener's timing.
		$this->language->add_lang('common', 'sebo/postreact');

		$sort_by_text['r'] = $this->language->lang('PR_SORT_BY_REACTION');

		// Correlated subquery: no column on posts stores the reaction
		// count, so it is counted per row at query time. p.post_id kept
		// as tie-breaker, same pattern phpBB uses for 'a'/'t'/'s'.
		$sort_by_sql['r'] = [
			'(SELECT COUNT(*) FROM ' . $this->table_prefix . 'sebo_postreact_table pr WHERE pr.post_id = p.post_id)',
			'p.post_id',
		];

		// No USERS_TABLE join needed for this key
		$join_user_sql['r'] = false;

		$event['sort_by_text'] = $sort_by_text;
		$event['sort_by_sql'] = $sort_by_sql;
		$event['join_user_sql'] = $join_user_sql;
	}
}
