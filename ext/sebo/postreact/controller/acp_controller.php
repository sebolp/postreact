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
	/** @var \phpbb\language\language */
	protected $language;
	/** @var \phpbb\request\request */
	protected $request;
	/** @var \phpbb\template\template */
	protected $template;
	/** @var \phpbb\user */
	protected $user;
	/** @var string Custom form action */
	protected $u_action;
	protected $table_prefix;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	/** @var php_ext */
	protected $php_ext;
	/** @var \phpbb\config\config */
	protected $config;
	/** @var string phpBB root path */
	protected $phpbb_root_path;
	/** @var string Path to reaction images */
	protected $url_reactions_path = 'images/sebo_postreact/reactions/';
	/**
	 * Constructor.
	 *
	 * @param \phpbb\language\language	$language	Language object
	 * @param \phpbb\log\log			$log		Log object
	 * @param \phpbb\request\request	$request	Request object
	 * @param \phpbb\template\template	$template	Template object
	 * @param \phpbb\user				$user		User object
	 */
	public function __construct(
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$table_prefix,
		\phpbb\db\driver\driver_interface $db,
		$php_ext,
		\phpbb\config\config $config,
		$phpbb_root_path
		)
	{
		$this->language	= $language;
		$this->request	= $request;
		$this->template	= $template;
		$this->user		= $user;
		$this->table_prefix = $table_prefix;
		$this->db		= $db;
		$this->php_ext = $php_ext;
		$this->config   = $config;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	 * Recursively remove a directory and its contents.
	 *
	 * @param string $path Path to the directory
	 * @return void
	 */
	private function remove_directory($path)
	{
		if (!is_dir($path))
		{
			return;
		}

		$files = array_diff(scandir($path), ['.', '..']);

		foreach ($files as $file)
		{
			$current_path = $path . DIRECTORY_SEPARATOR . $file;

			if (is_dir($current_path))
			{
				$this->remove_directory($current_path);
			}
			else
			{
				unlink($current_path);
			}
		}

		rmdir($path);
	}
	/**
	 * 	Check if directory exists to display error
	 */
	private function check_pr_directory($path)
	{
		return is_dir($path);
	}
	/**
	 * Display the options a user can configure for this extension.
	 *
	 * @return void
	 */
	public function display_options()
	{
		// Add our common language file
		$this->language->add_lang('common', 'sebo/postreact');
		$this->language->add_lang('permissions_postreact', 'sebo/postreact');
		$sid_pr = $this->request->variable('user_sid', \phpbb\request\request_interface::COOKIE);
		// ##
		// check if adding icon
		$add_pr = $this->request->variable('add_pr', 0, false);
		if ($add_pr === 1)
		{
			$sql_array = [
							'SELECT'    => 'icon_id',
							'FROM'      => [$this->table_prefix . 'sebo_postreact_icon' => ''],
							'ORDER_BY'     => 'icon_id DESC',
						];
			$sql_last = $this->db->sql_build_query('SELECT', $sql_array);
			$result_last = $this->db->sql_query_limit($sql_last, 1);
			$last_id = $this->db->sql_fetchrow($result_last);
			$this->db->sql_freeresult($result_last);
			$new_id = $last_id['icon_id'] + 1;
			// Default array creating
			$data = array(
				'icon_id'    => $new_id, // icon_id
				'icon_url'   => $this->url_reactions_path . 'default.svg', // icon_url
				'icon_width' => 32, // icon_width
				'icon_height'=> 32, // icon_height
				'icon_alt'   => '', // icon_alt
				'status'     => 0,  // status
				'icon_emoji' => '', // emoji
			);
			$sql_insert = 'INSERT INTO ' . $this->table_prefix . 'sebo_postreact_icon ' .
						  $this->db->sql_build_array('INSERT', $data);
			$this->db->sql_query($sql_insert);
		}
		//##
		// check if deleting icon
		$remove_pr = $this->request->variable('remove_pr', 0, false);
		if ($remove_pr != null)
		{
			$sql_remove = 'DELETE FROM ' . $this->table_prefix . 'sebo_postreact_icon
								WHERE icon_id = ' . (int) $remove_pr;
			$result_remove = $this->db->sql_query($sql_remove);
		}
		// ##
		// display error if directory not exists
		if (!$this->check_pr_directory($this->phpbb_root_path . $this->url_reactions_path))
		{
			$this->template->assign_var('PR_DIR_NOT_EXISTS', true);
		}
		// ##
		// grab icons
		// take the line corresponds to post_id
		$data_ico = [];
		$sql_array = [
				'SELECT'    => '*',
				'FROM'      => [$this->table_prefix . 'sebo_postreact_icon' => ''],
			];
		$sql_ico = $this->db->sql_build_query('SELECT', $sql_array);
		$result_ico = $this->db->sql_query($sql_ico);
		if ($result_ico)
		{
			while ($my_icons = $this->db->sql_fetchrow($result_ico))
			{
				// Decodifichiamo l'entità per mostrare l'emoji vera nel campo di input
				$my_icons['icon_emoji'] = html_entity_decode($my_icons['icon_emoji']);
				$data_ico[] = $my_icons;
			}
			$this->db->sql_freeresult($result_ico);
		}
		// ## main one
		add_form_key('sebo_postreact_acp');
		$errors = [];
		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('sebo_postreact_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}
			// If no errors, process the form data
			if (empty($errors))
			{

				// save self_react config
				// Checkbox on -> want value 0
				// Checkbox null -> want value 1 (denied)
				$self_react_status = $this->request->variable('config_self_react', '');
				$new_config_value = ($self_react_status === 'on') ? 0 : 1;

				$this->config->set('sebo_postreact_self_react', $new_config_value);
				// end

				// save display_position config
				// Checkbox on -> want value 0 - right
				// Checkbox null -> want value 1 - left
				$display_position_status = $this->request->variable('config_display_position', '');
				$new_display_position_value = ($display_position_status === 'on') ? 0 : 1;

				$this->config->set('sebo_postreact_display_position', $new_display_position_value);
				// end

				$update_data = [];
				$icon_ids = $this->request->variable('icon_ids', [0]);
				if (!is_array($icon_ids))
				{
					$icon_ids = explode(',', $icon_ids);
				}
				foreach ($icon_ids as $icon_id)
				{
					// grab variables
					$update_data[$icon_id] = [
						'url' => $this->request->variable('icon_url_' . (int) $icon_id, ''),
						'icon_alt' => $this->request->variable('icon_alt_' . (int) $icon_id, '', true),
						'icon_width' => $this->request->variable('icon_width_' . (int) $icon_id, ''),
						'icon_height' => $this->request->variable('icon_height_' . (int) $icon_id, ''),
						'status' => $this->request->variable('status_' . (int) $icon_id, '') === 'on' ? 1 : 0,
						'icon_emoji' => $this->request->variable('icon_emoji_' . (int) $icon_id, '', true),
					];
				}
				$all_queries_successful = true;
				$failed_icon_urls = [];

				foreach ($update_data as $icon_id => $data)
				{
					$emoji_safe = mb_convert_encoding($data['icon_emoji'], 'HTML-ENTITIES', 'UTF-8');
					$sql_array = [
						'icon_url'    => $this->url_reactions_path . $data['url'],
						'icon_alt'    => $data['icon_alt'],
						'icon_width'  => (int) $data['icon_width'],
						'icon_height' => (int) $data['icon_height'],
						'status'      => (int) $data['status'],
						'icon_emoji'  => $emoji_safe,
					];

					$sql = 'UPDATE ' . $this->table_prefix . 'sebo_postreact_icon SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE icon_id = ' . (int) $icon_id;

					$query_result = $this->db->sql_query($sql);

					if (!$query_result)
					{
						$all_queries_successful = false;
						$failed_icon_urls[] = $data['url'];
					}
				}

				if ($all_queries_successful)
				{
					trigger_error($this->language->lang('ACP_POSTREACT_SETTING_SAVED') . adm_back_link($this->u_action));
				} else
				{
					$error_message = $this->language->lang('ACP_POSTREACT_SETTING_NOT_SAVED');
					if (!empty($failed_icon_urls))
					{
						$error_message .= '<br />' . $this->language->lang('POSTREACT_FAILED_ICONS') . ': ' . implode(', ', $failed_icon_urls);
					}
					trigger_error($error_message . adm_back_link($this->u_action));
				}
			}
		}
		// Create urlS
		$delete_url = append_sid("index.{$this->php_ext}") . "&i=-sebo-postreact-acp-main_module&remove_pr=";
		$create_url = append_sid("index.{$this->php_ext}") . "&i=-sebo-postreact-acp-main_module&add_pr=1";
		$s_errors = !empty($errors);

		$this->template->assign_vars([
			'DISPLAY_OPTION_VAL' => (int) $this->config['sebo_postreact_display_position'],
			'SELF_REACT_VAL' => (int) $this->config['sebo_postreact_self_react'],
			'ICONS' 		=> $data_ico,
			'SID'			=> $sid_pr,
			'ARROW' 		=> '<i class="fa icon fa-chevron-right fa-fw" aria-hidden="true"></i>',
			'S_ERROR'		=> $s_errors,
			'ERROR_MSG'		=> $s_errors ? implode('<br />', $errors) : '',
			'DELETE_PR_URL'	=> $delete_url,
			'CREATE_PR_URL' => $create_url,
			'U_ACTION'		=> $this->u_action,
			'LINK_DONATE'	=> 'https://www.paypal.com/donate/?hosted_button_id=GS3T9MFDJJGT4',
			'PR_FOLDER_PATH'=> $this->url_reactions_path,
			'EMOJI_JS_PATH' => $this->phpbb_root_path . 'ext/sebo/postreact/adm/style/emoji_picker.js'
		]);

		// purge module
		if ($this->request->is_set_post('sync'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('sebo_postreact_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}
			// If no errors, process the form data
			if (empty($errors))
			{
				// take query time
				$start_pr_sync_time = microtime(true);

				// Count
				$sql_count = 'SELECT COUNT(*) AS total_missing
							  FROM ' . $this->table_prefix . 'sebo_postreact_table s
							  LEFT JOIN ' . $this->table_prefix . 'posts p
								  ON s.post_id = p.post_id
							  WHERE p.post_id IS NULL';
				$result = $this->db->sql_query($sql_count);
				$total_missing = (int) $this->db->sql_fetchfield('total_missing');
				$this->db->sql_freeresult($result);

				// Delete
				$sql_delete = 'DELETE s
							   FROM ' . $this->table_prefix . 'sebo_postreact_table s
							   LEFT JOIN ' . $this->table_prefix . 'posts p
								   ON s.post_id = p.post_id
							   WHERE p.post_id IS NULL';
				$this->db->sql_query($sql_delete);

				// Stop time
				$execution_pr_sync_time = microtime(true) - $start_pr_sync_time;

				meta_refresh(5, $this->u_action);
				$message = $this->language->lang('PR_SYNCSYSTEM_UPDATED', $total_missing, $execution_pr_sync_time) . '<br /><br />' . $this->language->lang('RETURN_ACP', $this->u_action);
				trigger_error($message);
			}
		}
		if ($this->request->is_set_post('purge'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('sebo_postreact_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}
			// If no errors, process the form data
			if (empty($errors))
			{
				// Start timer
				$start_pr_purge_time = microtime(true);

				// Count before
				$sql_count = 'SELECT COUNT(*) AS total_rows
							  FROM ' . $this->table_prefix . 'sebo_postreact_table';
				$result = $this->db->sql_query($sql_count);
				$total_deleted = (int) $this->db->sql_fetchfield('total_rows');
				$this->db->sql_freeresult($result);

				// do it
				$sql_truncate = 'TRUNCATE TABLE ' . $this->table_prefix . 'sebo_postreact_table';
				$this->db->sql_query($sql_truncate);

				// Stop time
				$execution_pr_purge_time = microtime(true) - $start_pr_purge_time;

				meta_refresh(5, $this->u_action);
				$message = $this->language->lang('PR_PURGESYSTEM_UPDATED', $total_deleted, $execution_pr_purge_time) . '<br /><br />' . $this->language->lang('RETURN_ACP', $this->u_action);

				trigger_error($message);
			}
		}
		if ($this->request->is_set_post('purge_ico'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('sebo_postreact_purge'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}
			// If no errors, process the form data
			if (empty($errors))
			{
				// Start timer
				$start_pr_purge_time = microtime(true);

				// Count before
				$sql_count = 'SELECT COUNT(*) AS total_rows
							  FROM ' . $this->table_prefix . 'sebo_postreact_icon';
				$result = $this->db->sql_query($sql_count);
				$total_deleted = (int) $this->db->sql_fetchfield('total_rows');
				$this->db->sql_freeresult($result);

				// do it
				$sql_truncate = 'TRUNCATE TABLE ' . $this->table_prefix . 'sebo_postreact_icon';
				$this->db->sql_query($sql_truncate);

				// Stop time
				$execution_pr_purge_time = microtime(true) - $start_pr_purge_time;

				meta_refresh(5, $this->u_action);
				$message = $this->language->lang('PR_PURGEICOSYSTEM_UPDATED', $total_deleted, $execution_pr_purge_time) . '<br /><br />' . $this->language->lang('RETURN_ACP', $this->u_action);

				trigger_error($message);
			}
		}

		if ($this->request->is_set_post('purge_reaction_folder'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('sebo_postreact_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}
			// If no errors, process the form data
			if (empty($errors))
			{
				// Step 1: Get the full path to reactions: root/images/sebo_postreact/reactions/
				$reaction_subfolder = $this->phpbb_root_path . $this->url_reactions_path;

				// Step 2: Get the parent folder: root/images/sebo_postreact/
				// dirname() strips the last folder from the path
				$extension_main_folder = dirname($reaction_subfolder);

				// Step 3: Remove the entire extension folder in images
				// This removes sebo_postreact and everything inside it (including reactions)
				$this->remove_directory($extension_main_folder);

				meta_refresh(5, $this->u_action);
				$message = $this->language->lang('PR_PURGE_FOLDER_DONE') . '<br /><br />' . $this->language->lang('RETURN_ACP', $this->u_action);

				trigger_error($message);
			}
		}
	}

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
