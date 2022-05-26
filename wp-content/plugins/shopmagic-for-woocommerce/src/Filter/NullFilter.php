<?php

namespace WPDesk\ShopMagic\Filter;

use WPDesk\ShopMagic\DataSharing\DataProvider;
use WPDesk\ShopMagic\DataSharing\Traits\DataReceiverAsProtectedField;
use WPDesk\ShopMagic\DataSharing\Traits\FieldsDataAsProtectedField;

/**
 * Filter that lets everything go.
 *
 * @package WPDesk\ShopMagic\Filters
 */
final class NullFilter implements Filter {
	use DataReceiverAsProtectedField;
	use FieldsDataAsProtectedField;

	/**
	 * @inheritDoc
	 */
	public function get_fields() {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function passed() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_group_slug() {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Filter does not exist', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_required_data_domains() {
		return [];
	}
}
