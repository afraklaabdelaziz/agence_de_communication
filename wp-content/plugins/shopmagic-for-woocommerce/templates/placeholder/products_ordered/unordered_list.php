<?php
/**
 * @var WC_Order_Item[] $order_items
 * @var WC_Product[] $products
 * @var string[] $product_names
 * @var string[] $parameters
 * @var \WPDesk\ShopMagic\Placeholder\Helper\PlaceholderUTMBuilder $utm_builder
 */

?>
<ul>
	<?php foreach ( $products as $product ) : ?>
		<li><a href="<?php echo esc_url( $utm_builder->append_utm_parameters_to_uri( $parameters, $product->get_permalink() ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></li>
	<?php endforeach; ?>
</ul>
