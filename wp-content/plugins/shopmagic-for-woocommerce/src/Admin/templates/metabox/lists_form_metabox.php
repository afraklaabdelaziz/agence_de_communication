<?php
/**
 * @var int         $id
 * @var bool $name
 * @var bool $labels
 * @var bool $double_optin
 * @var string $agreement
 */

use WPDesk\ShopMagic\MarketingLists\Shortcode\FrontendForm;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<p>
	<?php esc_html_e( 'You can show sign up form directly to your customers through the shortcode. Customize it and copy the code to the place you want to display it.', 'shopmagic-for-woocommerce' ); ?>
</p>
<div id="shopmagic_shortcode_form">
	<p>
		<code><output name="shortcode">[<?php echo esc_html( FrontendForm::SHORTCODE ); ?> id="<?php echo esc_html( (string) $id ); ?>"]</output></code>
	</p>
	<p>
		<label><input type="checkbox" name="_form_shortcode[name]" <?php checked( $name ); ?>/><?php esc_html_e( 'Name', 'shopmagic-for-woocommerce' ); ?></label>
	</p>
	<p>
		<label><input type="checkbox" name="_form_shortcode[labels]" <?php checked( $labels ); ?>/><?php esc_html_e( 'Labels', 'shopmagic-for-woocommerce' ); ?></label>
	</p>
	<p>
		<label><input type="checkbox" name="_form_shortcode[doubleOptin]" <?php checked( $double_optin ); ?>/><?php esc_html_e( 'Double optin', 'shopmagic-for-woocommerce' ); ?></label>
	</p>
	<p>
		<label><?php esc_html_e( 'Add marketing agreement', 'shopmagic-for-woocommerce' ); ?></label>
		<textarea cols="25" name="_form_shortcode[agreement]"><?php echo wp_kses_post( $agreement ); ?></textarea>
		<small><?php esc_html_e( 'Text of marketing agreement will be shown as a checkbox label for the form.', 'shopmagic-for-woocommerce' ); ?></small>
	</p>
	<p>
		<small style="color: orange"><?php esc_html_e( 'Changing the
		shortcode here, will not update it on the pages where it is used.
		Remember to update your shortcode.', 'shopmagic-for-woocommerce' ); ?></small>
	</p>
</div>

