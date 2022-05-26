<?php


namespace WPDesk\ShopMagic\Placeholder\Builtin\Shop;

use WPDesk\ShopMagic\Placeholder\BasicPlaceholder;
use WPDesk\ShopMagic\Placeholder\Helper\PlaceholderUTMBuilder;

final class ShopUrl extends BasicPlaceholder {

	/** @var PlaceholderUTMBuilder */
	private $utm_builder;

	public function __construct() {
		$this->utm_builder = new PlaceholderUTMBuilder();
	}

	public function get_slug(): string {
		return parent::get_slug() . '.url';
	}

	public function get_description(): string {
		return esc_html__( 'Display url of your website.', 'shopmagic-for-woocommerce' ) . '<br>' .
				$this->utm_builder->get_description();
	}

	public function get_supported_parameters(): array {
		return $this->utm_builder->get_utm_fields();
	}

	public function get_required_data_domains(): array {
		return [];
	}

	public function value( array $parameters ): string {
		return $this->utm_builder->append_utm_parameters_to_uri( $parameters, get_bloginfo( 'url' ) );
	}
}
