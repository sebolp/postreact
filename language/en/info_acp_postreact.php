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
	'ACP_POSTREACT_TITLE'	=> 'PostReaction',
	'ACP_POSTREACT'			=> 'Settings',
	'MEM_EXPLAIN'			=> 'To enable PostReaction, remember to setup permissions (and purge the cache!):',
	'PERM'					=> 'Permissions',
	'USER-GROUP'			=> 'User/Group',
	'ADVANCED'				=> 'Advanced permissions',
	'MESSAGES'				=> 'Messagges',
	'ENABLE_DISABLE'		=> 'Enable / Disable',
	
	'HOW_TO'				=> '<strong>To install a new emoji (animated too!):</strong><br>&#8226; Click the "Add PostReaction" button<br>&#8226; Add the file to the folder phpBB/ext/sebo/postreact/styles/all/img/<br>&#8226; Enter the file name including its extension: e.g., "icon.png" and path if needed e.g. "folder/icon.png"<br>&#8226; Enable the emoji',
	'FREE_IP_EX'			=> '<strong>Free icon pack example:</strong> (external link)',
	
	'ADD_PR'				=> 'Add PostReaction',
	
	'ICON_URL'				=> 'Name/link',
	'ICON_ALT'				=> 'Alternative text',
	'ICON_HEIGHT'			=> 'Height (px)',
	'ICON_WIDTH'			=> 'Width (px)',
	
	'MEMORANDUM'			=> 'Watchout',
	
	'PP_ME'					=> 'Buy me a beer for creating this extension',
	'PP_ME_EXT'				=> '<label>Make a donation for this extension:</label><br><span>This extension is completely free. It is a project that I spend my time on for the enjoyment and use of the phpBB community. If you enjoy using this extension, or if it has benefited your forum, please consider <a href="https://www.paypal.com/donate/?hosted_button_id=GS3T9MFDJJGT4" target="_blank" rel="noreferrer noopener">buying me a beer</a>. It would be greatly appreciated.</span>',
	
	'ACP_POSTREACT_SETTING_SAVED'		=> 'Settings saved.',
	'ACP_POSTREACT_SETTING_NOT_SAVED'	=> 'OOPSSS! something wrong.',

	'LOG_ACP_POSTREACT_SETTINGS'		=> '<strong>PostReaction settings updated</strong>',
]);
