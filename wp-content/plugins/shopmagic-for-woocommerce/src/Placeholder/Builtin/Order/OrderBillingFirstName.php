<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;


final class OrderBillingFirstName extends WooCommerceOrderBasedPlaceholder {


	public function get_slug() {
		return parent::get_slug() . '.billing_first_name';
	}

	public function get_description(): string {
		return esc_html__( 'Display the billing first name of current order.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function value( array $parameters ) {
		return $this->get_order()->get_billing_first_name();
	}
}
