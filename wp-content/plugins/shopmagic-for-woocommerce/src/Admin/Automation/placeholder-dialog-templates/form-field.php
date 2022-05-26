<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var \WPDesk\ShopMagic\FormField\Field $field
 * @var ShopMagicVendor\WPDesk\View\Renderer\Renderer $renderer
 * @var string $name_prefix
 * @var string $value
 *
 * @var string $template_name Real field template.
 */
?>

<tr class="shopmagic-field">
	<?php if ( $field->has_label() ) : ?>
		<td class="shopmagic-label">
			<?php echo $renderer->render( 'form-label', [ 'field' => $field ] ); ?>
		</td>
	<?php endif; ?>

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

		<?php if ( $field->has_description() ) : ?>
			<p class="description"><?php echo wp_kses_post( $field->get_description() ); ?></p>
		<?php endif; ?>
	</td>
</tr>
