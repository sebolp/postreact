<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\migrations;

class purge_legacy_notifications_2_6_3 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\sebo\postreact\migrations\widen_emoji_column_2_6_2'];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'purge_legacy_notifications']]],
		];
	}

	/**
	 * Notification item ids changed meaning in 2.6.3 (even/odd id spaces),
	 * so notifications created by earlier versions can no longer be matched
	 * and are removed.
	 */
	public function purge_legacy_notifications()
	{
		$sql = 'DELETE FROM ' . NOTIFICATIONS_TABLE . '
			WHERE notification_type_id IN (
				SELECT notification_type_id
				FROM ' . NOTIFICATION_TYPES_TABLE . "
				WHERE notification_type_name = 'sebo.postreact.notification.type.postreact_notification'
			)";
		$this->db->sql_query($sql);
	}
}