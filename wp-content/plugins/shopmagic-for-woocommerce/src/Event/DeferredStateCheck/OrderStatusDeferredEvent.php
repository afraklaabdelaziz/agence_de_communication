<?php

namespace WPDesk\ShopMagic\Event\DeferredStateCheck;

use Psr\Container\ContainerInterface;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Automation\Runner;
use WPDesk\ShopMagic\Event\Event2;
use WPDesk\ShopMagic\Event\OrderCommonEvent;
use WPDesk\ShopMagic\Filter\FilterLogic;
use WPDesk\ShopMagic\FormField\Field\CheckboxField;

/**
 * Decorates OrderCommonEvent by adding deferred status checks.
 *
 * @package WPDesk\ShopMagic\Event
 */
class OrderStatusDeferredEvent implements Event2, SupportsDeferredCheck {

	/** @var OrderCommonEvent */
	private $event;

	/** @var ContainerInterface */
	private $fields_data;

	/** @var string */
	private $status;

	public function __construct( OrderCommonEvent $event, $status ) {
		$this->event  = $event;
		$this->status = $status;
	}

	public function __clone() {
		$this->event = clone $this->event;
	}

	public function get_fields() {
		return array_merge( $this->event->get_fields(), [ ( new DefferedCheckField() ) ] );
	}

	public function is_event_still_valid() {
		if ( ! $this->fields_data->has( DefferedCheckField::NAME ) || $this->fields_data->get( DefferedCheckField::NAME ) === CheckboxField::VALUE_FALSE ) {
			return true;
		}
		$provided_data = $this->event->get_provided_data();

		return $provided_data[ \WC_Order::class ]->has_status( $this->status );
	}

	public function get_provided_data_domains() {
		return $this->event->get_provided_data_domains();
	}

	public function get_provided_data() {
		return $this->event->get_provided_data();
	}

	public function get_name() {
		return $this->event->get_name();
	}

	public function get_group_slug() {
		return $this->event->get_group_slug();
	}

	public function get_description() {
		return $this->event->get_description();
	}

	public function initialize() {
		$this->event->initialize();
	}

	public function set_filter_logic( FilterLogic $filter ) {
		$this->event->set_filter_logic( $filter );
	}

	public function set_automation( Automation $automation ) {
		$this->event->set_automation( $automation );
	}

	public function set_runner( Runner $runner ) {
		$this->event->set_runner( $runner );
	}

	public function jsonSerialize() {
		return $this->event->jsonSerialize();
	}

	// phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
	public function set_from_json( array $serializedJson ) {
		$this->event->set_from_json( $serializedJson );
	}
	// phpcs:enable

	public function update_fields_data( ContainerInterface $data ) {
		$this->fields_data = $data;
		$this->event->update_fields_data( $data );
	}
}
