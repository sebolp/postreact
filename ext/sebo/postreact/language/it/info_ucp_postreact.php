<?php
/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, sebo, https://www.fiatpandaclub.org
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
	'NOTIFICATION_TYPE_POSTREACT'	=> 'Qualcuno ha reagito a un tuo post',
	'UCP_POSTREACT_TITLE'			=> 'PostReaction',
	'UCP_POSTREACT_EXPLAIN'		=> 'Scegli come PostReaction ti avvisa quando qualcuno reagisce ai tuoi post.',
	'POSTREACT_NOTIFY_MODE' => 'Raggruppa le notifiche per post, non notificare ogni reazione ricevuta.',
	'POSTREACT_NOTIFY_MODE_EXPLAIN' => 'Se abilitata, reazioni multiple sullo stesso messaggio producono una singola notifica invece di una per ogni reazione.',
	'POSTREACT_NOTIFY_MODE_EXPLAIN_BOTTOM' => 'In entrambi i casi, per poterle ricevere, le notifiche devono essere abilitate nella scheda "Modifica opzioni di notifica".',
]);
