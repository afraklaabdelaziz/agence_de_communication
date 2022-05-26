<?php

namespace WPDesk\ShopMagic\Action;

use Psr\Container\ContainerInterface;
use ShopMagicVendor\WPDesk\Forms\FieldProvider;
use ShopMagicVendor\WPDesk\Forms\FieldsDataReceiver;
use WPDesk\ShopMagic\DataSharing\DataReceiver;
use WPDesk\ShopMagic\Placeholder\PlaceholderProcessor;

/**
 * Action is one of the major components in ShopMagic. When an event occurs the actions are executed.
 * Action can be serialized to json to save it's data to queue.
 * Actions are implemented using Prototype Pattern.
 *
 * @TODO: add _clone method - should be explicitly used even when empty
 *
 * @package WPDesk\ShopMagic\Action
 */
interface Action extends DataReceiver, FieldProvider, ActionLogic, FieldsDataReceiver, \JsonSerializable {

	/**
	 * Name of the action visible in admin panel.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Processor is required to process placeholders that can be used in action fields.
	 *
	 * @param PlaceholderProcessor $processor
	 *
	 * @return void
	 */
	public function set_placeholder_processor( PlaceholderProcessor $processor );

	/**
	 * Action has fields that can be shown in admin panel. Here are the values of these fields.
	 *
	 * @return ContainerInterface
	 */
	public function get_fields_data();
}
