<?php

namespace PaymentPlugins\Blocks\Stripe\Payments;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\Blocks\Payments\PaymentContext;
use Automattic\WooCommerce\Blocks\Payments\PaymentResult;
use \PaymentPlugins\Blocks\Stripe\Assets\Api as AssetsApi;

abstract class AbstractStripePayment extends AbstractPaymentMethodType {

	/**
	 * The Payment Method that is wrapped by this class.
	 * @var \WC_Payment_Gateway_Stripe
	 */
	protected $payment_method;

	protected $assets_api;

	public function __construct( AssetsApi $assets_api ) {
		$this->assets_api     = $assets_api;
		$this->payment_method = WC()->payment_gateways()->payment_gateways()[ $this->get_name() ];
		$this->init();
	}

	protected function init() {
		add_filter( 'woocommerce_saved_payment_methods_list', array( $this, 'transform_payment_method_type' ), 99 );
	}

	public function initialize() {
		$this->settings = $this->payment_method->settings;
	}


	public function is_active() {
		return $this->payment_method && $this->payment_method->is_available();
	}

	public function get_payment_method_script_handles() {
		return array();
	}

	public function get_payment_method_data() {
		return array(
			'name'                  => $this->get_name(),
			'title'                 => $this->payment_method->get_title(),
			'showSaveOption'        => $this->payment_method->supports( 'tokenization' ),
			'showSavedCards'        => $this->payment_method->supports( 'tokenization' ),
			'features'              => $this->get_supported_features(),
			'expressCheckout'       => $this->is_express_checkout_enabled(),
			'cartCheckoutEnabled'   => $this->is_cart_checkout_enabled(),
			'countryCode'           => wc_get_base_location()['country'],
			'totalLabel'            => __( 'Total', 'woo-stripe-payment' ),
			'isAdmin'               => is_admin(),
			'icons'                 => $this->get_payment_method_icon(),
			'placeOrderButtonLabel' => $this->payment_method->order_button_text,
			'description'           => $this->payment_method->get_description()
		);
	}

	public function get_supported_features() {
		return $this->payment_method->supports;
	}

	/**
	 * Blocks only recognize payment tokens of type 'cc' therefore it's necessary to map
	 * the 'stripe_cc' list entry to 'cc'.
	 *
	 * @param $list
	 *
	 * @return mixed
	 */
	public function transform_payment_method_type( $list ) {
		if ( isset( $list[ $this->get_name() ] ) ) {
			if ( isset( $list['cc'] ) ) {
				foreach ( $list[ $this->get_name() ] as $entry ) {
					$list['cc'][] = $entry;
				}
			} else {
				$list['cc'] = $list[ $this->get_name() ];
			}
			unset( $list[ $this->get_name() ] );
		}

		return $list;
	}

	/**
	 * Return true if the express checkout option is enabled for the payment method.
	 * @return bool
	 */
	protected function is_express_checkout_enabled() {
		return $this->payment_method->banner_checkout_enabled();
	}

	protected function is_cart_checkout_enabled() {
		return $this->payment_method->cart_checkout_enabled();
	}

	/**
	 * @param \PaymentPlugins\Blocks\Stripe\Assets\Api $style_api
	 */
	public function enqueue_payment_method_styles( $style_api ) {
	}

	protected function get_payment_method_icon() {
		return array();
	}
}