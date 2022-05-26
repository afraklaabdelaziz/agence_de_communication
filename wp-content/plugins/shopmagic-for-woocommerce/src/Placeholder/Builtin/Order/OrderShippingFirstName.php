<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;


final class OrderShippingFirstName extends WooCommerceOrderBasedPlaceholder {


	public function get_slug() {
		return parent::get_slug() . '.shipping_first_name';
	}

	public function get_description(): string {
		return esc_html__( 'Display the shipping first name of current order.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function value( array $parameters ) {
		return $this->get_order()->get_shipping_first_name();
	}
}
