<?php
/**
 * @var string $datetime
 * @var string $username
 * @var int $automation_id
 */

use WPDesk\ShopMagic\Admin\Automation\AutomationListActions\ActionDuplicate;
use WPDesk\ShopMagic\Admin\Queue;
use WPDesk\ShopMagic\Admin\Outcome;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<p>
<?php
printf(
	// translators: %1$s time of action trigger.
	// translators: %2$s user who triggered action.
	esc_html__( 'Actions were triggered on %1$s by %2$s.', 'shopmagic-for-woocommerce' ),
	esc_html( $datetime ),
	esc_html( $username )
);
?>
	</p>

<p><?php esc_html_e( 'If you want to run them again, please duplicate this automation.', 'shopmagic-for-woocommerce' ); ?></p>

<div class="metabox-footer">
	<a href="<?php echo esc_url( Queue\ListMenu::get_url( $automation_id ) ); ?>"><?php esc_html_e( 'Queue', 'shopmagic-for-woocommerce' ); ?></a> /
	<a href="<?php echo esc_url( Outcome\ListPage::get_url( $automation_id ) ); ?>"><?php esc_html_e( 'Outcomes', 'shopmagic-for-woocommerce' ); ?></a> /
	<a href="<?php echo esc_url( ActionDuplicate::get_duplication_url( $automation_id, [ 'referer' => 'manual_action' ] ) ); ?>"><?php esc_html_e( 'Duplicate', 'shopmagic-for-woocommerce' ); ?></a>
</div>
