<?php

namespace ShopMagicVendor;

/**
 * @var \ShopMagicVendor\WPDesk\Forms\Field $field
 * @var \ShopMagicVendor\WPDesk\View\Renderer\Renderer $renderer
 * @var string $name_prefix
 * @var string $value
 *
 * @var string $template_name Real field template.
 */
?>


<input
		<?php
		if ( $field->has_classes() ) :
			?>
			class="<?php echo esc_attr( $field->get_classes() ); ?>"<?php endif; ?>
<?php foreach ( $field->get_attributes( [] ) as $key => $value ) : ?>
	<?php echo esc_attr( $key ); ?>="<?php echo esc_attr( $value ); ?>"
<?php endforeach; ?>
type="<?php echo esc_attr( $field->get_type() ); ?>"
name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( $field->get_name() ); ?>]"
id="<?php echo esc_attr( $field->get_id() ); ?>"
value="<?php echo esc_html( $field->get_label() ); ?>"
<?php
if ( $field->is_required() ) :
	?>
	required="required"<?php endif; ?>
<?php
if ( $field->is_disabled() ) :
	?>
	disabled="disabled"<?php endif; ?>
<?php
if ( $field->is_readonly() ) :
	?>
	readonly="readonly"<?php endif; ?>
/>
