<?php


namespace WPDesk\ShopMagic\Helper;

/**
 * Idea behind this helper is to facilitate various formatting methods that depends on WooCommerce.
 *
 * Access should be static to lower complexity. It can be refactored in the future when DI container is introduced.
 *
 * @package WPDesk\ShopMagic\Helper
 */
final class WooCommerceFormatHelper {
	/**
	 * @param string $shortcut ie. PL
	 *
	 * @return string ie. Poland
	 */
	public static function country_full_name( $shortcut ) {
		$countries = WC()->countries->get_countries();
		if ( isset( $countries[ $shortcut ] ) ) {
			return $countries[ $shortcut ];
		}

		return $shortcut;
	}
}
