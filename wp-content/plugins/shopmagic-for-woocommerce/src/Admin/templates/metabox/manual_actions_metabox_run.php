<?php
/**
 * @var string $name Expected manual automation submit name.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<p>
	<?php esc_html_e( 'Please set up and save the automation. Then press the button below in order to trigger actions.', 'shopmagic-for-woocommerce' ); ?>
	<?php printf( ' <a href="https://docs.shopmagic.app/" target="_blank">%s &rarr;</a>', esc_html__( 'Learn more', 'shopmagic-for-woocommerce' ) ); ?>
</p>

<input type="submit" name="<?php echo esc_attr( $name ); ?>" value="<?php esc_html_e( 'Preview and run actions', 'shopmagic-for-woocommerce' ); ?>" class="button button-primary button-large"/>
