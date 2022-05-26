<?php

namespace WPDesk\ShopMagic\Filter\ComparisionType;

use ShopMagicVendor\WPDesk\Forms\Field\SelectField;

final class SelectManyToOneType extends AbstractType {

	/** @var array */
	private $options;

	public function __construct( array $options ) {
		$this->options = $options;
	}

	/**
	 * @param string|float $expected_value
	 * @param string $compare_type
	 * @param array $actual_value As it's many to one, array of arrays is expected
	 *
	 * @return bool
	 */
	public function passed( $expected_value, $compare_type, $actual_value ) {
		switch ( $compare_type ) {
			case 'all_are':
				foreach ( $actual_value as $value ) {
					if ( ! in_array( $expected_value, $value, false ) ) {
						return false;
					}
				}

				return true;

			case 'none_is':
				foreach ( $actual_value as $value ) {
					if ( in_array( $expected_value, $value, false ) ) {
						return false;
					}
				}

				return true;

			case 'any_is':
				foreach ( $actual_value as $value ) {
					if ( in_array( $expected_value, $value, false ) ) {
						return true;
					}
				}

				return false;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_fields() {
		return [
			( new SelectField() )
				->set_name( self::CONDITION_KEY )
				->set_options( $this->get_conditions() ),
			( new SelectField() )
				->set_name( self::VALUE_KEY )
				->set_options( $this->options ),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function get_conditions() {
		return [
			'any_is'  => __( 'any is', 'shopmagic-for-woocommerce' ),
			'all_are' => __( 'all are', 'shopmagic-for-woocommerce' ),
			'none_is' => __( 'none is', 'shopmagic-for-woocommerce' ),
		];
	}
}
