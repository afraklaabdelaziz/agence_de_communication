<?php

/**
 * @var \WPDesk\ShopMagic\FormField\Field $field
 * @var string $name_prefix
 * @var string[] $value
 */
?>

<table class="shopmagic-table">
	<tbody id="order_line_items">
	<tr class="shopmagic-field">
		<td class="shopmagic-label">
			<label for="<?php echo esc_attr( $field->get_name() ); ?>"><?php echo esc_html( $field->get_label() ); ?></label>
		</td>

		<td class="shopmagic-input">
			<select class="wc-product-search" multiple="multiple" style="width: 50%;"
					name="<?php echo esc_attr( $name_prefix ); ?>[<?php echo esc_attr( $field->get_name() ); ?>][]"
					data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'shopmagic-for-woocommerce' ); ?>"
					data-action="woocommerce_json_search_products_and_variations">
				<?php
				foreach ( (array) $value as $product_id ) {
					$product = wc_get_product( $product_id );
					if ( is_object( $product ) ) {
						echo '<option value="' . esc_attr( $product_id ) . '"' . selected(
							true,
							true,
							false
						) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
					}
				}
				?>
			</select>
		</td>
	</tr>
	</tbody>
</table>

<script type="text/javascript">
	jQuery(document.body).trigger('wc-enhanced-select-init');
</script>
