<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\controller;

/**
 * PostReaction ACP controller.
 */
class acp_controller
{
 '`t' * ($matches[0].Length / 4) /** @var \phpbb\language\language */
 '`t' * ($matches[0].Length / 4) protected $language;
/** @var \phpbb\request\request */
 '`t' * ($matches[0].Length / 4) protected $request;
/** @var \phpbb\template\template */
 '`t' * ($matches[0].Length / 4) protected $template;
/** @var \phpbb\user */
 '`t' * ($matches[0].Length / 4) protected $user;
/** @var string Custom form action */
 '`t' * ($matches[0].Length / 4) protected $u_action;

 '`t' * ($matches[0].Length / 4) protected $table_prefix;

 '`t' * ($matches[0].Length / 4) /** @var \phpbb\db\driver\driver_interface */
 '`t' * ($matches[0].Length / 4) protected $db;

 '`t' * ($matches[0].Length / 4) /** @var php_ext */
 '`t' * ($matches[0].Length / 4) protected $php_ext;
/**
 "`t" * ($matches[0].Length / 4)  * Constructor.
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @param \phpbb\language\language  $language   Language object
 "`t" * ($matches[0].Length / 4)  * @param \phpbb\log\log            $log        Log object
 "`t" * ($matches[0].Length / 4)  * @param \phpbb\request\request    $request    Request object
 "`t" * ($matches[0].Length / 4)  * @param \phpbb\template\template  $template   Template object
 "`t" * ($matches[0].Length / 4)  * @param \phpbb\user               $user       User object
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function __construct(\phpbb\language\language $language, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $table_prefix, \phpbb\db\driver\driver_interface $db, $php_ext)
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) $this->language = $language;
 '`t' * ($matches[0].Length / 4) $this->request  = $request;
 '`t' * ($matches[0].Length / 4) $this->template = $template;
 '`t' * ($matches[0].Length / 4) $this->user     = $user;
 '`t' * ($matches[0].Length / 4) $this->table_prefix = $table_prefix;
 '`t' * ($matches[0].Length / 4) $this->db       = $db;
 '`t' * ($matches[0].Length / 4) $this->php_ext = $php_ext;
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Display the options a user can configure for this extension.
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @return void
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function display_options()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) // Add our common language file
 '`t' * ($matches[0].Length / 4) $this->language->add_lang('common', 'sebo/postreact');
 '`t' * ($matches[0].Length / 4) $this->language->add_lang('permissions_postreact', 'sebo/postreact');
 '`t' * ($matches[0].Length / 4) $sid_pr = $this->request->variable('user_sid', \phpbb\request\request_interface::COOKIE);

 '`t' * ($matches[0].Length / 4) // ##
 '`t' * ($matches[0].Length / 4) // check if adding icon
 '`t' * ($matches[0].Length / 4) $add_pr = $this->request->variable('add_pr', 0, false);
 '`t' * ($matches[0].Length / 4) if ($add_pr === 1) {
 '`t' * ($matches[0].Length / 4) $sql_last = 'SELECT icon_id FROM `' . $this->table_prefix . 'sebo_postreact_icon` ORDER BY `' . $this->table_prefix . 'sebo_postreact_icon`.`icon_id` DESC LIMIT 1;';
 '`t' * ($matches[0].Length / 4) $result_last = $this->db->sql_query($sql_last);
 '`t' * ($matches[0].Length / 4) $last_id = $this->db->sql_fetchrow($result_last);
 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result_last);

 '`t' * ($matches[0].Length / 4) $new_id = $last_id['icon_id'] + 1;

 '`t' * ($matches[0].Length / 4) // Costruisci l'array con i valori da inserire nella tabella
 '`t' * ($matches[0].Length / 4) $data = array(
 '`t' * ($matches[0].Length / 4) 'icon_id'    => $new_id, // icon_id
 '`t' * ($matches[0].Length / 4) 'icon_url'   => 'ext/sebo/postreact/styles/all/img/default.png', // icon_url
 '`t' * ($matches[0].Length / 4) 'icon_width' => 32, // icon_width
 '`t' * ($matches[0].Length / 4) 'icon_height' => 32, // icon_height
 '`t' * ($matches[0].Length / 4) 'icon_alt'   => '', // icon_alt
 '`t' * ($matches[0].Length / 4) 'status'     => 0,  // status
 '`t' * ($matches[0].Length / 4) 'active'     => 0   // active
 '`t' * ($matches[0].Length / 4) );
 '`t' * ($matches[0].Length / 4) // Costruisci la query di inserimento
 '`t' * ($matches[0].Length / 4) $sql_insert = 'INSERT INTO ' . $this->table_prefix . 'sebo_postreact_icon ' .
 '`t' * ($matches[0].Length / 4)   $this->db->sql_build_array('INSERT', $data);
 '`t' * ($matches[0].Length / 4) // Esegui la query
 '`t' * ($matches[0].Length / 4) $this->db->sql_query($sql_insert);
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) //##
 '`t' * ($matches[0].Length / 4) // check if deleting icon
 '`t' * ($matches[0].Length / 4) $remove_pr = $this->request->variable('remove_pr', 0, false);
 '`t' * ($matches[0].Length / 4) if ($remove_pr != null) {
 '`t' * ($matches[0].Length / 4) $sql_remove = 'DELETE FROM ' . $this->table_prefix . "sebo_postreact_icon WHERE icon_id = $remove_pr";
 '`t' * ($matches[0].Length / 4) $result_remove = $this->db->sql_query($sql_remove);
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // ##
 '`t' * ($matches[0].Length / 4) // grab icons
 '`t' * ($matches[0].Length / 4) // take the line corresponds to post_id
 '`t' * ($matches[0].Length / 4) $sql_ico = 'SELECT * FROM ' . $this->table_prefix . 'sebo_postreact_icon';
 '`t' * ($matches[0].Length / 4) $data_ico = [];

 '`t' * ($matches[0].Length / 4) $result_ico = $this->db->sql_query($sql_ico);

 '`t' * ($matches[0].Length / 4) if ($result_ico) {
 '`t' * ($matches[0].Length / 4) while ($my_icons = $this->db->sql_fetchrow($result_ico)) {
 '`t' * ($matches[0].Length / 4) $data_ico[] = $my_icons;
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result_ico);
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // Create a form key for preventing CSRF attacks
 '`t' * ($matches[0].Length / 4) add_form_key('sebo_postreact_acp');
// Create an array to collect errors that will be output to the user
 '`t' * ($matches[0].Length / 4) $errors = [];
// Is the form being submitted to us?
 '`t' * ($matches[0].Length / 4) if ($this->request->is_set_post('submit')) {
// Test if the submitted form is valid
 '`t' * ($matches[0].Length / 4) if (!check_form_key('sebo_postreact_acp')) {
 '`t' * ($matches[0].Length / 4) $errors[] = $this->language->lang('FORM_INVALID');
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // If no errors, process the form data
 '`t' * ($matches[0].Length / 4) if (empty($errors)) {
// Initialize an empty array to hold the icon data
 '`t' * ($matches[0].Length / 4) $update_data = [];
 '`t' * ($matches[0].Length / 4) $icon_ids = $this->request->variable('icon_ids', [0]);
// if not array, convert it
 '`t' * ($matches[0].Length / 4) if (!is_array($icon_ids)) {
 '`t' * ($matches[0].Length / 4) $icon_ids = explode(',', $icon_ids);
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) foreach ($icon_ids as $icon_id) {
// grab variables
 '`t' * ($matches[0].Length / 4) $update_data[$icon_id] = [
 '`t' * ($matches[0].Length / 4) 'url' => $this->request->variable('icon_url_' . $icon_id, ''),
 '`t' * ($matches[0].Length / 4) 'icon_alt' => $this->request->variable('icon_alt_' . $icon_id, ''),
 '`t' * ($matches[0].Length / 4) 'icon_width' => $this->request->variable('icon_width_' . $icon_id, ''),
 '`t' * ($matches[0].Length / 4) 'icon_height' => $this->request->variable('icon_height_' . $icon_id, ''),
 '`t' * ($matches[0].Length / 4) 'status' => $this->request->variable('status_' . $icon_id, '') === 'on' ? 1 : 0,
 '`t' * ($matches[0].Length / 4) 'active' => $this->request->variable('icon_height_' . $icon_id, ''),
 '`t' * ($matches[0].Length / 4) ];
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // create query
 '`t' * ($matches[0].Length / 4) $sql = 'UPDATE `' . $this->table_prefix . 'sebo_postreact_icon` SET '
 '`t' * ($matches[0].Length / 4) . '`icon_url` = CASE `icon_id` ';
 '`t' * ($matches[0].Length / 4) foreach ($update_data as $icon_id => $data) {
 '`t' * ($matches[0].Length / 4) $sql .= "WHEN '" . $icon_id . "' THEN 'ext/sebo/postreact/styles/all/img/" . $data['url'] . "' ";
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) $sql .= 'END, '
 '`t' * ($matches[0].Length / 4) . 'icon_alt = CASE icon_id ';
 '`t' * ($matches[0].Length / 4) foreach ($update_data as $icon_id => $data) {
 '`t' * ($matches[0].Length / 4) $sql .= "WHEN '" . $icon_id . "' THEN '" . $data['icon_alt'] . "' ";
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) $sql .= 'END, '
 '`t' * ($matches[0].Length / 4) . '`icon_width` = CASE `icon_id` ';
 '`t' * ($matches[0].Length / 4) foreach ($update_data as $icon_id => $data) {
 '`t' * ($matches[0].Length / 4) $sql .= "WHEN '" . $icon_id . "' THEN '" . $data['icon_width'] . "' ";
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) $sql .= 'END, '
 '`t' * ($matches[0].Length / 4) . '`icon_height` = CASE `icon_id` ';
 '`t' * ($matches[0].Length / 4) foreach ($update_data as $icon_id => $data) {
 '`t' * ($matches[0].Length / 4) $sql .= "WHEN '" . $icon_id . "' THEN '" . $data['icon_height'] . "' ";
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) $sql .= 'END, '
 '`t' * ($matches[0].Length / 4) . '`status` = CASE `icon_id` ';
 '`t' * ($matches[0].Length / 4) foreach ($update_data as $icon_id => $data) {
 '`t' * ($matches[0].Length / 4) $sql .= "WHEN '" . $icon_id . "' THEN '" . $data['status'] . "' ";
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) $sql .= 'END, '
 '`t' * ($matches[0].Length / 4) . '`active` = CASE `icon_id` ';
 '`t' * ($matches[0].Length / 4) foreach ($update_data as $icon_id => $data) {
 '`t' * ($matches[0].Length / 4) $sql .= "WHEN '" . $icon_id . "' THEN '" . $data['active'] . "' ";
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) $sql .= 'END '
 '`t' * ($matches[0].Length / 4) . 'WHERE `icon_id` IN (' . implode(',', array_keys($update_data)) . ')';
 '`t' * ($matches[0].Length / 4) $result = $this->db->sql_query($sql);
 '`t' * ($matches[0].Length / 4) if ($result) {
 '`t' * ($matches[0].Length / 4) // Conferma il successo
 '`t' * ($matches[0].Length / 4) trigger_error($this->language->lang('ACP_POSTREACT_SETTING_SAVED') . adm_back_link($this->u_action));
 '`t' * ($matches[0].Length / 4) } else {
 '`t' * ($matches[0].Length / 4) // Segnala il fallimento
 '`t' * ($matches[0].Length / 4) trigger_error($this->language->lang('ACP_POSTREACT_SETTING_NOT_SAVED') . adm_back_link($this->u_action));
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result);
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // Create urlS
 '`t' * ($matches[0].Length / 4) $delete_url = append_sid("index.{$this->php_ext}") . '&i=-sebo-postreact-acp-main_module&remove_pr=';
 '`t' * ($matches[0].Length / 4) $create_url = append_sid("index.{$this->php_ext}") . '&i=-sebo-postreact-acp-main_module&add_pr=1';

 '`t' * ($matches[0].Length / 4) $s_errors = !empty($errors);
// Set output variables for display in the template
 '`t' * ($matches[0].Length / 4) $this->template->assign_vars([
 '`t' * ($matches[0].Length / 4) 'ICONS'         => $data_ico,
 '`t' * ($matches[0].Length / 4) 'SID'           => $sid_pr,
 '`t' * ($matches[0].Length / 4) 'ARROW'         => '<i class="fa icon fa-chevron-right fa-fw" aria-hidden="true"></i>',
 '`t' * ($matches[0].Length / 4) 'S_ERROR'       => $s_errors,
 '`t' * ($matches[0].Length / 4) 'ERROR_MSG'     => $s_errors ? implode('<br />', $errors) : '',
 '`t' * ($matches[0].Length / 4) 'DELETE_PR_URL' => $delete_url,
 '`t' * ($matches[0].Length / 4) 'CREATE_PR_URL' => $create_url,

 '`t' * ($matches[0].Length / 4) 'U_ACTION'      => $this->u_action,
 '`t' * ($matches[0].Length / 4) 'LINK_DONATE'   => 'https://www.paypal.com/donate/?hosted_button_id=GS3T9MFDJJGT4',
 '`t' * ($matches[0].Length / 4) ]);
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Set custom form action.
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @param string    $u_action   Custom form action
 "`t" * ($matches[0].Length / 4)  * @return void
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function set_page_url($u_action)
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) $this->u_action = $u_action;
 '`t' * ($matches[0].Length / 4) }
}
