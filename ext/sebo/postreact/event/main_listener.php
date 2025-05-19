<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * PostReaction Event listener.
 */
class main_listener implements EventSubscriberInterface
{
 '`t' * ($matches[0].Length / 4) public static function getSubscribedEvents()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) return [
 '`t' * ($matches[0].Length / 4) 'core.user_setup'                           => 'load_language_on_setup',
 '`t' * ($matches[0].Length / 4) 'core.viewtopic_assign_template_vars_before' => 'grab_icons',
 '`t' * ($matches[0].Length / 4) 'core.viewtopic_modify_post_row'            => 'assign_to_template',
 '`t' * ($matches[0].Length / 4) 'core.viewtopic_modify_page_title'          => 'write_db',
 '`t' * ($matches[0].Length / 4) 'core.viewforum_modify_page_title'          => 'grab_icons',
 '`t' * ($matches[0].Length / 4) 'core.viewforum_modify_topicrow'            => 'viewforum_edit',
 '`t' * ($matches[0].Length / 4) 'core.search_modify_tpl_ary'                => 'search_edit',
 '`t' * ($matches[0].Length / 4) 'core.permissions'                          => 'add_permissions',
 '`t' * ($matches[0].Length / 4) 'core.memberlist_view_profile'              => 'edit_view_profile',
 '`t' * ($matches[0].Length / 4) 'core.memberlist_modify_view_profile_template_vars' => 'assign_edit_view_profile'
 '`t' * ($matches[0].Length / 4) ];
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /* @var \phpbb\language\language */
 '`t' * ($matches[0].Length / 4) protected $language;
/** @var \phpbb\db\driver\driver_interface */
 '`t' * ($matches[0].Length / 4) protected $db;
/** @var */
 '`t' * ($matches[0].Length / 4) protected $user;
/** @var \phpbb\request\request */
 '`t' * ($matches[0].Length / 4) protected $request;
/** @var \phpbb\template\template */
 '`t' * ($matches[0].Length / 4) protected $template;

 '`t' * ($matches[0].Length / 4) protected $table_prefix;
/** @var auth */
 '`t' * ($matches[0].Length / 4) protected $auth;
/** @var \phpbb\notification\manager */
 '`t' * ($matches[0].Length / 4) protected $notification_manager;
/** @var php_ext */
 '`t' * ($matches[0].Length / 4) protected $php_ext;

 '`t' * ($matches[0].Length / 4) protected $profile_data;
/**
 "`t" * ($matches[0].Length / 4)  * Constructor
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function __construct(
 '`t' * ($matches[0].Length / 4) \phpbb\language\language $language,
 '`t' * ($matches[0].Length / 4) \phpbb\db\driver\driver_interface $db,
 '`t' * ($matches[0].Length / 4) \phpbb\user $user,
 '`t' * ($matches[0].Length / 4) \phpbb\request\request $request,
 '`t' * ($matches[0].Length / 4) \phpbb\template\template $template,
 '`t' * ($matches[0].Length / 4) $table_prefix,
 '`t' * ($matches[0].Length / 4) \phpbb\auth\auth $auth,
 '`t' * ($matches[0].Length / 4) \phpbb\notification\manager $notification_manager,
 '`t' * ($matches[0].Length / 4) $php_ext
 '`t' * ($matches[0].Length / 4) )
 {
 '`t' * ($matches[0].Length / 4) $this->language = $language;
 '`t' * ($matches[0].Length / 4) $this->db       = $db;
 '`t' * ($matches[0].Length / 4) $this->user     = $user;
 '`t' * ($matches[0].Length / 4) $this->request  = $request;
 '`t' * ($matches[0].Length / 4) $this->template = $template;
 '`t' * ($matches[0].Length / 4) $this->table_prefix = $table_prefix;
 '`t' * ($matches[0].Length / 4) $this->auth = $auth;
 '`t' * ($matches[0].Length / 4) $this->notification_manager   = $notification_manager;
 '`t' * ($matches[0].Length / 4) $this->php_ext = $php_ext;
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Load common language files during user setup
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function load_language_on_setup($event)
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) $lang_set_ext = $event['lang_set_ext'];
 '`t' * ($matches[0].Length / 4) $lang_set_ext[] = [
 '`t' * ($matches[0].Length / 4) 'ext_name' => 'sebo/postreact',
 '`t' * ($matches[0].Length / 4) 'lang_set' => 'common',
 '`t' * ($matches[0].Length / 4) ];
 '`t' * ($matches[0].Length / 4) $event['lang_set_ext'] = $lang_set_ext;
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) public function grab_icons()
 '`t' * ($matches[0].Length / 4)
 {
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
 '`t' * ($matches[0].Length / 4) return $data_ico;
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) public function assign_to_template($event)
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) $postrow = $event['postrow'];
 '`t' * ($matches[0].Length / 4) $row = $event['row'];
// ##
 '`t' * ($matches[0].Length / 4) // check permissions
 '`t' * ($matches[0].Length / 4) //if($this->auth->acl_get('u_new_sebo_postreact') == '1'){
 '`t' * ($matches[0].Length / 4) // granted
 '`t' * ($matches[0].Length / 4) //}
 '`t' * ($matches[0].Length / 4) //else if ($this->auth->acl_get('u_new_sebo_postreact_view') == '0'){
 '`t' * ($matches[0].Length / 4) // return;
 '`t' * ($matches[0].Length / 4) //}

 '`t' * ($matches[0].Length / 4) // ##
 '`t' * ($matches[0].Length / 4) //
 '`t' * ($matches[0].Length / 4) $my_pid = $row['post_id'];
 '`t' * ($matches[0].Length / 4) $my_tid = $row['topic_id'];
// take the line corresponds to post_id
 '`t' * ($matches[0].Length / 4) $sql = 'SELECT * FROM ' . $this->table_prefix . 'sebo_postreact_table WHERE post_id = ' . (int) $my_pid;
 '`t' * ($matches[0].Length / 4) $data = [];
 '`t' * ($matches[0].Length / 4) if ($sql) {
 '`t' * ($matches[0].Length / 4) // do it
 '`t' * ($matches[0].Length / 4) $result = $this->db->sql_query($sql);
 '`t' * ($matches[0].Length / 4) $this->data = [];
 '`t' * ($matches[0].Length / 4) if ($result) {
 '`t' * ($matches[0].Length / 4) while ($my_row = $this->db->sql_fetchrow($result)) {
 '`t' * ($matches[0].Length / 4) $this->data[] = $my_row;
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result);
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // total reaction count
 '`t' * ($matches[0].Length / 4) $total_match_count = count($this->data);

 '`t' * ($matches[0].Length / 4) // ##
 '`t' * ($matches[0].Length / 4) // count icon_id and users_ids
 '`t' * ($matches[0].Length / 4) $icon_counts = [];
 '`t' * ($matches[0].Length / 4) $user_ids_list = [];
 '`t' * ($matches[0].Length / 4) foreach ($this->data as $record) {
 '`t' * ($matches[0].Length / 4) $icon_id = $record['icon_id'];
 '`t' * ($matches[0].Length / 4) $user_id = $record['user_id'];
 '`t' * ($matches[0].Length / 4) $post_id = $record['post_id'];
 '`t' * ($matches[0].Length / 4) if (!isset($icon_counts[$icon_id])) {
 '`t' * ($matches[0].Length / 4) $icon_counts[$icon_id] = 0;
 '`t' * ($matches[0].Length / 4) $user_ids_list[$icon_id] = [];
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) $icon_counts[$icon_id]++;
 '`t' * ($matches[0].Length / 4) // Create an associative array for each entry with user_id and post_id
 '`t' * ($matches[0].Length / 4) $user_ids_list[$icon_id][] = [
 '`t' * ($matches[0].Length / 4) 'user_id' => $user_id,
 '`t' * ($matches[0].Length / 4) 'post_id' => $post_id
 '`t' * ($matches[0].Length / 4) ];
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // ##
 '`t' * ($matches[0].Length / 4) // search users_table information from user_id
 '`t' * ($matches[0].Length / 4) $user_data_detailed = [];
 '`t' * ($matches[0].Length / 4) foreach ($user_ids_list as $icon_id => $entries) {
 '`t' * ($matches[0].Length / 4) foreach ($entries as $entry) {
 '`t' * ($matches[0].Length / 4) $user_id = $entry['user_id'];
 '`t' * ($matches[0].Length / 4) if (!isset($user_data_detailed[$user_id])) {
 '`t' * ($matches[0].Length / 4) $query = 'SELECT group_id, username, user_colour FROM ' . $this->table_prefix . "users WHERE user_id = '$user_id'";
 '`t' * ($matches[0].Length / 4) $result = $this->db->sql_query($query);
 '`t' * ($matches[0].Length / 4) if ($result->num_rows > 0) {
 '`t' * ($matches[0].Length / 4) $row = $result->fetch_assoc();
 '`t' * ($matches[0].Length / 4) $user_data_detailed[$user_id] = [
 '`t' * ($matches[0].Length / 4) 'group_id' => $row['group_id'],
 '`t' * ($matches[0].Length / 4) 'username' => $row['username'],
 '`t' * ($matches[0].Length / 4) 'user_colour' => $row['user_colour'],
 '`t' * ($matches[0].Length / 4) ];
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result);
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // merge username, colour and group to user_id
 '`t' * ($matches[0].Length / 4) $user_ids_with_details = [];
 '`t' * ($matches[0].Length / 4) foreach ($user_ids_list as $icon_id => $entries) {
 '`t' * ($matches[0].Length / 4) foreach ($entries as $entry) {
 '`t' * ($matches[0].Length / 4) $user_id = $entry['user_id'];
 '`t' * ($matches[0].Length / 4) if (isset($user_data_detailed[$user_id])) {
 '`t' * ($matches[0].Length / 4) $user_ids_with_details[$icon_id][] = [
 '`t' * ($matches[0].Length / 4) 'user_id' => $user_id,
 '`t' * ($matches[0].Length / 4) 'username' => $user_data_detailed[$user_id]['username'],
 '`t' * ($matches[0].Length / 4) 'group_id' => $user_data_detailed[$user_id]['group_id'],
 '`t' * ($matches[0].Length / 4) 'user_colour' => $user_data_detailed[$user_id]['user_colour'],
 '`t' * ($matches[0].Length / 4) 'post_id' => $entry['post_id'],  // Include post_id
 '`t' * ($matches[0].Length / 4) ];
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // ##
 '`t' * ($matches[0].Length / 4) // mark your choise
 '`t' * ($matches[0].Length / 4) $check = [];
 '`t' * ($matches[0].Length / 4) $user_id_logged = $this->user->data['user_id'];
 '`t' * ($matches[0].Length / 4) $sql_check = 'SELECT post_id, icon_id FROM ' . $this->table_prefix . "sebo_postreact_table WHERE user_id = '$user_id_logged' AND post_id = '$my_pid'";
 '`t' * ($matches[0].Length / 4) $result_check = $this->db->sql_query($sql_check);
 '`t' * ($matches[0].Length / 4) while ($row_check = $this->db->sql_fetchrow($result_check)) {
 '`t' * ($matches[0].Length / 4) $check[] = [
 '`t' * ($matches[0].Length / 4) 'post_id' => $row_check['post_id'],
 '`t' * ($matches[0].Length / 4) 'icon_id' => $row_check['icon_id']
 '`t' * ($matches[0].Length / 4) ];
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result_check);

 '`t' * ($matches[0].Length / 4) // ##
 '`t' * ($matches[0].Length / 4) // template
 '`t' * ($matches[0].Length / 4) $event['post_row'] = array_merge($event['post_row'], array(
 '`t' * ($matches[0].Length / 4) 'PERM_W'        => $this->auth->acl_get('u_new_sebo_postreact'),
 '`t' * ($matches[0].Length / 4) 'PERM_R'        => $this->auth->acl_get('u_new_sebo_postreact_view'),
 '`t' * ($matches[0].Length / 4) 'N_REACTIONS'   => $total_match_count,
 '`t' * ($matches[0].Length / 4) 'ICONS'         => $this->grab_icons(),
 '`t' * ($matches[0].Length / 4) 'MY_PID'        => (int) $my_pid,
 '`t' * ($matches[0].Length / 4) 'MY_TID'        => (int) $my_tid,
 '`t' * ($matches[0].Length / 4) 'ICON_COUNTS'   => $icon_counts,
 '`t' * ($matches[0].Length / 4) 'ICON_CHECK'    => $check,
 '`t' * ($matches[0].Length / 4) 'REACTORS'      => $user_ids_with_details,
 '`t' * ($matches[0].Length / 4) ));
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) public function write_db()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) // ##
 '`t' * ($matches[0].Length / 4) // request
 '`t' * ($matches[0].Length / 4) $user_id_logged = $this->user->data['user_id'];
 '`t' * ($matches[0].Length / 4) $my_topic_id = $this->request->variable('tid', 0, false);
 '`t' * ($matches[0].Length / 4) $my_post_id = $this->request->variable('pid', 0, false);
 '`t' * ($matches[0].Length / 4) $my_icon_id = $this->request->variable('iid', 0, false);
 '`t' * ($matches[0].Length / 4) $r_time = time();
 '`t' * ($matches[0].Length / 4) if ($user_id_logged !== null && $user_id_logged !== 1) {
 '`t' * ($matches[0].Length / 4) if ($my_icon_id !== null && $my_post_id !== null && $my_topic_id !== null && $my_icon_id !== 0 && $my_post_id !== 0 && $my_topic_id !== 0 && is_numeric($my_topic_id) && is_numeric($my_icon_id) && is_numeric($my_post_id)) {
 '`t' * ($matches[0].Length / 4) // ##
 '`t' * ($matches[0].Length / 4) // check if reacted
 '`t' * ($matches[0].Length / 4) $sql_check = 'SELECT COUNT(*) as count FROM ' . $this->table_prefix . "sebo_postreact_table WHERE user_id = '$user_id_logged' AND post_id = '$my_post_id'";
 '`t' * ($matches[0].Length / 4) $result_check = $this->db->sql_query($sql_check);
 '`t' * ($matches[0].Length / 4) $row_check = $this->db->sql_fetchrow($result_check);

 '`t' * ($matches[0].Length / 4) if ($row_check['count'] > 0) {
// ##
 '`t' * ($matches[0].Length / 4) // delete if yes
 '`t' * ($matches[0].Length / 4) $sql = 'DELETE FROM ' . $this->table_prefix . "sebo_postreact_table WHERE user_id = '$user_id_logged' AND post_id = '$my_post_id';";
 '`t' * ($matches[0].Length / 4) $result = $this->db->sql_query($sql);
 '`t' * ($matches[0].Length / 4) if ($result) {
 '`t' * ($matches[0].Length / 4) //##
 '`t' * ($matches[0].Length / 4) // NOTIFICATION SYS DELETE
 '`t' * ($matches[0].Length / 4) // check posts info
 '`t' * ($matches[0].Length / 4) $sql_pname = 'SELECT 
										p.poster_id AS poster_id_clean, p.post_subject AS post_post_title, p.post_id, 
										u.username_clean AS poster_name_clean, u.user_id, u.user_colour AS poster_user_colour 
										FROM ' . $this->table_prefix . 'posts p 
										JOIN ' . $this->table_prefix . "users u ON p.poster_id = u.user_id 
										WHERE p.post_id = $my_post_id;";
 '`t' * ($matches[0].Length / 4) $result_pname = $this->db->sql_query($sql_pname);
 '`t' * ($matches[0].Length / 4) $row_pname = $this->db->sql_fetchrow($result_pname);

 '`t' * ($matches[0].Length / 4) // DEL =(notification_type+item_id+parent_id(reactor)+user_id(reactor))
 '`t' * ($matches[0].Length / 4) $PR_DEL_item_id = $my_post_id;
 '`t' * ($matches[0].Length / 4) $this->notification_manager->delete_notifications('sebo.postreact.notification.type.postreact_notification', $PR_DEL_item_id, $user_id_logged, $user_id_logged);

 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result_pname);
 '`t' * ($matches[0].Length / 4) //

 '`t' * ($matches[0].Length / 4) $message = $this->user->lang('DELETED_VALUE') . '<br /><br />' . $this->user->lang('RETURN_FORUM', '<a href="' . append_sid("viewtopic.{$this->php_ext}?p={$my_post_id}#p{$my_post_id}") . '">', '</a>');
 '`t' * ($matches[0].Length / 4) meta_refresh(2, append_sid("viewtopic.{$this->php_ext}?p={$my_post_id}#p{$my_post_id}"));
 '`t' * ($matches[0].Length / 4) trigger_error($message);
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) } else {
 '`t' * ($matches[0].Length / 4) // ##
 '`t' * ($matches[0].Length / 4) // react if not
 '`t' * ($matches[0].Length / 4) $sql = 'INSERT INTO ' . $this->table_prefix . "sebo_postreact_table (postreact_id, topic_id, post_id, user_id, icon_id, react_time) VALUES (NULL, '$my_topic_id', '$my_post_id', '$user_id_logged', '$my_icon_id', '$r_time');";
 '`t' * ($matches[0].Length / 4) $result = $this->db->sql_query($sql);
 '`t' * ($matches[0].Length / 4) if ($result) {
 '`t' * ($matches[0].Length / 4) // ##
 '`t' * ($matches[0].Length / 4) // NOTIFICATION SYS INSERT

 '`t' * ($matches[0].Length / 4) // check posts info
 '`t' * ($matches[0].Length / 4) $sql_pname = 'SELECT 
										p.poster_id AS poster_id_clean, p.post_subject AS post_post_title, p.post_id, 
										u.username_clean AS poster_name_clean, u.user_id, u.user_colour AS poster_user_colour 
										FROM ' . $this->table_prefix . 'posts p 
										JOIN ' . $this->table_prefix . "users u ON p.poster_id = u.user_id 
										WHERE p.post_id = $my_post_id;";
 '`t' * ($matches[0].Length / 4) $result_pname = $this->db->sql_query($sql_pname);
 '`t' * ($matches[0].Length / 4) $row_pname = $this->db->sql_fetchrow($result_pname);

 '`t' * ($matches[0].Length / 4) $sql_ico_pr = 'SELECT * FROM ' . $this->table_prefix . 'sebo_postreact_icon WHERE icon_id = ' . $my_icon_id . ';';
 '`t' * ($matches[0].Length / 4) $result_ico_pr = $this->db->sql_query($sql_ico_pr);
 '`t' * ($matches[0].Length / 4) $row_ico_pr = $this->db->sql_fetchrow($result_ico_pr);

 '`t' * ($matches[0].Length / 4) //item_id = poster_id + post_id + reactor_id
 '`t' * ($matches[0].Length / 4) $this->notification_manager->add_notifications('sebo.postreact.notification.type.postreact_notification', [
 '`t' * ($matches[0].Length / 4) 'PR_N_item_id'      => $my_post_id,
 '`t' * ($matches[0].Length / 4) 'PR_N_username'     => $row_pname['poster_name_clean'],
 '`t' * ($matches[0].Length / 4) 'PR_N_user_colour'  => $row_pname['poster_user_colour'],
 '`t' * ($matches[0].Length / 4) 'PR_N_post_title'   => $row_pname['post_post_title'],
 '`t' * ($matches[0].Length / 4) 'PR_N_user_id'      => $row_pname['poster_id_clean'],
 '`t' * ($matches[0].Length / 4) 'PR_N_sender_id'    => $user_id_logged,
 '`t' * ($matches[0].Length / 4) 'PR_N_post_id'      => $my_post_id,
 '`t' * ($matches[0].Length / 4) 'PR_N_topic_id'     => $my_topic_id,
 '`t' * ($matches[0].Length / 4) 'PR_N_icon'         => $row_ico_pr['icon_url']
 '`t' * ($matches[0].Length / 4) ]);

 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result_pname);
 '`t' * ($matches[0].Length / 4) //

 '`t' * ($matches[0].Length / 4) $message = $this->user->lang('INSERTED_VALUE') . '<br /><br />' . $this->user->lang('RETURN_FORUM', '<a href="' . append_sid("viewtopic.{$this->php_ext}?p={$my_post_id}#p{$my_post_id}") . '">', '</a>');
 '`t' * ($matches[0].Length / 4) meta_refresh(2, append_sid("viewtopic.{$this->php_ext}?p={$my_post_id}#p{$my_post_id}"));
 '`t' * ($matches[0].Length / 4) trigger_error($message);
 '`t' * ($matches[0].Length / 4) } else {
 '`t' * ($matches[0].Length / 4) // something wrong = not inserted
 '`t' * ($matches[0].Length / 4) $message = $this->user->lang('NOT_INSERTED_VALUE') . '<br /><br />' . $this->user->lang('RETURN_FORUM', '<a href="' . append_sid("viewtopic.{$this->php_ext}?p={$my_post_id}#p{$my_post_id}") . '">', '</a>');
 '`t' * ($matches[0].Length / 4) meta_refresh(2, append_sid("viewtopic.{$this->php_ext}?p={$my_post_id}#p{$my_post_id}"));
 '`t' * ($matches[0].Length / 4) trigger_error($message);
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result);
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result_check);
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) } else {
 '`t' * ($matches[0].Length / 4) $message = $this->user->lang('LOGIN_TO_REACT') . '<br /><br />' . $this->user->lang('RETURN_FORUM', '<a href="' . append_sid("viewtopic.{$this->php_ext}?p={$my_post_id}#p{$my_post_id}") . '">', '</a>');
 '`t' * ($matches[0].Length / 4) meta_refresh(2, append_sid("viewtopic.{$this->php_ext}?p={$my_post_id}#p{$my_post_id}"));
 '`t' * ($matches[0].Length / 4) trigger_error($message);
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) public function viewforum_edit($event)
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) $topicrow = $event['topicrow'];
 '`t' * ($matches[0].Length / 4) $row = $event['row'];
 '`t' * ($matches[0].Length / 4) $topic_id = $row['topic_id'];
 '`t' * ($matches[0].Length / 4) $sql = 'SELECT * FROM ' . $this->table_prefix . 'sebo_postreact_table WHERE topic_id = ' . (int) $topic_id;
 '`t' * ($matches[0].Length / 4) $data = [];
// do it
 '`t' * ($matches[0].Length / 4) $result = $this->db->sql_query($sql);

 '`t' * ($matches[0].Length / 4) // Array starts for data and IDs
 '`t' * ($matches[0].Length / 4) $filtered_rows = [];
 '`t' * ($matches[0].Length / 4) $post_ids = [];
 '`t' * ($matches[0].Length / 4) while ($my_row = $this->db->sql_fetchrow($result)) {
 '`t' * ($matches[0].Length / 4) $post_id = (int) $my_row['post_id'];
 '`t' * ($matches[0].Length / 4) // save the entire row of the post_ids
 '`t' * ($matches[0].Length / 4) $filtered_rows[$post_id] = $my_row;
 '`t' * ($matches[0].Length / 4) // save only post_id
 '`t' * ($matches[0].Length / 4) $post_ids[] = $post_id;
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // if IDs, check
 '`t' * ($matches[0].Length / 4) if (!empty($post_ids)) {
 '`t' * ($matches[0].Length / 4) $sql_post_exist = 'SELECT post_id FROM ' . $this->table_prefix . 'posts WHERE post_id IN (' . implode(',', $post_ids) . ')';
 '`t' * ($matches[0].Length / 4) $result_post_exist = $this->db->sql_query($sql_post_exist);
 '`t' * ($matches[0].Length / 4) $existing_posts = [];
 '`t' * ($matches[0].Length / 4) while ($row = $this->db->sql_fetchrow($result_post_exist)) {
 '`t' * ($matches[0].Length / 4) $existing_posts[$row['post_id']] = true;
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result_post_exist);
// Filter
 '`t' * ($matches[0].Length / 4) $data = [];
 '`t' * ($matches[0].Length / 4) foreach ($filtered_rows as $post_id => $row) {
 '`t' * ($matches[0].Length / 4) if (isset($existing_posts[$post_id])) {
 '`t' * ($matches[0].Length / 4) // save row
 '`t' * ($matches[0].Length / 4) $data[] = $row;
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result);

 '`t' * ($matches[0].Length / 4) // numb check icon_id
 '`t' * ($matches[0].Length / 4) $icon_counts = [];
 '`t' * ($matches[0].Length / 4) foreach ($data as $record) {
 '`t' * ($matches[0].Length / 4) $icon_id = $record['icon_id'];
 '`t' * ($matches[0].Length / 4) if (!isset($icon_counts[$icon_id])) {
 '`t' * ($matches[0].Length / 4) $icon_counts[$icon_id] = 0;
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) $icon_counts[$icon_id]++;
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) // ##
 '`t' * ($matches[0].Length / 4) // sort by number
 '`t' * ($matches[0].Length / 4) $icons_with_counts = [];
 '`t' * ($matches[0].Length / 4) foreach ($this->grab_icons() as $icon) {
 '`t' * ($matches[0].Length / 4) $icon_id = $icon['icon_id'];
 '`t' * ($matches[0].Length / 4) if (isset($icon_counts[$icon_id])) {
 '`t' * ($matches[0].Length / 4) $icons_with_counts[] = [
 '`t' * ($matches[0].Length / 4) 'icon_url' => $icon['icon_url'],
 '`t' * ($matches[0].Length / 4) 'icon_alt' => $icon['icon_alt'],
 '`t' * ($matches[0].Length / 4) 'icon_id' => $icon_id,
 '`t' * ($matches[0].Length / 4) 'count' => $icon_counts[$icon_id]
 '`t' * ($matches[0].Length / 4) ];
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) // sort for count DESC
 '`t' * ($matches[0].Length / 4) usort($icons_with_counts, function ($a, $b) {

 '`t' * ($matches[0].Length / 4) return $b['count'] - $a['count'];
 '`t' * ($matches[0].Length / 4) });
// ##
 '`t' * ($matches[0].Length / 4) // template
 '`t' * ($matches[0].Length / 4) $event['topic_row'] = array_merge($event['topic_row'], [
 '`t' * ($matches[0].Length / 4) 'ICONS'         => $icons_with_counts,
 '`t' * ($matches[0].Length / 4) 'PERM_W'        => $this->auth->acl_get('u_new_sebo_postreact'),
 '`t' * ($matches[0].Length / 4) 'PERM_R'        => $this->auth->acl_get('u_new_sebo_postreact_view')
 '`t' * ($matches[0].Length / 4) ]);
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) public function search_edit($event)
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) $row = $event['row'];
// sql_escape because of potential inject (?)
 '`t' * ($matches[0].Length / 4) $topic_id = isset($row['topic_id']) ? (int) $row['topic_id'] : 0;
 '`t' * ($matches[0].Length / 4) $topic_id_escaped = $this->db->sql_escape($topic_id);
 '`t' * ($matches[0].Length / 4) $sql = 'SELECT * FROM ' . $this->table_prefix . 'sebo_postreact_table WHERE topic_id = ' . $topic_id_escaped;
 '`t' * ($matches[0].Length / 4) $result = $this->db->sql_query($sql);
 '`t' * ($matches[0].Length / 4) $filtered_rows = [];
 '`t' * ($matches[0].Length / 4) $post_ids = [];
 '`t' * ($matches[0].Length / 4) while ($my_row = $this->db->sql_fetchrow($result)) {
 '`t' * ($matches[0].Length / 4) $post_id = (int) $my_row['post_id'];
 '`t' * ($matches[0].Length / 4) $filtered_rows[$post_id] = $my_row;
 '`t' * ($matches[0].Length / 4) $post_ids[] = $post_id;
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result);
 '`t' * ($matches[0].Length / 4) $data = [];
 '`t' * ($matches[0].Length / 4) if (!empty($post_ids)) {
 '`t' * ($matches[0].Length / 4) $sql_post_exist = 'SELECT post_id FROM ' . $this->table_prefix . 'posts WHERE post_id IN (' . implode(',', $post_ids) . ')';
 '`t' * ($matches[0].Length / 4) $result_post_exist = $this->db->sql_query($sql_post_exist);
 '`t' * ($matches[0].Length / 4) $existing_posts = [];
 '`t' * ($matches[0].Length / 4) while ($row = $this->db->sql_fetchrow($result_post_exist)) {
 '`t' * ($matches[0].Length / 4) $existing_posts[$row['post_id']] = true;
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result_post_exist);
 '`t' * ($matches[0].Length / 4) foreach ($filtered_rows as $post_id => $row) {
 '`t' * ($matches[0].Length / 4) if (isset($existing_posts[$post_id])) {
 '`t' * ($matches[0].Length / 4) $data[] = $row;
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // ##
 '`t' * ($matches[0].Length / 4) // numb check icon_id
 '`t' * ($matches[0].Length / 4) $topic_id_count = 0;
 '`t' * ($matches[0].Length / 4) foreach ($data as $record) {
 '`t' * ($matches[0].Length / 4) if ($record['topic_id'] == $topic_id) {
 '`t' * ($matches[0].Length / 4) $topic_id_count++;
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // start the counter
 '`t' * ($matches[0].Length / 4) $topic_id_count = 0;
 '`t' * ($matches[0].Length / 4) $data_ico = $this->grab_icons();

 '`t' * ($matches[0].Length / 4) // Step 1: make a new array with icon_id key
 '`t' * ($matches[0].Length / 4) $data_ico_assoc = [];
 '`t' * ($matches[0].Length / 4) foreach ($data_ico as $icon) {
 '`t' * ($matches[0].Length / 4) $data_ico_assoc[$icon['icon_id']] = $icon;
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // Step 2: count icon_id occurrences for topic_id
 '`t' * ($matches[0].Length / 4) $icon_counts = [];
 '`t' * ($matches[0].Length / 4) foreach ($data as $rec) {
 '`t' * ($matches[0].Length / 4) $icon_id = $rec['icon_id'];
 '`t' * ($matches[0].Length / 4) $topic_id = $rec['topic_id'];
 '`t' * ($matches[0].Length / 4) if (!isset($icon_counts[$topic_id])) {
 '`t' * ($matches[0].Length / 4) $icon_counts[$topic_id] = [];
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) if (!isset($icon_counts[$topic_id][$icon_id])) {
 '`t' * ($matches[0].Length / 4) $icon_counts[$topic_id][$icon_id] = 0;
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) $icon_counts[$topic_id][$icon_id]++;
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // Step 3: make new array with icon info and count, ensuring each icon_id is unique
 '`t' * ($matches[0].Length / 4) $new_array = [];
 '`t' * ($matches[0].Length / 4) foreach ($data as $rec) {
 '`t' * ($matches[0].Length / 4) $icon_id = $rec['icon_id'];
 '`t' * ($matches[0].Length / 4) $topic_id = $rec['topic_id'];
 '`t' * ($matches[0].Length / 4) if (isset($data_ico_assoc[$icon_id])) {
 '`t' * ($matches[0].Length / 4) // Only if the icon_id has not already been added to the final array
 '`t' * ($matches[0].Length / 4) if (!isset($new_array[$icon_id])) {
 '`t' * ($matches[0].Length / 4) $icon_info = $data_ico_assoc[$icon_id];
 '`t' * ($matches[0].Length / 4) $count = isset($icon_counts[$topic_id][$icon_id]) ? (string) $icon_counts[$topic_id][$icon_id] : '0';
 '`t' * ($matches[0].Length / 4) $new_array[$icon_id] = [
 '`t' * ($matches[0].Length / 4) 'icon_id' => $icon_info['icon_id'],
 '`t' * ($matches[0].Length / 4) 'icon_url' => $icon_info['icon_url'],
 '`t' * ($matches[0].Length / 4) 'icon_alt' => $icon_info['icon_alt'],
 '`t' * ($matches[0].Length / 4) 'topic_id' => $topic_id,
 '`t' * ($matches[0].Length / 4) 'count' => $count
 '`t' * ($matches[0].Length / 4) ];
 '`t' * ($matches[0].Length / 4) } else {
 '`t' * ($matches[0].Length / 4) // Update the count if icon_id already exists
 '`t' * ($matches[0].Length / 4) $new_array[$icon_id]['count'] = (string) max($new_array[$icon_id]['count'], $count);
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // Remove the associative key and reset the numeric index
 '`t' * ($matches[0].Length / 4) $array_with_counts = array_values($new_array);
// ##
 '`t' * ($matches[0].Length / 4) // template
 '`t' * ($matches[0].Length / 4) $event['tpl_ary'] = array_merge($event['tpl_ary'], [
 '`t' * ($matches[0].Length / 4) 'ICONS'         => $array_with_counts,
 '`t' * ($matches[0].Length / 4) 'PERM_W'        => $this->auth->acl_get('u_new_sebo_postreact'),
 '`t' * ($matches[0].Length / 4) 'PERM_R'        => $this->auth->acl_get('u_new_sebo_postreact_view')
 '`t' * ($matches[0].Length / 4) ]);
 '`t' * ($matches[0].Length / 4) }


 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Add permissions to the ACP -> Permissions settings page
 "`t" * ($matches[0].Length / 4)  * This is where permissions are assigned language keys and
 "`t" * ($matches[0].Length / 4)  * categories (where they will appear in the Permissions table):
 "`t" * ($matches[0].Length / 4)  * actions|content|forums|misc|permissions|pm|polls|post
 "`t" * ($matches[0].Length / 4)  * post_actions|posting|profile|settings|topic_actions|user_group
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * Developers note: To control access to ACP, MCP and UCP modules, you
 "`t" * ($matches[0].Length / 4)  * must assign your permissions in your module_info.php file. For example,
 "`t" * ($matches[0].Length / 4)  * to allow only users with the a_new_sebo_postreact permission
 "`t" * ($matches[0].Length / 4)  * access to your ACP module, you would set this in your acp/main_info.php:
 "`t" * ($matches[0].Length / 4)  *    'auth' => 'ext_sebo/postreact && acl_a_new_sebo_postreact'
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @param \phpbb\event\data $event  Event object
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function add_permissions($event)
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) $permissions = $event['permissions'];
 '`t' * ($matches[0].Length / 4) $permissions['u_new_sebo_postreact'] = ['lang' => 'ACL_U_NEW_SEBO_POSTREACT', 'cat' => 'post'];
 '`t' * ($matches[0].Length / 4) $permissions['u_new_sebo_postreact_view'] = ['lang' => 'ACL_U_NEW_SEBO_POSTREACT_VIEW', 'cat' => 'post'];
 '`t' * ($matches[0].Length / 4) $event['permissions'] = $permissions;
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Edit profile for reaction count
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function edit_view_profile($event)
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) $user_id = (int) $event['member']['user_id'];
// *
 '`t' * ($matches[0].Length / 4) // Reactions sent
 '`t' * ($matches[0].Length / 4) $sql = 'SELECT icon_id, COUNT(*) AS icon_count
				FROM ' . $this->table_prefix . 'sebo_postreact_table
				WHERE user_id = ' . $user_id . '
				GROUP BY icon_id';
 '`t' * ($matches[0].Length / 4) $result = $this->db->sql_query($sql);
 '`t' * ($matches[0].Length / 4) $icon_counts = [];
 '`t' * ($matches[0].Length / 4) $icon_ids = [];
 '`t' * ($matches[0].Length / 4) while ($row = $this->db->sql_fetchrow($result)) {
 '`t' * ($matches[0].Length / 4) $icon_counts[$row['icon_id']] = $row['icon_count'];
 '`t' * ($matches[0].Length / 4) $icon_ids[] = (int) $row['icon_id'];
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result);
 '`t' * ($matches[0].Length / 4) $icons = [];
 '`t' * ($matches[0].Length / 4) if (!empty($icon_ids)) {
 '`t' * ($matches[0].Length / 4) // start list for IN (...)
 '`t' * ($matches[0].Length / 4) $icon_ids_list = implode(',', $icon_ids);
 '`t' * ($matches[0].Length / 4) // Query for active icons (status = 1)
 '`t' * ($matches[0].Length / 4) $sql = 'SELECT icon_id, icon_url, icon_width, icon_height, icon_alt
					FROM ' . $this->table_prefix . 'sebo_postreact_icon
					WHERE icon_id IN (' . $icon_ids_list . ') AND status = 1';
 '`t' * ($matches[0].Length / 4) $result = $this->db->sql_query($sql);
 '`t' * ($matches[0].Length / 4) while ($row = $this->db->sql_fetchrow($result)) {
 '`t' * ($matches[0].Length / 4) $id = (int) $row['icon_id'];
 '`t' * ($matches[0].Length / 4) if (isset($icon_counts[$id])) {
 '`t' * ($matches[0].Length / 4) $icons[] = [
 '`t' * ($matches[0].Length / 4) 'ICON_ID'     => $id,
 '`t' * ($matches[0].Length / 4) 'ICON_COUNT'  => $icon_counts[$id],
 '`t' * ($matches[0].Length / 4) 'ICON_URL'    => $row['icon_url'],
 '`t' * ($matches[0].Length / 4) 'ICON_WIDTH'  => $row['icon_width'],
 '`t' * ($matches[0].Length / 4) 'ICON_HEIGHT' => $row['icon_height'],
 '`t' * ($matches[0].Length / 4) 'ICON_ALT'    => $row['icon_alt'],
 '`t' * ($matches[0].Length / 4) ];
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result);
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // assign
 '`t' * ($matches[0].Length / 4) $this->profile_data['icons'] = $icons;

 '`t' * ($matches[0].Length / 4) // *
 '`t' * ($matches[0].Length / 4) // Reactions received
 '`t' * ($matches[0].Length / 4) $sql = 'SELECT pr.icon_id, COUNT(*) AS icon_count
				FROM ' . $this->table_prefix . 'sebo_postreact_table pr
				INNER JOIN ' . $this->table_prefix . 'posts p
					ON pr.post_id = p.post_id
				WHERE p.poster_id = ' . $user_id . '
				GROUP BY pr.icon_id';
 '`t' * ($matches[0].Length / 4) $result = $this->db->sql_query($sql);
 '`t' * ($matches[0].Length / 4) $received_icon_counts = [];
 '`t' * ($matches[0].Length / 4) $received_icon_ids = [];
 '`t' * ($matches[0].Length / 4) while ($row = $this->db->sql_fetchrow($result)) {
 '`t' * ($matches[0].Length / 4) $received_icon_counts[$row['icon_id']] = $row['icon_count'];
 '`t' * ($matches[0].Length / 4) $received_icon_ids[] = (int) $row['icon_id'];
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result);
// grab
 '`t' * ($matches[0].Length / 4) $received_icons = [];
 '`t' * ($matches[0].Length / 4) if (!empty($received_icon_ids)) {
 '`t' * ($matches[0].Length / 4) $icon_ids_list = implode(',', $received_icon_ids);
 '`t' * ($matches[0].Length / 4) $sql = 'SELECT icon_id, icon_url, icon_width, icon_height, icon_alt
					FROM ' . $this->table_prefix . 'sebo_postreact_icon
					WHERE icon_id IN (' . $icon_ids_list . ') AND status = 1';
 '`t' * ($matches[0].Length / 4) $result = $this->db->sql_query($sql);
 '`t' * ($matches[0].Length / 4) while ($row = $this->db->sql_fetchrow($result)) {
 '`t' * ($matches[0].Length / 4) $id = (int) $row['icon_id'];
 '`t' * ($matches[0].Length / 4) if (isset($received_icon_counts[$id])) {
 '`t' * ($matches[0].Length / 4) $received_icons[] = [
 '`t' * ($matches[0].Length / 4) 'ICON_ID'     => $id,
 '`t' * ($matches[0].Length / 4) 'ICON_COUNT'  => $received_icon_counts[$id],
 '`t' * ($matches[0].Length / 4) 'ICON_URL'    => $row['icon_url'],
 '`t' * ($matches[0].Length / 4) 'ICON_WIDTH'  => $row['icon_width'],
 '`t' * ($matches[0].Length / 4) 'ICON_HEIGHT' => $row['icon_height'],
 '`t' * ($matches[0].Length / 4) 'ICON_ALT'    => $row['icon_alt'],
 '`t' * ($matches[0].Length / 4) ];
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) $this->db->sql_freeresult($result);
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // Assign
 '`t' * ($matches[0].Length / 4) $this->profile_data['icons_received'] = $received_icons;
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) public function assign_edit_view_profile($event)
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) if (!empty($this->profile_data)) {
// Assign reaction sent
 '`t' * ($matches[0].Length / 4) if (!empty($this->profile_data['icons'])) {
 '`t' * ($matches[0].Length / 4) foreach ($this->profile_data['icons'] as $icon) {
 '`t' * ($matches[0].Length / 4) $this->template->assign_block_vars('user_reactions', [
 '`t' * ($matches[0].Length / 4) 'ICON_ID'     => $icon['ICON_ID'],
 '`t' * ($matches[0].Length / 4) 'ICON_COUNT'  => $icon['ICON_COUNT'],
 '`t' * ($matches[0].Length / 4) 'ICON_URL'    => $icon['ICON_URL'],
 '`t' * ($matches[0].Length / 4) 'ICON_WIDTH'  => $icon['ICON_WIDTH'],
 '`t' * ($matches[0].Length / 4) 'ICON_HEIGHT' => $icon['ICON_HEIGHT'],
 '`t' * ($matches[0].Length / 4) 'ICON_ALT'    => $icon['ICON_ALT'],
 '`t' * ($matches[0].Length / 4) ]);
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) // Assign reaction received
 '`t' * ($matches[0].Length / 4) if (!empty($this->profile_data['icons_received'])) {
 '`t' * ($matches[0].Length / 4) foreach ($this->profile_data['icons_received'] as $icon) {
 '`t' * ($matches[0].Length / 4) $this->template->assign_block_vars('user_reactions_received', [
 '`t' * ($matches[0].Length / 4) 'ICON_ID'     => $icon['ICON_ID'],
 '`t' * ($matches[0].Length / 4) 'ICON_COUNT'  => $icon['ICON_COUNT'],
 '`t' * ($matches[0].Length / 4) 'ICON_URL'    => $icon['ICON_URL'],
 '`t' * ($matches[0].Length / 4) 'ICON_WIDTH'  => $icon['ICON_WIDTH'],
 '`t' * ($matches[0].Length / 4) 'ICON_HEIGHT' => $icon['ICON_HEIGHT'],
 '`t' * ($matches[0].Length / 4) 'ICON_ALT'    => $icon['ICON_ALT'],
 '`t' * ($matches[0].Length / 4) ]);
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }
 '`t' * ($matches[0].Length / 4) }
}
