<?php

namespace WPDesk\ShopMagic\Filter\ComparisionType;

use ShopMagicVendor\WPDesk\Forms\Field\InputTextField;
use ShopMagicVendor\WPDesk\Forms\Field\SelectField;

/**
 * @TODO: in 3.0 fix the typo Comparision->Comparison
 */
abstract class AbstractType implements ComparisionType {
	const VALUE_KEY     = 'value';
	const CONDITION_KEY = 'condition';

	/**
	 * @param mixed $expected_value
	 * @param string $compare_type
	 * @param mixed $actual_value
	 *
	 * @return bool
	 */
	abstract public function passed( $expected_value, $compare_type, $actual_value );

	/**
	 * @inheritDoc
	 */
	public function get_fields() {
		return [
			( new SelectField() )
				->set_name( self::CONDITION_KEY )
				->set_options( $this->get_conditions() ),
			( new InputTextField() )
				->set_name( self::VALUE_KEY )
				->set_placeholder( __( 'value', 'shopmagic-for-woocommerce' ) ),
		];
	}
}
