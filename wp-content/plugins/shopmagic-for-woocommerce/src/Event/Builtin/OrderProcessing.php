<?php

namespace WPDesk\ShopMagic\Event\Builtin;

use WPDesk\ShopMagic\Event\OrderCommonEvent;

final class OrderProcessing extends OrderCommonEvent {
	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Order Processing', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return __( 'Triggered when order status is processing', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function initialize() {
		add_action( 'woocommerce_order_status_processing', [ $this, 'process_event' ], 10, 2 );
	}

}
