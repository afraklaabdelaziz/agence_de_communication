<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin;

use WPDesk\ShopMagic\Placeholder\BasicPlaceholder;

abstract class WooCommerceOrderBasedPlaceholder extends BasicPlaceholder {
	/**
	 * @inheritDoc
	 */
	public function get_required_data_domains() {
		return [ \WC_Order::class ];
	}
}
