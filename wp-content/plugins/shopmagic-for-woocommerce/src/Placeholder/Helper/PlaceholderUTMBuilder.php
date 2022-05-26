<?php

declare( strict_types=1 );

namespace WPDesk\ShopMagic\Placeholder\Helper;

use WPDesk\ShopMagic\FormField\Field\InputTextField;

/**
 * Add UTM integration for placeholders. Create URI with UTM.
 *
 * @package WPDesk\ShopMagic\Placeholder\Helper
 */
class PlaceholderUTMBuilder {

	/**
	 * Coupled with JS value for UTM.
	 *
	 * @see UtmCache.UTM_PARAMETERS
	 */
	const UTM_PARAMETER_KEYS = [
		'utm_source',
		'utm_medium',
		'utm_campaign',
		'utm_content',
		'utm_term',
	];

	public function append_utm_parameters_to_uri( array $parameters, string $url ): string {
		$utm_parameters = $this->pick_utm_parameters( $parameters );
		return esc_url_raw( add_query_arg( $utm_parameters, $url ) );
	}

	private function pick_utm_parameters( array $parameters ): array {
		return array_filter(
			$parameters,
			static function ( $utm_value, $utm_property ) {
				return in_array( $utm_property, self::UTM_PARAMETER_KEYS, true ) && $utm_value !== '';
			},
			ARRAY_FILTER_USE_BOTH
		);
	}

	public function get_utm_fields(): array {
		return [
			( new InputTextField() )
				->set_label( __( 'UTM Source', 'shopmagic-for-woocommerce' ) . ' *' )
				->set_name( self::UTM_PARAMETER_KEYS[0] ),
			( new InputTextField() )
				->set_label( __( 'UTM Medium', 'shopmagic-for-woocommerce' ) . ' *' )
				->set_name( self::UTM_PARAMETER_KEYS[1] ),
			( new InputTextField() )
				->set_label( __( 'UTM Campaign', 'shopmagic-for-woocommerce' ) . ' *' )
				->set_name( self::UTM_PARAMETER_KEYS[2] ),
			( new InputTextField() )
				->set_label( __( 'UTM Content', 'shopmagic-for-woocommerce' ) )
				->set_name( self::UTM_PARAMETER_KEYS[3] ),
			( new InputTextField() )
				->set_label( __( 'UTM Term', 'shopmagic-for-woocommerce' ) )
				->set_name( self::UTM_PARAMETER_KEYS[4] ),
		];
	}

	public function get_description(): string {
		return esc_html__( 'If you want to', 'shopmagic-for-woocommerce' ) . ' ' .
			'<a href="">' . esc_html__( 'track your traffic in Google Analytics', 'shopmagic-for-woocommerce' ) . '</a> ' .
			esc_html__( ', you have to fill fields marked with * (UTM Source, Medium and Campaign).', 'shopmagic-for-woocommerce' ) .
			'<br>' .
			esc_html__( 'Otherwise, leave it blank and your placeholder will work fine.', 'shopmagic-for-woocommerce' );
	}

}
