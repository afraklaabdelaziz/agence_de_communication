<?php

namespace WPDesk\ShopMagic\Customer;

/**
 * Wrapper for \WP_User with fallback to \WC_Order data.
 *
 * @package WPDesk\ShopMagic\Customer
 */
final class UserInOrderContextAsCustomer implements Customer2 {

	/** @var \WP_User */
	private $user;

	/** @var \WC_Order */
	private $order;

	/** @var Customer */
	private $user_customer;

	public function __construct( \WP_User $user, \WC_Order $order ) {
		$this->user          = $user;
		$this->order         = $order;
		$this->user_customer = new UserAsCustomer( $this->user );
	}

	/**
	 * @inheritDoc
	 */
	public function is_guest() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function get_id() {
		return $this->user_customer->get_id();
	}

	/**
	 * @inheritDoc
	 */
	public function get_username() {
		return $this->user_customer->get_username();
	}

	/**
	 * @inheritDoc
	 */
	public function get_first_name() {
		$fallback = (string) $this->order->get_billing_first_name();
		$value    = $this->user_customer->get_first_name();

		return ( ! empty( $value ) ? $value : $fallback );
	}

	/**
	 * @inheritDoc
	 */
	public function get_last_name() {
		$fallback = (string) $this->order->get_billing_last_name();
		$value    = $this->user_customer->get_last_name();

		return ( ! empty( $value ) ? $value : $fallback );
	}

	/**
	 * @inheritDoc
	 */
	public function get_full_name() {
		$fallback = $this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name();
		$value    = $this->user_customer->get_full_name();

		return ( ! empty( $value ) ? $value : $fallback );
	}

	/**
	 * @inheritDoc
	 */
	public function get_email() {
		$fallback = (string) $this->order->get_billing_email();
		$value    = $this->user_customer->get_email();

		return ( ! empty( $value ) ? $value : $fallback );
	}

	/**
	 * @inheritDoc
	 */
	public function get_phone() {
		$fallback = $this->order->get_billing_phone();
		$value    = $this->user_customer->get_phone();

		return ( ! empty( $value ) ? $value : $fallback );
	}

	public function get_language(): string {
		return get_user_meta( $this->user->ID, Customer::USER_LANGUAGE_META, true ) ?: '';
	}
}
