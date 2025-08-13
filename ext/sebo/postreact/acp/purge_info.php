<?php
/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\acp;

/**
 * PostReaction ACP module info.
 */
class purge_info
{
	public function module()
	{
		return [
			'filename'	=> '\sebo\postreact\acp\purge_module',
			'title'		=> 'ACP_POSTREACT_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_POSTREACT',
					'auth'	=> 'ext_sebo/postreact && acl_a_board',
					'cat'	=> ['ACP_POSTREACT_PURGE'],
				],
			],
		];
	}
}
