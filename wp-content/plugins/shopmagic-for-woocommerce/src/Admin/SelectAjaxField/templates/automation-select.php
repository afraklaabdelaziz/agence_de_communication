<?php
/**
 * @var ShopMagicVendor\WPDesk\Forms\Field $field
 * @var string $name_prefix
 * @var string $value
 */

use WPDesk\ShopMagic\Admin\SelectAjaxField\AutomationSelectAjax;

?>

<select class="wc-product-search"
		style="width:203px;"
		name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( $field->get_name() ); ?>]"
		data-placeholder="<?php esc_attr_e( 'Search for an automation', 'shopmagic-for-woocommerce' ) . '&hellip;'; ?>"
		data-action="<?php echo esc_attr( AutomationSelectAjax::get_ajax_action_name() ); ?>"
		data-allow_clear="true"
>
	<?php
	$automation_post = get_post( (int) $value );
	if ( $value && $automation_post ) {
		echo '<option value="' . esc_attr( $value ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $automation_post->post_title ) . '</option>';
	}
	?>
</select>
