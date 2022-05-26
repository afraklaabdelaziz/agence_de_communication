<?php

namespace WPDesk\ShopMagic\Database\Updates;

use WPDesk\ShopMagic\Database\DatabaseSchema;

final class Version_40 extends \WPDesk\ShopMagic\Database\Abstraction\Schema\TableUpdate {

	public static function get_name(): string {
		return DatabaseSchema::get_guest_table_name();
	}

	public static function get_version(): int {
		return 40;
	}

	public function update( \wpdb $wpdb ): bool {
		$charset_collate  = $wpdb->get_charset_collate();
		$table_guest_name = self::get_name();
		$result           = $wpdb->query( "DROP TABLE IF EXISTS {$table_guest_name};" );

		$sql = "CREATE TABLE IF NOT EXISTS {$table_guest_name} (
			id int NOT NULL AUTO_INCREMENT,
			email varchar(255) NOT NULL,
			tracking_key varchar(32) NOT NULL,
			created datetime NOT NULL,
			updated datetime NOT NULL,
			PRIMARY KEY  (id)
		) {$charset_collate};";

		return $result && $wpdb->query( $sql );
	}
}
