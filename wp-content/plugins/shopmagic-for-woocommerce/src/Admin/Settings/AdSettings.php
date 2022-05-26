<?php

namespace WPDesk\ShopMagic\Admin\Settings;

use ShopMagicVendor\WPDesk\View\Renderer\Renderer;

/**
 * Only for internal ShopMagic use.
 *
 * @package WPDesk\ShopMagic\Admin\Settings
 */
abstract class AdSettings implements SettingsTab {

	final public function set_data( $data ) {
	}

	final public function handle_request( $request ) {
	}

	final public function get_data(): array {
		return [];
	}

	final protected function get_fields(): array {
		return [];
	}

	public static function nonce(): string {
		return 'shopmagic-installer-nonce';
	}

	abstract public static function ajax_install_action(): string;

	abstract public static function plugin_slug(): string;

	abstract public static function template(): string;

	public function render( Renderer $renderer ): string {
		$output  = '<br>';
		$output .= '<div class="notice notice-info">';
		$output .= $renderer->render(
			static::template(),
			[
				'nonce'       => static::nonce(),
				'ajax_action' => static::ajax_install_action(),
			]
		);
		$output .= '</div>';

		return $output;
	}
}
