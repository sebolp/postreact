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

	'NOT_INSERTED_VALUE'	=> 'OOPS! Reazione non aggiunta!',
	'INSERTED_VALUE'	=> 'Reazione aggiunta!',
	'DELETED_VALUE'	=> 'Reazione cancellata!',
	'LOGIN_TO_REACT'	=> 'Effettua il login per reagire!',

	'SEBO_POSTREACT_NOTIFICATION'	=> '<img src="%s" style="width:32px !important;height:32px !important;"><strong>Hai ricevuto una reazione</strong> da %s nell\'argomento "%s"',
	'ALREADY_REACTED'	=> 'Hai gia reagito a questo messaggio. Clicca per cancellare la tua reazione.',
	'REACTION_SENT_LIST'	=> 'Elenco reazioni inviate dall\'utente',
	'REACTION_RECEIVED_LIST'	=> 'Elenco reazioni ricevute dall\'utente',

]);
