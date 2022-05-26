<?php

namespace WPDesk\ShopMagic\Placeholder\Builtin\Order;

use ShopMagicVendor\WPDesk\View\Renderer\Renderer;
use WPDesk\ShopMagic\Placeholder\Builtin\WooCommerceOrderBasedPlaceholder;
use WPDesk\ShopMagic\Placeholder\Helper\PlaceholderUTMBuilder;
use WPDesk\ShopMagic\Placeholder\TemplateRendererForPlaceholders;

class OrderProductsOrdered extends WooCommerceOrderBasedPlaceholder {

	/** @var TemplateRendererForPlaceholders */
	protected $renderer;

	/** @var PlaceholderUTMBuilder */
	protected $utm_builder;

	public function __construct( Renderer $renderer ) {
		$this->renderer    = new TemplateRendererForPlaceholders( $renderer );
		$this->utm_builder = new PlaceholderUTMBuilder();
	}

	public function get_description(): string {
		return esc_html__( 'Display current ordered products.', 'shopmagic-for-woocommerce' ) . '\n' .
		$this->utm_builder->get_description();
	}

	public function get_slug(): string {
		return parent::get_slug() . '.products_ordered';
	}

	public function get_supported_parameters(): array {
		return array_merge( $this->utm_builder->get_utm_fields(), $this->renderer->get_template_selector_field() );
	}

	public function value( array $parameters ): string {
		$items         = $this->is_order_provided() ? $this->get_order()->get_items() : [];
		$products      = [];
		$product_names = [];

		foreach ( $items as $item ) {
			if ( $item instanceof \WC_Order_Item_Product ) {
				$product = $item->get_product();
				if ( $product instanceof \WC_Product ) {
					$products[]      = $product;
					$product_names[] = $product->get_name();
				}
			}
		}

		return $this->renderer->render(
			$parameters['template'],
			[
				'order_items'   => $items,
				'products'      => $products,
				'product_names' => $product_names,
				'parameters'    => $parameters,
				'utm_builder'   => $this->utm_builder,
			]
		);
	}
}
