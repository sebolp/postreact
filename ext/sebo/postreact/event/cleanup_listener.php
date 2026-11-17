<?php

/**
 *
 * PostReaction. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026, sebo, https://www.fiatpandaclub.org
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace sebo\postreact\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use sebo\postreact\service\notification_helper;

/**
 * Removes PostReaction notifications when posts are deleted.
 */
class cleanup_listener implements EventSubscriberInterface
{
	/** @var notification_helper */
	protected $notification_helper;

	public static function getSubscribedEvents()
	{
		return [
			'core.delete_posts_after' => 'delete_post_notifications',
		];
	}

	public function __construct(notification_helper $notification_helper)
	{
		$this->notification_helper = $notification_helper;
	}

	/**
	 * @param \phpbb\event\data $event
	 */
	public function delete_post_notifications($event)
	{
		$post_ids = isset($event['post_ids']) ? (array) $event['post_ids'] : [];
		$this->notification_helper->delete_for_posts($post_ids);
	}
}