<?php

namespace WPDesk\ShopMagic\Admin\Settings;

use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;

final class CartAdSettings extends AdSettings {
	public static function get_tab_slug(): string {
		return 'carts';
	}

	public function get_tab_name(): string {
		return __( 'Abandoned Carts', 'shopmagic-for-woocommerce' );
	}

	public static function ajax_install_action(): string {
		return 'shopmagic_install_carts';
	}

	public static function plugin_slug(): string {
		return 'shopmagic-abandoned-carts/shopmagic-abandoned-carts.php';
	}

	public static function template(): string {
		return 'cart-ad-template';
	}

	public static function render_for_event(): string {
		$renderer = new SimplePhpRenderer( new DirResolver( __DIR__ . '/settings-templates' ) );
		return $renderer->render(
			self::template(),
			[
				'nonce'       => self::nonce(),
				'ajax_action' => self::ajax_install_action(),
			]
		);
	}
}
