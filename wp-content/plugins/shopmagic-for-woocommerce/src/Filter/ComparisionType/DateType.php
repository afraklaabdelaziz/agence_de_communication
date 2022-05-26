<?php

namespace WPDesk\ShopMagic\Filter\ComparisionType;

use ShopMagicVendor\WPDesk\Forms\Field\DatePickerField;
use WPDesk\ShopMagic\FormField\Field\SelectField;

final class DateType extends AbstractType {
	/**
	 * @inheritDoc
	 */
	public function passed( $expected_value, $compare_type, $actual_value ) {
		if ( $actual_value instanceof \DateTimeInterface ) {
			$actual_value->setTimezone( wp_timezone() );
			$actual_value = $actual_value->format( 'Y-m-d' );
		}

		$actual_value   = date( 'Y-m-d', is_numeric( $actual_value ) ? $actual_value : strtotime( $actual_value ) );
		$expected_value = date( 'Y-m-d', is_numeric( $expected_value ) ? $expected_value : strtotime( $expected_value ) );

		switch ( $compare_type ) {
			case 'is_after':
				return $actual_value > $expected_value;

			case 'is_before':
				return $actual_value < $expected_value;

			case 'is_on':
				$x = $actual_value === $expected_value;

				return $actual_value === $expected_value;

			case 'is_not_on':
				return $actual_value !== $expected_value;
			default:
				return false;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_conditions() {
		$compare_types = [];

		$compare_types['is_after']  = __( 'Is after', 'shopmagic-for-woocommerce' );
		$compare_types['is_before'] = __( 'Is before', 'shopmagic-for-woocommerce' );
		$compare_types['is_on']     = __( 'Is on', 'shopmagic-for-woocommerce' );
		$compare_types['is_not_on'] = __( 'Is not on', 'shopmagic-for-woocommerce' );

		return $compare_types;
	}

	/**
	 * @inheritDoc
	 */
	public function get_fields() {
		return [
			( new SelectField() )
				->set_name( self::CONDITION_KEY )
				->set_options( $this->get_conditions() ),
			( new DatePickerField() )
				->set_name( self::VALUE_KEY ),
		];
	}
}
