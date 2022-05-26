<?php

namespace WPDesk\ShopMagic\Database\Updates;

use WPDesk\ShopMagic\Database\Abstraction\Schema\TableUpdate;
use WPDesk\ShopMagic\Database\DatabaseSchema;

final class Version_38 extends TableUpdate {

	public static function get_version(): int {
		return 38;
	}

	public function update( \wpdb $wpdb ): bool {
		$charset_collate    = $wpdb->get_charset_collate();
		$table_outcome_name = self::get_name();
		$result             = $wpdb->query( "DROP TABLE IF EXISTS {$table_outcome_name};" );

		$sql = "CREATE TABLE IF NOT EXISTS {$table_outcome_name} (
			id int NOT NULL AUTO_INCREMENT,
			execution_id varchar(48) NOT NULL,
			automation_id int NOT NULL,
			automation_name varchar(255) NOT NULL,
			action_index varchar(255) NOT NULL,
			action_name varchar(255) NOT NULL,
			customer_id int,
			guest_id int,
			customer_email varchar(255) NOT NULL,
			success tinyint(1),
			finished tinyint(1) NOT NULL DEFAULT FALSE,
			created datetime NOT NULL,
			updated datetime NOT NULL,
			PRIMARY KEY  (id)
		) {$charset_collate};";

		return $result && $wpdb->query( $sql );
	}

	public static function get_name(): string {
		return DatabaseSchema::get_automation_outcome_table_name();
	}
}
