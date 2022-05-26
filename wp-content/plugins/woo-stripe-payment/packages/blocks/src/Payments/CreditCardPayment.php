<?php

namespace PaymentPlugins\Blocks\Stripe\Payments;

use PaymentPlugins\Stripe\Installments\InstallmentController;

class CreditCardPayment extends AbstractStripePayment {

	protected $name = 'stripe_cc';

	/**
	 * @var InstallmentController
	 */
	private $installments;

	public function get_payment_method_script_handles() {
		$this->assets_api->register_script( 'wc-stripe-block-credit-card', 'build/wc-stripe-credit-card.js' );

		return array( 'wc-stripe-block-credit-card' );
	}

	public function get_payment_method_data() {
		$assets_url = $this->assets_api->get_asset_url( '../../assets/img/cards/' );

		return wp_parse_args( array(
			'cardOptions'            => $this->payment_method->get_card_form_options(),
			'customFieldOptions'     => $this->payment_method->get_card_custom_field_options(),
			'customFormActive'       => $this->payment_method->is_custom_form_active(),
			'elementOptions'         => $this->payment_method->get_element_options(),
			'customForm'             => $this->payment_method->get_option( 'custom_form' ),
			'customFormLabels'       => wp_list_pluck( wc_stripe_get_custom_forms(), 'label' ),
			'postalCodeEnabled'      => $this->payment_method->postal_enabled(),
			'saveCardEnabled'        => $this->payment_method->is_active( 'save_card_enabled' ),
			'savePaymentMethodLabel' => __( 'Save Card', 'woo-stripe-payment' ),
			'installmentsActive'     => $this->installments->is_available(),
			'cards'                  => array(
				'visa'       => $assets_url . 'visa.svg',
				'amex'       => $assets_url . 'amex.svg',
				'mastercard' => $assets_url . 'mastercard.svg',
				'discover'   => $assets_url . 'discover.svg',
				'diners'     => $assets_url . 'diners.svg',
				'jcb'        => $assets_url . 'jcb.svg',
				'maestro'    => $assets_url . 'maestro.svg',
				'unionpay'   => $assets_url . 'china_union_pay.svg',
				'unknown'    => $this->payment_method->get_custom_form()['cardBrand'],
			)
		), parent::get_payment_method_data() );
	}

	protected function get_payment_method_icon() {
		$icons = array();
		foreach ( $this->payment_method->get_option( 'cards', [] ) as $id ) {
			$icons[] = array(
				'id'  => $id,
				'alt' => '',
				'src' => stripe_wc()->assets_url( "img/cards/{$id}.svg" )
			);
		}

		return $icons;
	}

	/**
	 * @param \PaymentPlugins\Blocks\Stripe\Assets\Api $style_api
	 */
	public function enqueue_payment_method_styles( $style_api ) {
		if ( $this->payment_method->is_custom_form_active() ) {
			$form = $this->payment_method->get_option( 'custom_form' );
			wp_enqueue_style( 'wc-stripe-credit-card-style', $style_api->get_asset_url( "build/credit-card/{$form}.css" ) );
			wp_style_add_data( 'wc-stripe-credit-card-style', 'rtl', 'replace' );
		}
	}

	public function set_installments( InstallmentController $installments ) {
		$this->installments = $installments;
	}

}