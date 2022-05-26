<?php


namespace PaymentPlugins\CartFlows\Stripe;


class Main {

	public static function init() {
		if ( self::cartflows_enabled() ) {
			new PaymentsApi();
			new RoutesApi();
		}
	}

	public static function cartflows_enabled() {
		return defined( 'CARTFLOWS_PRO_FILE' );
	}
}