<?php

namespace WPDesk\ShopMagic\Filter\ComparisionType;

use ShopMagicVendor\WPDesk\Forms\Field\SelectField;

final class BoolType implements ComparisionType {
	const VALUE_KEY = 'value';

	/**
	 * @inheritDoc
	 */
	public function passed( $expected_value, $compare_type, $actual_value ) {
		return $expected_value === $actual_value;
	}

	/**
	 * @inheritDoc
	 */
	public function get_conditions() {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function get_fields() {
		return [
			( new SelectField() )
				->set_name( 'condition' )
				->set_disabled(),
			( new SelectField() )
				->set_options(
					[
						'yes' => __( 'Yes', 'shopmagic-for-woocommerce' ),
						'no'  => __( 'No', 'shopmagic-for-woocommerce' ),
					]
				)
				->set_name( 'value' ),
		];
	}
}
