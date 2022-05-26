<?php

namespace WPDesk\ShopMagic\Event;

use WPDesk\ShopMagic\CommunicationList\CommunicationListRepository;
use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\FormField\Field\SelectField;

abstract class OptCommonEvent extends UserCommonEvent implements ManualGlobalEvent {
	const PARAM_COMMUNICATION_TYPE = 'communication_type';

	/**
	 * @return string
	 * @throws \Exception
	 *
	 * TODO: refactor to use ManualGlobalEvent disptcher rather than late static binding
	 */
	protected static function get_internal_action_name() {
		throw new \Exception( 'Invalid get_internal_action_name call' );
	}

	public function get_fields(): array {
		return [
			( new SelectField() )
				->set_label( __( 'List', 'shopmagic-for-woocommerce' ) )
				->set_name( self::PARAM_COMMUNICATION_TYPE )
				->set_placeholder( __( 'Any list', 'shopmagic-for-woocommerce' ) )
				->set_options( CommunicationListRepository::get_lists_as_select_options() )
				->set_description_tip( __( 'Choose the list to which the customer opted in, or from which he opted out.', 'shopmagic-for-woocommerce' ) ),
		];
	}

	public static function trigger( array $args ) {
		// TODO: refactor to use ManualGlobalEvent disptcher rather than late static binding.
		do_action( static::get_internal_action_name(), ...$args );
	}

	public function initialize() {
		add_action( static::get_internal_action_name(), [ $this, 'process_event' ], 10, 2 );
	}

	/**
	 * Save params and run actions.
	 *
	 * @param Customer $customer
	 * @param int      $term_id
	 */
	public function process_event( Customer $customer, int $term_id ) {
		$this->customer = $customer;

		$expected_type_id = $this->fields_data->get( self::PARAM_COMMUNICATION_TYPE );
		if ( empty( $expected_type_id ) || (int) $expected_type_id === (int) $term_id ) {
			$this->run_actions();
		}
	}
}
