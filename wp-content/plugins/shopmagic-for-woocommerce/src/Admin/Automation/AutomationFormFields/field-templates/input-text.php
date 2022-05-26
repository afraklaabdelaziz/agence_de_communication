<?php
/**
 * @var \ShopMagicVendor\WPDesk\Forms\Field $field
 * @var string $name_prefix
 * @var string $value
 */
?>
		<input
			type="text"
			name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( $field->get_name() ); ?>]"
			<?php if ( $field->has_placeholder() ) : ?>
				placeholder="<?php echo esc_attr( $field->get_placeholder() ); ?>"
			<?php endif; ?>
			id="<?php echo esc_attr( $field->get_name() ); ?>"
			value="<?php echo esc_html( $value ); ?>"/>
