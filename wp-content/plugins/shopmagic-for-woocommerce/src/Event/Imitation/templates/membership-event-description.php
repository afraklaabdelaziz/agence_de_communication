<?php
/**
 * Small template for pro event description.
 */

?>
<h3>
	<strong><?php esc_html_e( 'WooCommerce Memberships Integration (available in ShopMagic PRO)', 'shopmagic-for-woocommerce' ); ?></strong>
</h3>

<p><?php esc_html_e( 'Allows creating automations based on memberships events, such as change of membership status or before membership expiration.', 'shopmagic-for-woocommerce' ); ?></p>

<p><strong><?php esc_html_e( 'Trigger actions by the following events:', 'shopmagic-for-woocommerce' ); ?></strong></p>

<ul>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'New Membership', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Membership Status Changed', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Membership Before Renewal', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Membership Before End', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Membership Trial End', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Membership Manual Trigger', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'and more...', 'shopmagic-for-woocommerce' ); ?>
	</li>
</ul>

<p><strong><?php esc_html_e( 'Perform actions on subscriptions:', 'shopmagic-for-woocommerce' ); ?></strong></p>

<ul>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Membership Change Status', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Membership Change Dates', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li>
		<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Membership Change to Manual', 'shopmagic-for-woocommerce' ); ?>
	</li>
	<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'and more...', 'shopmagic-for-woocommerce' ); ?>
	</li>
</ul>

<?php
$product_link = ( get_locale() === 'pl_PL' ) ? // phpcs:ignore Squiz.ControlStructures.InlineIfDeclaration.NotSingleLine
	'https://www.wpdesk.pl/sklep/shopmagic/?utm_source=woocommerce-memberships&utm_medium=notice&utm_campaign=e08' :
	'https://shopmagic.app/products/shopmagic-woocommerce-memberships/?utm_source=woocommerce-memberships&utm_medium=notice&utm_campaign=e08';
?>
<p><a class="button button-primary button-large" href="<?php echo esc_attr( $product_link ); ?>" target="_blank"><?php esc_html_e( 'Get WooCommerce Memberships integration', 'shopmagic-for-woocommerce' ); ?> &rarr;</a>
</p>
