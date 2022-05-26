<?php

namespace ShopMagicVendor\WPDesk\License\WpUpgrader;

use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable;
/**
 * Can handle plugin update for expired subscription.
 */
class SubscriptionHandler implements \ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable
{
    const UNAVAILABLE = 'unavailable';
    /**
     * @var string
     */
    private $plugin_file;
    /**
     * @var bool
     */
    private $activated;
    /**
     * @var string
     */
    private $my_account_subscription_link;
    /**
     * .
     * @param string $plugin_file                  .
     * @param bool   $activated                    .
     * @param string $my_account_subscription_link .
     */
    public function __construct($plugin_file, $activated, $my_account_subscription_link)
    {
        $this->plugin_file = $plugin_file;
        $this->activated = $activated;
        $this->my_account_subscription_link = $my_account_subscription_link;
    }
    /**
     * .
     */
    public function hooks()
    {
        \add_filter('site_transient_update_plugins', [$this, 'add_fake_package_url_if_not_present']);
        \add_filter('upgrader_pre_download', [$this, 'verify_package_url'], 1000, 4);
    }
    /**
     * .
     *
     * @param \stdClass $value .
     *
     * @return \stdClass
     */
    public function add_fake_package_url_if_not_present($value)
    {
        if (\function_exists('get_current_screen')) {
            $current_screen = \get_current_screen();
            if ($current_screen && 'update' === $current_screen->id && isset($value, $value->response, $value->response[$this->plugin_file]) && empty($value->response[$this->plugin_file]->package)) {
                $value->response[$this->plugin_file]->package = self::UNAVAILABLE;
            }
        }
        return $value;
    }
    /**
     * .
     *
     * @param bool   $reply .
     * @param string $package .
     *
     * @return \WP_Error|bool
     */
    public function verify_package_url($reply, $package, $upgrader, $hook_extra)
    {
        if (self::UNAVAILABLE === $package) {
            if ($this->activated) {
                return new \WP_Error('package_unavailable', \sprintf(\__('Your plugin subscription has expired. In order to update the plugin please %1$srenew your subscription →%2$s', 'shopmagic-for-woocommerce'), '</strong><a href="' . $this->add_utms($this->my_account_subscription_link) . '" class="button-primary" target="_blank" style="text-decoration: none;">', '</a><strong>'));
            }
            return new \WP_Error('package_unavailable', \sprintf(\__('An active subscription API key is required to download the plugin updates. In order to update the plugin please %1$sactivate your API key →%2$s', 'shopmagic-for-woocommerce'), '</strong><a href="' . \admin_url('plugins.php#flexible-shipping-import-export-activation-form') . '" class="button-primary" target="_blank" style="text-decoration: none;">', '</a><strong>'));
        }
        return $reply;
    }
    /**
     * @param string $link .
     *
     * @return string
     */
    private function add_utms($link)
    {
        return \trailingslashit($link) . '?utm_source=update&utm_medium=plugin-list&utm_campaign=subscriptions';
    }
}
