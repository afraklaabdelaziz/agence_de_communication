<?php

namespace WPDesk\ShopMagic\Placeholder;

use Automattic\WooCommerce\Admin\Events;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\DataSharing\DataProvider;

/**
 * Process string and delegate any variables creation to subsequent classes.
 *
 * @package WPDesk\ShopMagic\Placeholder
 */
class PlaceholderProcessor {
	const PARAM_SEPARATOR       = ',';
	const PARAM_VALUE_SEPARATOR = ':';
	const PARAM_VALUE_WRAP      = "'";
	const PARAMS_SEPARATOR      = '|';

	const PLACEHOLDER_REGEX = '/{{[ ]*([^}]+)[ ]*}}/';

	/** @var PlaceholderFactory2 */
	private $placeholder_factory;

	/** @var DataProvider */
	private $provider;

	public function __construct( PlaceholderFactory2 $placeholder_factory, DataProvider $provider_for_placeholder ) {
		$this->placeholder_factory = $placeholder_factory;
		$this->provider            = $provider_for_placeholder;
	}

	/**
	 * @param string|mixed $params_string
	 */
	private function extract_parameters( $params_string ): array {
		$params = [];
		if ( $params_string !== null && trim( $params_string ) !== '' ) {
			$pos = - 1;
			do {
				$pos++;
				$param_separator_pos   = strpos( $params_string, self::PARAM_VALUE_SEPARATOR, $pos );
				$param_name            = trim( substr( $params_string, $pos, $param_separator_pos - $pos ) );
				$param_value_start_pos = strpos( $params_string, self::PARAM_VALUE_WRAP, $param_separator_pos );
				$param_value_end_pos   = strpos( $params_string, self::PARAM_VALUE_WRAP, $param_value_start_pos + 1 );
				$param_value           = trim( substr( $params_string, $param_value_start_pos + 1, $param_value_end_pos - $param_value_start_pos - 1 ) );
				$params[ $param_name ] = $param_value;
				$pos                   = strpos( $params_string, self::PARAM_SEPARATOR, $param_value_end_pos );
			} while ( $pos !== false );
		}

		return $params;
	}

	public function get_placeholder_slugs( string $string ): array {
		$slugs = [];

		$string = preg_replace_callback(
			self::PLACEHOLDER_REGEX,
			function ( $full_placeholder ) use ( &$slugs ) {
				@list( $placeholder_slug, $params_string ) = array_map( //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
					'trim',
					explode( self::PARAMS_SEPARATOR, $full_placeholder[1] ?? '', 2 )
				);
				$slugs[]                                   = $placeholder_slug;

				return '';
			},
			$string
		);

		return $slugs;
	}

	/**
	 * @return string|string[]|null
	 */
	public function process( string $string ) {
		$replacement_count = 0;
		do {
			$string = preg_replace_callback(
				self::PLACEHOLDER_REGEX,
				function ( $full_placeholder ) {
					@list( $placeholder_slug, $params_string ) = array_map( //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
						'trim',
						explode( self::PARAMS_SEPARATOR, $full_placeholder[1] ?? '', 2 )
					);

					$params = $this->extract_parameters( $params_string );

					if ( $this->placeholder_factory->is_placeholder_available( $this->provider, $placeholder_slug ) ) {
						$placeholder = $this->placeholder_factory->create_placeholder(
							$this->provider,
							$placeholder_slug
						);

						return $placeholder->value( $params );
					}

					return '';
				},
				$string,
				1,
				$replacement_count
			);
		} while ( $replacement_count > 0 );

		return $string;
	}
}
