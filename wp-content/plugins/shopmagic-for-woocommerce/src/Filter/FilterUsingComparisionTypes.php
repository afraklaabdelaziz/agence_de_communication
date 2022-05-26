<?php

namespace WPDesk\ShopMagic\Filter;

use WPDesk\ShopMagic\DataSharing\Traits\DataReceiverAsProtectedField;
use WPDesk\ShopMagic\DataSharing\Traits\FieldsDataAsProtectedField;
use WPDesk\ShopMagic\DataSharing\Traits\StandardWooCommerceDataProviderAccessors;
use WPDesk\ShopMagic\Filter\ComparisionType\ComparisionType;

abstract class FilterUsingComparisionTypes implements Filter {
	use DataReceiverAsProtectedField;
	use FieldsDataAsProtectedField;
	use StandardWooCommerceDataProviderAccessors;

	public function __clone() {
		$this->fields_data = null;
	}

	/**
	 * Using this method the filter can declare what comparision types are available.
	 * ie. for integer numbers and date stamps there will be different possibilities.
	 *
	 * @return ComparisionType
	 */
	abstract protected function get_type();

	public function get_description() {
		return '';
	}

	public function get_fields() {
		return $this->get_type()->get_fields();
	}
}
