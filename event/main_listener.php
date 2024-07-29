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
			'core.user_setup'							=> 'load_language_on_setup',
			'core.viewtopic_assign_template_vars_before'=> 'grab_icons',
			'core.viewtopic_modify_post_row'			=> 'assign_to_template',
			'core.viewtopic_modify_page_title'			=> 'write_db',
			'core.viewforum_modify_page_title'			=> 'grab_icons',
			'core.viewforum_modify_topicrow' 			=> 'viewforum_edit',
			'core.permissions'							=> 'add_permissions',
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
			\phpbb\auth\auth $auth
			)
	{
		$this->language = $language;
		$this->db		= $db;
		$this->user		= $user;
		$this->request 	= $request;
		$this->template	= $template;
		$this->table_prefix = $table_prefix;
		$this->auth = $auth;
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
	
	public function grab_icons($event)
	{
		// ##
		// grab icons
		// take the line corresponds to post_id
		$sql_ico = 'SELECT * FROM ' . $this->table_prefix . 'sebo_postreact_icon';
		$data_ico = [];

		if ($sql_ico) {
			// do it
			$result_ico = $this->db->sql_query($sql_ico);
			$this->data_ico = [];

			if ($result_ico) {
				while ($my_icons = $this->db->sql_fetchrow($result_ico)) {
					$this->data_ico[] = $my_icons;
				}
				$this->db->sql_freeresult($result_ico);
			}
			$this->data_ico;
		}
	}

	public function assign_to_template($event)
	{
		$postrow = $event['postrow'];
		$row = $event['row'];
		
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
		// count icon_id
		$icon_counts = [];
		foreach ($this->data as $record) {
			$icon_id = $record['icon_id'];
			if (!isset($icon_counts[$icon_id])) {
				$icon_counts[$icon_id] = 0;
			}
			$icon_counts[$icon_id]++;
		}
		
		// ##
		// template
		$event['post_row'] = array_merge($event['post_row'], array(
				'N_REACTIONS' 	=> $total_match_count,
				'ICONS' 		=> $this->data_ico,
				'MY_PID' 		=> (int)$my_pid,
				'MY_TID' 		=> (int)$my_tid,
				'ICON_COUNTS' 	=> $icon_counts
		));
		
	}
	
	public function write_db()
	{
		// ##
		// request
		$user_id_logged = $this->user->data['user_id'];
		$my_topic_id = $this->request->variable('tid', 0, false);
		$my_post_id	= $this->request->variable('pid', 0, false);
		$my_icon_id	= $this->request->variable('iid', 0, false);
		$r_time = time();

		if ($user_id_logged !== null && $user_id_logged !== 1){
			if ($my_icon_id !== null && $my_post_id !== null && $my_topic_id !== null && $my_icon_id !== 0 && $my_post_id !== 0 && $my_topic_id !== 0 && is_numeric($my_topic_id) && is_numeric($my_icon_id) && is_numeric($my_post_id)) {
				$sql = "INSERT INTO `" . $this->table_prefix . "sebo_postreact_table` (`postreact_id`, `topic_id`, `post_id`, `user_id`, `icon_id`, `react_time`) VALUES (NULL, '$my_topic_id', '$my_post_id', '$user_id_logged', '$my_icon_id', '$r_time');";
				$result = $this->db->sql_query($sql);
				if($result){
					echo '	<script>
								alert("'.$this->user->lang('INSERTED_VALUE').'");
								window.location.replace("viewtopic.php?p='.$my_post_id.'#p='.$my_post_id.'");
							</script>';
					} else {
					echo '<script>
							alert("'.$this->user->lang('NOT_INSERTED_VALUE').'");
							window.location.replace("viewtopic.php?p='.$my_post_id.'#p='.$my_post_id.'");
						</script>';
					}
				$this->db->sql_freeresult($result);
			}
		} else {
			echo '<script>
					alert("'.$this->user->lang('LOGIN_TO_REACT').'");
					window.location.replace("viewtopic.php?p='.$my_post_id.'#p='.$my_post_id.'");
				</script>';
		}
	}
	
	public function viewforum_edit($event)
	{
		$topicrow = $event['topicrow'];
		$row = $event['row'];
		$topic_id = $row['topic_id'];
		$sql = 'SELECT * FROM ' . $this->table_prefix . 'sebo_postreact_table WHERE topic_id = ' . (int)$topic_id;
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
		// ##
		// numb check icon_id
		$icon_counts = [];
		foreach ($this->data as $record) {
			$icon_id = $record['icon_id'];
			if (!isset($icon_counts[$icon_id])) {
				$icon_counts[$icon_id] = 0;
			}
			$icon_counts[$icon_id]++;
		}
		// ##
		// sort by number
		$icons_with_counts = [];
		foreach ($this->data_ico as $icon) {
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
		usort($icons_with_counts, function($a, $b) {
			return $b['count'] - $a['count'];
		});

		// ##
		// template
		$event['topic_row'] = array_merge($event['topic_row'], [
			'ICONS' => $icons_with_counts
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
	 *    'auth' => 'ext_sebo/postreact && acl_a_new_sebo_postreact'
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function add_permissions($event)
	{
		$permissions = $event['permissions'];

		$permissions['a_new_sebo_postreact'] = ['lang' => 'ACL_A_NEW_SEBO_POSTREACT', 'cat' => 'misc'];
		$permissions['m_new_sebo_postreact'] = ['lang' => 'ACL_M_NEW_SEBO_POSTREACT', 'cat' => 'post_actions'];
		$permissions['u_new_sebo_postreact'] = ['lang' => 'ACL_U_NEW_SEBO_POSTREACT', 'cat' => 'post'];

		$event['permissions'] = $permissions;
	}
}
