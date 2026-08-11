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

	'NOT_INSERTED_VALUE'	=> 'OOPS! Reaction not added!',
	'INSERTED_VALUE'	=> 'Reaction added!',
	'DELETED_VALUE'		=> 'Reaction deleted!',
	'LOGIN_TO_REACT'	=> 'Login to react!',

	'SEBO_POSTREACT_NOTIFICATION'	=> '%s reacted to your post "%s"',
	'ALREADY_REACTED'	=> 'You have already reacted to this post. Click to delete your reaction.',
	'REACTION_SENT_LIST'	=> 'Total reactions sent by the user',
	'REACTION_RECEIVED_LIST'	=> 'Total reactions received by the user',
	'POSTREACT_SUMMARY'			=> 'SUMMARY',
	'REACT_TO_POST'				=> 'React to this post',
	/* > 2.1 */
	'CANNOT_SELF_REACT'			=> 'You cannot react to your own post!',
	'PR_EXTENSION_NAME'			=> 'POSTREACT(ions)',
	/* > 2.2 */
	'POSTREACTION_AJAX_ERROR'	=> 'Error during AJAX request',
	'POSTREACTION_JSON_ERROR'	=> 'Error parsing JSON response from server',
	/* > 2.3 */
	'SEARCH_USER_REACTIONS_RECEIVED' => 'PostReact(ions) %s received by %s',
	'SEARCH_USER_REACTIONS'     => 'PostReact(ions) %s given by %s',
	/* > 2.5.1 */
	'PR_NO_REACTION_TO_REMOVE' => 'No reaction to remove',
	'PR_ERROR_REMOVING_REACTION' => 'Error removing reaction',
	'PR_CLOSE' => 'Close',
	/* > 2.5.2 */
	'PR_BUTTON_TEXT' => 'React',
	'PR_DISPLAY_BUTTON_POSITION'  => 'Define button position into the post to react',
	'BUTT_POSITION_UP' => 'Up, inline with post buttons',
	'BUTT_POSITION_LOW' => 'Low, button style',
	'BUTT_POSITION_EMOJI_LEVEL' => 'Inline with reactions',
	'PR_MEMBERLIST_NO_REACTIONS_SENT' => 'No reaction sent by this user',
	'PR_MEMBERLIST_NO_REACTIONS_RECEIVED' => 'No reaction received by this user',
	'POSTREACT_NOTIFY_MODE'         => 'Only notify me once per post',
	'POSTREACT_NOTIFY_MODE_EXPLAIN' => 'When enabled, you will only receive one reaction notification per post. When disabled, you receive a separate notification for every reaction.',
]);
