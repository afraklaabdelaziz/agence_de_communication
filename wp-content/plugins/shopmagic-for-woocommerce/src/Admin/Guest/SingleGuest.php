<?php

namespace WPDesk\ShopMagic\Admin\Guest;

use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use ShopMagicVendor\WPDesk\View\Resolver\DirResolver;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Guest\Guest;
use WPDesk\ShopMagic\Guest\GuestDAO;

/**
 * Single guest view.
 */
final class SingleGuest {
	const SLUG = 'shopmagic_guest';

	public function hooks() {
		add_action(
			'admin_menu',
			function () {
				add_submenu_page(
					null,
					__( 'Guest', 'shopmagic-for-woocommerce' ),
					__( 'Guests', 'shopmagic-for-woocommerce' ),
					'manage_options',
					self::SLUG,
					[ $this, 'render_page_action' ]
				);
			}
		);
	}


	public static function get_url( Guest $guest ): string {
		return self::get_url_by_guest_id( $guest->get_id() );
	}

	private static function get_url_by_guest_id( int $guest_id ): string {
		$params = [
			'page' => self::SLUG,
			'id'   => $guest_id,
		];

		return AutomationPostType::get_url() . '&' . http_build_query( $params );
	}

	public static function get_url_by_customer_id( string $customer_id ): string {
		$id = CustomerFactory::convert_customer_guest_id_to_number( $customer_id );

		return self::get_url_by_guest_id( $id );
	}

	/**
	 * @internal
	 */
	public function render_page_action() {
		global $wpdb;
		$guest    = ( new GuestDAO( $wpdb ) )->get_by_id( (int) $_GET['id'] );
		$renderer = ( new SimplePhpRenderer( new DirResolver( __DIR__ . DIRECTORY_SEPARATOR . 'templates' ) ) );
		echo $renderer->render( 'single-guest', [ 'guest' => $guest ] );
	}
}
