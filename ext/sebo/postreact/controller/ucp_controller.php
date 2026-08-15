<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\controller;

/**
 * PostReaction UCP controller: "notify me once per post" preference.
 */
class ucp_controller
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	protected $user;
	/** @var \phpbb\language\language */
	protected $language;
	/** @var \phpbb\request\request */
	protected $request;
	/** @var \phpbb\template\template */
	protected $template;
	/** @var string Custom form action */
	protected $u_action;

	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		$user,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template
	)
	{
		$this->db = $db;
		$this->user = $user;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
	}

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}

	public function display_options()
	{
		$this->language->add_lang('common', 'sebo/postreact');

		add_form_key('sebo_postreact_ucp');

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('sebo_postreact_ucp'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action));
			}

			$notify_mode = $this->request->variable('postreact_notify_mode', 0) ? 1 : 0;

			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_postreact_notify_mode = ' . (int) $notify_mode . '
				WHERE user_id = ' . (int) $this->user->data['user_id'];
			$this->db->sql_query($sql);

			$this->user->data['user_postreact_notify_mode'] = $notify_mode;

			meta_refresh(3, $this->u_action);
			trigger_error($this->language->lang('PREFERENCES_UPDATED') . '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>'));
		}

		$this->template->assign_vars([
			'S_POSTREACT_NOTIFY_MODE'	=> (int) $this->user->data['user_postreact_notify_mode'],
			'TITLE_EXPLAIN'				=> $this->language->lang('UCP_POSTREACT_EXPLAIN'),
			'U_ACTION'					=> $this->u_action,
		]);
	}
}
