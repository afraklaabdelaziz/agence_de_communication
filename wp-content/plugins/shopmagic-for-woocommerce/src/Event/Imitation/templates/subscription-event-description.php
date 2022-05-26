<?php
/**
 * Small template for pro event description.
 */

?>
<h3>
	<strong><?php esc_html_e( 'WooCommerce Subscriptions Integration (available in ShopMagic PRO)', 'shopmagic-for-woocommerce' ); ?></strong>
</h3>

<p><?php esc_html_e( 'Allows to create automations based on subscription events, such as payments or status changes.', 'shopmagic-for-woocommerce' ); ?></p>

<p><strong><?php esc_html_e( 'Trigger actions by the following events:', 'shopmagic-for-woocommerce' ); ?></strong></p>

<ul>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'New Subscription', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Subscription Status Changed', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Subscription Before Renewal', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Subscription Before End', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Subscription Trial End', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Subscription Manual Trigger', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'and more...', 'shopmagic-for-woocommerce' ); ?>
	</li>
</ul>

<p><strong><?php esc_html_e( 'Perform actions on subscriptions:', 'shopmagic-for-woocommerce' ); ?></strong></p>

<ul>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Subscription Change Status', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Subscription Change Dates', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Subscription Change to Manual', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'and more...', 'shopmagic-for-woocommerce' ); ?>
	</li>
</ul>

<?php
$product_link = ( get_locale() === 'pl_PL' ) ? // phpcs:ignore Squiz.ControlStructures.InlineIfDeclaration.NotSingleLine
	'https://www.wpdesk.pl/sklep/shopmagic/?utm_source=woocommerce-subscriptions&utm_medium=notice&utm_campaign=e08' :
	'https://shopmagic.app/products/woocommerce-subscriptions/?utm_source=woocommerce-subscriptions&utm_medium=notice&utm_campaign=e08';
?>
<p><a class="button button-primary button-large" href="<?php echo esc_attr( $product_link ); ?>" target="_blank">
		<?php esc_html_e( 'Get WooCommerce Subscriptions integration &rarr;', 'shopmagic-for-woocommerce' ); ?></a>
</p>

