<?php
declare( strict_types=1 );

namespace WPDesk\ShopMagic\MarketingLists;

use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Customer\CustomerDAO;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Exception\CannotCreateGuestException;
use WPDesk\ShopMagic\Exception\CustomerNotFound;
use WPDesk\ShopMagic\MarketingLists\DAO\ListTable;

final class ConfirmedSubscriptionSaver implements Hookable {
	const ACTION = 'double_opt_in';

	/** @var ListTable */
	private $subscriptions;

	/** @var CustomerDAO */
	private $customer_factory;

	public function __construct(ListTable $subscriptions, CustomerFactory $customer_factory) {
		$this->subscriptions    = $subscriptions;
		$this->customer_factory = $customer_factory;
	}

	public function hooks() {
		add_action(
			'admin_post_nopriv_' . self::ACTION,
			function() {
				$this->try_signup_customer();
			}
		);
		add_action(
			'admin_post_' . self::ACTION,
			function() {
				$this->try_signup_customer();
			}
		);
	}

	/** @return void */
	private function try_signup_customer() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		try {
			$customer = $this->retrieve_customer( isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '' );
		} catch ( CustomerNotFound $e ) {
			wp_safe_redirect( trailingslashit( home_url() ) . PreferencesRoute::get_slug() . '?' . http_build_query( [ 'success' => 0 ] ) );
			die;
		}

		$hash = isset( $_GET['hash'] ) ? sanitize_text_field( wp_unslash( $_GET['hash'] ) ) : '';
		if ( ! PreferencesRoute::validate_hash( $customer->get_email(), $hash ) ) {
			wp_safe_redirect( trailingslashit( home_url() ) . PreferencesRoute::get_slug() . '?' . http_build_query( [ 'success' => 0 ] ) );
			die;
		}

		$target_list = isset( $_GET['list_id'] ) ? absint( wp_unslash( $_GET['list_id'] ) ) : 0;
		$this->subscriptions->subscribe( $customer->get_email(), $target_list );

		wp_safe_redirect( trailingslashit( home_url() ) . PreferencesRoute::get_slug() . '?' . http_build_query( [ 'success' => 1 ] ) );
		die;
		// phpcs:enable
	}

	private function retrieve_customer( string $id ): Customer {
		try {
			return $this->customer_factory->create_from_id( $id );
		} catch ( CannotCreateGuestException $e ) {
			throw new CustomerNotFound('Invalid ID.');
		}
	}
}
