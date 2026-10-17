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

	'NOT_INSERTED_VALUE'	=> 'HOPPÁ! A reakció nem lett hozzáadva!',
	'INSERTED_VALUE'	=> 'Reakció hozzáadva!',
	'DELETED_VALUE'		=> 'Reakció törölve!',
	'LOGIN_TO_REACT'	=> 'Jelentkezz be a reakcióadáshoz!',

	'SEBO_POSTREACT_NOTIFICATION'	=> '%s reagált a „%s” című hozzászólásodra',
	'ALREADY_REACTED'	=> 'Már reagáltál erre a hozzászólásra. Kattints a reakció törléséhez.',
	'REACTION_SENT_LIST'	=> 'A felhasználó által küldött összes reakció',
	'REACTION_RECEIVED_LIST'	=> 'A felhasználó által kapott összes reakció',
	'POSTREACT_SUMMARY'			=> 'ÖSSZEGZÉS',
	'REACT_TO_POST'				=> 'Reagálj erre a hozzászólásra',
	/* > 2.1 */
	'CANNOT_SELF_REACT'			=> 'Saját hozzászólásra nem reagálhatsz!',
	'PR_EXTENSION_NAME'			=> 'Hozzászólás-reakciók',
	/* > 2.2 */
	'POSTREACTION_AJAX_ERROR'	=> 'Hiba az AJAX-kérés során',
	'POSTREACTION_JSON_ERROR'	=> 'Hiba a szervertől érkező JSON-válasz feldolgozása közben',
	/* > 2.3 */
	'SEARCH_USER_REACTIONS_RECEIVED' => '%s hozzászólás-reakció, amelyet %s kapott',
	'SEARCH_USER_REACTIONS'     => '%s hozzászólás-reakció, amelyet %s adott',
	/* > 2.5.1 */
	'PR_NO_REACTION_TO_REMOVE' => 'Nincs eltávolítható reakció',
	'PR_ERROR_REMOVING_REACTION' => 'Hiba a reakció eltávolításakor',
	'PR_CLOSE' => 'Bezárás',
	/* > 2.5.2 */
	'PR_BUTTON_TEXT' => 'Reagálás',
	'PR_DISPLAY_BUTTON_POSITION'  => 'A reakciógomb pozíciójának meghatározása a hozzászóláson belül',
	'BUTT_POSITION_UP' => 'Fent, a hozzászólás gombjaival egy sorban',
	'BUTT_POSITION_LOW' => 'Lent, gombstílusban',
	'BUTT_POSITION_EMOJI_LEVEL' => 'A reakciókkal egy sorban',
	'PR_MEMBERLIST_NO_REACTIONS_SENT' => 'Nem küldött reakciót a felhasználó',
	'PR_MEMBERLIST_NO_REACTIONS_RECEIVED' => 'Nem kapott reakciót a felhasználó',
	/* > 2.6.0 */
	'PR_SORT_BY_REACTION' => 'Reakciók',
	'POSTREACTION_CSRF_ERROR' => 'Érvénytelen vagy lejárt munkamenet. Frissítsd az oldalt, és próbáld újra.',
	'PR_INVALID_PATH'	=> 'Érvénytelen név a %s ikonazonosítóhoz. Kérjük, távolítsd el a pontokat a perjelek elől vagy mögül.',
	'PR_NO_VIEW_FORUM'	=> 'Nem tekintheted meg ezt a fórumot.',
	'PR_NO_POST'	=> 'Nem tekintheted meg ezt a hozzászólást.',
]);
