<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\Traits\CountryFormatHelper;
use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;

final class OrderBillingCountry extends WooCommerceOrderBasedPlaceholder {
	use CountryFormatHelper;

	public function get_slug() {
		return parent::get_slug() . '.billing_country';
	}

	public function get_description(): string {
		return esc_html__( 'Display the billing country of current order.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function value( array $parameters ) {
		return $this->country_full_name( $this->get_order()->get_billing_country() );
	}
}
