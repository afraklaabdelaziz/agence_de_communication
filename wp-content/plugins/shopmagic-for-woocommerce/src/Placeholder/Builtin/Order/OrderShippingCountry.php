<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\Traits\CountryFormatHelper;
use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;

final class OrderShippingCountry extends WooCommerceOrderBasedPlaceholder {
	use CountryFormatHelper;

	public function get_slug() {
		return parent::get_slug() . '.shipping_country';
	}

	public function get_description(): string {
		return esc_html__( 'Display the shipping country of current order.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function value( array $parameters ) {
		return $this->country_full_name( $this->get_order()->get_shipping_country() );
	}
}
