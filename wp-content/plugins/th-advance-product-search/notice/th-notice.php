<?php

if (!defined('ABSPATH')){
    
    exit;
}

include_once ABSPATH . 'wp-admin/includes/plugin.php';

if (!is_plugin_active('th-advance-product-search-pro/th-advance-product-search-pro.php' ) && ! class_exists( 'TH_Advance_Product_Search_Notice' )){

class TH_Advance_Product_Search_Notice{

    function __construct(){

        if(isset($_GET['ntc-srch-disable']) && $_GET['ntc-srch-disable'] == true){
        add_action('admin_init', array($this,'th_advance_product_search_notice_set_cookie'));
        }

        if(!isset($_COOKIE['thntc_srch_time'])) {
        add_action( 'admin_enqueue_scripts', array($this,'th_advance_product_search_admin_enqueue_style') );
        add_action( 'admin_notices', array($this,'th_advance_product_search_admin_notice' ));
        }
        
        if(isset($_COOKIE['thntc_srch_time'])) {
            add_action( 'admin_notices', array($this,'th_advance_product_search_notice_unset_cookie'));
        }


        
    }

    function th_advance_product_search_admin_enqueue_style(){

         wp_enqueue_style( 'th-advance-product-search-notice-style', TH_ADVANCE_PRODUCT_SEARCH_PLUGIN_URI.'notice/css/th-notice.css', array(), '1.0.0' );

    } 

    function th_advance_product_search_admin_notice() { 
          $display = isset($_GET['ntc-srch-disable'])?'none':'block';
        ?>

    <div class="th-advance-product-search-notice notice" style="display:<?php echo $display; ?>;">
        <div class="th-advance-product-search-notice-wrap">
            <div class="th-advance-product-search-notice-image"><img src="<?php echo esc_url( TH_ADVANCE_PRODUCT_SEARCH_PLUGIN_URI.'notice/img/search-pro.png' );?>" alt="<?php _e('TH Advance Product Search Pro','th-advance-product-search'); ?>"></div>
            <div class="th-advance-product-search-notice-content-wrap">
                <div class="th-advance-product-search-notice-content">
                <p class="th-advance-product-search-heading"><?php _e('Get Effective and Proffessional Search Engine that quickly Leads Your Customer Towards Products they have searched.','th-advance-product-search'); ?></p>
                <p><?php _e('Allow user to search by Category, Tags , Attribute, SKU. Fast and Mobile Responsive Search Engine. You can also Highlight Sale Products or Featured Product in Search Result.','th-advance-product-search'); ?></p>
                </div>
                <a target="_blank" href="<?php echo esc_url('https://themehunk.com/advanced-product-search/');?>" class="upgradetopro-btn"><?php _e('Upgrade To Pro','th-advance-product-search');?> </a>
            </div>
             <a href="?ntc-srch-disable=1"  class="ntc-dismiss dashicons dashicons-dismiss dashicons-dismiss-icon"></a>
        </div>
    </div>

    <?php }

    function th_advance_product_search_notice_set_cookie() { 
 
        $visit_time = date('F j, Y  g:i a');

        $cok_time = time()+(86457*30);
 
        if(!isset($_COOKIE['thntc_srch_time'])) {
 
        // set a cookie for 1 year
        setcookie('thntc_srch_time', $cok_time, time()+(86457*30));
             
        }
 
    }

    function th_advance_product_search_notice_unset_cookie(){

            $visit_time = time();
            $cookie_time = $_COOKIE['thntc_srch_time'];

            if ($cookie_time < $visit_time) {
                setcookie('thntc_srch_time', null, strtotime('-1 day'));
            }
    }

    
}

$obj = New TH_Advance_Product_Search_Notice();

}