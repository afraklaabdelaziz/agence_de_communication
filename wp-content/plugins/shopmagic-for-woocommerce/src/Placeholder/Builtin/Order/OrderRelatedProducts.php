<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;
use WPDesk\ShopMagic\Placeholder\Helper\PlaceholderUTMBuilder;
use WPDesk\ShopMagic\Placeholder\TemplateRendererForPlaceholders;

class OrderRelatedProducts extends WooCommerceOrderBasedPlaceholder {

	/** @var TemplateRendererForPlaceholders */
	private $renderer;

	/** @var PlaceholderUTMBuilder */
	private $utm_builder;

	public function __construct( TemplateRendererForPlaceholders $renderer ) {
		$this->renderer    = $renderer;
		$this->utm_builder = new PlaceholderUTMBuilder();
	}

	public function get_description(): string {
		return esc_html__( 'Display the billing state of current order.', 'shopmagic-for-woocommerce' ) . '\n' .
			$this->utm_builder->get_description();
	}

	public function get_slug(): string {
		return parent::get_slug() . '.related_products';
	}

	public function get_supported_parameters(): array {
		return array_merge( $this->utm_builder->get_utm_fields(), $this->renderer->get_template_selector_field() );
	}

	public function value( array $parameters ): string {
		if ( ! $this->is_order_provided() ) {
			return '';
		}

		$order_items            = $this->get_order()->get_items();
		$related_products_id    = $this->get_related_products_id( $order_items );
		$related_products       = [];
		$related_products_names = [];

		foreach ( $related_products_id as $id ) {
			$related_product = wc_get_product( $id );
			if ( $related_product instanceof \WC_Product ) {
				$related_products[]       = $related_product;
				$related_products_names[] = $related_product->get_name();
			}
		}

		return $this->renderer->render(
			$parameters['template'],
			[
				'order_items'   => $order_items,
				'products'      => $related_products,
				'product_names' => $related_products_names,
				'parameters'    => $parameters,
				'utm_builder'   => $this->utm_builder,
			]
		);
	}

	private function get_related_products_id( array $order_items ): array {
		$product_ids          = [];
		$related_products_ids = [];

		foreach ( $order_items as $order_item ) {
			if ( $order_item instanceof \WC_Order_Item_Product ) {
				$product_id = $order_item->get_product_id();
				if ( $product_id ) {
					$product_ids[] = $product_id;
				}
			}
		}

		foreach ( $product_ids as $product_id ) {
			// Set wc_get_related_products limit high instead of unlimited because WC is cutting down first value of products array.
			// It is due to returning with array_slice function call, so when our product is one and only in related,
			// function returns no products ids at all.
			array_push( $related_products_ids, ...wc_get_related_products( $product_id, 100, $product_ids ) );
		}

		return array_unique( $related_products_ids );
	}
}
