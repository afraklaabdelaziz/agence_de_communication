<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;

final class OrderNumber extends WooCommerceOrderBasedPlaceholder {

	public function get_slug(): string {
		return parent::get_slug() . '.order_number';
	}

	public function get_description(): string {
		return esc_html__( 'Display the number of current order. Similar to ID, but useful when you use plugins for sequential order number in WooCoommerce.', 'shopmagic-for-woocommerce' );
	}

	/** @param string[] $parameters */
	public function value( array $parameters ): string {
		if ( $this->is_order_provided() ) {
			$order = $this->get_order();
			if ( $order instanceof \WC_Order ) {
				return $order->get_order_number();
			}
		}

		return '';
	}
}
