<?php

if (!defined('ABSPATH')){

    exit;
    
}

if (!class_exists('Taiowc_Pro') && !class_exists('Taiowcp_Main') && !class_exists( 'Taiowc_Notice' )){

class Taiowc_Notice{

    function __construct(){

        if(isset($_GET['ntc-taiowc-disable']) && $_GET['ntc-taiowc-disable'] == true){
        add_action('admin_init', array($this,'taiowc_notice_set_cookie'));
        }

        if(!isset($_COOKIE['thntc_taiowc_time'])) {
        add_action( 'admin_enqueue_scripts', array($this,'taiowc_admin_enqueue_style') );
        add_action( 'admin_notices', array($this,'taiowc_admin_notice' ));
        }

        if(isset($_COOKIE['thntc_taiowc_time'])) {
            add_action( 'admin_notices', array($this,'taiowc_notice_unset_cookie'));
        }

        
    }

    function taiowc_admin_enqueue_style(){

         wp_enqueue_style( 'taiowc-notice-style', TAIOWC_PLUGIN_URI.'notice/css/th-notice.css', array(), '1.0.0' );

    } 

    function taiowc_admin_notice() {  

      $display = isset($_GET['ntc-taiowc-disable'])?'none':'block';

    ?>

      <div class="taiowc-notice notice" style="display:<?php echo $display; ?>;">
        <div class="taiowc-notice-wrap">
            <div class="taiowc-notice-image"><img src="<?php echo esc_url( TAIOWC_PLUGIN_URI.'notice/img/cart-pro.png' );?>" alt="<?php _e('TH All In One Woo Cart Pro','taiowc'); ?>"></div>
            <div class="taiowc-notice-content-wrap">
                <div class="taiowc-notice-content">
                <p class="taiowc-heading"><?php _e('Increase your Woocommerce Store Usability With Th Menu Cart & Side Cart Premium','taiowc'); ?></p>
                <p><?php _e('Add Cross Sell , Related and Upsell products in the Cart to encourage the customer to add more products to the cart. You can also offer Coupon code in Cart','taiowc'); ?></p>
                </div>
                <a target="_blank" href="<?php echo esc_url('https://themehunk.com/th-all-in-one-woo-cart/');?>" class="upgradetopro-btn"><?php _e('Upgrade To Pro','taiowc');?> </a>

            </div>
             <a href="?ntc-taiowc-disable=1"  class="ntc-dismiss dashicons dashicons-dismiss dashicons-dismiss-icon"></a>
        </div>
    </div>

    <?php }


    function taiowc_notice_set_cookie() { 
 
        $visit_time = date('F j, Y  g:i a');

        $cok_time = time()+(86457*30);
 
        if(!isset($_COOKIE['thntc_taiowc_time'])) {
 
        // set a cookie for 1 year
        setcookie('thntc_taiowc_time', $cok_time, time()+(86457*30));
             
        }
 
    }

    function taiowc_notice_unset_cookie(){

            $visit_time = time();
            $cookie_time = $_COOKIE['thntc_taiowc_time'];

            if ($cookie_time < $visit_time) {
                setcookie('thntc_taiowc_time', null, strtotime('-1 day'));
            }
    }


    
}

$obj = New Taiowc_Notice();

 }