<?php

namespace WPDesk\ShopMagic\Filter\Builtin;

use WPDesk\ShopMagic\Event\EventFactory2;
use WPDesk\ShopMagic\Filter\FilterUsingComparisionTypes;

abstract class OrderNoteFilter extends FilterUsingComparisionTypes {
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
		return [ \WP_Comment::class ];
	}

	protected function get_order_note(): \WP_Comment {
		return $this->provided_data[ \WP_Comment::class ];
	}
}
