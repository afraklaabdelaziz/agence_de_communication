<?php

declare( strict_types=1 );

namespace WPDesk\ShopMagic\Database\Abstraction\DAO;

use WPDesk\ShopMagic\Exception\CannotProvideItemException;

/**
 * Can save/get/find/delete object from some kind of source.
 */
interface PersistenceGateway {
	/**
	 * Get an item using a primary key value. Value can be an array for compound keys.
	 *
	 * @param string[] $primary_key_value
	 *
	 * @return Item
	 *
	 * @throws CannotProvideItemException
	 */
	public function get_by_primary( string ...$primary_key_value ): Item;

	/**
	 * Get single item using a where. If where returns more the once item then only the first will be used.
	 *
	 * @param array $where
	 *
	 * @return Item
	 *
	 * @throws CannotProvideItemException
	 */
	public function get_single_by_where( array $where ): Item;

	/**
	 * Get all items using where and order clauses.
	 *
	 * @param array $where
	 * @param array $order
	 * @param int $offset
	 * @param int|null $limit Null when no limit and all items should be retrieved.
	 *
	 * @return Collection<Item>
	 */
	public function get_all( array $where = [], array $order = [], int $offset = 0, int $limit = null ): Collection;

	/**
	 * Get item count using where clause.
	 *
	 * @param array $where
	 *
	 * @return int
	 */
	public function get_count( array $where = [] ): int;

	/**
	 * Can this DAO handle a given item?
	 *
	 * @param Item $item
	 *
	 * @return bool True when this DAO can use provided DAOItem
	 */
	public function can_handle( Item $item ): bool;

	/**
	 * Save an item to the source.
	 *
	 * @param Item $item
	 *
	 * @return bool True if succeeded.
	 */
	public function save( Item $item ): bool;

	/**
	 * Delete item from source.
	 *
	 * @param string ...$primary_key_value Array of primary key values.
	 *
	 * @return bool True if succeeded.
	 */
	public function delete_by_primary( string ...$primary_key_value ): bool;

	/**
	 * Delete group of items from source.
	 *
	 * @param array $where
	 *
	 * @return int How many was deleted.
	 */
	public function delete_by_where( array $where = [] ): int;

	/**
	 * Refresh item from source and return a new one.
	 *
	 * @param Item $item
	 *
	 * @return Item Brand new $item.
	 */
	public function refresh( Item $item ): Item;

}
