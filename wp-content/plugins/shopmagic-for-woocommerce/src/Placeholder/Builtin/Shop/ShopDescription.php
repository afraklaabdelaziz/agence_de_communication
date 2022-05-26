<?php


namespace WPDesk\ShopMagic\Placeholder\Builtin\Shop;

use WPDesk\ShopMagic\Placeholder\BasicPlaceholder;

final class ShopDescription extends BasicPlaceholder {

	public function get_slug(): string {
		return parent::get_slug() . '.tagline';
	}

	public function get_description(): string {
		return esc_html__( 'Display tagline of your website. Can be configured in Settings -> General -> Tagline.', 'shopmagic-for-woocommerce' );
	}

	public function get_required_data_domains(): array {
		return [];
	}

	public function value( array $parameters ): string {
		return get_bloginfo( 'description' );
	}
}
