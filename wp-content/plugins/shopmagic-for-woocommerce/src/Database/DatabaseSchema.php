<?php
declare( strict_types=1 );

namespace WPDesk\ShopMagic\Database;

use WPDesk\ShopMagic\Database\Abstraction\Schema\AbstractSchema;

/**
 * Install and update database on plugin activation.
 */
final class DatabaseSchema extends AbstractSchema {
	const FLUSH_REQUIRED = 'shopmagic_flush_required';

	protected function get_updates(): array {
		return [
			Updates\Version_37::class,
			Updates\Version_38::class,
			Updates\Version_39::class,
			Updates\Version_40::class,
			Updates\Version_41::class,
			Updates\Version_42::class,
			Updates\Version_43::class,
			Updates\Version_44::class,
			Updates\Version_45::class,
		];
	}

	protected function get_log_option_name(): string {
		return 'shopmagic_db_log';
	}

	protected function get_version_option_name(): string {
		return 'shopmagic_db_version';
	}

	public static function get_marketing_lists_table_name(): string {
		global $wpdb;

		return $wpdb->prefix . 'shopmagic_marketing_lists';
	}

	public static function get_optin_email_table_name(): string {
		global $wpdb;

		return $wpdb->prefix . 'shopmagic_optin_email';
	}

	public static function get_automation_outcome_table_name(): string {
		global $wpdb;

		return $wpdb->prefix . 'shopmagic_automation_outcome';
	}

	public static function get_guest_table_name(): string {
		global $wpdb;

		return $wpdb->prefix . 'shopmagic_guest';
	}

	public static function get_guest_meta_table_name(): string {
		global $wpdb;

		return $wpdb->prefix . 'shopmagic_guest_meta';
	}

	public static function get_outcome_logs_table_name(): string {
		global $wpdb;

		return $wpdb->prefix . 'shopmagic_automation_outcome_logs';
	}

}
