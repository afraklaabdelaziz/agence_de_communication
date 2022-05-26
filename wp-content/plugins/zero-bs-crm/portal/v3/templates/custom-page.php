<?php
/**
 * Custom Page Template
 *
 * The child-page template
 *
 * @author 		ZeroBSCRM
 * @package 	Templates/Portal/Custom
 * @see			https://jetpackcrm.com/kb/
 * @version     3.0
 * 
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Don't allow direct access
do_action( 'zbs_enqueue_scripts_and_styles' );

	// setup some variables
	global $post;
	$post_slug = $post->post_name;
	$fullWidth = false;
	$showNav = true;
	$canView = true;

?>
<div class="entry">
	<div class="entry-content">
		<div class="alignwide zbs-site-main zbs-portal-grid">
		<?php
				if ($showNav){
					echo '<nav class="zbs-portal-nav">';
					zeroBS_portalnav($post_slug);
					echo '</nav>';
				}
				echo '<div class="zbs-portal-content">';
				if (!$canView){
					echo '<div class="zbs-alert-danger">' . __("<b>Error:</b> You are not allowed to view this Page","zero-bs-crm") . '</div>';
				} else {
					$the_title 		= apply_filters('the_title', $post->post_title);
					$the_content 	= apply_filters('the_content', $post->post_content);
					echo '<h2>' . $the_title . '</h2>';
					echo "<div class='zbs-entry-content' style='position:relative;'>";
					echo $the_content;
					echo "</div>";
				}  ?>
			</div>
			<div class="zbs-portal-grid-footer"><?php zeroBSCRM_portalFooter(); ?></div>
		</div>
	</div>
</div>