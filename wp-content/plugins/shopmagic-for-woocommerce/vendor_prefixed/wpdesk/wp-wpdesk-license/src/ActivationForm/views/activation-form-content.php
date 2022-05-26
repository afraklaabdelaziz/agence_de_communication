<?php

namespace ShopMagicVendor;

use ShopMagicVendor\WPDesk\License\Page\License\Action\ActionError;
/**
 * @var $plugin_slug             string
 * @var $api_key                 string
 * @var $activation_email        string
 * @var $is_active               bool
 * @var $errors                  ActionError[]
 * @var $my_account_link         string
 * @var $docs_link               string
 * @var $update_possible         bool
 * @var $renew_subscription_link string
 */
foreach ($errors as $error) {
    ?>
	<div class="wpdesk-license-notice notice inline notice-alt <?php 
    echo \esc_html($error->get_type());
    ?>">
		<?php 
    echo \wp_kses_post($error->get_message());
    ?>
	</div>
<?php 
}
?><p class="introduction">
<?php 
if ($is_active) {
    if ($update_possible) {
        \esc_html_e(\__('Plugin\'s API key has been successfully activated. You are now fully eligible to receive plugin updates and support.', 'shopmagic-for-woocommerce'));
    } else {
        \esc_html_e(\__('Plugin\'s API key has been successfully activated, but your subscription has expired. In order to receive the upcoming updates and support please renew your subscription.', 'shopmagic-for-woocommerce'));
    }
} else {
    echo \esc_html(\__('In order to receive the upcoming updates and support please enter the plugin\'s API key obtained after the purchase.', 'shopmagic-for-woocommerce'));
    if ($my_account_link && $docs_link) {
        // Translators: my account link.
        echo \wp_kses_post(' ' . \sprintf(
            // Translators: my account link and docs link.
            \__('You can find it in %1$sMy Account / API Keys%2$s tab. %3$sLearn more about activating the API key →%4$s', 'shopmagic-for-woocommerce'),
            \sprintf('<a href="%1$s" target="_blank">', $my_account_link),
            '</a>',
            \sprintf('<a href="%1$s" target="_blank">', $docs_link),
            '</a>'
        ));
    }
}
?>
</p>
	<div class="api-key">
		<?php 
echo \esc_html(\__('Key: ', 'shopmagic-for-woocommerce'));
?> <input type="text" name="api_key" value="<?php 
echo \esc_html($api_key);
?>" placeholder="" <?php 
\disabled($is_active);
?> />
	</div>
	<div class="activation-button">
		<?php 
if ($is_active) {
    ?>
			<button class="deactivate button"><?php 
    echo \esc_html(\__('Deactivate license', 'shopmagic-for-woocommerce'));
    ?></button><span class="spinner"></span>
			<?php 
    if (!$update_possible) {
        ?>
				<a href="<?php 
        echo \esc_url($renew_subscription_link);
        ?>" class="button-primary" target="_blank" style="text-decoration: none;"><?php 
        echo \esc_html(\__('Renew subscription →', 'shopmagic-for-woocommerce'));
        ?></a>
			<?php 
    }
    ?>
		<?php 
} else {
    ?>
			<button class="activate button button-primary"><?php 
    echo \esc_html(\__('Activate license', 'shopmagic-for-woocommerce'));
    ?></button><span class="spinner"></span>
		<?php 
}
?>
	</div>
<?php 
