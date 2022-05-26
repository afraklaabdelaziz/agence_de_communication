<?php

namespace WPDesk\ShopMagic\Filter\Builtin\Order;

use WPDesk\ShopMagic\Filter\Builtin\OrderNoteFilter;
use WPDesk\ShopMagic\Filter\ComparisionType\StringType;

final class OrderNoteContent extends OrderNoteFilter {
	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Order Note - Content', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function passed() {
		return $this->get_type()->passed(
			$this->fields_data->get( StringType::VALUE_KEY ),
			$this->fields_data->get( StringType::CONDITION_KEY ),
			$this->get_order_note()->comment_content
		);
	}

	/**
	 * @inheritDoc
	 */
	protected function get_type() {
		return new StringType();
	}
}
