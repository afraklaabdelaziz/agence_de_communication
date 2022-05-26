<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Customer;

use WPDesk\ShopMagic\Placeholder\Builtin\UserBasedPlaceholder;

final class CustomerFirstName extends UserBasedPlaceholder {

	public function get_slug() {
		return parent::get_slug() . '.first_name';
	}

	public function get_description(): string {
		return esc_html__( 'Display first name of current Customer.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function value( array $parameters ) {
		return $this->get_customer()->get_first_name();
	}
}
