<?php
// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
namespace WPDesk\ShopMagic\AutomationOutcome;

use WPDesk\ShopMagic\Action\Action;
use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Customer\CustomerFactory;
use WPDesk\ShopMagic\Database\DatabaseSchema;
use WPDesk\ShopMagic\Database\RepositoryOrderingTrait;
use WPDesk\ShopMagic\DataSharing\DataLayer;
use WPDesk\ShopMagic\Event\Event;
use WPDesk\ShopMagic\Guest\GuestDAO;
use WPDesk\ShopMagic\Helper\WordPressFormatHelper;

/**
 * Kind of repository pattern with some methods to insert outcomes.
 *
 * @todo use database abstraction in 3.0
 * @deprecated 2.34 Use OutcomeTable
 */
final class OutcomeReposistory implements OutcomeInformationRepository {
	use RepositoryOrderingTrait;

	/** @var \wpdb $wpdb */
	private $wpdb;

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/** @return void */
	public function log_note( string $execution_id, string $note, array $context = [] ) {
		$now = time();
		$this->insert_note( $execution_id, $note, $context, $now );

		$outcome_table = DatabaseSchema::get_automation_outcome_table_name();
		$this->wpdb->update(
			$outcome_table,
			[
				'updated' => WordPressFormatHelper::datetime_as_mysql( $now ),
			],
			[ 'execution_id' => $execution_id ]
		);
	}

	/** @return void */
	private function insert_note( string $execution_id, string $note, array $context, int $now ) {
		$log_table = DatabaseSchema::get_outcome_logs_table_name();
		$this->wpdb->insert(
			$log_table,
			[
				'execution_id' => $execution_id,
				'note'         => $note,
				'note_context' => json_encode( $context ),
				'created'      => WordPressFormatHelper::datetime_as_mysql( $now ),
			]
		);
	}

	/** @return void */
	public function finish_outcome( string $execution_id, bool $success, string $note = null, array $context = [] ) {
		$now = time();
		if ( ! empty( $note ) ) {
			$this->insert_note( $execution_id, $note, $context, $now );
		}

		$outcome_table = DatabaseSchema::get_automation_outcome_table_name();
		$this->wpdb->update(
			$outcome_table,
			[
				'finished' => true,
				'success'  => $success,
				'updated'  => WordPressFormatHelper::datetime_as_mysql( $now ),
			],
			[ 'id' => $execution_id ]
		);
	}

	/**
	 * Run to initialize outcome. Every outcome have to be initialized first.
	 *
	 * @return int Unique outcome id to log future notices and save finish.
	 */
	public function prepare_for_outcome( Automation $automation, Event $event, Action $action, int $action_index ): int {
		$unique_id = uniqid( 'execute_', true );

		$provided_data = ( new DataLayer( $event ) )->get_provided_data();
		/** @var Customer $customer */
		$customer = $provided_data[ Customer::class ] ?? null;
		if ( ! $customer instanceof Customer ) {
			// @TODO: log this and should be impossible in 3.0
			$customer = ( new CustomerFactory() )->create_null();
			$id       = null;
		} else {
			$id = CustomerFactory::convert_customer_guest_id_to_number( $customer->get_id() );
		}

		$outcome_table = DatabaseSchema::get_automation_outcome_table_name();
		$this->wpdb->insert(
			$outcome_table,
			[
				'execution_id'    => $unique_id,
				'automation_id'   => $automation->get_id(),
				'automation_name' => $automation->get_name(),
				'action_index'    => $action_index,
				'action_name'     => $action->get_name(),
				'customer_id'     => ! $customer->is_guest() ? $id : null,
				'guest_id'        => $customer->is_guest() ? $id : null,
				'customer_email'  => $customer->get_email(),
				'success'         => null,
				'finished'        => false,
				'created'         => gmdate( 'Y-m-d G:i:s' ),
				'updated'         => gmdate( 'Y-m-d G:i:s' ),
			]
		);

		return $this->wpdb->insert_id;
	}

	/** @return void */
	public function delete_outcome( int $execution_id ) {
		$outcome_table = DatabaseSchema::get_automation_outcome_table_name();
		$this->wpdb->delete( $outcome_table, [ 'id' => $execution_id ] );
	}

	/**
	 * @return int
	 */
	public function get_count( array $where ): int {
		$where      = array_merge( $where, [ 'finished' => true ] );
		$where_sql  = $this->where_array_to_sql( $where );
		$table_name = DatabaseSchema::get_automation_outcome_table_name();
		$sql        = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql}";

		return (int) $this->wpdb->get_var( $sql );
	}

	/**
	 * Guest has id in special format so we can detect it and search for him using different column.
	 *
	 * @param array $filter
	 *
	 * @return array
	 */
	private function inject_guest_support( array $filter ): array {
		if ( isset( $filter['customer_id'] ) && CustomerFactory::is_customer_guest_id( $filter['customer_id'] ) ) {
			$filter['guest_id'] = CustomerFactory::convert_customer_guest_id_to_number( $filter['customer_id'] );
			unset( $filter['customer_id'] );
		}

		return $filter;
	}

	/**
	 * @param int[]           $item item from database.
	 *
	 * @throws \Exception
	 */
	private function create_customer_from_outcome_row( CustomerFactory $customer_factory, array $item ): Customer {
		if ( ! empty( $item['customer_id'] ) ) {
			return $customer_factory->create_from_id( $item['customer_id'] );
		}
		if ( ! empty( $item['guest_id'] ) ) {
			global $wpdb;
			$guest_dao = new GuestDAO( $wpdb );

			return $customer_factory->create_from_guest( $guest_dao->get_by_id( $item['guest_id'] ) );
		}

		// fallback - when guest conversion is in progress.
		return $customer_factory->create_null();
	}

	/**
	 * @param array $where
	 * @param array $order
	 * @param int   $limit
	 * @param int   $offset
	 *
	 * @return \Generator<OutcomeInTable>
	 * @throws \Exception
	 */
	public function get_all( array $where, array $order, $limit = 10, $offset = 0 ) {
		$table_name      = DatabaseSchema::get_automation_outcome_table_name();
		$table_name_logs = DatabaseSchema::get_outcome_logs_table_name();
		$where           = array_merge( $this->inject_guest_support( $where ), [ 'finished' => true ] );
		$where_sql       = $this->where_array_to_sql( $where );
		$order_sql       = $this->order_array_to_sql( $order );
		$limit_sql       = $this->limit_offset_to_sql( $limit, $offset );

		$sql = "
			SELECT
				t.id, automation_id, automation_name, customer_id, guest_id, customer_email, action_name, action_index, updated, success,
				COUNT({$table_name_logs}.id) as log_count
			FROM
				{$table_name} t
				LEFT OUTER JOIN {$table_name_logs} ON {$table_name_logs}.execution_id = t.id
			WHERE
				{$where_sql}
            GROUP BY
				t.id
				{$order_sql} {$limit_sql}";

		$customer_factory = new CustomerFactory();

		foreach ( $this->wpdb->get_results( $sql, ARRAY_A ) as $item ) {
			$customer = $this->create_customer_from_outcome_row( $customer_factory, $item );

			yield new OutcomeInTable(
				$item['id'],
				$item['automation_id'],
				$item['automation_name'],
				$customer,
				$item['customer_email'],
				empty( $item['action_name'] ) ? '#' . ( $item['action_index'] + 1 ) : $item['action_name'],
				$item['success'],
				new \DateTimeImmutable( $item['updated'] ),
				$item['log_count'] > 0
			);
		}
	}

	public function get_by_id( int $id ): SingleOutcome {
		$table_name   = DatabaseSchema::get_automation_outcome_table_name();
		$sql          = "
			SELECT
       			id, execution_id, updated, success
       		FROM
       		     {$table_name}
            WHERE
                 id = %s
            LIMIT 1";
		$outcome_data = $this->wpdb->get_row( $this->wpdb->prepare( $sql, $id ), ARRAY_A );

		$table_name = DatabaseSchema::get_outcome_logs_table_name();
		$sql        = "
			SELECT
       			note, note_context, created
       		FROM
       		     {$table_name}
            WHERE
                 execution_id = %s";

		$outcome_logs_data = $this->wpdb->get_results( $this->wpdb->prepare( $sql, $outcome_data['id'] ), ARRAY_A );
		$logs              = [];
		foreach ( $outcome_logs_data as $log_data ) {
			$context = json_decode( $log_data['note_context'], true );
			if ( ! is_array( $context ) ) {
				$context = [];
			}
			$logs[] = new SingleOutcomeLog(
				new \DateTimeImmutable( $log_data['created'] ),
				$log_data['note'],
				$context
			);
		}

		return new SingleOutcome(
			$outcome_data['id'],
			$logs,
			new \DateTimeImmutable( $outcome_data['updated'] ),
			(bool) $outcome_data['success']
		);
	}

	/**
	 * @deprecated Use OutcomeReposistory::get_done_automation_count_per_customer()
	 */
	public function get_done_automation_count_for_customer( int $automation_id, string $customer_id ): int {
		$table_name = DatabaseSchema::get_automation_outcome_table_name();
		$sql        = "
			SELECT
       			COUNT(*)
       		FROM
       		     {$table_name}
            WHERE
                 automation_id = %d AND
                 customer_id = \"%s\"";

		return (int) $this->wpdb->get_var( $this->wpdb->prepare( $sql, $automation_id, $customer_id ) );
	}

	public function get_done_automation_count_per_customer( int $automation_id, Customer $customer ): int {
		$table_name = DatabaseSchema::get_automation_outcome_table_name();
		$sql        = "
			SELECT
       			COUNT(*)
       		FROM
       		     {$table_name}
            WHERE
                 automation_id = %d AND %0s = %d";

		if ( $customer->is_guest() ) {
			$column      = 'guest_id';
			$customer_id = CustomerFactory::convert_customer_guest_id_to_number( $customer->get_id() );
		} else {
			$column      = 'customer_id';
			$customer_id = $customer->get_id();
		}

		return (int) ( $this->wpdb->get_var( $this->wpdb->prepare( $sql, $automation_id, $column, $customer_id ) ) );
	}

	public function get_done_automation_count_with_time( int $automation_id, string $customer_id, int $in_days ): int {
		$newer_than = WordPressFormatHelper::datetime_as_mysql( time() - $in_days * DAY_IN_SECONDS );

		$table_name = DatabaseSchema::get_automation_outcome_table_name();
		$sql        = "
			SELECT
       			COUNT(*)
       		FROM
       		     {$table_name}
            WHERE
                 automation_id = %d AND
                 customer_id = \"%s\" AND
				 created >= \"%s\"";

		return (int) $this->wpdb->get_var( $this->wpdb->prepare( $sql, $automation_id, $customer_id, $newer_than ) );
	}
}

