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

	'NOT_INSERTED_VALUE'	=> 'OOPS! Reazione non aggiunta!',
	'INSERTED_VALUE'	=> 'Reazione aggiunta!',
	'DELETED_VALUE'	=> 'Reazione cancellata!',
	'LOGIN_TO_REACT'	=> 'Effettua il login per reagire!',

	'SEBO_POSTREACT_NOTIFICATION'	=> '%s ha reagito al tuo post "%s"',
	'ALREADY_REACTED'	=> 'Hai gia reagito a questo messaggio. Clicca per cancellare la tua reazione.',
	'REACTION_SENT_LIST'	=> 'Elenco reazioni inviate dall\'utente',
	'REACTION_RECEIVED_LIST'	=> 'Elenco reazioni ricevute dall\'utente',
	'POSTREACT_SUMMARY'			=> 'SUMMARY',
	'REACT_TO_POST'				=> 'Reagisci a questo messaggio',
	/* > 2.1 */
	'CANNOT_SELF_REACT'			=> 'Non puoi reagire ad un tuo messaggio',
	'PR_EXTENSION_NAME'			=> 'POSTREACT(ions)',
	/* > 2.2 */
	'POSTREACTION_AJAX_ERROR'	=> 'Errore durante la richiesta AJAX',
	'POSTREACTION_JSON_ERROR'	=> 'Errore nel parsing della risposta JSON dal server',
	/* > 2.3 */
	'SEARCH_USER_REACTIONS_RECEIVED' => 'PostReact(ions) %s ricevuti da %s',
	'SEARCH_USER_REACTIONS'     => 'PostReact(ions) %s inviati da %s',
	/* > 2.5.1 */
	'PR_NO_REACTION_TO_REMOVE' => 'Nessuna reazione da rimuovere',
	'PR_ERROR_REMOVING_REACTION' => 'Errore rimuovendo la reazione',
	'PR_CLOSE' => 'Chiudi',
	/* > 2.5.2 */
	'PR_BUTTON_TEXT' => 'Reagisci',
	'PR_DISPLAY_BUTTON_POSITION'  => 'Posizione del pulsante per inserire le reazioni nel post',
	'BUTT_POSITION_UP' => 'In alto, con altri pulsanti',
	'BUTT_POSITION_LOW' => 'In basso',
	'BUTT_POSITION_EMOJI_LEVEL' => 'Vicino le reazioni',
	'PR_MEMBERLIST_NO_REACTIONS_SENT' => 'Nessuna reazione inviata da questo utente',
	'PR_MEMBERLIST_NO_REACTIONS_RECEIVED' => 'Nessuna reazione ricevuta da questo utente',
	/* > 2.6.0 */
	'PR_SORT_BY_REACTION' => 'Reactions',
	'POSTREACTION_CSRF_ERROR' => 'Sessione scaduta o non valida. Ricarica la pagina e riprova.',
	'PR_INVALID_PATH'	=> 'Caratteri non validi rilevati per l\'icona ID %s.',
]);
