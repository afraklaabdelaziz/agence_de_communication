<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access.
}

if ( ! class_exists( 'ThemeHunk_MegaMenu_Menu_Settings' ) ) :


/**
 * Handles all admin related functionality.
 */
class ThemeHunk_MegaMenu_Menu_Settings {


    /**
     * All themes (default and custom)
     */
    var $themes = array();


    /**
     * Active theme
     */
    var $active_theme = array();


    /**
     * Active theme ID
     */
    var $id = "";
    /**
     * Constructor
     *
     * @since 1.0
     */
    public function __construct(){
             add_action( 'wp_ajax_themehunk_megamenu_save_setting', array( $this, 'ajax_save_theme' ) );
             add_action( 'admin_post_themehunk_megamenu_save_setting', array( $this, 'save_theme') );
             add_action( 'admin_post_themehunk_megamenu_reset_theme', array( $this, 'reset_theme') );
             add_action( 'themehunk_megamenu_page_general_settings', array( $this, 'themehunk_megamenu_theme_editor_page'));
             add_action( 'admin_menu', array( $this, 'themehunk_megamenu_themes_page') );
             add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );   
                   
    }

     /**
     * Adds the "Menu Themes" menu item and page.
     *
     * @since 1.0
     */
    public function themehunk_megamenu_themes_page(){

        $svg = '';

        $icon = 'dashicons-tagcloud';

        $page = add_menu_page( __('TH MegaMenu', 'themehunk-megamenu'), __('TH MegaMenu', 'themehunk-megamenu'), 'edit_theme_options', 'themehunkmegamenu', array($this, 'page'), $icon );


       
    }

     /**
     * Enqueue nav-menus.php scripts
     *
     * @since 1.8.3
     */
    public function enqueue_scripts(){

              wp_enqueue_style( 'spectrum', THEMEHUNK_MEGAMENU_URL . 'lib/spectrum/spectrum.css', false, THEMEHUNK_MEGAMENU_VERSION );
              wp_enqueue_style( 'codemirror', THEMEHUNK_MEGAMENU_URL . 'lib/codemirror/codemirror.css', false, THEMEHUNK_MEGAMENU_VERSION);
              wp_enqueue_style( 'pickr-classic', THEMEHUNK_MEGAMENU_URL . 'lib/pickr/css/classic.min.css', false, THEMEHUNK_MEGAMENU_VERSION);
              wp_enqueue_style( 'pickr-monolith', THEMEHUNK_MEGAMENU_URL . 'lib/pickr/css/monolith.min.css', false, THEMEHUNK_MEGAMENU_VERSION);
              wp_enqueue_style( 'pickr-nano', THEMEHUNK_MEGAMENU_URL . 'lib/pickr/css/nano.min.css', false, THEMEHUNK_MEGAMENU_VERSION);
              wp_enqueue_style( 'select-css', THEMEHUNK_MEGAMENU_URL . 'lib/select/select.css', false, THEMEHUNK_MEGAMENU_VERSION);
              wp_enqueue_script( 'select-js', THEMEHUNK_MEGAMENU_URL . 'lib/select/select.min.js', array( 'jquery' ), THEMEHUNK_MEGAMENU_VERSION );
              wp_enqueue_script( 'pickr-js', THEMEHUNK_MEGAMENU_URL . 'lib/pickr/js/pickr.min.js', array( 'jquery' ), THEMEHUNK_MEGAMENU_VERSION,true);
              wp_enqueue_script( 'mega-menu-theme-editor', THEMEHUNK_MEGAMENU_URL . 'assets/js/settings.js', array( 'jquery' ), THEMEHUNK_MEGAMENU_VERSION,true);
              wp_enqueue_script( 'spectrum', THEMEHUNK_MEGAMENU_URL . 'lib/spectrum/spectrum.js', array( 'jquery' ), THEMEHUNK_MEGAMENU_VERSION );
              wp_enqueue_script( 'codemirror', THEMEHUNK_MEGAMENU_URL . 'lib/codemirror/codemirror.js', array( 'jquery' ), THEMEHUNK_MEGAMENU_VERSION  );
              wp_localize_script( 'spectrum', 'megamenu_spectrum_settings',
                       apply_filters("megamenu_spectrum_localisation", array())
               );
               if ( function_exists('wp_enqueue_code_editor') ) {
                    wp_deregister_style('codemirror');
                    wp_deregister_script('codemirror');

                    $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/x-scss'));
                    wp_localize_script('mega-menu-theme-editor', 'cm_settings', $cm_settings);
                    wp_enqueue_style('wp-codemirror');
               }
              wp_localize_script( 'mega-menu-theme-editor', 'themehunk_megamenu_options',
              array(
                'confirm' => __("Are you sure?", "themehunk-megamenu"),
                "theme_save_error" => __("Error saving theme.", "themehunk-megamenu"),
                "theme_save_error_refresh" => __("Please try refreshing the page.", "themehunk-megamenu"),
                "theme_save_error_exhausted" => __("The server ran out of memory whilst trying to regenerate the menu CSS.", "themehunk-megamenu"),
                "theme_save_error_memory_limit" => __("Try disabling unusued plugins to increase the available memory. Alternatively, for details on how to increase your server memory limit see:", "themehunk-megamenu"),
                "theme_save_error_500" => __("The server returned a 500 error. The server did not provide an error message (you should find details of the error in your server error log), but this is usually due to your server memory limit being reached.", "themehunk-megamenu"),
                "increase_memory_limit_url" => "http://www.wpbeginner.com/wp-tutorials/fix-wordpress-memory-exhausted-error-increase-php-memory/",
                "increase_memory_limit_anchor_text" => "How to increase the WordPress memory limit"
            )
        );


    }
    /**
     *
     * @since 1.4
     */
    public function init() {

        if ( class_exists( "ThemeHunk_MegaMenu_Menu_Style_Manager" ) ) {

            $style_manager = new ThemeHunk_MegaMenu_Menu_Style_Manager();
            $this->themes = $style_manager->get_themes();

            $last_updated = themehunk_megamenu_menu_get_last_updated_theme();

            $preselected_theme = isset( $this->themes[ $last_updated ] ) ? $last_updated : 'default';

            $theme_id = isset( $_GET['theme'] ) ? sanitize_text_field( $_GET['theme'] ) : $preselected_theme;

            if ( isset( $this->themes[ $theme_id ] ) ) {
                $this->id = $theme_id;
            } else {
                $this->id = $preselected_theme;
            }

            $this->active_theme = $this->themes[$this->id];

        }

    }
   
    /**
     * Save changes to an exiting theme.
     *
     * @since 1.0
     */
    public function save_theme($is_ajax = false){

        check_admin_referer('themehunk_megamenu_save_setting' );

        $theme = esc_attr($_POST['theme_id']);

        $saved_themes = themehunk_megamenu_menu_get_themes();

        if ( isset( $saved_themes[ $theme ] ) ){
            unset( $saved_themes[ $theme ] );
        }

        $prepared_theme = $this->themehunk_megamenu_get_prepared_theme_for_saving();

        $saved_themes[ $theme ] = $prepared_theme;

        themehunk_megamenu_menu_save_themes( $saved_themes );
        themehunk_megamenu_menu_save_last_updated_theme( $theme );

        do_action("themehunk_megamenu_after_theme_save");
        do_action("themehunk_megamenu_delete_cache");

        if ( ! $is_ajax ){
            $this->redirect( esc_url_raw(admin_url("admin.php?page=themehunkmegamenu&theme={$theme}&saved=true" )));
            return;
        }

        return $prepared_theme;

    }
    /**
     *
     * @since 2.4.1
     */
    public function ajax_save_theme(){

        check_ajax_referer( 'themehunk_megamenu_save_setting' );

        $style_manager = new ThemeHunk_MegaMenu_Menu_Style_Manager();

        $test = $style_manager;

        if ( is_wp_error( $test ) ) {
            wp_send_json_error( $test->get_error_message() );
        } else {
            $this->save_theme(true);
            wp_send_json_success( "Saved" );
        }

        wp_die();

    }
    /**
     * Sanitize multidimensional array
     *
     * @since 2.7.5
     */
    public function themehunk_megamenu_sanitize_array( &$array ) {
        foreach ( $array as &$value) {   
            if ( ! is_array( $value ) ) {
                $value = sanitize_textarea_field( $value );
            } else {
                $this->themehunk_megamenu_sanitize_array( $value );
            }
        }
        return $array;
    }
    /**
     *
     * @since 2.4.1
     */
    public function themehunk_megamenu_get_prepared_theme_for_saving(){
     if ( isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ) {
        $submitted_settings = $this->themehunk_megamenu_sanitize_array( $_POST['settings'] );
        if ( isset( $_POST['checkboxes'] ) ) {
            foreach ( $_POST['checkboxes'] as $checkbox => $value ) {
                if ( isset( $submitted_settings[ $checkbox ] ) ) {
                    $submitted_settings[ $checkbox ] = 'on';
                } else {
                    $submitted_settings[ $checkbox ] = 'off';
                }
            }
        }

        if ( is_numeric( $submitted_settings['responsive_breakpoint'] ) ) {
            $submitted_settings['responsive_breakpoint'] = $submitted_settings['responsive_breakpoint'] ;
        }

        if ( isset( $submitted_settings['toggle_blocks'] ) ) {
            unset( $submitted_settings['toggle_blocks'] );
        }
       }
        $theme = array_map( 'esc_attr', $submitted_settings );

        return $theme;

    }

     /**
     * Revert a theme (only available for default themes, you can't revert a custom theme)
     *
     * @since 1.0
     */
    public function reset_theme() {

        check_admin_referer( 'themehunk_megamenu_reset_theme' );


        $theme =  esc_attr($_GET['theme_id']);

        $saved_themes = themehunk_megamenu_menu_get_themes();

        if ( isset( $saved_themes[$theme] ) ){
            unset( $saved_themes[$theme] );
        }

        themehunk_megamenu_menu_save_themes( $saved_themes );

        $this->redirect( esc_url_raw(admin_url( "admin.php?page=themehunkmegamenu&theme={$theme}&reset=true")) );

    }
     /**
     * Redirect and exit
     *
     * @since 1.8
     */
    public function redirect( $url ) {

        wp_redirect( $url );
        exit;

    }

    /**
     * Main settings page wrapper.
     *
     * @since 1.4
     */
    public function page() {

        $tab = isset( $_GET['page'] ) ? sanitize_text_field(substr( $_GET['page'], 12 )) : false;

        // backwards compatibility
        if ( isset ( $_GET['tab'] ) ) {
            $tab = sanitize_text_field($_GET['tab']);
        }

        if ( ! $tab ) {
            $tab = 'general_settings';
        }

        $versions = apply_filters( "themehunk_megamenu_versions", array(
            'core' => array(
                'version' => THEMEHUNK_MEGAMENU_VERSION,
                'text' => __("Core version", "themehunk-megamenu")
            ),
            
        ) );

        ?>

        <div class='megamenu_outer_wrap'>
             <div class='megamenu_header'>
                <div class='megamenu_header_left'>
                    <h2><?php _e("ThemeHunk MegaMenu", "themehunk-megamenu"); ?></h2>
                    <div class='version'>
                        <?php

                            $total = count( $versions );
                            $count = 0;
                            $separator = ' - ';

                            foreach ( $versions as $id => $data ) {
                                echo $data['text'] . ": <b>" . $data['version'] . "</b>";

                                $count = $count + 1;

                                if ( $total > 0 && $count != $total ) {
                                    echo $separator;
                                }
                            }
                        ?>
                    </div>
                </div>
            </div>
             <div class='megamenu_wrap'>
                <div class='megamenu_head'>
                    <?php
                    do_action( "themehunk_megamenu_page_general_settings" );
                    ?>
                </div>
            </div>

        </div>

        <?php
    }

    /**
     * Displays the theme editor form.
     *
     * @since 1.0
     */
    public function themehunk_megamenu_theme_editor_page( $saved_settings ){ 
         $this->init();
         $reset_url = esc_url( add_query_arg(
            array(
                'action'=>'themehunk_megamenu_reset_theme',
                'theme_id' => $this->id
            ),
            wp_nonce_url( admin_url("admin-post.php"), 'themehunk_megamenu_reset_theme' )
        ) );
    ?>
               <div class='themehunk_megamenu_menu_settings menu_settings_menu_themes'>
                 <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" class="theme_editor">
                    <input type="hidden" name="theme_id" value="<?php echo esc_attr( $this->id ); ?>" />
                    <input type="hidden" name="action" value="themehunk_megamenu_save_setting" />
                    <?php wp_nonce_field( 'themehunk_megamenu_save_setting' );
                                $settings = apply_filters( 'megamenu_theme_editor_settings', array(
                                               
                                               'menu_bar' => array(
                                                    'title' => __( "Menu Bar", "themehunk-megamenu" ),
                                                    'settings' => array(
                                                            'menu_item_height' => array(
                                                            'priority' => 05,
                                                            'title' => __( "Menu Height (px)", "themehunk-megamenu" ),
                                                            'description' =>'',
                                                            'settings' => array(
                                                                array(
                                                                    'title' => "Height",
                                                                    'type' => 'number',
                                                                    'key' => 'menu_item_link_height',
                                                                    'validation' => 'int'
                                                                )
                                                              )
                                                           ),

                                                            'menu_item_align' => array(
                                                            'priority' => 05,
                                                            'title' => __( "Menu Items Align", "themehunk-megamenu" ),
                                                            'description' => '',
                                                            
                                                            'settings' => array(
                                                                array(
                                                                    'title' => "",
                                                                    'type' => 'align',
                                                                    'key' => 'menu_item_align'
                                                                )
                                                             )
                                                           ),
                                                             'menu_background' => array(
                                                                'priority' => 10,
                                                                'title' => __( "Menu Background", "themehunk-megamenu" ),
                                                                'description' =>'',
                                                                'settings' => array(
                                                                    array(
                                                                        'title' => __( "Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'menu_bg_color'
                                                                    ),
                                                                   
                                                                )
                                                            ),
                                                              'menu_link_color' => array(
                                                                'priority' => 10,
                                                                'title' => __( "Menu Link Color", "themehunk-megamenu" ),
                                                                 'description' =>'',
                                                                'settings' => array(
                                                                    array(
                                                                        'title' => __( "Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'menu_link_color'
                                                                    ),
                                                                    array(
                                                                        'title' => __( "Hover Color / Current Item", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'menu_hvr_color'
                                                                    ),
                                                                   
                                                                )
                                                            ),
                                                              'menu_link_bg_color' => array(
                                                                'priority' => 10,
                                                                'title' => __( "Menu Link Bg Color", "themehunk-megamenu" ),
                                                                'description' =>'',
                                                                'settings' => array(
                                                                    array(
                                                                        'title' => __( "Background Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'menu_link_bg_color'
                                                                    ),
                                                                    array(
                                                                        'title' => __( "Background Hover Color / Background Current Item Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'menu_link_hvr_bg_color'
                                                                    ),
                                                                   
                                                                )
                                                            ),
                                                                 'menu-dropdown-arrow' => array(
                                                                    'priority' => 10,
                                                                    'title' => __( "Arrow", "themehunk-megamenu" ),
                                                                    'description' => __( "Select the arrow styles.", "themehunk-megamenu" ),
                                                                    'settings' => array(
                                                                        array(
                                                                            'title' => __( "Menu", "themehunk-megamenu" ),
                                                                            'type' => 'arrow',
                                                                            'key' => 'menu-dropdown-arrow'
                                                                        ),
                                                                        array(
                                                                            'title' => __( "Submenu ", "themehunk-megamenu" ),
                                                                            'type' => 'arrow',
                                                                            'key' => 'sub-menu-dropdown-arrow'
                                                                        ),
                                     
                                                                    )
                                                                ),

                                                                'sub_menu_items' => array(
                                                                'priority' => 11,
                                                                'title' => __( "Sub Menu Item", "themehunk-megamenu" ),
                                                                'description' => '',
                                                            ),

                                                             'sub_menu_background' => array(
                                                                'priority' => 12,
                                                                'title' => __( "Menu Background", "themehunk-megamenu" ),
                                                                'description' =>'',
                                                                'settings' => array(
                                                                    array(
                                                                        'title' => __( "Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'sub_menu_bg_color'
                                                                    ),
                                                                   
                                                                )
                                                            ),
                                                             'sub_menu_link_bg_color' => array(
                                                                'priority' => 12,
                                                                'title' => __( "Menu Link Background Color", "themehunk-megamenu" ),
                                                                'description' =>'',
                                                                'settings' => array(
                                                                    
                                                                    array(
                                                                        'title' => __( "Background Hover Color / Background Current Item Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'sub_menu_hvr_bg_color'
                                                                    ),
                                                                   
                                                                )
                                                            ),
                                                              'sub_menu_link_color' => array(
                                                                'priority' => 12,
                                                                'title' => __( "Menu Link Color", "themehunk-megamenu" ),
                                                                 'description' =>'',
                                                                'settings' => array(
                                                                    array(
                                                                        'title' => __( "Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'sub_menu_link_color'
                                                                    ),
                                                                    array(
                                                                        'title' => __( "Hover Color / Current Item", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'sub_menu_hvr_color'
                                                                    ),
                                                                   
                                                                )
                                                            ),
                                                            'mobile_menu_padding' => array(
                                                            'priority' => 13,
                                                            'title' => __( "Menu Padding (px)", "themehunk-megamenu" ),
                                                            'description' =>'',
                                                            'settings' => array(
                                                                array(
                                                                    'title' => __( "Top", "themehunk-megamenu" ),
                                                                    'type' => 'number',
                                                                    'key' => 'menu_padding_top',
                                                                    'validation'=>'int'
                                                                    
                                                                ),
                                                                array(
                                                                    'title' => __( "Right", "themehunk-megamenu" ),
                                                                    'type' => 'number',
                                                                    'key' => 'menu_padding_right',
                                                                    'validation'=>'int'
                                                                  
                                                                ),
                                                                array(
                                                                    'title' => __( "Bottom", "themehunk-megamenu" ),
                                                                    'type' => 'number',
                                                                    'key' => 'menu_padding_bottom',
                                                                    'validation'=>'int'
                                                                   
                                                                ),
                                                                array(
                                                                    'title' => __( "Left", "themehunk-megamenu" ),
                                                                    'type' => 'number',
                                                                    'key' => 'menu_padding_left',
                                                                    'validation'=>'int'
                                                                 
                                                                )
                                                            )
                                                            ),
                                                            'menu_margin' => array(
                                                            'priority' => 13,
                                                            'title' => __( "Menu Margin (px)", "themehunk-megamenu" ),
                                                            'description' =>'',
                                                            'settings' => array(
                                                                array(
                                                                    'title' => __( "Left", "themehunk-megamenu" ),
                                                                    'type' => 'number',
                                                                    'key' => 'menu_margin_left',
                                                                    'validation'=>'int'
                                                                    
                                                                ),
                                                                array(
                                                                    'title' => __( "Right", "themehunk-megamenu" ),
                                                                    'type' => 'number',
                                                                    'key' => 'menu_margin_right',
                                                                    'validation'=>'int'
                                                                  
                                                                ),
                                                                
                                                            )
                                                            ),

                                                            'menu_border_radius' => array(
                                                            'priority' => 14,
                                                            'title' => __( "Menu Border Radius (px)", "themehunk-megamenu" ),
                                                            'description' => __( "Set a border radius on the main menu bar.", "themehunk-megamenu" ),
                                                            'settings' => array(
                                                                array(
                                                                    'title' => __( "Top Left", "themehunk-megamenuu" ),
                                                                    'type' => 'number',
                                                                    'key' => 'menu_border_radius_top_left',
                                                                    'validation'=>'int'
                                                                   
                                                                ),
                                                                array(
                                                                    'title' => __( "Top Right", "themehunk-megamenu" ),
                                                                    'type' => 'number',
                                                                    'key' => 'menu_border_radius_top_right',
                                                                    'validation'=>'int'
                                                                  
                                                                ),
                                                                array(
                                                                    'title' => __( "Bottom Right", "themehunk-megamenu" ),
                                                                    'type' => 'number',
                                                                    'key' => 'menu_border_radius_bottom_right',
                                                                    'validation'=>'int'
                                                                   
                                                                ),
                                                                array(
                                                                    'title' => __( "Bottom Left", "themehunk-megamenu" ),
                                                                    'type' => 'number',
                                                                    'key' => 'menu_border_radius_bottom_left',
                                                                    'validation'=>'int'
                                                            
                                                                )
                                                            )
                                                        ),


                                                    ),
                                               ),
                                               'mobile_menu' => array(
                                                    'title' => __( "Mobile Menu", "themehunk-megamenu" ),
                                                    'settings' => array(
                                                            'responsive_breakpoint' => array(
                                                            'priority' => 10,
                                                            'title' => __( "Responsive Breakpoint", "themehunk-megamenu" ),
                                                            'description' => '',
                                                            'settings' => array(
                                                                array(
                                                                    'title' => "",
                                                                    'type' => 'number',
                                                                    'key' => 'responsive_breakpoint',
                                                                    'validation' => 'int'
                                                                )
                                                            ),
                                                        ),
                                                            'mobile_menu_item_align' => array(
                                                            'priority' => 10,
                                                            'title' => __( "Panel Open", "themehunk-megamenu" ),
                                                            'description' => '',
                                                            
                                                            'settings' => array(
                                                                array(
                                                                    'title' => "",
                                                                    'type' => 'align',
                                                                    'key' => 'mobile_menu_item_align'
                                                                )
                                                             )
                                                           ),
                                                            'toggle_text' => array(
                                                            'priority' => 10,
                                                            'title' => __( "Toggle Text", "themehunk-megamenu" ),
                                                            'description' => '',
                                                            'settings' => array(
                                                                array(
                                                                    'title' => __( "Text", "themehunk-megamenu" ),
                                                                    'type' => 'freetext',
                                                                    'key' => 'toggle_text',
                                                                    
                                                                ),
                                                                array(
                                                                    'title' => __( "Color", "themehunk-megamenu" ),
                                                                    'type' => 'color',
                                                                    'key' => 'toggle_text_clr',
                                                                    
                                                                )
                                                            ),
                                                        ),
                                                            'toggle_bg' => array(
                                                            'priority' => 10,
                                                            'title' => __( "Toggle Background", "themehunk-megamenu" ),
                                                            'description' => '',
                                                            'settings' => array(
                                                                array(
                                                                        'title' => __( "Background Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'toggle_bg_color'
                                                                    ),
                                                            ),
                                                        ),
                                                            'toggle_icon_clr' => array(
                                                            'priority' => 10,
                                                            'title' => __( "Toggle", "themehunk-megamenu" ),
                                                            'description' => '',
                                                            'settings' => array(
                                                                array(
                                                                        'title' => __( "Icon Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'toggle_icon_clr'
                                                                    ),
                                                                array(
                                                                        'title' => __( "Open Icon", "themehunk-megamenu" ),
                                                                        'type' => 'toggleicon',
                                                                        'key' => 'mobile-open-toggle-icon'
                                                                    ),
                                                                array(
                                                                        'title' => __( "Close Icon","themehunk-megamenu" ),
                                                                        'type' => 'toggleicon',
                                                                        'key' => 'mobile-close-toggle-icon'
                                                                    ),
                                                            ),
                                                        ),

                                                    'toggle_bar_height' => array(
                                                    'priority' => 10,
                                                    'title' => __( "Toggle Bar Height", "themehunk-megamenu" ),
                                                    'description' => '',
                                                    'settings' => array(
                                                        array(
                                                            'title' => "Height (px)",
                                                            'type' => 'number',
                                                            'key' => 'toggle_bar_height',
                                                            'validation'=>'int'
                                                        ),

                                                    )
                                                ),
                                                    'mobile-menu-dropdown-arrow' => array(
                                                                    'priority' => 10,
                                                                    'title' => __( "Arrow", "themehunk-megamenu" ),
                                                                    'description' => __( "Select the arrow styles.", "themehunk-megamenu" ),
                                                                    'settings' => array(
                                                                        array(
                                                                            'title' => __( "Down", "themehunk-megamenu" ),
                                                                            'type' => 'arrow',
                                                                            'key' => 'mobile-menu-dropdown-arrow'
                                                                        ),
                                                                        array(
                                                                            'title' => __( "Up", "themehunk-megamenu" ),
                                                                            'type' => 'arrow',
                                                                            'key' => 'mobile-sub-menu-dropdown-arrow'
                                                                        ),
                                     
                                                                    )
                                                                ),
                                                     'mobile_menu_bg' => array(
                                                            'priority' => 10,
                                                            'title' => __( "Mobile Menu Background", "themehunk-megamenu" ),
                                                            'description' => '',
                                                            'settings' => array(
                                                                array(
                                                                        'title' => __( "Background Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'mobile_menu_bg_color'
                                                                    ),
                                                            ),
                                                        ),
                                                       'mobile_menu_link_bg_color' => array(
                                                                'priority' => 10,
                                                                'title' => __( "Menu Link Background Color", "themehunk-megamenu" ),
                                                                'description' =>'',
                                                                'settings' => array(
                                                                    array(
                                                                        'title' => __( "Background Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'mobile_menu_link_bg_color'
                                                                    ),
                                                                    array(
                                                                        'title' => __( "Background hover color / Background Current Item Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'mobile_menu_hvr_bg_color'
                                                                    ),
                                                                   
                                                                )
                                                            ),
                                                       'mobile_menu_link_color' => array(
                                                                'priority' => 10,
                                                                'title' => __( "Menu Link Color", "themehunk-megamenu" ),
                                                                'description' =>'',
                                                                'settings' => array(
                                                                    array(
                                                                        'title' => __( "Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'mobile_menu_link_color'
                                                                    ),
                                                                    array(
                                                                        'title' => __( "Hover Color / Current Item Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'mobile_menu_hvr_link_color'
                                                                    ),
                                                                   
                                                                )
                                                            ),
                                                          'mobile_sub_menu_items' => array(
                                                                'priority' => 11,
                                                                'title' => __( "Sub Menu Item", "themehunk-megamenu" ),
                                                                'description' => '',
                                                            ),
                                                            'mobile_sub_menu_hide' => array(
                                                                'priority' => 12,
                                                                'title' => __( "Sub Menu Hide", "themehunk-megamenu" ),
                                                                'description' =>'',
                                                                'settings' => array(
                                                                    array(
                                                                        'title' => __( "Hide Sub Menu On Mobile", "themehunk-megamenu" ),
                                                                        'type' => 'checkbox',
                                                                        'key' => 'mobile_sub_menu_hide'
                                                                    ),
                                                                   
                                                                )
                                                            ),
                                                           'mobile_sub_menu_bg_link_color' => array(
                                                                'priority' => 12,
                                                                'title' => __( "Menu Link Background Color", "themehunk-megamenu" ),
                                                                'description' =>'',
                                                                'settings' => array(
                                                                    array(
                                                                        'title' => __( "Background Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'mobile_sub_menu_bg_link_color'
                                                                    ),
                                                                    array(
                                                                        'title' => __( "Background Hover Color / Current Item Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'mobile_sub_menu_bg_link_hvr_color'
                                                                    ),
                                                                   
                                                                )
                                                            ),
                                                           'mobile_sub_menu_link_color' => array(
                                                                'priority' => 12,
                                                                'title' => __( "Menu Link Color", "themehunk-megamenu" ),
                                                                'description' =>'',
                                                                'settings' => array(
                                                                    array(
                                                                        'title' => __( "Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'mobile_sub_menu_link_color'
                                                                    ),
                                                                    array(
                                                                        'title' => __( "Hover Color / Current Item Color", "themehunk-megamenu" ),
                                                                        'type' => 'color',
                                                                        'key' => 'mobile_sub_menu_link_hvr_color'
                                                                    ),
                                                                   
                                                                )
                                                            ),

        
                                            ),
                                     ),
                        'custom_styling' => array(
                            'title' => __( "Custom Styling", "themehunk-megamenu"),
                            'settings' => array(
                                'custom_styling' => array(
                                    'priority' => 40,
                                    'title' => __( "CSS Editor", "themehunk-megamenu" ),
                                    'description' => __( "Define any custom CSS you wish to add to menus using this theme. You can use standard CSS", "themehunk-megamenu"),
                                    'settings' => array(
                                        array(
                                            'title' =>  __( "CSS Editor", "themehunk-megamenu" ),
                                            'description' => __( "Define any custom CSS you wish to add to menus using this theme. You can use standard CSS", "themehunk-megamenu"),
                                            'type' => 'textarea',
                                            'key' => 'custom_css'
                                        )
                                    )
                                )
                            )
                        )


           ));
                            echo "<h2 class='nav-tab-wrapper'>";
                                  
                            $is_first = true;

                            foreach ( $settings as $section_id => $section ) {

                                if ($is_first) {
                                    $active = 'nav-tab-active ';
                                    $is_first = false;
                                } else {
                                    $active = '';
                                }

                                echo "<a class='mega-tab nav-tab {$active}' data-tab='mega-tab-content-{$section_id}'>".$section['title'] . "</a>";

                            }
                            echo "</h2>";
                            foreach ( $settings as $section_id => $section ) {
                       $is_first = true;
                       if ($is_first) {
                            $display = 'block';
                            $is_first = false;
                        } else {
                            $display = 'none';
                        }

                        echo " <div class='mega-tab-content mega-tab-content-{$section_id}' style='display: {$display}'>";
                        echo " <table class='{$section_id}'>";

                        // order the fields by priority
                        uasort( $section['settings'], array( $this, "themehunk_megamenu_compare_elems" ) );

                        foreach ( $section['settings'] as $group_id => $group ) {

                            echo "<tr class='mega-{$group_id}'>";

                            if ( isset( $group['settings'] ) ) {

                                echo "<td class='mega-name'>" . $group['title'] . "<div class='mega-description'>" . $group['description'] . "</div></td>";
                                echo "<td class='mega-value'>";

                                foreach ( $group['settings'] as $setting_id => $setting ) {

                                    if ( isset( $setting['validation'] ) ) {
                                        echo "<label class='mega-{$setting['key']}' data-validation='{$setting['validation']}'>";
                                    } else {
                                        echo "<label class='mega-{$setting['key']}'>";
                                    }
                                    echo "<span class='mega-short-desc'>{$setting['title']}</span>";

                                    switch ( $setting['type'] ) {
                                        case "freetext":
                                            $this->themehunk_megamenu_print_theme_freetext_option( $setting['key'] );
                                            break;
                                        case "number":
                                            $this->themehunk_megamenu_print_theme_number_option( $setting['key'] );
                                            break;    
                                        case "textarea":
                                            $this->themehunk_megamenu_print_theme_textarea_option( $setting['key'] );
                                            break;
                                        case "align":
                                            $this->themehunk_megamenu_print_theme_align_option( $setting['key'] );
                                            break;
                                        case "checkbox":
                                            $this->themehunk_megamenu_print_theme_checkbox_option( $setting['key'] );
                                            break;
                                        case "arrow":
                                            $this->themehunk_megamenu_print_theme_arrow_option( $setting['key'] );
                                            break;
                                        case "toggleicon":
                                            $this->themehunk_megamenu_print_theme_toggle_icon_option( $setting['key'] );
                                            break;
                                        case "color":
                                            $this->themehunk_megamenu_print_theme_color_option( $setting['key'] );
                                            break;
                                        case "weight":
                                            $this->print_theme_weight_option( $setting['key'] );
                                            break;
                                        case "font":
                                            $this->print_theme_font_option( $setting['key'] );
                                            break;
                                        case "transform":
                                            $this->print_theme_transform_option( $setting['key'] );
                                            break;
                                        case "decoration":
                                            $this->print_theme_text_decoration_option( $setting['key'] );
                                            break;
                                        case "mobile_columns":
                                            $this->print_theme_mobile_columns_option( $setting['key'] );
                                            break;
                                        case "copy_color":
                                            $this->print_theme_copy_color_option( $setting['key'] );
                                            break;
                                        default:
                                            do_action("megamenu_print_theme_option_{$setting['type']}", $setting['key'], $this->id );
                                            break;
                                    }

                                    echo "</label>";

                                }

                                if ( isset( $group['info'] ) ) {
                                    foreach ( $group['info'] as $paragraph ) {
                                        echo "<div class='mega-info'>{$paragraph}</div>";
                                    }
                                }

                                foreach ( $group['settings'] as $setting_id => $setting ) {
                                    if ( isset( $setting['validation'] ) ) {

                                        echo "<div class='mega-validation-message mega-validation-message-mega-{$setting['key']}'>";

                                        if ( $setting['validation'] == 'int' ) {
                                            $message = __("Enter a whole number (e.g. 1, 5, 100, 999)");
                                        }

                                        if ( $setting['validation'] == 'px' ) {
                                            $message = __("Enter a value including a unit (e.g. 10px, 10rem, 10%)");
                                        }
                                        if ( $setting['validation'] == 'numb' ) {
                                            $message = __("Enter a value including a Integer or Float type");
                                        }

                                        if ( $setting['validation'] == 'float' ) {
                                            $message = __("Enter a valid number (e.g. 0.1, 1, 10, 999)");
                                        }

                                        if ( strlen( $setting['title'] ) ) {
                                            echo $setting['title'] . ": " . $message;
                                        } else {
                                            echo $message;
                                        }

                                        echo "</div>";
                                    }
                                }

                                echo "</td>";
                            } else {
                                echo "<td colspan='2'><h5>{$group['title']}</h5></td>";
                            }

                            echo "</tr>";

                        }

                        echo "</table>";
                        echo "</div>";
                    }

                    ?>
                 <div class='megamenu_submit'>
                    <div class='mega_left'>
                        <?php submit_button(); ?><span class='spinner'></span>
                    </div>
                    <div class='mega_right'>
                            <a class='reset confirm' href='<?php echo $reset_url; ?>'><?php _e("Reset Option", "themehunk-megamenu"); ?></a>
                    </div>
                </div>
                   <?php $this->themehunk_megamenu_show_cache_warning(); ?>
                </form>
            </div>

  <?php   }
 /**
     * Check for installed caching/minification/CDN plugins and output a warning if one is found to be
     * installed and activated
     */ 
    private function themehunk_megamenu_show_cache_warning() {

        $active_plugins = themehunk_megamenu_get_active_caching_plugins();

        if ( count( $active_plugins ) ):

        ?>

        <hr />

        <div>

            <h3><?php _e("Changes not showing up?", "themehunk-megamenu"); ?></h3>

            <p><?php echo _n("We have detected the following plugin that may prevent changes made within the theme editor from being applied to the menu.", "We have detected the following plugins that may prevent changes made within the theme editor from being applied to the menu.", count( $active_plugins), "themehunk-megamenu"); ?></p>

            <ul class='ul-disc'>
                <?php
                    foreach ( $active_plugins as $name ) {
                        echo "<li>" . $name . "</li>";
                    }
                ?>
            </ul>

            <p><?php echo _n("Try clearing the cache of the above plugin if your changes are not being applied to the menu.", "Try clearing the caches of the above plugins if your changes are not being applied to the menu.", count( $active_plugins), "themehunk-megamenu"); ?></p>

        </div>

        <?php

        endif;
    }

    /**
     * Checks to see if a given string contains any of the provided search terms
     *
     * @param srgin $key
     * @param array $needles
     * @since 1.0
     */
    private function themehunk_megamenu_string_contains( $key, $needles ) {

        foreach ( $needles as $needle ) {

            if ( strpos( $key, $needle ) !== FALSE ) {
                return true;
            }
        }

        return false;

    }
     /**
     * Print a text input
     *
     * @since 1.0
     * @param string $key
     * @param string $value
     */
    public function themehunk_megamenu_print_theme_freetext_option( $key ) {

        $value = $this->active_theme[$key];

        echo "<input class='mega-setting-{$key}' type='text' name='settings[$key]' value='{$value}' />";

    }
    /**
     * Print a number input
     *
     * @since 1.0
     * @param string $key
     * @param string $value
     */
    public function themehunk_megamenu_print_theme_number_option( $key ) {

        $value = $this->active_theme[$key];

        echo "<input class='mega-setting-{$key}' type='number' name='settings[$key]' value='{$value}' />";

    }
    /**
     * Print a colorpicker
     *
     * @since 1.0
     * @param string $key
     * @param string $value
     */
    public function themehunk_megamenu_print_theme_color_option( $key ) {

        $value = $this->active_theme[$key];

        if ( $value == 'transparent' ) {
            $value = 'rgba(0,0,0,0)';
        }

        if ( $value == 'rgba(0,0,0,0)' ) {
            $value_text = 'transparent';
        } else {
            $value_text = $value;
        }

        echo "<input type='text' class='color_picker' name='settings[$key]' value='{$value}' style='background:{$value}' />";

    }
    /**
     * Print a select dropdown with left, center and right options
     *
     * @since 1.6.1
     * @param string $key
     * @param string $value
     */
    public function themehunk_megamenu_print_theme_align_option( $key ) {

        $value = $this->active_theme[$key];

        ?>

            <select name='settings[<?php echo esc_attr($key); ?>]'>
                <option value='left' <?php selected( $value, 'left' ); ?>><?php _e("Left", "themehunk-megamenu") ?></option>
                <option value='center' <?php selected( $value, 'center' ); ?>><?php _e("Center", "themehunk-megamenu") ?></option>
                <option value='right' <?php selected( $value, 'right' ); ?>><?php _e("Right", "themehunk-megamenu") ?></option>
            </select>

        <?php
    }
    /**
     * Print a checkbox option
     *
     * @since 1.6.1
     * @param string $key
     * @param string $value
     */
    public function themehunk_megamenu_print_theme_checkbox_option( $key ) {

        $value = $this->active_theme[$key];

        ?>

            <input type='hidden' name='checkboxes[<?php echo $key ?>]' />
            <input type='checkbox' name='settings[<?php echo $key ?>]' <?php checked( $value, 'on' ); ?> />

        <?php
    }
    /**
     * Print a textarea
     *
     * @since 1.0
     * @param string $key
     * @param string $value
     */
    public function themehunk_megamenu_print_theme_textarea_option( $key ) {

        $value = $this->active_theme[$key];
        ?>
        <textarea id='codemirror' name='settings[<?php echo $key ?>]'><?php echo stripslashes( $value ) ?></textarea>
        <?php
    }
    /**
     * List of all available arrow DashIcon classes.
     *
     * @since 1.0
     * @return array - Sorted list of icon classes
     */
    private function themehunk_megamenu_arrow_icons() {

        $icons = array(
            'f142' => 'dashicons-arrow-up',
            'f140' => 'dashicons-arrow-down',
            'f141' => 'dashicons-arrow-left',
            'f139' => 'dashicons-arrow-right',
            'f342' => 'dashicons-arrow-up-alt',
            'f346' => 'dashicons-arrow-down-alt',
            'f340' => 'dashicons-arrow-left-alt',
            'f344' => 'dashicons-arrow-right-alt',
            'f343' => 'dashicons-arrow-up-alt2',
            'f347' => 'dashicons-arrow-down-alt2',
            'f341' => 'dashicons-arrow-left-alt2',
            'f345' => 'dashicons-arrow-right-alt2',
            'f132' => 'dashicons-plus',
            'f460' => 'dashicons-minus',


        );

        $icons = apply_filters( "themehunk_megamenu_menu_arrow_icons", $icons );

        return $icons;

    }
    /**
     * Print an arrow dropdown selection box
     *
     * @since 1.0
     * @param string $key
     * @param string $value
     */
    public function themehunk_megamenu_print_theme_arrow_option( $key ) {

        $value = $this->active_theme[$key];

        $themehunk_megamenu_arrow_icons = $this->themehunk_megamenu_arrow_icons();

        ?>
            <select class='icon_dropdown' name='settings[<?php echo $key ?>]'>
                <?php

                    echo "<option value='disabled'>" . __("Disabled", "themehunk-megamenu") . "</option>";
                    foreach ($themehunk_megamenu_arrow_icons as $code => $class) {
                        $name = str_replace('dashicons-', '', $class);
                        $name = ucwords(str_replace(array('-','arrow'), ' ', $name));
                        echo "<option data-class='{$class}' value='{$code}' " . selected( $value, $code, false ) . ">" . $name . "</option>";
                    }

                ?>
            </select>

        <?php
    }
 public function themehunk_megamenu_toggle_icons() {

        $icons = array(
            'f333' => 'dashicons-menu',
            'f228' => 'dashicons-menu-alt',
            'f329' => 'dashicons-menu-alt2',
            'f349' => 'dashicons-menu-alt3',
            'f214' => 'dashicons-editor-justify',
            'f158' => 'dashicons-no',
            'f335' => 'dashicons-no-alt',
            'f132' => 'dashicons-plus',
            'f502' => 'dashicons-plus-alt',
            'f460' => 'dashicons-minus',
            'f153' => 'dashicons-dismiss',
            'f142' => 'dashicons-arrow-up',
            'f140' => 'dashicons-arrow-down',
            'f342' => 'dashicons-arrow-up-alt',
            'f346' => 'dashicons-arrow-down-alt',
            'f343' => 'dashicons-arrow-up-alt2',
            'f347' => 'dashicons-arrow-down-alt2',
        );
        $icons = apply_filters( "themehunk_megamenu_menu_toggle_icons", $icons );
        return $icons;
    }
/**
     * Print an arrow dropdown selection box
     *
     * @since 1.0
     * @param string $key
     * @param string $value
     */
    public function themehunk_megamenu_print_theme_toggle_icon_option( $key ) {

        $value = $this->active_theme[$key];

        $themehunk_megamenu_toggle_icons = $this->themehunk_megamenu_toggle_icons();

        ?>
            <select class='icon_dropdown' name='settings[<?php echo esc_attr($key); ?>]'>
                <?php

                    echo "<option value='disabled'>" . __("Disabled", "themehunk-megamenu") . "</option>";
                    foreach ($themehunk_megamenu_toggle_icons as $code => $class) {
                        $name = str_replace('dashicons-', '', $class);
                        $name = ucwords(str_replace(array('-','icon'), ' ', $name));
                        echo "<option data-class='{$class}' value='{$code}' " . selected( $value, $code, false ) . ">" . $name . "</option>";
                    }

                ?>
            </select>

        <?php
    }

     /**
     * Compare array values
     *
     * @param array $elem1
     * @param array $elem2
     * @return bool
     * @since 2.1
     */
    private function themehunk_megamenu_compare_elems( $elem1, $elem2 ) {

        return $elem1['priority'] > $elem2['priority'];

    }

}
endif;
if ( is_admin() ){
 new ThemeHunk_MegaMenu_Menu_Settings();
}