<?php
/**
 * Template for metabox showing placeholders in Automation edit.
 * Dynamically filled with JS
 *
 * @see admin-handler.js:initializePlaceholderArea()
 */

use WPDesk\ShopMagic\Helper\WordPressPluggableHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="placeholders">
	<div class="search-wrapper">
		<input id="find-placeholder" class="search" type="search" placeholder="<?php esc_attr_e( 'Find placeholder...', 'shopmagic-for-woocommerce' ); ?>">
		<label for="find-placeholder" class="screen-reader-text"><?php esc_html_e( 'Find placeholder', 'shopmagic-for-woocommerce' ); ?></label>
	</div>

	<?php
	if ( ! WordPressPluggableHelper::is_plugin_active( 'shopmagic-customer-coupons/shopmagic-customer-coupons.php' ) ) {
		$url = ( get_locale() === 'pl_PL' ) ? // phpcs:ignore Squiz.ControlStructures.InlineIfDeclaration.NotSingleLine
			'https://www.wpdesk.pl/sklep/shopmagic/?utm_source=customer-coupons&utm_medium=notice&utm_campaign=e08' :
			'https://shopmagic.app/products/shopmagic-customer-coupons/?utm_source=customer-coupons&utm_medium=notice&utm_campaign=e08';
		?>
		<div class="shopmagic-custom-notice">
			<p>
				<?php esc_html_e( 'Send dynamic and personalized coupons with', 'shopmagic-for-woocommerce' ); ?>
				<code>{{ shop.coupon }}</code>
				<?php esc_html_e( 'placeholder available in ', 'shopmagic-for-woocommerce' ); ?>
				<a href="<?php echo esc_url( $url ); ?>" target="_blank">
					<?php esc_html_e( 'Customer Coupons add-on', 'shopmagic-for-woocommerce' ); ?>
				</a>
			</p>
		</div>
	<?php } ?>
	<ul class="list"></ul>
</div>

<p>
	<a href="https://docs.shopmagic.app/article/1058-placeholders-usage-guide" target="_blank">
		<?php esc_html_e( 'Learn how to use placeholders', 'shopmagic-for-woocommerce' ); ?> &rarr;
	</a>
</p>
