<?php
/**
 * @var \WPDesk\ShopMagic\FormField\Field $field
 * @var string $name_prefix
 * @var string $value
 */
?>
		<input type="hidden" name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( $field->get_name() ); ?>]" value="no"/>

		<?php if ( $field->get_type() === 'checkbox' && $field->has_sublabel() ) : ?>
			<label>
		<?php endif; ?>



			<input
			type="checkbox"
			name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( $field->get_name() ); ?>]"
			id="<?php echo esc_attr( $field->get_name() ); ?>"
			value="yes"
			<?php
			if ( $value === 'yes' ) :
				?>
				checked="checked"<?php endif; ?>
		/>

		<?php if ( $field->get_type() === 'checkbox' && $field->has_sublabel() ) : ?>
			<?php echo esc_html( $field->get_sublabel() ); ?></label>
		<?php endif; ?>
