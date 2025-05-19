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
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = [];
}

$lang = array_merge($lang, [
    'ACL_U_NEW_SEBO_POSTREACT'      => 'Puo usare le reazioni ai post di PostReaction',
    'ACL_U_NEW_SEBO_POSTREACT_VIEW' => 'Puo vedere le reazioni ai post di PostReaction',
]);
