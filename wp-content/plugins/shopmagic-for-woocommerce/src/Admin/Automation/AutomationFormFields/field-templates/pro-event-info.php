<?php
/**
 * @var \ShopMagicVendor\WPDesk\Forms\Field $field
 * @var string $name_prefix
 * @var string $value
 */

?>

<tr class="shopmagic-field">
	<td class="shopmagic-label"></td>

	<td class="shopmagic-input">
		<div class="<?php echo esc_attr( $field->get_classes() ); ?>" data-notice-name="<?php echo esc_attr( $field->get_attribute( 'notice-name' ) ); ?>">
			<?php echo wpautop( $field->get_description() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( mb_strpos( $field->get_classes(), 'is-dismissible' ) !== false ) { ?>
				<button type="button" class="notice-dismiss" onclick="makeNoticeDismissible(this)"></button>
			<?php } ?>
		</div>
	</td>
</tr>
