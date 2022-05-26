<?php

namespace WPDesk\ShopMagic\Event;

use WPDesk\ShopMagic\DataSharing\DataReceiver;

/**
 * @deprecated Do not use. Only for old version of Manual Actions.
 */
interface ManualEvent extends DataReceiver, Event {
	/**
	 * Fires an event.
	 *
	 * @return void
	 */
	public function trigger();

	/**
	 * @return bool
	 */
	public function is_filter_passed();
}
