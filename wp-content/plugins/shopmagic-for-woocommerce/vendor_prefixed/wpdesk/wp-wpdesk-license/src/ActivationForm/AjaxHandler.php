<?php

namespace ShopMagicVendor\WPDesk\License\ActivationForm;

use ShopMagicVendor\WPDesk\License\LicenseManager;
use ShopMagicVendor\WPDesk\License\Page\LicensePageActions;
use ShopMagicVendor\WPDesk\License\PluginLicense;
use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use ShopMagicVendor\WPDesk_Plugin_Info;
/**
 * Handles License Activation Ajax requests.
 */
class AjaxHandler implements \ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable
{
    const AJAX_ACTION = 'license_activation';
    /**
     * @var WPDesk_Plugin_Info
     */
    private $plugin_info;
    /**
     * @param WPDesk_Plugin_Info $plugin_info .
     */
    public function __construct(\ShopMagicVendor\WPDesk_Plugin_Info $plugin_info)
    {
        $this->plugin_info = $plugin_info;
    }
    /**
     * Hooks.
     */
    public function hooks()
    {
        \add_action('wp_ajax_wpdesk_license_activation_' . $this->plugin_info->get_plugin_file_name(), [$this, 'handle_ajax_action']);
    }
    /**
     * Handles ajax action.
     */
    public function handle_ajax_action()
    {
        \check_ajax_referer(self::AJAX_ACTION, 'security');
        $license_manager = new \ShopMagicVendor\WPDesk\License\LicenseManager($this->plugin_info);
        $license_action = $this->get_post_value_as_string('license_action');
        $plugin_info = ['plugin' => $this->plugin_info->get_plugin_file_name(), 'product_id' => $this->plugin_info->get_product_id(), 'api_manager' => $license_manager->create_api_manager(\false)];
        $license_page_action = (new \ShopMagicVendor\WPDesk\License\Page\LicensePageActions())->create_action($license_action, \false);
        $license_page_action->execute($plugin_info);
        \delete_site_transient('update_plugins');
        \wp_update_plugins();
        $update_plugins = \get_site_transient('update_plugins');
        $update_plugins = \is_array($update_plugins) ? $update_plugins : [$this->plugin_info->get_plugin_file_name() => new \stdClass()];
        $plugin_license = new \ShopMagicVendor\WPDesk\License\PluginLicense($this->plugin_info);
        $form_content_renderer = new \ShopMagicVendor\WPDesk\License\ActivationForm\FormContentRenderer($this->plugin_info, $this->is_update_possible(isset($update_plugins[$this->plugin_info->get_plugin_file_name()]) ?? new \stdClass()), $license_page_action->get_errors());
        \wp_send_json_success(['activation_form_content' => $form_content_renderer->render(), 'is_active' => $plugin_license->is_active(), 'errors' => $license_page_action->get_errors()]);
    }
    /**
     * @param \stdClass $plugin_data .
     *
     * @return bool
     */
    private function is_update_possible($plugin_data)
    {
        if (isset($plugin_data->Version) && isset($plugin_data->new_version) && $plugin_data->Version !== $plugin_data->new_version && empty($plugin_data->package)) {
            return \false;
        }
        return \true;
    }
    /**
     * @param string $key .
     *
     * @return string
     */
    private function get_post_value_as_string(string $key) : string
    {
        $value = \wp_unslash($_POST[$key]);
        // phpcs:ignore.
        return \is_array($value) ? '' : $value;
    }
}
