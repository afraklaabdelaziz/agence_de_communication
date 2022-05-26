<?php

namespace WPDesk\ShopMagic\ActionExecution;

use WPDesk\ShopMagic\Action\Action;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Automation\AutomationFactory;
use WPDesk\ShopMagic\DataSharing\DataLayer;
use WPDesk\ShopMagic\Event\Event;
use WPDesk\ShopMagic\Placeholder\PlaceholderFactory2;
use WC_Queue_Interface;
use WPDesk\ShopMagic\Placeholder\PlaceholderProcessor;

/**
 * Responsible for creating strategies how to execute an action. This particular factory is using Queue.
 *
 * @package WPDesk\ShopMagic\ActionExecution
 *
 * @deprecated 2.34 No longer in use.
 */
class QueueExecutionStrategyFactory implements ExecutionStrategyFactory {
	const ACTION_EXECUTE_FROM_QUEUE = 'shopmagic/core/queue/execute';
	const FILTER_AVOID_QUEUE        = 'shopmagic/core/queue/avoid_queue';

	const QUEUE_GROUP = 'shopmagic-automation';

	const SCHEDULE_TYPE_PLACEHOLDER = 'placeholder';
	const SCHEDULE_TYPE_DELAY       = 'delay';

	const DELAY_FIELD_ENABLED            = '_action_delayed';
	const DELAY_FIELD_SCHEDULE_TYPE      = '_action_schedule_type';
	const DELAY_FIELD_PLACEHOLDER_STRING = '_action_variable_string';
	const DELAY_FIELD_DELAY_OFFSET       = '_action_delayed_offset_time';

	/** @var PlaceholderFactory2 */
	private $placeholder_factory;

	/** @var AutomationFactory */
	private $automation_factory;

	/** @var WC_Queue_Interface */
	private $queue_client;

	public function __construct( PlaceholderFactory2 $placeholder_factory ) {
		$this->placeholder_factory = $placeholder_factory;
	}

	/**
	 * @inheritDoc
	 */
	public function set_automation_factory( AutomationFactory $automation_factory ) {
		$this->automation_factory = $automation_factory;
	}

	/**
	 * Whe is delayed action it has parameters with info about delay.
	 *
	 * @param Action $action
	 *
	 * @return bool
	 */
	private function is_delayed_action( Action $action ) {
		try {
			return $action->get_fields_data()->has( self::DELAY_FIELD_ENABLED ) && $action->get_fields_data()->get( self::DELAY_FIELD_ENABLED ) === 'on'; // see: shopmagic-delayed-actions plugin.
		} catch ( \Exception $e ) {
			// TODO: better persistence library.
			return false;
		}
	}

	private function ensure_valid_date_format( string $variable ): string {
		if ( strpos( $variable, 'format' ) === false ) {
			if ( strpos( $variable, '|' ) === false ) {
				return str_replace( '}}', "| format: 'Y-m-d G:i:s' }}", $variable );
			}

			return str_replace( '}}', " , format: 'Y-m-d G:i:s' }}", $variable );
		}

		return $variable;
	}

	private function create_delayed_executor( Automation $automation, Event $event, Action $action ): ExecutionStrategy {
		try {
			if ( $action->get_fields_data()->has( self::DELAY_FIELD_SCHEDULE_TYPE ) ) {
				$schedule_type = $action->get_fields_data()->get( self::DELAY_FIELD_SCHEDULE_TYPE );
			} else {
				$schedule_type = self::SCHEDULE_TYPE_DELAY;
			}
			switch ( $schedule_type ) {
				case self::SCHEDULE_TYPE_PLACEHOLDER:
					$variable             = $action->get_fields_data()->get( self::DELAY_FIELD_PLACEHOLDER_STRING );
					$variable             = $this->ensure_valid_date_format( $variable );
					$data_layer           = new DataLayer( $event, [ $automation, $event, $action ] );
					$processor            = new PlaceholderProcessor( $this->placeholder_factory, $data_layer );
					$variable_processed   = $processor->process( $variable );
					$variable_in_timezone = new \DateTime( $variable_processed, wp_timezone() );
					$timestamp            = $variable_in_timezone->getTimestamp();
					if ( $timestamp !== false ) {
						return new ExecuteQueueScheduled( $this->queue_client, $timestamp );
					}

					return new ExecuteQueueNow( $this->queue_client );
				default:
					$delay = (int) $action->get_fields_data()->get( self::DELAY_FIELD_DELAY_OFFSET ); // see: shopmagic-delayed-actions plugin.

					return new ExecuteQueueScheduled( $this->queue_client, time() + $delay );
			}
		} catch ( \Throwable $e ) {
			return new ExecuteQueueNow( $this->queue_client );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function create_strategy( Automation $automation, Event $event, Action $action, $action_index ) {
		// TODO: refactor in 3.0 in sync with Delayed Action plugin. Should use fields.
		if ( $this->is_delayed_action( $action ) ) {
			return $this->create_delayed_executor( $automation, $event, $action );
		}

		$avoid_queue = apply_filters( self::FILTER_AVOID_QUEUE, false, $automation, $event, $action, $action_index );
		if ( $avoid_queue ) {
			return new ExecuteNow( $this->placeholder_factory );
		}

		return new ExecuteQueueNow( $this->queue_client );
	}

	/**
	 * Use hooks to catch deferred works from queue, refresh them with data and execute using ExecuteNow strategy.
	 *
	 * @return void
	 */
	public function initialize( \WC_Queue_Interface $queue ) {
		add_action(
			'woocommerce_init',
			function () {
				$this->queue_client = \WC_Queue::instance();
			}
		);

		add_action( self::ACTION_EXECUTE_FROM_QUEUE, [ $this, 'run_action' ], 10, 5 );
	}

	/**
	 * Run action from queue
	 *
	 * @param array{id: int} $automation_serialized @see \WPDesk\ShopMagic\Automation\Automation::jsonSerialize
	 * @param array $event_serialized @see \WPDesk\ShopMagic\Event\Event::jsonSerialize
	 * @param array $action_serialized
	 * @param int $action_index
	 * @param string $unique_id
	 *
	 * @return void
	 *
	 * @internal
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
