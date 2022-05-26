<?php

namespace WPDesk\ShopMagic\Event\Builtin;

use WPDesk\ShopMagic\Event\OrderCommonEvent;

final class OrderCancelled extends OrderCommonEvent {
	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Order Cancelled', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return __( 'Triggered when order status is set to cancelled', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function initialize() {
		add_action( 'woocommerce_order_status_cancelled', [ $this, 'process_event' ], 10, 2 );
	}

}
