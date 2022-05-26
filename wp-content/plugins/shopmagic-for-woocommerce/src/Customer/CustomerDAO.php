<?php

namespace WPDesk\ShopMagic\Customer;

use WP_User;
use WPDesk\ShopMagic\Exception\CannotCreateGuestException;
use WPDesk\ShopMagic\Exception\CustomerNotFound;
use WPDesk\ShopMagic\Guest\GuestDAO;

/**
 * Data access for Customer.
 *
 * @TODO: refactor to use database abstraction in 3.0
 *
 * @package WPDesk\ShopMagic\Persistence
 */
final class CustomerDAO {

	/** @var CustomerFactory */
	private $customer_factory;

	/**
	 * @param \wpdb                $deprecated
	 * @param ?CustomerFactory     $customer_factory
	 */
	public function __construct( \wpdb $deprecated = null, CustomerFactory $customer_factory = null ) {
		$this->customer_factory = $customer_factory ?? new CustomerFactory();
	}

	public function get_wpdb(): \wpdb {
		global $wpdb;

		return $wpdb;
	}

	/**
	 * @param string $email
	 *
	 * @since 2.37
	 */
	public function find_by_email( string $email ): Customer {
		$user = get_user_by( 'email', $email );
		if ( $user instanceof WP_User ) {
			return $this->customer_factory->create_from_user( $user );
		}

		$guest_dao = new GuestDAO( $this->get_wpdb() );
		try {
			return $this->customer_factory->create_from_guest( $guest_dao->get_by_email( $email ) );
		} catch ( CannotCreateGuestException $e ) {
			throw new CustomerNotFound( 'Couldn\'t find matching email in sites\' users and guests.' );
		}
	}

	/**
	 * @param string $phrase
	 * @param int $limit
	 *
	 * @return \Generator<Customer>
	 */
	public function search( $phrase, $limit = 20 ) {
		$query = new \WP_User_Query(
			[
				'search'         => '*' . esc_attr( $phrase ) . '*',
				'search_columns' => [ 'user_login', 'user_email', 'user_nicename', 'display_name' ],
				'fields'         => 'ID',
				'number'         => $limit,
			]
		);

		$query2 = new \WP_User_Query(
			[
				'fields'     => 'ID',
				'number'     => $limit,
				'meta_query' => [
					'relation' => 'OR',
					[
						'key'     => 'first_name',
						'value'   => $phrase,
						'compare' => 'LIKE',
					],
					[
						'key'     => 'last_name',
						'value'   => $phrase,
						'compare' => 'LIKE',
					],
				],
			]
		);

		foreach ( wp_parse_id_list( array_merge( $query->get_results(), $query2->get_results() ) ) as $id ) {
			yield $this->customer_factory->create_from_id( $id );
		}
		$guest_dao = new GuestDAO( $this->get_wpdb() );
		foreach ( $guest_dao->search( $phrase, $limit ) as $guest ) {
			yield $this->customer_factory->create_from_guest( $guest );
		}
	}

	/**
	 * @deprecated No longer needed.
	 */
	public function get_all_with_unique_emails(): \Generator {
		$emails  = [];
		$factory = new CustomerFactory();
		$page    = 1;

		do {
			$users_paged = get_users(
				[
					'paged'   => $page++,
					'number'  => 10,
					'orderby' => 'ID',
				]
			);
			foreach ( $users_paged as $user ) {
				if ( $user instanceof \WP_User ) {
					$emails[] = $user->user_email;
					yield $factory->create_from_user( $user );
				}
			}
		} while ( count( $users_paged ) > 0 );
		unset( $users_paged );

		$guest_dao = new GuestDAO( $this->get_wpdb() );
		foreach ( $guest_dao->get_all( [ 'id' => 'ASC' ], null ) as $guest ) {
			if ( ! in_array( $guest->get_email(), $emails ) ) {
				yield $factory->create_from_guest( $guest );
			}
		}
	}
}
