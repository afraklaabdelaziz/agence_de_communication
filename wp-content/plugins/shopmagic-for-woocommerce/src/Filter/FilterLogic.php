<?php

namespace WPDesk\ShopMagic\Filter;

use WPDesk\ShopMagic\DataSharing\DataReceiver;

/**
 * @package WPDesk\ShopMagic\Filters
 */
interface FilterLogic extends DataReceiver {
	/**
	 * Checks if filter allows event to be executed.
	 *
	 * @return bool True if event can be executed.
	 */
	public function passed();
}
