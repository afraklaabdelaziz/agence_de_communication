<?php

namespace ShopMagicVendor;

if (!\defined('ABSPATH')) {
    exit;
}
?>
<div id="wpdesk_tracker_notice" class="updated notice wpdesk_tracker_notice">
	<p><?php 
\esc_html_e('You successfully opted out of collecting usage data by WP Desk. If you change your mind, you can always opt in later in the plugin\'s quick links.', 'shopmagic-for-woocommerce');
?></p>
</div>
<?php 
