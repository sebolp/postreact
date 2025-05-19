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
    /** @var \phpbb\controller\helper */
    protected $helper;
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
     * Get notification type name
     *
     * @return string
     */
    public function get_type()
    {
        return 'sebo.postreact.notification.type.sample';
    }

    /**
     * Notification option data (for outputting to the user)
     *
     * @var bool|array False if the service should use it's default data
     *                  Array of data (including keys 'id', 'lang', and 'group')
     */
    public static $notification_option = [
    'lang'  => 'NOTIFICATION_TYPE_POSTREACT',
    ];
/**
     * Is this type available to the current user (defines whether or not it will be shown in the UCP Edit notification options)
     *
     * @return bool True/False whether or not this is available to the user
     */
    public function is_available()
    {
        return false;
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
        return $data['notification_id'];
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
        // No parent
        return 0;
    }

    /**
     * Find the users who want to receive notifications
     *
     * @param array $data The type specific data
     * @param array $options Options for finding users for notification
     *      ignore_users => array of users and user types that should not receive notifications from this type because they've already been notified
     *                      e.g.: [2 => [''], 3 => ['', 'email'], ...]
     *
     * @return array
     */
    public function find_users_for_notification($data, $options = [])
    {
        // Return an array of users to be notified, storing the user_ids as the array keys
        return [];
    }

    /**
     * Users needed to query before this notification can be displayed
     *
     * @return array Array of user_ids
     */
    public function users_to_query()
    {
        return [];
    }

    /**
     * Get the HTML formatted title of this notification
     *
     * @return string
     */
    public function get_title()
    {
        return $this->language->lang('SEBO_POSTREACT_NOTIFICATION');
    }

    /**
     * Get the url to this item
     *
     * @return string URL
     */
    public function get_url()
    {
        return $this->helper->route('sebo_postreact_controller', $this->get_data('postreact_sample_name'));
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
        $this->set_data('postreact_sample_name', $data['postreact_sample_name']);
        parent::create_insert_array($data, $pre_create_data);
    }
}
