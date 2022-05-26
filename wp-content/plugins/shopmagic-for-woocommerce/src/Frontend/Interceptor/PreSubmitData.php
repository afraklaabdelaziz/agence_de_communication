<?php

namespace WPDesk\ShopMagic\Frontend\Interceptor;

use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\ShopMagic\Admin\Settings\GeneralSettings;

class PreSubmitData implements Hookable {

	/** @var CurrentCustomer */
	private $customer_interceptor;

	/** @var string */
	private $assets_url;

	public function __construct( CurrentCustomer $customer_interceptor, string $assets_url ) {
		$this->customer_interceptor = $customer_interceptor;
		$this->assets_url           = $assets_url;
	}

	public function hooks() {
		if ( $this->is_enabled_pre_capture() ) {
			add_action(
				'wp_enqueue_scripts',
				function () {
					if ( ! is_checkout() ) {
						return;
					}
					wp_register_script( 'shopmagic-presubmit', $this->assets_url . 'js/presubmit.js', [ 'jquery' ], SHOPMAGIC_VERSION, true );
					wp_localize_script( 'shopmagic-presubmit', 'shopmagic_presubmit_params', $this->get_js_params() );
					wp_enqueue_script( 'shopmagic-presubmit' );
				}
			);
			add_action( 'wp_ajax_nopriv_capture_email_url', [ $this, 'ajax_capture_email' ] );
			add_action( 'wp_ajax_nopriv_capture_checkout_field_url', [ $this, 'ajax_capture_checkout_field' ] );
		}
	}

	private function is_enabled_pre_capture(): bool {
		return GeneralSettings::get_option( 'enable_pre_submit' ) && ! is_user_logged_in();
	}

	private function get_js_params(): array {
		$params                               = [];
		$params['email_capture_selectors']    = $this->get_email_capture_selectors();
		$params['checkout_capture_selectors'] = $this->get_checkout_capture_fields();
		$params['capture_email_url']          = add_query_arg( [ 'action' => 'capture_email_url' ], admin_url( 'admin-ajax.php' ) );
		$params['capture_checkout_field_url'] = add_query_arg( [ 'action' => 'capture_checkout_field_url' ], admin_url( 'admin-ajax.php' ) );

		return $params;
	}

	private function get_email_capture_selectors(): array {
		return apply_filters(
			'shopmagic/core/presubmit/guest_capture_fields',
			[
				'.woocommerce-checkout [type="email"]',
				'#billing_email',
				'.automatewoo-capture-guest-email',
				'input[name="billing_email"]',
			]
		);
	}

	private function get_checkout_capture_fields(): array {
		return apply_filters(
			'shopmagic/core/presubmit/checkout_capture_fields',
			[
				'billing_first_name',
				'billing_last_name',
				'billing_company',
				'billing_phone',
				'billing_country',
				'billing_address_1',
				'billing_address_2',
				'billing_city',
				'billing_state',
				'billing_postcode',
			]
		);
	}

	public function ajax_capture_email() {
		$email           = sanitize_email( $_REQUEST['email'] );
		$checkout_fields = $_REQUEST['checkout_fields'];

		$this->customer_interceptor->set_user_email( $email );

		if ( is_array( $checkout_fields ) ) {
			foreach ( $checkout_fields as $field_name => $field_value ) {

			if ( ! $this->is_checkout_capture_field( $field_name ) || empty( $field_value ) ) {
					continue; // IMPORTANT don't save the field if it is empty.
				}

				$this->customer_interceptor->set_meta( sanitize_key( $field_name ), sanitize_text_field( $field_value ) );
			}
		} else {
			$location = wc_get_customer_default_location();
			if ( $location['country'] ) {
				$this->customer_interceptor->set_meta( 'billing_country', $location['country'] );
			}
		}

		wp_send_json_success();
	}

	private function is_checkout_capture_field( string $field_name ): bool {
		return in_array( $field_name, $this->get_checkout_capture_fields(), true );
	}

	/**
	 * Capture an additional field from the checkout page
	 */
	public function ajax_capture_checkout_field() {
		$field_name  = sanitize_key( $_REQUEST['field_name'] );
		$field_value = stripslashes( sanitize_text_field( $_REQUEST['field_value'] ) );

		if ( $this->is_checkout_capture_field( $field_name ) ) {
			$this->customer_interceptor->set_meta( $field_name, $field_value );
		}
		wp_send_json_success();
	}
}
