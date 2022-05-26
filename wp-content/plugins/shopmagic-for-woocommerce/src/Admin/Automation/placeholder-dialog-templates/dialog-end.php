<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var string $slug
 * @var \WPDesk\ShopMagic\Placeholder\Placeholder $placeholder
 */
?>
		</tbody>
	</table>

	<div class="dialog-result">
		<input type="text" id="placeholder_result" value="" readonly="readonly" />
	</div>

	<div class="dialog-button">
		<button id="copy_placeholder_result" class="button-primary"><?php esc_html_e( 'Copy placeholder and close', 'shopmagic-for-woocommerce' ); ?></php></button>
	</div>
</div>
