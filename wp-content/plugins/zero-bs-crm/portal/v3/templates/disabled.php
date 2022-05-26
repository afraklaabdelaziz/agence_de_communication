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

<div class="alignwide zbs-site-main">

	<div class="zbs-portal-content">
		<h2><?php _e("Access Disabled", "zero-bs-crm"); ?></h2>
		<div class='zbs-entry-content' style="position:relative;">
		<p>
		<?php _e("Currently your client portal access is disabled.", "zero-bs-crm"); ?>
		</p>
		</div>
	</div>
	<div class="zbs-portal-grid-footer"><?php zeroBSCRM_portalFooter(); ?></div>
</div>