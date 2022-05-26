<?php

namespace WPDesk\ShopMagic\ActionExecution;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use WPDesk\ShopMagic\Action\Action;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\AutomationOutcome\OutcomeLogger;
use WPDesk\ShopMagic\AutomationOutcome\OutcomeReposistory;
use WPDesk\ShopMagic\DataSharing\DataLayer;
use WPDesk\ShopMagic\Event\Event;
use WPDesk\ShopMagic\Event\DeferredStateCheck\SupportsDeferredCheck;
use WPDesk\ShopMagic\Exception\ActionDisabledAfterStatusRecheckException;
use WPDesk\ShopMagic\LoggerFactory;
use WPDesk\ShopMagic\Placeholder\PlaceholderFactory2;
use WPDesk\ShopMagic\Placeholder\PlaceholderProcessor;

/**
 * Executes action NOW!. Do not use any queue od other async tools.
 *
 * @package WPDesk\ShopMagic\ActionExecution
 */
class ExecuteNow implements ExecutionStrategy {

	/** @var PlaceholderFactory2 */
	private $placeholder_factory;

	/** @var bool */
	private $permit_exceptions;

	public function __construct( PlaceholderFactory2 $placeholder_factory, bool $permit_exceptions = false ) {
		$this->placeholder_factory = $placeholder_factory;
		$this->permit_exceptions   = $permit_exceptions;
	}

	public function execute( Automation $automation, Event $event, Action $action, $action_index, $unique_id ) {
		if ( $action instanceof LoggerAwareInterface ) {
			$action->setLogger( $this->create_logger( $unique_id ) );
		}
		if ( $event instanceof SupportsDeferredCheck && ! $event->is_event_still_valid() ) {
			LoggerFactory::get_logger()->notice(
				"Deferred check. Event: {$event->get_name()}. Automation: {$automation->get_id()}. Order linked to Event has changed status again and is no longer consistent with this event.",
				[
					'automation'   => $automation,
					'event'        => $event,
					'action'       => $action,
					'action_index' => $action_index,
					'unique_id'    => $unique_id,
				]
			);

			throw new ActionDisabledAfterStatusRecheckException( esc_html__( 'Order linked to Event has changed status again and is no longer consistent with this event', 'shopmagic-for-woocommerce' ) );
		}
		$data_layer = new DataLayer( $event, [ $automation, $event, $action ] );
		$processor  = new PlaceholderProcessor( $this->placeholder_factory, $data_layer );

		$action->set_placeholder_processor( $processor );
		$action->set_provided_data( $data_layer->get_provided_data() );

		try {
			do_action( 'shopmagic/core/action/before_execution', $action, $automation, $event );
			$result = $action->execute( $automation, $event );
			$this->save_outcome( $unique_id, (bool) $result );
			do_action( 'shopmagic/core/action/successful_execution', $action, $automation, $event );
			do_action( 'shopmagic/core/action/after_execution', $action, $automation, $event );

			return $result;
		} catch ( \Throwable $e ) {
			$this->save_outcome(
				$unique_id,
				false,
				"error: {$e->getMessage()}",
				[
					'Error Code' => $e->getCode(),
					'Trace'      => $e->getTraceAsString(),
				]
			);
			do_action( 'shopmagic/core/action/failed_execution', $action, $automation, $event );
			do_action( 'shopmagic/core/action/after_execute', $action, $automation, $event );
			if ( $this->permit_exceptions ) {
				throw $e;
			}

			return false;
		}
	}

	private function create_logger( string $unique_id ): LoggerInterface {
		global $wpdb;
		$outcome_repository = new OutcomeReposistory( $wpdb );

		return new OutcomeLogger(
			LoggerFactory::get_logger(),
			$outcome_repository,
			$unique_id
		);
	}

	private function save_outcome( string $unique_id, bool $result, string $note = null, array $context = [] ) {
		global $wpdb;
		$outcome_repository = new OutcomeReposistory( $wpdb );
		$outcome_repository->finish_outcome( $unique_id, $result, $note, $context );
	}
}
