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

$lang = array_merge($lang, [

 "`t" * ($matches[0].Length / 4) 'NOT_INSERTED_VALUE'    => 'OOPS! Reazione non aggiunta!',
 "`t" * ($matches[0].Length / 4) 'INSERTED_VALUE'    => 'Reazione aggiunta!',
 "`t" * ($matches[0].Length / 4) 'DELETED_VALUE' => 'Reazione cancellata!',
 "`t" * ($matches[0].Length / 4) 'LOGIN_TO_REACT'    => 'Effettua il login per reagire!',

 "`t" * ($matches[0].Length / 4) 'SEBO_POSTREACT_NOTIFICATION'   => '<img src="%s" style="width:32px !important;height:32px !important;"><strong>Hai ricevuto una reazione</strong> da %s nell\'argomento "%s"',
 "`t" * ($matches[0].Length / 4) 'ALREADY_REACTED'   => 'Hai gia reagito a questo messaggio. Clicca per cancellare la tua reazione.',
 "`t" * ($matches[0].Length / 4) 'REACTION_SENT_LIST'    => 'Elenco reazioni inviate dall\'utente',
 "`t" * ($matches[0].Length / 4) 'REACTION_RECEIVED_LIST'    => 'Elenco reazioni ricevute dall\'utente',

]);
