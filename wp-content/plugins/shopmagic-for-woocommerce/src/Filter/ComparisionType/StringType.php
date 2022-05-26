<?php

namespace WPDesk\ShopMagic\Filter\ComparisionType;

final class StringType extends AbstractType {
	/**
	 * @inheritDoc
	 */
	public function passed( $expected_value, $compare_type, $actual_value ) {
		$actual_value   = (string) $actual_value;
		$expected_value = (string) $expected_value;

		// most comparisons are case insensitive.
		$actual_value_lowercase   = strtolower( $actual_value );
		$expected_value_lowercase = strtolower( $expected_value );

		switch ( $compare_type ) {
			case 'is':
				return $actual_value_lowercase == $expected_value_lowercase;
				break;

			case 'is_not':
				return $actual_value_lowercase != $expected_value_lowercase;
				break;

			case 'contains':
				return strstr( $actual_value_lowercase, $expected_value_lowercase ) !== false;
				break;

			case 'not_contains':
				return strstr( $actual_value_lowercase, $expected_value_lowercase ) === false;
				break;

			case 'starts_with':
				return $this->str_starts_with( $actual_value_lowercase, $expected_value_lowercase );

			case 'ends_with':
				return $this->str_ends_with( $actual_value_lowercase, $expected_value_lowercase );

			case 'blank':
				return empty( $actual_value );
				break;

			case 'not_blank':
				return ! empty( $actual_value );
				break;

			case 'regex':
				// Regex validation must not use case insensitive values.
				return $this->validate_string_regex( $actual_value, $expected_value );
		}

		return false;
	}

	/**
	 * Determine if a string starts with another string.
	 *
	 * @param string $haystack
	 * @param string $needle
	 *
	 * @return bool
	 */
	private function str_starts_with( $haystack, $needle ) {
		return substr( $haystack, 0, strlen( $needle ) ) === $needle;
	}

	/**
	 * Determine if a string ends with another string.
	 *
	 * @param string $haystack
	 * @param string $needle
	 *
	 * @return bool
	 */
	private function str_ends_with( $haystack, $needle ) {
		$length = strlen( $needle );

		if ( $length === 0 ) {
			return true;
		}

		return substr( $haystack, - $length ) === $needle;
	}

	/**
	 * Validates string regex rule.
	 *
	 * @param string $string
	 * @param string $regex
	 *
	 * @return bool
	 */
	private function validate_string_regex( $string, $regex ) {
		$regex = $this->remove_global_regex_modifier( trim( $regex ) );

		return (bool) @preg_match( $regex, $string );
	}

	/**
	 * Remove the global regex modifier as it is not supported by PHP.
	 *
	 * @param string $regex
	 *
	 * @return string
	 */
	private function remove_global_regex_modifier( $regex ) {
		return preg_replace_callback(
			'/(\/[a-z]+)$/',
			static function ( $modifiers ) {
				return str_replace( 'g', '', $modifiers[0] );
			},
			$regex
		);
	}

	/**
	 * @return string[]
	 */
	public function get_conditions() {
		return [
			'contains'     => esc_html__( 'contains', 'shopmagic-for-woocommerce' ),
			'not_contains' => esc_html__( 'does not contain', 'shopmagic-for-woocommerce' ),
			'is'           => esc_html__( 'is', 'shopmagic-for-woocommerce' ),
			'is_not'       => esc_html__( 'is not', 'shopmagic-for-woocommerce' ),
			'starts_with'  => esc_html__( 'starts with', 'shopmagic-for-woocommerce' ),
			'ends_with'    => esc_html__( 'ends with', 'shopmagic-for-woocommerce' ),
			'regex'        => esc_html__( 'matches regex', 'shopmagic-for-woocommerce' ),
		];
	}
}
