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
 "`t" * ($matches[0].Length / 4) 'ACP_POSTREACT'         => 'Settings',
 "`t" * ($matches[0].Length / 4) 'MEM_EXPLAIN'           => 'To enable PostReaction, remember to setup permissions (and purge the cache!):',
 "`t" * ($matches[0].Length / 4) 'PERM'                  => 'Permissions',
 "`t" * ($matches[0].Length / 4) 'USER-GROUP'            => 'User/Group',
 "`t" * ($matches[0].Length / 4) 'ADVANCED'              => 'Advanced permissions',
 "`t" * ($matches[0].Length / 4) 'MESSAGES'              => 'Messages',
 "`t" * ($matches[0].Length / 4) 'ENABLE_DISABLE'        => 'Enable / Disable',
 "`t" * ($matches[0].Length / 4) 'DELETE'                => 'Delete',

 "`t" * ($matches[0].Length / 4) 'HOW_TO'                => '<strong>To install a new emoji (animated too!):</strong><br>&#8226; Click the "Add PostReaction" button<br>&#8226; Add the file to the folder phpBB/ext/sebo/postreact/styles/all/img/<br>&#8226; Enter the file name including its extension: e.g., "icon.png" and path if needed e.g. "folder/icon.png"<br>&#8226; Enable the emoji',
 "`t" * ($matches[0].Length / 4) 'FREE_IP_EX'            => '<strong>Free icon pack example:</strong> (external link)',

 "`t" * ($matches[0].Length / 4) 'ADD_PR'                => 'Add PostReaction',

 "`t" * ($matches[0].Length / 4) 'ICON_URL'              => 'Name/link',
 "`t" * ($matches[0].Length / 4) 'ICON_ALT'              => 'Alternative text',
 "`t" * ($matches[0].Length / 4) 'ICON_HEIGHT'           => 'Height (px)',
 "`t" * ($matches[0].Length / 4) 'ICON_WIDTH'            => 'Width (px)',

 "`t" * ($matches[0].Length / 4) 'MEMORANDUM'            => 'Watchout',

 "`t" * ($matches[0].Length / 4) 'PP_ME_PR'              => 'Buy me a beer for creating this extension',
 "`t" * ($matches[0].Length / 4) 'PP_ME_EXT_PR'          => '<label>Make a donation for this extension:</label><br><span>This extension is completely free. It is a project that I spend my time on for the enjoyment and use of the phpBB community. If you enjoy using this extension, or if it has benefited your forum, please consider <a href="https://www.paypal.com/donate/?hosted_button_id=GS3T9MFDJJGT4" target="_blank" rel="noreferrer noopener">buying me a beer</a>. It would be greatly appreciated. Thank you for downloading PostReaction!</span>',
 "`t" * ($matches[0].Length / 4) 'PP_ME_EXT_ALT'         => 'Donate via PayPal',

 "`t" * ($matches[0].Length / 4) 'ACP_POSTREACT_SETTING_SAVED'       => 'Settings saved.',
 "`t" * ($matches[0].Length / 4) 'ACP_POSTREACT_SETTING_NOT_SAVED'   => 'OOPSSS! something wrong.',

 "`t" * ($matches[0].Length / 4) 'LOG_ACP_POSTREACT_SETTINGS'        => '<strong>PostReaction settings updated</strong>',
 "`t" * ($matches[0].Length / 4) 'NOT_AVAILABLE'                     => 'Still not available',

 "`t" * ($matches[0].Length / 4) 'DELETE_WARNING'                    => 'Are you sure you want to delete this icon? This cannot be undone.',
 "`t" * ($matches[0].Length / 4) 'DELETE_DELETE'                     => 'Confirm',
 "`t" * ($matches[0].Length / 4) 'DELETE_UNDONE'                     => 'Cancel',
]);
