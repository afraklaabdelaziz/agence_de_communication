<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Customer;

use WPDesk\ShopMagic\Placeholder\Builtin\UserBasedPlaceholder;

final class CustomerPhone extends UserBasedPlaceholder {
	public function get_slug() {
		return parent::get_slug() . '.phone';
	}

	public function get_description(): string {
		return esc_html__( 'Display phone number of current Customer.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function value( array $parameters ) {
		return $this->get_customer()->get_phone();
	}
}
