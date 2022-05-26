<?php
/**
 * @var \ShopMagicVendor\WPDesk\Forms\Field $field
 * @var string $name_prefix
 * @var string $value
 */
?>
<input
	type="file"
	name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( $field->get_name() ); ?>]"
	id="<?php echo esc_attr( $field->get_name() ); ?>"
	value="<?php echo esc_html( $value ); ?>"
	<?php if ( $field->is_attribute_set( 'accept' ) ) : ?>
		accept="<?php echo esc_attr( $field->get_attribute( 'accept' ) ); ?>"
	<?php endif; ?>
	<?php if ( $field->is_attribute_set( 'multiple' ) ) : ?>
		multiple
	<?php endif; ?>
	<?php if ( $field->is_required() ) : ?>
		required="required"
	<?php endif; ?>
/>
