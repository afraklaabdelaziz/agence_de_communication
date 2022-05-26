<?php

namespace WPDesk\ShopMagic\Action;

use WPDesk\ShopMagic\Customer\Customer;

abstract class CustomerAction extends BasicAction {
	/**
	 * @inheritDoc
	 */
	public function get_required_data_domains() {
		return [ Customer::class ];
	}
}
