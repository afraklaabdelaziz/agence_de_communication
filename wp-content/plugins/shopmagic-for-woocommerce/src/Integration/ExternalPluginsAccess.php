<?php

namespace WPDesk\ShopMagic\Integration;

use Psr\Log\LoggerInterface;
use WPDesk\ShopMagic\ActionExecution\ExecutionCreator\ExecutionCreator;
use WPDesk\ShopMagic\ActionExecution\ExecutionCreator\ExecutionCreatorContainer;
use WPDesk\ShopMagic\Automation\AutomationFactory;
use WPDesk\ShopMagic\Automation\AutomationValidator;
use WPDesk\ShopMagic\AutomationOutcome\OutcomeInformationRepository;
use WPDesk\ShopMagic\AutomationOutcome\OutcomeTable;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Customer\CustomerProvider;
use WPDesk\ShopMagic\Guest\GuestFactory;
use WPDesk\ShopMagic\Placeholder\PlaceholderFactory2;

/**
 * Class that grants access to some internal classes and info about ShopMagic to external plugins.
 *
 * @package WPDesk\ShopMagic\Integration
 * @final todo: explicitly mark as final in 3.0
 */
class ExternalPluginsAccess {

	/** @var string */
	private $version;

	/** @var GuestFactory */
	private $guest_factory;

	/** @var CustomerFactory */
	private $customer_factory;

	/** @var CustomerProvider */
	private $customer_provider;

	/** @var OutcomeInformationRepository */
	private $outcome_information;

	/** @var LoggerInterface */
	private $logger;

	/** @var AutomationFactory */
	private $automation_factory;

	/** @var ExecutionCreatorContainer */
	private $executor_factory;

	/** @var PlaceholderFactory2 */
	private $placeholder_factory;

	/** @var OutcomeTable */
	private $outcome_table;

	public function __construct(
		string $version,
		GuestFactory $guest_factory,
		CustomerFactory $customer_factory,
		CustomerProvider $customer_provider,
		LoggerInterface $logger,
		OutcomeInformationRepository $outcome_information,
		AutomationFactory $automation_factory,
		ExecutionCreatorContainer $executor_factory,
		PlaceholderFactory2 $placeholder_factory,
		OutcomeTable $outcome_table
	) {
		$this->version             = $version;
		$this->guest_factory       = $guest_factory;
		$this->customer_factory    = $customer_factory;
		$this->customer_provider   = $customer_provider;
		$this->logger              = $logger;
		$this->outcome_information = $outcome_information;
		$this->automation_factory  = $automation_factory;
		$this->executor_factory    = $executor_factory;
		$this->placeholder_factory = $placeholder_factory;
		$this->outcome_table       = $outcome_table;
	}

	/** @return void */
	public function set_validator( AutomationValidator $validator ) {
		$this->automation_factory->set_validator( $validator );
	}

	/**
	 * @todo refine this comment!
	 * You can extend the logic of how ShopMagic enqueues action to execute.
	 * WARNING: be cautious about adding the strategies because those are executed in order of addition.
	 * I.e. if you add two executors, which both returns `true` in `should_create` method, only the first one will execute.
	 *
	 * @see QueueExecutionStrategyFactory::create_strategy() for implementation details
	 *
	 * @example Delayed Actions add-on
	 *
	 * @param ExecutionCreator $factory
	 *
	 * @return void
	 */
	public function add_execution_creator( ExecutionCreator $factory ) {
		$this->executor_factory->add_execution_creator( $factory );
	}

	public function get_placeholder_factory(): PlaceholderFactory2 {
		return $this->placeholder_factory;
	}

	public function get_customer_factory(): CustomerFactory {
		return $this->customer_factory;
	}

	public function get_version(): string {
		return $this->version;
	}

	public function get_logger(): LoggerInterface {
		return $this->logger;
	}

	public function get_customer_provider(): CustomerProvider {
		return $this->customer_provider;
	}

	public function get_guest_factory(): GuestFactory {
		return $this->guest_factory;
	}

	public function get_outcome_information(): OutcomeInformationRepository {
		return $this->outcome_information;
	}

	public function get_outcome_table(): OutcomeTable {
		return $this->outcome_table;
	}
}
