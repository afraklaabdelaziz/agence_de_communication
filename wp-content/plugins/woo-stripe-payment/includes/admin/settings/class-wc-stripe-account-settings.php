<?php

defined( 'ABSPATH' ) || exit();

/**
 * Class WC_Stripe_Account_Settings
 *
 * @since   3.1.7
 * @author  Payment Plugins
 * @package Stripe/Classes
 */
class WC_Stripe_Account_Settings extends WC_Stripe_Settings_API {

	private $api_settings = array();

	const DEFAULT_ACCOUNT_SETTINGS = array(
		'account_id'       => '',
		'country'          => '',
		'default_currency' => ''
	);

	public function __construct() {
		$this->id = 'stripe_account';
		parent::__construct();
	}

	public function hooks() {
		add_action( 'wc_stripe_connect_settings', array( $this, 'connect_settings' ) );
		add_action( 'woocommerce_update_options_checkout_stripe_api', array( $this, 'pre_api_update' ), 5 );
		add_action( 'woocommerce_update_options_checkout_stripe_api', array( $this, 'post_api_update' ), 20 );
		add_action( 'wc_stripe_api_connection_test_success', array( $this, 'connection_test_success' ) );
	}

	/**
	 * @param object $response
	 */
	public function connect_settings( $response ) {
		foreach ( $response as $mode => $data ) {
			$this->save_account_settings( $data->stripe_user_id, $mode );
		}
	}

	/**
	 * @param string $account_id
	 */
	public function save_account_settings( $account_id, $mode = 'live' ) {
		// fetch the account and store the account data.
		$account = WC_Stripe_Gateway::load( $mode )->accounts->retrieve( $account_id );
		if ( ! is_wp_error( $account ) ) {
			if ( $mode === 'live' ) {
				$this->settings['account_id']       = $account->id;
				$this->settings['country']          = strtoupper( $account->country );
				$this->settings['default_currency'] = strtoupper( $account->default_currency );
			} else {
				$this->settings[ WC_Stripe_Constants::TEST ] = array(
					'account_id'       => $account->id,
					'country'          => strtoupper( $account->country ),
					'default_currency' => strtoupper( $account->default_currency )
				);
			}
			update_option( $this->get_option_key(), $this->settings, 'yes' );
		}
	}

	public function pre_api_update() {
		$settings           = stripe_wc()->api_settings;
		$this->api_settings = array(
			'key'    => $settings->get_option( 'publishable_key_test' ),
			'secret' => $settings->get_option( 'secret_key_test' )
		);
	}

	public function post_api_update() {
		$api_settings = stripe_wc()->api_settings;
		$settings     = array(
			'key'    => $api_settings->get_option( 'publishable_key_test' ),
			'secret' => $api_settings->get_option( 'secret_key_test' )
		);
		$is_valid     = array_filter( $settings ) == $settings;
		if ( ( ! isset( $this->settings['test'] ) || $settings != $this->api_settings ) && $is_valid ) {
			$this->save_account_settings( null, 'test' );
		}
	}

	public function connection_test_success( $mode ) {
		if ( $mode === WC_Stripe_Constants::TEST ) {
			unset( $this->settings[ WC_Stripe_Constants::TEST ] );
			$this->post_api_update();
		}
	}

	public function get_account_country( $mode = 'live' ) {
		if ( $mode === WC_Stripe_Constants::LIVE ) {
			$country = $this->get_option( 'country' );
		} else {
			$settings = $this->get_option( 'test', self::DEFAULT_ACCOUNT_SETTINGS );
			$country  = $settings['country'];
		}

		return $country;
	}

}