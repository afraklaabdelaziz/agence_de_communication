<?php

declare( strict_types=1 );

namespace WPDesk\ShopMagic\Database\Abstraction\DAO;

/**
 * Single Item that can be load and save using DAO.
 */
interface Item {

	// TODO: uncomment in 3.0
	// public function get_id(): int;

	/**
	 * @return string[]
	 */
	public function get_changed_fields(): array;

	/**
	 * @return bool
	 */
	public function has_changed(): bool;

	/**
	 * If item has any autogenerated/autoincremented values then this method will be fired with an int.
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	public function set_last_inserted_id( int $id );

	/**
	 * @return non-empty-array<string, mixed>
	 */
	public function normalize(): array;
}
