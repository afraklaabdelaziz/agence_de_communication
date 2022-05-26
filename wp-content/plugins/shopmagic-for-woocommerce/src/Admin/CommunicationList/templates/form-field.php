<?php
/**
 * @var \ShopMagicVendor\WPDesk\Forms\Field $field
 * @var \ShopMagicVendor\WPDesk\View\Renderer\Renderer $renderer
 * @var string $name_prefix
 * @var string $value
 *
 * @var string $template_name Real field template.
 */

?>

<tr class="shopmagic-field">
	<td class="shopmagic-label">
		<?php if ( $field->has_label() ) : ?>
			<?php echo $renderer->render( 'form-label', [ 'field' => $field ] ); ?>
		<?php endif; ?>

		<?php if ( $field->has_description() ) : ?>
			<p class="description"><?php echo wp_kses_post( $field->get_description() ); ?></p>
		<?php endif; ?>
	</td>

	<td class="shopmagic-input">
		<?php
		echo $renderer->render(
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
