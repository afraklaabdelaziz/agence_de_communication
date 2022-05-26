<?php
declare( strict_types=1 );

namespace WPDesk\ShopMagic\Database\Updates;

use WPDesk\ShopMagic\Database\DatabaseSchema;

/**
 * Force update database to mitigate malfunctions introduced with Version 43 and 44.
 * Previously Marketing Lists table was using Unique key for (email, list_id) combination.
 * This caused issues as key was too large for most of the databases.
 * From this update Version_43 class installs table without Unique key.
 *
 * If the table is already created, just bump version number.
 */
final class Version_45 extends \WPDesk\ShopMagic\Database\Abstraction\Schema\Update {

	public static function get_version(): int {
		return 45;
	}

	public function update( \wpdb $wpdb ): bool {
		if ( ! $this->needs_force_update() ) {
			return true;
		}

		$version_43 = new Version_43();
		$no_errors  = $version_43->update( $wpdb );

		if ( $no_errors ) {
			delete_option( Version_44::UPDATE_REQUIRED );
			$version_44 = new Version_44();
			$no_errors  = $version_44->update( $wpdb );
		}

		return $no_errors;
	}

	public function needs_force_update(): bool {
		global $wpdb;
		$table_name = DatabaseSchema::get_marketing_lists_table_name();
		return $wpdb->query( "SELECT * FROM {$table_name} LIMIT 1" ) === false;
	}
}
