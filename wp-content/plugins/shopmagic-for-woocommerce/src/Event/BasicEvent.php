<?php

namespace WPDesk\ShopMagic\Event;

use Psr\Container\ContainerInterface;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Automation\Runner;
use WPDesk\ShopMagic\Filter\FilterLogic;

/**
 * ShopMagic Events Base class.
 *
 * @package WPDesk\ShopMagic\Event
 */
abstract class BasicEvent implements Event2 {

	/** @var Automation*/
	protected $automation;

	/** @var ContainerInterface */
	protected $fields_data;

	/** @var Runner */
	protected $runner;

	/** @var FilterLogic */
	protected $filter;

	public function __clone() {
		$this->fields_data = null;
		$this->automation  = null;
		$this->filter      = null;
		$this->runner      = null;
	}

	public function update_fields_data( ContainerInterface $data ) {
		$this->fields_data = $data;
	}

	public function get_provided_data_domains() {
		return [ Automation::class ];
	}

	public function get_fields() {
		return [];
	}

	public function get_provided_data() {
		return [ Automation::class => $this->runner->get_automation() ];
	}

	public function set_automation( Automation $automation ) {
		$this->automation = $automation;
	}

	public function set_runner( Runner $runner ) {
		$this->runner = $runner;
	}

	public function set_filter_logic( FilterLogic $filter ) {
		$this->filter = $filter;
	}

	/**
	 * Run registered actions from automation
	 *
	 * @since 1.0.0
	 * @deprecated 2.31.0 Use trigger_automation() for method immutability.
	 */
	protected function run_actions() {
		$this->trigger_automation();
	}

	/**
	 * @return void
	 * @since 2.31.0
	 */
	final protected function trigger_automation() {
		$this->runner->run();
	}

	/**
	 * Returns the description of the current Event
	 *
	 * @since   1.0.4
	 */
	public function get_description() {
		return __( 'No description provided for this event.', 'shopmagic-for-woocommerce' );
	}

	public function jsonSerialize() {
		return [];
	}

	public function set_from_json( array $serializedJson ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
		// nothing to do here...
	}
}
