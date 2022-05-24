<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access.
}
/**
 * Class for adding widgets in nav menu
 */
if ( ! class_exists('ThemeHunk_MegaMenu_Widgets')) {
	class ThemeHunk_MegaMenu_Widgets
	{
		
		function __construct() {
            add_action('init', array($this, 'themehunk_megamenu_register_sidebar'));
		    add_action('wp_ajax_themehunk_megamenu_reorder_items', array($this, 'themehunk_megamenu_reorder_items'));
            add_action('wp_ajax_themehunk_megamenu_save_widget', array($this, 'themehunk_megamenu_save_widget'));
            add_action('wp_ajax_themehunk_megamenu_drag_to_add_widget_item', array($this, 'themehunk_megamenu_drag_to_add_widget_item'));

            add_action('wp_ajax_themehunk_megamenu_delete_row', array($this, 'themehunk_megamenu_delete_row'));
            add_action('wp_ajax_themehunk_megamenu_delete_column', array($this, 'themehunk_megamenu_delete_column'));
            add_action('wp_ajax_themehunk_megamenu_delete_widget', array($this, 'themehunk_megamenu_delete_widget'));
            add_action('wp_ajax_themehunk_megamenu_reorder_row', array($this, 'themehunk_megamenu_reorder_row'));
            add_action('wp_ajax_themehunk_megamenu_reorder_col', array($this, 'themehunk_megamenu_reorder_col'));
            add_action('wp_ajax_themehunk_megamenu_add_grid_row_column', array($this, 'themehunk_megamenu_add_grid_row_column'));
		}

	 /**
     * @return ThemeHunk_MegaMenu_Widgets
     */
        public static function init(){
            $return = new self();
            return $return;
        }

        /**
         * Register sidebar to call it smartly
         */
        public function themehunk_megamenu_register_sidebar() {
            register_sidebar(
                array(
                    'id' => 'mmth',
                    'name' => __("ThemeHunk MegaMenu Widgets", "themehunk-megamenu"),
                    'description'   => __("All the widgets added to submenus using ThemeHunk MegaMenu builder are stored here.", "themehunk-megamenu")
                )
            );
        }

        /**
         * @return bool | array
         *
         * get mmth megamenu sidebar widgets
         */
        public function themehunk_megamenu_get_sidebar_widgets() {
            $widget = wp_get_sidebars_widgets();
            if ( ! isset( $widget[ 'mmth'] ) ) {
                return false;
            }
            return $widget[ 'mmth' ];
        }


        /**
         * @param $widgets_array
         *
         * Set widgets to wp megamenu sidebar
         */
        private function themehunk_megamenu_set_sidebar_widgets( $widgets_array ) {
            $widgets = wp_get_sidebars_widgets();
            $widgets[ 'mmth' ] = $widgets_array;
            wp_set_sidebars_widgets( $widgets );
        }
        
        /**
         * @param $new_widget_id
         * @return mixed
         *
         */
        private function themehunk_megamenu_add_widget_to_sidebar( $new_widget_id ) {
            $new_widgets = $this->themehunk_megamenu_get_sidebar_widgets();
            $new_widgets[] = $new_widget_id;
            $this->themehunk_megamenu_set_sidebar_widgets($new_widgets);
            return $new_widget_id;
        } 

       /**
         * @param $widget_id
         * @return bool
         *
         * Get base widget id
         */
        public function themehunk_megamenu_get_id_base_for_widget_id( $widget_id ) {
            global $wp_registered_widget_controls;

            if ( ! isset( $wp_registered_widget_controls[ $widget_id ] ) ) {
                return false;
            }
            $control = $wp_registered_widget_controls[ $widget_id ];
            $id_base = isset( $control['id_base'] ) ? $control['id_base'] : $control['id'];
            return $id_base;
        }   

        /**
         * @param $widget_id
         * @return bool|string
         */
        public function themehunk_megamenu_get_widget_class_by_widget_id( $widget_id ) {
            global $wp_registered_widget_controls;

            if ( ! isset( $wp_registered_widget_controls[ $widget_id ] ) ) {
                return false;
            }
            $control = $wp_registered_widget_controls[ $widget_id ];

            $widget_class_name = get_class($control['callback'][0]);
            return $widget_class_name;
        }
  

	   /**
	     * @param $menu_item
	     * @param int $widget_key_id
	     *
	     * Menu item show in widget area
	     */

	     public static function themehunk_megamenu_menu_items( $menu_item, $widget_key_id = 0){
            ?>
            <div id="widget-<?php echo $menu_item['ID']; ?>" class="widget"  data-item-key-id="<?php echo $widget_key_id; ?>">
                <div class="widget-top">
                    <div class="widget-title ui-sortable-handle">
                        <h3><?php echo $menu_item['title']; ?></h3>
                    </div>
                </div>
            </div>
            <?php
        }

	    /**
         * @param $widget_id
         * @param $menu_item_id
         *
         *
         * Get widget item in item settings panel
         */
        public static function themehunk_megamenu_widget_items($widget_id, $widget_key_id = 0){   
            global $wp_registered_widget_controls;   
            $control = $wp_registered_widget_controls[$widget_id];
            $nonce = wp_create_nonce('megamenu_save_widget_' . $widget_id);  
            $id_base = isset( $control['id_base'] ) ? $control['id_base'] : $control['id']; 
            ?>
            <div id="widget-<?php echo $widget_id; ?>" class="widget"  data-item-key-id="<?php
            echo $widget_key_id; ?>">
                <div class="widget-top">

                    <div class="widget-title-action">
                        <button type="button" class="widget-action hide-if-no-js widget-form-open" aria-expanded="false">
                            <span class="screen-reader-text"><?php printf( __( 'Edit widget: %s','themehunk-megamenu' ), $control['name'] ); ?></span>
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>

                    </div>
                    <div class="widget-title">
                        <h3><?php echo $control['name']; ?><span class="in-widget-title"></span></h3>
                    </div>
                </div>

                <div class="widget-inner widget-inside">
                    <form method='post'  class="themehunk_megamenu_widget_save_form">
                        <input type="hidden" name="widget-id" class="widget-id" value="<?php echo esc_attr($widget_id) ?>" />
                        <input type='hidden' name='id_base'   class="id_base" value='<?php echo esc_attr($id_base); ?>' />
                        <input type='hidden' name='widget_id' value='<?php echo esc_attr($widget_id) ?>' />
                        <input type='hidden' name='_wpnonce'  value='<?php echo esc_attr($nonce) ?>' />
                        <div class='widget-content'>
                            <?php
                            if ( is_callable( $control['callback'] ) ) {
                                call_user_func_array( $control['callback'], $control['params'] );
                            }
                            ?>

                            <div class='widget-controls'>
                                <a class='delete' href='#delete'><?php _e('Delete', 'themehunk-megamenu'); ?></a> |
                                <a class='close' href='#close'><?php _e('Close', 'themehunk-megamenu'); ?></a>
                            </div>

                            <?php
                                submit_button( __( 'Save' ), 'button-primary alignright', 'savewidget', false );
                            ?>
                            <div class="clear"></div>
                        </div>
                    </form>
                </div>

            </div>
            <?php
        }

        /**
         * @param $widget_id
         * @return bool|string
         */
        public function themehunk_megamenu_get_widget_name_by_widget_id( $widget_id ) {
            global $wp_registered_widget_controls;

            if ( ! isset( $wp_registered_widget_controls[$widget_id] ) ) {
                return false;
            }else{
                return $wp_registered_widget_controls[$widget_id]['name'];
            }
        }

        /**
         * @return bool
         *
         * Save or update a widget data from popup builder
         */
        public function themehunk_megamenu_save_widget(){

            $id_base = sanitize_text_field( $_POST['id_base'] );
            $widget_id = sanitize_text_field( $_POST['widget-id'] );

            global $wp_registered_widget_updates;
            $control = $wp_registered_widget_updates[$id_base];
            if ( is_callable( $control['callback'] ) ) {
                call_user_func_array( $control['callback'], $control['params'] );
                return true;
            }
            wp_send_json_success( ['msg' => __('Widget saved success', 'themehunk-megamenu'), 'id_base' => $id_base ] );
        }

	   /**
	     * get all registere available widget
	     */
	    public static function themehunk_megamenu_get_all_registered_widgets(){
	        global $wp_widget_factory;

	        $widgets = array();
	        foreach( $wp_widget_factory->widgets as $widget ) {
	            $widgets[] = array(
	                'name' => $widget->name,
	                'id_base' => $widget->id_base
	            );
	        }
	        return $widgets;
	    }

        /**
         * @param $id
         * @return string
         *
         * Show a widget html output in the menu on frontend
         */
        public function themehunk_megamenu_show_widget( $id ) {
            global $wp_registered_widgets;
            $params = array_merge(
                array( array_merge( array( 'widget_id' => $id, 'widget_name' => $wp_registered_widgets[$id]['name'] ) ) ),
                (array) $wp_registered_widgets[$id]['params']
            );
            $params[0]['before_title'] = apply_filters( "themehunk_megamenu_before_widget_title", '<h4 class="themehunk-megamenu-item-title">', $wp_registered_widgets[$id] );
            $params[0]['after_title'] = apply_filters( "themehunk_megamenu_after_widget_title", '</h4>', $wp_registered_widgets[$id] );
            $params[0]['before_widget'] = apply_filters( "themehunk_megamenu_before_widget", "", $wp_registered_widgets[$id] );
            $params[0]['after_widget'] = apply_filters( "themehunk_megamenu_after_widget", "", $wp_registered_widgets[$id] );

            $callback = $wp_registered_widgets[$id]['callback'];

            if ( is_callable( $callback ) ) {
                ob_start();
                echo '<div id="'.$wp_registered_widgets[$id]['id'].'" class="navmenu-widget-wrapper" >';
                call_user_func_array( $callback, $params );
                echo '</div >';
                return ob_get_clean();
            }
        }

        /**
         * Reorder items in the widget area
         */
        public function themehunk_megamenu_reorder_items(){
            $menu_item_id = (int) sanitize_text_field($_POST['menu_item_id']);
            $row_id = (int) sanitize_text_field($_POST['row_id']);
            $col_id = (int) sanitize_text_field($_POST['col_id']);

            $item_order = sanitize_text_field($_POST['item_order']);
            $get_layout = get_post_meta($menu_item_id, 'themehunk_megamenu_layout', true);

            $item_order_array = explode(',', $item_order);

            //If move one col to another col
            if ( ! empty($_POST['type'])){
                $type = sanitize_text_field($_POST['type']);
                if ($type === 'connect'){
                    $from_row_id = (int) sanitize_text_field($_POST['from_row_id']);
                    $from_col_id = (int) sanitize_text_field($_POST['from_col_id']);
                    $from_item_index = (int) sanitize_text_field($_POST['from_item_index']);

                    $move_item = $get_layout['layout'][$from_row_id]['row'][$from_col_id]['items'][$from_item_index];

                    if (count($get_layout['layout'][$from_row_id]['row'][$from_col_id]['items']) > 1){
                        unset($get_layout['layout'][$from_row_id]['row'][$from_col_id]['items'][$from_item_index]);
                    }else{
                        unset($get_layout['layout'][$from_row_id]['row'][$from_col_id]['items']);
                    }

                    $all_items = (array) $get_layout['layout'][$row_id]['row'][$col_id]['items'];
                    $all_items[]= $move_item;
                    foreach ($all_items as $key => $item){
                        if (empty($item)){
                            unset($all_items[$key]);
                        }
                    }
                    $get_layout['layout'][$row_id]['row'][$col_id]['items'] = $all_items;

                    //remove empty key from array
                    $update = update_post_meta($menu_item_id, 'themehunk_megamenu_layout', $get_layout );
                    wp_send_json_success( __('Widget item column moved', 'themehunk-megamenu') );
                }
            }else{
                //Else sorting it within own col
                if ( ! empty($get_layout['layout'][$row_id]['row'][$col_id]['items'])) {
                    $item_count = count($get_layout['layout'][$row_id]['row'][$col_id]['items']);
                    //Determine it comes from update, not receive method in sortable
                    if ($item_count == count($item_order_array)){
                        $sorted_item = array();
                        if (count($item_order_array)){
                            for ($i=0; $i<count($item_order_array); $i++){
                                $sorted_item[$item_order_array[$i]] = $get_layout['layout'][$row_id]['row'][$col_id]['items'][$item_order_array[$i]];
                            }
                        }
                        $get_layout['layout'][$row_id]['row'][$col_id]['items'] = $sorted_item;
                        $update = update_post_meta($menu_item_id, 'themehunk_megamenu_layout', $get_layout );
                        wp_send_json_success( __('Widget item column update', 'themehunk-megamenu') );
                    }
                }
            }
        }

        /**
         * Delete Row
         */
        public function themehunk_megamenu_delete_row(){
            check_ajax_referer( 'themehunk_megamenu_check_security', 'themehunk_megamenu_nonce' );

            $menu_item_id   = (int) sanitize_text_field($_POST['menu_item_id']);
            $row_id         = (int) sanitize_text_field($_POST['row_id']);
            $get_layout = maybe_unserialize(get_post_meta($menu_item_id, 'themehunk_megamenu_layout', true));
            if (key_exists($row_id, $get_layout['layout']) ){
                unset($get_layout['layout'][$row_id]);
            }
            update_post_meta($menu_item_id, 'themehunk_megamenu_layout', $get_layout );
            wp_send_json_success( __('Row has been deleted', 'themehunk-megamenu') );
        }


        /**
         * Delete column
         */
        public function themehunk_megamenu_delete_column(){
            check_ajax_referer( 'themehunk_megamenu_check_security', 'themehunk_megamenu_nonce' );

            $menu_item_id   = (int) sanitize_text_field($_POST['menu_item_id']);
            $row_id         = (int) sanitize_text_field($_POST['row_id']);
            $col_id         = (int) sanitize_text_field($_POST['col_id']);
            $get_layout     = maybe_unserialize(get_post_meta($menu_item_id, 'themehunk_megamenu_layout', true));

            if (key_exists( $col_id, $get_layout['layout'][$row_id]['row'] ) ){
                unset( $get_layout['layout'][$row_id]['row'][$col_id] );
            }

            update_post_meta($menu_item_id, 'themehunk_megamenu_layout', $get_layout );

            $updated_data = get_post_meta($menu_item_id, 'themehunk_megamenu_layout', $get_layout );

            wp_send_json_success( [ 'msg' => __('Column has been deleted.', 'themehunk-megamenu') ] );
        }

        /**
         * Reorder row in popup builder
         */
        public function themehunk_megamenu_reorder_row(){
            $rows_order = sanitize_text_field($_POST['rows_order']);
            $rows_order = explode(',', $rows_order);

            $menu_item_id = (int) sanitize_text_field($_POST['menu_item_id']);
            $get_layout = maybe_unserialize(get_post_meta($menu_item_id, 'themehunk_megamenu_layout', true));

            $sorted_items = array();
            $new_order = array();
            if (count($rows_order)){
                foreach ($rows_order as $key => $row_id){
                    $new_order[$key] = $row_id;
                    $sorted_items[$key] = $get_layout['layout'][$row_id];
                }
            }
            $get_layout['layout'] = $sorted_items;
            update_post_meta( $menu_item_id, 'themehunk_megamenu_layout', $get_layout );
            $updated_row_data = get_post_meta($menu_item_id, 'themehunk_megamenu_layout' );
            wp_send_json_success( ['msg' => __('Row updated', 'themehunk-megamenu'), 
                                    'get_layout' => $get_layout,
                                    'rows_order' => $rows_order,
                                    'new_order' => $new_order,
                                    'sorted_items' => $sorted_items,
                                    'updated_row_data' => $updated_row_data
                    ] );
        }

        /**
         * Reorder col in popup builder
         */
        public function themehunk_megamenu_reorder_col(){
            $col_order = sanitize_text_field($_POST['col_order']);
            $col_order = explode(',', $col_order);

            $menu_item_id = (int) sanitize_text_field($_POST['menu_item_id']);
            $row_id = (int) sanitize_text_field($_POST['row_id']);
            $get_layout = maybe_unserialize(get_post_meta($menu_item_id, 'themehunk_megamenu_layout', true));

            $sorted_item = array();
            $new_order = array();
            if (count($col_order)){
                foreach ($col_order as $key => $col_id){
                    $new_order[$key] = $row_id;
                    $sorted_item[$key] = $get_layout['layout'][$row_id]['row'][$col_id];
                }
            }
            $get_layout['layout'][$row_id]['row'] = $sorted_item;
            $update = update_post_meta($menu_item_id, 'themehunk_megamenu_layout', $get_layout );
            $updated_col_data = get_post_meta($menu_item_id, 'themehunk_megamenu_layout' );
            wp_send_json_success( [
                                    'msg' => __('Column updated', 'themehunk-megamenu'), 
                                    'get_layout' => $get_layout,
                                    'col_order' => $col_order,
                                    'new_order' => $new_order,
                                    'sorted_items' => $sorted_items,
                                    'updated_col_data' => $updated_col_data
            ] );
        }        

        /**
         * Add widget by drag and drop
         */
        public function themehunk_megamenu_drag_to_add_widget_item() {
            require_once( ABSPATH . 'wp-admin/includes/widgets.php' );
            $menu_item_id = (int) sanitize_text_field($_POST['menu_item_id']);
            $row_id = (int) sanitize_text_field($_POST['row_id']);
            $col_id = (int) sanitize_text_field($_POST['col_id']);
            $widget_base_id = sanitize_text_field($_POST['widget_base_id']);

            //Add widget
            $next_id = next_widget_id_number( $widget_base_id );
            $widget_id = $widget_base_id.'-'.$next_id;
            $this->themehunk_megamenu_add_widget_to_sidebar($widget_id);

            //get new widget id
            $get_widget_option = get_option('widget_'.$widget_base_id);
            $get_widget_option[$next_id] = array();
            update_option('widget_'.$widget_base_id, $get_widget_option);

            $get_widget_option = get_option('widget_'.$widget_base_id);
            //Settings in item post meta
            $widget_name = $this->themehunk_megamenu_get_widget_name_by_widget_id($widget_id);
            $widget_class = $this->themehunk_megamenu_get_widget_class_by_widget_id($widget_id);
            $get_layout = maybe_unserialize(get_post_meta($menu_item_id, 'themehunk_megamenu_layout', true));

            if ( empty( $get_layout['layout'][$row_id]['row'][$col_id]['items'] ) ) {
                $get_layout['layout'][$row_id]['row'][$col_id]['items'] = [];
                $get_layout['layout'][$row_id]['row'][$col_id]['items'][] = array( 'item_type' => 'widget', 'widget_class' => $widget_class, 'title' => $widget_name, 'widget_name' => $widget_name, 'widget_id' => $widget_id, 'options' => array() ); 
            }else {
                $get_layout['layout'][$row_id]['row'][$col_id]['items'][] = array( 'item_type' => 'widget', 'widget_class' => $widget_class, 'title' => $widget_name, 'widget_name' => $widget_name, 'widget_id' => $widget_id, 'options' => array() ); 
            }

            update_post_meta( $menu_item_id, 'themehunk_megamenu_layout', $get_layout );

            $updated_data = get_post_meta($menu_item_id, 'themehunk_megamenu_layout');

            wp_send_json_success( array('message' => __('Widget added', 'themehunk-megamenu') ) );
        }

        
        // Adds a column to grid row

        public function themehunk_megamenu_add_grid_row_column() {
            check_ajax_referer( 'themehunk_megamenu_check_security', 'themehunk_megamenu_nonce' );
            $menu_item_id = (int) sanitize_text_field($_POST['menu_item_id']);
            $row_id = sanitize_text_field($_POST['row_id']); //current modifying row id.
            $layout_format = sanitize_text_field($_POST['layout_format']); 
            $layout_name = sanitize_text_field($_POST['layout_name']); 

            $old_layout = (array) maybe_unserialize( get_post_meta($menu_item_id, 'themehunk_megamenu_layout', true));
            $layout_explode = explode(',', $layout_format); // (12, [6,6],[4,4,4], [3,3,3,3], [2,2,2,2,2,2] )
            $total_cols = count( $layout_explode ) - 1 ;
            $new_layout = array();
            $condition = true;
            foreach ( $old_layout['layout'] as $row_key => $row_value ) {
                if ( $row_key == $row_id ) {   // We are inside the current modifying row                 
                    for ( $col = 0; $col <= $total_cols; $col++ ) { 
                        foreach( $layout_explode as $col_size ){                     
                            foreach ( $old_layout['layout'][$row_id]['row'] as $col_key => $col_value ) {
                                    $new_layout['layout'][$row_id]['row'][$col]['col'] =  $col_size;
                                    $new_layout['layout'][$row_id]['row'][$col]['items'] = array();
                                    if ( $col === $total_cols ) {
                                        $new_layout['layout'][$row_id]['row'][$col]['items'] =  '';
                                    }else {
                                        $new_layout['layout'][$row_id]['row'][$col]['items'] =  $old_layout['layout'][$row_id]['row'][$col]['items'];
                                     }
                            }
                        }       
                    }
                }else {                
                    $new_layout['layout'][$row_key] =  $old_layout['layout'][$row_key];
                }
            }


            update_post_meta($menu_item_id, 'themehunk_megamenu_layout', $new_layout);

        }


        /**
         *
         * Delete an item from widget area in wp megamenu
         */

        public function themehunk_megamenu_delete_widget(){
            check_ajax_referer( 'themehunk_megamenu_check_security', 'themehunk_megamenu_nonce' );

            $id_base = sanitize_text_field( $_POST['id_base'] );
            $widget_id = sanitize_text_field($_POST['widget_id']);
            $menu_item_id = (int) sanitize_text_field($_POST['menu_item_id']);
            $widget_key_id = (int) sanitize_text_field($_POST['widget_key_id']);

            $row_id = (int) sanitize_text_field($_POST['row_id']);
            $col_id = (int) sanitize_text_field($_POST['col_id']);

            $this->themehunk_megamenu_delete_widget_from_sidebar( $widget_id );
            $this->themehunk_megamenu_delete_widget_from_builder_column( $menu_item_id, $row_id, $col_id, $widget_key_id );
            $this->themehunk_megamenu_delete_widget_from_widget_options_db( $id_base, $widget_id );

       }

        public function themehunk_megamenu_delete_widget_from_sidebar( $widget_id ){
            //Remove from sidebar
            $sidebar_widgets = $this->themehunk_megamenu_get_sidebar_widgets();
            $new_widgets = array();
            foreach ($sidebar_widgets as $key => $value){
                if ( $widget_id != $value ){
                    $new_widgets[] = $value;
                }
            }
            $this->themehunk_megamenu_set_sidebar_widgets($new_widgets);
        }

        public function themehunk_megamenu_delete_widget_from_builder_column( $menu_item_id, $row_id, $col_id, $widget_key_id ){
            //Remove from menu item post meta
            $get_layout = get_post_meta($menu_item_id, 'themehunk_megamenu_layout', true);

            if ( ! empty($get_layout['layout'][$row_id]['row'][$col_id]['items'][$widget_key_id])){
                unset($get_layout['layout'][$row_id]['row'][$col_id]['items'][$widget_key_id]);
            }
            update_post_meta($menu_item_id, 'themehunk_megamenu_layout', $get_layout );
        }


        public function themehunk_megamenu_delete_widget_from_widget_options_db( $id_base, $widget_id ){
            //Remove from option widget_{$widget_base_id}
            $get_widget_option = get_option('widget_'.$id_base);
            preg_match('!\d+!', $widget_id, $id_num);

            unset($get_widget_option[$id_num[0]]);
            update_option('widget_'.$id_base, $get_widget_option);
        }

	} // Class Ends Here

	ThemeHunk_MegaMenu_Widgets::init();


}	