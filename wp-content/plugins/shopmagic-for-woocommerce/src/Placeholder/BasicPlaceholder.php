<?php

namespace WPDesk\ShopMagic\Placeholder;

use WPDesk\ShopMagic\DataSharing\Traits\DataReceiverAsProtectedField;
use WPDesk\ShopMagic\DataSharing\Traits\StandardWooCommerceDataProviderAccessors;

abstract class BasicPlaceholder implements Placeholder {
	use DataReceiverAsProtectedField;
	use StandardWooCommerceDataProviderAccessors;

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return PlaceholderGroup::class_to_group( $this->get_required_data_domains() );
	}

	/**
	 * @inheritDoc
	 */
	public function get_supported_parameters() {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {}
}
