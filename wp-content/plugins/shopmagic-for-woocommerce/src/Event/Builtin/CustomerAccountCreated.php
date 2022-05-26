<?php

namespace WPDesk\ShopMagic\Event\Builtin;

use WPDesk\ShopMagic\Event\UserCommonEvent;

/**
 * Trigger when new user is created in WordPress.
 */
final class CustomerAccountCreated extends UserCommonEvent {
	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Customer Account Created', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return __( 'Triggered when new customer account gets created via WooCommerce', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function initialize() {
		add_action( 'user_register', [ $this, 'process_event' ], 10, 1 );
	}

	/**
	 * Save user id and run actions.
	 *
	 * @param int $user_id
	 *
	 * @return void
	 */
	public function process_event( $user_id ) {
		$this->user_id = $user_id;
		$this->run_actions();
	}
}
