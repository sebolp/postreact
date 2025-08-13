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
class purge_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \sebo\postreact\controller\acp_controller $acp_controller */
		$acp_controller = $phpbb_container->get('sebo.postreact.controller.acp');

		// Load a template from adm/style for our ACP page
		$this->tpl_name = 'acp_postreact_purge';

		// Set the page title for our ACP page
		$this->page_title = 'ACP_POSTREACT_TITLE';

		// Make the $u_action url available in our ACP controller
		$acp_controller->set_page_url($this->u_action);

		// Load the display options handle in our ACP controller
		$acp_controller->display_options();
	}
}
