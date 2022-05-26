<?php

namespace WPDesk\ShopMagic\Integration\FlexibleShipping;

use WPDesk\ShopMagic\Placeholder\PlaceholderFactory2;
use WPDesk\ShopMagic\Placeholder\PlaceholderFactoryCore;

final class Integrator {
	public function integrate_placeholders( PlaceholderFactory2 $factory ) {
		if ( $this->is_flexible_shipping_active() ) {
			$factory->append_placeholder( new Placeholder\OrderShipmentTrackingLinks( PlaceholderFactoryCore::get_placeholder_template_renderer( 'shipment_tracking_url' ) ) );
		}
	}

	private function is_flexible_shipping_active(): bool {
		return function_exists( 'fs_get_order_shipments' );
	}
}
