<?php


namespace WPDesk\ShopMagic\Guest;

/**
 * Catches guests from newly created orders and puts them into our guest repository.
 *
 * @package WPDesk\ShopMagic\Application
 */
class GuestOrderIntegration {
	const PRIORITY_BEFORE_DEFAULT = - 100;

	public function hooks() {
		add_action( 'woocommerce_new_order', [ $this, 'catch_guest' ], self::PRIORITY_BEFORE_DEFAULT, 2 );
		add_action( 'woocommerce_api_create_order', [ $this, 'catch_guest' ], self::PRIORITY_BEFORE_DEFAULT, 2 );
	}

	/**
	 * @param int $order_id
	 * @param \WC_Abstract_Order $order
	 *
	 * @internal
	 */
	public function catch_guest( $order_id, $order = null ) {
		global $wpdb;
		$repository = new GuestDAO( $wpdb );
		$factory    = new GuestFactory( $repository );
		// some hooks do not provide an order.
		if ( ! $order instanceof \WC_Abstract_Order ) {
			$order = wc_get_order( $order_id );
		}
		if ( $factory->order_has_guest( $order ) ) {
			$guest = $factory->create_from_order_and_db( $order );
			$guest = $repository->save( $guest );
			$factory->touch_order( $order->get_id(), $guest->get_id() );
		}
	}
}
