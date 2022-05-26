<?php

namespace WPDesk\ShopMagic\ActionExecution;

use WPDesk\ShopMagic\Automation\AutomationFactory;
use WPDesk\ShopMagic\Placeholder\PlaceholderFactory2;

/**
 * Runs specific action from ActionScheduler's queue hook.
 */
final class QueueActionRunner {
	const ACTION_EXECUTE_FROM_QUEUE = 'shopmagic/core/queue/execute';
	const QUEUE_GROUP               = 'shopmagic-automation';

	/** @var PlaceholderFactory2 */
	private $placeholder_factory;

	/** @var AutomationFactory */
	private $automation_factory;

	public function __construct( PlaceholderFactory2 $placeholder_factory, AutomationFactory $factory ) {
		$this->placeholder_factory = $placeholder_factory;
		$this->automation_factory  = $factory;
	}

	/** @return void */
	public function initialize() {
		add_action( self::ACTION_EXECUTE_FROM_QUEUE, [ $this, 'run_action' ], 10, 5 );
	}

	/**
	 * @param array{id: int} $automation_serialized
	 * @param array $event_serialized
	 * @param array $action_serialized
	 * @param int $action_index
	 * @param string $unique_id
	 *
	 * @return void
	 */
	public function run_action( $automation_serialized, $event_serialized, $action_serialized, $action_index, $unique_id = null ) {
		if ( $unique_id === null ) {
			$unique_id = uniqid( 'fallback_', true );
		}
		$run = function () use ( $automation_serialized, $event_serialized, $action_index, $unique_id ) {
			$automation = $this->automation_factory->create_automation( $automation_serialized['id'] );
			$automation->initialize( $this->automation_factory->create_runner( $automation ) );

			$event = $automation->get_event();
			$event->set_from_json( $event_serialized );

			$executor = new ExecuteNow( $this->placeholder_factory, true );
			$executor->execute( $automation, $event, $automation->get_action( $action_index ), $action_index, $unique_id );
		};

		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			$run();
		} elseif ( did_action( 'wp_loaded' ) ) {
			$run();
		} else {
			add_action( 'wp_loaded', $run );
		}
	}

}
