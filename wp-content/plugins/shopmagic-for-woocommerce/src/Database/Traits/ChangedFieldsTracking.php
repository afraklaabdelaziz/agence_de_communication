<?php

declare( strict_types=1 );

namespace WPDesk\ShopMagic\Database\Traits;

use WPDesk\ShopMagic\Database;

/**
 * Can automatically track field changes when item has no setters.
 *
 * @see Database\Abstraction\DAO\Item
 *
 * @package WPDesk\ShopMagic\Database\Traits
 */
trait ChangedFieldsTracking {

	private $changed_fields = [];

	public function __call( $name, $arguments ) {
		if ( strpos( 'set_', $name ) === 0 && count( $arguments ) === 1 ) {
			$property_name = substr( $name, 4 );
			if ( property_exists( $this, $property_name ) ) {
				$this->{$property_name}                 = $arguments[0];
				$this->changed_fields[ $property_name ] = true;
			}
		}
	}

	public function has_changed(): bool {
		return count( $this->changed_fields() ) > 0;
	}

	public function changed_fields(): array {
		return $this->changed_fields;
	}
}
