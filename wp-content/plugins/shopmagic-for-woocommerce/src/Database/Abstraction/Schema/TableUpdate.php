<?php

declare( strict_types=1 );

namespace WPDesk\ShopMagic\Database\Abstraction\Schema;

/**
 * Single schema update to the DB. This update alters/creates a db table.
 *
 * @package WPDesk\ShopMagic\Database\Abstraction\Schema
 */
abstract class TableUpdate extends Update {
	/**
	 * Returns a table name.
	 *
	 * @return string
	 */
	abstract public static function get_name(): string;
}
