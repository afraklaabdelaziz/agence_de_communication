<?php
/**
 * @var \WPDesk\ShopMagic\AutomationOutcome\SingleOutcome $outcome
 */

use WPDesk\ShopMagic\Admin\Outcome;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wrap">
	<h1 class="wp-heading-inline">
	<?php
		// translators: %s outcome ID.
		echo sprintf( esc_html__( 'Outcome log #%s', 'shopmagic-for-woocommerce' ), esc_html( $outcome->get_execution_id() ) );
	?>
	</h1>

	<?php if ( count( $outcome->get_logs() ) === 0 ) : ?>
		<p><?php esc_html_e( 'Outcome log is empty', 'shopmagic-for-woocommerce' ); ?></p>
	<?php else : ?>
		<table class="form-table">
			<tbody>
			<?php foreach ( $outcome->get_logs() as $log ) : ?>
				<tr>
					<th><?php esc_html_e( 'Timestamp', 'shopmagic-for-woocommerce' ); ?></th>
					<td><?php echo esc_html( \WPDesk\ShopMagic\Helper\WordPressFormatHelper::format_wp_datetime_with_seconds( $log->get_created_date() ) ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Message', 'shopmagic-for-woocommerce' ); ?></th>
					<td><?php echo esc_html( $log->get_note() ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Context', 'shopmagic-for-woocommerce' ); ?></th>
					<td>
						<?php foreach ( $log->get_context() as $key => $value ) : ?>
							<p><strong><?php echo esc_html( $key ); ?>: </strong></p>
							<pre>
								<?php echo esc_html( var_export( $value, true ) ); // phpcs:ignore WordPress.PHP.DevelopementFunctions ?>
							</pre>
						<?php endforeach; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<a href="<?php echo esc_url( Outcome\ListPage::get_url() ); ?>"><?php esc_html_e( '&larr; Go back', 'shopmagic-for-woocommerce' ); ?></a>
</div>
