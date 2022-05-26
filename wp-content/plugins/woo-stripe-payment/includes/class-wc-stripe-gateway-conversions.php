<?php
defined( 'ABSPATH' ) || exit();

/**
 *
 * @since 3.1.0
 * @author Payment Plugins
 *
 */
class WC_Stripe_Gateway_Conversion {

	public static function init() {
		add_filter( 'woocommerce_order_get_payment_method', array( __CLASS__, 'convert_payment_method' ), 10, 2 );
		add_filter( 'woocommerce_subscription_get_payment_method', array( __CLASS__, 'convert_payment_method' ), 10, 2 );
	}

	/**
	 *
	 * @param string $payment_method
	 * @param WC_Order $order
	 */
	public static function convert_payment_method( $payment_method, $order ) {
		$old_payment_method = $payment_method;

		switch ( $payment_method ) {
			case 'stripe':
				// Another Stripe plugin is active, don't convert $payment_method as that could affect
				// checkout functionality.
				if ( did_action( 'woocommerce_checkout_order_processed' ) ) {
					return $payment_method;
				}
				$payment_method = 'stripe_cc';
				break;
		}
		// commented out in 3.2.10. Don't update the post_meta in case merchant switches back to other plugin.
		/*if ( $payment_method !== $old_payment_method && ! empty( $payment_method ) ) {
			update_post_meta( $order->get_id(), '_payment_method', $payment_method );
		}*/

		return $payment_method;
	}
}

WC_Stripe_Gateway_Conversion::init();
