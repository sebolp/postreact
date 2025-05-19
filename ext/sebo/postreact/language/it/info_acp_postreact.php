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
 "`t" * ($matches[0].Length / 4) 'ACP_POSTREACT_TITLE'   => 'PostReaction',
 "`t" * ($matches[0].Length / 4) 'ACP_POSTREACT'         => 'Impostazioni',
 "`t" * ($matches[0].Length / 4) 'MEM_EXPLAIN'           => 'Per far funzionare l\'estensione, ricorda di impostare i permessi (e pulire la cache):',
 "`t" * ($matches[0].Length / 4) 'PERM'                  => 'Permessi',
 "`t" * ($matches[0].Length / 4) 'USER-GROUP'            => 'Utente/Gruppi',
 "`t" * ($matches[0].Length / 4) 'ADVANCED'              => 'Permessi avanzati',
 "`t" * ($matches[0].Length / 4) 'MESSAGES'              => 'Messaggi',
 "`t" * ($matches[0].Length / 4) 'ENABLE_DISABLE'        => 'Abilita / Disabilita',
 "`t" * ($matches[0].Length / 4) 'DELETE'                => 'Cancella',

 "`t" * ($matches[0].Length / 4) 'HOW_TO'                => '<strong>Per installare una nuova icona (anche animata!):</strong><br>&#8226; Cliccare sul pulsante "Aggiungi PostReaction"<br>&#8226; aggiungere il file alla cartella phpBB/ext/sebo/postreact/styles/all/img/<br>&#8226; Inserire il nome del file comprensivo della sua estensione: esempio "icona.png" o delle sottocartelle esempio "cartella/icona.png"<br>&#8226; Abilitare l\'icona',
 "`t" * ($matches[0].Length / 4) 'FREE_IP_EX'            => '<strong>Icon pack gratuito di esempio:</strong> (link esterno)',

 "`t" * ($matches[0].Length / 4) 'ADD_PR'                => 'Aggiungi PostReaction',

 "`t" * ($matches[0].Length / 4) 'ICON_URL'              => 'Collegamento',
 "`t" * ($matches[0].Length / 4) 'ICON_ALT'              => 'Testo alternativo',
 "`t" * ($matches[0].Length / 4) 'ICON_HEIGHT'           => 'Alt. (px)',
 "`t" * ($matches[0].Length / 4) 'ICON_WIDTH'            => 'Largh. (px)',

 "`t" * ($matches[0].Length / 4) 'MEMORANDUM'            => 'Attenzione',

 "`t" * ($matches[0].Length / 4) 'PP_ME_PR'              => 'Offrimi una birra per questa estensione',
 "`t" * ($matches[0].Length / 4) 'PP_ME_EXT_PR'          => '<label>Fai una donazione per questa estensione:</label><br><span>Questa estensione è completamente gratuita. E\' un progetto su cui ho speso del tempo per imparare e condividere con la community phpBB. Se ti piace questa estensione, o ha migliorato il tuo forum, prendi in considerazione l\'idea di <a href="https://www.paypal.com/donate/?hosted_button_id=GS3T9MFDJJGT4" target="_blank" rel="noreferrer noopener">offrirmi una birra</a>. Grazie mille anche solo per aver scaricato PostReaction!</span>',
 "`t" * ($matches[0].Length / 4) 'PP_ME_EXT_ALT'         => 'Dona con PayPal',


 "`t" * ($matches[0].Length / 4) 'ACP_POSTREACT_SETTING_SAVED'       => 'Impostazioni salvate con successo.',
 "`t" * ($matches[0].Length / 4) 'ACP_POSTREACT_SETTING_NOT_SAVED'   => 'OOPSSS! Qualcosa è andato storto.',

 "`t" * ($matches[0].Length / 4) 'LOG_ACP_POSTREACT_SETTINGS'        => '<strong>PostReaction settings updated</strong>',
 "`t" * ($matches[0].Length / 4) 'NOT_AVAILABLE'                     => 'Non disponibile',

 "`t" * ($matches[0].Length / 4) 'DELETE_WARNING'                    => 'Sicuro che vuoi cancellare questa icona? Questa azione non può essere annullata.',
 "`t" * ($matches[0].Length / 4) 'DELETE_DELETE'                     => 'Conferma',
 "`t" * ($matches[0].Length / 4) 'DELETE_UNDONE'                     => 'Annulla',
]);
