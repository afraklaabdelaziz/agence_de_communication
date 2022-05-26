<?php

namespace WPDesk\ShopMagic\ActionExecution;

use WPDesk\ShopMagic\Action\Action;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\DataSharing\DataLayer;
use WPDesk\ShopMagic\Event\Event;
use WC_Queue_Interface;

/**
 * Execute action asap but use queue client to spread the load.
 *
 * @package WPDesk\ShopMagic\ActionExecution
 */
class ExecuteQueueNow implements ExecutionStrategy {

	/** @var WC_Queue_Interface */
	private $queue_client;

	public function __construct( WC_Queue_Interface $queue_client ) {
		$this->queue_client = $queue_client;
	}

	/**
	 * @inheritDoc
	 */
	public function execute( Automation $automation, Event $event, Action $action, $action_index, $unique_id ) {
		$this->queue_client->add(
			QueueActionRunner::ACTION_EXECUTE_FROM_QUEUE,
			[ $automation, $event, $action, $action_index, $unique_id, new DataLayer( $event ) ],
			QueueActionRunner::QUEUE_GROUP
		);

		return null;
	}
}
