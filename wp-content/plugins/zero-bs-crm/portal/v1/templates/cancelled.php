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

<div id="zbs-main" class="zbs-site-main">
	<div class="zbs-client-portal-wrap main site-main zbs-post zbs-hentry">

		<?php
			//moved into func
			zeroBS_portalnav('dashboard');
		?>

	<div class='zbs-portal-wrapper zbs-portal-invoices-list'>
			<h1><?php _e("Payment Cancelled", "zero-bs-crm"); ?></h1>
			<p>
			<?php _e("Your payment was cancelled.", "zero-bs-crm"); ?>
			</p>
	</div>

</div>

<div style="clear:both"></div>
</div>
