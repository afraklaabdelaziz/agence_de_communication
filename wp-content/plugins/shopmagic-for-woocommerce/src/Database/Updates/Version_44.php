<?php

namespace WPDesk\ShopMagic\Database\Updates;

use WPDesk\ShopMagic\Database\DatabaseSchema;

final class Version_44 extends \WPDesk\ShopMagic\Database\Abstraction\Schema\Update {

	const UPDATE_REQUIRED = 'shopmagic_subscribers_update_required';

	public static function get_version(): int {
		return 44;
	}

	public function update( \wpdb $wpdb ): bool {
		$table_name     = DatabaseSchema::get_marketing_lists_table_name();
		$old_table_name = DatabaseSchema::get_optin_email_table_name();

		$time = microtime( true );
		if ( ! get_option( self::UPDATE_REQUIRED ) ) {
			update_option( self::UPDATE_REQUIRED, $time, true );
			if ( get_option( self::UPDATE_REQUIRED ) === $time ) {
				$sql = "INSERT INTO {$table_name} (list_id, email, active, type, created, updated)
				(SELECT p1.communication_type as list_id,
					p1.email as email,
					p1.subscribe as active,
					type,
					initial as created,
					max(p1.created) as updated
				FROM
					`{$old_table_name}` as p1
				JOIN (
					SELECT communication_type, email, min(created) as initial
					FROM `${old_table_name}`
					GROUP BY email, communication_type
				) as p2 ON p1.email = p2.email AND p1.communication_type = p2.communication_type
				JOIN (
					SELECT post_id, (case when meta_value = 'opt_out' then 0 else 1 end) as type FROM `{$wpdb->postmeta}`
					WHERE meta_key = 'type'
				) as m1 ON m1.post_id = p1.communication_type
				WHERE p1.active = 1
				GROUP BY p1.email, p1.communication_type)
				ON DUPLICATE KEY UPDATE active=VALUES(active);";

				return (bool) $wpdb->query( $sql );
			}
		}

		return true;
	}
}
