<?php

namespace WPDesk\ShopMagic\Integration\FlexibleCheckoutFields\Placeholder;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use WPDesk\FCF\Free\Integration\FieldInterface;
use WPDesk\ShopMagic\FormField\Field\SelectField;
use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;
use WPDesk\ShopMagic\Placeholder\PlaceholderGroup;

final class OrderCheckoutField extends WooCommerceOrderBasedPlaceholder implements LoggerAwareInterface {
	use LoggerAwareTrait;

	const PARAM_NAME_FIELD = 'field';

	/** @var \WPDesk\FCF\Free\Integration\Integrator */
	private $integrator;

	public function __construct( \WPDesk\FCF\Free\Integration\Integrator $integrator, LoggerInterface $logger ) {
		$this->integrator = $integrator;
		$this->logger     = $logger;
	}

	public function get_slug(): string {
		return PlaceholderGroup::class_to_group( $this->get_required_data_domains() ) . '.checkout_field';
	}

	private function get_fcf_options(): array {
		$options = [];
		$fields  = $this->integrator->get_available_fields();
		foreach ( $fields as $field ) {
			$label = $field->get_field_label();
			if ( empty( $label ) ) {
				$label = $field->get_field_key();
			}

			$options[ $field->get_field_key() ] = "{$field->get_group_key()}: {$label}";
		}

		return $options;
	}

	public function get_supported_parameters(): array {
		return [
			( new SelectField() )
				->set_description( __( 'Flexible Checkout Field name to display', 'shopmagic-for-woocommerce' ) )
				->set_label( __( 'Field name', 'shopmagic-for-woocommerce' ) )
				->set_options( $this->get_fcf_options() )
				->set_required()
				->set_name( self::PARAM_NAME_FIELD ),
		];
	}

	public function value( array $parameters ): string {
		if ( ! isset( $parameters[ self::PARAM_NAME_FIELD ] ) ) {
			return '';
		}
		try {
			$field_key = $parameters[ self::PARAM_NAME_FIELD ];

			$field = array_reduce(
				$this->integrator->get_available_fields(),
				static function ( $carry, FieldInterface $field ) use ( $field_key ) {
					return $field->get_field_key() === $field_key ? $field : $carry;
				}
			);
			if ( $field instanceof FieldInterface ) {
				$value = $this->integrator->get_field_value( $field_key, $this->get_order()->get_id() );
				$type  = $field->get_field_type();
				switch ( $type ) {
					case 'wpdeskmultiselect':
						return (string) implode( ', ', $value );
					case 'file': // @TODO: value is empty. Bug in FCF
					default:
						return (string) $value;
				}
			}

			return '';
		} catch ( \Throwable $e ) {
			$this->logger->error( 'Error in FCF Integration in ' . __CLASS__ . '::' . __METHOD__, [ 'exception' => $e ] );

			return '';
		}
	}
}
