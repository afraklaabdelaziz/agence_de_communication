<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Product;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceProductBasedPlaceholder;

final class ProductName extends WooCommerceProductBasedPlaceholder {

	public function get_slug() {
		return parent::get_slug() . '.name';
	}

	public function get_description(): string {
		return esc_html__( 'Display name of current product.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function value( array $parameters ) {
		return $this->get_product()->get_name();
	}
}
