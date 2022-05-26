<?php

/**
 * @since 3.3.13
 */
class WC_Stripe_Utils {

	/**
	 * @param WC_Order $data
	 */
	public static function display_fee( $order ) {
		return self::display_amount( 'fee', $order );
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	public static function display_net( $order ) {
		return self::display_amount( 'net', $order );
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	private static function display_amount( $type, $order ) {
		$payment_balance = self::get_payment_balance( $order );
		if ( $payment_balance && isset( $payment_balance->{$type}, $payment_balance->currency ) && is_numeric( $payment_balance->{$type} ) ) {
			if ( $type === 'fee' ) {
				$amount = - 1 * $payment_balance->fee;
			} else {
				$amount = $payment_balance->net;
			}

			return wc_price( $amount, array( 'currency' => $payment_balance->currency ) );
		}

		return '';
	}

	/**
	 * @param \Stripe\Charge $charge
	 * @param \WC_Order      $order
	 * @param bool           $save
	 */
	public static function add_balance_transaction_to_order( $charge, $order, $save = false ) {
		if ( isset( $charge->balance_transaction ) && is_object( $charge->balance_transaction ) ) {
			$display_order_currency = stripe_wc()->advanced_settings->is_display_order_currency();
			$balance_transaction    = $charge->balance_transaction;
			$exchange_rate          = $balance_transaction->exchange_rate === null ? 1 : $balance_transaction->exchange_rate;
			$amount_refunded        = $display_order_currency ? $charge->amount_refunded : $charge->amount_refunded * $exchange_rate;
			// the balance_transaction_net already has the fee deducted from it.
			$net                       = $display_order_currency ? $balance_transaction->net / $exchange_rate : $balance_transaction->net;
			$net                       = wc_format_decimal( $net - $amount_refunded, 4 );
			$fee                       = $display_order_currency ? wc_format_decimal( $balance_transaction->fee / $exchange_rate, 4 ) : $balance_transaction->fee;
			$currency                  = $display_order_currency ? $order->get_currency() : strtoupper( $balance_transaction->currency );
			$payment_balance           = new WC_Stripe_Payment_Balance( $order );
			$payment_balance->currency = $currency;
			$payment_balance->fee      = wc_stripe_remove_number_precision( $fee, $currency, true, 4 );
			$payment_balance->net      = wc_stripe_remove_number_precision( $net, $currency, true, 4 );
			if ( $charge->refunds->count() > 0 ) {
				foreach ( $charge->refunds->data as $refund ) {
					/**
					 * @var \Stripe\Refund $refund
					 */
					if ( is_object( $refund->balance_transaction ) ) {
						self::update_balance_transaction( $refund->balance_transaction, $order, false, $payment_balance );
					}
				}
			}
			$payment_balance->update_meta_data( $save );
		}
	}

	/**
	 * @param \Stripe\BalanceTransaction $balance_transaction
	 * @param \WC_Order                  $order
	 */
	public static function update_balance_transaction( $balance_transaction, $order, $save = false, $payment_balance = null ) {
		if ( $balance_transaction->reporting_category === 'partial_capture_reversal' ) {
			$payment_balance = $payment_balance ? $payment_balance : self::get_payment_balance( $order );
			if ( $payment_balance ) {
				$exchange_rate          = $balance_transaction->exchange_rate === null ? 1 : $balance_transaction->exchange_rate;
				$display_order_currency = stripe_wc()->advanced_settings->is_display_order_currency() && $payment_balance->currency !== strtoupper( $balance_transaction->currency );
				$currency               = $display_order_currency ? $order->get_currency() : strtoupper( $balance_transaction->currency );
				// fee is negative here since it's a reversal, that's why for net we subtract and for fee we add.
				$fee                  = $display_order_currency ? $balance_transaction->fee / $exchange_rate : $balance_transaction->fee;
				$fee                  = wc_stripe_remove_number_precision( $fee, $currency, true, 4 );
				$payment_balance->net = $payment_balance->net - $fee;
				$payment_balance->fee = $payment_balance->fee + $fee;
				$payment_balance->update_meta_data( $save );
			}
		}
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return \WC_Stripe_Payment_Balance|null
	 */
	private static function get_payment_balance( $order ) {
		return new WC_Stripe_Payment_Balance( $order );
	}

	/**
	 * @param $value
	 *
	 * @since 3.3.14
	 * @return string
	 */
	public static function sanitize_statement_descriptor( $value ) {
		return trim( str_replace( array( '<', '>', '\\', '\'', '"', '*' ), '', $value ) );
	}

	/**
	 * Sanitizes intent data before it's stored.
	 *
	 * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent
	 */
	public static function sanitize_intent( $intent ) {
		unset( $intent->client_secret );

		return $intent;
	}

}