<?php


namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;

final class OrderShippingMethod extends WooCommerceOrderBasedPlaceholder {

	public function get_slug(): string {
		return parent::get_slug() . '.shipping_method';
	}

	public function get_description(): string {
		return esc_html__( 'Display the shipping method of current order.', 'shopmagic-for-woocommerce' );
	}

	public function value( array $parameters ): string {
		if ( $this->is_abstract_order_provided() ) {
			$order = $this->get_order();
			if ( $order instanceof \WC_Abstract_Order ) {
				return $order->get_shipping_method();
			}
		}
		return '';
	}
}
