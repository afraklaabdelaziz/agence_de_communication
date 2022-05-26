<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;


final class OrderAdminUrl extends WooCommerceOrderBasedPlaceholder {

	public function get_slug(): string {
		return parent::get_slug() . '.admin_url';
	}

	public function get_description(): string {
		return esc_html__( 'Display link to editing current order in admin site.', 'shopmagic-for-woocommerce' );
	}

	public function value( array $parameters ): string {
		return $this->get_order()->get_edit_order_url();
	}
}
