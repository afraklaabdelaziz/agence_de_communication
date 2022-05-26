<?php

namespace WPDesk\ShopMagic\Event;

use WPDesk\ShopMagic\DataSharing\DataReceiver;
use WPDesk\ShopMagic\DataSharing\RenderableItemProvider;

/**
 * Interface for all events that can be manually triggered.
 * There is a difference between ManualGlobalEvent and this event: this event always fires per instance.
 * When ManualGlobalEvent it fires for all automation that are connected to the event. Here always only one automation
 * is used.
 *
 * @TODO: rename to ManualEvent in 3.0
 *
 * @package WPDesk\ShopMagic\Event
 */
interface ManualEvent2 extends DataReceiver, Event, RenderableItemProvider {
	/**
	 * Fires an event.
	 *
	 * @param object|int $item The item that has been provided by RenderableItemProvider methods or unique int ID of that item.
	 *
	 * @return mixed
	 */
	public function trigger( $item );
}
