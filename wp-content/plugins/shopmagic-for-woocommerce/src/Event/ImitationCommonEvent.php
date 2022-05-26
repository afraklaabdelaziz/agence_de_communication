<?php

namespace WPDesk\ShopMagic\Event;

use Psr\Container\ContainerInterface;
use WPDesk\ShopMagic\Admin\Automation\AutomationFormFields\ProEventInfoField;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Automation\Runner;
use WPDesk\ShopMagic\Filter\FilterLogic;

/**
 * Base class for events that shows info about PRO upgrades.
 */
abstract class ImitationCommonEvent implements Event2 {

	/** @return \ShopMagicVendor\WPDesk\Forms\Field[] */
	public function get_fields(): array {
		$fields = [];

		$fields[] = ( new ProEventInfoField() );

		return $fields;
	}

	public function initialize() {
	}

	public function get_group_slug(): string {
		return EventFactory2::GROUP_PRO;
	}

	public function set_filter_logic( FilterLogic $filter ) {
	}

	public function set_automation( Automation $automation ) {
	}

	public function set_runner( Runner $runner ) {}

	public function __clone() {}

	public function jsonSerialize() {
	}

	public function set_from_json( array $serializedJson ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
	}

	public function get_provided_data_domains(): array {
		return [];
	}

	public function get_provided_data(): array {
		return [];
	}

	public function update_fields_data( ContainerInterface $data ) {
	}
}
