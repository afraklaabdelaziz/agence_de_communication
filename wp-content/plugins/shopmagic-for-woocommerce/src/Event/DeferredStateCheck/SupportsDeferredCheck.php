<?php

namespace WPDesk\ShopMagic\Event\DeferredStateCheck;

/**
 * Event capability to check if the deferred event still qualifies for running an action.
 *
 * @package WPDesk\ShopMagic\Event
 */
interface SupportsDeferredCheck {
	/**
	 * Return true if event state still qualifies for running an action.
	 *
	 * @return bool
	 */
	public function is_event_still_valid();
}
