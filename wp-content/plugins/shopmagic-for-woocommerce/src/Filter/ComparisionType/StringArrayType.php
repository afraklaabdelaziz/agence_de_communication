<?php

namespace WPDesk\ShopMagic\Filter\ComparisionType;

/**
 * Can be used when you want to check if single value is one of set given by string.
 *
 * @package WPDesk\ShopMagic\Filter\ComparisionType
 */
final class StringArrayType extends AbstractType {
	const DELIMITER = ',';

	/**
	 * @inheritDoc
	 */
	public function passed( $expected_value, $compare_type, $actual_value ) {
		$actual_value   = (string) $actual_value;
		$expected_value = explode( self::DELIMITER, $expected_value );

		switch ( $compare_type ) {
			case 'matches_any':
				return in_array( $actual_value, $expected_value, false );

			case 'matches_none':
				return ! in_array( $actual_value, $expected_value, false );
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function get_conditions() {
		return [
			'matches_any'  => __( 'matches any', 'shopmagic-for-woocommerce' ),
			'matches_none' => __( 'matches none', 'shopmagic-for-woocommerce' ),
		];
	}
}
