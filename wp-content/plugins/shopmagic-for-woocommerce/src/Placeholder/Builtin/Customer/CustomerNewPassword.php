<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Customer;

use WPDesk\ShopMagic\DataSharing\DataAccess\NewUserAccount;
use WPDesk\ShopMagic\Placeholder\BasicPlaceholder;


final class CustomerNewPassword extends BasicPlaceholder {

	public function get_slug() {
		return parent::get_slug() . '.new_password';
	}

	public function get_required_data_domains() {
		return [ NewUserAccount::class ];
	}

	/**
	 * @return NewUserAccount
	 */
	private function get_user_account() {
		return $this->provided_data[ NewUserAccount::class ];
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function value( array $parameters ) {
		return $this->get_user_account()->get_password();
	}
}
