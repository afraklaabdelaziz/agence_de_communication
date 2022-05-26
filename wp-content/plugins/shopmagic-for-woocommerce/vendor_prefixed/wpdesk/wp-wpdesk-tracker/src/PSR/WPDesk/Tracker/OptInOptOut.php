<?php

namespace ShopMagicVendor\WPDesk\Tracker;

use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use ShopMagicVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
class OptInOptOut implements \ShopMagicVendor\WPDesk\PluginBuilder\Plugin\HookableCollection
{
    use HookableParent;
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
     * @var string
     */
    private $plugin_name;
    /**
     * @param string $plugin_file
     * @param string $plugin_slug
     * @param string $shop_url
     * @param string $plugin_name
     *
     */
    public function __construct($plugin_file, $plugin_slug, $shop_url, $plugin_name)
    {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = $plugin_slug;
        $this->shop_url = $shop_url;
        $this->plugin_name = $plugin_name;
    }
    /**
     * Creates hookable objects.
     */
    public function create_objects()
    {
        $this->add_hookable(new \ShopMagicVendor\WPDesk\Tracker\PluginActionLinks($this->plugin_file, $this->plugin_slug, $this->shop_url));
        $this->add_hookable(new \ShopMagicVendor\WPDesk\Tracker\OptInPage($this->plugin_file, $this->plugin_slug));
        $this->add_hookable(new \ShopMagicVendor\WPDesk\Tracker\OptOut($this->plugin_slug, $this->plugin_name));
        $this->add_hookable(new \ShopMagicVendor\WPDesk\Tracker\Assets($this->plugin_slug));
    }
    public function hooks()
    {
        $this->hooks_on_hookable_objects();
    }
}
