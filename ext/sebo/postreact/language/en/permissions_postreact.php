<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB')) {
 "`t" * ($matches[0].Length / 4) exit;
}

if (empty($lang) || !is_array($lang)) {
 "`t" * ($matches[0].Length / 4) $lang = [];
}

$lang = array_merge($lang, [
 "`t" * ($matches[0].Length / 4) 'ACL_U_NEW_SEBO_POSTREACT'      => 'Can use PostReactions in posts',
 "`t" * ($matches[0].Length / 4) 'ACL_U_NEW_SEBO_POSTREACT_VIEW' => 'Can view PostReactions in posts',
]);
