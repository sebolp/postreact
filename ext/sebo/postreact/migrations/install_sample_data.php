<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, sebo, https://www.fiatpandaclub.org - Thanks Chris1278!
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\migrations;

class install_sample_data extends \phpbb\db\migration\migration
{
 '`t' * ($matches[0].Length / 4) public static function depends_on()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) return ['\sebo\postreact\migrations\install_sample_schema'];
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Add, update or delete data stored in the database during extension installation.
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * https://area51.phpbb.com/docs/dev/3.2.x/migrations/data_changes.html
 "`t" * ($matches[0].Length / 4)  *  config.add: Add config data.
 "`t" * ($matches[0].Length / 4)  *  config.update: Update config data.
 "`t" * ($matches[0].Length / 4)  *  config.remove: Remove config.
 "`t" * ($matches[0].Length / 4)  *  config_text.add: Add config_text data.
 "`t" * ($matches[0].Length / 4)  *  config_text.update: Update config_text data.
 "`t" * ($matches[0].Length / 4)  *  config_text.remove: Remove config_text.
 "`t" * ($matches[0].Length / 4)  *  module.add: Add a new CP module.
 "`t" * ($matches[0].Length / 4)  *  module.remove: Remove a CP module.
 "`t" * ($matches[0].Length / 4)  *  permission.add: Add a new permission.
 "`t" * ($matches[0].Length / 4)  *  permission.remove: Remove a permission.
 "`t" * ($matches[0].Length / 4)  *  permission.role_add: Add a new permission role.
 "`t" * ($matches[0].Length / 4)  *  permission.role_update: Update a permission role.
 "`t" * ($matches[0].Length / 4)  *  permission.role_remove: Remove a permission role.
 "`t" * ($matches[0].Length / 4)  *  permission.permission_set: Set a permission to Yes or Never.
 "`t" * ($matches[0].Length / 4)  *  permission.permission_unset: Set a permission to No.
 "`t" * ($matches[0].Length / 4)  *  custom: Run a callable function to perform more complex operations.
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @return array Array of data update instructions
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function update_data()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) return [
 '`t' * ($matches[0].Length / 4) // Add new config table settings
 '`t' * ($matches[0].Length / 4)['config.add', ['sebo_postreact', 0]],

 '`t' * ($matches[0].Length / 4) // Add new permissions
 '`t' * ($matches[0].Length / 4)['permission.add', ['u_new_sebo_postreact']], // New user permission - need only u_
 '`t' * ($matches[0].Length / 4)['permission.add', ['u_new_sebo_postreact_view']], // New user permission view - need only u_

 '`t' * ($matches[0].Length / 4) // Set our new permissions
 '`t' * ($matches[0].Length / 4)['permission.permission_set', ['ROLE_USER_FULL', 'u_new_sebo_postreact', 1]], // Give ROLE_USER_FULL u_new_sebo_postreact permission
 '`t' * ($matches[0].Length / 4)['permission.permission_set', ['ROLE_USER_STANDARD', 'u_new_sebo_postreact', 1]], // Give ROLE_USER_STANDARD u_new_sebo_postreact permission
 '`t' * ($matches[0].Length / 4)['permission.permission_set', ['REGISTERED', 'u_new_sebo_postreact', 'group', 1]], // Give REGISTERED group u_new_sebo_postreact permission Y
 '`t' * ($matches[0].Length / 4)['permission.permission_set', ['ADMINISTRATORS', 'u_new_sebo_postreact', 'group', 1]],
 '`t' * ($matches[0].Length / 4)['permission.permission_set', ['GLOBAL_MODERATORS', 'u_new_sebo_postreact', 'group', 1]],
 '`t' * ($matches[0].Length / 4)['permission.permission_set', ['NEWLY_REGISTERED', 'u_new_sebo_postreact', 'group', false]], // N
 '`t' * ($matches[0].Length / 4)['permission.permission_set', ['BOTS', 'u_new_sebo_postreact', 'group', false]],
 '`t' * ($matches[0].Length / 4)['permission.permission_set', ['REGISTERED_COPPA', 'u_new_sebo_postreact', 'group', false]], // Set u_new_sebo_postreact to never for REGISTERED_COPPA
 '`t' * ($matches[0].Length / 4) // All view
 '`t' * ($matches[0].Length / 4)['permission.permission_set', ['ROLE_USER_FULL', 'u_new_sebo_postreact_view', 1]],
 '`t' * ($matches[0].Length / 4)['permission.permission_set', ['ROLE_USER_STANDARD', 'u_new_sebo_postreact_view', 1]],
 '`t' * ($matches[0].Length / 4)['permission.permission_set', ['REGISTERED', 'u_new_sebo_postreact_view', 'group', 1]],
 '`t' * ($matches[0].Length / 4)['permission.permission_set', ['ADMINISTRATORS', 'u_new_sebo_postreact_view', 'group', 1]],
 '`t' * ($matches[0].Length / 4)['permission.permission_set', ['GLOBAL_MODERATORS', 'u_new_sebo_postreact_view', 'group', 1]],
 '`t' * ($matches[0].Length / 4)['permission.permission_set', ['NEWLY_REGISTERED', 'u_new_sebo_postreact_view', 'group', 1]],
 '`t' * ($matches[0].Length / 4)['permission.permission_set', ['BOTS', 'u_new_sebo_postreact_view', 'group', 1]],
 '`t' * ($matches[0].Length / 4)['permission.permission_set', ['REGISTERED_COPPA', 'u_new_sebo_postreact_view', 'group', 1]],

 '`t' * ($matches[0].Length / 4) // Call a custom callable function to perform more complex operations.
 '`t' * ($matches[0].Length / 4)['custom', [[$this, 'table_pr_install']]],
 '`t' * ($matches[0].Length / 4) ];
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Add, update or delete data stored in the database during extension un-installation (purge step).
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * IMPORTANT: Under normal circumstances, the changes performed in update_data will
 "`t" * ($matches[0].Length / 4)  * automatically be reverted during un-installation. This revert_data method is optional
 "`t" * ($matches[0].Length / 4)  * and only needs to be used to perform custom un-installation changes, such as to revert
 "`t" * ($matches[0].Length / 4)  * changes made by custom functions called in update_data.
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * https://area51.phpbb.com/docs/dev/3.2.x/migrations/data_changes.html
 "`t" * ($matches[0].Length / 4)  *  config.add: Add config data.
 "`t" * ($matches[0].Length / 4)  *  config.update: Update config data.
 "`t" * ($matches[0].Length / 4)  *  config.remove: Remove config.
 "`t" * ($matches[0].Length / 4)  *  config_text.add: Add config_text data.
 "`t" * ($matches[0].Length / 4)  *  config_text.update: Update config_text data.
 "`t" * ($matches[0].Length / 4)  *  config_text.remove: Remove config_text.
 "`t" * ($matches[0].Length / 4)  *  module.add: Add a new CP module.
 "`t" * ($matches[0].Length / 4)  *  module.remove: Remove a CP module.
 "`t" * ($matches[0].Length / 4)  *  permission.add: Add a new permission.
 "`t" * ($matches[0].Length / 4)  *  permission.remove: Remove a permission.
 "`t" * ($matches[0].Length / 4)  *  permission.role_add: Add a new permission role.
 "`t" * ($matches[0].Length / 4)  *  permission.role_update: Update a permission role.
 "`t" * ($matches[0].Length / 4)  *  permission.role_remove: Remove a permission role.
 "`t" * ($matches[0].Length / 4)  *  permission.permission_set: Set a permission to Yes or Never.
 "`t" * ($matches[0].Length / 4)  *  permission.permission_unset: Set a permission to No.
 "`t" * ($matches[0].Length / 4)  *  custom: Run a callable function to perform more complex operations.
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @return array Array of data update instructions
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function revert_data()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) return [
 '`t' * ($matches[0].Length / 4)['config.remove', ['sebo_postreact', 0]],

 '`t' * ($matches[0].Length / 4)['permission.remove', ['u_new_sebo_postreact']],
 '`t' * ($matches[0].Length / 4)['permission.remove', ['u_new_sebo_postreact_view']],
 '`t' * ($matches[0].Length / 4) ];
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * A custom function for making more complex database changes
 "`t" * ($matches[0].Length / 4)  * during extension installation. Must be declared as public.
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function table_pr_install()
 '`t' * ($matches[0].Length / 4)
 {

 '`t' * ($matches[0].Length / 4) $data = [
 '`t' * ($matches[0].Length / 4)['`t' * ($matches[0].Length / 4) 'id'            => 1,
 '`t' * ($matches[0].Length / 4) 'icon_id'       => 1,
 '`t' * ($matches[0].Length / 4) 'icon_url'      => 'ext/sebo/postreact/styles/all/img/like.png',
 '`t' * ($matches[0].Length / 4) 'icon_width'    => 32,
 '`t' * ($matches[0].Length / 4) 'icon_height'   => 32,
 '`t' * ($matches[0].Length / 4) 'icon_alt'      => 'Like',
 '`t' * ($matches[0].Length / 4) 'status'        => '1',
 '`t' * ($matches[0].Length / 4) 'active'        => '0',
 '`t' * ($matches[0].Length / 4)],
 '`t' * ($matches[0].Length / 4)['`t' * ($matches[0].Length / 4) 'id'            => 2,
 '`t' * ($matches[0].Length / 4) 'icon_id'       => 2,
 '`t' * ($matches[0].Length / 4) 'icon_url'      => 'ext/sebo/postreact/styles/all/img/heart.png',
 '`t' * ($matches[0].Length / 4) 'icon_width'    => 32,
 '`t' * ($matches[0].Length / 4) 'icon_height'   => 32,
 '`t' * ($matches[0].Length / 4) 'icon_alt'      => 'Heart',
 '`t' * ($matches[0].Length / 4) 'status'        => '1',
 '`t' * ($matches[0].Length / 4) 'active'        => '0',
 '`t' * ($matches[0].Length / 4)],
 '`t' * ($matches[0].Length / 4)['`t' * ($matches[0].Length / 4) 'id'            => 3,
 '`t' * ($matches[0].Length / 4) 'icon_id'       => 3,
 '`t' * ($matches[0].Length / 4) 'icon_url'      => 'ext/sebo/postreact/styles/all/img/laugh.png',
 '`t' * ($matches[0].Length / 4) 'icon_width'    => 32,
 '`t' * ($matches[0].Length / 4) 'icon_height'   => 32,
 '`t' * ($matches[0].Length / 4) 'icon_alt'      => 'Laught',
 '`t' * ($matches[0].Length / 4) 'status'        => '1',
 '`t' * ($matches[0].Length / 4) 'active'        => '0',
 '`t' * ($matches[0].Length / 4)],
 '`t' * ($matches[0].Length / 4)['`t' * ($matches[0].Length / 4) 'id'            => 4,
 '`t' * ($matches[0].Length / 4) 'icon_id'       => 4,
 '`t' * ($matches[0].Length / 4) 'icon_url'      => 'ext/sebo/postreact/styles/all/img/sad.png',
 '`t' * ($matches[0].Length / 4) 'icon_width'    => 32,
 '`t' * ($matches[0].Length / 4) 'icon_height'   => 32,
 '`t' * ($matches[0].Length / 4) 'icon_alt'      => 'Sad',
 '`t' * ($matches[0].Length / 4) 'status'        => '1',
 '`t' * ($matches[0].Length / 4) 'active'        => '0',
 '`t' * ($matches[0].Length / 4)],
 '`t' * ($matches[0].Length / 4)['`t' * ($matches[0].Length / 4) 'id'            => 5,
 '`t' * ($matches[0].Length / 4) 'icon_id'       => 5,
 '`t' * ($matches[0].Length / 4) 'icon_url'      => 'ext/sebo/postreact/styles/all/img/angry.png',
 '`t' * ($matches[0].Length / 4) 'icon_width'    => 32,
 '`t' * ($matches[0].Length / 4) 'icon_height'   => 32,
 '`t' * ($matches[0].Length / 4) 'icon_alt'      => 'Angry',
 '`t' * ($matches[0].Length / 4) 'status'        => '1',
 '`t' * ($matches[0].Length / 4) 'active'        => '0',
 '`t' * ($matches[0].Length / 4)],
 '`t' * ($matches[0].Length / 4)['`t' * ($matches[0].Length / 4) 'id'            => 6,
 '`t' * ($matches[0].Length / 4) 'icon_id'       => 6,
 '`t' * ($matches[0].Length / 4) 'icon_url'      => 'ext/sebo/postreact/styles/all/img/surprise.png',
 '`t' * ($matches[0].Length / 4) 'icon_width'    => 32,
 '`t' * ($matches[0].Length / 4) 'icon_height'   => 32,
 '`t' * ($matches[0].Length / 4) 'icon_alt'      => 'Suprise',
 '`t' * ($matches[0].Length / 4) 'status'        => '1',
 '`t' * ($matches[0].Length / 4) 'active'        => '0',
 '`t' * ($matches[0].Length / 4)],
 '`t' * ($matches[0].Length / 4)['`t' * ($matches[0].Length / 4) 'id'            => 7,
 '`t' * ($matches[0].Length / 4) 'icon_id'       => 7,
 '`t' * ($matches[0].Length / 4) 'icon_url'      => 'ext/sebo/postreact/styles/all/img/sunglasses.png',
 '`t' * ($matches[0].Length / 4) 'icon_width'    => 32,
 '`t' * ($matches[0].Length / 4) 'icon_height'   => 32,
 '`t' * ($matches[0].Length / 4) 'icon_alt'      => 'Cool',
 '`t' * ($matches[0].Length / 4) 'status'        => '1',
 '`t' * ($matches[0].Length / 4) 'active'        => '0',
 '`t' * ($matches[0].Length / 4)],
 '`t' * ($matches[0].Length / 4)['`t' * ($matches[0].Length / 4) 'id'            => 8,
 '`t' * ($matches[0].Length / 4) 'icon_id'       => 8,
 '`t' * ($matches[0].Length / 4) 'icon_url'      => 'ext/sebo/postreact/styles/all/img/love.png',
 '`t' * ($matches[0].Length / 4) 'icon_width'    => 32,
 '`t' * ($matches[0].Length / 4) 'icon_height'   => 32,
 '`t' * ($matches[0].Length / 4) 'icon_alt'      => 'Love',
 '`t' * ($matches[0].Length / 4) 'status'        => '0',
 '`t' * ($matches[0].Length / 4) 'active'        => '0',
 '`t' * ($matches[0].Length / 4)],
 '`t' * ($matches[0].Length / 4)['`t' * ($matches[0].Length / 4) 'id'            => 9,
 '`t' * ($matches[0].Length / 4) 'icon_id'       => 9,
 '`t' * ($matches[0].Length / 4) 'icon_url'      => 'ext/sebo/postreact/styles/all/img/skype/worker.png',
 '`t' * ($matches[0].Length / 4) 'icon_width'    => 32,
 '`t' * ($matches[0].Length / 4) 'icon_height'   => 32,
 '`t' * ($matches[0].Length / 4) 'icon_alt'      => 'Worker',
 '`t' * ($matches[0].Length / 4) 'status'        => '1',
 '`t' * ($matches[0].Length / 4) 'active'        => '0',
 '`t' * ($matches[0].Length / 4)],
 '`t' * ($matches[0].Length / 4)['`t' * ($matches[0].Length / 4) 'id'            => 10,
 '`t' * ($matches[0].Length / 4) 'icon_id'       => 10,
 '`t' * ($matches[0].Length / 4) 'icon_url'      => 'ext/sebo/postreact/styles/all/img/skype/lol.png',
 '`t' * ($matches[0].Length / 4) 'icon_width'    => 32,
 '`t' * ($matches[0].Length / 4) 'icon_height'   => 32,
 '`t' * ($matches[0].Length / 4) 'icon_alt'      => 'LOL',
 '`t' * ($matches[0].Length / 4) 'status'        => '1',
 '`t' * ($matches[0].Length / 4) 'active'        => '0',
 '`t' * ($matches[0].Length / 4)],
 '`t' * ($matches[0].Length / 4)['`t' * ($matches[0].Length / 4) 'id'            => 11,
 '`t' * ($matches[0].Length / 4) 'icon_id'       => 11,
 '`t' * ($matches[0].Length / 4) 'icon_url'      => 'ext/sebo/postreact/styles/all/img/skype/party.png',
 '`t' * ($matches[0].Length / 4) 'icon_width'    => 32,
 '`t' * ($matches[0].Length / 4) 'icon_height'   => 32,
 '`t' * ($matches[0].Length / 4) 'icon_alt'      => 'Party',
 '`t' * ($matches[0].Length / 4) 'status'        => '1',
 '`t' * ($matches[0].Length / 4) 'active'        => '0',
 '`t' * ($matches[0].Length / 4)],
 '`t' * ($matches[0].Length / 4)['`t' * ($matches[0].Length / 4) 'id'            => 12,
 '`t' * ($matches[0].Length / 4) 'icon_id'       => 12,
 '`t' * ($matches[0].Length / 4) 'icon_url'      => 'ext/sebo/postreact/styles/all/img/skype/mechanic.png',
 '`t' * ($matches[0].Length / 4) 'icon_width'    => 32,
 '`t' * ($matches[0].Length / 4) 'icon_height'   => 32,
 '`t' * ($matches[0].Length / 4) 'icon_alt'      => 'Mechanic',
 '`t' * ($matches[0].Length / 4) 'status'        => '1',
 '`t' * ($matches[0].Length / 4) 'active'        => '0',
 '`t' * ($matches[0].Length / 4)],
 '`t' * ($matches[0].Length / 4)['`t' * ($matches[0].Length / 4) 'id'            => 13,
 '`t' * ($matches[0].Length / 4) 'icon_id'       => 13,
 '`t' * ($matches[0].Length / 4) 'icon_url'      => 'ext/sebo/postreact/styles/all/img/skype/cry.png',
 '`t' * ($matches[0].Length / 4) 'icon_width'    => 32,
 '`t' * ($matches[0].Length / 4) 'icon_height'   => 32,
 '`t' * ($matches[0].Length / 4) 'icon_alt'      => 'Cry',
 '`t' * ($matches[0].Length / 4) 'status'        => '1',
 '`t' * ($matches[0].Length / 4) 'active'        => '0',
 '`t' * ($matches[0].Length / 4)],
 '`t' * ($matches[0].Length / 4)['`t' * ($matches[0].Length / 4) 'id'            => 14,
 '`t' * ($matches[0].Length / 4) 'icon_id'       => 14,
 '`t' * ($matches[0].Length / 4) 'icon_url'      => 'ext/sebo/postreact/styles/all/img/skype/censored.png',
 '`t' * ($matches[0].Length / 4) 'icon_width'    => 32,
 '`t' * ($matches[0].Length / 4) 'icon_height'   => 32,
 '`t' * ($matches[0].Length / 4) 'icon_alt'      => 'Censored',
 '`t' * ($matches[0].Length / 4) 'status'        => '1',
 '`t' * ($matches[0].Length / 4) 'active'        => '0',
 '`t' * ($matches[0].Length / 4)],
 '`t' * ($matches[0].Length / 4)['`t' * ($matches[0].Length / 4) 'id'            => 15,
 '`t' * ($matches[0].Length / 4) 'icon_id'       => 15,
 '`t' * ($matches[0].Length / 4) 'icon_url'      => 'ext/sebo/postreact/styles/all/img/telegram/waving.webp',
 '`t' * ($matches[0].Length / 4) 'icon_width'    => 32,
 '`t' * ($matches[0].Length / 4) 'icon_height'   => 32,
 '`t' * ($matches[0].Length / 4) 'icon_alt'      => 'Hello!',
 '`t' * ($matches[0].Length / 4) 'status'        => '1',
 '`t' * ($matches[0].Length / 4) 'active'        => '0',
 '`t' * ($matches[0].Length / 4)],
 '`t' * ($matches[0].Length / 4) ];

 '`t' * ($matches[0].Length / 4) $this->db->sql_multi_insert($this->table_prefix . 'sebo_postreact_icon', $data);
 '`t' * ($matches[0].Length / 4) }

 '`t' * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * A custom function for making more complex database changes
 "`t" * ($matches[0].Length / 4)  * during extension un-installation. Must be declared as public.
 "`t" * ($matches[0].Length / 4)  */
 '`t' * ($matches[0].Length / 4) public function table_pr_uninstall()
 '`t' * ($matches[0].Length / 4)
 {
 '`t' * ($matches[0].Length / 4) // Run some SQL queries on the database
 '`t' * ($matches[0].Length / 4) }
}
