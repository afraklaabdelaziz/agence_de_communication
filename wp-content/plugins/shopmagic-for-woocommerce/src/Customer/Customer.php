<?php

namespace WPDesk\ShopMagic\Customer;

/**
 * Customer data access unification.
 *
 * @package WPDesk\ShopMagic\Customer
 */
interface Customer {
	const USER_LANGUAGE_META = 'shopmagic_user_language';
	/**
	 * @return bool
	 */
	public function is_guest();

	/**
	 * @return string
	 */
	public function get_id();

	/**
	 * @return string
	 */
	public function get_username();

	/**
	 * @return string
	 */
	public function get_first_name();

	/**
	 * @return string
	 */
	public function get_last_name();

	/**
	 * @return string
	 */
	public function get_full_name();

	/**
	 * @return string
	 */
	public function get_email();

	/**
	 * @return string
	 */
	public function get_phone();
}
