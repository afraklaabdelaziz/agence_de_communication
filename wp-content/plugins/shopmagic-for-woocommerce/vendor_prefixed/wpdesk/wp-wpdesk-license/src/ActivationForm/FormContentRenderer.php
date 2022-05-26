<?php

namespace ShopMagicVendor\WPDesk\License\ActivationForm;

use ShopMagicVendor\WPDesk\License\Page\License\Action\ActionError;
use ShopMagicVendor\WPDesk\License\PluginLicense;
use ShopMagicVendor\WPDesk_Plugin_Info;
/**
 * Can render License Activation form content.
 */
class FormContentRenderer
{
    const SHOP_WWW_WPDESK_PL = 'https://www.wpdesk.pl/';
    const MY_ACCOUNT_API_KEYS_EN = 'my-account/api-keys/';
    const MY_ACCOUNT_API_KEYS_PL = 'moje-konto/api-keys/';
    const DOCS_EN = 'docs/how-to-activate-licenses';
    const DOCS_PL = 'docs/licencje-wtyczek/';
    /**
     * @var WPDesk_Plugin_Info
     */
    private $plugin_info;
    /**
     * @var \stdClass
     */
    private $update_possible;
    /**
     * @var ActionError[]
     */
    private $errors;
    /**
     * @param WPDesk_Plugin_Info $plugin_info .
     * @param bool               $update_possible .
     * @param ActionError[]      $errors .
     */
    public function __construct(\ShopMagicVendor\WPDesk_Plugin_Info $plugin_info, bool $update_possible, array $errors = [])
    {
        $this->plugin_info = $plugin_info;
        $this->update_possible = $update_possible;
        $this->errors = $errors;
    }
    /**
     * @return string
     */
    public function render() : string
    {
        \ob_start();
        $this->output_render();
        return \ob_get_clean();
    }
    public function output_render()
    {
        $plugin_license = new \ShopMagicVendor\WPDesk\License\PluginLicense($this->plugin_info);
        $is_active = $plugin_license->is_active();
        $plugin_slug = $this->plugin_info->get_plugin_slug();
        $api_key = $plugin_license->get_api_key();
        $activation_email = $plugin_license->get_activation_email();
        $plugin_file = $this->plugin_info->get_plugin_file_name();
        $errors = $this->errors;
        $my_account_link = $this->prapare_my_account_link_according_to_locale_and_shop(\get_locale(), $this->plugin_info->get_plugin_shops());
        $docs_link = $this->prapare_docs_link_according_to_locale_and_shop(\get_locale(), $this->plugin_info->get_plugin_shops());
        $update_possible = $this->update_possible;
        $renew_subscription_link = $this->prepare_renew_subscription_link_according_to_shop($plugin_license->get_upgrade_url());
        include __DIR__ . '/views/activation-form-content.php';
    }
    /**
     * @param string $upgrade_url .
     *
     * @return string
     */
    private function prepare_renew_subscription_link_according_to_shop($upgrade_url)
    {
        $utms = '?utm_source=renew&utm_medium=plugin-list&utm_campaign=subscriptions';
        return \trailingslashit($upgrade_url) . '/my-account/' . $utms;
    }
    /**
     * @param string               $locale .
     * @param array<string,string> $plugin_shops .
     *
     * @return string
     */
    private function prapare_my_account_link_according_to_locale_and_shop(string $locale, array $plugin_shops) : string
    {
        $default = 'default';
        $default_shop = $plugin_shops[$default] ?? '';
        $shop_url = $plugin_shops[$locale] ?? $default_shop;
        $my_account = self::SHOP_WWW_WPDESK_PL !== $shop_url ? self::MY_ACCOUNT_API_KEYS_EN : self::MY_ACCOUNT_API_KEYS_PL;
        $utms = '?utm_source=my-account-list&utm_medium=link&utm_campaign=licenses';
        return isset($shop_url) ? \trailingslashit($shop_url) . $my_account . $utms : '';
    }
    /**
     * @param string               $locale .
     * @param array<string,string> $plugin_shops .
     *
     * @return string
     */
    private function prapare_docs_link_according_to_locale_and_shop(string $locale, array $plugin_shops) : string
    {
        $default = 'default';
        $default_shop = $plugin_shops[$default] ?? '';
        $shop_url = $plugin_shops[$locale] ?? $default_shop;
        $docs = self::SHOP_WWW_WPDESK_PL !== $shop_url ? self::DOCS_EN : self::DOCS_PL;
        $utms = '?utm_source=docs&utm_medium=link&utm_campaign=licenses';
        return isset($shop_url) ? \trailingslashit($shop_url) . $docs . $utms : '';
    }
}
