<?php 
/**
 * Plugin Name: ThemeHunk MegaMenu Plus
 * Plugin URI:  https://themehunk.com
 * Description: Megamenu plugin from Themehunk.
 * Version:     1.0.7
 * Author:      Themehunk
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: themehunk-megamenu
 */
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
define( 'THEMEHUNK_MEGAMENU_VERSION',  '1.0.6' );
// Setting path variables
if( ! defined( 'THEMEHUNK_MEGAMENU_URL' ) ){
	define( 'THEMEHUNK_MEGAMENU_URL', plugin_dir_url( __FILE__ ) );
}
if( ! defined( 'THEMEHUNK_MEGAMENU_DIR' ) ){
	define( 'THEMEHUNK_MEGAMENU_DIR', plugin_dir_path(__FILE__) );
}
//Include files here
include_once( THEMEHUNK_MEGAMENU_DIR . 'inc/megamenu-setting.php' );
include_once( THEMEHUNK_MEGAMENU_DIR . 'inc/megamenu-default-option.php' );
include_once( THEMEHUNK_MEGAMENU_DIR . 'inc/megamenu-base.php' );
include_once( THEMEHUNK_MEGAMENU_DIR . 'inc/megamenu-class.php' );
include_once( THEMEHUNK_MEGAMENU_DIR . 'inc/toggle-themehunk-megamenu.php' );
include_once( THEMEHUNK_MEGAMENU_DIR . 'inc/megamenu-functions.php' );
include_once( THEMEHUNK_MEGAMENU_DIR . 'inc/megamenu-nav-menu-settings.php' );
include_once( THEMEHUNK_MEGAMENU_DIR . 'inc/megamenu-widgets.php' );
include_once( THEMEHUNK_MEGAMENU_DIR . 'inc/walker.php' );
include_once( THEMEHUNK_MEGAMENU_DIR . 'inc/megamenu-style.php' );

// show notify
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if((!is_plugin_active( 'themehunk-customizer/themehunk-customizer.php' )) && (!is_plugin_active( 'hunk-companion/hunk-companion.php' )) && (!is_plugin_active( 'lead-form-builder/lead-form-builder.php' )) && (!is_plugin_active( 'wp-popup-builder/wp-popup-builder.php' ))){
  include_once(plugin_dir_path(__FILE__) . 'notify/notify.php' );
}