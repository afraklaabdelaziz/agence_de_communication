<?php

namespace WPDesk\ShopMagic\Event;

/**
 * Interface for all events that can be manually triggered at will and all of them should be triggered together.
 *
 * @see ManualEvent2
 *
 * @package WPDesk\ShopMagic\Event
 */
interface ManualGlobalEvent {
	/**
	 * Fires an event.
	 *
	 * @param array $args Trigger arguments ie. [ $order ]
	 *
	 * @return void
	 */
	public static function trigger( array $args);
}
