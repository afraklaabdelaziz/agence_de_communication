<?php


namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;

final class OrderPaymentMethod extends WooCommerceOrderBasedPlaceholder {

	public function get_slug(): string {
		return parent::get_slug() . '.payment_method';
	}

	public function get_description(): string {
		return esc_html__( 'Display the payment method of current order.', 'shopmagic-for-woocommerce' );
	}

	public function value( array $parameters ): string {
		if ( $this->is_order_provided() ) {
			$order = $this->get_order();
			if ( $order instanceof \WC_Order ) {
				return $order->get_payment_method_title();
			}
		}
		return '';
	}
}
