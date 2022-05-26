<?php

namespace WPDesk\ShopMagic\Filter\ComparisionType;

use ShopMagicVendor\WPDesk\Forms\Field\SelectField;

final class SelectOneToOneType extends AbstractType {

	/** @var array */
	private $options;

	public function __construct( array $options ) {
		$this->options = $options;
	}

	/**
	 * @inheritDoc
	 */
	public function passed( $expected_value, $compare_type, $actual_value ) {
		if ( $compare_type === 'is' ) {
			return $expected_value === $actual_value;
		}

		return $expected_value !== $actual_value;
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
			'is'     => __( 'is', 'shopmagic-for-woocommerce' ),
			'is_not' => __( 'is not', 'shopmagic-for-woocommerce' ),
		];
	}
}
