<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderNoteBasedPlaceholder;


final class OrderNoteContent extends WooCommerceOrderNoteBasedPlaceholder {


	public function get_slug() {
		return parent::get_slug() . '.content';
	}

	public function get_description(): string {
		return esc_html__( 'Display the content of note for current order.', 'shopmagic-for-woocommerce' );
	}

	/**
	 * @param array $parameters
	 *
	 * @return string
	 */
	public function value( array $parameters ) {
		return $this->get_order_note()->comment_content;
	}
}
