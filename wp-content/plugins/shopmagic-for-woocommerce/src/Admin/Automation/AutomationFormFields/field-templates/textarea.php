<?php
/**
 * @var \WPDesk\ShopMagic\FormField\Field $field
 * @var string $name_prefix
 * @var string $value
 */
$uniqid = uniqid( 'textarea' );
?>

<tr class="shopmagic-field">
	<td class="shopmagic-label">
		<label for="<?php echo esc_attr( $uniqid ); ?>"><?php echo esc_html( $field->get_label() ); ?></label>

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
		<textarea id="<?php echo esc_attr( $uniqid ); ?>" rows="14"
				  name="<?php echo esc_attr( $name_prefix ) . '[' . esc_attr( $field->get_name() ) . ']'; ?>"><?php echo esc_html( $value ); ?></textarea>
	</td>
</tr>
