<?php

/**
 * @var \WPDesk\Forms\Field $field
 * @var \WPDesk\View\Renderer\Renderer $renderer
 * @var string $name_prefix
 * @var string $value
 * @var string $template_name Real field template.
 */
if ( empty( $value ) || \is_string( $value ) ) {
	$input_values[] = '';
} else {
	$input_values = $value;
}
?>
<?php foreach ( array_values( $input_values ) as $i => $text_value ) { ?>
	<tr class="shopmagic-field shopmagic-field--multiple js-clone-wrapper">
		<?php if ( ! \in_array( $field->get_type(), [ 'number', 'text', 'hidden' ], \true ) ) { ?>
			<input type="hidden" name="
			<?php
			echo \esc_attr( $name_prefix ) . '[' . \esc_attr( $field->get_name() ) . ']';
			?>
			" value="no"/>
		<?php } ?>

		<td class="shopmagic-label">
			<label class="js-row-name"><?php echo esc_html( chr( ord( 'A' ) + $i ) ); ?></label>
		</td>
		<td class="shopmagic-input shopmagic-input--multiple">
			<input
				type="<?php echo \esc_attr( $field->get_type() ); ?>"
				name="<?php echo \esc_attr( $name_prefix ) . '[' . \esc_attr( $field->get_name() ) . '][]'; ?>"
				id="<?php echo \esc_attr( $field->get_id() ); ?>"

				<?php
				if ( $field->has_classes() ) {
					?>
					class="
					<?php
					echo \esc_attr( $field->get_classes() );
					?>
					"
					<?php
				}
				?>

				<?php
				if ( $field->get_type() === 'text' && $field->has_placeholder() ) {
					?>
					placeholder="
					<?php
					echo \esc_html( $field->get_placeholder() );
					?>
					"
					<?php
				}
				?>

				<?php
				foreach ( $field->get_attributes() as $key => $atr_val ) {
					echo \esc_attr( $key ) . '="' . \esc_attr( $atr_val ) . '"';
					?>
					<?php
				}
				?>

				<?php
				if ( $field->is_required() ) {
					?>
					required="required"
					<?php
				}
				?>
				<?php
				if ( $field->is_disabled() ) {
					?>
					disabled="disabled"
					<?php
				}
				?>
				<?php
				if ( $field->is_readonly() ) {
					?>
					readonly="readonly"
					<?php
				}
				?>
				<?php
				if ( \in_array( $field->get_type(), [ 'number', 'text', 'hidden' ], \true ) ) {
					?>
					value="<?php echo trim( \esc_html( $text_value ) ); ?>"
					<?php
				} else {
					?>
					value="yes"
					<?php
					if ( $value === 'yes' ) {
						?>
						checked="checked"
						<?php
					}
					?>
					<?php
				}
				?>
			/>
			<span class="add-field"><span class="dashicons dashicons-plus-alt"></span></span>
			<span class="remove-field"><span class="dashicons dashicons-remove"></span></span>
		</td>
	</tr>
<?php } ?>
	<template id="field-multiple-row">
		<tr class="shopmagic-field shopmagic-field--multiple js-clone-wrapper">
			<?php if ( ! \in_array( $field->get_type(), [ 'number', 'text', 'hidden' ], \true ) ) { ?>
				<input type="hidden" name="
				<?php
				echo \esc_attr( $name_prefix ) . '[' . \esc_attr( $field->get_name() ) . ']';
				?>
				" value="no"/>
			<?php } ?>
			<td class="shopmagic-label">
				<label class="js-row-name">{{ index }}</label>
			</td>
			<td class="shopmagic-input shopmagic-input--multiple">
				<input
					type="<?php echo \esc_attr( $field->get_type() ); ?>"
					name="<?php echo \esc_attr( $name_prefix ) . '[' . \esc_attr( $field->get_name() ) . '][]'; ?>"
					id="<?php echo \esc_attr( $field->get_id() ); ?>"

					<?php
					if ( $field->has_classes() ) {
						?>
						class="
						<?php
						echo \esc_attr( $field->get_classes() );
						?>
						"
						<?php
					}
					?>

					<?php
					if ( $field->get_type() === 'text' && $field->has_placeholder() ) {
						?>
						placeholder="
						<?php
						echo \esc_html( $field->get_placeholder() );
						?>
						"
						<?php
					}
					?>

					<?php
					foreach ( $field->get_attributes() as $key => $atr_val ) {
						if ( $key === 'disabled' ) {
							continue;
						}
						echo \esc_attr( $key ) . '="' . \esc_attr( $atr_val ) . '"';
						?>
						<?php
					}
					?>

					<?php
					if ( $field->is_required() ) {
						?>
						required="required"
						<?php
					}
					?>
					<?php
					if ( $field->is_readonly() ) {
						?>
						readonly="readonly"
						<?php
					}
					?>
					<?php if ( \in_array( $field->get_type(), [ 'number', 'text', 'hidden' ], \true ) ) { ?>
						value=""
					<?php } else { ?>
						value="yes"
						<?php if ( $value === 'yes' ) { ?>
							checked="checked"
						<?php } ?>
					<?php } ?>
				/>
				<span class="add-field"><span class="dashicons dashicons-plus-alt"></span></span>
				<span class="remove-field"><span class="dashicons dashicons-remove"></span></span>
			</td>
		</tr>
	</template>
	<style>
		.shopmagic-input--multiple {
			display: flex;
			align-items: center;
		}

		.shopmagic-field--multiple:nth-of-type(4) .remove-field	{
			display: none;
		}
	</style>
	<script>
		document.querySelectorAll('.js-clone-wrapper').forEach((row) => {
			attachAddAndRemove(row)
		})
	</script>
<?php
