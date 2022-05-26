<?php

namespace WPDesk\ShopMagic\Filter\ComparisionType;

class FloatType extends AbstractType {
	/**
	 * @inheritDoc
	 */
	public function passed( $expected_value, $compare_type, $actual_value ) {
		$actual_value   = (float) str_replace( ',', '.', $actual_value );
		$expected_value = (float) str_replace( ',', '.', $expected_value );

		switch ( $compare_type ) {

			case 'is':
				return $actual_value === $expected_value;
				break;

			case 'is_not':
				return $actual_value !== $expected_value;
				break;

			case 'greater_than':
				return $actual_value > $expected_value;
				break;

			case 'less_than':
				return $actual_value < $expected_value;
				break;

		}

		// validate 'multiple of' compares, only accept integers.
		if ( ! $this->is_whole_number( $actual_value ) || ! $this->is_whole_number( $expected_value ) ) {
			return false;
		}

		$actual_value   = (int) $actual_value;
		$expected_value = (int) $expected_value;

		switch ( $compare_type ) {

			case 'multiple_of':
				return $actual_value % $expected_value === 0;
				break;

			case 'not_multiple_of':
				return $actual_value % $expected_value !== 0;
				break;
		}

		return false;
	}

	/**
	 * @param $number
	 *
	 * @return bool
	 */
	private function is_whole_number( $number ) {
		$number = (float) $number;

		return floor( $number ) === $number;
	}

	/**
	 * @inheritDoc
	 */
	public function get_conditions() {
		return [
			'is'           => __( 'is', 'shopmagic-for-woocommerce' ),
			'is_not'       => __( 'is not', 'shopmagic-for-woocommerce' ),
			'greater_than' => __( 'is greater than', 'shopmagic-for-woocommerce' ),
			'less_than'    => __( 'is less than', 'shopmagic-for-woocommerce' ),
		];
	}
}
