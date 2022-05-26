<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @var \WPDesk\ShopMagic\Action\Builtin\SendMail\AbstractSendMailAction $action
 * @var int $action_index
 * @var string $email
 * @var string $hook_name
 */
?>

<span class="action-actions">
	<a href="#" class="send_test_email"
	   data-dialog-id="dialog_<?php echo esc_attr( $action_index ); ?>"><?php esc_html_e( 'Send test', 'shopmagic-for-woocommerce' ); ?></a>
</span>

<div id="dialog_<?php echo esc_attr( $action_index ); ?>" title="Send test email" style="display: none;">
	<table class="shopmagic-table">
		<tbody>
			<tr class="shopmagic-field">
				<td class="shopmagic-label">
					<label><?php esc_html_e( 'Email', 'shopmagic-for-woocommerce' ); ?> <span class="required">*</span></label>
				</td>

				<td class="shopmagic-input">
					<input type="text" class="email_to_test" value="<?php echo esc_attr( $email ); ?>" placeholder="<?php esc_html_e( 'Enter email...', 'shopmagic-for-woocommerce' ); ?>"/>
				</td>
			</tr>
		</tbody>
	</table>

	<div class="dialog-result"></div>

	<div class="dialog-button">
		<button data-hook-name="<?php echo esc_attr( $hook_name ); ?>" class="button-primary test_email_button"><?php esc_html_e( 'Send test email', 'shopmagic-for-woocommerce' ); ?></php></button>
	</div>
</div>
