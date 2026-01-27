<?php
/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, sebo
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\migrations;

class install_data_2_3 extends \phpbb\db\migration\container_aware_migration
{
	/** @var \phpbb\filesystem\filesystem */
	protected $filesystem;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $source_dir;

	/** @var string */
	protected $dest_dir;

	/**
	 * Initialize class properties
	 */
	protected function init()
	{
		if (!isset($this->filesystem))
		{
			$this->filesystem = $this->container->get('filesystem');
			$this->phpbb_root_path = $this->container->getParameter('core.root_path');
			
			$this->source_dir = $this->phpbb_root_path . 'ext/sebo/postreact/styles/all/img';
			$this->dest_dir = $this->phpbb_root_path . 'images/sebo_postreact/reactions';
		}
	}

	/**
	 * Check if the migration is already installed
	 *
	 * @return bool
	 */
	public function effectively_installed()
	{
		$this->init();

		// If the destination directory exists AND the source is gone, consider it done
		return $this->filesystem->exists($this->dest_dir) && !$this->filesystem->exists($this->source_dir);
	}

	public static function depends_on()
	{
		return ['\sebo\postreact\migrations\install_data_2_1_1'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'sebo_postreact_icon' => [
					'icon_emoji' => ['VCHAR:50', ''],
				],
			],
			'drop_columns' => [
				$this->table_prefix . 'sebo_postreact_icon' => [
					'active',
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'sebo_postreact_icon' => [
					'icon_emoji',
				],
			],
		];
	}

	public function update_data(): array
	{
		return [
			// Run the custom function to migrate images
			['custom', [[$this, 'move_images_to_store']]],
			// Update icon paths
			['custom', [[$this, 'update_icon_paths']]],
			// Update emojis
			['custom', [[$this, 'update_emojis']]],
		];
	}

	public function revert_data(): array
	{
		// No default revert actions, delete images manually if needed
		return [
		];
	}

	/**
	 * Update the icon_url paths in the database
	 */
	public function update_icon_paths()
	{
		$old_path = 'ext/sebo/postreact/styles/all/img/';
		$new_path = 'images/sebo_postreact/reactions/';

		$sql = 'UPDATE ' . $this->table_prefix . 'sebo_postreact_icon
			SET icon_url = REPLACE(icon_url, \'' . $this->db->sql_escape($old_path) . '\', \'' . $this->db->sql_escape($new_path) . '\')
			WHERE icon_url LIKE \'' . $this->db->sql_escape($old_path) . '%\'';

		$this->db->sql_query($sql);
	}

	/**
	 * Update the emoji field in the database based on icon names
	 */
	public function update_emojis()
	{
		// Mapping of icon names to emojis HTML entities
		$icon_to_emoji = [
			'like.png'			=> '&#128077;',
			'heart.png'			=> '&#10084;&#65039;',
			'laugh.png'			=> '&#128514;',
			'sad.png'			=> '&#128546;',
			'angry.png'			=> '&#128545;',
			'surprise.png'		=> '&#128559;',
			'sunglasses.png'	=> '&#128526;',
			'love.png'			=> '&#128525;',
			'worker.png'		=> '&#128104;&zwj;&#128295;',
			'lol.png'			=> '&#129315;',
			'party.png'			=> '&#129395;',
			'mechanic.png'		=> '&#129489;&zwj;&#128295;',
			'cry.png'			=> '&#128557;',
			'censored.png'		=> '&#129324;',
			'waving.webp'		=> '&#128075;',
		];

		foreach ($icon_to_emoji as $icon_name => $emoji)
		{
			// Using LIKE to match the filename at the end of the path
			$sql = 'UPDATE ' . $this->table_prefix . 'sebo_postreact_icon
				SET icon_emoji = \'' . $this->db->sql_escape($emoji) . '\'
				WHERE icon_url LIKE \'%' . $this->db->sql_escape($icon_name) . '\'';

			$this->db->sql_query($sql);
		}
	}

	/**
	 * Relocation of images and cleanup
	 */
	public function move_images_to_store()
	{
		$this->init();

		// Check if source directory exists before proceeding
		if (!$this->filesystem->exists($this->source_dir))
		{
			return;
		}

		try
		{
			// Create destination directory if it does not exist
			if (!$this->filesystem->exists($this->dest_dir))
			{
				$this->filesystem->mkdir($this->dest_dir, 0755);
			}

			// Start recursive copy
			$this->copy_recursive($this->source_dir, $this->dest_dir);

			// Delete the source directory and all its content after copying
			$this->filesystem->remove($this->source_dir);
		}
		catch (\phpbb\filesystem\exception\filesystem_exception $e)
		{
			// Log or handle any errors here using $e->get_filename() or $e->getMessage()
			// If copy fails, we do not delete the source
		}
	}

	/**
	 * Helper function to copy files and directories recursively using the filesystem service
	 *
	 * @param string $src Source path
	 * @param string $dst Destination path
	 */
	protected function copy_recursive($src, $dst)
	{
		$dir = opendir($src);

		// Ensure the specific sub-destination exists
		if (!$this->filesystem->exists($dst))
		{
			$this->filesystem->mkdir($dst, 0755);
		}

		while (false !== ($file = readdir($dir)))
		{
			if (($file != '.') && ($file != '..'))
			{
				$src_file = $src . '/' . $file;
				$dst_file = $dst . '/' . $file;

				if (is_dir($src_file))
				{
					// Recursive call for subdirectories
					$this->copy_recursive($src_file, $dst_file);
				}
				else
				{
					$this->filesystem->copy($src_file, $dst_file);
				}
			}
		}
		
		closedir($dir);
	}
}
