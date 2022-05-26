<?php

/**
 * @var \WPDesk\Forms\Field $field
 * @var \WPDesk\View\Renderer\Renderer $renderer
 * @var string $name_prefix
 * @var string $value
 * @var string $template_name Real field template.
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
	<td class="shopmagic-input">
		<?php
		$renderer->output_render(
			$template_name,
			[
				'field'       => $field,
				'renderer'    => $renderer,
				'name_prefix' => $name_prefix,
				'value'       => $value,
			]
		);
		?>

	</td>
</tr>
