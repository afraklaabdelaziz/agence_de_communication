<?php

namespace WPDesk\ShopMagic\Customer;

interface CustomerProvider {

	/**
	 * @return Customer
	 *
	 * @throws \WPDesk\ShopMagic\Exception\CannotProvideCustomerException;
	 */
	public function get_customer(): Customer;

	public function is_customer_provided(): bool;
}
