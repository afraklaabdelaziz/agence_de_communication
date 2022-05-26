<?php

namespace WPDesk\ShopMagic\Guest;

use WPDesk\ShopMagic\Database\DatabaseSchema;
use WPDesk\ShopMagic\Database\RepositoryOrderingTrait;
use WPDesk\ShopMagic\Exception\CannotCreateGuestException;
use WPDesk\ShopMagic\Exception\CannotSaveGuestException;

/**
 * Can save/load guest.
 *
 * @TODO: refactor to use database abstraction in 3.0
 */
final class GuestDAO {
	use RepositoryOrderingTrait;

	/** @var \wpdb $wpdb */
	private $wpdb;

	/** @var Guest[] */
	private static $cache = [];

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * @param string $email
	 *
	 * @return Guest
	 * @throws CannotCreateGuestException|\Exception
	 */
	public function get_by_email( $email ) {
		if ( isset( self::$cache[ $email ] ) ) {
			return self::$cache[ $email ];
		}
		$table = DatabaseSchema::get_guest_table_name();
		// phpcs:disable WordPress.DB
		$query      = $this->wpdb->prepare( "SELECT id, email, tracking_key, created, updated FROM {$table} WHERE email = %s LIMIT 1", $email );
		$guest_data = $this->wpdb->get_row( $query, ARRAY_A );
		// phpcs:enable
		if ( empty( $guest_data ) ) {
			throw new CannotCreateGuestException( "Guest is not available for email {$email}" );
		}

		return new Guest( $guest_data['id'], $guest_data['email'], $guest_data['tracking_key'], new \DateTime( $guest_data['created'] ), new \DateTime( $guest_data['updated'] ), $this->get_metadata( $guest_data['id'] ) );
	}

	/**
	 * @param int $id
	 *
	 * @return Guest
	 * @throws CannotCreateGuestException|\Exception
	 */
	public function get_by_tracking_key( $token ): Guest {
		$table = DatabaseSchema::get_guest_table_name();
		// phpcs:disable WordPress.DB
		$query      = $this->wpdb->prepare( "SELECT id, email, tracking_key, created, updated FROM {$table} WHERE tracking_key = %d LIMIT 1", $token );
		$guest_data = $this->wpdb->get_row( $query, ARRAY_A );
		// phpcs:enable
		if ( empty( $guest_data ) ) {
			throw new CannotCreateGuestException( "Guest is not available for token {$token}" );
		}

		return new Guest( $guest_data['id'], $guest_data['email'], $guest_data['tracking_key'], new \DateTime( $guest_data['created'] ), new \DateTime( $guest_data['updated'] ), $this->get_metadata( $guest_data['id'] ) );
	}

	/**
	 * @param int $id
	 *
	 * @return Guest
	 * @throws CannotCreateGuestException|\Exception
	 */
	public function get_by_id( $id ) {
		if ( isset( self::$cache[ $id ] ) ) {
			return self::$cache[ $id ];
		}
		$table = DatabaseSchema::get_guest_table_name();
		// phpcs:disable WordPress.DB
		$query      = $this->wpdb->prepare( "SELECT id, email, tracking_key, created, updated FROM {$table} WHERE id = %d LIMIT 1", $id );
		$guest_data = $this->wpdb->get_row( $query, ARRAY_A );
		// phpcs:enable
		if ( empty( $guest_data ) ) {
			throw new CannotCreateGuestException( "Guest is not available for id {$id}" );
		}

		return new Guest( $guest_data['id'], $guest_data['email'], $guest_data['tracking_key'], new \DateTime( $guest_data['created'] ), new \DateTime( $guest_data['updated'] ), $this->get_metadata( $id ) );
	}

	/**
	 * @param array $order
	 * @param int|null $limit
	 * @param int $offset
	 *
	 * @return \Generator|Guest[]
	 * @throws \Exception
	 */
	public function get_all( array $order, $limit = 10, $offset = 0 ) {
		$table     = DatabaseSchema::get_guest_table_name();
		$order_sql = $this->order_array_to_sql( $order );
		if ( $limit !== null ) {
			$limit_sql = $this->limit_offset_to_sql( $limit, $offset );
		} else {
			$limit_sql = '';
		}
		$sql = "
			SELECT
			       id, email, tracking_key, created, updated
			FROM {$table}
			    {$order_sql} {$limit_sql}";
		foreach ( $this->wpdb->get_results( $sql, ARRAY_A ) as $guest_data ) { // phpcs:ignore WordPress.DB
			yield new Guest( $guest_data['id'], $guest_data['email'], $guest_data['tracking_key'], new \DateTime( $guest_data['created'] ), new \DateTime( $guest_data['updated'] ), $this->get_metadata( $guest_data['id'] ) );
		}
	}

	/**
	 * @return int
	 */
	public function get_count() {
		$table_name = DatabaseSchema::get_guest_table_name();
		$sql        = "SELECT COUNT(*) FROM {$table_name}";

		return (int) $this->wpdb->get_var( $sql );
	}

	/**
	 * @param $id
	 *
	 * @return array
	 */
	private function get_metadata( $id ) {
		$table = DatabaseSchema::get_guest_meta_table_name();
		// phpcs:disable WordPress.DB
		$query = $this->wpdb->prepare( "SELECT meta_id, meta_key, meta_value FROM {$table} WHERE guest_id = %d", $id );

		$result = [];
		foreach ( $this->wpdb->get_results( $query, ARRAY_A ) as $item ) {
			$result[ $item['meta_key'] ] = $item['meta_value'];
		}
		// phpcs:enable

		return $result;
	}

	/**
	 * @param Guest $guest
	 *
	 * @return Guest Saved guest with id.
	 *
	 * @throws CannotSaveGuestException
	 */
	public function save( Guest $guest ) {
		$id = $guest->get_id();
		$this->wpdb->query( 'START TRANSACTION' );
		try {
			$guest_data = [
				'email'        => $guest->get_email(),
				'tracking_key' => $guest->get_tracking_key(),
				'created'      => $guest->get_created()->format( 'Y-m-d G:i:s' ),
				'updated'      => gmdate( 'Y-m-d G:i:s' ),
			];
			if ( $id === null ) {
				if ( ! $this->wpdb->insert( DatabaseSchema::get_guest_table_name(), $guest_data ) ) {
					throw new CannotSaveGuestException( $this->wpdb->last_error );
				}
				$id = $this->wpdb->insert_id;
				$guest->sync_with_id( $id );
			} else {
				unset( $guest_data['created'] );
				$this->wpdb->update( DatabaseSchema::get_guest_table_name(), $guest_data, [ 'id' => $id ] ); // do not throw as update can not change anything.
			}

			$this->save_metadata( $id, $guest->get_all_metadata() );

			return $guest;
		} catch ( \Throwable $e ) {
			$this->wpdb->query( 'ROLLBACK' );
			throw $e;
		} finally {
			$this->wpdb->query( 'COMMIT' );
			self::$cache[ $guest->get_id() ]    = $guest;
			self::$cache[ $guest->get_email() ] = $guest;
		}
	}

	/**
	 * @param int      $guest_id
	 * @param string[] $metadata
	 *
	 * @return void
	 */
	private function save_metadata( int $guest_id, array $metadata ) {
		// phpcs:disable WordPress.DB
		foreach ( $metadata as $meta_key => $meta_value ) {
			if ( $this->has_meta( $guest_id, $meta_key ) ) {
				$query_result = $this->wpdb->update(
					DatabaseSchema::get_guest_meta_table_name(),
					[ 'meta_value' => $meta_value ],
					[
						'guest_id' => $guest_id,
						'meta_key' => $meta_key,
					]
				);
			} else {
				$query_result = $this->wpdb->insert(
					DatabaseSchema::get_guest_meta_table_name(),
					[
						'guest_id'   => $guest_id,
						'meta_key'   => $meta_key,
						'meta_value' => $meta_value,
					]
				);
			}

			if ( $query_result === false ) {
				throw new CannotSaveGuestException( $this->wpdb->last_error );
			}
		}
		// phpcs:enable
	}

	private function has_meta( int $id, string $meta_key ): bool {
		$table = DatabaseSchema::get_guest_meta_table_name();
		return ! is_null( $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM `$table` WHERE guest_id=%d AND meta_key=%s", $id, $meta_key ) ) ); // phpcs:ignore WordPress.DB
	}

	/**
	 * @param string $phrase
	 * @param int $limit
	 *
	 * @retun Generator<Guest>
	 */
	public function search( $phrase, $limit = 20 ) {
		$table          = DatabaseSchema::get_guest_table_name();
		$meta_table     = DatabaseSchema::get_guest_meta_table_name();
		$escaped_phrase = $this->wpdb->esc_like( $phrase );
		$guest_query    = $this->wpdb->prepare(
			"
			SELECT
			       DISTINCT id
			FROM
			     {$table} g
			LEFT JOIN {$meta_table} m ON m.guest_id = g.id
			WHERE
				email LIKE '%%%s%%' OR
			      (meta_key = 'first_name' AND meta_value LIKE '%%%s%%') OR
			      (meta_key = 'last_name' AND meta_value LIKE '%%%s%%')
			LIMIT
				{$limit}",
			$escaped_phrase,
			$escaped_phrase,
			$escaped_phrase
		);

		foreach ( $this->wpdb->get_col( $guest_query ) as $id ) {
			yield $this->get_by_id( $id );
		}
	}

	public function delete( string $id ): int {
		$result = (int) $this->wpdb->delete(
			DatabaseSchema::get_guest_meta_table_name(),
			[ 'guest_id' => $id ]
		);

		$result += (int) $this->wpdb->delete(
			DatabaseSchema::get_guest_table_name(),
			[ 'id' => $id ]
		);

		return $result;
	}
}
