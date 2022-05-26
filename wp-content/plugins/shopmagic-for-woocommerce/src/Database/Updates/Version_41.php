<?php

namespace WPDesk\ShopMagic\Database\Updates;

use WPDesk\ShopMagic\Database\Abstraction\Schema\TableUpdate;
use WPDesk\ShopMagic\Database\DatabaseSchema;

final class Version_41 extends TableUpdate {

	public static function get_name(): string {
		return DatabaseSchema::get_guest_meta_table_name();
	}

	public static function get_version(): int {
		return 41;
	}

	public function update( \wpdb $wpdb ): bool {
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = self::get_name();
		$result          = $wpdb->query( "DROP TABLE IF EXISTS {$table_name};" );

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
				meta_id int NOT NULL AUTO_INCREMENT,
				guest_id int NOT NULL,
				meta_key varchar(255) NOT NULL,
				meta_value longtext NOT NULL,
				PRIMARY KEY  (meta_id),
				KEY guest_id (guest_id)
			) {$charset_collate};";

		return $result && $wpdb->query( $sql );
	}
}
