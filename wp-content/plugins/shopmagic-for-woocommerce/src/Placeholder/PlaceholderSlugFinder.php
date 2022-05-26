<?php

namespace WPDesk\ShopMagic\Placeholder;

class PlaceholderSlugFinder {

	public function get_placeholder_slugs( string $string ): array {
		$slugs = [];

		preg_replace_callback(
			PlaceholderProcessor::PLACEHOLDER_REGEX,
			function ( $full_placeholder ) use ( &$slugs ) {
				@list( $placeholder_slug, $params_string ) = array_map(
					'trim',
					explode( PlaceholderProcessor::PARAMS_SEPARATOR, isset( $full_placeholder[1] ) ? $full_placeholder[1] : '', 2 )
				);
				$slugs[]                                   = $placeholder_slug;

				return '';
			},
			$string
		);

		return $slugs;
	}
}
