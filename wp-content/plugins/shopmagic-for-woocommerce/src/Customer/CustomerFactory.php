<?php

namespace WPDesk\ShopMagic\Customer;

use WPDesk\ShopMagic\Exception\CannotCreateGuestException;
use WPDesk\ShopMagic\Guest\Guest;
use WPDesk\ShopMagic\Guest\GuestDAO;

/**
 * Can create customer.
 *
 * @TODO: refactor to use database abstraction in 3.0
 *
 * @package WPDesk\ShopMagic\Customer
 */
final class CustomerFactory {
	const GUEST_ID_PREFIX = 'g_';

	/**
	 * @param \WP_User $user
	 *
	 * @return Customer
	 */
	public function create_from_user( \WP_User $user ) {
		return new UserAsCustomer( $user );
	}

	/**
	 * @param int|string $id For guest id can be string
	 *
	 * @return Customer
	 * @throws CannotCreateGuestException|\Exception
	 */
	public function create_from_id( $id ) {
		global $wpdb;
		if ( ! self::is_customer_guest_id( $id ) ) {
			return $this->create_from_user( new \WP_User( $id ) );
		}
		$guest_repository = new GuestDAO( $wpdb );

		$numeric_id = self::convert_customer_guest_id_to_number( $id );

		return $this->create_from_guest( $guest_repository->get_by_id( $numeric_id ) );
	}

	/**
	 * @param string $id
	 *
	 * @return int
	 */
	public static function convert_customer_guest_id_to_number( string $id ): int {
		return (int) str_replace( self::GUEST_ID_PREFIX, '', $id );
	}

	/**
	 * @param int $id
	 *
	 * @return bool
	 */
	public static function is_customer_guest_id( $id ) {
		return ! is_numeric( $id );
	}

	/**
	 * @param \WP_User $user
	 * @param \WC_Order $order
	 *
	 * @return Customer
	 */
	public function create_from_user_and_order( \WP_User $user, \WC_Order $order ) {
		return new UserInOrderContextAsCustomer( $user, $order );
	}

	/**
	 * @param Guest $guest
	 *
	 * @return Customer
	 */
	public function create_from_guest( Guest $guest ) {
		return new CustomerDTO(
			true,
			self::GUEST_ID_PREFIX . $guest->get_id(),
			$guest->get_meta_value( 'username' ),
			$guest->get_meta_value( 'first_name' ),
			$guest->get_meta_value( 'last_name' ),
			$guest->get_meta_value( 'first_name' ) . ' ' . $guest->get_meta_value( 'last_name' ),
			$guest->get_email(),
			$guest->get_meta_value( 'billing_phone' ),
			$guest->get_meta_value( Customer::USER_LANGUAGE_META )
		);
	}

	public function create_from_guest_id( int $id ): Customer {
		global $wpdb;
		$guest_repository = new GuestDAO( $wpdb );

		return $this->create_from_guest( $guest_repository->get_by_id( $id ) );
	}

	/**
	 * Creates a null client.
	 *
	 * @return Customer
	 */
	public function create_null() {
		return new CustomerDTO( true, null, '', '', '', '', '', '', '' );
	}
}
