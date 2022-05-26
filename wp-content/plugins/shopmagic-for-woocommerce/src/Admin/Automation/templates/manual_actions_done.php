<?php

use WPDesk\ShopMagic\Automation\Automation;
use WPDesk\ShopMagic\Automation\AutomationPostType;
use WPDesk\ShopMagic\Admin\Queue;
use WPDesk\ShopMagic\Admin\Outcome;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * @var Automation $automation
 * @var \Generator|\WC_Order[] $matched_orders_generator
 */
?>

<div class="wrap manual-action-confirm">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'ShopMagic / Manual Actions', 'shopmagic-for-woocommerce' ); ?></h1>
	<h2><?php esc_html_e( 'Automation: ' ); ?><?php echo esc_html( $automation->get_name() ); ?></h2>

	<div class="notice notice-success">
		<p><strong><?php esc_html_e( 'Actions have beed added to the queue and will run shortly.', 'shopmagic-for-woocommerce' ); ?></strong></p>
	</div>

	<div class="confirm-footer">
		<a href="<?php echo esc_url( Queue\ListMenu::get_url( $automation->get_id() ) ); ?>" class="button button-primary"><?php esc_html_e( 'View in queue', 'shopmagic-for-woocommerce' ); ?></a>
		<a href="<?php echo esc_url( Outcome\ListPage::get_url( $automation->get_id() ) ); ?>" class="button button-primary"><?php esc_html_e( 'View in outcomes', 'shopmagic-for-woocommerce' ); ?></a>

		<span class="manual-action-confirm-or">
			<?php esc_html_e( 'or', 'shopmagic-for-woocommerce' ); ?>
			<a href="<?php echo esc_url( AutomationPostType::get_url() ); ?>"><?php esc_html_e( 'go back to automations', 'shopmagic-for-woocommerce' ); ?></a>
		</span>
	</div>
</div>
