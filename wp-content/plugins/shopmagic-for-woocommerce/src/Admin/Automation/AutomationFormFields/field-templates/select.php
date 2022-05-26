<?php
/**
 * @var \ShopMagicVendor\WPDesk\Forms\Field $field
 * @var string $name_prefix
 * @var string $value
 */
?>

<select id="<?php echo esc_attr( $field->get_name() ); ?>"
		name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( $field->get_name() ); ?>]"
	<?php if ( $field->is_disabled() ) : ?>
		disabled="disabled"
	<?php endif; ?>
>

	<?php
	if ( $field->has_placeholder() ) :
		?>
		<option value=""><?php echo esc_html( $field->get_placeholder() ); ?></option><?php endif; ?>

	<?php foreach ( $field->get_possible_values() as $possible_value => $label ) : ?>
		<option
			<?php
			if ( $possible_value === $value || ( \is_numeric( $possible_value ) && \is_numeric( $value ) && (int) $possible_value === (int) $value ) ) :
				?>
				selected="selected"<?php endif; ?>
			value="<?php echo esc_attr( $possible_value ); ?>"
		><?php echo esc_html( $label ); ?></option>
	<?php endforeach; ?>
</select>
