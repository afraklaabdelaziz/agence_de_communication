<?php
/**
 * Small template for pro event description.
 */

?>
<h3>
	<strong><?php esc_html_e( 'Send email manually and reach your customers whenever you like', 'shopmagic-for-woocommerce' ); ?></strong>
</h3>

<ul>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Send one-time manual emails to specific group of customers (e.g. who bought a specific product or course)', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Reach all your customer with marketing email like newsletters and discounts', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Bring back past customers with updates and news', 'shopmagic-for-woocommerce' ); ?>
	</li>
</ul>

<p><strong><?php esc_html_e( 'This event is available in ShopMagic Manual Actions - a part of PRO addons bundle that give you even more control, integrations and email personalization options.', 'shopmagic-for-woocommerce' ); ?>
	</strong></p>

<?php
$product_link = ( get_locale() === 'pl_PL' ) ? // phpcs:ignore Squiz.ControlStructures.InlineIfDeclaration.NotSingleLine
	'https://www.wpdesk.pl/sklep/shopmagic/?utm_source=manual-actions&utm_medium=notice&utm_campaign=e08' :
	'https://shopmagic.app/products/shopmagic-manual-actions/?utm_source=manual-actions&utm_medium=notice&utm_campaign=e08';
?>
<p>
	<a class="button button-primary button-large" href="<?php echo esc_attr( $product_link ); ?>" target="_blank"><?php esc_html_e( 'Get Manual Actions with ShopMagic PRO', 'shopmagic-for-woocommerce' ); ?> &rarr;</a>
</p>
