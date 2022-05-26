<?php

namespace WPDesk\ShopMagic\Filter;

use ShopMagicVendor\WPDesk\Forms\FieldProvider;
use ShopMagicVendor\WPDesk\Forms\FieldsDataReceiver;

/**
 * Filter are connected in ChainOfResponsibility pattern.
 *
 * @see FilterFactory2::get_filter() - prototype pattern
 *
 * @TODO: add _clone method - should be explicitly used even when empty
 *
 * @package WPDesk\ShopMagic\Filters
 */
interface Filter extends FilterLogic, FieldProvider, FieldsDataReceiver {
	/**
	 * @return string
	 */
	public function get_name();

	/**
	 * @return string
	 */
	public function get_description();

	/**
	 * @return string
	 */
	public function get_group_slug();
}
