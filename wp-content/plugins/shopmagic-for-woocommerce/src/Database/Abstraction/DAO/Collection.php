<?php

declare( strict_types=1 );

namespace WPDesk\ShopMagic\Database\Abstraction\DAO;

/**
 * Collection of DAO Items.
 *
 * @package WPDesk\ShopMagic\Database\Abstraction\DAO
 */
interface Collection extends \Traversable, \Countable, \JsonSerializable, \Iterator {
	public function is_empty(): bool;
}
