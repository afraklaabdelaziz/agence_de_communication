<?php

declare( strict_types=1 );

namespace WPDesk\ShopMagic\Helper;

/**
 * Manages cookies data using wc methods.
 *
 * @package WPDesk\ShopMagic\Helper
 */
class WooCommerceCookies {
	public static function set( string $name, string $value, int $expire = 0 ): bool {
		wc_setcookie( $name, $value, $expire, is_ssl() );
		$_COOKIE[ $name ] = $value;

		return true;
	}

	public static function is_set( string $name ): bool {
		return isset( $_COOKIE[ $name ] );
	}

	public static function get( string $name ): string {
		return isset( $_COOKIE[ $name ] ) ? (string) $_COOKIE[ $name ] : '';
	}

	public static function clear( string $name ) {
		if ( isset( $_COOKIE[ $name ] ) ) {
			wc_setcookie( $name, '', time() - HOUR_IN_SECONDS, is_ssl() );
			unset( $_COOKIE[ $name ] );
		}
	}
}
