<?php
/**
 * @var \ShopMagicVendor\WPDesk\Forms\Field $field
 * @var string $name_prefix
 * @var string $value
 */
?>
<?php wp_print_styles( 'media-views' ); ?>

<script>
	window.SM_EditorInitialized = true;
</script>

<tr class="shopmagic-field">
	<td class="shopmagic-label">
		<label for="message_text"><?php echo esc_html( $field->get_label() ); ?></label>

		<p class="content">
		<?php
		esc_html_e(
			'Copy and paste placeholders (including double brackets) from the metabox on the right to personalize.',
			'shopmagic-for-woocommerce'
		);
		?>
				</p>
	</td>

	<td class="shopmagic-input">
		<?php
		$editor_id       = uniqid( 'wyswig_' );
		$editor_settings = [
			'textarea_name' => esc_attr( $name_prefix ) . '[' . esc_attr( $field->get_name() ) . ']',
		];

		// @phpstan-ignore-next-line
		$content = wp_kses( $value, array_merge( wp_kses_allowed_html( 'post' ), [ 'style' => [] ] ) );

		wp_editor( $content, $editor_id, $editor_settings );
		?>
		<script type="text/javascript">
			(function () {
				ShopMagic.wyswig.init('<?php echo esc_attr( $editor_id ); ?>');
			}());
		</script>

	</td>
</tr>
