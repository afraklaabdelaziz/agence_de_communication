<?php
/**
 * @var \WPDesk\ShopMagic\Admin\Form\Fields\PostAjaxSelect $field
 * @var string $name_prefix
 * @var string $value
 */

?>

<select class="wc-product-search"
		style="width:203px;"
		name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( $field->get_name() ); ?>]"
		data-placeholder="<?php echo esc_attr( $field->get_placeholder() ) . '&hellip;'; ?>"
		data-action="<?php echo esc_attr( $field->get_name() ); ?>"
		data-allow_clear="true"
>
	<?php
	if ( $value ) {
		$user = get_user_by( 'id', $value );
		echo '<option value="' . esc_attr( $value ) . '" ' . selected( true, true, false ) . '>' . esc_html( $field->get_label_for_value( (int) $value ) ) . '</option>';
	}
	?>
</select>
