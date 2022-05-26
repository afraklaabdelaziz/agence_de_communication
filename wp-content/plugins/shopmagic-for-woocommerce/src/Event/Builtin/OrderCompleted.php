<?php

namespace WPDesk\ShopMagic\Event\Builtin;

use WPDesk\ShopMagic\Event\OrderCommonEvent;

final class OrderCompleted extends OrderCommonEvent {
	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Order Completed', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return __( 'Triggered when order status is set to completed', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function initialize() {
		add_action( 'woocommerce_order_status_completed', [ $this, 'process_event' ], 10, 2 );
	}

}
