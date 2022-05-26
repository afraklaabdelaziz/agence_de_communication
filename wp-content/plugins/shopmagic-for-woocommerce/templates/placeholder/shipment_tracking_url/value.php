<?php
/** @var string[] $urls */
?>
<?php foreach ( $urls as $index => $url ) : ?>
	<a href="<?php echo esc_url( $url ); ?>">
	  <?php echo esc_html_e( 'Shipment tracking', 'shopmagic-for-woocommerce' ); ?>
	</a><br/>
<?php endforeach; ?>
