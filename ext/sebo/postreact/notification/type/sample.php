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
class sample extends \phpbb\notification\type\base
{
 '`t' * ($matches[0].Length / 4) /** @var \phpbb\controller\helper */
 '`t' * ($matches[0].Length / 4) protected $helper;
/**
 "`t" * ($matches[0].Length / 4)  * Set the controller helper
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @param \phpbb\controller\helper $helper
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @return void
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function set_controller_helper(\phpbb\controller\helper $helper)
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) $this->helper = $helper;
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Get notification type name
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @return string
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function get_type()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) return 'sebo.postreact.notification.type.sample';
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Notification option data (for outputting to the user)
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @var bool|array False if the service should use it's default data
 "`t" * ($matches[0].Length / 4)  *                  Array of data (including keys 'id', 'lang', and 'group')
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public static $notification_option = [
 '`t' * ($matches[0].Length / 4) 'lang'  => 'NOTIFICATION_TYPE_POSTREACT',
 '`t' * ($matches[0].Length / 4) ];
/**
 "`t" * ($matches[0].Length / 4)  * Is this type available to the current user (defines whether or not it will be shown in the UCP Edit notification options)
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @return bool True/False whether or not this is available to the user
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function is_available()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) return false;
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Get the id of the notification
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @param array $data The type specific data
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @return int Id of the notification
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public static function get_item_id($data)
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) return $data['notification_id'];
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Get the id of the parent
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @param array $data The type specific data
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @return int Id of the parent
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public static function get_item_parent_id($data)
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) // No parent
 '`t' * ($matches[0].Length / 4) return 0;
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Find the users who want to receive notifications
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @param array $data The type specific data
 "`t" * ($matches[0].Length / 4)  * @param array $options Options for finding users for notification
 "`t" * ($matches[0].Length / 4)  *      ignore_users => array of users and user types that should not receive notifications from this type because they've already been notified
 "`t" * ($matches[0].Length / 4)  *                      e.g.: [2 => [''], 3 => ['', 'email'], ...]
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @return array
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function find_users_for_notification($data, $options = [])
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) // Return an array of users to be notified, storing the user_ids as the array keys
 '`t' * ($matches[0].Length / 4) return [];
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Users needed to query before this notification can be displayed
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @return array Array of user_ids
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function users_to_query()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) return [];
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Get the HTML formatted title of this notification
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @return string
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function get_title()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) return $this->language->lang('SEBO_POSTREACT_NOTIFICATION');
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Get the url to this item
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @return string URL
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function get_url()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) return $this->helper->route('sebo_postreact_controller', $this->get_data('postreact_sample_name'));
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Get email template
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @return string|bool
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function get_email_template()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) return false;
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Get email template variables
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @return array
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function get_email_template_variables()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) return [];
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Function for preparing the data for insertion in an SQL query
 "`t" * ($matches[0].Length / 4)  * (The service handles insertion)
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @param array $data The type specific data
 "`t" * ($matches[0].Length / 4)  * @param array $pre_create_data Data from pre_create_insert_array()
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function create_insert_array($data, $pre_create_data = [])
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) $this->set_data('postreact_sample_name', $data['postreact_sample_name']);
 '`t' * ($matches[0].Length / 4) parent::create_insert_array($data, $pre_create_data);
 '`t' * ($matches[0].Length / 4) }
}
