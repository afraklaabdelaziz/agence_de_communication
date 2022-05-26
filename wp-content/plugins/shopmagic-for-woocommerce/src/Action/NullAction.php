<?php

namespace WPDesk\ShopMagic\Action;

use Psr\Container\ContainerInterface;
use ShopMagicVendor\WPDesk\Persistence\Adapter\ArrayContainer;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Event\Event;
use WPDesk\ShopMagic\Placeholder\PlaceholderProcessor;

/**
 * NullObject pattern. When no action is found this class is used.
 *
 * @package WPDesk\ShopMagic\Action
 */
class NullAction implements Action {
	public function get_name() {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function set_placeholder_processor( PlaceholderProcessor $processor ) {
	}

	/**
	 * @inheritDoc
	 */
	public function get_fields_data() {
		return new ArrayContainer();
	}

	/**
	 * @inheritDoc
	 */
	public function execute( Automation $automation, Event $event ) {
	}

	/**
	 * @inheritDoc
	 */
	public function get_required_data_domains() {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function set_provided_data( array $data ) {
	}

	public function get_fields() {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function update_fields_data( ContainerInterface $data ) {
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize() {
		return [];
	}
}
