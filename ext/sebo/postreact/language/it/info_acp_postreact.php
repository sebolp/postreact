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
	'ACP_POSTREACT_TITLE'	=> 'PostReaction',
	'ACP_POSTREACT'			=> 'Impostazioni',
	'MEM_EXPLAIN'			=> 'Per far funzionare l\'estensione, ricorda di impostare i permessi (e pulire la cache):',
	'PERM'					=> 'Permessi',
	'USER-GROUP'			=> 'Utente/Gruppi',
	'ADVANCED'				=> 'Permessi avanzati',
	'MESSAGES'				=> 'Messaggi',
	'ENABLE_DISABLE'		=> 'Abilita / Disabilita',
	'DELETE'				=> 'Cancella',
	'HOW_TO'				=> '<strong>Per installare una nuova icona (anche animata!):</strong><br>&#8226; Cliccare sul pulsante "Aggiungi PostReaction"<br>&#8226; aggiungere il file alla cartella phpBB/ext/sebo/postreact/styles/all/img/<br>&#8226; Inserire il nome del file comprensivo della sua estensione: esempio "icona.png" o delle sottocartelle esempio "cartella/icona.png"<br>&#8226; Abilitare l\'icona',
	'FREE_IP_EX'			=> '<strong>Icon pack gratuito di esempio:</strong> (link esterno)',
	'ADD_PR'				=> 'Aggiungi PostReaction',
	'ICON_URL'				=> 'Collegamento',
	'ICON_ALT'				=> 'Testo alternativo',
	'ICON_HEIGHT'			=> 'Alt. (px)',
	'ICON_WIDTH'			=> 'Largh. (px)',
	'MEMORANDUM'			=> 'Attenzione',
	'PP_ME_PR'				=> 'Offrimi una birra per questa estensione',
	'PP_ME_EXT_PR'			=> '<label>Fai una donazione per questa estensione:</label><br><span>Questa estensione è completamente gratuita. E\' un progetto su cui ho speso del tempo per imparare e condividere con la community phpBB. Se ti piace questa estensione, o ha migliorato il tuo forum, prendi in considerazione l\'idea di <a href="https://www.paypal.com/donate/?hosted_button_id=GS3T9MFDJJGT4" target="_blank" rel="noreferrer noopener">offrirmi una birra</a>. Grazie mille anche solo per aver scaricato PostReaction!</span>',
	'PP_ME_EXT_ALT'			=> 'Dona con PayPal',
	'ACP_POSTREACT_SETTING_SAVED'		=> 'Impostazioni salvate con successo.',
	'ACP_POSTREACT_SETTING_NOT_SAVED'	=> 'OOPSSS! Qualcosa è andato storto.',
	'LOG_ACP_POSTREACT_SETTINGS'		=> '<strong>PostReaction settings updated</strong>',
	'NOT_AVAILABLE'						=> 'Non disponibile',
	'DELETE_WARNING'					=> 'Sicuro che vuoi cancellare questa icona? Questa azione non può essere annullata.',
	'DELETE_DELETE'						=> 'Conferma',
	'DELETE_UNDONE'						=> 'Annulla',
]);
