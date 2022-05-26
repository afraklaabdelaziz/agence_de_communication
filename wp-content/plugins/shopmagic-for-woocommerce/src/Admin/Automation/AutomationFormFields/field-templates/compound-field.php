<?php
/**
 * @var \ShopMagicVendor\WPDesk\ShopMagic\FormField\Field $field
 * @var \ShopMagicVendor\WPDesk\View\Renderer\Renderer $renderer
 * @var string $name_prefix
 * @var string $value
 */
?>
<tr class="shopmagic-field <?php echo esc_attr( $field->get_classes() ); ?>">
	<td class="shopmagic-label">
		<label for="<?php echo esc_attr( $field->get_name() ); ?>">
			<?php echo esc_html( $field->get_label() ); ?>

			<?php if ( $field->has_description_tip() ) : ?>
				<?php echo wc_help_tip( wp_kses_post( $field->get_description_tip() ) ); ?>
			<?php endif ?>

		</label>

		<?php if ( $field->has_description() ) : ?>
			<p class="content"><?php echo wp_kses_post( $field->get_description() ); ?></p>
		<?php endif ?>

	</td>

	<td class="shopmagic-input shopmagic-input--compound-field">
		<?php foreach ( $field->get_fields() as $key => $input ) : ?>
			<?php
			$renderer->output_render(
				$input->get_template_name(),
				[
					'field'       => $input,
					'name_prefix' => $name_prefix,
					'value'       => $value[ $key ] ?? $input->get_default_value(),
				]
			);
			?>
		<?php endforeach; ?>
	</td>
</tr>
