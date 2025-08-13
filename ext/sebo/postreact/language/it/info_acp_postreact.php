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
	/* > 1.3.6 */
	'ACP_POSTREACT_PURGE'	=> 'P.R. pulizia DB',
	'ACP_POSTREACT_PURGE_T'	=> 'PostReaction pulizia',
	'PURGE_EXPLAIN'			=> 'Per prevenire rimozioni accidentali di reazioni, queste non verranno cancellate al momento della classica disinistallazione: visita il modulo "P.R. pulizia DB" prima di disinstallare l\'estensione!',
	'PURGE_PAGE_EXPLAIN'	=> 'Benvenuti nel sistema di eliminazione di PostReaction. È stato creato per evitare la rimozione indesiderata di icone e reazioni durante la disinstallazione/aggiornamento dell\'estensione.<br>Se utilizzi Disabilita/Elimina dati dal gestore estensioni senza utilizzare questo modulo, le reazioni non verranno eliminate.<br>Questa pagina deve essere utilizzata per modificare, controllare e rimuovere le reazioni dai tuoi post.<br>Usala prima di disinstallare l\'estensione per eliminare correttamente le tabelle del database. <strong>Queste operazioni non possono essere annullate!</strong>.<br>Potrebbe essere necessario eliminare manualmente le tabelle dal database se desideri una rimozione completa.',
	'PR_PS_S1'				=> 'Sincronizza il database',
	'PR_PS_S1_EXPLAIN'		=> 'Utilizza questa opzione per sincronizzare le tue reazioni e i tuoi post. Questa opzione controlla se la reazione è ancora correlata a un post esistente (ad esempio, potrebbe essere stata eliminato da un moderatore o da un\'eliminazione automatica). Se non viene trovato alcun post corrispondente, la reazione verrà eliminata.<br>Le statistiche utente vengono aggiornate automaticamente.',
	'PR_PS_S1_EXPLAIN_SUB'	=> 'A seconda della dimensione del tuo database, questa operazione potrebbe richiedere del tempo. Se fallisce, verifica le impostazioni di timeout del tuo server.',
	'PR_PS_S2'				=> 'Cancella le reazioni',
	'PR_PS_S2_EXPLAIN'		=> 'Usa questa opzione per eliminare le reazioni. Esegui questa operazione prima di disinstallare l\'estensione dal gestore estensioni. Perderai tutte le tue reazioni.',
	'PR_PS_S3'				=> 'Cancella le icone',
	'PR_PS_S3_EXPLAIN'		=> 'Usa questa opzione per eliminare le icone/emoji. Esegui questa operazione prima di disinstallare l\'estensione dal gestore estensioni. Perderai tutte le tue icone.',
	'PURGE_IT'				=> 'Cancella i dati',
	'SYNC_IT'				=> 'Sincronizza i dati',
	'PR_SYNCSYSTEM_UPDATED'	=> 'Sincronizzazione del database riuscita!<br>Sono state rimosse <strong>%d</strong> reazioni.<br><em>La query ha impiegato %s secondi.</em>',
	'PR_PURGESYSTEM_UPDATED'=> 'Il database è stato svuotato con successo.<br>Sono state rimosse <strong>%d</strong> reazioni.<br><em>La query ha impiegato %s secondi.</em>',
	'PR_PURGEICOSYSTEM_UPDATED'	=> 'Il database è stato svuotato con successo.<br>Sono state rimosse <strong>%d</strong> icone.<br><em>La query ha impiegato %s secondi.</em>',
]);
