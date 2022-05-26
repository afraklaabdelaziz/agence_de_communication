<?php
/**
 * @var ShopMagicVendor\WPDesk\Forms\Field $field
 * @var string $name_prefix
 * @var string $value
 */

use WPDesk\ShopMagic\Admin\SelectAjaxField\CustomerSelectAjax;

?>

<select class="wc-product-search"
		style="width:203px;"
		name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( $field->get_name() ); ?>]"
		data-placeholder="<?php esc_attr_e( 'Search for a customer', 'shopmagic-for-woocommerce' ) . '&hellip;'; ?>"
		data-action="<?php echo esc_attr( CustomerSelectAjax::get_ajax_action_name() ); ?>"
		data-allow_clear="true"
>
	<?php
	if ( $value ) {
		$user = get_user_by( 'id', $value );
		echo '<option value="' . esc_attr( $value ) . '" ' . selected( true, true, false ) . '>' . esc_html( CustomerSelectAjax::convert_value_to_option_text( (int) $value ) ) . '</option>';
	}
	?>
</select>
