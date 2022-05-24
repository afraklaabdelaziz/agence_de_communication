<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access.
}
class ThemeHunk_MegaMenu_Nav_Menu_Settings {
    
    public function __construct() {
        add_action( 'load-nav-menus.php', array( $this, 'themehunk_megamenu_add_metabox_to_nav_menu_settings' ) );
        add_action('wp_ajax_themehunk_megamenu_nav_menu_save', array($this, 'themehunk_megamenu_nav_menu_save'));
    }

    public function themehunk_megamenu_add_metabox_to_nav_menu_settings() {
        add_meta_box( 'themehunk-megamenu-nav-menu-metabox-set', __( 'ThemeHunk MegaMenu Setting', 'themehunk-megamenu'), array( $this, 'themehunk_megamenu_themes_meta_box' ), 'nav-menus', 'side', 'high' );
    }

    public function themehunk_megamenu_themes_meta_box(){ 
        include_once( THEMEHUNK_MEGAMENU_DIR . 'inc/megamenu-nav-menu-metadata.php' );
    }

    public function themehunk_megamenu_nav_menu_save(){
        check_ajax_referer( 'themehunk_megamenu_check_security', 'themehunk_megamenu_nonce' );

        $menu_id = (int) sanitize_text_field($_POST['menu_id']);
        $mmth_settings_array = json_decode( stripslashes( $_POST['mmth_settings'] ), true );
        $saved_settings = array();

            foreach ( $mmth_settings_array as $index => $value ) {
                $name = $value['name'];
    
            // find values between square brackets
                preg_match_all( "/\[(.*?)\]/", $name, $matches );

                if ( isset( $matches[1][0] ) && isset( $matches[1][1] ) ) {
                     $location = sanitize_key($matches[1][0]);
                     $setting = sanitize_key($matches[1][1]);

                     $saved_settings[$location][$setting] = sanitize_key($value['value']);
                     $saved_settings[$location]['menu_id'] = sanitize_key($menu_id);
                    
                }
            }

            if ( ! get_option( 'themehunk_megamenu_options' ) ) {

                update_option( 'themehunk_megamenu_options', $saved_settings );

            } else {

                
                $existing_settings = get_option( 'themehunk_megamenu_options' );

                $new_settings = array_merge( $existing_settings, $saved_settings );

                update_option( 'themehunk_megamenu_options', $new_settings );

            }
            $mmth_updated_option = get_option( 'themehunk_megamenu_options' );
            wp_send_json_success( array( 'msg' =>__( 'Settings saved.', 'themehunk-megamenu'), 
            'setting_data' => $mmth_settings_array,
            'mmth_updated_option' => $mmth_updated_option ) 
        );

    }


}
new ThemeHunk_MegaMenu_Nav_Menu_Settings();