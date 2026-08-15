<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\ucp;

/**
 * PostReaction UCP module info. Adds a dedicated entry under
 * "Board preferences", next to "Edit notification options" - instead of
 * injecting content into phpBB's own notifications template, which is
 * shared between the notification list and notification settings pages.
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\sebo\postreact\ucp\main_module',
			'title'		=> 'UCP_POSTREACT_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'UCP_POSTREACT_TITLE',
					'auth'	=> '',
					'cat'	=> ['UCP_PREFS'],
				],
			],
		];
	}
}
