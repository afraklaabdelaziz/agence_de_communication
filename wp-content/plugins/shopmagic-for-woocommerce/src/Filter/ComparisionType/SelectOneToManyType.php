<?php

namespace WPDesk\ShopMagic\Filter\ComparisionType;

use ShopMagicVendor\WPDesk\Forms\Field\SelectField;
use ShopMagicVendor\WPDesk\Forms\Field\WooSelect;

class SelectOneToManyType extends AbstractType {
	const VALUE_KEY = 'value_ids';

	const CONDITION_MATCHES_NONE = 'matches_none';

	/** @var array */
	private $options;

	public function __construct( array $options ) {
		$this->options = $options;
	}

	/**
	 * @inheritDoc
	 */
	public function get_fields() {
		return [
			( new SelectField() )
				->set_name( AbstractType::CONDITION_KEY )
				->set_options( $this->get_conditions() ),
			$this->get_select_field(),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function get_conditions() {
		return [
			'matches_any'                => __( 'matches any', 'shopmagic-for-woocommerce' ),
			self::CONDITION_MATCHES_NONE => __( 'matches none', 'shopmagic-for-woocommerce' ),
		];
	}

	/**
	 * @return SelectField
	 */
	protected function get_select_field() {
		return ( new WooSelect() )
			->set_options( $this->options )
			->set_name( self::VALUE_KEY );
	}

	/**
	 * @inheritDoc
	 */
	public function passed( $expected_value, $compare_type, $actual_value ) {
		if ( $compare_type === 'matches_none' ) {
			return ! in_array( $actual_value, $expected_value );
		}

		return in_array( $actual_value, $expected_value );
	}

	/**
	 * Special comparision for WooCommerce statuses only.
	 *
	 * @param string[] $expected_value
	 * @param string $compare_type
	 * @param string $actual_value
	 *
	 * @return bool
	 */
	public function passed_wc_status( array $expected_value, string $compare_type, string $actual_value ): bool {
		$actual_wc_value = 'wc-' . $actual_value;
		if ( $compare_type === 'matches_none' ) {
			return ! in_array( $actual_value, $expected_value ) && ! in_array( $actual_wc_value, $expected_value );
		}

		return in_array( $actual_value, $expected_value ) || in_array( $actual_wc_value, $expected_value );
	}
}
