<?php

namespace WPDesk\ShopMagic\Filter\ComparisionType;

final class NullType implements ComparisionType {
	public function passed( $expected_value, $compare_type, $actual_value ) {
		return true;
	}

	public function get_conditions() {
		return [];
	}

	public function get_fields() {
		return [];
	}
}
