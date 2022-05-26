<?php

namespace WPDesk\ShopMagic\Database\Updates;

use WPDesk\ShopMagic\Database\DatabaseSchema;

final class Version_43 extends \WPDesk\ShopMagic\Database\Abstraction\Schema\TableUpdate {

	public static function get_name(): string {
		return DatabaseSchema::get_marketing_lists_table_name();
	}

	public static function get_version(): int {
		return 43;
	}

	public function update( \wpdb $wpdb ): bool {
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = self::get_name();

		$sql = "
		CREATE TABLE IF NOT EXISTS {$table_name} (
		    id      int unsigned NOT NULL AUTO_INCREMENT,
		    list_id int unsigned NOT NULL,
		    email   varchar(255) NOT NULL,
		    active  tinyint(1)   NOT NULL DEFAULT 1,
		    type    tinyint(1)   NOT NULL,
		    created datetime     NOT NULL,
		    updated datetime     NOT NULL,
		    PRIMARY KEY (id)
		) {$charset_collate};";

		return (bool) $wpdb->query( $sql );
	}
}
