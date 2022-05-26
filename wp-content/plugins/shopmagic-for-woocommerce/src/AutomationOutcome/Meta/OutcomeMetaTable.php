<?php

namespace WPDesk\ShopMagic\AutomationOutcome\Meta;

use Psr\Log\LoggerInterface;
use WPDesk\ShopMagic\Database\Abstraction\DAO;
use WPDesk\ShopMagic\Database\DatabaseSchema;

/**
 * Representation of Outcome Logs table.
 */
final class OutcomeMetaTable extends \WPDesk\ShopMagic\Database\Abstraction\AbstractSingleTable {

	/** @var DAO\ItemFactory */
	private $factory;

	public function __construct( DAO\ItemFactory $factory, LoggerInterface $logger = null ) {
		parent::__construct( $logger );
		$this->factory = $factory;
	}

	protected function get_name(): string {
		return DatabaseSchema::get_outcome_logs_table_name();
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
			'note',
			'created',
			'note_context',
		];
	}
}
