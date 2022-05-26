<?php
/**
 * @var WC_Order_Item[] $order_items
 * @var WC_Product[] $products
 * @var string[] $product_names
 * @var string[] $parameters
 * @var \WPDesk\ShopMagic\Placeholder\Helper\PlaceholderUTMBuilder $utm_builder
 */

echo esc_html( implode( ', ', $product_names ) );
