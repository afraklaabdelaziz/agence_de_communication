<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\HookEmitter;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\ShopMagic\Database\Abstraction\DAO;
use WPDesk\ShopMagic\Database\Abstraction\DAO\Collection;

/**
 * Allows deleting items from database on recurring schedule;
 */
abstract class RecurringCleaner implements Hookable, LoggerAwareInterface {

	use LoggerAwareTrait;

	const DEFAULT_EXPIRATION_TIME = '-30 days';

	/** @var DAO\PersistenceGateway */
	protected $table;

	final public function __construct( DAO\PersistenceGateway $table, LoggerInterface $logger ) {
		$this->table  = $table;
		$this->logger = $logger;
	}

	final public function hooks() {
		add_action( 'shopmagic/core/cron/weekly', [ $this, 'clean_resources' ] );
	}

	/**
	 * @return void
	 * @throws \Exception
	 * @internal
	 */
	final public function clean_resources() {
		global $wpdb;
		$items_to_clean = $this->get_items_to_clean();
		if ( $items_to_clean->is_empty() ) {
			return;
		}

		$wpdb->query( 'START TRANSACTION' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		foreach ( $items_to_clean as $item ) {
			$this->table->delete_by_primary( (string) $item->get_id() );
		}
		$this->post_clean_hook( $items_to_clean );
		$wpdb->query( 'COMMIT' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}

	/** @return Collection<DAO\Item> */
	abstract protected function get_items_to_clean(): Collection;

	/**
	 * Overwrite this method in child class if you need to perform additional actions.
	 * I.e. cleaning meta table associated with main table.
	 * Hook if fired before database transaction commit.
	 *
	 * @param Collection<DAO\Item> $items
	 *
	 * @return void
	 */
	protected function post_clean_hook( Collection $items ) {}
}
