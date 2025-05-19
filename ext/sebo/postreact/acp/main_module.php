<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\acp;

/**
 * PostReaction ACP module.
 */
class main_module
{
 '`t' * ($matches[0].Length / 4) public $page_title;
 '`t' * ($matches[0].Length / 4) public $tpl_name;
 '`t' * ($matches[0].Length / 4) public $u_action;
/**
 "`t" * ($matches[0].Length / 4)  * Main ACP module
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @param int    $id   The module ID
 "`t" * ($matches[0].Length / 4)  * @param string $mode The module mode (for example: manage or settings)
 "`t" * ($matches[0].Length / 4)  * @throws \Exception
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function main($id, $mode)
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) global $phpbb_container;
/** @var \sebo\postreact\controller\acp_controller $acp_controller */
 '`t' * ($matches[0].Length / 4) $acp_controller = $phpbb_container->get('sebo.postreact.controller.acp');
// Load a template from adm/style for our ACP page
 '`t' * ($matches[0].Length / 4) $this->tpl_name = 'acp_postreact_body';
// Set the page title for our ACP page
 '`t' * ($matches[0].Length / 4) $this->page_title = 'ACP_POSTREACT_TITLE';
// Make the $u_action url available in our ACP controller
 '`t' * ($matches[0].Length / 4) $acp_controller->set_page_url($this->u_action);
// Load the display options handle in our ACP controller
 '`t' * ($matches[0].Length / 4) $acp_controller->display_options();
 '`t' * ($matches[0].Length / 4) }
}
