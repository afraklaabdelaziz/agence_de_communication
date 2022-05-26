<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Product;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceProductBasedPlaceholder;

final class ProductId extends WooCommerceProductBasedPlaceholder {

	public function get_slug() {
		return parent::get_slug() . '.id';
	}

	public function get_description(): string {
		return esc_html__( 'Display ID of current product.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @param array $parameters
	 *
	 * @return int|string
	 */
	public function value( array $parameters ) {
		return $this->get_product()->get_id();
	}
}
