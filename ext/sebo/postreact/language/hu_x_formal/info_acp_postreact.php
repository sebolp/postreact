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
	'ACP_POSTREACT_TITLE'	=> 'Hozzászólás-reakciók',
	'ACP_POSTREACT'			=> 'Beállítások',
	'MEM_EXPLAIN'			=> 'A Hozzászólás-reakciók engedélyezéséhez ne felejtse el beállítani a jogosultságokat (és üríteni a gyorsítótárat!):',
	'PERM'					=> 'Jogosultságok',
	'USER-GROUP'			=> 'Felhasználó/Csoport',
	'ADVANCED'				=> 'Haladó jogosultságok',
	'MESSAGES'				=> 'Üzenetek',
	'ENABLE_DISABLE'		=> 'Engedélyezés / Letiltás',
	'DELETE'				=> 'Törlés',
	'FREE_IP_EX'			=> '<strong>Példa ingyenes ikonkészletre:</strong> (külső hivatkozás)',
	'ADD_PR'				=> 'Hozzászólás-reakció hozzáadása',
	'ICON_ALT'				=> 'Alternatív szöveg',
	'ICON_HEIGHT'			=> 'Magasság (px)',
	'ICON_WIDTH'			=> 'Szélesség (px)',
	'MEMORANDUM'			=> 'Figyelem',
	'PR_PP_ME'				=> 'Vegyen nekem egy sört a kiterjesztés elkészítéséért',
	'PR_PP_ME_EXT'			=> '<label>Adományozzon a kiterjesztésért:</label><br><span>Ez a kiterjesztés teljesen ingyenes. Olyan projekt, amelyre azért fordítom az időmet, hogy a phpBB közösség örömére és hasznára legyen. Ha szívesen használja a kiterjesztést, vagy hasznosnak találta fórumán, kérem, fontolja meg, hogy <a href="https://www.paypal.com/donate/?hosted_button_id=GS3T9MFDJJGT4" target="_blank" rel="noreferrer noopener">vesz nekem egy sört</a>. Nagyra értékelném. Köszönöm, hogy letöltötte a Hozzászólás-reakciókat!</span>',
	'PR_PP_ME_EXT_ALT'			=> 'Adományozás PayPalon keresztül',
	'ACP_POSTREACT_SETTING_SAVED'		=> 'Beállítások mentve.',
	'ACP_POSTREACT_SETTING_NOT_SAVED'	=> 'HOPPÁ! Valami hiba történt.',
	'POSTREACT_FAILED_ICONS'			=> 'Menteni nem sikerült ikonok',
	'DELETE_WARNING'					=> 'Biztosan törölni szeretné ezt az ikont? A művelet nem vonható vissza.',
	'DELETE_DELETE'						=> 'Megerősítés',
	'DELETE_UNDONE'						=> 'Mégse',
	/* > 1.3.6 */
	'ACP_POSTREACT_PURGE'	=> 'H.r. Tisztítórendszer',
	'ACP_POSTREACT_PURGE_T'	=> 'Hozzászólás-reakciók tisztítórendszere',
	'PURGE_EXPLAIN'			=> 'A nem kívánt reakciók törlésének elkerülése érdekében látogassa meg a „H.r. Tisztítórendszer” modult a kiterjesztés eltávolítása előtt!',
	'PURGE_PAGE_EXPLAIN'	=> 'Üdvözöljük a Hozzászólás-reakciók tisztítórendszerében. Azért hoztuk létre, hogy elkerüljük az ikonok és reakciók nem kívánt törlését a kiterjesztés eltávolítása vagy frissítése során.<br>Ha e modul igénybevétele nélkül használja a „Letiltás” és „Adatok törlése” funkciót a kiterjesztéskezelőben, a reakciók nem törlődnek.<br>Ezt az oldalt kell használni a hozzászólásokhoz tartozó reakciók szerkesztésére, ellenőrzésére és eltávolítására.<br>Használja a kiterjesztés eltávolítása előtt az adatbázistáblák megfelelő törléséhez. <strong>Ezek a műveletek nem vonhatók vissza!</strong>.<br>Teljes eltávolítás esetén szükség lehet az adatbázistáblák kézi törlésére.',
	'PR_PS_S1'				=> 'Adatbázis szinkronizálása',
	'PR_PS_S1_EXPLAIN'		=> 'Használja ezt a lehetőséget a reakciók és a hozzászólások szinkronizálásához. Ez ellenőrzi, hogy a reakció továbbra is létező hozzászóláshoz kapcsolódik-e (pl. moderátor vagy automatikus tisztítás törölhette a hozzászólást). Ha nem található hozzászólás, a reakció törlődik.<br>A felhasználói statisztikák automatikusan frissülnek.',
	'PR_PS_S1_EXPLAIN_SUB'	=> 'Az adatbázis méretétől függően ez eltarthat egy kis ideig. Hiba esetén ellenőrizze a szerverbeállításokban az időtúllépési értéket.',
	'PR_PS_S2'				=> 'Reakció-adatbázis ürítése',
	'PR_PS_S2_EXPLAIN'		=> 'Használja ezt az opciót a hozzászólásokhoz kapcsolódó reakció-adatbázis teljes törléséhez. Ezt a kiterjesztéskezelőből történő eltávolítás előtt végezze el. Ezzel minden reakciót elveszít.',
	'PR_PS_S3'				=> 'Ikon-adatbázis ürítése',
	'PR_PS_S3_EXPLAIN'		=> 'Használja ezt az opciót az ikon/emoji-adatbázis teljes törléséhez. Ezt a kiterjesztéskezelőből történő eltávolítás előtt végezze el. Ezzel minden ikont elveszít.',
	'PURGE_IT'				=> 'Adatok törlése',
	'SYNC_IT'				=> 'Adatok szinkronizálása',
	'PR_SYNCSYSTEM_UPDATED'	=> 'Az adatbázis-szinkronizálás sikeres!<br><strong>%d</strong> reakció eltávolítva.<br><em>A lekérdezés %s másodpercig tartott.</em>',
	'PR_PURGESYSTEM_UPDATED' => 'Az adatbázis sikeresen ürítve.<br><strong>%d</strong> reakció eltávolítva.<br><em>A lekérdezés %s másodpercig tartott.</em>',
	'PR_PURGEICOSYSTEM_UPDATED' => 'Az adatbázis sikeresen ürítve.<br><strong>%d</strong> ikon eltávolítva.<br><em>A lekérdezés %s másodpercig tartott.</em>',
	/* > 2.1 */
	'CONFIG_SETTINGS'		=> 'Általános beállítások',
	'ICON_SETTINGS'			=> 'Ikonbeállítások',
	'ENABLE_SELF_REACTING'	=> 'Lehessen a felhasználóknak a saját hozzászólásaikra reakciót adni',
	/* > 2.1.1 */
	'DISPLAY_POSITION'		=> 'Reakciók igazítása a hozzászólásnál:',
	'DISPLAY_POSITION_R'	=> 'JOBBRA',
	'DISPLAY_POSITION_L'	=> 'BALRA',
	'SAVING_REQUIRED'		=> 'Ne felejtse el elküldeni az űrlapot a beállítások mentéséhez',
	/* >2.3 */
	'ICON_EMOJI'			=> 'Emoji',
	'HOW_TO'				=> '<strong>Új reakció telepítéséhez (akár animált is!):</strong><br>&#8226; Kattintson a „Hozzászólás-reakció hozzáadása” gombra<br>&#8226; Helyezze a fájlt a következő mappába: <code class="inline">%s</code> <br>&#8226; Adja meg a fájl nevét a kiterjesztéssel együtt: pl. <code class="inline">icon.png</code>, és szükség esetén az elérési utat is, pl. <code class="inline">folder/icon.png</code><br>&#8226; Adjon meg egy emojit a webes push-értesítésekhez és a keresési oldalakhoz (opcionális)<br>&#8226; Engedélyezze a reakciót',
	'FREE_EM_EX'            => '<strong>Példa ingyenes emoji-gyűjteményre:</strong> (külső hivatkozás)',
	'PR_PURGE_REACTION_FOLDER'    => 'Reakciófájlok mappájának ürítése',
	'PR_PURGE_REACTION_FOLDER_EXPLAIN' => 'Az összes reakció-képfájl törlése a szervermappából. Ez nem törli az adatbázis-hozzászólásoket, ezért az adatbázis ürítése után, de a kiterjesztés eltávolítása előtt használja. Ez a művelet nem vonható vissza!',
	'PR_PURGE_REACTION_FOLDER_EXPLAIN_AVOID' => 'Elkerülheti ezt a műveletet, ha manuálisan törli a reakciófájlokat (és -mappákat) a szerverről FTP-n vagy fájlkezelőn keresztül: a <code class="inline">%s</code> mappából az <code class="inline">images/</code> mappába.',
	'PR_PURGE_FOLDER_BUTTON'    => 'Fájlok és mappák törlése',
	'PR_PURGE_FOLDER_DONE'    => 'A fájlok és könyvtárak sikeresen törölve.',
	'PR_DIR_NOT_EXISTS_MSG' => 'A reakciómappa nem létezik a szerveren: <code class="inline">%s</code>. Kérjük, hozza létre, és töltse fel oda a reakcióképeket a Hozzászólás-reakciók kiterjesztés használatához. Máskülönben teljesen el kell távolítania a kiegészítőt, és az eredeti fájlokkal újra kell telepítenie azt..',
	'ICON_URL'				=> 'Könyvtár/Név – hivatkozás',
	/* >2.4 */
	'RETURN_ACP'			=> '<a href="%s">Vissza az ACP-modulhoz</a>',
	/* >2.5 */
	/* for translators: edited in 2.5.0 'HOW_TO' lang key line 74, added "and search pages" for emojis use */
	'PR_MOVE'		=> 'Sorrend',
	'PR_MOVE_UP'	=> 'Mozgatás feljebb',
	'PR_MOVE_DOWN'	=> 'Mozgatás lejjebb',
	/* >2.5.1 */
	'PR_PURGE_CONFIRM_OPERATION' => 'Biztos benne? Ez a művelet nem vonható vissza!',
	'PR_IF_NOT_PURGE_BEFORE' => 'Ha nem használja a „H.r. Tisztítórendszert”, az adatbázisban lévő adatok nem törlődnek, hogy megelőzze a nem kívánt eltávolítást.',
]);
