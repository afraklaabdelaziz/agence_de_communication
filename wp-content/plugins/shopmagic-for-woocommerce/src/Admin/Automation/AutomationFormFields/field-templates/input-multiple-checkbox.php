<?php
/**
 * @var \ShopMagicVendor\WPDesk\Forms\Field $field
 * @var string $name_prefix
 * @var string $value
 */
?>
<div class="shopmagic-input--multiple-checkbox">
	<?php $n = 0; ?>
	<?php foreach ( $field->get_possible_values() as $possible_value => $label ) : ?>
		<label class="checkbox-label-within">
			<?php echo esc_html( $label ); ?>
			<input
				type="checkbox"
				name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( $field->get_name() ); ?>][]"
				value="<?php echo esc_attr( $possible_value ); ?>"
				id="<?php echo esc_attr( $field->get_name() ); ?>"
				<?php if ( $field->is_disabled() ) : ?>
					disabled="disabled"
				<?php endif; ?>
				<?php
				if ( isset( $value[ $n ] ) ) :
					if ( $possible_value === $value[ $n ] ||
						 (
							 \is_numeric( $possible_value ) &&
							 \is_numeric( $value[ $n ] ) &&
							 (int) $possible_value === (int) $value[ $n ]
						 )
					) :
						?>
						checked="checked"
						<?php $n++; ?>
					<?php endif; ?>
				<?php endif; ?>
			>
		</label>
	<?php endforeach; ?>
</div>
