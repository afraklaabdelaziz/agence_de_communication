<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;

final class OrderShippingCompany extends WooCommerceOrderBasedPlaceholder {

	public function get_slug(): string {
		return parent::get_slug() . '.shipping_company';
	}

	public function get_description(): string {
		return esc_html__( 'Display the shipping company of current order.', 'shopmagic-for-woocommerce' );
	}

	public function value( array $parameters ): string {
		if ( $this->is_order_provided() ) {
			return $this->get_order()->get_shipping_company();
		}

		return '';
	}
}
