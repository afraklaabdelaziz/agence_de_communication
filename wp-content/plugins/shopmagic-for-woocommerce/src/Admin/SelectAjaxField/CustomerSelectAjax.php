<?php

namespace WPDesk\ShopMagic\Admin\SelectAjaxField;

use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Customer\CustomerDAO;
use WPDesk\ShopMagic\FormField\BasicField;

/**
 * @TODO: remove static
 *
 * @package WPDesk\ShopMagic\Admin\SelectAjaxField
 */
class CustomerSelectAjax extends BasicField {

	public function get_template_name(): string {
		return 'customer-select';
	}

	public static function get_ajax_action_name(): string {
		return 'shopmagic_customer';
	}

	/**
	 * @return void
	 * @throws \Exception
	 * @internal
	 */
	public static function customer_select_ajax() {
		global $wpdb;
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['term'] ) || ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}
		$term = sanitize_text_field( wp_unslash( $_GET['term'] ) );
		// phpcs:enable

		if ( empty( $term ) ) {
			wp_die();
		}

		$customer_repository = new CustomerDAO( $wpdb );
		$customers           = $customer_repository->search( $term );

		$results = [];
		foreach ( $customers as $customer ) {
			$results[ $customer->get_id() ] = self::convert_value_to_option_text( $customer );
		}
		wp_send_json( $results );
	}

	/**
	 * @param int|\WP_User|Customer $value
	 *
	 * @return Customer
	 * @throws \Exception
	 */
	private static function create_customer( $value ): Customer {
		$customer_factory = new CustomerFactory();
		if ( $value instanceof \WP_User ) {
			return $customer_factory->create_from_user( $value );
		}
		if ( $value instanceof Customer ) {
			return $value;
		}

		return $customer_factory->create_from_id( $value );
	}

	/**
	 * @param int|\WP_User|Customer $value
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function convert_value_to_option_text( $value ): string {
		$customer = self::create_customer( $value );

		if ( $customer->get_first_name() !== '' || $customer->get_last_name() !== '' ) {
				return "{$customer->get_first_name()} {$customer->get_last_name()}" . ' &ndash; ' . $customer->get_email();
		}

		return $customer->get_email();
	}

	/**
	 * @return void
	 */
	public static function hooks() {
		add_action( 'wp_ajax_' . self::get_ajax_action_name(), '\WPDesk\ShopMagic\Admin\SelectAjaxField\CustomerSelectAjax::customer_select_ajax' );
	}
}
