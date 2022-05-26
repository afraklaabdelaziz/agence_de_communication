<?php

namespace WPDesk\ShopMagic\ActionExecution;

use WPDesk\ShopMagic\Action\Action;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Automation\AutomationFactory;
use WPDesk\ShopMagic\Event\Event;

/**
 * Responsible for creating strategies how to execute an action.
 *
 * @deprecated 2.34 Will be replaced with ExecutionStrategyContainerInterface.
 */
interface ExecutionStrategyFactory {

	/**
	 * Factory method to decide how to execute an action.
	 *
	 * @param Automation $automation
	 * @param Event $event Event hydrated with current event data.
	 * @param Action $action
	 * @param int $action_index Index key of action in given Automation. Can be used to decide what to do next (ie. run next action).
	 *
	 * @return ExecutionStrategy
	 */
	public function create_strategy( Automation $automation, Event $event, Action $action, $action_index );

	/**
	 * Set factory that can create an automation from given id.
	 *
	 * @param AutomationFactory $automation_factory
	 *
	 * @return void
	 */
	public function set_automation_factory( AutomationFactory $automation_factory );

	/**
	 * Optional method to initialize the factory. Plugin have to hit it.
	 *
	 * @param \WC_Queue_Interface $queue
	 *
	 * @return void
	 */
	public function initialize( \WC_Queue_Interface $queue);
}
