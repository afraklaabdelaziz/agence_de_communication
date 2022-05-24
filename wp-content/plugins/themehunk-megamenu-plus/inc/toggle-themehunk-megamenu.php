<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access.
}

if ( ! class_exists('ThemeHunk_MegaMenu_Toggle_Blocks') ) :

/**
 * Mobile Toggle Blocks
 */
class ThemeHunk_MegaMenu_Toggle_Blocks {

	/**
		 * Member Variable
		 *
		 * @var object instance
		 */
		private static $instance;
		/**
		 * Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
        
        /**
     * Constructor
     *
     * @since 2.1
     */
    public function __construct() {
            add_filter( 'themehunk_megamenu_toggle_bar_content', array( $this, 'themehunk_megamenu_output_public_toggle_blocks' ), 10, 4 );
            add_action( 'themehunk_megamenu_output_public_toggle_block_menu_toggle', array( $this, 'themehunk_megamenu_output_menu_public_toggle_block_html'), 10, 2 );

    }

    /**
     * Output the menu toggle block (front end)
     *
     * @since 2.4.1
     * @param string $html
     * @param array $settings
     * @return string
     */
    public function themehunk_megamenu_output_menu_public_toggle_block_html( $html, $settings ) {

           $settings = get_option('themehunk_megamenu_themes');

            $last_updated = themehunk_megamenu_menu_get_last_updated_theme();

            $preselected_theme = isset( $this->themes[ $last_updated ] ) ? $last_updated : 'default';

            $theme_id = isset( $_GET['theme'] ) ? sanitize_text_field( $_GET['theme'] ) : $preselected_theme;
        // only use HTML version of toggle block if CSS version is above 2.4.0.2
        // if transient is missing, assume the latest version of the CSS is present and use Flex layout
            $toggle_text = isset( $settings[ $theme_id ]['toggle_text'] ) ? do_shortcode( stripslashes( $settings[ $theme_id ]['toggle_text'] ) ) : "MENU";
            
            $html = "<span class='mega-toggle-themehunk-megamenu-label' role='button' aria-expanded='false'><span class='mega-toggle-label-themehunk-megamenu-closed'>{$toggle_text}</span></span>";
       

        return apply_filters("themehunk_megamenu_toggle_menu_toggle_html", $html);

    }
    /**
     * Get the HTML output for the toggle blocks
     *
     * @since 2.1
     * @param string $content
     * @param string $nav_menu
     * @param array $args
     * @param string $theme_id
     * @return string
     */
    public function themehunk_megamenu_output_public_toggle_blocks( $content, $nav_menu, $args, $theme_id ) {

        $toggle_blocks = $this->themehunk_megamenu_get_toggle_blocks_for_theme( $theme_id );

        $blocks_html = "";

       if ( is_array( $toggle_blocks ) ) {
        $blocks_html = $this->themehunk_megamenu_get_flex_blocks_html($toggle_blocks, $content, $nav_menu, $args, $theme_id);
           
        }
        $content .= $blocks_html;

        return $content;

    }

    /**
     * Sort the toggle blocks into 3 divs (left, center, right) to be aligned using flex CSS.
     *
     * @param array $toggle_blocks
     * @since 2.4.1
     * @return string html
     */
    private function themehunk_megamenu_get_flex_blocks_html($toggle_blocks, $content, $nav_menu, $args, $theme_id ) {

        $sorted_blocks = array();
        /** Sort blocks into left, center, right array **/
        foreach ( $toggle_blocks as $block_id => $block ) {
            if ( isset( $block['align'] ) ) {
                $sorted_blocks[$block['align']][$block_id] = $block;
            } else {
                $sorted_blocks['left'][$block_id] = $block;
            }
        }
        $blocks_html = '<div class="mega-toggle-blocks-right">';

        if ( isset( $sorted_blocks['right'] ) ) {
            foreach ( $sorted_blocks['right'] as $block_id => $block ) {
               $blocks_html .= $this->themehunk_megamenu_get_toggle_block_html($block_id, $block, $content, $nav_menu, $args, $theme_id);
            }
        }

        $blocks_html .= "</div>";
        return $blocks_html;
    }


    /** 
     * Generate the HTML for a single toggle block
     *
     * @since 2.4.1
     * @param string block_id
     * @param array $block
     * @return string
     */
    private function themehunk_megamenu_get_toggle_block_html($block_id, $block, $content, $nav_menu, $args, $theme_id) {
        $block_html = "";

        if ( isset( $block['type'] ) ) {
            $class = "mega-" . str_replace("_", "-", $block['type']) . "-block";
        } else {
            $class = "";
        }

        $id = apply_filters('themehunk_megamenu_toggle_block_id', 'mega-toggle-block-' . $block_id);

        $atts = array(
            "class" => "mega-toggle-block {$class} mega-toggle-block-{$block_id}",
            "id" => "mega-toggle-block-{$block_id}",
        );

        if ( isset( $block['type'] ) && $block['type'] == 'menu_toggle' ) {
            $atts['tabindex'] = '0';
        }

        $attributes = apply_filters('themehunk_megamenu_toggle_block_attributes', $atts, $block, $content, $nav_menu, $args, $theme_id);

        $block_html .= "<div";

        foreach ( $attributes as $attribute => $val ) {
            $block_html .= " " . $attribute . "='" . esc_attr( $val ) . "'";
        }

        $block_html .= ">";
        $block_html .= apply_filters("themehunk_megamenu_output_public_toggle_block_{$block['type']}", "", $block);
        $block_html .= "</div>";

        return $block_html;
    }


    /**
     * Return the saved toggle blocks for a specified theme
     *
     * @param string $theme_id
     * @since 2.1
     * @return array
     */
    private function themehunk_megamenu_get_toggle_blocks_for_theme( $theme_id ) {

        // $blocks = max_mega_menu_get_toggle_blocks();

        // if ( isset( $blocks[ $theme_id ] ) ) {
        //     return $blocks[ $theme_id ];
        // }

        // backwards compatibility
        // default to right aligned menu toggle using existing theme settings
        $default_blocks = array(
            1 => $this->themehunk_megamenu_get_default_menu_toggle_block( $theme_id )
        );

        return $default_blocks;

    }

    /**
     * Return default menu toggle block settings
     *
     * @since 2.1
     * @return array
     */
    private function themehunk_megamenu_get_default_menu_toggle_block( $theme_id = 'default' ) {

        $menu_theme =array();

        $defaults = array(
            'type' => 'menu_toggle',
            'align' => 'right',
               
        );

        return $defaults;
    }



}

endif;

ThemeHunk_MegaMenu_Toggle_Blocks::get_instance();