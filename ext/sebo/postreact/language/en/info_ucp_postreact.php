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
	'NOTIFICATION_TYPE_POSTREACT'	=> 'Someone reacted to your post',
	'UCP_POSTREACT_TITLE'			=> 'PostReact(ion) options',
	'UCP_POSTREACT_EXPLAIN'		=> 'Choose how PostReact(ion) notifies you when someone reacts to your posts.',
	'POSTREACT_NOTIFY_MODE' => 'Notify me once per post, not once per reaction',
	'POSTREACT_NOTIFY_MODE_EXPLAIN' => 'If enabled, multiple reactions on the same post produce a single notification instead of one per reaction.',
	'POSTREACT_NOTIFY_MODE_EXPLAIN_BOTTOM' => 'In both cases notification must be enabled in tab "Edit notification options" to receive notifications.',
]);
