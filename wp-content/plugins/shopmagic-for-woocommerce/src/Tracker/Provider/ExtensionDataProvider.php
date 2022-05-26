<?php

namespace WPDesk\ShopMagic\Tracker\Provider;

/**
 * Provides info about events/filter/actions/placeholders from extensions.
 *
 * @package WPDesk\ShopMagic\Tracker\Provider
 */
class ExtensionDataProvider implements \WPDesk_Tracker_Data_Provider {
	/**
	 * @inheritDoc
	 */
	public function get_data() {
		return [
			'shopmagic_extensions' => [
				'events'       => array_keys( apply_filters( 'shopmagic/core/events', [] ) ),
				'actions'      => array_keys( apply_filters( 'shopmagic/core/actions', [] ) ),
				'filters'      => array_keys( apply_filters( 'shopmagic/core/filters', [] ) ),
				'placeholders' => array_keys( apply_filters( 'shopmagic/core/placeholders', [] ) ),
			],
		];
	}
}

