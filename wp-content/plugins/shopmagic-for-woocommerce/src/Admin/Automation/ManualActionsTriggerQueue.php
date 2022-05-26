<?php

namespace WPDesk\ShopMagic\Admin\Automation;

use WPDesk\ShopMagic\Automation\AutomationFactory;
use WPDesk\ShopMagic\Event\ManualEvent2;

/**
 * Trigger manual automations.
 */
final class ManualActionsTriggerQueue {
	const HOOK_TRIGGER_FOR_SLICE = 'shopmagic/core/manual/trigger_for_slice';

	/** @var AutomationFactory */
	protected $automation_factory;

	/**
	 * @param AutomationFactory $automation_factory
	 */
	public function __construct( AutomationFactory $automation_factory ) {
		$this->automation_factory = $automation_factory;
	}

	/**
	 * @param int $automation_id
	 * @param int[] $slice
	 *
	 * @return void
	 *
	 * @internal
	 */
	public function trigger_for_slice_from_queue( int $automation_id, array $slice ) {
		$automation = $this->automation_factory->create_automation( $automation_id );
		$automation->initialize( $this->automation_factory->create_runner( $automation ) );

		$event = $automation->get_event();
		if ( $event instanceof ManualEvent2 ) {
			foreach ( $slice as $id ) {
				$event->trigger( $id );
			}
		}
	}

	/**
	 * @return void
	 */
	public function hooks() {
		add_action( self::HOOK_TRIGGER_FOR_SLICE, [ $this, 'trigger_for_slice_from_queue' ], 10, 2 );
	}
}
