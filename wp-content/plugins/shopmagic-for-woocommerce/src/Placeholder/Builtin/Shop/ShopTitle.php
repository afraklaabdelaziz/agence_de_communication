<?php


namespace WPDesk\ShopMagic\Placeholder\Builtin\Shop;

use WPDesk\ShopMagic\Placeholder\BasicPlaceholder;

final class ShopTitle extends BasicPlaceholder {

	public function get_slug(): string {
		return parent::get_slug() . '.title';
	}

	public function get_description(): string {
		return esc_html__( 'Display title of your website. Can be configured in Settings -> General -> Site Title.', 'shopmagic-for-woocommerce' );
	}

	public function get_required_data_domains(): array {
		return [];
	}

	public function value( array $parameters ): string {
		return get_bloginfo( 'name' );
	}
}
