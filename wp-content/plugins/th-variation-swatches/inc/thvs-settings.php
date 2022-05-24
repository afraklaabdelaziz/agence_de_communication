<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Th_Variation_Swatches_Settings' ) ):

	class Th_Variation_Swatches_Settings {

    private $setting_name = 'th_variation_swatches';
		private $setting_reset_name = 'reset';
		private $theme_feature_name = 'th-variation-swatches';
		private $slug;
		private $plugin_class;
		private $defaults = array();
		private $reserved_key = '';
		private $reserved_fields = array();
		private $fields = array();
		
             public function __construct() {
             $this->settings_name   = apply_filters( 'thvs_settings_name', $this->setting_name );
             $this->fields          = apply_filters( 'thvs_settings', $this->fields );
		         $this->reserved_key    = sprintf( '%s_reserved', $this->settings_name );
		         $this->reserved_fields = apply_filters( 'thvs_reserved_fields', array() );

             
             add_action( 'admin_menu', array( $this, 'add_menu' ) );
             add_action( 'init', array( $this, 'set_defaults' ), 8 );
             add_action( 'admin_init', array( $this, 'settings_init' ), 90 );
             add_action( 'admin_enqueue_scripts', array( $this, 'script_enqueue' ) );
             
				     add_action('wp_ajax_thvs_form_setting', array($this, 'thvs_form_setting'));
				     add_action( 'wp_ajax_nopriv_thvs_form_setting', array($this, 'thvs_form_setting'));
          }
            
            public function add_menu() {
						$page_title = esc_html__( 'Variation Swatches for WooCommerce Settings', 'th-variation-swatches' );
						 $menu_title = esc_html__( 'TH Variation Swatches', 'th-variation-swatches' );
						// add_menu_page( $page_title, $menu_title, 'edit_theme_options', 'th-variation-swatches', array(
						// 	$this,
						// 	'settings_form'
						// ),  esc_url(TH_VARIATION_SWATCHES_IMAGES_URI.'/icon.png'), 31 );
						add_submenu_page( 'themehunk-plugins', $page_title, $menu_title, 'manage_options', 'th-variation-swatches', array($this, 'settings_form'),15 );
		}

		public function admin_add_class(){
			  $classes ='';
			  if($this->get_option( 'show_title' ) == 1){
			  	return $classes = 'show-title';
			  }
				
		}

		public function settings_form() {

			if ( ! current_user_can( 'manage_options' ) ) {

				    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

			}

			if( ! class_exists( 'WooCommerce' ) ){

				   printf('<h2 class="requirement-notice">%s</h2>',__('Th Variation Swatches requires WooCommerce to work. Make sure that you have installed and activated WooCommerce Plugin.','th-variation-swatches' ) );

             return;

				}
		
			?>
			<div id="thvs" class="settings-wrap  <?php echo esc_attr($this->admin_add_class());?>">
  
				<div class="top-wrap"><div id="logo"></div>
				  <h1><?php _e('TH Variation Swatches','th-variation-swatches'); ?></h1>
			   </div>
				<form method="post" action="" enctype="multipart/form-data" class="thvs-setting-form">
                     <input type="hidden" name="action" value="thvs_form_setting">
					 

					<?php $this->options_tabs(); ?>
                     <div class="setting-wrap">
					<div id="settings-tabs">
						<?php foreach ( $this->fields as $tab ):

							if ( ! isset( $tab['active'] ) ) {
								$tab['active'] = false;
							}
							$is_active = ( $this->get_last_active_tab() == $tab['id'] );

							?>

							<div id="<?php echo esc_attr($tab['id']); ?>"
								 class="settings-tab thvs-setting-tab"
								 style="<?php echo ! esc_attr($is_active) ? 'display: none' : '' ?>">
								 
								<?php foreach ( $tab['sections'] as $section ):

					        	$this->do_settings_sections( $tab['id'] . $section['id'] );

								endforeach; ?>
							</div>

						<?php endforeach; ?>
					</div>

					<?php
					$this->last_tab_input();
					
					?>
					<p class="submit thvs-button-wrapper">
						
						 <a onclick="return confirm('<?php esc_attr_e( 'Are you sure to reset current settings?', 'th-variation-swatches' ) ?>')" class="reset" href="<?php echo esc_url($this->reset_url()); ?>"><?php esc_html_e( 'Reset all', 'th-variation-swatches' ); ?>
						</a>
						 <button  disabled id="submit" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'th-variation-swatches' ) ?>"><span class="dashicons dashicons-image-rotate spin"></span><span><?php esc_html_e( 'Save Changes', 'th-variation-swatches' ) ?></span>
						 </button>
					</p> 

            </div>

				</form>
			</div>
			<div class="thvs-notes-wrap">

            	<div class="thvs-notes-row thvs-wrap-doc"><h4 class="wrp-title"><?php esc_html_e( 'Documentation', 'th-variation-swatches' ) ?></h4><p><?php esc_html_e( '', 'th-variation-swatches' ) ?></p><a target="_blank" href="<?php echo esc_url('https://themehunk.com/docs/th-variation-swatches-plugin/'); ?>"><?php esc_html_e( 'Read Now', 'th-variation-swatches' ) ?></a></div>

            	<div class="thvs-notes-row thvs-wrap-pro"><h4 class="wrp-title"><?php esc_html_e( 'Unlock TH Variation Swatches Pro','th-variation-swatches', 'th-advance-product-search' ) ?></h4><img src='<?php echo esc_url(TH_VARIATION_SWATCHES_IMAGES_URI.'th-variation-pro.png') ?>' alt="amaz-store"><a target="_blank" href="<?php echo esc_url('https://themehunk.com/th-variation-swatches/'); ?>"><?php esc_html_e( 'Upgrade Pro', 'th-variation-swatches' ) ?></a></div>

            	<div class="thvs-notes-row thvs-wrap-img">
	               	<a target="_blank" href="<?php echo esc_url('https://themehunk.com/product/amaz-store/'); ?>"><img src='<?php echo esc_url(TH_VARIATION_SWATCHES_IMAGES_URI.'amaz-store.png') ?>' alt="amaz-store">
	               	</a>
            	</div>

      </div>

			<?php
			
		}

    public function thvs_form_setting(){  
             if( isset($_POST['th_variation_swatches']) ){
             	        $th_variation_swatches =  $_POST['th_variation_swatches']; 
                      $sanitize_data_array = $this->thvs_form_sanitize($th_variation_swatches);
                      update_option('th_variation_swatches',$sanitize_data_array);         
	            }
	            die();  
    }
        
    public function thvs_form_sanitize( $input ){
			$new_input = array();
			foreach ( $input as $key => $val ){
				$new_input[ $key ] = ( isset( $input[ $key ] ) ) ? sanitize_text_field( $val ) :'';
	   }
	   return $new_input;

    }

		public function reset_url() {
			return add_query_arg( array( 'page' => 'th-variation-swatches', 'reset' => '' ), admin_url( 'admin.php' ) );
		}

		public function settings_url(){
			return add_query_arg( array( 'page' => 'th-variation-swatches' ), admin_url( 'admin.php' ) );
		}
    private function set_default( $key, $type, $value ) {
		$this->defaults[ $key ] = array( 'id' => $key, 'type' => $type, 'value' => $value );
		}

		private function get_default( $key ) {
			return isset( $this->defaults[ $key ] ) ? $this->defaults[ $key ] : null;
		}

		public function get_defaults() {
			return $this->defaults;
		}


    public function is_reset_all() {
			return isset( $_GET['page'] ) && ( $_GET['page'] == 'th-variation-swatches' ) && isset( $_GET[ $this->setting_reset_name ] );
		}  

		public function delete_settings() {

			do_action( sprintf( 'delete_%s_settings', $this->settings_name ), $this );

			// license_key should not updated

			return delete_option( $this->settings_name );
		}
		public function set_defaults() {
			foreach ( $this->fields as $tab_key => $tab ) {
				$tab = apply_filters( 'thvs_settings_tab', $tab );

				foreach ( $tab['sections'] as $section_key => $section ) {

					$section = apply_filters( 'thvs_settings_section', $section, $tab );

					$section['id'] = ! isset( $section['id'] ) ? $tab['id'] . '-section' : $section['id'];

					$section['fields'] = apply_filters( 'thvs_settings_fields', $section['fields'], $section, $tab );

					foreach ( $section['fields'] as $field ) {
						if ( isset( $field['pro'] ) ) {
							continue;
						}
						$field['default'] = isset( $field['default'] ) ? $field['default'] : null;
						$this->set_default( $field['id'], $field['type'], $field['default'] );
					}
				}
			}
		}
		
		public function sanitize_callback( $options ) {

			foreach ( $this->get_defaults() as $opt ) {
				if ( $opt['type'] === 'checkbox' && ! isset( $options[ $opt['id'] ] ) ){
					$options[ $opt['id'] ] = 0;
				}
			}

			return $options;
		}

		public function settings_init() {

			if ( $this->is_reset_all() ) {
				 $this->delete_settings();
				 wp_redirect(esc_url($this->settings_url()));
			}
              
		  register_setting( $this->settings_name, $this->settings_name, array( $this, 'sanitize_callback' ) );

			foreach ( $this->fields as $tab_key => $tab ) {

				$tab = apply_filters( 'thvs_settings_tab', $tab );

				foreach ( $tab['sections'] as $section_key => $section ) {

					$section = apply_filters( 'thvs_settings_section', $section, $tab );

					$section['id'] = ! isset( $section['id'] ) ? $tab['id'] . '-section-' . $section_key : $section['id'];

					// Adding Settings section id
					$this->fields[ $tab_key ]['sections'][ $section_key ]['id'] = $section['id'];

					add_settings_section( $tab['id'] . $section['id'], $section['title'], function () use ( $section ) {
						if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {
							echo '<div class="inside">' . esc_html($section['desc']) . '</div>';
						}
					}, $tab['id'] . $section['id'] );

					$section['fields'] = apply_filters( 'thvs_settings_fields', $section['fields'], $section, $tab );

					foreach ( $section['fields'] as $field ) {

						$field['label_for'] = $field['id'] . '-field';
						$field['default']   = isset( $field['default'] ) ? $field['default'] : null;

						if ( $field['type'] == 'checkbox' || $field['type'] == 'radio' ) {
							unset( $field['label_for'] );
						}

						add_settings_field( $this->settings_name . '[' . $field['id'] . ']', $field['title'], array(
							$this,
							'field_callback'
						), $tab['id'] . $section['id'], $tab['id'] . $section['id'], $field );

					}
				}
			}
		}

		public function options_tabs() {
			?>
			<div class="nav-tab-wrapper wp-clearfix">
				<?php foreach ( $this->fields as $tabs ): ?>
					<a data-target="<?php echo esc_attr($tabs['id']); ?>"  class="thvs-setting-nav-tab nav-tab <?php echo esc_html($this->get_options_tab_css_classes( $tabs )); ?> " href="#<?php echo esc_attr($tabs['id']); ?>"><?php echo esc_html($tabs['title']); ?></a>
				<?php endforeach; ?>
			</div>
			<?php
		}

		private function get_last_active_tab() {
			$last_option_tab = '';
			$last_tab        = $last_option_tab;

			if ( isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ) {
				$last_tab = trim( sanitize_key($_GET['tab']) );
			}

			if ( $last_option_tab ) {
				$last_tab = $last_option_tab;
			}

			$default_tab = '';
			foreach ( $this->fields as $tabs ) {
				if ( isset( $tabs['active'] ) && $tabs['active'] ) {
					$default_tab = sanitize_key($tabs['id']);
					break;
				}
			}

			return ! empty( $last_tab ) ? esc_html( $last_tab ) : esc_html( $default_tab );

		}
		private function last_tab_input() {
			printf( '<input type="hidden" id="_last_active_tab" name="%s[_last_active_tab]" value="%s">', $this->settings_name, $this->get_last_active_tab() );
		}

		private function get_options_tab_css_classes( $tabs ) {
			$classes = array();

			$classes[] = ( $this->get_last_active_tab() == $tabs['id'] ) ? 'nav-tab-active' : '';

			return implode( ' ', array_unique( apply_filters( 'get_options_tab_css_classes', $classes ) ) );
		}
		private function do_settings_sections( $page ) {
			global $wp_settings_sections, $wp_settings_fields;

			if ( ! isset( $wp_settings_sections[ $page ] ) ) {
				return;
			}

			foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
				if ( $section['title'] ) {
					echo "<h2>".esc_html($section['title'])."</h2>";	
				}

				if ( $section['callback'] ) {
					call_user_func( $section['callback'], $section );
				}

				if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
					continue;
				}

				echo '<table class="form-table">';
				$this->do_settings_fields( $page, $section['id'] );
				echo '</table>';
			}
		}

		private function do_settings_fields( $page, $section ) {
			global $wp_settings_fields;

			if ( ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
				return;
			}

			foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
				
				$custom_attributes = $this->array2html_attr( isset( $field['args']['attributes'] ) ? $field['args']['attributes'] : array() );

				$wrapper_id = ! empty( $field['args']['id'] ) ? esc_attr( $field['args']['id'] ) . '-wrapper' : '';
				$dependency = ! empty( $field['args']['require'] ) ? $this->build_dependency( $field['args']['require'] ) : '';

				printf( '<tr id="%s" %s %s>', $wrapper_id, $custom_attributes, $dependency );

				if ( isset( $field['args']['pro'] ) ) {
					echo '<td colspan="2" style="padding: 0; margin: 0">';
					$this->pro_field_callback( $field['args'] );
					echo '</td>';
			  	}	elseif ( isset( $field['args']['usefull'] ) ) {
					echo '<td colspan="2" style="padding: 0; margin: 0">';
					$this->usefullplugin_field_callback( $field['args'] );
					echo '</td>';
			  	} else {
					echo '<th scope="row" class="thvs-settings-label">';
					if ( ! empty( $field['args']['label_for'] ) ) {
						echo '<label for="' . esc_attr( $field['args']['label_for'] ) . '">' . esc_html($field['title']). '</label>';
					} else {
						echo esc_html($field['title']);
					}

					echo '</th>';
					echo '<td class="thvs-settings-field-content">';
					call_user_func( $field['callback'], $field['args'] );
					echo '</td>';
				}
				echo '</tr>';
			}
		}

		 public function array2html_attr( $attributes, $do_not_add = array() ) {

			$attributes = wp_parse_args( $attributes, array() );

			if ( ! empty( $do_not_add ) and is_array( $do_not_add ) ) {
				foreach ( $do_not_add as $att_name ) {
					unset( $attributes[ $att_name ] );
				}
			}


			$attributes_array = array();

			foreach ( $attributes as $key => $value ) {

				if ( is_bool( $attributes[ $key ] ) and $attributes[ $key ] === true ) {
					return $attributes[ $key ] ? $key : '';
				} elseif ( is_bool( $attributes[ $key ] ) and $attributes[ $key ] === false ) {
					$attributes_array[] = '';
				} else {
					$attributes_array[] = $key . '="' . esc_attr($value) . '"';
				}
			}

			return implode( ' ', $attributes_array );
		}
      private function build_dependency( $require_array ) {
			$b_array = array();
			foreach ( $require_array as $k => $v ) {
				$b_array[ '#' . $k . '-field' ] = $v;
			}

			return 'data-thvsdepends="[' . esc_attr( wp_json_encode( $b_array ) ) . ']"';
		}
		
	
      public function make_implode_html_attributes( $attributes, $except = array( 'type', 'id', 'name', 'value' ) ) {
			$attrs = array();
			foreach ( $attributes as $name => $value ) {
				if ( in_array( $name, $except, true ) ) {
					continue;
				}
				$attrs[] = esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
			}

			return implode( ' ', array_unique( $attrs ) );
		}
		public function get_option( $option ) {
			$default = $this->get_default( $option );
			$options = get_option( $this->settings_name );
			$is_new = ( ! is_array( $options ) && is_bool( $options ) );

			// Theme Support
			if ( current_theme_supports( $this->theme_feature_name ) ) {
				$theme_support    = get_theme_support( $this->theme_feature_name );
				$default['value'] = isset( $theme_support[0][ $option ] ) ? $theme_support[0][ $option ] : $default['value'];
			}

			$default_value = isset( $default['value'] ) ? $default['value'] : null;

			if ( ! is_null( $this->get_reserved( $option ) ) ) {
				$default_value = $this->get_reserved( $option );
			}

			if ( $is_new ) {
			
				return $default_value;
			} else {
			
				return isset( $options[ $option ] ) ? $options[ $option ] : $default_value;
			}
		}

		public function get_options(){
			return get_option( $this->settings_name );
		}

		public function get_reserved( $key = false ){

			$data = (array) get_option( $this->reserved_key );
			if ( $key ) {
				return isset( $data[ $key ] ) ? $data[ $key ] : null;
			} else {
				return $data;
			}
		}

		public function save_reserved( $value ) {
			$reserved_data = array();
			foreach ( (array) $this->reserved_fields as $fieldKey ) {
				if ( ! empty( $value[ $fieldKey ] ) ) {
					$reserved_data[ $fieldKey ] = $value[ $fieldKey ];
				}
			}

			if ( ! empty( $reserved_data ) ) {
				update_option( $this->reserved_key, $reserved_data );
			} else {
				delete_option( $this->reserved_key );
			}
		}
	
    /***************/
		// Field call back function
		/***************/

		public function field_callback( $field ) {

			switch ( $field['type'] ) {
				case 'radio':
					$this->radio_field_callback( $field );
					break;

				case 'checkbox':
					$this->checkbox_field_callback( $field );
					break;

				case 'select':
					$this->select_field_callback( $field );
					break;

				case 'number':
					$this->number_field_callback( $field );
					break;

				case 'color':
					$this->color_field_callback( $field );
					break;

				case 'post_select':
					$this->post_select_field_callback( $field );
					break;

				case 'pro':
					$this->pro_field_callback( $field );
					break;

				case 'usefullplugin':
					$this->usefullplugin_field_callback( $field );
					break;	

				case 'iframe':
					$this->iframe_field_callback( $field );
					break;

				default:
					$this->text_field_callback( $field );
					break;
			}
			do_action( 'thvs_settings_field_callback', $field );
		}

      public function checkbox_field_callback( $args ) {
               
			$value = wc_string_to_bool( $this->get_option( $args['id'] ) );

			$attrs = isset( $args['attrs'] ) ? $this->make_implode_html_attributes( $args['attrs'] ) : '';

			$html = sprintf( '<fieldset><label><input %1$s type="checkbox" id="%2$s-field" name="%4$s[%2$s]" value="%3$s" %5$s/> %6$s</label> %7$s</fieldset>', $attrs, $args['id'], true, $this->settings_name, checked( $value, true, false ), esc_attr( $args['desc'] ), $this->get_field_description( $args ) );

			echo $html;
		}
			public function radio_field_callback( $args ) {
		
			$options = apply_filters( "thvs_settings_{$args[ 'id' ]}_radio_options", $args['options'] );
			$value   = esc_attr( $this->get_option( $args['id'] ) );

			$attrs = isset( $args['attrs'] ) ? $this->make_implode_html_attributes( $args['attrs'] ) : '';


			$html = '<fieldset>';
			$html .= implode( '<br />', array_map( function ( $key, $option ) use ( $attrs, $args, $value ) {
				return sprintf( '<label><input %1$s type="radio"  name="%4$s[%2$s]" value="%3$s" %5$s/> %6$s</label>', $attrs, $args['id'], $key, $this->settings_name, checked( $value, $key, false ), $option );
			}, array_keys( $options ), $options ) );
			$html .= $this->get_field_description( $args );
			$html .= '</fieldset>';

			echo $html;
		}
		public function select_field_callback( $args ) {
			$options = apply_filters( "thvs_settings_{$args[ 'id' ]}_select_options", $args['options'] );
			$value   = esc_attr( $this->get_option( $args['id'] ) );
			$options = array_map( function ( $key, $option ) use ( $value ) {
				return "<option value='{$key}'" . selected( $key, $value, false ) . ">{$option}</option>";
			}, array_keys( $options ), $options );
			$size    = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

			$attrs = isset( $args['attrs'] ) ? $this->make_implode_html_attributes( $args['attrs'] ) : '';

			$html = sprintf( '<select %5$s class="%1$s-text" id="%2$s-field" name="%4$s[%2$s]">%3$s</select>', $size, $args['id'], implode( '', $options ), $this->settings_name, $attrs );
			$html .= $this->get_field_description( $args );

			echo $html;
		}
		public function get_field_description( $args ) {

			$desc = '';
			

			if ( ! empty( $args['desc'] ) ) {
				$desc .= sprintf( '<p class="description">%s</p>', $args['desc'] );
			} else {
				$desc .= '';
			}

			return ( ( $args['type'] === 'checkbox' ) ) ? '' : $desc;
		}
		public function post_select_field_callback( $args ) {

			$options = apply_filters( "thvs_settings_{$args[ 'id' ]}_post_select_options", $args['options'] );

			$value = esc_attr( $this->get_option( $args['id'] ) );

			$options = array_map( function ( $option ) use ( $value ) {
				return "<option value='{$option->ID}'" . selected( $option->ID, $value, false ) . ">$option->post_title</option>";
			}, $options );

			$size = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$html = sprintf( '<select class="%1$s-text" id="%2$s-field" name="%4$s[%2$s]">%3$s</select>', $size, $args['id'], implode( '', $options ), $this->settings_name );
			$html .= $this->get_field_description( $args );
			echo $html;
		}

		public function text_field_callback( $args ) {
			$value = esc_attr( $this->get_option( $args['id'] ) );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

			$attrs = isset( $args['attrs'] ) ? $this->make_implode_html_attributes( $args['attrs'] ) : '';

			$html = sprintf( '<input %5$s type="text" class="%1$s-text" id="%2$s-field" name="%4$s[%2$s]" value="%3$s"/>', $size, $args['id'], $value, $this->settings_name, $attrs );
			$html .= $this->get_field_description( $args );

			echo $html;
		}

		public function pro_field_callback( $args ) {

			$is_html = isset( $args['html'] );

			if ( $is_html ) {
				$html = $args['html'];
			} else {
				$image1 = esc_url( $args['screen_shot1'] );
				$image2 = esc_url( $args['screen_shot2'] );
				$image3 = esc_url( $args['screen_shot3'] );
				$image4 = esc_url( $args['screen_shot4'] );
				$image5 = esc_url( $args['screen_shot5'] );
				$image6 = esc_url( $args['screen_shot6'] );
				$link1  = $args['link1'];
				$link2  = $args['link2'];
				$width = isset( $args['width'] ) ? $args['width'] : '70%';
				$html = sprintf( '<a target="_blank" href="%s"><img style="width: %s" src="%s" /></a>', $link1, $width, $image1 );
				$html .= sprintf( '<a target="_blank" href="%s"><img style="width: %s" src="%s" /></a>', $link1, $width, $image2 );
				$html .= sprintf( '<a target="_blank" href="%s"><img style="width: %s" src="%s" /></a>', $link1, $width, $image3 );
				$html .= sprintf( '<a target="_blank" href="%s"><img style="width: %s" src="%s" /></a>', $link1, $width, $image4 );
				$html .= sprintf( '<a target="_blank" href="%s"><img style="width: %s" src="%s" /></a>', $link1, $width, $image5 );
				$html .= sprintf( '<a target="_blank" href="%s"><img style="width: %s" src="%s" /></a>', $link1, $width, $image6 );
				$html .= sprintf( '<a class="pro-button" target="_blank" href="%s">MORE DETAIL</a>', $link2 );
				$html .= sprintf( '<a class="pro-button buynow" target="_blank" href="%s">BUY NOW</a>', $link1 );
				
				$html .= $this->get_field_description( $args );
			}


			echo $html;
		}

		public function usefullplugin_field_callback( $args ) {

			$is_html = isset( $args['html'] );

			if ( $is_html ) {
				$html = $args['html'];
			  } else {
				$plugin_image  = esc_url( $args['plugin_image'] );
				$plugin_title  = $args['plugin_title'];
				$plugin_link   = $args['plugin_link'];

				$html = sprintf( '<div class="thvs-use-plugin"><img src="%s" /><a target="_blank" href="%s">%s</a></div>', $plugin_image, $plugin_link, $plugin_title);
				
			}


			echo $html;
		}

		public function iframe_field_callback( $args ) {
			$is_html = isset( $args['html'] );
			if ( $is_html ){
				$html = $args['html'];
			  } else {
				$screen_frame = esc_url( $args['screen_frame'] );
        $doc_link     = esc_url( $args['doc_link'] );
        $doc_text     = esc_html($args['doc-texti']);
				$width        = isset( $args['width'] ) ? $args['width'] : '100%';
				$height       = isset( $args['height'] ) ? $args['height'] : '100%';

        $html = sprintf( '<iframe width="%1s" height="%2s" src="%3s" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe><a target="_blank" href="%4s">%5s</a>',  $width, $height, $screen_frame ,$doc_link, $doc_text);

				$html .= $this->get_field_description( $args );
			}
			echo $html;
		}

		public function color_field_callback( $args ){
			$value = esc_attr( $this->get_option( $args['id'] ) );
			
			$alpha = isset( $args['alpha'] ) && $args['alpha'] === true ? ' data-alpha="true"' : '';
			$html  = sprintf( '<input type="text" %1$s class="thvs-color-picker" id="%2$s-field" name="%4$s[%2$s]" value="%3$s"  data-default-color="%3$s" />', $alpha, $args['id'], $value, $this->settings_name );
			$html  .= $this->get_field_description( $args );

			echo $html;
		}
		public function number_field_callback( $args ) {
			$value = esc_attr( $this->get_option( $args['id'] ) );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'small';

			$min    = isset( $args['min'] ) && ! is_null( $args['min'] ) ? 'min="' . $args['min'] . '"' : '';
			$max    = isset( $args['max'] ) && ! is_null( $args['max'] ) ? 'max="' . $args['max'] . '"' : '';
			$step   = isset( $args['step'] ) && ! is_null( $args['step'] ) ? 'step="' . $args['step'] . '"' : '';
			$suffix = isset( $args['suffix'] ) && ! is_null( $args['suffix'] ) ? ' <span>' . $args['suffix'] . '</span>' : '';

			$attrs = isset( $args['attrs'] ) ? $this->make_implode_html_attributes( $args['attrs'] ) : '';


			$html = sprintf( '<input %9$s type="number" class="%1$s-text" id="%2$s-field" name="%4$s[%2$s]" value="%3$s" %5$s %6$s %7$s /> %8$s', $size, $args['id'], $value, $this->settings_name, $min, $max, $step, $suffix, $attrs );
			$html .= $this->get_field_description( $args );

			echo $html;
		}

		public function script_enqueue(){
				if (isset($_GET['page']) && $_GET['page'] == 'th-variation-swatches') {

				wp_enqueue_media();
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_style( 'th-variation-swatches-admin', TH_VARIATION_SWATCHES_PLUGIN_URI. '/assets/css/admin.css', array(), TH_VARIATION_SWATCHES_VERSION );
				
				wp_enqueue_script( 'wp-color-picker-alpha', TH_VARIATION_SWATCHES_PLUGIN_URI. '/assets/js/wp-color-picker-alpha.js', array('wp-color-picker'),true);
				wp_enqueue_script( 'wp-color-picker-alpha' );
				wp_enqueue_script( 'thvs-setting-script', TH_VARIATION_SWATCHES_PLUGIN_URI. '/assets/js/thvs-setting.js', array('jquery'),true);
				wp_localize_script(
					'thvs-setting-script', 'THVSPluginObject', array(
						'media_title'   => esc_html__( 'Choose an Image', 'th-variation-swatches' ),
						'button_title'  => esc_html__( 'Use Image', 'th-variation-swatches' ),
						'add_media'     => esc_html__( 'Add Media', 'th-variation-swatches' ),
						'ajaxurl'       => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
						'nonce'         => wp_create_nonce( 'thvs_plugin_nonce' ),
					)
				);
			}
		}
  }

endif;