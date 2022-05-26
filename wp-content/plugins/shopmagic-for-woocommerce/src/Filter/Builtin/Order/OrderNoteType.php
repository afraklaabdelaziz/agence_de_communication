<?php

namespace WPDesk\ShopMagic\Filter\Builtin\Order;

use WPDesk\ShopMagic\Filter\Builtin\OrderNoteFilter;
use WPDesk\ShopMagic\Filter\ComparisionType\SelectOneToOneType;

final class OrderNoteType extends OrderNoteFilter {
	const PARAM_PRIVATE  = 'private';
	const PARAM_CUSTOMER = 'customer';

	/**
	 * @inheritDoc
	 */
	public function get_name() {
		return __( 'Order Note - Type', 'shopmagic-for-woocommerce' );
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
		$order_note      = $this->get_order_note();
		$order_note_type = get_comment_meta( $order_note->comment_ID, 'is_customer_note', true ) ? self::PARAM_CUSTOMER : self::PARAM_PRIVATE;

		return $this->get_type()->passed(
			$this->fields_data->get( SelectOneToOneType::VALUE_KEY ),
			$this->fields_data->get( SelectOneToOneType::CONDITION_KEY ),
			$order_note_type
		);
	}

	/**
	 * @inheritDoc
	 */
	protected function get_type() {
		return new SelectOneToOneType(
			$options = [
				self::PARAM_PRIVATE  => __( 'Private Note', 'shopmagic-for-woocommerce' ),
				self::PARAM_CUSTOMER => __( 'Customer Note', 'shopmagic-for-woocommerce' ),
			]
		);
	}
}
