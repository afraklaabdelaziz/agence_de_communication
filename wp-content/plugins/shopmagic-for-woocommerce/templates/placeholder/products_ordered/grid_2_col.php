<?php
/**
 * @var WC_Order_Item[] $order_items
 * @var WC_Product[] $products
 * @var string[] $product_names
 * @var string[] $parameters
 * @var \WPDesk\ShopMagic\Placeholder\Helper\PlaceholderUTMBuilder $utm_builder
 */

?>
<style>
	<?php // Don't inline this css - hack for gmail. ?>
	.sm-product-grid .sm-product-grid-item-2-col img {
		height: auto !important;
	}
</style>

<table cellspacing="0" cellpadding="0" class="sm-product-grid">
	<tbody>
	<tr>
		<td style="padding: 0;">
			<div class="sm-product-grid-container">
				<?php $n = 1; ?>
				<?php foreach ( $products as $product ) : ?>
					<div class="sm-product-grid-item-2-col" style="<?php echo( ( $n % 2 ) ? '' : 'margin-right: 0;' ); ?>">
						<a href="<?php echo esc_url( $utm_builder->append_utm_parameters_to_uri( $parameters, $product->get_permalink() ) ); ?>">
							<?php echo wp_kses_post( $product->get_image() ); ?>
						</a>
						<h3>
							<a href="<?php echo esc_url( $utm_builder->append_utm_parameters_to_uri( $parameters, $product->get_permalink() ) ); ?>">
								<?php echo esc_html( $product->get_name() ); ?>
							</a>
						</h3>
					</div>
					<?php
					$n++;
				endforeach;
				?>
			</div>
		</td>
	</tr>
	</tbody>
</table>
