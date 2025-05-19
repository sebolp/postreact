<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact;

/**
 * PostReaction Extension base
 *
 * It is recommended to remove this file from
 * an extension if it is not going to be used.
 */
class ext extends \phpbb\extension\base
{
 "`t" * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Enable notifications for the extension
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @param   mixed   $old_state  The return value of the previous call
 "`t" * ($matches[0].Length / 4)  *                              of this method, or false on the first call
 "`t" * ($matches[0].Length / 4)  * @return  mixed               Returns false after last step, otherwise
 "`t" * ($matches[0].Length / 4)  *                              temporary state which is passed as an
 "`t" * ($matches[0].Length / 4)  *                              argument to the next step
 "`t" * ($matches[0].Length / 4)  */
 "`t" * ($matches[0].Length / 4) public function enable_step($old_state)
 "`t" * ($matches[0].Length / 4) {
 "`t" * ($matches[0].Length / 4) if ($old_state === false) {
/** @var \phpbb\notification\manager $notification_manager */
 "`t" * ($matches[0].Length / 4) $notification_manager = $this->container->get('notification_manager');
 "`t" * ($matches[0].Length / 4) $notification_manager->enable_notifications('sebo.postreact.notification.type.postreact_notification');
 "`t" * ($matches[0].Length / 4) return 'notification';
 "`t" * ($matches[0].Length / 4) }

 "`t" * ($matches[0].Length / 4) return parent::enable_step($old_state);
 "`t" * ($matches[0].Length / 4) }

 "`t" * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Disable notifications for the extension
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @param   mixed   $old_state  The return value of the previous call
 "`t" * ($matches[0].Length / 4)  *                              of this method, or false on the first call
 "`t" * ($matches[0].Length / 4)  * @return  mixed               Returns false after last step, otherwise
 "`t" * ($matches[0].Length / 4)  *                              temporary state which is passed as an
 "`t" * ($matches[0].Length / 4)  *                              argument to the next step
 "`t" * ($matches[0].Length / 4)  */
 "`t" * ($matches[0].Length / 4) public function disable_step($old_state)
 "`t" * ($matches[0].Length / 4) {
 "`t" * ($matches[0].Length / 4) if ($old_state === false) {
/** @var \phpbb\notification\manager $notification_manager */
 "`t" * ($matches[0].Length / 4) $notification_manager = $this->container->get('notification_manager');
 "`t" * ($matches[0].Length / 4) $notification_manager->disable_notifications('sebo.postreact.notification.type.postreact_notification');
 "`t" * ($matches[0].Length / 4) return 'notification';
 "`t" * ($matches[0].Length / 4) }

 "`t" * ($matches[0].Length / 4) return parent::disable_step($old_state);
 "`t" * ($matches[0].Length / 4) }

 "`t" * ($matches[0].Length / 4) /**
 "`t" * ($matches[0].Length / 4)  * Purge notifications for the extension
 "`t" * ($matches[0].Length / 4)  *
 "`t" * ($matches[0].Length / 4)  * @param   mixed   $old_state  The return value of the previous call
 "`t" * ($matches[0].Length / 4)  *                              of this method, or false on the first call
 "`t" * ($matches[0].Length / 4)  * @return  mixed               Returns false after last step, otherwise
 "`t" * ($matches[0].Length / 4)  *                              temporary state which is passed as an
 "`t" * ($matches[0].Length / 4)  *                              argument to the next step
 "`t" * ($matches[0].Length / 4)  */
 "`t" * ($matches[0].Length / 4) public function purge_step($old_state)
 "`t" * ($matches[0].Length / 4) {
 "`t" * ($matches[0].Length / 4) if ($old_state === false) {
/** @var \phpbb\notification\manager $notification_manager */
 "`t" * ($matches[0].Length / 4) $notification_manager = $this->container->get('notification_manager');
 "`t" * ($matches[0].Length / 4) $notification_manager->purge_notifications('sebo.postreact.notification.type.postreact_notification');
 "`t" * ($matches[0].Length / 4) return 'notification';
 "`t" * ($matches[0].Length / 4) }

 "`t" * ($matches[0].Length / 4) return parent::purge_step($old_state);
 "`t" * ($matches[0].Length / 4) }
}
