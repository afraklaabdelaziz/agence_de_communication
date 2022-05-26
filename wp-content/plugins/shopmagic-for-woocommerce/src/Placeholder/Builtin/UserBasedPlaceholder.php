<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin;

use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Placeholder\BasicPlaceholder;

/**
 * @TODO: change to CustomerBased
 *
 * @package WPDesk\ShopMagic\Placeholder\Builtin
 */
abstract class UserBasedPlaceholder extends BasicPlaceholder {
	/**
	 * @inheritDoc
	 */
	public function get_required_data_domains() {
		return [ Customer::class ];
	}
}
