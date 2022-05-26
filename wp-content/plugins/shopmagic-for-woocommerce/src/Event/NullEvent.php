<?php

namespace WPDesk\ShopMagic\Event;

use Psr\Container\ContainerInterface;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Automation\Runner;
use WPDesk\ShopMagic\Filter\FilterLogic;

/**
 * Event NullObject passed when no compatiblie Event is found.
 */
class NullEvent implements Event2 {
	public function get_provided_data_domains() {
		return [];
	}

	public function get_provided_data() {
		return [];
	}

	public function __clone() {}

	public function get_name() {
		return __( 'Event does not exist', 'shopmagic-for-woocommerce' );
	}

	public function get_group_slug() {
		return '';
	}

	public function get_description() {
		return '';
	}

	public function initialize() {
	}

	public function set_filter_logic( FilterLogic $filter ) {
	}

	public function set_automation( Automation $automation ) {
	}

	public function set_runner( Runner $runner ) {
	}

	public function supports_deferred_check() {
		return false;
	}

	public function get_fields() {
		return [];
	}

	public function jsonSerialize() {
		return [];
	}

	public function set_from_json( array $serializedJson ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
	}

	public function update_fields_data( ContainerInterface $data ) {
	}
}
