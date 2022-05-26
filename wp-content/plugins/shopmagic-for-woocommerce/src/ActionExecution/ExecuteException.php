<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\ActionExecution;

use Throwable;
use WPDesk\ShopMagic\Action\Action;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\AutomationOutcome\OutcomeReposistory;
use WPDesk\ShopMagic\Event\Event;

/**
 * Used when action for some reason shouldn't execute.
 */
final class ExecuteException implements ExecutionStrategy {

	/** @var Throwable */
	private $e;

	public function __construct( Throwable $e ) {
		$this->e = $e;
	}

	public function execute( Automation $automation, Event $event, Action $action, $action_index, $unique_id ) {
		$this->save_outcome(
			$unique_id,
			false,
			"error: {$this->e->getMessage()}"
		);
	}

	private function save_outcome( string $unique_id, bool $result, string $note = null, array $context = [] ) {
		global $wpdb;
		$outcome_repository = new OutcomeReposistory( $wpdb );
		$outcome_repository->finish_outcome( $unique_id, $result, $note, $context );
	}
}
