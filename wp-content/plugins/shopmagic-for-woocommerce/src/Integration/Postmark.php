<?php

namespace WPDesk\ShopMagic\Integration;

use Psr\Container\ContainerInterface;
use ShopMagicVendor\WPDesk\Forms\Field;
use WPDesk\ShopMagic\FormField\Field\InputTextField;

/**
 * Integration with Postmark streams through https://github.com/wildbit/postmark-wordpress plugin
 *
 * @package WPDesk\ShopMagic\Integration
 */
class Postmark {
	const FIELD_NAME_STREAM = 'postmark_message_stream';

	/** @var bool */
	private $enabled;

	/** @var \Closure|null */
	private $hook;

	public function __construct() {
		$this->enabled = (bool) apply_filters( 'shopmagic/core/postmark/enabled', false );
	}

	/**
	 * Appends Postmark form fields to the list.
	 *
	 * @param Field[] $fields
	 *
	 * @return Field[]
	 */
	public function append_fields_if_enabled( array $fields ): array {
		if ( $this->enabled ) {
			$fields[] = ( new InputTextField() )
				->set_name( self::FIELD_NAME_STREAM )
				->set_label( __( 'Postmark message stream', 'shopmagic-for-woocommerce' ) )
				->set_placeholder( __( 'outbound', 'shopmagic-for-woocommerce' ) )
				->set_description( __( 'Optional - Default is \'outbound\' if blank.', 'shopmagic -for- woocommerce' ) );
		}

		return $fields;
	}

	/**
	 * Hooks into Postmark plugin options.
	 *
	 * @param ContainerInterface $fields_data Action data in container.
	 */
	public function hook_to_postmark_if_enabled( ContainerInterface $fields_data ) {
		if ( $this->enabled ) {
			if ( $fields_data->has( self::FIELD_NAME_STREAM ) ) {
				$stream_name = $fields_data->get( self::FIELD_NAME_STREAM );
			}
			if ( empty( $stream_name ) ) {
				$stream_name = 'outbound';
			}
			$this->hook = static function ( $options ) use ( $stream_name ) {
				$options_array = json_decode( $options, true );
				if ( is_array( $options_array ) ) {
					$options_array['stream_name'] = $stream_name;
				}

				return json_encode( $options_array );
			};
			add_filter( 'option_postmark_settings', $this->hook );
		}
	}

	/**
	 * Clears hooks to Postmark plugin options.
	 */
	public function clear_hooks() {
		if ( $this->hook ) {
			remove_filter( 'option_postmark_settings', $this->hook );
			$this->hook = null;
		}
	}
}
