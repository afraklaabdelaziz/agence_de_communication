<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin;

use WPDesk\ShopMagic\Placeholder\BasicPlaceholder;

abstract class WooCommerceOrderNoteBasedPlaceholder extends BasicPlaceholder {
	public function get_slug() {
		return 'order_note';
	}

	/**
	 * @inheritDoc
	 */
	public function get_required_data_domains() {
		return [ \WP_Comment::class, \WC_Order::class ];
	}

	protected function get_order_note(): \WP_Comment {
		return $this->provided_data[ \WP_Comment::class ];
	}
}
