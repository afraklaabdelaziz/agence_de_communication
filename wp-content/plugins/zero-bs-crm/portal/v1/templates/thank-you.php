<?php
/**
 * Payment Thank You
 *
 * This is used as a 'Payment Confirmation' when payment success
 *
 * @author 		ZeroBSCRM
 * @package 	Templates/Portal/Thanks
 * @see			https://jetpackcrm.com/kb/
 * @version     3.0
 * 
 */



if ( ! defined( 'ABSPATH' ) ) exit; // Don't allow direct access
do_action( 'zbs_enqueue_scripts_and_styles' );
//zeroBS_portal_enqueue_stuff();

	$uid = get_current_user_id();
	$uinfo = get_userdata( $uid );
	$cID = zeroBS_getCustomerIDWithEmail($uinfo->user_email);
	
?>
<div id="zbs-main" class="zbs-site-main">
	<div class="zbs-client-portal-wrap main site-main zbs-post zbs-hentry">
		<?php
			zeroBS_portalnav('dashboard');
		?>
		<div class='zbs-portal-wrapper zbs-portal-invoices-list'>
			<?php 
				// preview msg		
				zeroBSCRM_portal_adminPreviewMsg($cID,'margin-bottom:1em');

				// admin msg (upsell cpp) (checks perms itself, safe to run)
				zeroBSCRM_portal_adminMsg();

			?>
				<h1><?php _e("Thank You", "zero-bs-crm"); ?></h1>
				<p>
				<?php _e("Thank you for your payment.", "zero-bs-crm"); ?>
				</p>
		</div>
		<div style="clear:both"></div>
		<?php zeroBSCRM_portalFooter(); ?>
	</div>
</div>
