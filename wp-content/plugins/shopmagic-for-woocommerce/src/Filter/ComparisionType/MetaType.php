<?php

namespace WPDesk\ShopMagic\Filter\ComparisionType;

use ShopMagicVendor\WPDesk\Forms\Field\InputTextField;
use ShopMagicVendor\WPDesk\Forms\Field\SelectField;

final class MetaType extends AbstractType {
	const META_KEY = 'meta_key';

	/** @var StringType */
	private $string;

	/** @var FloatType */
	private $float;

	public function __construct() {
		$this->string = new StringType();
		$this->float  = new FloatType();
	}

	/**
	 * @inheritDoc
	 */
	public function passed( $expected_value, $compare_type, $actual_value ) {
		if ( array_key_exists( $compare_type, $this->string->get_conditions() ) ) {
			return $this->string->passed( $expected_value, $compare_type, $actual_value );
		}

		return $this->float->passed( $expected_value, $compare_type, $actual_value );
	}

	/**
	 * @inheritDoc
	 */
	public function get_fields() {
		return [
			( new InputTextField() )
				->set_name( self::META_KEY )
				->set_placeholder( 'meta key' ),
			( new SelectField() )
				->set_name( self::CONDITION_KEY )
				->set_options( $this->get_conditions() ),
			( new InputTextField() )
				->set_name( self::VALUE_KEY )
				->set_placeholder( 'meta value' ),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function get_conditions() {
		return array_merge( $this->string->get_conditions(), $this->float->get_conditions() );
	}
}
