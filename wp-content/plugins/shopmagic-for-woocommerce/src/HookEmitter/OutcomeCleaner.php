<?php
declare(strict_types=1);

namespace WPDesk\ShopMagic\HookEmitter;

use WPDesk\ShopMagic\AutomationOutcome\Meta\OutcomeMetaFactory;
use WPDesk\ShopMagic\AutomationOutcome\Meta\OutcomeMetaTable;
use WPDesk\ShopMagic\AutomationOutcome\Outcome;
use WPDesk\ShopMagic\Database\Abstraction\DAO\Collection;
use WPDesk\ShopMagic\Database\DatabaseSchema;
use WPDesk\ShopMagic\Helper\WordPressFormatHelper;

/**
 * Removes outcomes from database.
 */
final class OutcomeCleaner extends RecurringCleaner {
	protected function get_items_to_clean(): Collection {
		$cut_time = ( new \DateTimeImmutable( 'now', wp_timezone() ) )
			->modify( RecurringCleaner::DEFAULT_EXPIRATION_TIME );

		return $this->table->get_all(
			[
				[
					'field'     => 'updated',
					'condition' => '<=',
					'value'     => WordPressFormatHelper::datetime_as_mysql( $cut_time ),
				],

			]
		);
	}

	/**
	 * @param Collection<Outcome> $items
	 *
	 * @return void
	 */
	protected function post_clean_hook( Collection $items ) {
		global $wpdb;

		$ids_to_clean = [];
		foreach ( $items as $item ) {
			$ids_to_clean[] = $item->get_id();
		}

		$count        = count( $ids_to_clean );
		$placeholders = implode( ', ', array_fill( 0, $count, '%d' ) );
		$meta_table   = DatabaseSchema::get_outcome_logs_table_name();
		// phpcs:disable WordPress.DB
		$result = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $meta_table WHERE `execution_id` IN ($placeholders)",
				$ids_to_clean
			)
		);
		// phpcs:enable
	}
}
