<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Product;

use WPDesk\ShopMagic\FormField\Field\InputTextField;
use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceProductBasedPlaceholder;

final class ProductMeta extends WooCommerceProductBasedPlaceholder {
	const PARAM_KEY_NAME = 'key';

	public function get_slug() {
		return parent::get_slug() . '.meta';
	}

	public function get_supported_parameters() {
		return [
			( new InputTextField() )
				->set_required()
				->set_name( self::PARAM_KEY_NAME )
				->set_label( __( 'The meta key to retrieve', 'shopmagic-for-woocommerce' ) ),
		];
	}

	public function get_description(): string {
		return esc_html__( 'Display meta value from product meta.', 'shopmagic-for-woocommerce' ) . ' ' .
			esc_html__( 'You can find more about using this placeholder in ' ) .
			'<a target="_blank" href="https://docs.shopmagic.app/article/1163-meta-placeholders">' . esc_html__( 'documentation', 'shopmagic-for-woocommerce' ) . '</a>.';
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function value( array $parameters ) {
		$key = $parameters[ self::PARAM_KEY_NAME ];

		if ( ! $key ) {
			return '';
		}

		$product    = $this->get_product();
		$product_id = $product->get_id();
		$value      = get_post_meta( $product_id, $key, true );

		if ( empty( $value ) && $product->is_type( 'variation' ) ) {
			$parent_id = $product->get_parent_id();
			$parent    = wc_get_product( $parent_id );
			$value     = $parent ? get_post_meta( $parent_id, $key, true ) : '';
		}

		return (string) $value;
	}
}
