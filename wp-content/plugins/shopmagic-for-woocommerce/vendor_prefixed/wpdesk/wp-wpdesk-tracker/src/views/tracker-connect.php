<?php

namespace ShopMagicVendor;

if (!\defined('ABSPATH')) {
    exit;
}
?>
<div id="wpdesk_tracker_connect" class="plugin-card">
	<div class="message plugin-card-top">
        <span class="wpdesk-logo"></span>

		<p>
			<?php 
\printf(\esc_html__('Hey %s,', 'shopmagic-for-woocommerce'), \esc_html($username));
?><br/>
			<?php 
\esc_html_e('Please help us improve our plugins! If you opt-in, we will collect some non-sensitive data and usage information anonymously. If you skip this, that\'s okay! All plugins will work just fine.', 'shopmagic-for-woocommerce');
?>
		</p>
	</div>

	<div class="actions plugin-card-bottom">
		<a id="wpdesk_tracker_allow_button" href="<?php 
echo \esc_url($allow_url);
?>" class="button button-primary button-allow button-large"><?php 
\esc_html_e('Allow & Continue &rarr;', 'shopmagic-for-woocommerce');
?></a>
		<a href="<?php 
echo \esc_url($skip_url);
?>" class="button button-secondary"><?php 
\esc_html_e('Skip', 'shopmagic-for-woocommerce');
?></a>
		<div class="clear"></div>
	</div>

	<div class="permissions">
		<a class="trigger" href="#"><?php 
\esc_html_e('What permissions are being granted?', 'shopmagic-for-woocommerce');
?></a>

		<div class="permissions-details">
		    <ul>
		    	<li id="permission-site" class="permission site">
		    		<i class="dashicons dashicons-admin-settings"></i>
		    		<div>
		    			<span><?php 
\esc_html_e('Your Site Overview', 'shopmagic-for-woocommerce');
?></span>
		    			<p><?php 
\esc_html_e('WP version, PHP info', 'shopmagic-for-woocommerce');
?></p>
		    		</div>
		    	</li>
		    	<li id="permission-events" class="permission events">
		    		<i class="dashicons dashicons-admin-plugins"></i>
		    		<div>
		    			<span><?php 
\esc_html_e('Plugin Usage', 'shopmagic-for-woocommerce');
?></span>
		    			<p><?php 
\esc_html_e('Current settings and usage information of WP Desk plugins', 'shopmagic-for-woocommerce');
?></p>
		    		</div>
		    	</li>
		    	<li id="permission-store" class="permission store">
		    		<i class="dashicons dashicons-store"></i>
		    		<div>
		    			<span><?php 
\esc_html_e('Your Store Overview', 'shopmagic-for-woocommerce');
?></span>
		    			<p><?php 
\esc_html_e('Anonymized and non-sensitive store usage information', 'shopmagic-for-woocommerce');
?></p>
		    		</div>
		    	</li>
		    </ul>

            <div class="terms">
                <a href="<?php 
echo \esc_url($terms_url);
?>" target="_blank"><?php 
\esc_html_e('Find out more &raquo;', 'shopmagic-for-woocommerce');
?></a>
            </div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery('.trigger').click(function(e) {
	    e.preventDefault();
	    if (jQuery(this).parent().hasClass('open')) {
            jQuery(this).parent().removeClass('open')
        }
        else {
            jQuery(this).parent().addClass('open');
        }
	});
</script>
<?php 
