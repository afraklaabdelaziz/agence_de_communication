<?php
/**
 * @var \WPDesk\Forms\Field $field
 * @var string $name_prefix
 * @var string $value
 */

$header_size = (int) $field->get_meta_value( 'header_size' ) ?: 2;
$classes     = $field->has_classes() ? 'class="' . \esc_attr( $field->get_classes() ) . '"' : '';
$header_tag  = "h{$header_size}";
?>
<tr class="shopmagic-field">
	<th class="shopmagic-label" colspan="2" style="text-align: left">
	<?php
	if ( $field->has_label() ) {
		?>
		<<?php echo esc_attr( $header_tag ); ?>
			<?php echo \esc_attr( $classes ); ?>
		>
			<?php
			echo \esc_html( $field->get_label() );
			?>
		</<?php echo \esc_attr( $header_tag ); ?>>
		<?php
	}
	?>

	<?php if ( $field->has_description() ) : ?>
		<p <?php echo \esc_attr( $classes ); ?>>
			<?php echo \wp_kses_post( $field->get_description() ); ?>
		</p>
	<?php endif; ?>
	</th>
</tr>
