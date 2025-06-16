<?php
/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\notification\type;

class postreact_notification extends \phpbb\notification\type\base
{
	/** @var \phpbb\controller\helper */
	protected $helper;
	/** @var \phpbb\user_loader */
	protected $user_loader;

	public function set_controller_helper(\phpbb\controller\helper $helper)
	{
		$this->helper = $helper;
	}

	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	public function get_type()
	{
		return 'sebo.postreact.notification.type.postreact_notification';
	}

	public static $notification_option = [
		'lang'		=> 'NOTIFICATION_TYPE_POSTREACT',
		'group'		=> 'NOTIFICATION_GROUP_POSTING',
	];

	public function is_available()
	{
		return true;
	}

	public static function get_item_id($data)
	{
		return $data['PR_N_item_id'];
	}

	public static function get_item_parent_id($data)
	{
		return 0;
	}

	public function find_users_for_notification($data, $options = [])
	{
		return $this->check_user_notification_options([$data['PR_N_user_id']], $options);
	}

	public function users_to_query()
	{
		return [$this->get_data('PR_N_sender_id')];
	}

	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('PR_N_sender_id'), false, true);
	}

	public function get_title()
	{
		return '<img src="'.$this->get_data('PR_N_icon').'" style="width:32px !important;height:32px !important;"> ' . $this->language->lang('SEBO_POSTREACT_NOTIFICATION', $this->user_loader->get_username($this->get_data('PR_N_sender_id'), 'no_profile'), $this->get_data('PR_N_post_title'));
	}

	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'viewtopic.' . $this->php_ext, "p={$this->get_data('PR_N_post_id')}#p{$this->get_data('PR_N_post_id')}");
	}

	public function get_email_template()
	{
		return false;
	}

	public function get_email_template_variables()
	{
		return [];
	}

	public function create_insert_array($data, $pre_create_data = [])
	{
		$this->set_data('PR_N_sender_id', $data['PR_N_sender_id']);
		$this->set_data('PR_N_post_id', $data['PR_N_post_id']);
		$this->set_data('PR_N_topic_id', $data['PR_N_topic_id']);
		$this->set_data('PR_N_username', $data['PR_N_username']);
		$this->set_data('PR_N_post_title', $data['PR_N_post_title']);
		$this->set_data('PR_N_icon', $data['PR_N_icon']);

		parent::create_insert_array($data, $pre_create_data);
	}
}
