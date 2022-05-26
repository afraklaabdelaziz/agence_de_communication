<?php
/**
 * @var \ShopMagicVendor\WPDesk\Forms\Field $field
 * @var string $name_prefix
 * @var string $value
 */
?>
<input
	type="number"
	name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( $field->get_name() ); ?>]"
	id="<?php echo esc_attr( $field->get_name() ); ?>"
	value="<?php echo esc_html( $value ); ?>"
	<?php if ( $field->get_attribute( 'min' ) !== null ) : ?>
		min="<?php echo esc_attr( $field->get_attribute( 'min' ) ); ?>"
	<?php endif; ?>
	<?php if ( $field->get_attribute( 'max' ) !== null ) : ?>
		max="<?php echo esc_attr( $field->get_attribute( 'max' ) ); ?>"
	<?php endif; ?>
	<?php if ( $field->is_required() ) : ?>
		required="required"
	<?php endif; ?>
/>
