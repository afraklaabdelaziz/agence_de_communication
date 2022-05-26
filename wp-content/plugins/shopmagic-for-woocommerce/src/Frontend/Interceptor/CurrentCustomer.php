<?php

namespace WPDesk\ShopMagic\Frontend\Interceptor;

use Psr\Log\LoggerAwareTrait;
use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\ShopMagic\Admin\Settings\GeneralSettings;
use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Customer\CustomerDAO;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Customer\CustomerProvider;
use WPDesk\ShopMagic\Exception\CannotCreateGuestException;
use WPDesk\ShopMagic\Exception\CannotProvideCustomerException;
use WPDesk\ShopMagic\Exception\CustomerNotFound;
use WPDesk\ShopMagic\Guest\GuestDAO;
use WPDesk\ShopMagic\Guest\GuestFactory;
use WPDesk\ShopMagic\Helper\WooCommerceCookies;
use WPDesk\ShopMagic\LoggerFactory;

final class CurrentCustomer implements Hookable, CustomerProvider {
	use LoggerAwareTrait;
	const SESSION_TRACKING_DATA_KEY = 'shopmagic_customer_token';

	/** @var CustomerFactory */
	private $customer_factory;

	/** @var GuestDAO */
	private $guest_dao;

	/** @var GuestFactory */
	private $guest_factory;

	/** @var string */
	private $cookie_name;

	/** @var int */
	private $days_to_expire_cookie;

	/** @var array{user_id?: numeric-string, hash: string, email?: string, meta: string[]} */
	private $tracking_data;

	public function __construct( CustomerFactory $customer_factory, GuestDAO $guest_dao, GuestFactory $guest_factory ) {
		$this->customer_factory = $customer_factory;
		$this->guest_dao        = $guest_dao;
		$this->guest_factory    = $guest_factory;

		$this->cookie_name =
			/**
			 * Cookie name for customer tracking. Used to properly identify the same guest users.
			 *
			 * @param string $cookie_name Current cookie name. shopmagic_visitor_HASH by default.
			 * @return string New cookie name.
			 *
			 * @sice 2.17
			 */
			apply_filters( 'shopmagic/core/customer_interceptor/cookie_name', 'shopmagic_visitor_' . COOKIEHASH );
		$this->days_to_expire_cookie =
			/**
			 * The expiration time for customer tracking cookie.
			 *
			 * @param int $cookie_expiry Current cookie expiration. 365 by default.
			 * @see shopmagic/core/customer_interceptor/cookie_name
			 * @return int New expiration time.
			 *
			 * @sice 2.17
			 */
			apply_filters( 'shopmagic/core/customer_interceptor/cookie_expiry', 365 );

		$this->tracking_data = $this->get_decoded_tracking_data();
		$this->refresh_tracking_user_id();
	}

	/**
	 * @return array{user_id?: numeric-string, email?: string, meta: string[]}
	 */
	private function get_decoded_tracking_data(): array {
		$data = json_decode( $this->get_raw_tracking_data(), true );
		if ( ! is_array( $data ) ) {
			$data = [ 'meta' => [] ];
		}
		unset( $data['hash'] );

		return $data;
	}

	private function encode_tracking_data(): string {
		$hash         = md5( json_encode( $this->tracking_data ) . AUTH_SALT );
		$data         = $this->tracking_data;
		$data['hash'] = $hash;

		return json_encode( $data );
	}

	private function get_raw_tracking_data(): string {
		if ( WC()->session ) {
			$raw_data = WC()->session->get( self::SESSION_TRACKING_DATA_KEY );
			if ( ! empty( $raw_data ) ) {
				return $raw_data;
			}
		}
		if ( $this->is_enabled_cookie() ) {
			$raw_data = WooCommerceCookies::get( $this->cookie_name );
			if ( ! empty( $raw_data ) ) {
				return $raw_data;
			}
		}

		return '';
	}

	private function set_user_id( int $user_id ) {
		$this->tracking_data['user_id'] = $user_id;
		/**
		 * @ignore Action used to internally sync customer status with other interceptors.
		 */
		do_action( 'shopmagic/core/customer_interceptor/changed', $this->tracking_data );
	}

	public function set_user_email( string $email ) {
		$this->tracking_data['email'] = $email;
		/**
		 * @ignore Action used to internally sync customer status with other interceptors.
		 */
		do_action( 'shopmagic/core/customer_interceptor/changed', $this->tracking_data );
	}

	public function set_meta( string $meta_name, string $meta_value ) {
		$this->tracking_data['meta'][ $meta_name ] = $meta_value;
	}

	/**
	 * @internal
	 */
	public function refresh_tracking_user_id() {
		if ( is_user_logged_in() ) {
			$this->set_user_id( (int) wp_get_current_user()->ID );
		}
	}

	public function is_customer_provided(): bool {
		try {
			return $this->get_customer() instanceof Customer;
		} catch ( CannotProvideCustomerException $e ) {
			return false;
		}
	}

	public function get_customer(): Customer {
		if ( is_user_logged_in() ) {
			return $this->customer_factory->create_from_user( new \WP_User( get_current_user_id() ) );
		}

		if ( isset( $this->tracking_data['user_id'] ) ) {
			$user = get_user_by( 'id', $this->tracking_data['user_id'] );
			if ( $user instanceof \WP_User ) {
				return $this->customer_factory->create_from_user( $user );
			}
		}

		if ( isset( $this->tracking_data['email'] ) ) {
			$customer_dao = new CustomerDAO( null, $this->customer_factory );
			try {
				return $customer_dao->find_by_email( $this->tracking_data['email'] );
			} catch ( CustomerNotFound $e ) {
				$guest = $this->guest_factory->create_from_email_and_db( $this->tracking_data['email'] );
				foreach ( $this->tracking_data['meta'] as $meta_key => $meta_value ) {
					$guest->set_meta_value( $meta_key, $meta_value );
				}
				if ( ! $guest->is_saved() || count( $this->tracking_data['meta'] ) > 0 ) {
					$this->guest_dao->save( $guest );
				}

				return $this->customer_factory->create_from_guest( $guest );
			}
		}

		throw new CannotProvideCustomerException( 'Customer is not available' );
	}

	/**
	 * @internal
	 */
	public function remember_tracking_key() {
		$encoded_tracking_data = $this->encode_tracking_data();
		if ( WC()->session ) {
			WC()->session->set( self::SESSION_TRACKING_DATA_KEY, $encoded_tracking_data );
		}
		if ( $this->can_save_cookie( $this->cookie_name, $encoded_tracking_data ) ) {
			WooCommerceCookies::set( $this->cookie_name, $encoded_tracking_data, time() + $this->days_to_expire_cookie * DAY_IN_SECONDS );
		}
	}

	private function is_enabled_session_tracking(): bool {
		return GeneralSettings::get_option( 'enable_session_tracking', true );
	}

	private function is_enabled_cookie(): bool {
		/**
		 * Can be used to globally override cookie usage. When disabled no cookies will ever by created
		 * by the ShopMagic plugins.
		 *
		 * @param bool $enabled Current value. True by default.
		 * @returns bool
		 *
		 * @since 2.17
		 */
		return apply_filters( 'shopmagic/core/customer_interceptor/cookies_enabled', true );
	}

	/**
	 * @param int $comment_id
	 *
	 * @internal
	 */
	public function capture_from_comment( $comment_id ) {
		if ( is_user_logged_in() ) {
			return;
		}

		$comment = get_comment( $comment_id );
		if ( $comment && ! $comment->user_id ) {
			$this->set_user_email( $comment->comment_author_email );
		}
	}

	public function capture_from_order( $order_id, $order = null ) {
		if ( is_user_logged_in() ) {
			return;
		}

		if ( ! $order instanceof \WC_Abstract_Order ) {
			$order = wc_get_order( $order_id );
		}

		$this->set_user_email( $order->get_billing_email() );
	}

	public function hooks() {
		if ( ! $this->is_enabled_session_tracking() ) {
			return;
		}

		add_action( 'wp', [ $this, 'remember_tracking_key' ], 99 );
		add_action( 'shutdown', [ $this, 'remember_tracking_key' ], 0 );
		add_action( 'set_logged_in_cookie', [ $this, 'refresh_tracking_user_id' ], 10, 4 );
		add_action( 'comment_post', [ $this, 'capture_from_comment' ], 10, 2 );
		add_action( 'woocommerce_new_order', [ $this, 'capture_from_order' ], 10, 2 );
		add_action( 'woocommerce_api_create_order', [ $this, 'capture_from_order' ], 10, 2 );
	}

	private function can_save_cookie( string $name, $data ): bool {
		if ( headers_sent() ) {
			return false;
		}

		if ( ! $this->is_enabled_cookie() ) {
			return false;
		}

		if ( empty( $this->tracking_data['email'] ) || empty( $this->tracking_data['user_id'] ) ) {
			return false;
		}

		if ( WooCommerceCookies::is_set( $name ) && WooCommerceCookies::get( $name ) === $data ) {
			return false;
		}

		return true;
	}
}
