<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe_Local_Payment' ) ) {
	return;
}

/**
 * Class WC_Payment_Gateway_Stripe_Afterpay
 *
 * @since   3.3.1
 * @package Stripe/Gateways
 */
class WC_Payment_Gateway_Stripe_Afterpay extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	protected $payment_method_type = 'afterpay_clearpay';

	public function __construct() {
		$this->local_payment_type = 'afterpay_clearpay';
		$this->currencies         = array( 'AUD', 'CAD', 'NZD', 'GBP', 'USD' );
		$this->countries          = array( 'AU', 'CA', 'NZ', 'GB', 'US' );
		$this->id                 = 'stripe_afterpay';
		$this->tab_title          = __( 'Afterpay', 'woo-stripe-payment' );
		$this->method_title       = __( 'Afterpay', 'woo-stripe-payment' );
		$this->method_description = __( 'Afterpay gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		$this->icon               = '';
		parent::__construct();
		$this->template_name = 'afterpay.php';
	}

	public function init_supports() {
		parent::init_supports();
		$this->supports[] = 'wc_stripe_cart_checkout';
		$this->supports[] = 'wc_stripe_product_checkout';
		$this->supports[] = 'wc_stripe_mini_cart_checkout';
	}

	public function get_order_button_text( $text ) {
		return __( 'Complete Order', 'woo-stripe-payment' );
	}

	public function get_local_payment_settings() {
		$settings = wp_parse_args( array(
			'charge_type'                 => array(
				'type'        => 'select',
				'title'       => __( 'Charge Type', 'woo-stripe-payment' ),
				'default'     => 'capture',
				'class'       => 'wc-enhanced-select',
				'options'     => array(
					'capture'   => __( 'Capture', 'woo-stripe-payment' ),
					'authorize' => __( 'Authorize', 'woo-stripe-payment' ),
				),
				'desc_tip'    => true,
				'description' => __( 'This option determines whether the customer\'s funds are captured immediately or authorized and can be captured at a later date.',
					'woo-stripe-payment' ),
			),
			'payment_sections'            => array(
				'type'        => 'multiselect',
				'title'       => __( 'Payment Sections', 'woo-stripe-payment' ),
				'class'       => 'wc-enhanced-select',
				'options'     => array(
					'product'   => __( 'Product Page', 'woo-stripe-payment' ),
					'cart'      => __( 'Cart Page', 'woo-stripe-payment' ),
					'mini_cart' => __( 'Mini Cart', 'woo-stripe-payment' ),
				),
				'default'     => array( 'product', 'cart' ),
				'description' => __( 'These are the additional sections where the Afterpay messaging will be enabled. You can control individual products via the Edit product page.',
					'woo-stripe-payment' ),
			),
			'hide_ineligible'             => array(
				'title'       => __( 'Hide If Ineligible', 'woo-stripe-payment' ),
				'type'        => 'checkbox',
				'value'       => 'yes',
				'default'     => 'no',
				'desc_tip'    => true,
				'description' => __( 'If enabled, Afterpay won\'t show when the products in the cart are not eligible.', 'woo-stripe-payment' )
			),
			'checkout_styling'            => array(
				'type'  => 'title',
				'title' => __( 'Checkout Page Styling', 'woo-stripe-payments' )
			),
			'icon_checkout'               => array(
				'title'       => __( 'Icon', 'woo-stripe-payment' ),
				'type'        => 'select',
				'default'     => 'black-on-mint',
				'options'     => array(
					'black-on-mint'  => __( 'Black on mint', 'woo-stripe-payment' ),
					'black-on-white' => __( 'Black on white', 'woo-stripe-payment' ),
					'mint-on-black'  => __( 'Mint on black', 'woo-stripe-payment' ),
					'white-on-black' => __( 'White on black', 'woo-stripe-payment' )
				),
				'desc_tip'    => true,
				'description' => __( 'This is the icon style that appears next to the gateway on the checkout page.', 'woo-stripe-payment' ),
			),
			'intro_text_checkout'         => array(
				'title'   => __( 'Intro text', 'woo-stripe-payment' ),
				'type'    => 'select',
				'default' => 'In',
				'options' => array(
					'In'     => 'In',
					'Or'     => 'Or',
					'Pay'    => 'Pay',
					'Pay in' => 'Pay in'
				)
			),
			'modal_link_style_checkout'   => array(
				'title'       => __( 'Modal link style', 'woo-stripe-payment' ),
				'type'        => 'select',
				'default'     => 'circled-info-icon',
				'options'     => array(
					'more-info-text'    => __( 'More info text', 'woo-stripe-payment' ),
					'circled-info-icon' => __( 'Circled info icon', 'woo-stripe-payment' ),
					'learn-more-text'   => __( 'Learn more text', 'woo-stripe-payment' ),
				),
				'description' => __( 'This is the style of the Afterpay info link.', 'woo-stripe-payment' ),
				'desc_tip'    => true
			),
			'modal_theme_checkout'        => array(
				'title'       => __( 'Modal link style', 'woo-stripe-payment' ),
				'type'        => 'select',
				'default'     => 'mint',
				'options'     => array(
					'mint'  => __( 'Mint', 'woo-stripe-payment' ),
					'white' => __( 'White', 'woo-stripe-payment' )
				),
				'description' => __( 'This is the theme color for the Afterpay info modal.', 'woo-stripe-payment' ),
				'desc_tip'    => true
			),
			'show_interest_free_checkout' => array(
				'type'        => 'checkbox',
				'title'       => __( 'Show interest free', 'woo-stripe-payment' ),
				'default'     => 'no',
				'value'       => 'yes',
				'description' => __( 'If enabled, the Afterpay message will contain the interest free text.', 'woo-stripe-payment' ),
				'desc_tip'    => true
			),
			'cart_styling'                => array(
				'type'  => 'title',
				'title' => __( 'Cart Page Styling' )
			),
			'icon_cart'                   => array(
				'title'       => __( 'Icon', 'woo-stripe-payment' ),
				'type'        => 'select',
				'default'     => 'black-on-mint',
				'options'     => array(
					'black-on-mint'  => __( 'Black on mint', 'woo-stripe-payment' ),
					'black-on-white' => __( 'Black on white', 'woo-stripe-payment' ),
					'mint-on-black'  => __( 'Mint on black', 'woo-stripe-payment' ),
					'white-on-black' => __( 'White on black', 'woo-stripe-payment' )
				),
				'desc_tip'    => true,
				'description' => __( 'This is the icon style that appears next to the gateway on the checkout page.', 'woo-stripe-payment' ),
			),
			'intro_text_cart'             => array(
				'title'   => __( 'Intro text', 'woo-stripe-payment' ),
				'type'    => 'select',
				'default' => 'Or',
				'options' => array(
					'In'     => 'In',
					'Or'     => 'Or',
					'Pay'    => 'Pay',
					'Pay in' => 'Pay in'
				)
			),
			'modal_link_style_cart'       => array(
				'title'       => __( 'Modal link style', 'woo-stripe-payment' ),
				'type'        => 'select',
				'default'     => 'circled-info-icon',
				'options'     => array(
					'more-info-text'    => __( 'More info text', 'woo-stripe-payment' ),
					'circled-info-icon' => __( 'Circled info icon', 'woo-stripe-payment' ),
					'learn-more-text'   => __( 'Learn more text', 'woo-stripe-payment' ),
				),
				'description' => __( 'This is the style of the Afterpay info link.', 'woo-stripe-payment' ),
				'desc_tip'    => true
			),
			'modal_theme_cart'            => array(
				'title'       => __( 'Modal link style', 'woo-stripe-payment' ),
				'type'        => 'select',
				'default'     => 'mint',
				'options'     => array(
					'mint'  => __( 'Mint', 'woo-stripe-payment' ),
					'white' => __( 'White', 'woo-stripe-payment' )
				),
				'description' => __( 'This is the theme color for the Afterpay info modal.', 'woo-stripe-payment' ),
				'desc_tip'    => true
			),
			'show_interest_free_cart'     => array(
				'type'        => 'checkbox',
				'title'       => __( 'Show interest free', 'woo-stripe-payment' ),
				'default'     => 'no',
				'value'       => 'yes',
				'description' => __( 'If enabled, the Afterpay message will contain the interest free text.', 'woo-stripe-payment' ),
				'desc_tip'    => true
			),
			'product_styling'             => array(
				'type'  => 'title',
				'title' => __( 'Product Page Styling' )
			),
			'icon_product'                => array(
				'title'       => __( 'Icon', 'woo-stripe-payment' ),
				'type'        => 'select',
				'default'     => 'black-on-mint',
				'options'     => array(
					'black-on-mint'  => __( 'Black on mint', 'woo-stripe-payment' ),
					'black-on-white' => __( 'Black on white', 'woo-stripe-payment' ),
					'mint-on-black'  => __( 'Mint on black', 'woo-stripe-payment' ),
					'white-on-black' => __( 'White on black', 'woo-stripe-payment' )
				),
				'desc_tip'    => true,
				'description' => __( 'This is the icon style that appears next to the gateway on the checkout page.', 'woo-stripe-payment' ),
			),
			'intro_text_product'          => array(
				'title'   => __( 'Intro text', 'woo-stripe-payment' ),
				'type'    => 'select',
				'default' => 'Pay in',
				'options' => array(
					'In'     => 'In',
					'Or'     => 'Or',
					'Pay'    => 'Pay',
					'Pay in' => 'Pay in'
				)
			),
			'modal_link_style_product'    => array(
				'title'       => __( 'Modal link style', 'woo-stripe-payment' ),
				'type'        => 'select',
				'default'     => 'circled-info-icon',
				'options'     => array(
					'more-info-text'    => __( 'More info text', 'woo-stripe-payment' ),
					'circled-info-icon' => __( 'Circled info icon', 'woo-stripe-payment' ),
					'learn-more-text'   => __( 'Learn more text', 'woo-stripe-payment' ),
				),
				'description' => __( 'This is the style of the Afterpay info link.', 'woo-stripe-payment' ),
				'desc_tip'    => true
			),
			'modal_theme_product'         => array(
				'title'       => __( 'Modal link style', 'woo-stripe-payment' ),
				'type'        => 'select',
				'default'     => 'mint',
				'options'     => array(
					'mint'  => __( 'Mint', 'woo-stripe-payment' ),
					'white' => __( 'White', 'woo-stripe-payment' )
				),
				'description' => __( 'This is the theme color for the Afterpay info modal.', 'woo-stripe-payment' ),
				'desc_tip'    => true
			),
			'show_interest_free_product'  => array(
				'type'        => 'checkbox',
				'title'       => __( 'Show interest free', 'woo-stripe-payment' ),
				'default'     => 'no',
				'value'       => 'yes',
				'description' => __( 'If enabled, the Afterpay message will contain the interest free text.', 'woo-stripe-payment' ),
				'desc_tip'    => true
			)
		), parent::get_local_payment_settings() );

		// @todo maybe add this option back in a future version.
		unset( $settings['title_text'] );

		return $settings;
	}

	public function enqueue_product_scripts( $scripts ) {
		$scripts->enqueue_script( 'afterpay-product', $scripts->assets_url( 'js/frontend/afterpay.js' ), array(
			$scripts->get_handle( 'wc-stripe' )
		) );
		$scripts->localize_script( 'afterpay-product', $this->get_localized_params( 'product' ) );
	}

	public function enqueue_cart_scripts( $scripts ) {
		$scripts->enqueue_script( 'afterpay-cart', $scripts->assets_url( 'js/frontend/afterpay.js' ), array(
			$scripts->get_handle( 'wc-stripe' )
		) );
		$scripts->localize_script( 'afterpay-cart', $this->get_localized_params( 'cart' ) );
	}

	public function product_fields() {
		$this->enqueue_frontend_scripts( 'product' );
		$this->output_display_items( 'product' );
	}

	public function cart_fields() {
		$this->enqueue_frontend_scripts( 'cart' );
		$this->output_display_items( 'cart' );
	}

	public function mini_cart_fields() {
		$this->output_display_items( 'cart' );
	}

	public function get_required_parameters() {
		return apply_filters( 'wc_stripe_afterpay_get_required_parameters', array(
			'AUD' => array( 'AU', 1, 2000 ),
			'CAD' => array( 'CA', 1, 2000 ),
			'NZD' => array( 'NZ', 1, 2000 ),
			'GBP' => array( 'GB', 1, 1000 ),
			'USD' => array( 'US', 1, 2000 )
		), $this );
	}

	/**
	 * @param $currency
	 * @param $billing_country
	 * @param $total
	 *
	 * @return bool
	 */
	public function validate_local_payment_available( $currency, $billing_country, $total ) {
		$_available      = false;
		$account_country = stripe_wc()->account_settings->get_account_country( wc_stripe_mode() );
		// in test mode, the API keys might have been manually entered which
		// means the account settings 'country' value will be blank
		if ( empty( $account_country ) && wc_stripe_mode() === 'test' ) {
			$account_country = wc_get_base_location()['country'];
		}
		$params          = $this->get_required_parameters();
		$filtered_params = isset( $params[ $currency ] ) ? $params[ $currency ] : false;
		if ( $filtered_params ) {
			list( $country, $min_amount, $max_amount ) = $filtered_params;
			// country associated with currency must match the Stripe account's registered country
			$_available = $account_country === $country && $min_amount <= $total && $total <= $max_amount;
		}

		return $_available;
	}

	public function get_icon() {
		return '';
	}

	public function get_localized_params( $context = 'checkout' ) {
		$params                      = parent::get_localized_params();
		$params['currencies']        = $this->currencies;
		$params['msg_options']       = $this->get_afterpay_message_options( $context );
		$params['supported_locales'] = $this->get_supported_locales();
		$params['requirements']      = $this->get_required_parameters();
		$params['hide_ineligible']   = $this->is_active( 'hide_ineligible' ) ? 'yes' : 'no';
		$locale                      = get_locale();
		$params['locale']            = $locale ? str_replace( '_', '-', substr( $locale, 0, 5 ) ) : 'auto';

		return $params;
	}

	public function get_supported_locales() {
		return apply_filters( 'wc_stripe_afterpay_supported_locales', array( 'en-US', 'en-CA', 'en-AU', 'en-NZ', 'en-GB', 'fr-FR', 'it-IT', 'es-ES' ) );
	}

	public function get_element_options( $options = array() ) {
		$locale = get_locale();
		$locale = wc_stripe_get_site_locale();
		if ( ! in_array( $locale, $this->get_supported_locales() ) ) {
			$locale = 'auto';
		}
		$options['locale'] = $locale;

		return parent::get_element_options( $options ); // TODO: Change the autogenerated stub
	}

	public function get_afterpay_message_options( $context = 'checkout' ) {
		$options = array(
			'logoType'         => 'badge',
			'badgeTheme'       => $this->get_option( "icon_{$context}" ),
			'lockupTheme'      => 'black',
			'introText'        => $this->get_option( "intro_text_{$context}" ),
			'showInterestFree' => $this->is_active( "show_interest_free_{$context}" ),
			'modalTheme'       => $this->get_option( "modal_theme_{$context}" ),
			'modalLinkStyle'   => $this->get_option( "modal_link_style_{$context}" )
		);
		if ( in_array( $context, array( 'cart', 'checkout' ) ) ) {
			$options['isEligible'] = WC()->cart && WC()->cart->needs_shipping();
		} elseif ( $context === 'product' ) {
			global $product;
			if ( $product ) {
				$options['isEligible'] = $product->needs_shipping();
			}
		}

		return apply_filters( 'wc_stripe_afterpay_message_options', $options, $context, $this );
	}

	public function get_title() {
		// override because design guidelines state the title should consist of the
		// Afterpay pay in 4 text
		if ( is_checkout() ) {
			$this->title = '';
		}

		return parent::get_title();
	}

	protected function get_payment_description() {
		$desc = parent::get_payment_description();
		if ( ( $country = stripe_wc()->account_settings->get_option( 'country' ) ) ) {
			$params = $this->get_required_parameters();
			// get currency for country
			foreach ( $params as $currency => $param ) {
				if ( $param[0] === $country ) {
					$desc = sprintf( __( 'Store currency must be %s for Afterpay to show because your Stripe account is registered in %s. This is a requirement of Afterpay.',
						'woo-stripe-payment' ),
						$currency,
						$country );
					break;
				}
			}
		}

		return $desc;
	}

	public function enqueue_mini_cart_scripts( $scripts ) {
		if ( ! wp_script_is( $scripts->get_handle( 'mini-cart' ) ) ) {
			$scripts->enqueue_script( 'mini-cart',
				$scripts->assets_url( 'js/frontend/mini-cart.js' ),
				apply_filters( 'wc_stripe_mini_cart_dependencies', array( $scripts->get_handle( 'wc-stripe' ) ), $scripts ) );
		}
		$scripts->localize_script( 'mini-cart', $this->get_localized_params( 'cart' ), 'wc_' . $this->id . '_mini_cart_params' );
	}

}