<?php

namespace WPDesk\ShopMagic\Optin;

use WPDesk\ShopMagic\CommunicationList\CommunicationList;
use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Database\DatabaseSchema;
use WPDesk\ShopMagic\Database\RepositoryOrderingTrait;
use WPDesk\ShopMagic\Event\Builtin\CustomerOptedIn;
use WPDesk\ShopMagic\Event\Builtin\CustomerOptedOut;
use WPDesk\ShopMagic\Guest\GuestDAO;
use WPDesk\ShopMagic\Guest\GuestFactory;

/**
 * Repository access to opitin/optout table. Use to get/find objects and optionally to save.
 *
 * @package WPDesk\ShopMagic\Optin
 */
class EmailOptRepository {
	use RepositoryOrderingTrait;

	/** @var \wpdb */
	private $wpdb;

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * Deactive old optins so only the last one has an active flag set.
	 *
	 * @param string $email
	 * @param int $communization_type_id
	 * @param int $last_active_id
	 */
	private function deactivate_old( $email, $communization_type_id, $last_active_id ) {
		$query = $this->wpdb->prepare(
			"UPDATE {$this->get_table_name()} SET active = 0 WHERE email = %s AND communication_type = %d AND id <> %d ",
			$email,
			$communization_type_id,
			$last_active_id
		);
		$this->wpdb->query( $query );
	}

	/**
	 * @param string $email
	 * @param int $communization_type_id
	 */
	public function opt_in( $email, $communization_type_id ) {
		$optins = $this->find_by_email( $email );
		if ( ! $optins->is_opted_in( $communization_type_id ) ) {
			$this->wpdb->insert(
				$this->get_table_name(),
				[
					'email'              => $email,
					'communication_type' => $communization_type_id,
					'subscribe'          => '1',
					'created'            => gmdate( 'Y-m-d G:i:s' ),
				]
			);
			$this->deactivate_old( $email, $communization_type_id, $this->wpdb->insert_id );
			$customer = $this->convert_email_to_customer( $email );
			CustomerOptedIn::trigger(
				[
					$customer,
					$communization_type_id,
				]
			);
		}
	}

	private function convert_email_to_customer( string $email ): Customer {
		$customer_factory = new CustomerFactory();
		$user             = get_user_by( 'email', $email );
		if ( $user !== false ) {
			return $customer_factory->create_from_user( $user );
		}

		$guest_dao     = new GuestDAO( $this->wpdb );
		$guest_factory = new GuestFactory( $guest_dao );
		$guest         = $guest_factory->create_from_email_and_db( $email );
		if ( ! $guest->is_saved() ) {
			$guest_dao->save( $guest );
		}

		return $customer_factory->create_from_guest( $guest );
	}

	/**
	 * Soft opt in email to all given communication types.
	 *
	 * @param string $email
	 * @param CommunicationList[] $soft_types
	 */
	public function soft_opt_in( $email, array $soft_types ) {
		$optins = $this->find_by_email( $email );
		foreach ( $soft_types as $type ) {
			if ( ! $optins->is_opted_out( $type->get_id() ) && ! $optins->is_opted_in( $type->get_id() ) ) {
				$this->opt_in( $optins->get_email(), $type->get_id() );
			}
		}
	}

	/**
	 * @param string $email
	 * @param int $communization_type_id
	 */
	public function opt_out( $email, $communization_type_id ) {
		$optins = $this->find_by_email( $email );
		if ( $optins->is_opted_in( $communization_type_id ) ) {
			$this->wpdb->insert(
				$this->get_table_name(),
				[
					'email'              => $email,
					'communication_type' => $communization_type_id,
					'subscribe'          => '0',
					'created'            => gmdate( 'Y-m-d G:i:s' ),
				]
			);
			$this->deactivate_old( $email, $communization_type_id, $this->wpdb->insert_id );
			$customer = $this->convert_email_to_customer( $email );
			CustomerOptedOut::trigger(
				[
					$customer,
					$communization_type_id,
				]
			);
		}
	}

	private function get_table_name() {
		return DatabaseSchema::get_optin_email_table_name();
	}

	/**
	 * @param string $email
	 *
	 * @return EmailOptModel
	 */
	public function find_by_email( $email ) {
		$items = $this->get_all( [ 'p1.email' => $email ], [] );
		if ( count( $items ) === 0 ) {
			return new EmailOptModel( $email, [], [] );
		}

		return $items[0];
	}

	/**
	 * @param EmailOptModel $email
	 *
	 * @return EmailOptModel
	 */
	public function refresh( EmailOptModel $email ) {
		return $this->find_by_email( $email->get_email() );
	}

	/**
	 * @param array $where
	 * @param array $order
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return EmailOptModel[]
	 */
	public function get_all( array $where, array $order, $limit = 10, $offset = 0 ) {
		$table     = $this->get_table_name();
		$where_sql = $this->where_array_to_sql( $where );
		$order_sql = $this->order_array_to_sql( $order );
		$limit_sql = ''; // TODO: Pagination.

		$query = "
			SELECT
				p1.email, p1.communication_type, p1.subscribe, p1.created, ct.post_title as list_name
			FROM
			     {$table} AS p1
			     INNER JOIN {$this->wpdb->posts} ct ON p1.communication_type = ct.id,
			    (SELECT
					email, communication_type, MAX(created) as last_optin
				FROM
					 {$table}
				GROUP BY
	                email, communication_type) AS maxes
			WHERE
				p1.email = maxes.email AND
			  	p1.communication_type = maxes.communication_type AND
		      	p1.created = maxes.last_optin

			AND {$where_sql} {$order_sql} {$limit_sql}
		";

		$models  = [];
		$email   = '';
		$optins  = [];
		$optouts = [];
		foreach ( $this->wpdb->get_results( $query, ARRAY_A ) as $item ) {
			if ( $email !== $item['email'] ) {
				if ( $email !== '' ) {
					$models[] = new EmailOptModel( $email, $optins, $optouts );
				}
				$email   = $item['email'];
				$optins  = [];
				$optouts = [];
			}
			if ( $item['subscribe'] === '1' ) {
				$optins[] = new OptInModel( (int) $item['communication_type'], $item['list_name'], new \DateTimeImmutable( $item['created'] ) );
			} else {
				$optouts[] = new OptOutModel( (int) $item['communication_type'], $item['list_name'], new \DateTimeImmutable( $item['created'] ) );
			}
		}
		if ( $email !== '' ) {
			$models[] = new EmailOptModel( $email, $optins, $optouts );
		}

		return $models;
	}

	/**
	 * @param array $where
	 *
	 * @return int
	 */
	public function get_total_items( array $where ) {
		$table       = $this->get_table_name();
		$where_sql   = $this->where_array_to_sql( $where );
		$query_count = "SELECT COUNT(*) FROM $table as p1 WHERE {$where_sql}";
		$count       = $this->wpdb->get_var( $query_count );
		if ( is_numeric( $count ) ) {
			return (int) $count;
		}

		return 0;
	}

	/**
	 * @param int $page
	 * @param null $pagesize
	 *
	 * @return string[]
	 */
	public function get_all_optin_emails( $page = 0, $pagesize = null ) {
		$table = $this->get_table_name();
		if ( is_int( $pagesize ) ) {
			$offset = max( 0, $page - 1 ) * $pagesize;
			$query  = $this->wpdb->prepare( "SELECT DISTINCT email FROM {$table} LIMIT %d OFFSET %d", $pagesize, $offset );
		} else {
			$query = $this->wpdb->prepare( "SELECT DISTINCT email FROM {$table}" );
		}

		return $this->wpdb->get_col( $query );
	}
}
