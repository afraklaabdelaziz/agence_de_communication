<?php
/**
 * @var \ShopMagicVendor\WPDesk\Forms\Field $field
 * @var string $name_prefix
 * @var string $value
 */
?>
<label for="<?php echo esc_attr( $field->get_id() ); ?>"><?php echo esc_html( $field->get_label() ); ?>
	<?php if ( $field->is_required() ) : ?>
		<span class="required"> *</span>
	<?php endif ?>

	<?php if ( $field->has_description_tip() ) : ?>
		<?php echo wc_help_tip( wp_kses_post( $field->get_description_tip() ) ); ?>
	<?php endif ?>
</label>
