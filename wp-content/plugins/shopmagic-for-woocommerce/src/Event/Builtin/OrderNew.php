<?php

namespace WPDesk\ShopMagic\Event\Builtin;

use WPDesk\ShopMagic\Event\EventMutex;
use WPDesk\ShopMagic\Event\OrderCommonEvent;

final class OrderNew extends OrderCommonEvent {

	/** @var EventMutex */
	private $event_mutex;

	public function __construct( EventMutex $event_mutex ) {
		$this->event_mutex = $event_mutex;
	}

	public function get_name(): string {
		return __( 'New Order', 'shopmagic-for-woocommerce' );
	}

	public function get_description() {
		return __( 'Triggered when a new order is created', 'shopmagic-for-woocommerce' );
	}

	public function process_event( $order_id, $order ) {
		if ( $this->event_mutex->check_uniqueness_once( spl_object_hash( $this ), [ 'order_id' => $order_id ] ) ) {
			$this->order = $order;
			$this->run_actions();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function initialize() {
		add_action( 'woocommerce_new_order', [ $this, 'process_event' ], self::PRIORITY_AFTER_DEFAULT, 2 );
		add_action( 'woocommerce_api_create_order', [ $this, 'process_event' ], self::PRIORITY_AFTER_DEFAULT, 2 );
	}
}
