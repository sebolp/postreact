<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\ucp;

/**
 * PostReaction UCP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var \sebo\postreact\controller\ucp_controller $ucp_controller */
		$ucp_controller = $phpbb_container->get('sebo.postreact.controller.ucp');

		$this->tpl_name = 'ucp_postreact_body';
		$this->page_title = 'UCP_POSTREACT_TITLE';

		$ucp_controller->set_page_url($this->u_action);
		$ucp_controller->display_options();
	}
}
