<?php

namespace WPDesk\ShopMagic\Helper;

/**
 * Helper for functions in wp-admin/includes/plugin.php and wp-includes/plugin.php that maybe works.
 *
 * @package WPDesk\ShopMagic\Helper
 */
final class WordPressPluggableHelper {
	public static function is_plugin_active_for_network( string $plugin ): bool {
		if ( ! is_multisite() ) {
			return false;
		}

		$plugins = get_site_option( 'active_sitewide_plugins' );
		if ( isset( $plugins[ $plugin ] ) ) {
			return true;
		}

		return false;
	}

	public static function is_plugin_active( string $plugin ): bool {
		return in_array( $plugin, (array) get_option( 'active_plugins', [] ), true ) || self::is_plugin_active_for_network( $plugin );
	}
}
