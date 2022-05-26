<?php

namespace WPDesk\ShopMagic\Event\Builtin;

use WPDesk\ShopMagic\Event\DeferredStateCheck\DefferedCheckField;
use WPDesk\ShopMagic\Event\OrderCommonEvent;
use WPDesk\ShopMagic\Event\DeferredStateCheck\SupportsDeferredCheck;

final class OrderPaid extends OrderCommonEvent implements SupportsDeferredCheck {
	public function get_name(): string {
		return __( 'Order Paid', 'shopmagic-for-woocommerce' );
	}

	public function get_description(): string {
		return __( 'Triggered when order status is changed.', 'shopmagic-for-woocommerce' );
	}

	public function initialize() {
		add_action( 'woocommerce_order_status_changed', [ $this, 'status_changed' ], 10, 4 );
	}

	public function get_fields(): array {
		return [ new DefferedCheckField() ];
	}

	/**
	 * @internal
	 */
	public function status_changed( $order_id, $old_status, $new_status, $order ) {
		$this->order = $order;

		if ( in_array( $old_status, wc_get_is_paid_statuses(), true ) ) {
			return;
		}

		if ( ! in_array( $new_status, wc_get_is_paid_statuses(), true ) ) {
			return;
		}

		$this->run_actions();
	}

	public function is_event_still_valid(): bool {
		if ( in_array( $this->get_order()->get_status(), wc_get_is_paid_statuses(), true ) ) {
			return true;
		}

		return false;
	}
}
