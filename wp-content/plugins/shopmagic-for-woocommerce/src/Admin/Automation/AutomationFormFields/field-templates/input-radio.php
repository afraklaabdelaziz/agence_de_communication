<?php
/**
 * @var \WPDesk\ShopMagic\FormField\Field $field
 * @var string $name_prefix
 * @var string $value
 */
?>
		<input
			type="radio"
			name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( $field->get_name() ); ?>]"
			id="<?php echo esc_attr( $field->get_name() ); ?>"
			value="<?php echo esc_html( $value ); ?>"/>
