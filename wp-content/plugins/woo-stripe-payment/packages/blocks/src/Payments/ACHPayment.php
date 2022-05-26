<?php


namespace PaymentPlugins\Blocks\Stripe\Payments;


class ACHPayment extends AbstractStripePayment {

	protected $name = 'stripe_ach';

	public function get_payment_method_script_handles() {
		$this->assets_api->register_external_script( 'wc-stripe-plaid', 'https://cdn.plaid.com/link/v2/stable/link-initialize.js', array(), null );
		$this->assets_api->register_script( 'wc-stripe-blocks-ach', 'build/wc-stripe-ach.js' );

		return array( 'wc-stripe-blocks-ach' );
	}

	public function get_payment_method_icon() {
		return array(
			'id'  => $this->get_name(),
			'alt' => 'ACH Payment',
			'src' => $this->payment_method->icon
		);
	}

	public function get_payment_method_data() {
		return wp_parse_args( array(
			'plaidEnvironment' => $this->payment_method->get_plaid_environment(),
			'clientName'       => $this->payment_method->get_option( 'client_name' ),
		), parent::get_payment_method_data() );
	}
}