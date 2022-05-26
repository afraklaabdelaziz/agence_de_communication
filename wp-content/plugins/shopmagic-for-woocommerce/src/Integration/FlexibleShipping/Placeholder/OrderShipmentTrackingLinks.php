<?php

namespace WPDesk\ShopMagic\Integration\FlexibleShipping\Placeholder;

use ShopMagicVendor\WPDesk\View\Renderer\Renderer;
use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;

final class OrderShipmentTrackingLinks extends WooCommerceOrderBasedPlaceholder {

	/** @var Renderer */
	private $renderer;

	public function __construct( Renderer $renderer ) {
		$this->renderer = $renderer;
	}

	public function get_slug(): string {
		return parent::get_slug() . '.shipment_tracking_links';
	}

	private function get_tracking_urls( \WC_Abstract_Order $order ): array {
		$urls      = [];
		$shipments = fs_get_order_shipments( $order->get_id() );
		foreach ( $shipments as $shipment ) {
			if ( method_exists( $shipment, 'get_tracking_url' ) ) {
				$url = $shipment->get_tracking_url();
				if ( ! empty( $url ) ) {
					$urls[] = $url;
				}
			}
		}

		return $urls;
	}

	public function value( array $parameters ): string {
		if ( ! function_exists( 'fs_get_order_shipments' ) ) {
			return '';
		}

		return $this->renderer->render(
			'value',
			[
				'urls' => $this->get_tracking_urls( $this->get_order() ),
			]
		);
	}
}
