<?php

namespace WPDesk\ShopMagic\Filter\Builtin\Customer;

use WPDesk\ShopMagic\Filter\Builtin\CustomerFilter;
use WPDesk\ShopMagic\Filter\ComparisionType\ComparisionType;
use WPDesk\ShopMagic\Filter\ComparisionType\IntegerType;

class CustomerIdFilter extends CustomerFilter {
	public function get_name(): string {
		return __( 'Customer - ID', 'shopmagic-for-woocommerce' );
	}

	public function passed(): bool {
		if ( $this->get_customer()->is_guest() ) {
			return false;
		}

		return $this->get_type()->passed(
			$this->fields_data->get( IntegerType::VALUE_KEY ),
			$this->fields_data->get( IntegerType::CONDITION_KEY ),
			(int) $this->get_customer()->get_id()
		);
	}

	protected function get_type(): ComparisionType {
		return new IntegerType();
	}

}
