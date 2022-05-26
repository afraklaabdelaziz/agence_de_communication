<?php

namespace WPDesk\ShopMagic\Automation;

use WPDesk\ShopMagic\Action\Action;
use WPDesk\ShopMagic\ActionExecution\ExecutionStrategyFactory;
use WPDesk\ShopMagic\AutomationOutcome\OutcomeReposistory;
use WPDesk\ShopMagic\DataSharing\DataLayer;

/**
 * Prepares, validates and runs automation.
 */
final class AutomationRunner implements Runner {

	/** @var Automation */
	private $automation;

	/** @var AutomationValidator */
	private $validator;

	/** @var ExecutionStrategyFactory */
	private $factory;

	public function __construct(
		Automation $automation,
		AutomationValidator $validator,
		ExecutionStrategyFactory $executor
	) {
		$this->automation = $automation;
		$this->validator  = $validator;
		$this->factory    = $executor;
	}

	public function run() {
		$this->setup();

		if ( $this->validator->valid() ) {
			$this->execute_actions();
		}

		$this->cleanup();
	}

	/** @return void */
	private function execute_actions() {
		foreach ( array_values( $this->automation->get_actions() ) as $index => $action ) {
			/** @deprecated 2.31.0 */
			do_action( 'shopmagic/core/automation/event_fired', $this->automation, $this->automation->get_event(), $action );

			if ( $this->should_execute_action( $action ) ) {
				$this->delegate_for_execution( $action, $index );
			}
		}
	}

	/** @return void */
	private function delegate_for_execution( Action $action, int $index ) {
		$executor  = $this->factory->create_strategy( $this->automation, $this->automation->get_event(), $action, $index );
		$unique_id = $this->log_outcome( $action, $index );
		$executor->execute( $this->automation, $this->automation->get_event(), $action, $index, $unique_id );
	}

	private function log_outcome( Action $action, int $index ): string {
		global $wpdb;
		$outcomes = new OutcomeReposistory( $wpdb );
		return (string) $outcomes->prepare_for_outcome( $this->automation, $this->automation->get_event(), $action, $index );
	}

	/** @return void */
	private function setup() {
		$data_layer    = new DataLayer( $this->automation->get_event(), [ $this->automation ] );
		$provided_data = ( $data_layer )->get_provided_data();

		$this->validator->set_provided_data( $provided_data );

		do_action( 'shopmagic/core/automation/setup', $this );
	}

	/** @return void */
	private function cleanup() {
		do_action( 'shopmagic/core/automation/cleanup', $this );
	}

	private function should_execute_action( Action $action ): bool {
		return apply_filters( 'shopmagic/core/automation/should_execute_action', true, $this->automation, $this->automation->get_event(), $action );
	}

	public function get_automation(): Automation {
		return $this->automation;
	}

}
