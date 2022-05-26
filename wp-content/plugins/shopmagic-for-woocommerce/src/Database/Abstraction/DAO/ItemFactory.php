<?php

declare( strict_types=1 );

namespace WPDesk\ShopMagic\Database\Abstraction\DAO;

/**
 * Can create Items.
 *
 * @package WPDesk\ShopMagic\Database\Abstraction\DAO
 */
interface ItemFactory {
	/**
	 * Creates a a null object.
	 *
	 * @return Item
	 */
	public function create_null(): Item;

	/**
	 * Creates a item and hydrates it with data.
	 *
	 * @param array $data
	 *
	 * @return Item
	 */
	public function create_item( array $data ): Item;
}
