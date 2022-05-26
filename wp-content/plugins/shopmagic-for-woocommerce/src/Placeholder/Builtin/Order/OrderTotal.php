<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;


final class OrderTotal extends WooCommerceOrderBasedPlaceholder {


	public function get_slug() {
		return parent::get_slug() . '.total';
	}

	public function get_description(): string {
		return esc_html__( 'Display the total value of current order. Including currency format.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function value( array $parameters ) {
		$total = $this->get_order()->get_total();
		$price = number_format(
			abs( $total ),
			wc_get_price_decimals(),
			wc_get_price_decimal_separator(),
			wc_get_price_thousand_separator()
		);

		return ( $total < 0 ? '-' : '' ) . html_entity_decode( sprintf( get_woocommerce_price_format(), '' . get_woocommerce_currency_symbol() . '', $price ), ENT_COMPAT, 'UTF-8' );
	}
}
