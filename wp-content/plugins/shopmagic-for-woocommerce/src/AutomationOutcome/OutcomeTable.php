<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\AutomationOutcome;

use Psr\Log\LoggerInterface;
use WPDesk\ShopMagic\Database\Abstraction\AbstractSingleTable;
use WPDesk\ShopMagic\Database\Abstraction\DAO;
use WPDesk\ShopMagic\Database\DatabaseSchema;

final class OutcomeTable extends AbstractSingleTable {

	/** @var DAO\ItemFactory */
	private $factory;

	public function __construct( DAO\ItemFactory $factory, LoggerInterface $logger = null ) {
		parent::__construct( $logger );
		$this->factory = $factory;
	}

	protected function get_name(): string {
		return DatabaseSchema::get_automation_outcome_table_name();
	}

	protected function get_factory(): DAO\ItemFactory {
		return $this->factory;
	}

	protected function get_primary_key(): array {
		return [ 'id' ];
	}

	protected function get_columns(): array {
		return [
			'id',
			'execution_id',
			'automation_id',
			'automation_name',
			'action_index',
			'action_name',
			'customer_id',
			'guest_id',
			'customer_email',
			'success',
			'finished',
			'created',
			'updated',
		];
	}
}
