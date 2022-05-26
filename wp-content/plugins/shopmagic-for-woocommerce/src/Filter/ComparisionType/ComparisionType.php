<?php

namespace WPDesk\ShopMagic\Filter\ComparisionType;

use ShopMagicVendor\WPDesk\Forms\FieldProvider;

interface ComparisionType extends FieldProvider {
	/**
	 * @param mixed $expected_value
	 * @param string $compare_type
	 * @param mixed $actual_value
	 *
	 * @return bool
	 */
	public function passed( $expected_value, $compare_type, $actual_value );

	/**
	 * @return string[]
	 */
	public function get_conditions();
}
