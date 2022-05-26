<?php

namespace WPDesk\ShopMagic\Filter\Builtin;

use WPDesk\ShopMagic\Customer\Customer;
use WPDesk\ShopMagic\Event\EventFactory2;
use WPDesk\ShopMagic\Filter\FilterUsingComparisionTypes;

abstract class CustomerFilter extends FilterUsingComparisionTypes {
	/**
	 * @inheritDoc
	 */
	public function get_group_slug() {
		return EventFactory2::GROUP_USERS;
	}

	/**
	 * @inheritDoc
	 */
	public function get_required_data_domains() {
		return [ Customer::class ];
	}
}
