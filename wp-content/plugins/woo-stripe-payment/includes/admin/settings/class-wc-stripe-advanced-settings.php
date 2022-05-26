<?php

defined( 'ABSPATH' ) || exit();

/**
 * @since 3.3.13
 */
class WC_Stripe_Advanced_Settings extends WC_Stripe_Settings_API {

	public function __construct() {
		$this->id        = 'stripe_advanced';
		$this->tab_title = __( 'Advanced Settings', 'woo-stripe-payment' );
		parent::__construct();
	}

	public function hooks() {
		parent::hooks();
		add_action( 'woocommerce_update_options_checkout_' . $this->id, array( $this, 'process_admin_options' ) );
		add_filter( 'wc_stripe_settings_nav_tabs', array( $this, 'admin_nav_tab' ) );
		add_action( 'woocommerce_stripe_settings_checkout_' . $this->id, array( $this, 'admin_options' ) );
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'title'                  => array(
				'type'  => 'title',
				'title' => __( 'Advanced Settings', 'woo-stripe-payment' ),
			),
			'locale'                 => array(
				'title'       => __( 'Locale Type', 'woo-stripe-payment' ),
				'type'        => 'select',
				'default'     => 'auto',
				'options'     => array(
					'auto' => __( 'Auto', 'woo-stripe-payment' ),
					'site' => __( 'Site Locale', 'woo-stripe-payment' )
				),
				'desc_tip'    => true,
				'description' => __( 'If set to "auto" Stripe will determine the locale to use based on the customer\'s browser/location settings. Site locale uses the Wordpress locale setting.',
					'woo-stripe-payment' )
			),
			'settings_description'   => array(
				'type'        => 'description',
				'description' => __( 'This section provides advanced settings that allow you to configure functionality that fits your business process.', 'woo-stripe-payment' )
			),
			'statement_descriptor'   => array(
				'title'             => __( 'Statement Descriptor', 'woo-stripe-payment' ),
				'type'              => 'text',
				'default'           => '',
				'desc_tip'          => true,
				'description'       => __( 'Maximum of 22 characters. This value represents the full statement descriptor that your customer will see. If left blank, Stripe will use your account descriptor.',
					'woo-stripe-payment' ),
				'sanitize_callback' => function ( $value ) {
					if ( ! empty( $value ) && strlen( $value ) > 21 ) {
						$value = substr( $value, 0, 22 );
					}

					return WC_Stripe_Utils::sanitize_statement_descriptor( $value );
				}
			),
			'installments'           => array(
				'title'       => __( 'Installments', 'woo-stripe-payment' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'value'       => 'yes',
				'desc_tip'    => false,
				'description' => sprintf( __( 'If enabled, installments will be available for the credit card gateway. %1$s', 'woo-stripe-payment' ), $this->get_supported_countries_description() )
			),
			'stripe_fee'             => array(
				'title'       => __( 'Display Stripe Fee', 'woo-stripe-payment' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'value'       => 'yes',
				'desc_tip'    => true,
				'description' => __( 'If enabled, the Stripe fee will be displayed on the Order Details page. The fee and net payout are displayed in your Stripe account currency.',
					'woo-stripe-payment' )
			),
			'stripe_fee_currency'    => array(
				'title'             => __( 'Fee Display Currency', 'woo-stripe-payment' ),
				'type'              => 'checkbox',
				'default'           => 'no',
				'description'       => __( 'If enabled, the Stripe fee and payout will be displayed in the currency of the order. Stripe by default provides the fee and payout in the Stripe account\'s currency.',
					'woo-stripe-payment' ),
				'custom_attributes' => array(
					'data-show-if' => array(
						'stripe_fee' => true
					)
				)
			),
			'refund_cancel'          => array(
				'title'       => __( 'Refund On Cancel', 'woo-stripe-payment' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'value'       => 'yes',
				'desc_tip'    => true,
				'description' => __( 'If enabled, the plugin will process a payment cancellation or refund within Stripe when the order\'s status is set to cancelled.', 'woo-stripe-payment' )
			),
			'disputes'               => array(
				'title' => __( 'Dispute Settings', 'woo-stripe-payment' ),
				'type'  => 'title'
			),
			'dispute_created'        => array(
				'title'       => __( 'Dispute Created', 'woo-stripe-payment' ),
				'type'        => 'checkbox',
				'default'     => 'yes',
				'description' => __( 'If enabled, the plugin will listen for the <strong>charge.dispute.created</strong> webhook event and set the order\'s status to on-hold by default.',
					'woo-stripe-payment' )
			),
			'dispute_created_status' => array(
				'title'             => __( 'Disputed Created Order Status', 'woo-stripe-payment' ),
				'type'              => 'select',
				'default'           => 'wc-on-hold',
				'options'           => wc_get_order_statuses(),
				'description'       => __( 'The status assigned to an order when a dispute is created.', 'woo-stripe-payment' ),
				'custom_attributes' => array(
					'data-show-if' => array(
						'dispute_created' => true
					)
				)
			),
			'dispute_closed'         => array(
				'title'       => __( 'Dispute Closed', 'woo-stripe-payment' ),
				'type'        => 'checkbox',
				'default'     => 'yes',
				'description' => __( 'If enabled, the plugin will listen for the <strong>charge.dispute.closed</strong> webhook event and set the order\'s status back to the status before the dispute was opened.',
					'woo-stripe-payment' )
			),
			'reviews'                => array(
				'title' => __( 'Review Settings', 'woo-stripe-payment' ),
				'type'  => 'title'
			),
			'review_created'         => array(
				'title'       => __( 'Review Created', 'woo-stripe-payment' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __( 'If enabled, the plugin will listen for the <strong>review.created</strong> webhook event and set the order\'s status to on-hold by default.',
					'woo-stripe-payment' )
			),
			'review_closed'          => array(
				'title'       => __( 'Review Closed', 'woo-stripe-payment' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __( 'If enabled, the plugin will listen for the <strong>review.closed</strong> webhook event and set the order\'s status back to the status before the review was opened.',
					'woo-stripe-payment' )
			),
			'email_title'            => array(
				'type'  => 'title',
				'title' => __( 'Stripe Email Options', 'woo-stripe-payment' )
			),
			'email_enabled'          => array(
				'type'        => 'checkbox',
				'title'       => __( 'Email Receipt', 'woo-stripe-payment' ),
				'default'     => 'no',
				'description' => __( 'If enabled, an email receipt will be sent to the customer by Stripe when the order is processed.',
					'woo-stripe-payment' ),
			)
		);
	}

	public function is_fee_enabled() {
		return $this->is_active( 'stripe_fee' );
	}

	public function is_display_order_currency() {
		return $this->is_active( 'stripe_fee_currency' );
	}

	public function is_email_receipt_enabled() {
		return $this->is_active( 'email_enabled' );
	}

	public function is_refund_cancel_enabled() {
		return $this->is_active( 'refund_cancel' );
	}

	public function is_dispute_created_enabled() {
		return $this->is_active( 'dispute_created' );
	}

	public function is_dispute_closed_enabled() {
		return $this->is_active( 'dispute_closed' );
	}

	public function is_review_opened_enabled() {
		return $this->is_active( 'review_created' );
	}

	public function is_review_closed_enabled() {
		return $this->is_active( 'review_closed' );
	}

	public function get_supported_countries_description() {
		return sprintf( __( 'Supported Stripe account countries: %1$s', 'woo-stripe-payment' ), implode( ', ', \PaymentPlugins\Stripe\Installments\InstallmentController::get_supported_countries() ) );
	}

}
