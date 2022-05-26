<?php
/**
 * @var \WPDesk\Forms\Field $field
 * @var string              $name_prefix
 * @var array              $value
 */
$value_identifier = esc_attr( $name_prefix ) . '[' . \esc_attr( $field->get_name() ) . '][]';
?>
		<div class="media-input-wrapper" id="<?php echo esc_attr( $field->get_id() ); ?>">
			<?php foreach ( (array) $value as $single_attachment_url ) : ?>
				<input type="hidden" value="<?php echo esc_url( $single_attachment_url ); ?>"
					name="<?php echo $value_identifier; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"/>
			<?php endforeach; ?>
			<p class="hide-if-no-js">
				<a class="js-add-attachment" href="#">
					<?php \esc_html_e( 'Add PDF attachment', 'shopmagic-for-woocommerce' ); ?>
				</a>
			</p>
			<div class="js-attachments-container">
				<?php foreach ( (array) $value as $single_attachment_url ) : ?>
					<p>
						<a href="#" class="js-remove-attachment shopmagic-remove-attachment-button">✕</a>
						<a href="<?php echo esc_url( $single_attachment_url ); ?>" target="_blank">
							<?php echo esc_html( basename( $single_attachment_url ) ); ?>
						</a>
					</p>
				<?php endforeach; ?>
			</div>
		</div>
<script>
	ShopMagic.media("<?php echo esc_attr( $field->get_id() ); ?>","<?php echo $value_identifier; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>")
</script>
