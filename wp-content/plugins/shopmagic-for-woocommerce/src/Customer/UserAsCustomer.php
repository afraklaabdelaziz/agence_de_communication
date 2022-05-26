<?php

namespace WPDesk\ShopMagic\Customer;

/**
 * Wrapper for \WP_User
 *
 * @package WPDesk\ShopMagic\Customer
 */
final class UserAsCustomer implements Customer2 {

	/** @var \WP_User */
	private $user;

	public function __construct( \WP_User $user ) {
		$this->user = $user;
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
		return (string) $this->user->ID;
	}

	/**
	 * @inheritDoc
	 */
	public function get_username() {
		return $this->user->user_login;
	}

	/**
	 * @inheritDoc
	 */
	public function get_first_name() {
		return ( ! empty( $this->user->first_name ) ? $this->user->first_name : '' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_last_name() {
		return ( ! empty( $this->user->last_name ) ? $this->user->last_name : '' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_full_name() {
		return ( ! empty( $this->user->display_name ) ? $this->user->display_name : '' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_email() {
		return ( ! empty( $this->user->user_email ) ? $this->user->user_email : '' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_phone() {
		$phone = get_user_meta( $this->user->ID, 'billing_phone', true );

		return ! empty( $phone ) ? $phone : '';
	}

	public function get_language(): string {
		return get_user_meta( $this->user->ID, Customer::USER_LANGUAGE_META, true ) ?: '';
	}
}
