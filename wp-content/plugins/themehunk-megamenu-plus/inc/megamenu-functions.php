<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access.
}
if ( ! function_exists('themehunk_megamenu_get_attached_location_with_menu')){
	function themehunk_megamenu_get_attached_location_with_menu( $menu_id = 0 ) {
		if ( ! $menu_id){
			return;
		}

		$locations = array();
		$nav_menu_locations = get_nav_menu_locations();
		$nav_menus = get_registered_nav_menus();

		foreach ($nav_menus  as $id => $name ) {
			if ( isset( $nav_menu_locations[ $id ] ) && $nav_menu_locations[$id] == $menu_id ){
				$locations[$id] = $name;
			}
		}
		return $locations;
	}
}

if ( ! function_exists('get_themehunk_megamenu_option')){
    function get_themehunk_megamenu_option( $id ) {
        $options = get_option( 'themehunk_megamenu_options' );
        if ( isset( $options[$id] ) ) {
            return $options[$id];
        }
        return false;
    }
}
 
if ( ! function_exists( 'themehunk_megamenu_is_enabled' ) ) {

    /**
     * Determines if Themehunk Mega Menu has been enabled for a given menu location.
     *
     * Usage:
     *
     * Mega Menu is enabled:
     * function_exists( 'mega_menu_themehunk_megamenu_is_enabled' )
     *
     * Mega Menu has been enabled for a theme location:
     * function_exists( 'mega_menu_themehunk_megamenu_is_enabled' ) && mega_menu_themehunk_megamenu_is_enabled( $location )
     *
     * @since 1.8
     * @param string $location - theme location identifier
     */
    function themehunk_megamenu_is_enabled( $location = false ) {

        if ( ! $location ) {
            return true; // the plugin is enabled
        }

        if ( ! has_nav_menu( $location ) ) {
            return false;
        }

        // if a location has been passed, check to see if MMTH has been enabled for the location
        $options = get_option( 'themehunk_megamenu_options' );

        return is_array( $options ) && isset( $options[ $location ]['is_enabled'] ) && $options[ $location ]['is_enabled'] == 1;
    }
}

if ( ! function_exists( 'themehunk_megamenu_themehunk_megamenu_get_theme_id_for_location' ) ) {

    /**
     * @since 2.1
     * @param string $location - theme location identifier
     */
    function themehunk_megamenu_themehunk_megamenu_get_theme_id_for_location( $location = false ) {

        if ( ! $location ) {
            return false;
        }

        if ( ! has_nav_menu( $location ) ) {
            return false;
        }

        // if a location has been passed, check to see if MMM has been enabled for the location
        $settings = get_option( 'themehunk_megamenu_options' );

        if ( is_array( $settings ) && isset( $settings[ $location ]['is_enabled'] ) && isset( $settings[ $location ]['menu_id'] ) ) {
            return $settings[ $location ]['menu_id'];
        }

        return false;
    }
}

/**
 * @param array $array
 * @return int|null|string
 */
if ( ! function_exists('themehunk_megamenu_get_array_first_key')){
    function themehunk_megamenu_get_array_first_key($array = array()){
        if (! empty($array)){
            foreach ($array as $key => $value){
                return $key;
            }
        }
        return null;
    }
}



add_shortcode( 'themehunk_megamenu_test_shortcode', 'themehunk_megamenu_test_shortcode_function' );

function themehunk_megamenu_test_shortcode_function(){ 
      // $options = get_option( 'themehunk_megamenu_options' );
    $updated_data = get_post_meta( 1807, 'themehunk_megamenu_layout', true );

    $themehunk_megamenu_nav_locations = get_nav_menu_locations();
 
    $available_menus = wp_get_nav_menus();

    return print_r($themehunk_megamenu_nav_locations);
    // return '<pre>'. print_r($available_menus) . '</pre>';
}


if ( ! function_exists('themehunk_megamenu_dashicons')) {
    function themehunk_megamenu_dashicons(){
        $icons = array(
            'dashicons-menu' => __('Menu', 'themehunk-megamenu'),
            'dashicons-dashboard' => __('Dashboard', 'themehunk-megamenu'),
            'dashicons-admin-site' => __('Admin Site', 'themehunk-megamenu'),
            'dashicons-admin-media' => __('Admin Media', 'themehunk-megamenu'),
            'dashicons-admin-page' => __('Admin Page', 'themehunk-megamenu'),
            'dashicons-admin-comments' => __('Admin Comments', 'themehunk-megamenu'),
            'dashicons-admin-appearance' => __('Admin Appearance', 'themehunk-megamenu'),
            'dashicons-admin-plugins' => __('Admin Plugins', 'themehunk-megamenu'),
            'dashicons-admin-users' => __('Admin Users', 'themehunk-megamenu'),
            'dashicons-admin-tools' => __('Admin Tools', 'themehunk-megamenu'),
            'dashicons-admin-settings' => __('Admin Settings', 'themehunk-megamenu'),
            'dashicons-admin-network' => __('Admin Network', 'themehunk-megamenu'),
            'dashicons-admin-generic' => __('Admin Generic', 'themehunk-megamenu'),
            'dashicons-admin-home' => __('Admin Home', 'themehunk-megamenu'),
            'dashicons-admin-collapse' => __('Admin Collapse', 'themehunk-megamenu'),
            'dashicons-admin-links' => __('Admin Links', 'themehunk-megamenu'),
            'dashicons-admin-post' => __('Admin Post', 'themehunk-megamenu'),
            'dashicons-format-standard' => __('Admin Plugins', 'themehunk-megamenu'),
            'dashicons-format-image' => __('Image Post Format', 'themehunk-megamenu'),
            'dashicons-format-gallery' => __('Gallery Post Format', 'themehunk-megamenu'),
            'dashicons-format-audio' => __('Audio Post Format', 'themehunk-megamenu'),
            'dashicons-format-video' => __('Video Post Format', 'themehunk-megamenu'),
            'dashicons-format-links' => __('Link Post Format', 'themehunk-megamenu'),
            'dashicons-format-chat' => __('Chat Post Format', 'themehunk-megamenu'),
            'dashicons-format-status' => __('Status Post Format', 'themehunk-megamenu'),
            'dashicons-format-aside' => __('Aside Post Format', 'themehunk-megamenu'),
            'dashicons-format-quote' => __('Quote Post Format', 'themehunk-megamenu'),
            'dashicons-welcome-write-blog' => __('Welcome Write Blog', 'themehunk-megamenu'),
            'dashicons-welcome-edit-page' => __('Welcome Edit Page', 'themehunk-megamenu'),
            'dashicons-welcome-add-page' => __('Welcome Add Page', 'themehunk-megamenu'),
            'dashicons-welcome-view-site' => __('Welcome View Site', 'themehunk-megamenu'),
            'dashicons-welcome-widgets-menus' => __('Welcome Widget Menus', 'themehunk-megamenu'),
            'dashicons-welcome-comments' => __('Welcome Comments', 'themehunk-megamenu'),
            'dashicons-welcome-learn-more' => __('Welcome Learn More', 'themehunk-megamenu'),
            'dashicons-image-crop' => __('Image Crop', 'themehunk-megamenu'),
            'dashicons-image-rotate-left' => __('Image Rotate Left', 'themehunk-megamenu'),
            'dashicons-image-rotate-right' => __('Image Rotate Right', 'themehunk-megamenu'),
            'dashicons-image-flip-vertical' => __('Image Flip Vertical', 'themehunk-megamenu'),
            'dashicons-image-flip-horizontal' => __('Image Flip Horizontal', 'themehunk-megamenu'),
            'dashicons-undo' => __('Undo', 'themehunk-megamenu'),
            'dashicons-redo' => __('Redo', 'themehunk-megamenu'),
            'dashicons-editor-bold' => __('Editor Bold', 'themehunk-megamenu'),
            'dashicons-editor-italic' => __('Editor Italic', 'themehunk-megamenu'),
            'dashicons-editor-ul' => __('Editor UL', 'themehunk-megamenu'),
            'dashicons-editor-ol' => __('Editor OL', 'themehunk-megamenu'),
            'dashicons-editor-quote' => __('Editor Quote', 'themehunk-megamenu'),
            'dashicons-editor-alignleft' => __('Editor Align Left', 'themehunk-megamenu'),
            'dashicons-editor-aligncenter' => __('Editor Align Center', 'themehunk-megamenu'),
            'dashicons-editor-alignright' => __('Editor Align Right', 'themehunk-megamenu'),
            'dashicons-editor-insertmore' => __('Editor Insert More', 'themehunk-megamenu'),
            'dashicons-editor-spellcheck' => __('Editor Spell Check', 'themehunk-megamenu'),
            'dashicons-editor-distractionfree' => __('Editor Distraction Free', 'themehunk-megamenu'),
            'dashicons-editor-expand' => __('Editor Expand', 'themehunk-megamenu'),
            'dashicons-editor-contract' => __('Editor Contract', 'themehunk-megamenu'),
            'dashicons-editor-kitchensink' => __('Editor Kitchen Sink', 'themehunk-megamenu'),
            'dashicons-editor-underline' => __('Editor Underline', 'themehunk-megamenu'),
            'dashicons-editor-justify' => __('Editor Justify', 'themehunk-megamenu'),
            'dashicons-editor-textcolor' => __('Editor Text Colour', 'themehunk-megamenu'),
            'dashicons-editor-paste-word' => __('Editor Paste Word', 'themehunk-megamenu'),
            'dashicons-editor-paste-text' => __('Editor Paste Text', 'themehunk-megamenu'),
            'dashicons-editor-removeformatting' => __('Editor Remove Formatting', 'themehunk-megamenu'),
            'dashicons-editor-video' => __('Editor Video', 'themehunk-megamenu'),
            'dashicons-editor-customchar' => __('Editor Custom Character', 'themehunk-megamenu'),
            'dashicons-editor-outdent' => __('Editor Outdent', 'themehunk-megamenu'),
            'dashicons-editor-indent' => __('Editor Indent', 'themehunk-megamenu'),
            'dashicons-editor-help' => __('Editor Help', 'themehunk-megamenu'),
            'dashicons-editor-strikethrough' => __('Editor Strikethrough', 'themehunk-megamenu'),
            'dashicons-editor-unlink' => __('Editor Unlink', 'themehunk-megamenu'),
            'dashicons-editor-rtl' => __('Editor RTL', 'themehunk-megamenu'),
            'dashicons-editor-break' => __('Editor Break', 'themehunk-megamenu'),
            'dashicons-editor-code' => __('Editor Code', 'themehunk-megamenu'),
            'dashicons-editor-paragraph' => __('Editor Paragraph', 'themehunk-megamenu'),
            'dashicons-align-left' => __('Align Left', 'themehunk-megamenu'),
            'dashicons-align-right' => __('Align Right', 'themehunk-megamenu'),
            'dashicons-align-center' => __('Align Center', 'themehunk-megamenu'),
            'dashicons-align-none' => __('Align None', 'themehunk-megamenu'),
            'dashicons-lock' => __('Lock', 'themehunk-megamenu'),
            'dashicons-calendar' => __('Calendar', 'themehunk-megamenu'),
            'dashicons-visibility' => __('Visibility', 'themehunk-megamenu'),
            'dashicons-post-status' => __('Post Status', 'themehunk-megamenu'),
            'dashicons-edit' => __('Edit', 'themehunk-megamenu'),
            'dashicons-post-trash' => __('Post Trash', 'themehunk-megamenu'),
            'dashicons-trash' => __('Trash', 'themehunk-megamenu'),
            'dashicons-external' => __('External', 'themehunk-megamenu'),
            'dashicons-arrow-up' => __('Arrow Up', 'themehunk-megamenu'),
            'dashicons-arrow-down' => __('Arrow Down', 'themehunk-megamenu'),
            'dashicons-arrow-left' => __('Arrow Left', 'themehunk-megamenu'),
            'dashicons-arrow-right' => __('Arrow Right', 'themehunk-megamenu'),
            'dashicons-arrow-up-alt' => __('Arrow Up (alt)', 'themehunk-megamenu'),
            'dashicons-arrow-down-alt' => __('Arrow Down (alt)', 'themehunk-megamenu'),
            'dashicons-arrow-left-alt' => __('Arrow Left (alt)', 'themehunk-megamenu'),
            'dashicons-arrow-right-alt' => __('Arrow Right (alt)', 'themehunk-megamenu'),
            'dashicons-arrow-up-alt2' => __('Arrow Up (alt 2)', 'themehunk-megamenu'),
            'dashicons-arrow-down-alt2' => __('Arrow Down (alt 2)', 'themehunk-megamenu'),
            'dashicons-arrow-left-alt2' => __('Arrow Left (alt 2)', 'themehunk-megamenu'),
            'dashicons-arrow-right-alt2' => __('Arrow Right (alt 2)', 'themehunk-megamenu'),
            'dashicons-leftright' => __('Arrow Left-Right', 'themehunk-megamenu'),
            'dashicons-sort' => __('Sort', 'themehunk-megamenu'),
            'dashicons-randomize' => __('Randomise', 'themehunk-megamenu'),
            'dashicons-list-view' => __('List View', 'themehunk-megamenu'),
            'dashicons-exerpt-view' => __('Excerpt View', 'themehunk-megamenu'),
            'dashicons-hammer' => __('Hammer', 'themehunk-megamenu'),
            'dashicons-art' => __('Art', 'themehunk-megamenu'),
            'dashicons-migrate' => __('Migrate', 'themehunk-megamenu'),
            'dashicons-performance' => __('Performance', 'themehunk-megamenu'),
            'dashicons-universal-access' => __('Universal Access', 'themehunk-megamenu'),
            'dashicons-universal-access-alt' => __('Universal Access (alt)', 'themehunk-megamenu'),
            'dashicons-tickets' => __('Tickets', 'themehunk-megamenu'),
            'dashicons-nametag' => __('Name Tag', 'themehunk-megamenu'),
            'dashicons-clipboard' => __('Clipboard', 'themehunk-megamenu'),
            'dashicons-heart' => __('Heart', 'themehunk-megamenu'),
            'dashicons-megaphone' => __('Megaphone', 'themehunk-megamenu'),
            'dashicons-schedule' => __('Schedule', 'themehunk-megamenu'),
            'dashicons-wordpress' => __('WordPress', 'themehunk-megamenu'),
            'dashicons-wordpress-alt' => __('WordPress (alt)', 'themehunk-megamenu'),
            'dashicons-pressthis' => __('Press This', 'themehunk-megamenu'),
            'dashicons-update' => __('Update', 'themehunk-megamenu'),
            'dashicons-screenoptions' => __('Screen Options', 'themehunk-megamenu'),
            'dashicons-info' => __('Info', 'themehunk-megamenu'),
            'dashicons-cart' => __('Cart', 'themehunk-megamenu'),
            'dashicons-feedback' => __('Feedback', 'themehunk-megamenu'),
            'dashicons-cloud' => __('Cloud', 'themehunk-megamenu'),
            'dashicons-translation' => __('Translation', 'themehunk-megamenu'),
            'dashicons-tag' => __('Tag', 'themehunk-megamenu'),
            'dashicons-category' => __('Category', 'themehunk-megamenu'),
            'dashicons-archive' => __('Archive', 'themehunk-megamenu'),
            'dashicons-tagcloud' => __('Tag Cloud', 'themehunk-megamenu'),
            'dashicons-text' => __('Text', 'themehunk-megamenu'),
            'dashicons-media-archive' => __('Media Archive', 'themehunk-megamenu'),
            'dashicons-media-audio' => __('Media Audio', 'themehunk-megamenu'),
            'dashicons-media-code' => __('Media Code)', 'themehunk-megamenu'),
            'dashicons-media-default' => __('Media Default', 'themehunk-megamenu'),
            'dashicons-media-document' => __('Media Document', 'themehunk-megamenu'),
            'dashicons-media-interactive' => __('Media Interactive', 'themehunk-megamenu'),
            'dashicons-media-spreadsheet' => __('Media Spreadsheet', 'themehunk-megamenu'),
            'dashicons-media-text' => __('Media Text', 'themehunk-megamenu'),
            'dashicons-media-video' => __('Media Video', 'themehunk-megamenu'),
            'dashicons-playlist-audio' => __('Audio Playlist', 'themehunk-megamenu'),
            'dashicons-playlist-video' => __('Video Playlist', 'themehunk-megamenu'),
            'dashicons-yes' => __('Yes', 'themehunk-megamenu'),
            'dashicons-no' => __('No', 'themehunk-megamenu'),
            'dashicons-no-alt' => __('No (alt)', 'themehunk-megamenu'),
            'dashicons-plus' => __('Plus', 'themehunk-megamenu'),
            'dashicons-plus-alt' => __('Plus (alt)', 'themehunk-megamenu'),
            'dashicons-minus' => __('Minus', 'themehunk-megamenu'),
            'dashicons-dismiss' => __('Dismiss', 'themehunk-megamenu'),
            'dashicons-marker' => __('Marker', 'themehunk-megamenu'),
            'dashicons-star-filled' => __('Star Filled', 'themehunk-megamenu'),
            'dashicons-star-half' => __('Star Half', 'themehunk-megamenu'),
            'dashicons-star-empty' => __('Star Empty', 'themehunk-megamenu'),
            'dashicons-flag' => __('Flag', 'themehunk-megamenu'),
            'dashicons-share' => __('Share', 'themehunk-megamenu'),
            'dashicons-share1' => __('Share 1', 'themehunk-megamenu'),
            'dashicons-share-alt' => __('Share (alt)', 'themehunk-megamenu'),
            'dashicons-share-alt2' => __('Share (alt 2)', 'themehunk-megamenu'),
            'dashicons-twitter' => __('twitter', 'themehunk-megamenu'),
            'dashicons-rss' => __('RSS', 'themehunk-megamenu'),
            'dashicons-email' => __('Email', 'themehunk-megamenu'),
            'dashicons-email-alt' => __('Email (alt)', 'themehunk-megamenu'),
            'dashicons-facebook' => __('Facebook', 'themehunk-megamenu'),
            'dashicons-facebook-alt' => __('Facebook (alt)', 'themehunk-megamenu'),
            'dashicons-networking' => __('Networking', 'themehunk-megamenu'),
            'dashicons-googleplus' => __('Google+', 'themehunk-megamenu'),
            'dashicons-location' => __('Location', 'themehunk-megamenu'),
            'dashicons-location-alt' => __('Location (alt)', 'themehunk-megamenu'),
            'dashicons-camera' => __('Camera', 'themehunk-megamenu'),
            'dashicons-images-alt' => __('Images', 'themehunk-megamenu'),
            'dashicons-images-alt2' => __('Images Alt', 'themehunk-megamenu'),
            'dashicons-video-alt' => __('Video (alt)', 'themehunk-megamenu'),
            'dashicons-video-alt2' => __('Video (alt 2)', 'themehunk-megamenu'),
            'dashicons-video-alt3' => __('Video (alt 3)', 'themehunk-megamenu'),
            'dashicons-vault' => __('Vault', 'themehunk-megamenu'),
            'dashicons-shield' => __('Shield', 'themehunk-megamenu'),
            'dashicons-shield-alt' => __('Shield (alt)', 'themehunk-megamenu'),
            'dashicons-sos' => __('SOS', 'themehunk-megamenu'),
            'dashicons-search' => __('Search', 'themehunk-megamenu'),
            'dashicons-slides' => __('Slides', 'themehunk-megamenu'),
            'dashicons-analytics' => __('Analytics', 'themehunk-megamenu'),
            'dashicons-chart-pie' => __('Pie Chart', 'themehunk-megamenu'),
            'dashicons-chart-bar' => __('Bar Chart', 'themehunk-megamenu'),
            'dashicons-chart-line' => __('Line Chart', 'themehunk-megamenu'),
            'dashicons-chart-area' => __('Area Chart', 'themehunk-megamenu'),
            'dashicons-groups' => __('Groups', 'themehunk-megamenu'),
            'dashicons-businessman' => __('Businessman', 'themehunk-megamenu'),
            'dashicons-id' => __('ID', 'themehunk-megamenu'),
            'dashicons-id-alt' => __('ID (alt)', 'themehunk-megamenu'),
            'dashicons-products' => __('Products', 'themehunk-megamenu'),
            'dashicons-awards' => __('Awards', 'themehunk-megamenu'),
            'dashicons-forms' => __('Forms', 'themehunk-megamenu'),
            'dashicons-testimonial' => __('Testimonial', 'themehunk-megamenu'),
            'dashicons-portfolio' => __('Portfolio', 'themehunk-megamenu'),
            'dashicons-book' => __('Book', 'themehunk-megamenu'),
            'dashicons-book-alt' => __('Book (alt)', 'themehunk-megamenu'),
            'dashicons-download' => __('Download', 'themehunk-megamenu'),
            'dashicons-upload' => __('Upload', 'themehunk-megamenu'),
            'dashicons-backup' => __('Backup', 'themehunk-megamenu'),
            'dashicons-clock' => __('Clock', 'themehunk-megamenu'),
            'dashicons-lightbulb' => __('Lightbulb', 'themehunk-megamenu'),
            'dashicons-microphone' => __('Microphone', 'themehunk-megamenu'),
            'dashicons-desktop' => __('Desktop', 'themehunk-megamenu'),
            'dashicons-tablet' => __('Tablet', 'themehunk-megamenu'),
            'dashicons-smartphone' => __('Smartphone', 'themehunk-megamenu'),
            'dashicons-smiley' => __('Smiley', 'themehunk-megamenu')
        );

        return $icons;
    }
}


if( ! function_exists('themehunk_megamenu_share_themes_across_multisite') ) {
    /*
     * In the first version of MMM, themes were (incorrectly) shared between all sites in a multi site network.
     * Themes will not be shared across sites for new users installing v2.4.3 onwards, but they will be shared for existing (older) users.
     *
     * @since 2.3.7
     */
    function themehunk_megamenu_share_themes_across_multisite(){

        if ( defined('THEMEHUNK_MEGAMENU_SHARE_THEMES_MULTISITE') && THEMEHUNK_MEGAMENU_SHARE_THEMES_MULTISITE === false ) {
            return false;
        }

        if ( defined('THEMEHUNK_MEGAMENU_SHARE_THEMES_MULTISITE') && THEMEHUNK_MEGAMENU_SHARE_THEMES_MULTISITE === true ) {
            return true;
        }

        if ( get_option('themehunk_megamenu_multisite_share_themes') === 'false' ) { // only exists if initially installed version is 2.4.3+
            return false;
        }

        return apply_filters( 'themehunk_megamenu_share_themes_across_multisite', true );
        
    }
}

if ( ! function_exists('themehunk_megamenu_menu_get_last_updated_theme') ) {
    /*
     * Return last updated theme
     *
     * @since 2.3.7
     */
    function themehunk_megamenu_menu_get_last_updated_theme() {

        if ( ! themehunk_megamenu_share_themes_across_multisite() ) {
            return get_option( "themehunk_megamenu_themes_last_updated" );
        }

        return get_site_option( "themehunk_megamenu_themes_last_updated" );
        
    }
}

    if ( ! function_exists('themehunk_megamenu_menu_get_themes') ) {
    /*
     * Return saved themes
     *
     * @since 2.3.7
     */
    function themehunk_megamenu_menu_get_themes() {

        if ( ! themehunk_megamenu_share_themes_across_multisite() ) {
            return get_option( "themehunk_megamenu_themes" );
        }

        return get_site_option( "themehunk_megamenu_themes" );      

    }
}
if ( ! function_exists('themehunk_megamenu_menu_save_themes') ) {
    /*
     * Save menu theme
     *
     * @since 2.3.7
     */
    function themehunk_megamenu_menu_save_themes( $themes ) {

        if ( ! themehunk_megamenu_share_themes_across_multisite() ) {
            return update_option( "themehunk_megamenu_themes", $themes );
        }

        return update_site_option( "themehunk_megamenu_themes", $themes );
        
    }
}
if ( ! function_exists('themehunk_megamenu_menu_save_last_updated_theme') ) {
    /*
     * Save last updated theme
     *
     * @since 2.3.7
     */
    function themehunk_megamenu_menu_save_last_updated_theme( $theme ) {

        if ( ! themehunk_megamenu_share_themes_across_multisite() ) {
            return update_option( "themehunk_megamenu_themes_last_updated", $theme );
        }

        return update_site_option( "themehunk_megamenu_themes_last_updated", $theme );
        
    }
}

if ( ! function_exists('themehunk_megamenu_get_active_caching_plugins') ) {

    /**
     * Return list of active caching/CDN/minification plugins
     *
     * @since 2.4
     * @return array
     */
    function themehunk_megamenu_get_active_caching_plugins() {

        $caching_plugins = apply_filters("themehunk_megamenu_caching_plugins", array(
            'litespeed-cache/litespeed-cache.php',
            'js-css-script-optimizer/js-css-script-optimizer.php',
            'merge-minify-refresh/merge-minify-refresh.php',
            'minify-html-markup/minify-html.php',
            'simple-cache/simple-cache.php',
            'w3-total-cache/w3-total-cache.php',
            'wp-fastest-cache/wpFastestCache.php',
            'wp-speed-of-light/wp-speed-of-light.php',
            'wp-super-cache/wp-cache.php',
            'wp-super-minify/wp-super-minify.php',
            'autoptimize/autoptimize.php',
            'bwp-minify/bwp-minify.php',
            'cache-enabler/cache-enabler.php',
            'cloudflare/cloudflare.php',
            'comet-cache/comet-cache.php',
            'css-optimizer/bpminifycss.php',
            'fast-velocity-minify/fvm.php',
            'hyper-cache/plugin.php',
            'remove-query-strings-littlebizzy/remove-query-strings.php',
            'remove-query-strings-from-static-resources/remove-query-strings.php',
            'query-strings-remover/query-strings-remover.php',
            'wp-rocket/wp-rocket.php',
            'hummingbird-performance/wp-hummingbird.php',
            'breeze/breeze.php'
        ));

        $active_plugins = array();

        foreach ( $caching_plugins as $plugin_path ) {
            if ( is_plugin_active( $plugin_path ) ) {
                $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );
                $active_plugins[] = $plugin_data['Name'];
            }
        }

        return $active_plugins;
    }
}
