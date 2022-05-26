<?php

use WPDesk\ShopMagic\Automation\Automation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @var Automation $automation
 * @var \Iterator $matched_items_generator
 * @var \WPDesk\ShopMagic\DataSharing\RenderableItemProvider $item_renderer
 * @var \ShopMagicVendor\WPDesk\View\Renderer\Renderer $renderer
 */
?>

<div class="wrap manual-action-confirm">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'ShopMagic / Manual Actions', 'shopmagic-for-woocommerce' ); ?></h1>
	<h2><?php esc_html_e( 'Automation: ' ); ?><?php echo esc_html( $automation->get_name() ); ?></h2>

	<table class="form-table">
		<tbody>
		<tr>
			<th><?php esc_html_e( 'Matched items', 'shopmagic-for-woocommerce' ); ?></th>

			<td>
				<div class="items">
					<ul class="item-list">
						<?php $counter = 0; ?>
						<?php foreach ( $matched_items_generator as $order ) : ?>
							<?php $counter++; ?>
							<?php echo $item_renderer->render_item( $order ); ?>
						<?php endforeach; ?>
					</ul>

					<div class="item-summary">
						<?php esc_html_e( 'Total: ', 'shopmagic-for-woocommerce' ); ?><?php echo esc_html( $counter ); ?>
					</div>
				</div>
			</td>
		</tr>

		<?php echo $renderer->render( 'manual_actions_confirm_automation_info', [ 'automation' => $automation ] ); ?>

	</tbody>
</table>

<div class="confirm-footer">
	<form method="POST">
		<input class="button button-primary" type="submit" name="run" value="<?php esc_attr_e( 'Run actions now', 'shopmagic-for-woocommerce' ); ?>"/>
		<span class="manual-action-confirm-or">
			<?php esc_html_e( 'or', 'shopmagic-for-woocommerce' ); ?>
			<a href="<?php echo esc_url( get_edit_post_link( $automation->get_id() ) ); ?>"><?php esc_html_e( 'go back end edit', 'shopmagic-for-woocommerce' ); ?></a>
		</span>
	</form>
</div>
