<?php
/** Small template for pro event description. */

$url = ( get_locale() === 'pl_PL' ) ? // phpcs:ignore Squiz.ControlStructures.InlineIfDeclaration.NotSingleLine
	'https://www.wpdesk.pl/sklep/shopmagic/?utm_source=advanced-filters&utm_medium=notice&utm_campaign=e08' :
	'https://shopmagic.app/products/shopmagic-advanced-filters/?utm_source=advanced-filters&utm_medium=notice&utm_campaign=e08';
?>
<div>
	<p>
		<b><?php esc_html_e( 'Send highly-personalized emails based on category and more than 30 additional filter combinations.', 'shopmagic-for-woocommerce' ); ?></b>
		<br>
	<p><?php esc_html_e( 'Filter Order - Item Categories is available with Advanced Filters - a part of ShopMagic PRO add-ons bundle.', 'shopmagic-for-woocommerce' ); ?></p>
	<a class="button button-primary" href="<?php echo esc_url( $url ); ?>" target="_blank">
		<?php esc_html_e( 'Read more about segmentation with Advanced Filters', 'shopmagic-for-woocommerce' ); ?>&rarr;
	</a>
	</p>
</div>
