<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;
use WPDesk\ShopMagic\Placeholder\Helper\PlaceholderUTMBuilder;
use WPDesk\ShopMagic\Placeholder\TemplateRendererForPlaceholders;

class OrderCrossSells extends WooCommerceOrderBasedPlaceholder {

	/** @var TemplateRendererForPlaceholders */
	private $renderer;

	/** @var PlaceholderUTMBuilder */
	private $utm_builder;

	public function __construct( TemplateRendererForPlaceholders $renderer ) {
		$this->renderer    = $renderer;
		$this->utm_builder = new PlaceholderUTMBuilder();
	}

	public function get_description(): string {
		return esc_html__( 'Display cross sell products associated with of current order\'s products.', 'shopmagic-for-woocommerce' ) . '\n' .
			$this->utm_builder->get_description();
	}

	public function get_slug(): string {
		return parent::get_slug() . '.cross_sells';
	}

	public function get_supported_parameters(): array {
		return array_merge( $this->utm_builder->get_utm_fields(), $this->renderer->get_template_selector_field() );
	}

	public function value( array $parameters ): string {
		if ( ! $this->is_order_provided() ) {
			return '';
		}

		$order_items              = $this->get_order()->get_items();
		$cross_sell_products_id   = $this->get_cross_sell_products_id( $order_items );
		$cross_sell_product_names = [];
		$cross_sell_products      = [];

		foreach ( $cross_sell_products_id as $id ) {
			$product = wc_get_product( $id );
			if ( $product instanceof \WC_Product ) {
				$cross_sell_products[]      = $product;
				$cross_sell_product_names[] = $product->get_name();
			}
		}

		return $this->renderer->render(
			$parameters['template'],
			[
				'order_items'   => $order_items,
				'products'      => $cross_sell_products,
				'product_names' => $cross_sell_product_names,
				'parameters'    => $parameters,
				'utm_builder'   => $this->utm_builder,
			]
		);
	}

	/**
	 * @param \WC_Order_Item[] $order_items
	 *
	 * @return int[]
	 */
	private function get_cross_sell_products_id( array $order_items ): array {
		$cross_sell_products_id = [];

		foreach ( $order_items as $order_item ) {
			if ( $order_item instanceof \WC_Order_Item_Product ) {
				$product = $order_item->get_product();
				if ( $product instanceof \WC_Product ) {
					$cross_sell_ids = $product->get_cross_sell_ids();
					if ( ! empty( $cross_sell_ids ) ) {
						array_push( $cross_sell_products_id, ...$cross_sell_ids );
					}
				}
			}
		}

		return array_unique( $cross_sell_products_id );
	}

}
