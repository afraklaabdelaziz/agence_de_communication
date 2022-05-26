<?php

namespace WPDesk\ShopMagic\Automation;

use ShopMagicVendor\WPDesk\Persistence\Adapter\ArrayContainer;
use WPDesk\ShopMagic\Action\ActionFactory2;
use WPDesk\ShopMagic\ActionExecution\ExecutionStrategyFactory;
use WPDesk\ShopMagic\Automation\Validator\FiltersValidator;
use WPDesk\ShopMagic\Automation\Validator\FullyConfiguredValidator;
use WPDesk\ShopMagic\Event\Event2;
use WPDesk\ShopMagic\Event\EventFactory2;
use WPDesk\ShopMagic\Filter\FilterFactory2;
use WPDesk\ShopMagic\Filter\FilterGroupLogic;

/**
 * Can create automation from given id.
 *
 * @package WPDesk\ShopMagic\Automation
 */
final class AutomationFactory {

	/** @var EventFactory2 */
	private $event_factory;

	/** @var ActionFactory2 */
	private $action_factory;

	/** @var FilterFactory2 */
	private $filter_factory;

	/** @var Automation[] */
	private $automations = [];

	/** @var ExecutionStrategyFactory */
	private $execution_factory;

	/** @var AutomationValidator|null */
	private $validator;

	public function __construct(
		EventFactory2 $event_factory,
		ActionFactory2 $action_factory,
		FilterFactory2 $filter_factory,
		ExecutionStrategyFactory $execution_factory
	) {
		$this->event_factory     = $event_factory;
		$this->action_factory    = $action_factory;
		$this->filter_factory    = $filter_factory;
		$this->execution_factory = $execution_factory;
	}

	/** @return void */
	public function set_validator( AutomationValidator $validator ) {
		$this->validator = $validator;
	}

	public function get_validator(): AutomationValidator {
		return $this->validator ?? new AutomationValidator();
	}

	/** @return Automation[] */
	public function initialize_active_automations(): array {
		foreach ( get_posts( $this->all_active_automations() ) as $automation_post ) {
			$automation = $this->create_automation( (int) $automation_post->ID, new AutomationPersistence( $automation_post->ID ) );
			$automation->initialize( $this->create_runner( $automation ) );
		}

		return $this->automations;
	}

	public function create_runner( Automation $automation ): Runner {
		$validator = clone $this->get_validator();
		$validator
			->add_validator( new FullyConfiguredValidator( $automation ) )
			->add_validator( new FiltersValidator( $automation->get_filters() ) );

		return new AutomationRunner( $automation, $validator, $this->execution_factory );
	}

	/**
	 * @param int                       $automation_id
	 * @param PersistentAutomation|null $persistence @todo Remove fallback in 3.0
	 *
	 * @return Automation
	 */
	public function create_automation( int $automation_id, PersistentAutomation $persistence = null ): Automation {
		if ( isset( $this->automations[ $automation_id ] ) ) {
			return $this->automations[ $automation_id ];
		}

		if ( $persistence === null ) {
			$persistence = new AutomationPersistence( $automation_id );
		}

		$automation = new Automation(
			$automation_id,
			$this->get_event_for_automation( $persistence ),
			$this->get_filters_for_automation( $persistence ),
			$this->get_actions_for_automation( $persistence )
		);

		$this->automations[ $automation_id ] = $automation;
		return $automation;
	}

	private function get_filters_for_automation( PersistentAutomation $persistence ): FilterGroupLogic {
		$filters = [];
		foreach ( $persistence->get_filters_data() as $or_group_index => $and_group ) {
			$filters[ $or_group_index ] = [];
			foreach ( $and_group as $and_group_index => $data ) {
				if ( ! empty( $data[ AutomationPersistence::FILTER_DATA_KEY ] ) ) {
					$filter = $this->filter_factory->create_filter( $data[ AutomationPersistence::FILTER_SLUG_KEY ] );
					$filter->update_fields_data( new ArrayContainer( $data[ AutomationPersistence::FILTER_DATA_KEY ] ) );

					$filters[ $or_group_index ][ $and_group_index ] = $filter;
				}
			}
		}

		return new FilterGroupLogic( $filters );
	}

	private function get_event_for_automation( PersistentAutomation $persistence ): Event2 {
		$event = $this->event_factory->create_event( $persistence->get_event_slug() ); // @phpstan-ignore-line
		$event->update_fields_data( new ArrayContainer( $persistence->get_event_data() ) );
		return $event;
	}

	/** @return \WPDesk\ShopMagic\Action\Action[] */
	private function get_actions_for_automation( PersistentAutomation $persistence ): array {
		$actions = [];
		foreach ( $persistence->get_actions_data() as $action_data ) {
			$actions[] = $this->action_factory->create_action(
				$action_data['_action'],
				new ArrayContainer( $action_data )
			);
		}

		return $actions;
	}

	/** @return array{post_type: string, post_status: string, posts_per_page: int} */
	private function all_active_automations(): array {
		return [
			'post_type'      => 'shopmagic_automation',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		];
	}
}
