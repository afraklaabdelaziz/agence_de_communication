<?php

namespace WPDesk\ShopMagic\Filter\Builtin\Order;

use WPDesk\ShopMagic\Filter\Builtin\OrderFilter;
use WPDesk\ShopMagic\Filter\ComparisionType\ProductSelectType;

final class OrderItems extends OrderFilter {
	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Order - Items', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		return __( 'Filter for specified product.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * Checks if filter allows event to be executed.
	 *
	 * @return bool True if event can be executed.
	 */
	public function passed() {
		$order = $this->get_order();

		$items        = $order->get_items();
		$products_ids = [];
		foreach ( $items as $item ) {
			$products_ids[] = $item['product_id'];
			$products_ids[] = $item['variation_id'];
		}

		return $this->get_type()->passed(
			$this->fields_data->get( ProductSelectType::VALUE_KEY ),
			$this->fields_data->get( ProductSelectType::CONDITION_KEY ),
			$products_ids
		);
	}

	/**
	 * @inheritDoc
	 */
	protected function get_type() {
		return new ProductSelectType();
	}
}
