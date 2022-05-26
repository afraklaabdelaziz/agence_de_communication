<?php
/**
 * @var \ShopMagicVendor\WPDesk\Forms\Field $field
 * @var string $name_prefix
 * @var string $value
 */

$value_identifier = esc_attr( $name_prefix ) . '[' . esc_attr( $field->get_name() ) . ']';
?>
<input
	type="datetime-local"
	name="<?php echo $value_identifier; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
	id="<?php echo esc_attr( $field->get_name() ); ?>"
	value="<?php echo esc_attr( $value ); ?>"
	<?php if ( $field->has_placeholder() ) : ?>
		placeholder="<?php echo esc_attr( $field->get_placeholder() ); ?>"
	<?php endif; ?>
	<?php if ( $field->get_attribute( 'min' ) ) : ?>
		min="<?php echo esc_attr( $field->get_attribute( 'min' ) ); ?>"
	<?php endif; ?>
	<?php if ( $field->get_attribute( 'max' ) ) : ?>
		max="<?php echo esc_attr( $field->get_attribute( 'max' ) ); ?>"
	<?php endif; ?>
	<?php if ( $field->is_required() ) : ?>
		required="required"
	<?php endif; ?>
	pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}"
/>
