<?php

namespace WPDesk\ShopMagic\Customer;

/**
 * Customer data access unification.
 *
 * @package WPDesk\ShopMagic\Customer
 */
interface Customer2 extends Customer {

	public function get_language(): string;
}
