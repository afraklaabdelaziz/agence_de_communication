<?php

namespace WPDesk\ShopMagic\Database\Updates;

use WPDesk\ShopMagic\Database\DatabaseSchema;

final class Version_42 extends \WPDesk\ShopMagic\Database\Abstraction\Schema\TableUpdate {

	public static function get_name(): string {
		return DatabaseSchema::get_outcome_logs_table_name();
	}

	public static function get_version(): int {
		return 42;
	}

	public function update( \wpdb $wpdb ): bool {
		$table_name = self::get_name();

		$sql    = "ALTER TABLE {$table_name} MODIFY `note` TEXT NOT NULL";
		$result = $wpdb->query( $sql );
		$sql    = "ALTER TABLE {$table_name} ADD `note_context` TEXT";

		return $result && $wpdb->query( $sql );
	}
}
