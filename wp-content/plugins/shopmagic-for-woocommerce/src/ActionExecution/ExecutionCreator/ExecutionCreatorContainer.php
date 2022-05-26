<?php

namespace WPDesk\ShopMagic\ActionExecution\ExecutionCreator;

use WPDesk\ShopMagic\Action\Action;
use WPDesk\ShopMagic\ActionExecution\ExecutionStrategy;
use WPDesk\ShopMagic\ActionExecution\ExecutionStrategyFactory;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Automation\AutomationFactory;
use WPDesk\ShopMagic\Event\Event;
use WPDesk\ShopMagic\Exception\NoExecutionCreatorFoundException;

/**
 * Handles multiple ExecutionStrategies in an extensible way.
 *
 * Old methods remains stubbed for interface backward compatibility.
 */
final class ExecutionCreatorContainer implements ExecutionCreator, ExecutionStrategyFactory {

	/** @var ExecutionCreator[] */
	private $creators = [];

	/** @return void */
	public function add_execution_creator( ExecutionCreator $creator ) {
		$this->creators[] = $creator;
	}

	/**
	 * Fire attached executors in reversed order. This way it's easier for external ExecutionCreator to hook and override execution behavior.
	 * Possibly, that should leave core default executor untouched as last executed element.
	 *
	 * @throws NoExecutionCreatorFoundException
	 */
	public function create_executor( Automation $automation, Event $event, Action $action ): ExecutionStrategy {
		if ( ! $this->should_create( $action ) ) {
			throw new NoExecutionCreatorFoundException( self::class . ' needs at least one ExecutionCreator attached through ' . self::class . '::add_execution_creator method.' );
		}

		foreach ( array_reverse( $this->creators ) as $creator ) {
			if ( $creator->should_create( $action ) ) {
				return $creator->create_executor( $automation, $event, $action );
			}
		}

		throw new NoExecutionCreatorFoundException( 'No valid ExecutionCreator found. Possibly, each attached creator returned `false` in ExecutionCreator::should_create().' );
	}

	public function should_create( Action $action ): bool {
		return ! empty( $this->creators );
	}

	/** @codeCoverageIgnore */
	public function create_strategy( Automation $automation, Event $event, Action $action, $action_index ) {
		return $this->create_executor( $automation, $event, $action );
	}

	/** @codeCoverageIgnore */
	public function set_automation_factory( AutomationFactory $automation_factory ) {}

	/** @codeCoverageIgnore */
	public function initialize( \WC_Queue_Interface $queue ) {}

	/** @internal For testing purposes only. */
	public function get_creators(): array {
		return $this->creators;
	}

}
