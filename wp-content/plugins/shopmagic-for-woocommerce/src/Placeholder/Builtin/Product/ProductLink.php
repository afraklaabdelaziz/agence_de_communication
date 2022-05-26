<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Product;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceProductBasedPlaceholder;
use WPDesk\ShopMagic\Placeholder\Helper\PlaceholderUTMBuilder;

final class ProductLink extends WooCommerceProductBasedPlaceholder {

	/** @var PlaceholderUTMBuilder */
	private $utm_builder;

	public function __construct() {
		$this->utm_builder = new PlaceholderUTMBuilder();
	}

	public function get_slug(): string {
		return parent::get_slug() . '.link';
	}

	public function get_description(): string {
		return esc_html__( 'Display link to current product.', 'shopmagic-for-woocommerce' ) . '<br>' .
				$this->utm_builder->get_description();
	}

	public function get_supported_parameters(): array {
		return $this->utm_builder->get_utm_fields();
	}

	public function value( array $parameters ): string {
		return $this->utm_builder->append_utm_parameters_to_uri( $parameters, $this->get_product()->get_permalink() );
	}
}
