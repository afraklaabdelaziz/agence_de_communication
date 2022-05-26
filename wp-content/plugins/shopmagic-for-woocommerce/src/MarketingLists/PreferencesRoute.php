<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\MarketingLists;

use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Database\DatabaseSchema;
use WPDesk\ShopMagic\MarketingLists\View\AccountPreferences;

final class PreferencesRoute {

	/** @var AccountPreferences */
	private $view;

	/** @return void */
	public function hooks() {
		add_action( 'template_redirect', [ $this, 'show_communication_page' ] );
		add_action( 'init', [ $this, 'add_communication_route' ] );
		add_filter( 'query_vars', [ $this, 'add_query_vars' ], 0 );
	}

	/** @return void */
	public function set_view( AccountPreferences $view ) {
		$this->view = $view;
	}

	/** @return void */
	public function add_communication_route() {
		$routes = EP_ROOT;
		if ( $this->should_append_communication_page_to_account() ) {
			$routes |= EP_PAGES;
		}
		add_rewrite_endpoint( self::get_slug(), $routes );

		if ( get_option( DatabaseSchema::FLUSH_REQUIRED ) === 1 ) {
			flush_rewrite_rules();
			update_option( DatabaseSchema::FLUSH_REQUIRED, 0 );
		}
	}

	private function should_append_communication_page_to_account(): bool {
		return apply_filters( 'shopmagic/core/communication_type/account_page_show', true );
	}

	/**
	 * @param string[] $vars
	 *
	 * @return string[]
	 *
	 * @internal
	 */
	public function add_query_vars( array $vars ): array {
		$vars[] = self::get_slug();

		return $vars;
	}

	/** @return void */
	public function show_communication_page() {
		global $wp_query;

		if ( ! array_key_exists( self::get_slug(), $wp_query->query_vars ) ) {
			return;
		}

		if ( array_key_exists( self::get_slug(), $wp_query->query_vars ) && ( $wp_query->queried_object_id === wc_get_page_id( 'myaccount' ) ) ) {
			return;
		}

		if ( is_user_logged_in() && array_key_exists( self::get_slug(), $wp_query->query_vars ) && $this->should_append_communication_page_to_account() ) {
			wp_safe_redirect( wc_get_account_endpoint_url( self::get_slug() ) );
			die;
		}

		$this->view->display_communication_page();
		die;
	}

	public static function get_slug(): string {
		return sanitize_title_with_dashes( apply_filters( 'shopmagic/core/communication_type/account_page_slug', 'communication-preferences' ) );
	}

	public function create_preferences_url( Customer $customer ): string {
		if ( ! $customer->is_guest() && $this->should_append_communication_page_to_account() ) {
			return wc_get_account_endpoint_url( self::get_slug() );
		}

		$link = trailingslashit( home_url() ) . self::get_slug();

		return $link . '?' . http_build_query(
			[
				'hash' => md5( $customer->get_email() . SECURE_AUTH_SALT ),
				'id'   => $customer->get_guest_id(),
			]
		);
	}

	public static function validate_hash( string $email, string $hash ): bool {
		return md5( $email . SECURE_AUTH_SALT ) === $hash;
	}

}
