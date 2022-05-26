<?php

namespace WPDesk\ShopMagic\Event\Builtin;

use WPDesk\ShopMagic\Event\OptCommonEvent;

final class CustomerOptedOut extends OptCommonEvent {
	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Customer Opted Out', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return __( 'Triggered when the customer unsubscribes from a list.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @return string
	 */
	protected static function get_internal_action_name() {
		return 'shopmagic/core/event/manual/optout';
	}

}
