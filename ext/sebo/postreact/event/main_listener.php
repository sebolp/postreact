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
	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'						   => 'load_language_on_setup',
			'core.viewtopic_assign_template_vars_before'=> 'grab_icons',
			'core.viewtopic_modify_post_row'			=> 'assign_to_template',
			'core.viewtopic_modify_page_title'		  => 'write_db',
			'core.viewforum_modify_page_title'		  => 'grab_icons',
			'core.viewforum_modify_topicrow'			=> 'viewforum_edit',
			'core.search_modify_tpl_ary'				=> 'search_edit',
			'core.permissions'						  => 'add_permissions',
			'core.memberlist_view_profile'			  => 'edit_view_profile',
			'core.memberlist_modify_view_profile_template_vars' => 'assign_edit_view_profile'
		];
	}
	/* @var \phpbb\language\language */
	protected $language;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	/** @var */
	protected $user;
	/** @var \phpbb\request\request */
	protected $request;
	/** @var \phpbb\template\template */
	protected $template;
	protected $table_prefix;
	/** @var auth */
	protected $auth;
	/** @var \phpbb\notification\manager */
	protected $notification_manager;
	/** @var php_ext */
	protected $php_ext;
	protected $profile_data;
	/**
	 * Constructor
	 */
	public function __construct(
		\phpbb\language\language $language,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		$table_prefix,
		\phpbb\auth\auth $auth,
		\phpbb\notification\manager $notification_manager,
		$php_ext
	) {
		$this->language = $language;
		$this->db	   = $db;
		$this->user	 = $user;
		$this->request  = $request;
		$this->template = $template;
		$this->table_prefix = $table_prefix;
		$this->auth = $auth;
		$this->notification_manager   = $notification_manager;
		$this->php_ext = $php_ext;
	}
	/**
	 * Load common language files during user setup
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'sebo/postreact',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}
	public function grab_icons()
	{
		// ##
		// grab icons
		// take the line corresponds to post_id
		$sql_ico = 'SELECT * FROM ' . $this->table_prefix . 'sebo_postreact_icon';
		$data_ico = [];
		$result_ico = $this->db->sql_query($sql_ico);
		if ($result_ico) {
			while ($my_icons = $this->db->sql_fetchrow($result_ico)) {
				$data_ico[] = $my_icons;
			}
			$this->db->sql_freeresult($result_ico);
		}
		return $data_ico;
	}
	public function assign_to_template($event)
	{
		$postrow = $event['postrow'];
		$row = $event['row'];
		// ##
		// check permissions
		//if($this->auth->acl_get('u_new_sebo_postreact') == '1'){
			// granted
		//}
		//else if ($this->auth->acl_get('u_new_sebo_postreact_view') == '0'){
			// return;
		//}
		// ##
		//
		$my_pid = $row['post_id'];
		$my_tid = $row['topic_id'];
		// take the line corresponds to post_id
		$sql = 'SELECT * FROM ' . $this->table_prefix . 'sebo_postreact_table WHERE post_id = ' . (int)$my_pid;
		$data = [];
		if ($sql) {
			// do it
			$result = $this->db->sql_query($sql);
			$this->data = [];
			if ($result) {
				while ($my_row = $this->db->sql_fetchrow($result)) {
					$this->data[] = $my_row;
				}
				$this->db->sql_freeresult($result);
			}
		}
		// total reaction count
		$total_match_count = count($this->data);
		// ##
		// count icon_id and users_ids
		$icon_counts = [];
		$user_ids_list = [];
		foreach ($this->data as $record) {
			$icon_id = $record['icon_id'];
			$user_id = $record['user_id'];
			$post_id = $record['post_id'];
			if (!isset($icon_counts[$icon_id])) {
				$icon_counts[$icon_id] = 0;
				$user_ids_list[$icon_id] = [];
			}
			$icon_counts[$icon_id]++;
			// Create an associative array for each entry with user_id and post_id
			$user_ids_list[$icon_id][] = [
				'user_id' => $user_id,
				'post_id' => $post_id
			];
		}
		// ##
		// search users_table information from user_id
		$user_data_detailed = [];
		foreach ($user_ids_list as $icon_id => $entries) {
			foreach ($entries as $entry) {
				$user_id = $entry['user_id'];
				if (!isset($user_data_detailed[$user_id])) {
					$query = "SELECT group_id, username, user_colour FROM " . $this->table_prefix . "users WHERE user_id = '$user_id'";
					$result = $this->db->sql_query($query);
					if ($result->num_rows > 0) {
						$row = $result->fetch_assoc();
						$user_data_detailed[$user_id] = [
							'group_id' => $row['group_id'],
							'username' => $row['username'],
							'user_colour' => $row['user_colour'],
						];
					}
					$this->db->sql_freeresult($result);
				}
			}
		}
		// merge username, colour and group to user_id
		$user_ids_with_details = [];
		foreach ($user_ids_list as $icon_id => $entries) {
			foreach ($entries as $entry) {
				$user_id = $entry['user_id'];
				if (isset($user_data_detailed[$user_id])) {
					$user_ids_with_details[$icon_id][] = [
						'user_id' => $user_id,
						'username' => $user_data_detailed[$user_id]['username'],
						'group_id' => $user_data_detailed[$user_id]['group_id'],
						'user_colour' => $user_data_detailed[$user_id]['user_colour'],
						'post_id' => $entry['post_id'],  // Include post_id
					];
				}
			}
		}
		// ##
		// mark your choise
		$check = [];
		$user_id_logged = $this->user->data['user_id'];
		$sql_check = "SELECT post_id, icon_id FROM " . $this->table_prefix . "sebo_postreact_table WHERE user_id = '$user_id_logged' AND post_id = '$my_pid'";
		$result_check = $this->db->sql_query($sql_check);
		while ($row_check = $this->db->sql_fetchrow($result_check)) {
			$check[] = [
				'post_id' => $row_check['post_id'],
				'icon_id' => $row_check['icon_id']
			];
		}
		$this->db->sql_freeresult($result_check);
		// ##
		// template
		$event['post_row'] = array_merge($event['post_row'], array(
				'PERM_W'		=> $this->auth->acl_get('u_new_sebo_postreact'),
				'PERM_R'		=> $this->auth->acl_get('u_new_sebo_postreact_view'),
				'N_REACTIONS'   => $total_match_count,
				'ICONS'		 => $this->grab_icons(),
				'MY_PID'		=> (int)$my_pid,
				'MY_TID'		=> (int)$my_tid,
				'ICON_COUNTS'   => $icon_counts,
				'ICON_CHECK'	=> $check,
				'REACTORS'	  => $user_ids_with_details,
		));
	}
	public function write_db()
	{
		// ##
		// request
		$user_id_logged = $this->user->data['user_id'];
		$my_topic_id = $this->request->variable('tid', 0, false);
		$my_post_id = $this->request->variable('pid', 0, false);
		$my_icon_id = $this->request->variable('iid', 0, false);
		$r_time = time();
		if ($user_id_logged !== null && $user_id_logged !== 1) {
			if ($my_icon_id !== null && $my_post_id !== null && $my_topic_id !== null && $my_icon_id !== 0 && $my_post_id !== 0 && $my_topic_id !== 0 && is_numeric($my_topic_id) && is_numeric($my_icon_id) && is_numeric($my_post_id)) {
				// ##
				// check if reacted
				$sql_check = "SELECT COUNT(*) as count FROM " . $this->table_prefix . "sebo_postreact_table WHERE user_id = '$user_id_logged' AND post_id = '$my_post_id'";
				$result_check = $this->db->sql_query($sql_check);
				$row_check = $this->db->sql_fetchrow($result_check);
				if ($row_check['count'] > 0) {
					// ##
					// delete if yes
					$sql = "DELETE FROM " . $this->table_prefix . "sebo_postreact_table WHERE user_id = '$user_id_logged' AND post_id = '$my_post_id';";
					$result = $this->db->sql_query($sql);
					if ($result) {
						//##
						// NOTIFICATION SYS DELETE
						// check posts info
						$sql_pname = "SELECT
										p.poster_id AS poster_id_clean, p.post_subject AS post_post_title, p.post_id,
										u.username_clean AS poster_name_clean, u.user_id, u.user_colour AS poster_user_colour
										FROM " . $this->table_prefix . "posts p
										JOIN " . $this->table_prefix . "users u ON p.poster_id = u.user_id
										WHERE p.post_id = $my_post_id;";
						$result_pname = $this->db->sql_query($sql_pname);
						$row_pname = $this->db->sql_fetchrow($result_pname);
						// DEL =(notification_type+item_id+parent_id(reactor)+user_id(reactor))
						$PR_DEL_item_id = $my_post_id;
						$this->notification_manager->delete_notifications('sebo.postreact.notification.type.postreact_notification', $PR_DEL_item_id, $user_id_logged, $user_id_logged);
						$this->db->sql_freeresult($result_pname);
						//
						$message = $this->user->lang('DELETED_VALUE') . '<br /><br />' . $this->user->lang('RETURN_FORUM', '<a href="' . append_sid("viewtopic.{$this->php_ext}?p={$my_post_id}#p{$my_post_id}") . '">', '</a>');
						meta_refresh(2, append_sid("viewtopic.{$this->php_ext}?p={$my_post_id}#p{$my_post_id}"));
						trigger_error($message);
					}
				} else {
					// ##
					// react if not
					$sql = "INSERT INTO " . $this->table_prefix . "sebo_postreact_table (postreact_id, topic_id, post_id, user_id, icon_id, react_time) VALUES (NULL, '$my_topic_id', '$my_post_id', '$user_id_logged', '$my_icon_id', '$r_time');";
					$result = $this->db->sql_query($sql);
					if ($result) {
						// ##
						// NOTIFICATION SYS INSERT
						// check posts info
						$sql_pname = "SELECT
										p.poster_id AS poster_id_clean, p.post_subject AS post_post_title, p.post_id,
										u.username_clean AS poster_name_clean, u.user_id, u.user_colour AS poster_user_colour
										FROM " . $this->table_prefix . "posts p
										JOIN " . $this->table_prefix . "users u ON p.poster_id = u.user_id
										WHERE p.post_id = $my_post_id;";
						$result_pname = $this->db->sql_query($sql_pname);
						$row_pname = $this->db->sql_fetchrow($result_pname);
						$sql_ico_pr = 'SELECT * FROM ' . $this->table_prefix . 'sebo_postreact_icon WHERE icon_id = '.$my_icon_id.';';
						$result_ico_pr = $this->db->sql_query($sql_ico_pr);
						$row_ico_pr = $this->db->sql_fetchrow($result_ico_pr);
						//item_id = poster_id + post_id + reactor_id
						$this->notification_manager->add_notifications('sebo.postreact.notification.type.postreact_notification', [
							'PR_N_item_id'	  => $my_post_id,
							'PR_N_username'	 => $row_pname['poster_name_clean'],
							'PR_N_user_colour'  => $row_pname['poster_user_colour'],
							'PR_N_post_title'   => $row_pname['post_post_title'],
							'PR_N_user_id'	  => $row_pname['poster_id_clean'],
							'PR_N_sender_id'	=> $user_id_logged,
							'PR_N_post_id'	  => $my_post_id,
							'PR_N_topic_id'	 => $my_topic_id,
							'PR_N_icon'		 => $row_ico_pr['icon_url']
						]);
						$this->db->sql_freeresult($result_pname);
						//
						$message = $this->user->lang('INSERTED_VALUE') . '<br /><br />' . $this->user->lang('RETURN_FORUM', '<a href="' . append_sid("viewtopic.{$this->php_ext}?p={$my_post_id}#p{$my_post_id}") . '">', '</a>');
						meta_refresh(2, append_sid("viewtopic.{$this->php_ext}?p={$my_post_id}#p{$my_post_id}"));
						trigger_error($message);
					} else {
						// something wrong = not inserted
						$message = $this->user->lang('NOT_INSERTED_VALUE') . '<br /><br />' . $this->user->lang('RETURN_FORUM', '<a href="' . append_sid("viewtopic.{$this->php_ext}?p={$my_post_id}#p{$my_post_id}") . '">', '</a>');
						meta_refresh(2, append_sid("viewtopic.{$this->php_ext}?p={$my_post_id}#p{$my_post_id}"));
						trigger_error($message);
					}
					$this->db->sql_freeresult($result);
				}
				$this->db->sql_freeresult($result_check);
			}
		} else {
				$message = $this->user->lang('LOGIN_TO_REACT') . '<br /><br />' . $this->user->lang('RETURN_FORUM', '<a href="' . append_sid("viewtopic.{$this->php_ext}?p={$my_post_id}#p{$my_post_id}") . '">', '</a>');
				meta_refresh(2, append_sid("viewtopic.{$this->php_ext}?p={$my_post_id}#p{$my_post_id}"));
				trigger_error($message);
		}
	}
	public function viewforum_edit($event)
	{
		$topicrow = $event['topicrow'];
		$row = $event['row'];
		$topic_id = $row['topic_id'];
		$sql = 'SELECT * FROM ' . $this->table_prefix . 'sebo_postreact_table WHERE topic_id = ' . (int)$topic_id;
		$data = [];
		// do it
		$result = $this->db->sql_query($sql);
		// Array starts for data and IDs
		$filtered_rows = [];
		$post_ids = [];
		while ($my_row = $this->db->sql_fetchrow($result)) {
			$post_id = (int)$my_row['post_id'];
			// save the entire row of the post_ids
			$filtered_rows[$post_id] = $my_row;
			// save only post_id
			$post_ids[] = $post_id;
		}
		// if IDs, check
		if (!empty($post_ids)) {
			$sql_post_exist = 'SELECT post_id FROM ' . $this->table_prefix . 'posts WHERE post_id IN (' . implode(',', $post_ids) . ')';
			$result_post_exist = $this->db->sql_query($sql_post_exist);
			$existing_posts = [];
			while ($row = $this->db->sql_fetchrow($result_post_exist)) {
				$existing_posts[$row['post_id']] = true;
			}
			$this->db->sql_freeresult($result_post_exist);
			// Filter
			$data = [];
			foreach ($filtered_rows as $post_id => $row) {
				if (isset($existing_posts[$post_id])) {
					// save row
					$data[] = $row;
				}
			}
		}
		$this->db->sql_freeresult($result);
		// numb check icon_id
		$icon_counts = [];
		foreach ($data as $record) {
			$icon_id = $record['icon_id'];
			if (!isset($icon_counts[$icon_id])) {
				$icon_counts[$icon_id] = 0;
			}
			$icon_counts[$icon_id]++;
		}
		// ##
		// sort by number
		$icons_with_counts = [];
		foreach ($this->grab_icons() as $icon) {
			$icon_id = $icon['icon_id'];
			if (isset($icon_counts[$icon_id])) {
				$icons_with_counts[] = [
					'icon_url' => $icon['icon_url'],
					'icon_alt' => $icon['icon_alt'],
					'icon_id' => $icon_id,
					'count' => $icon_counts[$icon_id]
				];
			}
		}
		// sort for count DESC
		usort($icons_with_counts, function ($a, $b) {
			return $b['count'] - $a['count'];
		});
		// ##
		// template
		$event['topic_row'] = array_merge($event['topic_row'], [
			'ICONS'		 => $icons_with_counts,
			'PERM_W'		=> $this->auth->acl_get('u_new_sebo_postreact'),
			'PERM_R'		=> $this->auth->acl_get('u_new_sebo_postreact_view')
		]);
	}
	public function search_edit($event)
	{
		$row = $event['row'];
		// sql_escape because of potential inject (?)
		$topic_id = isset($row['topic_id']) ? (int)$row['topic_id'] : 0;
		$topic_id_escaped = $this->db->sql_escape($topic_id);
		$sql = 'SELECT * FROM ' . $this->table_prefix . 'sebo_postreact_table WHERE topic_id = ' . $topic_id_escaped;
		$result = $this->db->sql_query($sql);
		$filtered_rows = [];
		$post_ids = [];
		while ($my_row = $this->db->sql_fetchrow($result)) {
			$post_id = (int)$my_row['post_id'];
			$filtered_rows[$post_id] = $my_row;
			$post_ids[] = $post_id;
		}
		$this->db->sql_freeresult($result);
		$data = [];
		if (!empty($post_ids)) {
			$sql_post_exist = 'SELECT post_id FROM ' . $this->table_prefix . 'posts WHERE post_id IN (' . implode(',', $post_ids) . ')';
			$result_post_exist = $this->db->sql_query($sql_post_exist);
			$existing_posts = [];
			while ($row = $this->db->sql_fetchrow($result_post_exist)) {
				$existing_posts[$row['post_id']] = true;
			}
			$this->db->sql_freeresult($result_post_exist);
			foreach ($filtered_rows as $post_id => $row) {
				if (isset($existing_posts[$post_id])) {
					$data[] = $row;
				}
			}
		}
		// ##
		// numb check icon_id
		$topic_id_count = 0;
		foreach ($data as $record) {
			if ($record['topic_id'] == $topic_id) {
				$topic_id_count++;
			}
		}
		// start the counter
		$topic_id_count = 0;
		$data_ico = $this->grab_icons();
		// Step 1: make a new array with icon_id key
		$data_ico_assoc = [];
		foreach ($data_ico as $icon) {
			$data_ico_assoc[$icon['icon_id']] = $icon;
		}
		// Step 2: count icon_id occurrences for topic_id
		$icon_counts = [];
		foreach ($data as $rec) {
			$icon_id = $rec['icon_id'];
			$topic_id = $rec['topic_id'];
			if (!isset($icon_counts[$topic_id])) {
				$icon_counts[$topic_id] = [];
			}
			if (!isset($icon_counts[$topic_id][$icon_id])) {
				$icon_counts[$topic_id][$icon_id] = 0;
			}
			$icon_counts[$topic_id][$icon_id]++;
		}
		// Step 3: make new array with icon info and count, ensuring each icon_id is unique
		$new_array = [];
		foreach ($data as $rec) {
			$icon_id = $rec['icon_id'];
			$topic_id = $rec['topic_id'];
			if (isset($data_ico_assoc[$icon_id])) {
				// Only if the icon_id has not already been added to the final array
				if (!isset($new_array[$icon_id])) {
					$icon_info = $data_ico_assoc[$icon_id];
					$count = isset($icon_counts[$topic_id][$icon_id]) ? (string)$icon_counts[$topic_id][$icon_id] : '0';
					$new_array[$icon_id] = [
						'icon_id' => $icon_info['icon_id'],
						'icon_url' => $icon_info['icon_url'],
						'icon_alt' => $icon_info['icon_alt'],
						'topic_id' => $topic_id,
						'count' => $count
					];
				} else {
					// Update the count if icon_id already exists
					$new_array[$icon_id]['count'] = (string)max($new_array[$icon_id]['count'], $count);
				}
			}
		}
		// Remove the associative key and reset the numeric index
		$array_with_counts = array_values($new_array);
		// ##
		// template
		$event['tpl_ary'] = array_merge($event['tpl_ary'], [
			'ICONS'		 => $array_with_counts,
			'PERM_W'		=> $this->auth->acl_get('u_new_sebo_postreact'),
			'PERM_R'		=> $this->auth->acl_get('u_new_sebo_postreact_view')
		]);
	}
	/**
	 * Add permissions to the ACP -> Permissions settings page
	 * This is where permissions are assigned language keys and
	 * categories (where they will appear in the Permissions table):
	 * actions|content|forums|misc|permissions|pm|polls|post
	 * post_actions|posting|profile|settings|topic_actions|user_group
	 *
	 * Developers note: To control access to ACP, MCP and UCP modules, you
	 * must assign your permissions in your module_info.php file. For example,
	 * to allow only users with the a_new_sebo_postreact permission
	 * access to your ACP module, you would set this in your acp/main_info.php:
	 *	'auth' => 'ext_sebo/postreact && acl_a_new_sebo_postreact'
	 *
	 * @param \phpbb\event\data $event  Event object
	 */
	public function add_permissions($event)
	{
		$permissions = $event['permissions'];
		$permissions['u_new_sebo_postreact'] = ['lang' => 'ACL_U_NEW_SEBO_POSTREACT', 'cat' => 'post'];
		$permissions['u_new_sebo_postreact_view'] = ['lang' => 'ACL_U_NEW_SEBO_POSTREACT_VIEW', 'cat' => 'post'];
		$event['permissions'] = $permissions;
	}
	/**
	 * Edit profile for reaction count
	 */
	public function edit_view_profile($event)
	{
		$user_id = (int) $event['member']['user_id'];
		// *
		// Reactions sent
		$sql = 'SELECT icon_id, COUNT(*) AS icon_count
				FROM ' . $this->table_prefix . 'sebo_postreact_table
				WHERE user_id = ' . $user_id . '
				GROUP BY icon_id';
		$result = $this->db->sql_query($sql);
		$icon_counts = [];
		$icon_ids = [];
		while ($row = $this->db->sql_fetchrow($result)) {
			$icon_counts[$row['icon_id']] = $row['icon_count'];
			$icon_ids[] = (int) $row['icon_id'];
		}
		$this->db->sql_freeresult($result);
		$icons = [];
		if (!empty($icon_ids)) {
			// start list for IN (...)
			$icon_ids_list = implode(',', $icon_ids);
			// Query for active icons (status = 1)
			$sql = 'SELECT icon_id, icon_url, icon_width, icon_height, icon_alt
					FROM ' . $this->table_prefix . 'sebo_postreact_icon
					WHERE icon_id IN (' . $icon_ids_list . ') AND status = 1';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result)) {
				$id = (int) $row['icon_id'];
				if (isset($icon_counts[$id])) {
					$icons[] = [
						'ICON_ID'	 => $id,
						'ICON_COUNT'  => $icon_counts[$id],
						'ICON_URL'	=> $row['icon_url'],
						'ICON_WIDTH'  => $row['icon_width'],
						'ICON_HEIGHT' => $row['icon_height'],
						'ICON_ALT'	=> $row['icon_alt'],
					];
				}
			}
			$this->db->sql_freeresult($result);
		}
		// assign
		$this->profile_data['icons'] = $icons;
		// *
		// Reactions received
		$sql = 'SELECT pr.icon_id, COUNT(*) AS icon_count
				FROM ' . $this->table_prefix . 'sebo_postreact_table pr
				INNER JOIN ' . $this->table_prefix . 'posts p
					ON pr.post_id = p.post_id
				WHERE p.poster_id = ' . $user_id . '
				GROUP BY pr.icon_id';
		$result = $this->db->sql_query($sql);
		$received_icon_counts = [];
		$received_icon_ids = [];
		while ($row = $this->db->sql_fetchrow($result)) {
			$received_icon_counts[$row['icon_id']] = $row['icon_count'];
			$received_icon_ids[] = (int) $row['icon_id'];
		}
		$this->db->sql_freeresult($result);
		// grab
		$received_icons = [];
		if (!empty($received_icon_ids)) {
			$icon_ids_list = implode(',', $received_icon_ids);
			$sql = 'SELECT icon_id, icon_url, icon_width, icon_height, icon_alt
					FROM ' . $this->table_prefix . 'sebo_postreact_icon
					WHERE icon_id IN (' . $icon_ids_list . ') AND status = 1';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result)) {
				$id = (int) $row['icon_id'];
				if (isset($received_icon_counts[$id])) {
					$received_icons[] = [
						'ICON_ID'	 => $id,
						'ICON_COUNT'  => $received_icon_counts[$id],
						'ICON_URL'	=> $row['icon_url'],
						'ICON_WIDTH'  => $row['icon_width'],
						'ICON_HEIGHT' => $row['icon_height'],
						'ICON_ALT'	=> $row['icon_alt'],
					];
				}
			}
			$this->db->sql_freeresult($result);
		}
		// Assign
		$this->profile_data['icons_received'] = $received_icons;
	}
	public function assign_edit_view_profile($event)
	{
		if (!empty($this->profile_data)) {
			// Assign reaction sent
			if (!empty($this->profile_data['icons'])) {
				foreach ($this->profile_data['icons'] as $icon) {
					$this->template->assign_block_vars('user_reactions', [
						'ICON_ID'	 => $icon['ICON_ID'],
						'ICON_COUNT'  => $icon['ICON_COUNT'],
						'ICON_URL'	=> $icon['ICON_URL'],
						'ICON_WIDTH'  => $icon['ICON_WIDTH'],
						'ICON_HEIGHT' => $icon['ICON_HEIGHT'],
						'ICON_ALT'	=> $icon['ICON_ALT'],
					]);
				}
			}
			// Assign reaction received
			if (!empty($this->profile_data['icons_received'])) {
				foreach ($this->profile_data['icons_received'] as $icon) {
					$this->template->assign_block_vars('user_reactions_received', [
						'ICON_ID'	 => $icon['ICON_ID'],
						'ICON_COUNT'  => $icon['ICON_COUNT'],
						'ICON_URL'	=> $icon['ICON_URL'],
						'ICON_WIDTH'  => $icon['ICON_WIDTH'],
						'ICON_HEIGHT' => $icon['ICON_HEIGHT'],
						'ICON_ALT'	=> $icon['ICON_ALT'],
					]);
				}
			}
		}
	}
}