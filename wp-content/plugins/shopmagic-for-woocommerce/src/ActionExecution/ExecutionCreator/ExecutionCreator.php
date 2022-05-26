<?php

namespace WPDesk\ShopMagic\ActionExecution\ExecutionCreator;

use WPDesk\ShopMagic\Action\Action;
use WPDesk\ShopMagic\ActionExecution\ExecutionStrategy;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Event\Event;

/**
 * Can create various executors from ExecutionStrategy
 *
 * @since 2.34
 */
interface ExecutionCreator {

	public function create_executor( Automation $automation, Event $event, Action $action ): ExecutionStrategy;

	public function should_create( Action $action ): bool;
}
