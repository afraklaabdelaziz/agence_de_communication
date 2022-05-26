<?php
/**
 * Portal Dashboard Page
 *
 * This is used as the main dashboard page of the portal
 *
 * @author 		ZeroBSCRM
 * @package 	Templates/Portal/Dashboard
 * @see			https://jetpackcrm.com/kb/
 * @version     3.0
 * 
 */

 
if ( ! defined( 'ABSPATH' ) ) exit; // Don't allow direct access

do_action( 'zbs_enqueue_scripts_and_styles' );

#} was added through above action too, then re-called directly
//zeroBS_portal_enqueue_stuff();

    $uid = get_current_user_id();
    $uinfo = get_userdata( $uid );
    $cID = zeroBS_getCustomerIDWithEmail($uinfo->user_email);
?>

<div class="alignwide zbs-site-main zbs-portal-grid">
    <nav class="zbs-portal-nav">
        <?php
            zeroBS_portalnav('dashboard');
        ?>
    </nav>

    <div class="zbs-portal-content">
			<?php zeroBSCRM_portal_adminPreviewMsg($cID); ?>
			<?php 
				// admin msg (upsell cpp) (checks perms itself, safe to run)
				zeroBSCRM_portal_adminMsg();

				$page_title = __("Welcome to your Dashboard","zero-bs-crm");
				$page_title = apply_filters('zbs_portal_dashboard_title', $page_title);
			?>
		<h2><?php echo $page_title; ?></h2>
		<div class='zbs-entry-content' style="position:relative;">
			<p>
				<?php
				//add actions for additional content
				do_action('zbs_pre_dashboard_content');

				$dashboard = __("Welcome to your Client Portal dashboard. From here you can view your information using the portal navigation bar.", "zero-bs-crm");
				//added so this can be modified, with a shortcode too 
				$dashboard = apply_filters('zbs_portal_dashboard_content' , $dashboard);
				echo $dashboard;

				do_action('zbs_post_dashboard_content');
				?>
			</p>
		</div>
	</div>
    <div class="zbs-portal-grid-footer"><?php zeroBSCRM_portalFooter(); ?></div>
</div>