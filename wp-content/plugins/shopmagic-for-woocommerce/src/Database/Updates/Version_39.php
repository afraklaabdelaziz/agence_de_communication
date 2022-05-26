<?php

namespace WPDesk\ShopMagic\Database\Updates;

use WPDesk\ShopMagic\Database\Abstraction\Schema\TableUpdate;
use WPDesk\ShopMagic\Database\DatabaseSchema;

final class Version_39 extends TableUpdate {

	public static function get_name(): string {
		return DatabaseSchema::get_outcome_logs_table_name();
	}

	public static function get_version(): int {
		return 39;
	}

	public function update( \wpdb $wpdb ): bool {
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = self::get_name();

		$result = $wpdb->query( "DROP TABLE IF EXISTS {$table_name};" );

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
				id int NOT NULL AUTO_INCREMENT,
				execution_id varchar(48) NOT NULL,
				note varchar(2048) NOT NULL,
				created datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY execution_id (execution_id)
			) {$charset_collate};";

		return $result && $wpdb->query( $sql );
	}
}
