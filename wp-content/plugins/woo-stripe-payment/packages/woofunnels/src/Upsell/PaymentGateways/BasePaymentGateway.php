<?php


namespace PaymentPlugins\WooFunnels\Stripe\Upsell\PaymentGateways;

/**
 * Class BaseGateway
 * @package PaymentPlugins\WooFunnels\Stripe\Upsell\PaymentGateways
 */
class BasePaymentGateway extends \WFOCU_Gateway {

	public $refund_supported = true;

	/**
	 * @var \WFOCU_Logger
	 */
	private $logger;

	private $client;

	private $payment;

	public function __construct( \WC_Stripe_Gateway $client, \WC_Stripe_Payment $payment, \WFOCU_Logger $logger ) {
		$this->client  = $client;
		$this->payment = $payment;
		$this->logger  = $logger;
		$this->initialize();
	}

	public static function get_instance() {
		static $instance;
		if ( ! $instance ) {
			$instance = new static( \WC_Stripe_Gateway::load(), new \WC_Stripe_Payment_Intent( null, null ), WFOCU_Core()->log );
		}

		return $instance;
	}

	public function initialize() {
	}

	/**
	 * @param \WC_Order $order
	 *
	 * @return false|true|void
	 */
	public function process_charge( $order ) {
		$this->handle_client_error();
		$intent = isset( $_POST['_payment_intent'] ) ? $_POST['_payment_intent'] : null;
		// check if payment intent exists.
		if ( $intent ) {
			$intent = $this->client->paymentIntents->retrieve( $intent );
		} else {
			// If there is no customer ID, create one
			$customer_id = $order->get_meta( \WC_Stripe_Constants::CUSTOMER_ID );
			if ( ! $customer_id && ! is_user_logged_in() ) {
				$this->create_stripe_customer( WC()->customer, $order );
			} elseif ( is_user_logged_in() ) {
				$order->update_meta_data( \WC_Stripe_Constants::CUSTOMER_ID, wc_stripe_get_customer_id( $order->get_customer_id() ) );
				$order->save();
			}
			// create the payment intent
			$intent = $this->create_payment_intent( $order );
		}
		if ( $intent->status === \WC_Stripe_Constants::REQUIRES_PAYMENT_METHOD ) {
			$intent = $this->client->paymentIntents->update( $intent->id, [ 'payment_method' => $order->get_meta( \WC_Stripe_Constants::PAYMENT_METHOD_TOKEN ) ] );
			if ( is_wp_error( $intent ) ) {
				throw new \WFOCU_Payment_Gateway_Exception( $intent->get_error_message() );
			}
		}
		if ( $intent->status === \WC_Stripe_Constants::REQUIRES_CONFIRMATION ) {
			$intent = $this->client->paymentIntents->confirm( $intent->id );
			if ( is_wp_error( $intent ) ) {
				throw new \WFOCU_Payment_Gateway_Exception( $intent->get_error_message() );
			}
		}
		if ( $intent->status === \WC_Stripe_Constants::REQUIRES_ACTION ) {
			// send back response
			return \wp_send_json( [
				'success' => true,
				'data'    => [ 'redirect_url' => $this->get_payment_intent_redirect_url( $intent ) ]
			] );
		}
		WFOCU_Core()->data->set( '_transaction_id', $intent->charges->data[0]->id );

		return $this->handle_result( true );
	}

	/**
	 * @param \WC_Order $order
	 *
	 * @return bool
	 */
	public function process_refund_offer( $order ) {
		$charge = isset( $_POST['txn_id'] ) ? $_POST['txn_id'] : false;
		$amount = isset( $_POST['amt'] ) ? round( $_POST['amt'], 2 ) : false;
		$mode   = wc_stripe_order_mode( $order );
		$result = $this->client->refunds->mode( $mode )->create( [
			'charge'   => $charge,
			'amount'   => wc_stripe_add_number_precision( $amount, $order->get_currency() ),
			'metadata' => array(
				'order_id'    => $order->get_id(),
				'created_via' => 'woocommerce'
			)
		] );
		if ( is_wp_error( $result ) ) {
			$this->logger->log( sprintf( 'Error refunding charge %s. Reason: %s', $charge, $result->get_error_message() ) );

			return false;
		} else {
			$this->logger->log( sprintf( 'Charge %s refunded n Stripe.', $charge ) );
		}

		return $result->id;
	}

	public function get_transaction_link( $transaction_id, $order_id ) {
		$order = wc_get_order( $order_id );
		$mode  = wc_stripe_order_mode( $order );
		$url   = 'https://dashboard.stripe.com/payments/%s';
		if ( $mode === 'test' ) {
			$url = 'https://dashboard.stripe.com/test/payments/%s';
		}

		return sprintf( $url, $transaction_id );
	}

	public function handle_client_error() {
		$package = WFOCU_Core()->data->get( '_upsell_package' );
		if ( $package && isset( $package['_client_error'] ) ) {
			$this->logger->log( sprintf( 'Stripe client error: %s', sanitize_text_field( $package['_client_error'] ) ) );
		}
	}

	/**
	 * @param \WC_Customer $customer
	 *
	 * @throws \WFOCU_Payment_Gateway_Exception
	 */
	private function create_stripe_customer( \WC_Customer $customer, \WC_Order $order ) {
		$result = \WC_Stripe_Customer_Manager::instance()->create_customer( $customer );
		if ( ! is_wp_error( $result ) ) {
			$order->update_meta_data( \WC_Stripe_Constants::CUSTOMER_ID, $result->id );
			$order->save();

			// now that we have a customer created, attach the payment method
			$payment_method = $order->get_meta( \WC_Stripe_Constants::PAYMENT_METHOD_TOKEN );

			return $this->client->paymentMethods->attach( $payment_method, [ 'customer' => $result->id ] );
		}

		throw new \WFOCU_Payment_Gateway_Exception( $result->get_error_message() );
	}

	private function create_payment_intent( \WC_Order $order ) {
		$package        = WFOCU_Core()->data->get( '_upsell_package' );
		$payment_method = $this->get_wc_gateway();
		$params         = array(
			'amount'               => wc_stripe_add_number_precision( $package['total'], $order->get_currency() ),
			'description'          => sprintf( __( '%1$s - Order %2$s - One Time offer', 'woo-stripe-payment' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $order->get_order_number() ),
			'payment_method'       => $order->get_meta( \WC_Stripe_Constants::PAYMENT_METHOD_TOKEN ),
			'confirmation_method'  => 'automatic', //$payment_method->get_confirmation_method( $order ),
			'capture_method'       => $payment_method->get_option( 'charge_type' ) === 'capture' ? 'automatic' : 'manual',
			'confirm'              => false,
			'payment_method_types' => [ $payment_method->get_payment_method_type() ],
			'customer'             => $order->get_meta( \WC_Stripe_Constants::CUSTOMER_ID )
		);
		$this->payment->add_order_metadata( $params, $order );
		$this->payment->add_order_currency( $params, $order );
		$this->payment->add_order_shipping_address( $params, $order );

		$result = $this->client->paymentIntents->mode( wc_stripe_order_mode( $order ) )->create( $params );
		if ( is_wp_error( $result ) ) {
			throw new \WFOCU_Payment_Gateway_Exception( $result->get_error_message() );
		}

		return $result;
	}

	public function has_token( $order ) {
		$payment_token = $order->get_meta( \WC_Stripe_Constants::PAYMENT_METHOD_TOKEN );

		return ! empty( $payment_token );
	}

	/**
	 * @param \Stripe\PaymentIntent $intent
	 *
	 * @return string
	 */
	protected function get_payment_intent_redirect_url( \Stripe\PaymentIntent $intent ) {
		return sprintf( '#response=%s', rawurlencode( base64_encode(
			wp_json_encode( [
					'payment_intent' => $intent->id,
					'client_secret'  => $intent->client_secret
				]
			) ) ) );
	}
}