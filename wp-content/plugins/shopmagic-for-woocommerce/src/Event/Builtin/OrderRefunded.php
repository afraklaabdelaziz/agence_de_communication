<?php

namespace WPDesk\ShopMagic\Event\Builtin;

use WPDesk\ShopMagic\Event\OrderCommonEvent;

final class OrderRefunded extends OrderCommonEvent {
	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Order Refunded', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return __( 'Triggered when the order has been refunded', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function initialize() {
		add_action( 'woocommerce_order_status_refunded', [ $this, 'process_event' ], 10, 2 );
	}

}
