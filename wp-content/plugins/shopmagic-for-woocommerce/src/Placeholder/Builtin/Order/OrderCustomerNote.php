<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;

final class OrderCustomerNote extends WooCommerceOrderBasedPlaceholder {

	public function get_slug() {
		return parent::get_slug() . '.customer_note';
	}

	public function get_description(): string {
		return esc_html__( 'Display the note added to customer to current order.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function value( array $parameters ) {
		$order = $this->get_order();

		if ( ! $order instanceof \WC_Order ) {
			return '';
		}

		return $order->get_customer_note();
	}
}
