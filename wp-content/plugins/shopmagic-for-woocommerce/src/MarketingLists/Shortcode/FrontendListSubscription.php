<?php
declare( strict_types=1 );

namespace WPDesk\ShopMagic\MarketingLists\Shortcode;

use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\ShopMagic\CommunicationList\CommunicationListRepository;
use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Customer\CustomerDAO;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Exception\CustomerNotFound;
use WPDesk\ShopMagic\Guest\GuestDAO;
use WPDesk\ShopMagic\Guest\GuestFactory;
use WPDesk\ShopMagic\MarketingLists\DAO\ListTable;

final class FrontendListSubscription implements Hookable {

	/** @var ListTable */
	private $subscriptions;

	/** @var CustomerDAO */
	private $customers_repository;

	/** @var CommunicationListRepository */
	private $lists_repository;

	public function __construct( ListTable $subscriptions, CustomerDAO $customers_repository, CommunicationListRepository $lists_repository ) {
		$this->subscriptions        = $subscriptions;
		$this->customers_repository = $customers_repository;
		$this->lists_repository     = $lists_repository;
	}

	/** @return void */
	public function hooks() {
		add_action(
			'wp_ajax_' . FrontendForm::ACTION,
			function() {
				$this->try_signup_customer();
			}
		);
		add_action(
			'wp_ajax_nopriv_' . FrontendForm::ACTION,
			function() {
				$this->try_signup_customer();
			}
		);
	}

	/** @return void */
	private function try_signup_customer() {
		check_ajax_referer( FrontendForm::ACTION );

		$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$list_id = isset( $_POST['list_id'] ) ? absint( wp_unslash( $_POST['list_id'] ) ) : 0;

		if ( empty( $email ) ) {
			wp_send_json_error( esc_html__( 'Please, enter a valid email address!', 'shopmagic-for-woocommerce' ) );
		}

		if ( $this->subscriptions->is_subscribed_to_list( $email, $list_id ) ) {
			wp_send_json_error( esc_html__( 'You are already subscribed.', 'shopmagic-for-woocommerce' ) );
		}

		$customer = $this->retrieve_customer( $email, $name );

		if ( isset( $_POST['double_optin'] ) ) {
			( new ConfirmationDispatcher( $customer, $this->lists_repository->get_by_id( $list_id ) ) )
				->dispatch_ajax_confirmation();
			die;
		}

		$result = $this->subscriptions->subscribe( $customer->get_email(), $list_id );

		if ( $result === false ) {
			wp_send_json_error( esc_html__( 'An error occurred during sign up.', 'shopmagic-for-woocommerce' ) );
		}

		wp_send_json_success( esc_html__( 'You have been successfully subscribed!', 'shopmagic-for-woocommerce' ) );
	}

	private function retrieve_customer( string $email, string $name ): Customer {
		try {
			return $this->customers_repository->find_by_email( $email );
		} catch ( CustomerNotFound $e ) {
			global $wpdb;

			$guest_repository = new GuestDAO( $wpdb );
			$guest_factory    = new GuestFactory( $guest_repository );
			$guest            = $guest_factory->create_from_email_and_db( $email );
			$guest->set_meta_value( 'first_name', $name );
			return ( new CustomerFactory() )
				->create_from_guest( $guest_repository->save( $guest ) );
		}
	}

}
