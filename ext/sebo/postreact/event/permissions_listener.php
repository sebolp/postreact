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
 * Registers the extension's ACL permissions.
 */
class permissions_listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return [
			'core.permissions' => 'add_permissions',
		];
	}

	public function add_permissions($event)
	{
		$permissions = $event['permissions'];
		$permissions['u_new_sebo_postreact'] = ['lang' => 'ACL_U_NEW_SEBO_POSTREACT', 'cat' => 'post'];
		$permissions['u_new_sebo_postreact_view'] = ['lang' => 'ACL_U_NEW_SEBO_POSTREACT_VIEW', 'cat' => 'post'];
		$event['permissions'] = $permissions;
	}
}
