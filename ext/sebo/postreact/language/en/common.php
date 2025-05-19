<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB')) {
 "`t" * ($matches[0].Length / 4) exit;
}

if (empty($lang) || !is_array($lang)) {
 "`t" * ($matches[0].Length / 4) $lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [

 "`t" * ($matches[0].Length / 4) 'NOT_INSERTED_VALUE'    => 'OOPS! Reaction not added!',
 "`t" * ($matches[0].Length / 4) 'INSERTED_VALUE'    => 'Reaction added!',
 "`t" * ($matches[0].Length / 4) 'DELETED_VALUE'     => 'Reaction deleted!',
 "`t" * ($matches[0].Length / 4) 'LOGIN_TO_REACT'    => 'Login to react!',

 "`t" * ($matches[0].Length / 4) 'SEBO_POSTREACT_NOTIFICATION'   => '<img src="%s" style="width:32px !important;height:32px !important;"><strong>Received a reaction</strong> from %s in topic "%s"',
 "`t" * ($matches[0].Length / 4) 'ALREADY_REACTED'   => 'You have already reacted to this post. Click to delete your reaction.',
 "`t" * ($matches[0].Length / 4) 'REACTION_SENT_LIST'    => 'Total reactions sent by the user',
 "`t" * ($matches[0].Length / 4) 'REACTION_RECEIVED_LIST'    => 'Total reactions received by the user',

]);
