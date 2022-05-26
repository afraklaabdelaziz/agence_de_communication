<?php
/**
 * Payment Cancelled Page
 *
 * This is used as a 'Payment Cancelled' page following a cancelled payment
 *
 * @author 		ZeroBSCRM
 * @package 	Templates/Portal/Cancelled
 * @see			https://jetpackcrm.com/kb/
 * @version     3.0
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Don't allow direct access

//zeroBS_portal_enqueue_stuff();

do_action( 'zbs_enqueue_scripts_and_styles' );
?>
<div class="alignwide zbs-site-main zbs-portal-grid">
	<nav class="zbs-portal-nav">
		<?php
			//moved into func
			zeroBS_portalnav('dashboard');
		?>
	</nav>

	<div class="zbs-portal-content">
			<h2><?php _e("Payment Cancelled", "zero-bs-crm"); ?></h2>
			<div class='zbs-entry-content' style="position:relative;">
				<p>
				<?php _e("Your payment was cancelled.", "zero-bs-crm"); ?>
				</p>
			</div>
	</div>
	<div class="zbs-portal-grid-footer"><?php zeroBSCRM_portalFooter(); ?></div>
</div>
