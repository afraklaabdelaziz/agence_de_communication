<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Traits;

use WPDesk\ShopMagic\Helper\WordPressFormatHelper;

/**
 * Provides functions to format date to WP i18n.
 *
 * @package WPDesk\ShopMagic\Placeholder\Builtin\Traits
 *
 * @deprecated use Helpers
 */
trait DateFormatHelper {
	/**
	 * @param string|\WC_DateTime|int|null $date
	 *
	 * @return string
	 */
	private function format_date( $date ) {
		return WordPressFormatHelper::format_wp_date( $date );
	}
}
