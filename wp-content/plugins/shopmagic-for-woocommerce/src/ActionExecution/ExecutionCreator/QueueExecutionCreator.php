<?php

namespace WPDesk\ShopMagic\ActionExecution\ExecutionCreator;

use WPDesk\ShopMagic\Action\Action;
use WPDesk\ShopMagic\ActionExecution\ExecuteNow;
use WPDesk\ShopMagic\ActionExecution\ExecuteQueueNow;
use WPDesk\ShopMagic\ActionExecution\ExecutionStrategy;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Event\Event;
use WPDesk\ShopMagic\Placeholder\PlaceholderFactory2;

/**
 * Default executor based on ActionScheduler queue.
 * Should be always attached in ExecutionStrategyContainer.
 */
final class QueueExecutionCreator implements ExecutionCreator {

	/** @var PlaceholderFactory2 */
	private $placeholder_factory;

	/** @var \WC_Queue_Interface */
	private $queue_client;

	public function __construct( \WC_Queue_Interface $queue, PlaceholderFactory2 $placeholder_factory ) {
		$this->placeholder_factory = $placeholder_factory;
		$this->queue_client        = $queue;
	}

	public function create_executor( Automation $automation, Event $event, Action $action ): ExecutionStrategy {
		// @todo Remove 0 as an index.
		if ( $this->should_avoid_queue( $automation, $event, $action, 0 ) ) {
			return new ExecuteNow( $this->placeholder_factory );
		}

		return new ExecuteQueueNow( $this->queue_client );
	}

	private function should_avoid_queue( Automation $automation, Event $event, Action $action, int $action_index ): bool {
		/**
		 * @param bool $avoid_queue
		 * @param Automation $automation
		 * @param Event $event
		 * @param Action $action
		 * @param int $action_index
		 *
		 * @return bool
		 */
		return (bool) apply_filters( 'shopmagic/core/queue/avoid_queue', false, $automation, $event, $action, $action_index );
	}


	public function should_create( Action $action ): bool {
		return true;
	}
}
