<?php
/**
 * Account Disabled
 *
 * This is shown if a users Portal access is disabled
 *
 * @author 		ZeroBSCRM
 * @package 	Templates/Portal/Disabled
 * @see			https://jetpackcrm.com/kb/
 * @version     3.0
 * 
 */



if ( ! defined( 'ABSPATH' ) ) exit; // Don't allow direct access

//zeroBS_portal_enqueue_stuff();

do_action( 'zbs_enqueue_scripts_and_styles' );
?>

<div class="zbs-client-portal-wrap">

	<div class='zbs-portal-wrapper zbs-portal-invoices-list'>
			<h1><?php _e("Access Disabled", "zero-bs-crm"); ?></h1>
			<p>
			<?php _e("Currently your client portal access is disabled.", "zero-bs-crm"); ?>
			</p>
	</div>

</div>
<div style="clear:both"></div>
