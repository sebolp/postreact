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

/**
 * PostReaction Notification class.
 */
class postreact_notification extends \phpbb\notification\type\base
{
	/** @var \phpbb\controller\helper */
	protected $helper;
	/** @var \phpbb\user_loader */
	protected $user_loader;

	/**
	 * Set the controller helper
	 *
	 * @param \phpbb\controller\helper $helper
	 *
	 * @return void
	 */
	public function set_controller_helper(\phpbb\controller\helper $helper)
	{
		$this->helper = $helper;
	}
	/**
	 * Set user loader.
	 *
	 * @param \phpbb\user_loader  $user_loader  User loader object
	 * @return void
	 */
	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	/**
	 * Get notification type name
	 *
	 * @return string
	 */
	public function get_type()
	{
		return 'sebo.postreact.notification.type.postreact_notification';
	}

	/**
	 * Notification option data (for outputting to the user)
	 *
	 * @var bool|array False if the service should use it's default data
	 * 					Array of data (including keys 'id', 'lang', and 'group')
	 * lang 4 notification type
	 * group 4 category under wich will be displayed notification
	 */
	public static $notification_option = [
		'lang'		=> 'NOTIFICATION_TYPE_POSTREACT',
		'group'		=> 'NOTIFICATION_GROUP_POSTING',
	];

	/**
	 * Is this type available to the current user (defines whether or not it will be shown in the UCP Edit notification options)
	 *
	 * @return bool True/False whether or not this is available to the user
	 */
	public function is_available()
	{
		return true;
	}

	/**
	 * Get the id of the notification
	 *
	 * @param array $data The type specific data
	 *
	 * @return int Id of the notification
	 */
	public static function get_item_id($data)
	{
		return $data['PR_N_item_id'];
	}

	/**
	 * Get the id of the parent
	 *
	 * @param array $data The type specific data
	 *
	 * @return int Id of the parent
	 */
	public static function get_item_parent_id($data)
	{
		return $data['PR_N_sender_id'];
	}

	/**
	 * Find the users who want to receive notifications
	 *
	 * @param array $data The type specific data
	 * @param array $options Options for finding users for notification
	 * 		ignore_users => array of users and user types that should not receive notifications from this type because they've already been notified
	 * 						e.g.: [2 => [''], 3 => ['', 'email'], ...]
	 *
	 * @return array
	 */
	public function find_users_for_notification($data, $options = [])
	{
		return $this->check_user_notification_options([$data['PR_N_user_id']], $options);
	}

	/**
	 * Users needed to query before this notification can be displayed
	 *
	 * @return array Array of user_ids
	 */
	public function users_to_query()
	{
		return [$this->get_data('PR_N_sender_id')];
	}

	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('PR_N_sender_id'), false, true);
	}

	/**
	 * Get the HTML formatted title of this notification
	 *
	 * @return string
	 */
	public function get_title()
	{
		if ($this->get_data('PR_N_user_colour') != null)
		{
			$PR_N_username_full = '<strong style="color:#'.$this->get_data('PR_N_user_colour').'">'.$this->get_data('PR_N_username').'</strong>';
		}
		else
		{
			$PR_N_username_full = $this->get_data('PR_N_username');
		}
		return $this->language->lang('SEBO_POSTREACT_NOTIFICATION', $this->get_data('PR_N_icon'), $PR_N_username_full, $this->get_data('PR_N_post_title'));
	}

	/**
	 * Get the url to this item
	 *
	 * @return string URL
	 */
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'viewtopic.' . $this->php_ext, "p={$this->get_data('PR_N_post_id')}#p{$this->get_data('PR_N_post_id')}");
	}

	/**
	 * Get email template
	 *
	 * @return string|bool
	 */
	public function get_email_template()
	{
		return false;
	}


	/**
	 * Get email template variables
	 *
	 * @return array
	 */
	public function get_email_template_variables()
	{
		return [];
	}

	/**
	 * Function for preparing the data for insertion in an SQL query
	 * (The service handles insertion)
	 *
	 * @param array $data The type specific data
	 * @param array $pre_create_data Data from pre_create_insert_array()
	 */
	public function create_insert_array($data, $pre_create_data = [])
	{
		$this->set_data('PR_N_sender_id', $data['PR_N_sender_id']);
		$this->set_data('PR_N_post_id', $data['PR_N_post_id']);
		$this->set_data('PR_N_topic_id', $data['PR_N_topic_id']);
		$this->set_data('PR_N_username', $data['PR_N_username']);
		$this->set_data('PR_N_post_title', $data['PR_N_post_title']);
		$this->set_data('PR_N_user_colour', $data['PR_N_user_colour']);
		$this->set_data('PR_N_icon', $data['PR_N_icon']);

		parent::create_insert_array($data, $pre_create_data);
	}
}
