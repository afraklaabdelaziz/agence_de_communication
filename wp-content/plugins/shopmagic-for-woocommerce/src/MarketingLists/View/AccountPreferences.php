<?php

namespace WPDesk\ShopMagic\MarketingLists\View;

use Exception;
use ShopMagicVendor\WPDesk\View\Renderer\Renderer;
use ShopMagicVendor\WPDesk\View\Renderer\SimplePhpRenderer;
use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Customer\CustomerDAO;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Exception\CustomerNotFound;
use WPDesk\ShopMagic\Frontend\FrontRenderer;
use WPDesk\ShopMagic\Helper\TemplateResolver;
use WPDesk\ShopMagic\MarketingLists\DAO\ListFactory;
use WPDesk\ShopMagic\MarketingLists\DAO\ListTable;
use WPDesk\ShopMagic\MarketingLists\PreferencesRoute;

/**
 * Communication type info for customer. Optins/optouts.
 */
final class AccountPreferences {
	const ACCOUNT_SHORTCODE = 'shopmagic_communication_preferences';

	/** @var ListTable */
	private $list_table;

	/** @var Renderer */
	private $renderer;

	public function __construct( ListTable $list_table, Renderer $renderer ) {
		$this->list_table = $list_table;
		$this->renderer   = $renderer;
	}

	/** @return void */
	public function hooks() {
		/** @deprecated 2.37 Communication preferences can no longer be used as shortcode. */
		add_shortcode( self::ACCOUNT_SHORTCODE, [ $this, 'communication_preferences_shortcode' ] );
		if ( $this->should_append_page_to_account() ) {
			add_filter( 'woocommerce_account_menu_items', [ $this, 'new_menu_items' ] );
			add_action( 'woocommerce_account_' . PreferencesRoute::get_slug() . '_endpoint', [ $this, 'nav_menu_content' ] );
		}
	}

	/**
	 * Insert the new endpoint into the My Account menu.
	 *
	 * @param string[] $items
	 *
	 * @return string[]
	 *
	 * @internal
	 */
	public function new_menu_items( array $items ): array {
		$logout_item = false;

		if ( isset( $items['customer-logout'] ) ) {
			$logout_item = $items['customer-logout'];
			unset( $items['customer-logout'] );
		}

		$items[ PreferencesRoute::get_slug() ] = $this->get_title();

		if ( $logout_item ) {
			$items['customer-logout'] = $logout_item;
		}

		return $items;
	}

	private function get_title(): string {
		return apply_filters( 'shopmagic/core/communication_type/account_page_title', __( 'Communication', 'shopmagic-for-woocommerce' ) );
	}

	/**
	 * @return void
	 * @internal WooCommerce communication preferences callback.
	 */
	public function nav_menu_content() {
		$this->maybe_display_note();
		$this->display_preferences_form( $this->get_customer() );
	}

	/** @internal This is a shortcode. Do not use outside the class. */
	public function communication_preferences_shortcode(): string {
		return '';
	}

	private function get_customer(): Customer {
		try {
			return ( new CustomerFactory() )->create_from_id( $this->determine_customer_id() );
		} catch ( Exception $e ) {
			$this->display_error();
			die;
		}
	}

	/** @return void */
	public function display_communication_page() {
		$email = isset( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : '';
		$hash  = isset( $_GET['hash'] ) ? sanitize_text_field( wp_unslash( $_GET['hash'] ) ) : '';

		if ( empty( $hash ) && isset( $_GET['success'] ) ) {
			$this->renderer->output_render( 'communication_preferences_wrap_start' );
			$this->maybe_display_note();
			$this->renderer->output_render( 'communication_preferences_wrap_end' );
			die;
		}

		if ( $email !== '' ) { // backward compatibility.
			try {
				$customer = ( new CustomerDAO() )->find_by_email( $email );
			} catch ( CustomerNotFound $e ) {
				$this->display_error();
				die;
			}
		} else {
			$customer = $this->get_customer();
		}

		if ( ! PreferencesRoute::validate_hash( $customer->get_email(), $hash ) ) {
			$this->display_error();
			die;
		}

		if ( $customer->is_guest() || ! $this->should_append_page_to_account() ) {
			$this->renderer->output_render( 'communication_preferences_wrap_start' );
		}

		$this->maybe_display_note();

		$this->display_preferences_form( $customer );

		if ( $customer->is_guest() || ! $this->should_append_page_to_account() ) {
			$this->renderer->output_render( 'communication_preferences_wrap_end' );
		}
	}

	/** @return string ID must be returned as string for compatibility with guest ID. */
	private function determine_customer_id(): string {
		if ( is_user_logged_in() ) {
			return (string) get_current_user_id();
		}
		return isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
	}

	private function should_append_page_to_account(): bool {
		return (bool) apply_filters( 'shopmagic/core/communication_type/account_page_show', true );
	}

	private function get_customer_email_for_display( Customer $customer ): string {
		if ( $customer->is_guest() || ! $this->should_append_page_to_account() ) {
			return $this->obfuscate_email( $customer->get_email() );
		}

		return $customer->get_email();
	}

	private function obfuscate_email( string $email ): string {
		list($local, $domain) = explode( '@', $email );

		$obfuscate = static function ( string $string ): string {
			$len = strlen( $string );
			return implode(
				array_map(
					static function ( int $key, string $value ) use ( $len ) {
						return ( $key === 0 || $key === $len - 1 ) ? $value : '*';
					},
					array_keys( str_split( $string ) ),
					array_values( str_split( $string ) )
				)
			);
		};

		return $obfuscate( $local ) . '@' . $domain;
	}

	/** @return void */
	private function display_error() {
		wp_die(
			esc_html__( 'Sorry, but something is wrong with your request.', 'shopmagic-for-woocommerce' ),
			esc_html__( 'Account preferences', 'shopmagic-for-woocommerce' ),
			400
		);
	}

	/** @return void */
	private function display_preferences_form( Customer $customer ) {
		$this->renderer->output_render(
			'communication_preferences',
			[
				'email'         => $customer->get_email(),
				'email_display' => $this->get_customer_email_for_display( $customer ),
				'action'        => PreferencesRoute::get_slug(),
				'signed_ups'    => $this->list_table->get_all(
					[
						'email'  => $customer->get_email(),
						'active' => 1,
					]
				),
			]
		);
	}

	private function maybe_display_note() {
		if ( isset( $_GET['success'] ) ) {
			if ( (int) $_GET['success'] === 1 ) {
				$args = [
					'message' => esc_html__( 'You have successfully updated your preferences.', 'shopmagic-for-woocommerce' ),
					'success' => 1,
				];
			} else {
				$args = [
					'message' => esc_html__( 'An error occurred during saving your preferences.', 'shopmagic-for-woocommerce' ),
					'success' => 0,
				];
			}
			$this->renderer->output_render( 'preferences_success', $args );
		}
	}

}
