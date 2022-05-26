<?php


namespace PaymentPlugins\Blocks\Stripe\Payments;


class AfterpayPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_afterpay';

	public function get_payment_method_data() {
		$data = wp_parse_args( array(
			'requiredParams' => $this->payment_method->get_required_parameters(),
			'msgOptions'     => $this->payment_method->get_afterpay_message_options(),
			'offSiteSrc'     => stripe_wc()->assets_url( 'img/offsite.svg' ),
			'cartTotal'      => WC()->cart ? wc_stripe_add_number_precision( WC()->cart->total ) : 0,
			'currency'       => get_woocommerce_currency(),
			'accountCountry' => $this->get_account_country(),
			'hideIneligible' => $this->payment_method->is_active( 'hide_ineligible' )
		), parent::get_payment_method_data() );
		if ( ! in_array( $data['locale'], $this->payment_method->get_supported_locales() ) ) {
			$data['locale'] = 'auto';
		}

		return $data;
	}

	/**
	 * @since 3.3.12
	 * @return mixed|string
	 */
	private function get_account_country() {
		$country = stripe_wc()->account_settings->get_option( 'country' );
		if ( empty( $country ) && wc_stripe_mode() === 'test' ) {
			$country = wc_get_base_location()['country'];
		}

		return $country;
	}

}