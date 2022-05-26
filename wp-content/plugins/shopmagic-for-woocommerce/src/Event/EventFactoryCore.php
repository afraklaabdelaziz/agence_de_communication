<?php

namespace WPDesk\ShopMagic\Event;

use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Event\Builtin\CustomerAccountCreated;
use WPDesk\ShopMagic\Event\Builtin\CustomerOptedIn;
use WPDesk\ShopMagic\Event\Builtin\CustomerOptedOut;
use WPDesk\ShopMagic\Event\Builtin\OrderCancelled;
use WPDesk\ShopMagic\Event\Builtin\OrderCompleted;
use WPDesk\ShopMagic\Event\Builtin\OrderFailed;
use WPDesk\ShopMagic\Event\Builtin\OrderNew;
use WPDesk\ShopMagic\Event\Builtin\OrderNoteAdded;
use WPDesk\ShopMagic\Event\Builtin\OrderOnHold;
use WPDesk\ShopMagic\Event\Builtin\OrderPaid;
use WPDesk\ShopMagic\Event\Builtin\OrderPending;
use WPDesk\ShopMagic\Event\Builtin\OrderProcessing;
use WPDesk\ShopMagic\Event\Builtin\OrderRefunded;
use WPDesk\ShopMagic\Event\Builtin\OrderStatusChanged;
use WPDesk\ShopMagic\Event\DeferredStateCheck\OrderStatusDeferredEvent;
use WPDesk\ShopMagic\Event\Imitation\CartAdEvent;
use WPDesk\ShopMagic\Event\Imitation\MembershipsProEvent;
use WPDesk\ShopMagic\Event\Imitation\SubscriptionProEvent;
use WPDesk\ShopMagic\Event\Imitation\ManualTriggerProEvent;
use WPDesk\ShopMagic\Filter\FilterLogic;
use WPDesk\ShopMagic\Helper\WordPressPluggableHelper;

/**
 * Manage events creation in runtime.
 *
 * @package WPDesk\ShopMagic\Event
 */
class EventFactoryCore implements EventFactory2 {

	/** @var EventMutex */
	private $event_mutex;

	/** @var bool */
	private $is_pro_active;

	/** @var Event[] */
	private static $events_cache;

	public function __construct( EventMutex $event_mutex, bool $is_pro_active ) {
		$this->event_mutex   = $event_mutex;
		$this->is_pro_active = $is_pro_active;
	}

	public function create_event( string $slug, Automation $automation = null, FilterLogic $filters = null ): Event {
		return clone $this->get_event( $slug );
	}

	/**
	 * @return Event[]
	 */
	public function get_event_list(): array {
		if ( empty( self::$events_cache ) ) {
			self::$events_cache = apply_filters( 'shopmagic/core/events', array_merge( $this->get_builtin_events(), $this->get_not_built_in_events() ) );
		}

		return self::$events_cache;
	}

	/** @return Event[] */
	private function get_not_built_in_events(): array {
		$events = [];

		if ( ! $this->is_pro_active && WordPressPluggableHelper::is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			$events['subscription_status_changed'] = new SubscriptionProEvent();
		}

		if ( ! $this->is_pro_active && WordPressPluggableHelper::is_plugin_active( 'woocommerce-memberships/woocommerce-memberships.php' ) ) {
			$events['membership_status_changed'] = new MembershipsProEvent();
		}

		if ( ! WordPressPluggableHelper::is_plugin_active( 'shopmagic-abandoned-carts/shopmagic-abandoned-carts.php' ) ) {
			$events['cart_abandoned_event'] = new CartAdEvent();
		}

		if ( ! $this->is_pro_active && ! WordPressPluggableHelper::is_plugin_active( 'shopmagic-manual-actions/shopmagic-manual-actions.php' ) ) {
			$events['shopmagic_order_manual_trigger'] = new ManualTriggerProEvent();
		}

		return $events;
	}

	/**
	 * @param string $group_id
	 *
	 * @return string
	 */
	public function event_group_name( string $group_id ): string {
		$groups = apply_filters(
			'shopmagic/core/groups',
			[
				self::GROUP_ORDERS       => __( 'Orders', 'shopmagic-for-woocommerce' ),
				self::GROUP_USERS        => __( 'Customers', 'shopmagic-for-woocommerce' ),
				self::GROUP_SUBSCRIPTION => __( 'Subscriptions', 'shopmagic-for-woocommerce' ),
				self::GROUP_CARTS        => __( 'Carts', 'shopmagic-for-woocommerce' ),
				self::GROUP_MEMBERSHIPS  => __( 'Memberships', 'shopmagic-for-woocommerce' ),
				self::GROUP_PRO          => __( 'PRO', 'shopmagic-for-woocommerce' ),
				self::GROUP_FORMS        => __( 'Forms', 'shopmagic-for-woocommerce' ),
				self::GROUP_AUTOMATION   => __( 'Automation', 'shopmagic-for-woocommerce' ),
			]
		);

		return $groups[ $group_id ] ?: '';
	}

	public function get_event( string $slug ): Event {
		$events = $this->get_event_list();
		if ( isset( $events[ $slug ] ) ) {
			return apply_filters( 'shopmagic/core/single_event', $events[ $slug ] );
		}

		return new NullEvent();
	}

	/**
	 * @return Event[]
	 */
	private function get_builtin_events(): array {
		return [
			'shopmagic_order_new_event'        => new OrderNew( $this->event_mutex ),
			'shopmagic_order_pending_event'    => new OrderStatusDeferredEvent( new OrderPending( $this->event_mutex ), 'pending' ),
			'shopmagic_order_processing_event' => new OrderStatusDeferredEvent( new OrderProcessing(), 'processing' ),
			'shopmagic_order_cancelled_event'  => new OrderStatusDeferredEvent( new OrderCancelled(), 'cancelled' ),
			'shopmagic_order_completed_event'  => new OrderStatusDeferredEvent( new OrderCompleted(), 'completed' ),
			'shopmagic_order_failed_event'     => new OrderStatusDeferredEvent( new OrderFailed(), 'failed' ),
			'shopmagic_order_on_hold_event'    => new OrderStatusDeferredEvent( new OrderOnHold(), 'on-hold' ),
			'shopmagic_order_refunded_event'   => new OrderStatusDeferredEvent( new OrderRefunded(), 'refunded' ),
			'shopmagic_order_status_changed'   => new OrderStatusChanged(),
			'shopmagic_order_status_paid'      => new OrderPaid(),
			'shopmagic_order_note_added'       => new OrderNoteAdded(),

			'shopmagic_new_account_event'      => new CustomerAccountCreated(),
			'shopmagic_customer_optin_event'   => new CustomerOptedIn(),
			'shopmagic_customer_optout_event'  => new CustomerOptedOut(),
		];
	}
}
