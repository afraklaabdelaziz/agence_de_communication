<?php

declare( strict_types=1 );

namespace WPDesk\ShopMagic\Database\Abstraction;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use WPDesk\ShopMagic\Database\Abstraction\DAO;
use WPDesk\ShopMagic\Database\CollectionUsingRawData;
use WPDesk\ShopMagic\Database\RepositoryOrderingTrait;
use WPDesk\ShopMagic\Exception\CannotProvideItemException;

/**
 * Implementation of DAO\PersistenceGateway for single database table.
 */
abstract class AbstractSingleTable implements DAO\PersistenceGateway, LoggerAwareInterface {
	use RepositoryOrderingTrait;
	use LoggerAwareTrait;

	public function __construct( LoggerInterface $logger = null ) {
		$this->logger = $logger ?? new NullLogger();
	}

	public function get_all( array $where = [], array $order = [], int $offset = 0, int $limit = null ): DAO\Collection {
		$where_sql = $this->where_array_to_sql( $where );
		$order_sql = $this->order_array_to_sql( $order );
		$sql       = "SELECT * FROM {$this->get_name()} WHERE {$where_sql} {$order_sql}";
		if ( $limit !== null ) {
			$sql .= " LIMIT {$limit} OFFSET {$offset}";
		}

		return new CollectionUsingRawData( $this->get_factory(), $this->get_wpdb()->get_results( $sql, ARRAY_A ) );
	}

	/**
	 * Table name.
	 *
	 * @return string
	 */
	abstract protected function get_name(): string;

	/**
	 * Factory that can creates item for given table.
	 *
	 * @return DAO\ItemFactory
	 */
	abstract protected function get_factory(): DAO\ItemFactory;

	protected function get_wpdb(): \wpdb {
		global $wpdb;

		return $wpdb;
	}

	public function get_count( array $where = [] ): int {
		$where_sql = $this->where_array_to_sql( $where );
		$sql       = "SELECT COUNT(*) FROM {$this->get_name()} WHERE {$where_sql}";

		return (int) $this->get_wpdb()->get_var( $sql );
	}

	public function can_handle( DAO\Item $item ): bool {
		return false;
	}

	public function save( DAO\Item $item ): bool {
		if ( ! $item->has_changed() ) {
			return false;
		}
		$item_data = $item->normalize();
		if ( count( $item->get_changed_fields() ) > 0 ) {
			$item_data = array_intersect_key( $item_data, $item->get_changed_fields() );
		}
		$item_data = array_intersect_key( $item_data, array_flip( $this->get_columns() ) );

		$insert_required = count( $this->retrieve_primary_key_value_from_item( $item ) ) !== count( $this->get_primary_key() );
		if ( ! $insert_required ) {
			$saved_count = $this->get_wpdb()->update(
				$this->get_name(),
				$item_data,
				array_combine( $this->get_primary_key(), $this->retrieve_primary_key_value_from_item( $item ) )
			);
			if ( $saved_count === 0 ) {
				$insert_required = true;
			}
		}
		if ( $insert_required ) {
			$saved_count = $this->get_wpdb()->insert(
				$this->get_name(),
				$item_data
			);
			if ( $saved_count === 1 ) {
				$item->set_last_inserted_id( $this->get_wpdb()->insert_id );
			}
		}

		return $saved_count > 0;
	}

	private function retrieve_primary_key_value_from_item( DAO\Item $item ): array {
		$keys = [];
		foreach ( $this->get_primary_key() as $key_index ) {
			$value = (string) $this->get_field_value_from_item( $item, $key_index );
			if ( $value !== '' ) {
				$keys[] = $value;
			}
		}

		return $keys;
	}

	/**
	 * @return string[] It can be compound primary key.
	 */
	abstract protected function get_primary_key(): array;

	/**
	 * @param DAO\Item $item
	 * @param string $key_index
	 *
	 * @return mixed
	 */
	private function get_field_value_from_item( DAO\Item $item, string $key_index ) {
		$method = 'get_' . $key_index;
		if ( method_exists( $item, $method ) ) {
			return $item->{$method}();
		}

		return $item->normalize()[ $key_index ];
	}

	public function delete_by_primary( string ...$primary_key_value ): bool {
		$where = array_combine( $this->get_primary_key(), $primary_key_value );

		return $this->delete_by_where( $where ) > 0;
	}

	public function delete_by_where( array $where = [] ): int {
		return (int) $this->get_wpdb()->delete( $this->get_name(), $where );
	}

	public function refresh( DAO\Item $item ): DAO\Item {
		return $this->get_by_primary( ...$this->retrieve_primary_key_value_from_item( $item ) );
	}

	/**
	 * @throws \WPDesk\ShopMagic\Exception\CannotProvideItemException
	 */
	public function get_by_primary( string ...$primary_key_value ): DAO\Item {
		$sql    = "SELECT * FROM {$this->get_name()} WHERE {$this->build_primary_key_value_for_prepare(...$this->get_primary_key())} LIMIT 1";
		$result = $this->get_wpdb()->get_row( $this->get_wpdb()->prepare( $sql, ...$primary_key_value ), ARRAY_A );

		if ( ! empty( $result ) ) {
			return $this->get_factory()->create_item( $result );
		}
		throw CannotProvideItemException::create_for_persistence_gateway( 'get_by_primary' );
	}

	/**
	 * @throws \WPDesk\ShopMagic\Exception\CannotProvideItemException
	 */
	public function get_single_by_where( array $where ): DAO\Item {
		$where_sql = $this->where_array_to_sql( $where );
		$sql       = "SELECT * FROM {$this->get_name()} WHERE {$where_sql} LIMIT 1";
		$result    = $this->get_wpdb()->get_row( $sql, ARRAY_A );

		if ( ! empty( $result ) ) {
			return $this->get_factory()->create_item( $result );
		}

		throw CannotProvideItemException::create_for_persistence_gateway( 'get_single_by_where' );
	}

	private function build_primary_key_value_for_prepare( string ...$primary_key_value ): string {
		return implode(
			' AND ',
			array_map(
				function ( $key_part ) {
					return "{$key_part} = %s";
				},
				$primary_key_value
			)
		);
	}

	/**
	 * Table item fields.
	 *
	 * @return string[]
	 */
	abstract protected function get_columns(): array;
}
