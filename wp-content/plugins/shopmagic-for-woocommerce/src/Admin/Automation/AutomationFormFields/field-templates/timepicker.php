<?php
/**
 * @var \ShopMagicVendor\WPDesk\Forms\Field $field
 * @var string $name_prefix
 * @var string $value
 */
?>
<tr class="shopmagic-field">
	<td class="shopmagic-label">
		<label for="<?php echo esc_attr( $field->get_name() ); ?>">
			<?php echo esc_html( $field->get_label() ); ?>

			<?php if ( $field->has_description_tip() ) : ?>
				<?php echo wc_help_tip( wp_kses_post( $field->get_description_tip() ) ); ?>
			<?php endif ?>
		</label>

	</td>

	<td class="shopmagic-input timepicker-group">
		<div class="timepicker-group-fields">
			<input
					class="timepicker-hour"
					type="number"
					min="0"
					max="23"
					name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( $field->get_name() ); ?>][0]"
					id="<?php echo esc_attr( $field->get_name() ); ?>-0"
					value="<?php echo esc_html( $value[0] ?? '' ); ?>"/>
			<div class="timepicker-separator">:</div>
			<input
					class="timepicker-minute"
					type="number"
					min="0"
					max="59"
					name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( $field->get_name() ); ?>][1]"
					id="<?php echo esc_attr( $field->get_name() ); ?>-1"
					value="<?php echo esc_html( $value[1] ?? '' ); ?>"/>
		</div>
		<span><?php echo wp_kses_post( $field->get_description() ); ?></span>
	</td>
</tr>
