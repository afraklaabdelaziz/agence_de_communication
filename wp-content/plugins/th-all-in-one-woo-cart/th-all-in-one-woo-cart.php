<?php
/**
 * Plugin Name:             TH All In One Woo Cart
 * Plugin URI:              https://themehunk.com/th-all-in-one-woo-cart/
 * Description:             TH All In One Woo Cart is a perfect choice to display Cart on your website and improve your potential customer’s buying experience. This plugin will add Floating Cart in your website.  Customers can update or remove products from the cart without reloading the cart continuously. It is a fully Responsive, mobile friendly plugin and supports many advanced features.
 * Version:                 1.0.8
 * Author:                  ThemeHunk
 * Author URI:              https://themehunk.com
 * Requires at least:       4.8
 * Tested up to:            5.9.3
 * WC requires at least:    3.2
 * WC tested up to:         5.1
 * Domain Path:             /languages
 * Text Domain:             taiowc
 * Tags: floating cart,ajax,cart,woocommerce,woocommerce cart,slider
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if (!defined('TAIOWC_PLUGIN_FILE')) {
    define('TAIOWC_PLUGIN_FILE', __FILE__);
}

if (!defined('TAIOWC_PLUGIN_URI')) {
    define( 'TAIOWC_PLUGIN_URI', plugin_dir_url( __FILE__ ) );
}

if (!defined('TAIOWC_PLUGIN_PATH')) {
    define( 'TAIOWC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if (!defined('TAIOWC_PLUGIN_DIRNAME')) {
    define( 'TAIOWC_PLUGIN_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );
}

if (!defined('TAIOWC_PLUGIN_BASENAME')) {
    define( 'TAIOWC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

if (!defined('TAIOWC_IMAGES_URI')) {
define( 'TAIOWC_IMAGES_URI', trailingslashit( plugin_dir_url( __FILE__ ) . 'images' ) );
}

if (!defined('TAIOWC_VERSION')){
$plugin_data = get_file_data(__FILE__, array('version' => 'Version'), false);
define('TAIOWC_VERSION', $plugin_data['version']);
} 

if (!class_exists('Taiowc') && !class_exists('Taiowc_Pro')){
include_once(TAIOWC_PLUGIN_PATH . 'inc/themehunk-menu/admin-menu.php');
require_once("inc/taiowc.php");
require_once("notice/th-notice.php");
}              

/**
* Add the settings link to plugin row
*
* @param array $links - Links for the plugin
* @return array - Links
 */

function taiowc_plugin_action_links($links){
          $settings_page = add_query_arg(array('page' => 'taiowc'), admin_url());
          $settings_link = '<a href="'.esc_url($settings_page).'">'.__('Settings', 'taiowc' ).'</a>';
          array_unshift($links, $settings_link);
          return $links;
        }
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'taiowc_plugin_action_links', 10, 1);

/**
   * Add links to plugin's description in plugins table
   *
   * @param array  $links  Initial list of links.
   * @param string $file   Basename of current plugin.
   *
   * @return array
   */
if ( ! function_exists( 'taiowc_plugin_meta_links' ) ){

  function taiowc_plugin_meta_links($links, $file){

    if ($file !== plugin_basename(__FILE__)) {
      return $links;
    }

    $demo_link = '<a target="_blank" href="#" title="' . __('Live Demo', 'taiowc') . '"><span class="dashicons  dashicons-laptop"></span>' . __('Live Demo', 'taiowc') . '</a>';

    $doc_link = '<a target="_blank" href="'.esc_url('https://themehunk.com/docs/th-all-in-one-woo-cart/').'" title="' . __('Documentation', 'taiowc') . '"><span class="dashicons  dashicons-search"></span>' . __('Documentation', 'taiowc') . '</a>';

    $support_link = '<a target="_blank" href="'.esc_url('https://themehunk.com/th-all-in-one-woo-cart/').'" title="' . __('Support', 'taiowc') . '"><span class="dashicons  dashicons-admin-users"></span>' . __('Support', 'taiowc') . '</a>';

    $pro_link = '<a target="_blank" href="'. esc_url('https://themehunk.com/th-all-in-one-woo-cart/').'" title="' . __('Premium Version', 'taiowc') . '"><span class="dashicons  dashicons-cart"></span>' . __('Premium Version', 'taiowc') . '</a>';

    $links[] = $demo_link;
    $links[] = $doc_link;
    $links[] = $support_link;
    $links[] = $pro_link;

    return $links;

  } // plugin_meta_links

}
add_filter('plugin_row_meta', 'taiowc_plugin_meta_links', 10, 2);