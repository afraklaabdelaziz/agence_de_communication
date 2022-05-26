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

<div id="zbs-main" class="zbs-site-main" style="position:relative;">
	
	<div class="zbs-client-portal-wrap main site-main zbs-portal zbs-hentry">

		<?php
			//moved into func
			zeroBS_portalnav('dashboard');
		?>
		<div class='zbs-portal-wrapper'>
			<?php zeroBSCRM_portal_adminPreviewMsg($cID); ?>
			<?php 
				// admin msg (upsell cpp) (checks perms itself, safe to run)
				zeroBSCRM_portal_adminMsg();

				$page_title = __("Welcome to your Dashboard","zero-bs-crm");
				$page_title = apply_filters('zbs_portal_dashboard_title', $page_title);
			?>
		<h1><?php echo $page_title; ?></h1>

		<div class='content' style="position:relative;">
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
		<div style="clear:both"></div>

	</div>

	<div style="clear:both"></div>

	<?php zeroBSCRM_portalFooter(); ?>

</div>