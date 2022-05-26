<?php

namespace WPDesk\ShopMagic\Event;

use WPDesk\ShopMagic\Automation\Runner;

/**
 * When event knows it fires it should check if there are no filters that blocks it.
 * If no filter blocked the Event then it notifies the Automation object about it.
 * Automation decides what to do with it next.
 *
 * @see EventFactory2::create_event() - prototype pattern
 *
 * @package WPDesk\ShopMagic\Event
 */
interface Event2 extends Event {

	public function __clone();

	/** @return void */
	public function set_runner( Runner $runner );

}
