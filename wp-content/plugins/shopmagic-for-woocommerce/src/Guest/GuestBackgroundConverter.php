<?php

namespace WPDesk\ShopMagic\Guest;

use Psr\Log\LoggerInterface;
use WPDesk\ShopMagic\Database\Abstraction\DAO\Collection;
use WPDesk\ShopMagic\Database\DatabaseSchema;
use WPDesk\ShopMagic\Exception\CannotCreateGuestException;
use WPDesk\ShopMagic\MarketingLists\DAO\ListDTO;
use WPDesk\ShopMagic\MarketingLists\DAO\ListFactory;
use WPDesk\ShopMagic\MarketingLists\DAO\ListTable;
use WPDesk\ShopMagic\Optin\EmailOptRepository;

/**
 * Converts various data in db extracting/injecting guests.
 */
class GuestBackgroundConverter {
	const SUBSCRIBER_CONVERTER_LIMIT = 10;

	/** @var \WC_Queue_Interface */
	private $queue_client;

	/** @var LoggerInterface */
	private $logger;

	/** @var bool */
	private $initialized = false;

	const PAGE_SIZE                 = self::SUBSCRIBER_CONVERTER_LIMIT;
	const HOOK_CONVERT_ORDER_PAGE   = 'shopmagic/core/guest/convert_order_page';
	const HOOK_CONVERT_OPTIN_PAGE   = 'shopmagic/core/guest/convert_optin_page';
	const HOOK_CONVERT_OUTCOME_PAGE = 'shopmagic/core/guest/convert_outcome_page';

	const CONVERSION_MUTEX_OPTION_NAME = 'shopmagic_guest_conversion';

	public function __construct( \WC_Queue_Interface $queue_client, LoggerInterface $logger ) {
		$this->queue_client = $queue_client;
		$this->logger       = $logger;
	}

	/**
	 * Converts page of orders to guests.
	 *
	 * @param int $page
	 *
	 * @internal
	 */
	public function convert_optins_and_queue_next( $page ) {
		global $wpdb;
		try {
			$guest_repository  = new GuestDAO( $wpdb );
			$guest_factory     = new GuestFactory( $guest_repository );
			$subscription_list = new ListTable( new ListFactory() );

			$subscribers = $subscription_list->get_all( [ 'active' => '1' ], [], max( 0, $page - 1 ) * self::SUBSCRIBER_CONVERTER_LIMIT, self::SUBSCRIBER_CONVERTER_LIMIT );
			/** @var ListDTO $subscriber */
			foreach ( $subscribers as $subscriber ) {
				$this->logger->debug( "Guest extraction from email: {$subscriber->get_email()}" );
				if ( ! get_user_by( 'email', $subscriber->get_email() ) ) {
					$guest = $guest_factory->create_from_email_and_db( $subscriber->get_email() );
					if ( ! $guest->is_saved() ) {
						$guest_repository->save( $guest );
					}
				}
			}

			if ( $subscribers->count() > 0 ) {
				$this->queue_client->add(
					self::HOOK_CONVERT_OPTIN_PAGE,
					[
						'page' => $page + 1,
					]
				);
			}
		} catch ( \Throwable $e ) {
			$this->logger->error(
				'Error while converting optin to guest',
				[
					'exception' => $e,
					'guest'     => $guest,
				]
			);
		}
	}

	/**
	 * Converts page of orders to guests.
	 *
	 * @param int $page
	 *
	 * @internal
	 */
	public function convert_orders_and_queue_next( $page ) {
		global $wpdb;
		try {
			$guest_repository = new GuestDAO( $wpdb );
			$guest_factory    = new GuestFactory( $guest_repository );
			$orders           = wc_get_orders(
				[
					'posts_per_page' => self::PAGE_SIZE,
					'page'           => $page,
					'paginate'       => true,
					'order'          => 'ASC',
					'orderby'        => 'date',
				]
			);
			foreach ( $orders->orders as $order ) {
				/** @var $order \WC_Abstract_Order */
				$this->logger->debug( "Guest extraction from order: {$order->get_id()}" );
				if ( $guest_factory->order_has_guest( $order ) ) {
					$guest = $guest_factory->create_from_order_and_db( $order );
					$guest = $guest_repository->save( $guest );
					$guest_factory->touch_order( $order->get_id(), $guest->get_id() );
				}
			}

			if ( count( $orders->orders ) > 0 ) {
				$this->queue_client->add(
					self::HOOK_CONVERT_ORDER_PAGE,
					[
						'page' => $page + 1,
					]
				);
			} else { // after all orders we can convert outcomes and optins.
				$this->queue_client->add(
					self::HOOK_CONVERT_OUTCOME_PAGE,
					[
						'page' => 0,
					]
				);
				$this->queue_client->add(
					self::HOOK_CONVERT_OPTIN_PAGE,
					[
						'page' => 0,
					]
				);
			}
		} catch ( \Throwable $e ) {
			$this->logger->error(
				'Error while converting order to guest',
				[
					'exception' => $e,
					'order_id'  => $order instanceof \WC_Abstract_Order ? $order->get_id() : null,
					'guest_id'  => $guest instanceof Guest ? $guest->get_id() : null,
					'stack'     => $e->getTraceAsString(),
				]
			);
		}
	}

	/**
	 * Converts page of outcomes injecting guests.
	 *
	 * @param int $page
	 *
	 * @internal
	 */
	public function convert_outcomes_and_queue_next( $page ) {
		global $wpdb;
		try {
			$guest_repository = new GuestDAO( $wpdb );
			$outcomes_table   = DatabaseSchema::get_automation_outcome_table_name();

			$limit    = self::PAGE_SIZE;
			$offset   = max( 0, $page - 1 ) * self::PAGE_SIZE;
			$outcomes = $wpdb->get_results( "SELECT id, customer_email FROM {$outcomes_table} WHERE customer_id is NULL LIMIT {$limit} OFFSET {$offset}" );
			foreach ( $outcomes as $outcome_data ) {
				try {
					$guest = $guest_repository->get_by_email( $outcome_data->customer_email );
					$wpdb->update( $outcomes_table, [ 'guest_id' => $guest->get_id() ], [ 'id' => $outcome_data->id ] );
				} catch ( CannotCreateGuestException $e ) {
					$guest_factory = new GuestFactory( $guest_repository );
					$guest         = $guest_factory->create_from_email_and_db( $outcome_data->customer_email );
					if ( ! $guest->is_saved() ) {
						$guest = $guest_repository->save( $guest );
					}
					$wpdb->update( $outcomes_table, [ 'guest_id' => $guest->get_id() ], [ 'id' => $outcome_data->id ] );
				}
			}

			if ( count( $outcomes ) > 0 ) {
				$this->queue_client->add(
					self::HOOK_CONVERT_OUTCOME_PAGE,
					[
						'page' => $page + 1,
					]
				);
			}
		} catch ( \Throwable $e ) {
			$this->logger->error(
				'Error while injectings guests to outcomes',
				[
					'exception' => $e,
				]
			);
		}
	}

	/**
	 * Extract guests from orders and optins if run first time.
	 */
	public function start_guest_extraction_if_needed() {
		if ( ! $this->initialized ) {
			$this->logger->warning( self::class . ' must be initialized first.' );

			return;
		}
		$option_key_started = self::CONVERSION_MUTEX_OPTION_NAME;
		$time               = microtime( true );
		if ( ! get_option( $option_key_started ) ) {
			update_option( $option_key_started, $time, true );
			if ( get_option( $option_key_started ) === $time ) {
				$this->ensure_guest_tracking_key();
				$this->queue_client->add(
					self::HOOK_CONVERT_ORDER_PAGE,
					[
						'page' => 0,
					]
				);
			}
		}
	}

	private function ensure_guest_tracking_key() {
		global $wpdb;
		$table = DatabaseSchema::get_guest_table_name();
		$wpdb->query( "UPDATE {$table} SET trackig_key = MD5(concat(id, email))" );
	}

	/**
	 * Initialize the class so it can work properly.
	 *
	 * @return $this
	 */
	public function initialize() {
		$this->initialized = true;
		add_action( self::HOOK_CONVERT_ORDER_PAGE, [ $this, 'convert_orders_and_queue_next' ] );
		add_action( self::HOOK_CONVERT_OPTIN_PAGE, [ $this, 'convert_optins_and_queue_next' ] );
		add_action( self::HOOK_CONVERT_OUTCOME_PAGE, [ $this, 'convert_outcomes_and_queue_next' ] );

		return $this;
	}
}
