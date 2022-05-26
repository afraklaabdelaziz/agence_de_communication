<?php
/**
 * ShopMagic's Admin Alerts and Popups
 *
 * Prepare admin area classes and variables
 *
 * @package ShopMagic
 * @version 1.4.0
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_notices', 'shopmagic_admin_notice_getstarted' );
/**
 * Display Get Started Admin Notice in Dashboard
 *
 * @since   1.1.1
 */
function shopmagic_admin_notice_getstarted() {

	global $current_user;
	$user_id     = $current_user->ID;
	$notice_name = 'getting_started';

	if ( ! get_user_meta( $user_id, 'shopmagic_ignore_notice_' . $notice_name ) ) {

		echo '<div class="notice notice-success is-dismissible shopmagic-getting-started-notice" data-notice-name="' . $notice_name . '">';
		echo '<p>';
		echo esc_html__( 'Thank you for installing ShopMagic for WooCommerce!', 'shopmagic-for-woocommmerce' );
		echo ' <a href="' . admin_url( 'edit.php?post_type=shopmagic_automation&page=shopmagic_welcome_page' ) . '" target="_blank">' . esc_html__( 'Learn how to get started', 'shopmagic-for-woocommerce' ) . '</a> ';
		echo esc_html__( 'or', 'shopmagic-for-woocommerce' );
		echo ' <a href="' . admin_url( 'post-new.php?post_type=shopmagic_automation' ) . '" target="_blank">' . esc_html__( 'create your first automation', 'shopmagic-for-woocommerce' ) . '</a> &rarr;';
		echo '</p>';
		echo '</div>';
	}
}

add_action(
	'admin_notices',
	function() use ( $pro_is_active ) {
		$user_id        = get_current_user_id();
		$current_screen = get_current_screen();
		$notice_name    = 'pro';
		$time_dismissed = get_user_meta( $user_id, 'shopmagic_ignore_notice_' . $notice_name, true );
		$show_after     = $time_dismissed ? $time_dismissed + MONTH_IN_SECONDS : ''; // Will show again after 1 month.

		if ( $current_screen->parent_base === 'edit' && $current_screen->post_type === 'shopmagic_automation' && current_user_can( 'manage_options' ) && ! $pro_is_active && time() > $show_after ) {
			?>
		<div class="notice notice-info shopmagic-pro-notice is-dismissible" data-notice-name="<?php echo esc_attr( $notice_name ); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
				<path d="M224 96l16-32 32-16-32-16-16-32-16 32-32 16 32 16 16 32zM80 160l26.66-53.33L160 80l-53.34-26.67L80 0 53.34 53.33 0 80l53.34 26.67L80 160zm352 128l-26.66 53.33L352 368l53.34 26.67L432 448l26.66-53.33L512 368l-53.34-26.67L432 288zm70.62-193.77L417.77 9.38C411.53 3.12 403.34 0 395.15 0c-8.19 0-16.38 3.12-22.63 9.38L9.38 372.52c-12.5 12.5-12.5 32.76 0 45.25l84.85 84.85c6.25 6.25 14.44 9.37 22.62 9.37 8.19 0 16.38-3.12 22.63-9.37l363.14-363.15c12.5-12.48 12.5-32.75 0-45.24zM359.45 203.46l-50.91-50.91 86.6-86.6 50.91 50.91-86.6 86.6z"/>
			</svg>

			<h2><?php esc_html_e( 'Do you want to do more to increase your sales? Learn about ShopMagic PRO', 'shopmagic-for-woocommerce' ); ?></h2>

			<p><?php esc_html_e( 'ShopMagic Pro lets you boost your superpowers as an ecommerce seller by encouraging more sales from your best customers.', 'shopmagic-for-woocommerce' ); ?></p>

			<?php
			if ( get_locale() === 'pl_PL' ) {
				$base_url              = 'https://www.wpdesk.pl/sklep/shopmagic';
				$delayed_actions_slug  = '';
				$review_requests_slug  = '';
				$customer_coupons_slug = '';
				$manual_actions_slug   = '';
				$advanced_filters_slug = '';
			} else {
				$base_url              = 'https://shopmagic.app/products/';
				$delayed_actions_slug  = 'shopmagic-delayed-actions';
				$review_requests_slug  = 'shopmagic-review-requests';
				$customer_coupons_slug = 'shopmagic-customer-coupons';
				$manual_actions_slug   = 'shopmagic-manual-actions';
				$advanced_filters_slug = 'shopmagic-advanced-filters';
			}
			?>

			<p>
				<a href="<?php echo esc_url( $base_url . $delayed_actions_slug ); ?>/?utm_source=pro-notice&utm_medium=link&utm_campaign=shopmagic-notice&utm_content=delayed-actions"
				   target="blank"><?php esc_html_e( 'Delayed Actions', 'shopmagic-for-woocommerce' ); ?></a> | <a
						href="<?php echo esc_url( $base_url . $review_requests_slug ); ?>/?utm_source=pro-notice&utm_medium=link&utm_campaign=shopmagic-notice&utm_content=review-requests"
						target="blank"><?php esc_html_e( 'Review Requests', 'shopmagic-for-woocommerce' ); ?></a> | <a
						href="<?php echo esc_url( $base_url . $customer_coupons_slug ); ?>/?utm_source=pro-notice&utm_medium=link&utm_campaign=shopmagic-notice&utm_content=customer-coupons"
						target="blank"><?php esc_html_e( 'Customer Coupons', 'shopmagic-for-woocommerce' ); ?></a> | <a
						href="<?php echo esc_url( $base_url . $manual_actions_slug ); ?>/?utm_source=pro-notice&utm_medium=link&utm_campaign=shopmagic-notice&utm_content=add-to-mailing-list"
						target="blank"><?php esc_html_e( 'Manual Emails', 'shopmagic-for-woocommerce' ); ?></a> | <a
						href="<?php echo esc_url( $base_url ); ?>/?utm_source=pro-notice&utm_medium=link&utm_campaign=shopmagic-notice&utm_content=and-more"
						target="blank"><?php esc_html_e( 'and more...', 'shopmagic-for-woocommerce' ); ?></a></p>
		</div>
			<?php
		}
	}
);

add_action( 'wp_ajax_shopmagic_notice_dismiss', 'shopmagic_get_started_notice_dismiss' );
/**
 * If user dismisses Getting Started notice, record in user settings
 *
 * @since 1.4
 */
function shopmagic_get_started_notice_dismiss() {

	if ( isset( $_POST['notice_name'] ) ) {
		$notice_name = $_POST['notice_name'];
		$user_id     = get_current_user_id();

		update_user_meta( $user_id, 'shopmagic_ignore_notice_' . $notice_name, time() );
	}
}

add_action( 'admin_head', 'shopmagic_get_started_notice_script' );
/**
 * Add script for dismissing Getting Started notice
 *
 * @since 1.4
 */
function shopmagic_get_started_notice_script() {
	?>
	<script type="text/javascript">
		jQuery(document).on('click', '.notice-dismiss', function () {
			var notice_name = jQuery(this).closest('.notice').data('notice-name');
			if ('' !== notice_name) {
				jQuery.ajax({
					url: ajaxurl,
					type: 'post',
					data: {
						action: 'shopmagic_notice_dismiss',
						notice_name: notice_name
					},
					success: function (response) {
					}
				});
			}
		});
	</script>
	<?php
}
