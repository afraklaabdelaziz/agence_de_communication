<?php

namespace WPDesk\ShopMagic\Database\Updates;

final class Version_37 extends \WPDesk\ShopMagic\Database\Abstraction\Schema\TableUpdate {

	public static function get_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'shopmagic_optin_email';
	}

	public static function get_version(): int {
		return 37;
	}

	public function update( \wpdb $wpdb ): bool {
		$table_name      = self::get_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id int NOT NULL AUTO_INCREMENT,
			email varchar(255) NOT NULL,
			communication_type int NOT NULL,
			created datetime NOT NULL,
			subscribe tinyint(1) NOT NULL,
			active tinyint(1) NOT NULL DEFAULT TRUE,
			PRIMARY KEY  (id)
		) {$charset_collate};";

		return (bool) $wpdb->query( $sql );
	}
}
