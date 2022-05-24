<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access.
}
/**
 * Class ThemeHunk_MegaMenu_Style
 */
if ( ! class_exists('ThemeHunk_MegaMenu_Style')) {

    class ThemeHunk_MegaMenu_Style{

        public $themehunk_megamenu_nav_wrapper_class;
        public $themehunk_megamenu_nav_wrapper_id;
        public $themehunk_megamenu_nav_menu_id;
       /**
         * ThemeHunk_MegaMenu_Style constructor.
         */
        public function __construct(){
            $this->themehunk_megamenu_nav_wrapper_class = '.themehunk-megamenu-wrap';
            $this->themehunk_megamenu_nav_wrapper_id = '#themehunk-megamenu-wrap';
            $this->themehunk_megamenu_nav_menu_id = '#themehunk-megamenu';

            add_action( 'wp_head', array( $this, 'render_css' ) );
        }    

        public function css(){
            $nav_class = $this->themehunk_megamenu_nav_wrapper_class;
            $navbar_id = $this->themehunk_megamenu_nav_wrapper_id;

            //Get all integrated Nav
            $themehunk_megamenu_nav_locations = get_nav_menu_locations();

	        $themehunk_megamenu_options = get_option('themehunk_megamenu_options');
               
            $style = "";

            if (is_array($themehunk_megamenu_nav_locations) && count($themehunk_megamenu_nav_locations)) {
                foreach ( $themehunk_megamenu_nav_locations as $nav_location => $nav_id ) {

	                	if ( !empty( $themehunk_megamenu_options[ $nav_location ]['is_enabled'] ) && $themehunk_megamenu_options[ $nav_location ]['is_enabled'] == '1' ) {
                      		$style .= $this->themehunk_megamenu_generateCss( $nav_location );				            
				        }                
                }
            }

            return $style;
        }   
       /**
         * @param $nav_location
         *
         * @return string
         *
         * Main CSS Generate Method
         */
        public function themehunk_megamenu_generateCss( $nav_location ){

            $nav_wrapper_id = $this->themehunk_megamenu_nav_wrapper_id;

            $style = '';
      
          	$nav_wrapper_id = $nav_wrapper_id . '-' . $nav_location;
          	$themehunk_megamenu_nav_menu_id = $this->themehunk_megamenu_nav_menu_id . '-' . $nav_location;
            //Menu Option 
             $style_manager = new ThemeHunk_MegaMenu_Menu_Style_Manager();

             
             $settings = $style_manager->get_themes();

             $last_updated = themehunk_megamenu_menu_get_last_updated_theme();

             $preselected_theme = isset( $this->themes[ $last_updated ] ) ? $last_updated : 'default';

             $theme_id = isset( $_GET['theme'] ) ? sanitize_text_field( $_GET['theme'] ) : $preselected_theme;

            $menu_item_link_height = $settings[ $theme_id ]['menu_item_link_height'].'px';
            $menu_item_align       = $settings[ $theme_id ]['menu_item_align'];
            $menu_bg_color         = $settings[ $theme_id ]['menu_bg_color'];
            $menu_link_color       = $settings[ $theme_id ]['menu_link_color'];
            $menu_hvr_color        = $settings[ $theme_id ]['menu_hvr_color'];
            $menu_link_bg_color    = $settings[ $theme_id ]['menu_link_bg_color'];
            $menu_link_hvr_bg_color= $settings[ $theme_id ]['menu_link_hvr_bg_color'];
 
            //sub menu
            $sub_menu_bg_color         = $settings[ $theme_id ]['sub_menu_bg_color'];
            $sub_menu_link_color       = $settings[ $theme_id ]['sub_menu_link_color'];
            $sub_menu_hvr_color        = $settings[ $theme_id ]['sub_menu_hvr_color'];
            $sub_menu_hvr_bg_color     = $settings[ $theme_id ]['sub_menu_hvr_bg_color'];

            //menu padding
            $menu_padding_top      = $settings[ $theme_id ]['menu_padding_top'].'px';
            $menu_padding_right    = $settings[ $theme_id ]['menu_padding_right'].'px';
            $menu_padding_bottom   = $settings[ $theme_id ]['menu_padding_bottom'].'px';
            $menu_padding_left     = $settings[ $theme_id ]['menu_padding_left'].'px';

            //menu right
            $menu_margin_left      = $settings[ $theme_id ]['menu_margin_left'].'px';
            $menu_margin_right     = $settings[ $theme_id ]['menu_margin_right'].'px';

            //border radius
            $menu_border_radius_top_left       = $settings[ $theme_id ]['menu_border_radius_top_left'].'px';
            $menu_border_radius_top_right      = $settings[ $theme_id ]['menu_border_radius_top_right'].'px';
            $menu_border_radius_bottom_right   = $settings[ $theme_id ]['menu_border_radius_bottom_right'].'px';
            $menu_border_radius_bottom_left    = $settings[ $theme_id ]['menu_border_radius_bottom_left'].'px';

            // mobile menu
            
            $responsive_breakpoint     = $settings[ $theme_id ]['responsive_breakpoint'].'px';
            $mobile_menu_bg_color      = $settings[ $theme_id ]['mobile_menu_bg_color'];
            $mobile_menu_link_bg_color = $settings[ $theme_id ]['mobile_menu_link_bg_color'];
            $mobile_menu_hvr_bg_color  = $settings[ $theme_id ]['mobile_menu_hvr_bg_color'];

            $mobile_menu_link_color    = $settings[ $theme_id ]['mobile_menu_link_color'];
            $mobile_menu_hvr_link_color= $settings[ $theme_id ]['mobile_menu_hvr_link_color'];

            $menu_dropdown_arrow1 = $settings[ $theme_id ]['menu-dropdown-arrow'];
            if($menu_dropdown_arrow1=='disabled'){
               $menu_dropdown_arrow = "";
            }else{
               $menu_dropdown_arrow = "'\\" .  $menu_dropdown_arrow1 . "'";
            }

            $sub_menu_dropdown_arrow1 = $settings[ $theme_id ]['sub-menu-dropdown-arrow'];

            if( $sub_menu_dropdown_arrow1=='disabled'){
               $sub_menu_dropdown_arrow = "";
            }else{
              $sub_menu_dropdown_arrow = "'\\" . $sub_menu_dropdown_arrow1 . "'";
            }

            // mobile
            $mobile_menu_dropdown_arrow1 = $settings[ $theme_id ]['mobile-menu-dropdown-arrow'];
            if( $mobile_menu_dropdown_arrow1=='disabled'){
               $mobile_menu_dropdown_arrow = "";
            }else{
              $mobile_menu_dropdown_arrow = "'\\" . $mobile_menu_dropdown_arrow1 . "'";
            }

            $mobile_sub_menu_dropdown_arrow1 = $settings[ $theme_id ]['mobile-sub-menu-dropdown-arrow'];
             if( $mobile_sub_menu_dropdown_arrow1=='disabled'){
               $mobile_sub_menu_dropdown_arrow = "";
            }else{
              $mobile_sub_menu_dropdown_arrow = "'\\" . $mobile_sub_menu_dropdown_arrow1 . "'";
            }

            // mobile sub menu
             $mobile_sub_menu_bg_link_color     = $settings[ $theme_id ]['mobile_sub_menu_bg_link_color'];
             $mobile_sub_menu_bg_link_hvr_color = $settings[ $theme_id ]['mobile_sub_menu_bg_link_hvr_color'];

             $mobile_sub_menu_link_color     = $settings[ $theme_id ]['mobile_sub_menu_link_color'];
             $mobile_sub_menu_link_hvr_color = $settings[ $theme_id ]['mobile_sub_menu_link_hvr_color'];
             
             $toggle_bar_height  = $settings[ $theme_id ]['toggle_bar_height'].'px';
             $toggle_bg_color    = $settings[ $theme_id ]['toggle_bg_color'];

             $toggle_icon_clr  = $settings[ $theme_id ]['toggle_icon_clr'];
             $toggle_text_clr  = $settings[ $theme_id ]['toggle_text_clr'];

             $mobile_open_toggle_icon1 = $settings[ $theme_id ]['mobile-open-toggle-icon'];
             $mobile_open_toggle_icon  = "'\\" . $mobile_open_toggle_icon1 . "'";

             $mobile_close_toggle_icon1 = $settings[ $theme_id ]['mobile-close-toggle-icon'];
             $mobile_close_toggle_icon  = "'\\" . $mobile_close_toggle_icon1 . "'";

             $custom_css_  = $settings[ $theme_id ]['custom_css'];
             $custom_css = stripslashes( html_entity_decode( $custom_css_, ENT_QUOTES ) );
            
			 $style = <<<EOT

		     @media only screen and (min-width:$responsive_breakpoint ){
              $nav_wrapper_id  {
                            clear: both;
                        }
                        $nav_wrapper_id {
                            background: #222;
                        }
                        $nav_wrapper_id {
                            border-radius: 0;
                        }
                        
                        $nav_wrapper_id $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-menu-item{
                            transition: none;
                            border-radius: 0;
                            box-shadow: none;
                            background: none;
                            border: 0;
                            bottom: auto;
                            box-sizing: border-box;
                            clip: auto;
                            color: #666;
                            display: block;
                            float: none;
                            font-family: inherit;
                            font-size: 16px;
                            height: auto;
                            left: auto;
                            line-height: 1.7;
                            list-style-type: none;
                            margin: 0;
                            min-height: auto;
                            max-height: none;
                            opacity: 1;
                            outline: none;
                            overflow: visible;
                            padding: 0;
                            pointer-events: auto;
                            right: auto;
                            text-align: left;
                            text-decoration: none;
                            text-indent: 0;
                            text-transform: none;
                            transform: none;
                            top: auto;
                            vertical-align: baseline;
                            visibility: inherit;
                            width: auto;
                            word-wrap: break-word;
                            white-space: normal;
                            position: relative;
                        }
                        $nav_wrapper_id  $themehunk_megamenu_nav_menu_id{
                            visibility: visible;
                            text-align: left;
                            padding: 0px 0px 0px 0px;
                            margin:0;
                        }
                        $nav_wrapper_id  $themehunk_megamenu_nav_menu_id > li.themehunk-megamenu-menu-item {
                            margin: 0 0px 0 0;
                            display: inline-block;
                            height: auto;
                            vertical-align: middle;
                        }

                       $nav_wrapper_id  $themehunk_megamenu_nav_menu_id > li.themehunk-megamenu-menu-item > a.themehunk-megamenu-menu-link{
                            line-height: 40px;
                            height: 40px;
                            padding: 0px 10px 0px 10px;
                            vertical-align: baseline;
                            width: auto;
                            display: block;
                            color: #333;
                            text-transform: none;
                            text-decoration: none;
                            text-align: left;
                            text-decoration: none;
                            background: rgba(0, 0, 0, 0);
                            border: 0;
                            border-radius: 0;
                            font-family: inherit;
                            font-size: 16px;
                            font-weight: normal;
                            outline: none;
                        }

                       $nav_wrapper_id  $themehunk_megamenu_nav_menu_id > li.themehunk-megamenu-menu-item > a.themehunk-megamenu-menu-link:hover {
                            background: #0073aa;
                            color: #fff;
                            font-weight: normal;
                            text-decoration: none;
                            border-color: #fff;
                        }
                       $nav_wrapper_id  $themehunk_megamenu_nav_menu_id a.themehunk-megamenu-menu-link{
                            cursor: pointer;
                            padding-top:0;
                            padding-bottom:0;
                        }

                      $nav_wrapper_id  $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-is-megamenu{
                            position: static;
                        }
                      $nav_wrapper_id  $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-is-megamenu .themehunk-megamenu-menu-item.themehunk-megamenu-mmth-type-widget{
                            padding: 10px;
                        }
                     
                       $nav_wrapper_id  $themehunk_megamenu_nav_menu_id .themehunk-megamenu-is-megamenu p {
                            margin-bottom: 10px;
                        }
                       $nav_wrapper_id  $themehunk_megamenu_nav_menu_id ul ul {
                            list-style-type: none;
                            margin: 0;
                            padding: 0;
                       }


            $nav_wrapper_id $themehunk_megamenu_nav_menu_id > li.themehunk-megamenu-menu-item > a.themehunk-megamenu-menu-link{
              height:$menu_item_link_height;
              line-height:$menu_item_link_height;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id{
               text-align:$menu_item_align;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id > li.themehunk-megamenu-menu-item > a.themehunk-megamenu-menu-link{
               color: $menu_link_color;
            }
             $nav_wrapper_id, $nav_wrapper_id $themehunk_megamenu_nav_menu_id{
                background:$menu_bg_color;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id > li.themehunk-megamenu-menu-item > a.themehunk-megamenu-menu-link{
               color: $menu_link_color;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id > li.themehunk-megamenu-menu-item > a.themehunk-megamenu-menu-link:hover{
               color:$menu_hvr_color;
            }
             $nav_wrapper_id $themehunk_megamenu_nav_menu_id > li.themehunk-megamenu-menu-item > a.themehunk-megamenu-menu-link{
                background:$menu_link_bg_color;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id > li.themehunk-megamenu-menu-item > a.themehunk-megamenu-menu-link:hover{
                background:$menu_link_hvr_bg_color;
            }  
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-menu-item > ul.mega-sub-menu-themehunk-megamenu,
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id ul.mega-sub-menu-themehunk-megamenu li.themehunk-megamenu-menu-item a.themehunk-megamenu-menu-link{
                background:$sub_menu_bg_color;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id .mega-sub-menu-themehunk-megamenu > li.themehunk-megamenu-menu-item a.themehunk-megamenu-menu-link:hover{
                background:$sub_menu_hvr_bg_color;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id ul.mega-sub-menu-themehunk-megamenu li.themehunk-megamenu-menu-item a.themehunk-megamenu-menu-link{
                color:$sub_menu_link_color;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id ul.mega-sub-menu-themehunk-megamenu li.themehunk-megamenu-menu-item a.themehunk-megamenu-menu-link:hover{
                color:$sub_menu_hvr_color;
            }

            $nav_wrapper_id $themehunk_megamenu_nav_menu_id{
             padding-top:$menu_padding_top;
             padding-left:$menu_padding_left;
             padding-right:$menu_padding_right;
             padding-bottom:$menu_padding_bottom;
             border-top-left-radius:$menu_border_radius_top_left;    
             border-top-right-radius:$menu_border_radius_top_right;   
             border-bottom-right-radius:$menu_border_radius_bottom_right;
             border-bottom-left-radius:$menu_border_radius_bottom_left;

           }
           $nav_wrapper_id $themehunk_megamenu_nav_menu_id > li.themehunk-megamenu-menu-item > a.themehunk-megamenu-menu-link{
            margin-left:$menu_margin_left;
            margin-right:$menu_margin_right;
           }
           

           $nav_wrapper_id $themehunk_megamenu_nav_menu_id .themehunk-megamenu-menu-item-has-children .themehunk-megamenu-menu-link > span.mega-indicator:after{
              content:$menu_dropdown_arrow;
           }
           $nav_wrapper_id $themehunk_megamenu_nav_menu_id ul.mega-sub-menu-themehunk-megamenu .themehunk-megamenu-menu-link > span.mega-indicator:after{
              content:$sub_menu_dropdown_arrow;
           }
           $nav_wrapper_id $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-is-megamenu .themehunk-megamenu-menu-item.themehunk-megamenu-mmth-type-widget{
            font-size:14px;
            font-weight:normal;
            }

            $nav_wrapper_id $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-is-megamenu .themehunk-megamenu-menu-item.themehunk-megamenu-mmth-type-widget h4.themehunk-megamenu-item-title{
                 font-weight: 600;
                    font-size: 14px;
                    line-height: 22px;
                    margin-bottom:15px;
                    text-transform: uppercase;
                    margin-left: 0;
                    margin-right: 0;
                     margin-top: 0;
                     font-weight:normal;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-is-megamenu .themehunk-megamenu-menu-item.themehunk-megamenu-mmth-type-widget ul{
            list-style-type:none;
            padding-left:0;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-is-megamenu .themehunk-megamenu-menu-item.themehunk-megamenu-mmth-type-widget ul li{
                list-style-type: none;
                    line-height: 20px;
                    font-size: 13px;
                    margin-bottom: 15px;
                }

            .themehunk-megamenu-menu .navigation.mobile-menu-wrapper ul ul{
                    display:block;
                }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id li a{
                border:none;
                width:100%;
            }    
           
                           
          }   
            @media only screen and (max-width:$responsive_breakpoint ){
                 $nav_wrapper_id  $themehunk_megamenu_nav_menu_id .themehunk-megamenu-themehunk-megamenu-col-6, 
                 $nav_wrapper_id  $themehunk_megamenu_nav_menu_id.themehunk-megamenu-themehunk-megamenu-col-4,
                 $nav_wrapper_id  $themehunk_megamenu_nav_menu_id.themehunk-megamenu-themehunk-megamenu-col-3,
                 $nav_wrapper_id  $themehunk_megamenu_nav_menu_id .themehunk-megamenu-themehunk-megamenu-col-5,
                 $nav_wrapper_id  $themehunk_megamenu_nav_menu_id .themehunk-megamenu-themehunk-megamenu-col-2{
                 width:100%;
            }
           $nav_wrapper_id  $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-menu-item.themehunk-megamenu-is-megamenu > ul.mega-sub-menu-themehunk-megamenu{
                width:100%!important;
           }
           $nav_wrapper_id  .mega-menu-themehunk-megamenu-toggle .mega-toggle-blocks-right .mega-toggle-block-1:after,ul[data-effect-mobile=slide_center] .mega-toggle-label-themehunk-megamenu-closed:after{
                color: $toggle_icon_clr;
           }
           $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle .mega-toggle-themehunk-megamenu-label{
                color: $toggle_text_clr;
           }
             .mega-menu-themehunk-megamenu-toggle{
                display: -webkit-box;
                display: -ms-flexbox;
                display: -webkit-flex;
                display: inline-flex;
            }
            $nav_wrapper_id  $themehunk_megamenu_nav_menu_id{
                position: fixed;
                display: block;
                width: 300px;
                max-width: 90%;
                height: 100vh;
                max-height: 100vh;
                top: 0;
                box-sizing: border-box;
                transition: left 200ms ease-in-out, right 200ms ease-in-out;
                overflow:auto;
                z-index: 9999999999;
            }
            $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle + $themehunk_megamenu_nav_menu_id{
                background:#0073aa;
                padding: 0px 0px 0px 0px;
                display: none;
            }
            
            $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle + $themehunk_megamenu_nav_menu_id{
                position: fixed;
                display: block;
                width: 300px;
                max-width: 90%;
                height: 100vh;
                max-height: 100vh;
                top: 0;
                box-sizing: border-box;
                transition: left 200ms ease-in-out, right 200ms ease-in-out;
                overflow:auto;
                z-index: 9999999999;
                margin:0;
                    text-align: initial;
            }
            $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle + [data-effect-mobile="slide_left"]{
                right: -300px;
            }
            $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle.mega-menu-open + $themehunk_megamenu_nav_menu_id{
                display: block;
            }

            $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle.mega-menu-open + [data-effect-mobile="slide_left"] {
                right: 0;

            }
             $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle.mega-menu-open + [data-effect-mobile="slide_right"] {
               left: 0;
            }
             $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle + [data-effect-mobile="slide_right"]{
                    left: -300px;
                }
            $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle.mega-menu-open + [data-effect-mobile="slide_center"]{
                display:block!important;
            }
              $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle + [data-effect-mobile="slide_center"] {
                    width: 100%!important;
                    margin: 0!important;
                    top: 0!important;
                    left: 0!important;
                    right: 0!important;
                    bottom: 0!important;
                    max-width: 100%!important;
                display:none!important;
                   -webkit-transition: all 0.3s ease!important;
                    -moz-transition: all 0.3s ease!important;
                    -ms-transition: all 0.3s ease!important;
                    -o-transition: all 0.3s ease!important;
                    transition: all 0.3s ease!important;
                    -webkit-animation: bodyfadeIn .3s!important;
                    -moz-animation: bodyfadeIn .3s!important;
                    -ms-animation: bodyfadeIn .3s!important;
                    -o-animation: bodyfadeIn .3s!important;
                    animation: bodyfadeIn .3s!important;
                }
                $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle + [data-effect-mobile="slide_center"] li{
                    width: 250px!important;
                    margin: auto!important;
                    display: inherit!important;
                }
                ul[data-effect-mobile=slide_center] .mega-toggle-label-themehunk-megamenu-closed:first-child{
                    display:block;
                }
                    ul[data-effect-mobile=slide_center] .mega-toggle-label-themehunk-megamenu-closed{
                    display:none;
                    }   

                   
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id > li.themehunk-megamenu-menu-item{
                display: list-item;
                margin: 0;
                clear: both;
                border: 0;
                line-height:normal;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id > li.themehunk-megamenu-menu-item > a.themehunk-megamenu-menu-link{
                border-radius: 0;
                border: 0;
                margin: 0;
                line-height: 40px;
                height: 40px;
                padding: 0 10px;
                background: transparent;
                text-align: left;
                color: #fff;
                font-size: 14px;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id > li.themehunk-megamenu-menu-item-has-children > a.themehunk-megamenu-menu-link > span.mega-indicator {
                float: right;
            }

            $nav_wrapper_id $themehunk_megamenu_nav_menu_id > li.themehunk-megamenu-menu-item ul.mega-sub-menu-themehunk-megamenu{
             position: static;
                float: left;
                width: 100%;
                padding: 0;
                border: 0;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id  > li.themehunk-megamenu-menu-item.mega-toggle-on > ul.mega-sub-menu-themehunk-megamenu,li.mega-toggle-on > ul.mega-sub-menu-themehunk-megamenu{
                display:block;
                visibility: visible;
                opacity: 1;
               
               transition:none;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id> li.themehunk-megamenu-menu-item > ul.mega-sub-menu-themehunk-megamenu,ul.mega-sub-menu-themehunk-megamenu{
                display:none;
                visibility: visible;
                opacity: 1;
            transition:none;
            }

            $nav_wrapper_id $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-menu-item-has-children.mega-toggle-on > a.themehunk-megamenu-menu-link > span.mega-indicator:after {
                content: '\f142';
            }
            .themehunk-megamenu-is-megamenu ul.mega-sub-menu-themehunk-megamenu{
            display:block;
            }

            $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle + #themehunk-megamenu-menu-1{
              background:$mobile_menu_bg_color;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-menu-item{
              background:$mobile_menu_link_bg_color;
              
            }
             $nav_wrapper_id $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-menu-item:hover{
             background:$mobile_menu_hvr_bg_color;
             
           }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id > li.themehunk-megamenu-menu-item > a.themehunk-megamenu-menu-link{
             color:$mobile_menu_link_color;
           }
           $nav_wrapper_id $themehunk_megamenu_nav_menu_id > li.themehunk-megamenu-menu-item:hover a.themehunk-megamenu-menu-link{
             color:$mobile_menu_hvr_link_color;
           }
           $nav_wrapper_id $themehunk_megamenu_nav_menu_id ul.mega-sub-menu-themehunk-megamenu li.themehunk-megamenu-menu-item a.themehunk-megamenu-menu-link{
            background:$mobile_sub_menu_bg_link_color;
            color:$mobile_sub_menu_link_color;
           }
           $nav_wrapper_id $themehunk_megamenu_nav_menu_id ul.mega-sub-menu-themehunk-megamenu li.themehunk-megamenu-menu-item a.themehunk-megamenu-menu-link:hover{
            background:$mobile_sub_menu_bg_link_hvr_color;
            color:$mobile_sub_menu_link_hvr_color;
           }
          $nav_wrapper_id $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-is-megamenu .themehunk-megamenu-menu-item.themehunk-megamenu-mmth-type-widget:hover,$nav_wrapper_id $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-is-megamenu .themehunk-megamenu-menu-item.themehunk-megamenu-mmth-type-widget{
            background:transparent;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-menu-item.themehunk-megamenu-is-megamenu > ul.mega-sub-menu-themehunk-megamenu{
            max-height: inherit;
                overflow: auto;
            }
          .mega-menu-themehunk-megamenu-toggle{
              height:$toggle_bar_height;
              line-height:$toggle_bar_height;
              background:$toggle_bg_color;
           }
          $nav_wrapper_id $themehunk_megamenu_nav_menu_id .themehunk-megamenu-menu-link > span.mega-indicator:after,ul.mega-sub-menu-themehunk-megamenu .themehunk-megamenu-menu-link > span.mega-indicator:after{
              content:$mobile_menu_dropdown_arrow;
          }
          $nav_wrapper_id $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-menu-item-has-children.mega-toggle-on > a.themehunk-megamenu-menu-link > span.mega-indicator:after{
               content:$mobile_sub_menu_dropdown_arrow;
          }
          $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle .mega-toggle-blocks-right .mega-toggle-block-1:after{
            content:$mobile_open_toggle_icon;
          }
          $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle.mega-menu-open .mega-toggle-block-1:after{
            content:$mobile_close_toggle_icon;
          }
          $nav_wrapper_id $themehunk_megamenu_nav_menu_id .themehunk-megamenu-is-megamenu .themehunk-megamenu-themehunk-megamenu-col{
            width:100%;
            }
           
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-is-megamenu .themehunk-megamenu-menu-item.themehunk-megamenu-mmth-type-widget {
                font-size: 14px;
                font-weight: normal;
            padding:10px;
            }
            $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle + $themehunk_megamenu_nav_menu_id::-webkit-scrollbar-track
            {
                -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
                background-color: #F5F5F5;
            }

            $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle + $themehunk_megamenu_nav_menu_id::-webkit-scrollbar
            {
                width: 5px;
                background-color: #F5F5F5;
            }

            $nav_wrapper_id .mega-menu-themehunk-megamenu-toggle + $themehunk_megamenu_nav_menu_id::-webkit-scrollbar-thumb
            {
                background-color: #000000;
                border: 2px solid #555555;
            }
            $nav_wrapper_id ul[data-mobile-hide-submenu="on"] ul.mega-sub-menu-themehunk-megamenu,$nav_wrapper_id ul[data-mobile-hide-submenu="on"] .mega-indicator{
             display:none!important;

            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id .themehunk-megamenu-is-megamenu .themehunk-megamenu-themehunk-megamenu-col{
                padding:0!important;
            }
            $nav_wrapper_id $themehunk_megamenu_nav_menu_id li.themehunk-megamenu-menu-item.themehunk-megamenu-is-megamenu > ul.mega-sub-menu-themehunk-megamenu{
                border-radius:0!important;
                padding:5px!important;
             }

             $nav_wrapper_id $themehunk_megamenu_nav_menu_id .themehunk-megamenu-is-megamenu .themehunk-megamenu-mmth-type-widget .themehunk-megamenu-item-title{
                font-size:16px;
                line-height:23px;
                margin:0;
                padding:0;
                margin-bottom:10px;
           }
           .themehunk-megamenu-menu a.themehunk-megamenu-menu-link{
                       cursor: pointer;
                        padding-top: 0;
                        padding-bottom: 0;
                        padding:0;
                        line-height: normal;
                        display: block;
          }
           $nav_wrapper_id $themehunk_megamenu_nav_menu_id .themehunk-megamenu-is-megamenu .themehunk-megamenu-mmth-type-widget .tagcloud a{
                padding:7px!important;
            }
          $nav_wrapper_id $themehunk_megamenu_nav_menu_id .themehunk-megamenu-is-megamenu .themehunk-megamenu-mmth-type-widget a {
                    padding: 0px!important;
                }
         .themehunk-megamenu-is-megamenu .mega-sub-menu-themehunk-megamenu.depth-0:before{
            height:100%!important;
         }   

         ol, ul {
                margin: 0;
            }  

        }

        li.themehunk-megamenu-menu-item.themehunk-megamenu-is-megamenu > ul.mega-sub-menu-themehunk-megamenu.panright{
        left: auto;
        right: 0;
        }

        $custom_css
                                                                      
EOT;

            return apply_filters('themehunk_megamenu_generated_css', $style, $nav_wrapper_id);
        }  
        /**
         * Render css to wp head
         */
        public function render_css(){

            $style = '<style type="text/css">';
            $style .= $this->css();
            $style .= '</style>';

            echo $style;
        }
}

	new ThemeHunk_MegaMenu_Style();

}