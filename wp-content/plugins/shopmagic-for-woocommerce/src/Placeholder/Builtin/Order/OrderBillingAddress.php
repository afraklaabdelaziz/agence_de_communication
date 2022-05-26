<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;


final class OrderBillingAddress extends WooCommerceOrderBasedPlaceholder {
	public function get_slug() {
		return parent::get_slug() . '.billing_address';
	}

	public function get_description(): string {
		return esc_html__( 'Display the billing address of current order.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function value( array $parameters ) {
		return $this->get_order()->get_billing_address_1();
	}
}

