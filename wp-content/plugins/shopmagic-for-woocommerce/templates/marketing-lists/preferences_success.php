<?php
/**
 * @var string $message
 * @var int $success
 */

?>
<div class="woocommerce-message shopmagic-message" role="alert">
	<p class="<?php echo esc_attr( $success === 1 ? 'success' : 'error' ); ?>" style="margin: 0"><?php echo esc_html( $message ); ?></p>
</div>
