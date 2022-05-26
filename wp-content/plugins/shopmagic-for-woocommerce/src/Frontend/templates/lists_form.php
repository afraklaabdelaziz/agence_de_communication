<?php
/**
 * Override this template by copying it to yourtheme/shopmagic/lists_form.php
 *
 * @version 2.37.0
 *
 * @var int $list_id List id.
 * @var string $action
 * @var bool $show_name True if name field visible, false if not.
 * @var bool $show_labels True if labels visible, false if not.
 * @var bool $double_optin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="shopmagic-form">
	<form enctype="application/x-www-form-urlencoded"
		  id="shopmagic-form-<?php echo esc_attr( $list_id ); ?>"
		  action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
		  method="post"
		  target="_self">
		<?php wp_nonce_field( $action ); ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
		<input type="hidden" name="list_id" value="<?php echo esc_attr( $list_id ); ?>">
		<?php if ( $double_optin ) : ?>
			<input type="hidden" name="double_optin" value="true">
		<?php endif; ?>
		<?php if ( $show_name ) : ?>
			<p class="shopmagic-form-field shopmagic-form-field-name">
				<label class="shopmagic-label <?php echo ( ! $show_labels ) ? 'sr-only' : ''; ?>"
					   for="shopmagic-name-<?php echo esc_attr( $list_id ); ?>"><?php esc_html_e( 'First name', 'shopmagic-for-woocommerce' ); ?>
				</label>

				<input id="shopmagic-name-<?php echo esc_attr( $list_id ); ?>" class="shopmagic-input shopmagic-input-name"
					   type="text"
					   name="name"
					   placeholder="<?php esc_html_e( 'First name', 'shopmagic-for-woocommerce' ); ?>">
			</p>
		<?php endif; ?>

		<p class="shopmagic-form-field shopmagic-form-field-email">
			<label class="shopmagic-label <?php echo ( ! $show_labels ) ? 'sr-only' : ''; ?>"
				   for="shopmagic-email-<?php echo esc_attr( $list_id ); ?>"><?php esc_html_e( 'Email', 'shopmagic-for-woocommerce' ); ?>
				<span class="shopmagic-required required">*</span></label>

			<input id="shopmagic-email-<?php echo esc_attr( $list_id ); ?>" class="shopmagic-input shopmagic-input-email"
				   type="email"
				   name="email"
				   placeholder="<?php esc_attr_e( 'Email', 'shopmagic-for-woocommerce' ); ?>" required>
		</p>

		<?php if ( ! empty( $agreement ) ) { ?>
			<p class='shopmagic-form-field shopmagic-form-field-email'>
				<input id='agreement' type='checkbox' name='agreement' value='1' required>
				<label class="" for="agreement"><?php echo wp_kses_post( $agreement ); ?>
					<span class="shopmagic-required required">*</span>
				</label>
			</p>
		<?php } ?>

		<p class="shopmagic-form-field shopmagic-form-field-submit">
			<input class="shopmagic-submit" type="submit"
				   value="<?php esc_attr_e( 'Sign up', 'shopmagic-for-woocommerce' ); ?>"/>
		</p>

		<p id="shopmagic-message-<?php echo esc_attr( $list_id ); ?>" class="shopmagic-message hide"></p>

	</form>
	<script type="module">
		window.ShopMagic.shortcodeForm("#shopmagic-form-<?php echo esc_attr( (string) $list_id ); ?>")
	</script>
</div>

