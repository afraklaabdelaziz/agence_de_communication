<?php

use WPDesk\ShopMagic\Action\Builtin\SendMail\AbstractSendMailAction;
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

<?php foreach ( $automation->get_actions() as $index => $action ) : ?>
	<tr class="action">
		<th><?php esc_html_e( 'Action', 'shopmagic-for-woocommerce' ); ?></th>

		<td>
			<ul>
				<li>
					<strong><?php esc_html_e( 'Type:', 'shopmagic-for-woocommerce' ); ?></strong> <?php echo esc_html( $action->get_name() ); ?>
				</li>

				<?php
				$description = $action->get_fields_data()->get( '_action_title' );
				if ( $description ) :
					?>
					<li>
						<strong><?php esc_html_e( 'Description:', 'shopmagic-for-woocommerce' ); ?></strong> <?php echo esc_html( $description ); ?>
					</li>
				<?php endif; ?>

				<?php if ( $action instanceof AbstractSendMailAction ) : ?>
					<li>
						<strong><?php esc_html_e( 'Subject: ', 'shopmagic-for-woocommerce' ); ?></strong><?php echo esc_html( $action->get_subject_raw() ); ?>
					</li>
				<?php endif; ?>

				<?php do_action( 'shopmagic/core/manual_action/confirm_template/after_action_fields', $action, $automation ); ?>
			</ul>
		</td>
	</tr>
<?php endforeach; ?>
