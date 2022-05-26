<?php

namespace WPDesk\ShopMagic\Action;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

interface ActionFactory2 {
	/**
	 * Return an action corresponding to given slug.
	 * This action can be executed. When you only want to list possible actions use @param ContainerInterface $data Fields values.
	 *
	 * @param string slug Unique action slug.
	 * @param ContainerInterface $data Data from provider to hydrate action.
	 *
	 * @return Action
	 * @see get_action.
	 * Actions are implemented using Prototype Pattern.
	 */
	public function create_action( string $slug, ContainerInterface $data ): Action;

	/**
	 * Return an action corresponding to given slug.
	 * WARNING: Returned action is not hydrated with field data so should never be executed.
	 * If you want to execute an action. @return Action When action is not found returns null object
	 *
	 * @see create_action
	 * This method can be used for testing action execution but fields_data must be hydrated manually.
	 */
	public function get_action( string $slug ): Action;

	/**
	 * Returns all actions.
	 *
	 * @return Action[] In format [ slug => instance, .. ]
	 */
	public function get_action_list(): array;

	/**
	 * Initialize all action by injecting test data and running hooks if has any.
	 *
	 * @param LoggerInterface $logger
	 */
	public function initialize_actions_additional_hooks( LoggerInterface $logger );
}
