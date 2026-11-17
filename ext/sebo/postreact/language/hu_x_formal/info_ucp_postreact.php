<?php
/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'NOTIFICATION_TYPE_POSTREACT'	=> 'Valaki reagált a hozzászólására',
	'UCP_POSTREACT_TITLE'			=> 'Hozzászólás-reakciók beállításai',
	'UCP_POSTREACT_EXPLAIN'		=> 'Válassza ki, hogyan értesítsen a rendszer, ha valaki reagál a hozzászólására.',
	'POSTREACT_NOTIFY_MODE' => 'Értesítésen hozzászólásonként egyszer, nem pedig reakciónként',
	'POSTREACT_NOTIFY_MODE_EXPLAIN' => 'Ha engedélyezi, több reakció ugyanazon a hozzászóláson egyetlen értesítést küld el, nem pedig reakciónként egyet.',
	'POSTREACT_NOTIFY_MODE_EXPLAIN_BOTTOM' => 'Mindkét esetben engedélyezni kell az értesítéseket az „Értesítési beállítások szerkesztése” fülön ahhoz, hogy megkapja az értesítéseket.',
]);
