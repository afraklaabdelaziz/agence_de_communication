<?php

namespace WPDesk\ShopMagic\Filter\Builtin;

use WPDesk\ShopMagic\Event\EventFactory2;
use WPDesk\ShopMagic\Filter\FilterUsingComparisionTypes;

abstract class OrderFilter extends FilterUsingComparisionTypes {
	/**
	 * @inheritDoc
	 */
	public function get_group_slug() {
		return EventFactory2::GROUP_ORDERS;
	}

	/**
	 * @inheritDoc
	 */
	public function get_required_data_domains() {
		return [ \WC_Order::class ];
	}
}
