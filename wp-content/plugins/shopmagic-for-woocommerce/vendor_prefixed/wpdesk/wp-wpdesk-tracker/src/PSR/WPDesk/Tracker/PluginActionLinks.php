<?php

namespace ShopMagicVendor\WPDesk\Tracker;

use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable;
/**
 * Can add Plugin actions links: opt-in/opt-out to tracker.
 */
class PluginActionLinks implements \ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable
{
    /**
     * @var string
     */
    private $plugin_file;
    /**
     * @var string
     */
    private $plugin_slug;
    /**
     * @var string
     */
    private $shop_url;
    /**
     * @param string $plugin_file
     * @param string $plugin_slug
     * @param string $shop_url
     */
    public function __construct($plugin_file, $plugin_slug, $shop_url)
    {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = $plugin_slug;
        $this->shop_url = $shop_url;
    }
    public function hooks()
    {
        \add_filter('plugin_action_links_' . $this->plugin_file, array($this, 'append_plugin_action_links'));
    }
    /**
     * @param array $links .
     */
    public function append_plugin_action_links($links)
    {
        if (!$this->tracker_enabled() || \apply_filters('wpdesk_tracker_do_not_ask', \false) || !\is_array($links)) {
            return $links;
        }
        $tracker_consent = new \ShopMagicVendor\WPDesk_Tracker_Persistence_Consent();
        $plugin_links = array();
        if (!$tracker_consent->is_active()) {
            $opt_in_link = \admin_url('admin.php?page=wpdesk_tracker_' . $this->plugin_slug . '&shop_url=' . $this->shop_url);
            $plugin_links[] = '<a href="' . \esc_url($opt_in_link) . '">' . \esc_html__('Opt-in', 'shopmagic-for-woocommerce') . '</a>';
        } else {
            $opt_in_link = \admin_url('plugins.php?wpdesk_tracker_opt_out_' . $this->plugin_slug . '=1&security=' . \wp_create_nonce($this->plugin_slug));
            $plugin_links[] = '<a href="' . \esc_url($opt_in_link) . '">' . \esc_html__('Opt-out', 'shopmagic-for-woocommerce') . '</a>';
        }
        return \array_merge($plugin_links, $links);
    }
    /**
     * @return bool
     */
    private function tracker_enabled()
    {
        $tracker_enabled = \true;
        if (!empty($_SERVER['SERVER_ADDR']) && $this->is_localhost($_SERVER['SERVER_ADDR'])) {
            $tracker_enabled = \false;
        }
        return (bool) \apply_filters('wpdesk_tracker_enabled', $tracker_enabled);
    }
    /**
     * @param string $address
     *
     * @return bool
     */
    private function is_localhost($address)
    {
        return \in_array($address, ['127.0.0.1', '::1'], \true);
    }
}
