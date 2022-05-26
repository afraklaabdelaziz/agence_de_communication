<?php

namespace WPDesk\ShopMagic\Event;

use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Exception\ReferenceNoLongerAvailableException;

abstract class OrderCommonEvent extends BasicEvent {
	const PRIORITY_AFTER_DEFAULT = 100;

	/** @var \WC_Order|\WC_Order_Refund */
	protected $order;

	public function get_group_slug(): string {
		return EventFactory2::GROUP_ORDERS;
	}

	public function get_provided_data_domains() {
		return array_merge(
			parent::get_provided_data_domains(),
			[ \WC_Order::class, \WP_User::class ]
		);
	}

	public function get_provided_data() {
		return array_merge(
			parent::get_provided_data(),
			[
			\WC_Order::class => $this->get_order(),
			\WP_User::class  => $this->get_user(),
		]);
	}

	/**
	 * @param $order_id
	 * @param \WC_Order|\WC_Order_Refund|\WC_Abstract_Order $order
	 *
	 * @internal
	 */
	public function process_event( $order_id, $order ) {
		$this->order = $order;
		$this->run_actions();
	}

	/**
	 * Returns the order objects, associated with an event
	 *
	 * @return \WC_Order|\WC_Order_Refund
	 */
	protected function get_order() {
		return $this->order;
	}

	/**
	 * Returns the user objects, associated with an event
	 *
	 * @return \WP_User
	 *
	 * @deprecated Use Customer instead.
	 * @codeCoverageIgnore
	 */
	protected function get_user() {
		return $this->get_order()->get_user();
	}

	/**
	 * @return array Normalized event data required for Queue serialization.
	 */
	public function jsonSerialize() {
		return array_merge(
			parent::jsonSerialize(),
			[
				'order_id' => $this->get_order()->get_id(),
			]
		);
	}

	/**
	 * @param array $serializedJson @see jsonSerialize
	 *
	 * @throws ReferenceNoLongerAvailableException When serialized object reference is no longer valid. ie. order no loger exists.
	 * phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
	 */
	public function set_from_json( array $serializedJson ) {
		parent::set_from_json( $serializedJson );
		$this->order = wc_get_order( $serializedJson['order_id'] );
		if ( ! $this->order instanceof \WC_Order && ! $this->order instanceof \WC_Order_Refund ) {
			// translators: %d: ID of an order.
			throw new ReferenceNoLongerAvailableException( sprintf( __( 'Order %d no longer exists.', 'shopmagic-for-woocommerce' ), $serializedJson['order_id'] ) );
		}
	}

	// phpcs:enable
}
