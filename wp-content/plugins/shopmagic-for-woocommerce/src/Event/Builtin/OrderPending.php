<?php

namespace WPDesk\ShopMagic\Event\Builtin;

use WPDesk\ShopMagic\Event\EventMutex;
use WPDesk\ShopMagic\Event\OrderCommonEvent;

final class OrderPending extends OrderCommonEvent {
	const DEFERRED_CHECK_QUEUE_HOOK = 'shopmagic/core/queue/pending-deferred-check';
	const DEFERRED_RUN_HOOK         = 'shopmagic/core/event/order_pending/deferred_run';
	const QUEUE_GROUP_NAME          = 'shopmagic-automation-internal';

	/** @var \WC_Queue_Interface */
	private static $queue_client;

	/** @var EventMutex */
	private $event_mutex;

	public function __construct( EventMutex $event_mutex ) {
		$this->event_mutex = $event_mutex;
	}

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Order Pending', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return __( 'Triggered when order is pending', 'shopmagic-for-woocommerce' );
	}

	/**
	 * Check if newly created order still has pending status.
	 * Have to be executed as soon as possible.
	 */
	public static function initialize_pending_on_created_check( \WC_Queue_Interface $queue ) {
		self::$queue_client = $queue;

		// and if status was not changed during checkout run event.
		add_action(
			self::DEFERRED_CHECK_QUEUE_HOOK,
			function ( $order_id, $status_to_check ) {
				$run_event = function () use ( $order_id, $status_to_check ) {
					$order = wc_get_order( $order_id );
					if ( $status_to_check === $order->get_status() ) {
						do_action( self::DEFERRED_RUN_HOOK, $order_id, $order );
					}
				};

				if ( did_action( 'wp_loaded' ) ) {
					$run_event();
				} else {
					add_action( 'wp_loaded', $run_event );
				}
			},
			2,
			self::PRIORITY_AFTER_DEFAULT
		);
	}

	/**
	 * @inheritDoc
	 */
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
		add_action( 'woocommerce_order_status_pending', [ $this, 'process_event' ], 10, 2 );
		add_action( self::DEFERRED_RUN_HOOK, [ $this, 'process_event' ], 10, 2 );

		// check if order is created with a given status and this status is not immediately changed.
		add_action(
			'woocommerce_new_order',
			function ( $order_id, $order = null ) {
				if ( ! $order instanceof \WC_Abstract_Order ) {
					$order = wc_get_order( $order_id );
				}
				if ( $order instanceof \WC_Abstract_Order ) {
					$status_to_check   = 'pending';
					$is_pending_status = $order->get_status() === $status_to_check;

					// if status is pending add to queue to check later.
					if ( $is_pending_status && self::$queue_client instanceof \WC_Queue_Interface ) {
						if ( $this->event_mutex->check_uniqueness_once( self::class, [ 'order_id' => $order_id ] ) ) {
							self::$queue_client->add(
								self::DEFERRED_CHECK_QUEUE_HOOK,
								[
									$order_id,
									$status_to_check,
								],
								self::QUEUE_GROUP_NAME
							);
						}
					}
				}
			},
			2,
			self::PRIORITY_AFTER_DEFAULT
		);
	}
}
