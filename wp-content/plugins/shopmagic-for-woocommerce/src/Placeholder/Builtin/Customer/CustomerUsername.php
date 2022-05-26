<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Customer;

use WPDesk\ShopMagic\Placeholder\Builtin\UserBasedPlaceholder;

final class CustomerUsername extends UserBasedPlaceholder {

	public function get_slug() {
		return parent::get_slug() . '.username';
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return __( 'Displays the customer\'s username. It will be blank for guests.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function value( array $parameters ) {
		if ( $this->get_customer()->is_guest() ) {
			return '';
		}

		return $this->get_customer()->get_username();
	}
}
