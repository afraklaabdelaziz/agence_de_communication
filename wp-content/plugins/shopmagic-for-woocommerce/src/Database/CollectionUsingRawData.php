<?php

declare( strict_types=1 );

namespace WPDesk\ShopMagic\Database;

use WPDesk\ShopMagic\Database\Abstraction\DAO\ItemFactory;

/**
 * Collection for group of DTOItem objects.
 *
 * @package WPDesk\ShopMagic\Database
 */
class CollectionUsingRawData implements Abstraction\DAO\Collection {

	/** @var ItemFactory */
	private $factory;

	/** @var array */
	private $raw_data;

	public function __construct( ItemFactory $factory, array $raw_data ) {
		$this->factory  = $factory;
		$this->raw_data = $raw_data;
	}

	public function is_empty(): bool {
		return count( $this->raw_data ) === 0;
	}

	public function count(): int {
		return count( $this->raw_data );
	}

	public function jsonSerialize() {
		return array_map(
			function ( array $item ) {
				return $this->factory->create_item( $item )->jsonSerialize();
			},
			$this->raw_data
		);
	}

	/**
	 * @return Abstraction\DAO\Item|false
	 */
	public function current() {
		$current = current( $this->raw_data );
		if ( $current ) {
			return $this->factory->create_item( $current );
		}

		return $current;
	}

	/**
	 * @return Abstraction\DAO\Item|false
	 */
	public function next() {
		$next = next( $this->raw_data );
		if ( $next ) {
			return $this->factory->create_item( $next );
		}

		return $next;
	}

	public function key() {
		return key( $this->raw_data );
	}

	public function valid(): bool {
		return current( $this->raw_data ) !== false;
	}

	/**
	 * @return Abstraction\DAO\Item|false
	 */
	public function rewind() {
		$first = reset( $this->raw_data );
		if ( $first ) {
			return $this->factory->create_item( $first );
		}

		return $first;
	}
}
