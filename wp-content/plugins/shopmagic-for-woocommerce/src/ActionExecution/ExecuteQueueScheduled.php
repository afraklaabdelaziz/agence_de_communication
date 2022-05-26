<?php

namespace WPDesk\ShopMagic\ActionExecution;

use WC_Queue_Interface;
use WPDesk\ShopMagic\Action\Action;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\DataSharing\DataLayer;
use WPDesk\ShopMagic\Event\Event;

/**
 * Execute action at a given time. Use scheduled queue to defer.
 *
 * @package WPDesk\ShopMagic\ActionExecution
 */
class ExecuteQueueScheduled implements ExecutionStrategy {

	/** @var WC_Queue_Interface */
	private $queue_client;

	/** @var int */
	private $scheduled_time;

	public function __construct( WC_Queue_Interface $queue_client, $scheduled_time ) {
		$this->queue_client   = $queue_client;
		$this->scheduled_time = $scheduled_time;
	}

	public function execute( Automation $automation, Event $event, Action $action, $action_index, $unique_id ) {
		$this->queue_client->schedule_single(
			$this->scheduled_time,
			QueueActionRunner::ACTION_EXECUTE_FROM_QUEUE,
			[ $automation, $event, $action, $action_index, $unique_id, new DataLayer( $event ) ],
			QueueActionRunner::QUEUE_GROUP
		);

		return null;
	}
}
