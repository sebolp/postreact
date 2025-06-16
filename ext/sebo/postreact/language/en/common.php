<?php
/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, sebo, https://www.fiatpandaclub.org
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
	'REACT_TO_POST'				=> 'React to this post'

]);
