<?php

namespace WPDesk\ShopMagic\DataSharing;

use WPDesk\ShopMagic\Event\EventFactory2;

/**
 * Can create test data provider adequate to event.
 *
 * @package WPDesk\ShopMagic\DataSharing
 */
class TestDataProviderFactory {

	/** @var EventFactory2 */
	private static $event_factory;

	public static function set_event_factory( EventFactory2 $event_factory ) {
		self::$event_factory = $event_factory;
	}

	public static function create_test_data_provider( string $event_slug ): DataProvider {
		return new TestDataProvider();
	}
}
