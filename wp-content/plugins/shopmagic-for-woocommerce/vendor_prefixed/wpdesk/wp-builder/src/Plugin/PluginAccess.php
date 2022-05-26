<?php

namespace ShopMagicVendor\WPDesk\PluginBuilder\Plugin;

/**
 * @package WPDesk\PluginBuilder\Plugin
 */
trait PluginAccess
{
    /**
     * Plugin.
     *
     * @var AbstractPlugin
     */
    private $plugin;
    /**
     * Set plugin.
     *
     * @param AbstractPlugin $plugin Plugin.
     */
    public function set_plugin(\ShopMagicVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin $plugin)
    {
        $this->plugin = $plugin;
    }
    /**
     * Get plugin.
     *
     * @return AbstractPlugin
     */
    public function get_plugin()
    {
        return $this->plugin;
    }
}
