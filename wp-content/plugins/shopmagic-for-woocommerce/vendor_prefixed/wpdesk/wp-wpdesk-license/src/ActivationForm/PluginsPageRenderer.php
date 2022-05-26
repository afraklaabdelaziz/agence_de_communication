<?php

namespace ShopMagicVendor\WPDesk\License\ActivationForm;

use ShopMagicVendor\WPDesk\License\PluginLicense;
use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable;
use ShopMagicVendor\WPDesk_Plugin_Info;
/**
 * Can render activation form on plugins page.
 */
class PluginsPageRenderer implements \ShopMagicVendor\WPDesk\PluginBuilder\Plugin\Hookable
{
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
    public function hooks()
    {
        \add_action('after_plugin_row_' . $this->plugin_info->get_plugin_file_name(), [$this, 'display_activation_form_in_table_row'], 10, 3);
    }
    /**
     * Displays activation form.
     *
     * @param string $plugin_file .
     * @param array  $plugin_data .
     * @param string $status .
     */
    public function display_activation_form_in_table_row($plugin_file, $plugin_data, $status)
    {
        $this->output_render($plugin_file, $plugin_data);
    }
    /**
     * @param string $plugin_file .
     * @param array  $plugin_data .
     *
     * @return string
     */
    public function render(string $plugin_file, $plugin_data)
    {
        \ob_start();
        $this->output_render($plugin_file, $plugin_data);
        return \ob_get_clean();
    }
    /**
     * @param string $plugin_file .
     * @param array  $plugin_data .
     */
    public function output_render(string $plugin_file, $plugin_data)
    {
        $plugin_license = new \ShopMagicVendor\WPDesk\License\PluginLicense($this->plugin_info);
        $plugin_slug = $this->plugin_info->get_plugin_slug();
        $is_active = $plugin_license->is_active();
        $update_possible = $this->is_update_possible($plugin_data);
        $form_content = new \ShopMagicVendor\WPDesk\License\ActivationForm\Renderer($this->plugin_info, $update_possible);
        include __DIR__ . '/views/plugins-page-row.php';
    }
    /**
     * @param array $plugin_data .
     *
     * @return bool
     */
    private function is_update_possible(array $plugin_data)
    {
        if (isset($plugin_data['Version']) && isset($plugin_data['new_version']) && $plugin_data['Version'] !== $plugin_data['new_version'] && empty($plugin_data['package'])) {
            return \false;
        }
        return \true;
    }
}
