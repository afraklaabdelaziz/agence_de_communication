<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Traits;

use WPDesk\ShopMagic\Helper\WooCommerceFormatHelper;

/**
 * Provides functions to format country to WP i18n.
 *
 * @package WPDesk\ShopMagic\Placeholder\Builtin\Traits
 *
 * @deprecated use Helpers
 */
trait CountryFormatHelper {
	/**
	 * @param string $shortcut ie. PL
	 *
	 * @return string ie. Poland
	 */
	private function country_full_name( $shortcut ) {
		return WooCommerceFormatHelper::country_full_name( $shortcut );
	}
}
