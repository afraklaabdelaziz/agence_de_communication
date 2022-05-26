<?php

namespace WPDesk\ShopMagic\Filter\ComparisionType;

use ShopMagicVendor\WPDesk\Forms\Field\SelectField;
use ShopMagicVendor\WPDesk\Forms\Field\WooSelect;

class SelectManyToManyType extends AbstractType {
	const VALUE_KEY = 'value_ids';

	/** @var array */
	protected $options;

	public function __construct( array $options ) {
		$this->options = $options;
	}

	public function get_fields() {
		return [
			( new SelectField() )
				->set_name( AbstractType::CONDITION_KEY )
				->set_options( $this->get_conditions() ),
			$this->get_select_field(),
		];
	}

	public function get_conditions() {
		return [
			'matches_any'  => __( 'matches any', 'shopmagic-for-woocommerce' ),
			'matches_all'  => __( 'matches all', 'shopmagic-for-woocommerce' ),
			'matches_none' => __( 'matches none', 'shopmagic-for-woocommerce' ),
		];
	}

	protected function get_select_field() {
		return ( new WooSelect() )
			->set_options( $this->options )
			->set_name( self::VALUE_KEY );
	}

	public function passed( $expected_value, $compare_type, $actual_value ) {
		if ( $compare_type === 'matches_none' ) {
			return count( array_intersect( $expected_value, $actual_value ) ) === 0;
		}
		if ( $compare_type === 'matches_all' ) {
			return count( array_intersect( $expected_value, $actual_value ) ) === count( $expected_value );
		}

		return count( array_intersect( $expected_value, $actual_value ) ) > 0;
	}
}
