<?php

if (!defined('ABSPATH')){
    exit;
}

include_once ABSPATH . 'wp-admin/includes/plugin.php';


if ( ! class_exists( 'TH_Variation_Swatches_Notice' ) && !is_plugin_active( 'th-variation-swatches-pro/th-variation-swatches-pro.php' )){

class TH_Variation_Swatches_Notice{

    function __construct(){

        if(isset($_GET['ntc-vrtn-disable']) && $_GET['ntc-vrtn-disable'] == true){
        add_action('admin_init', array($this,'th_variation_swatches_notice_set_cookie'));
        }

        if(!isset($_COOKIE['thntc_vrtn_time'])){
        add_action( 'admin_enqueue_scripts', array($this,'th_variation_swatches_admin_enqueue_style') );
        add_action( 'admin_notices', array($this,'th_variation_swatches_admin_notice' ));
        }

        if(isset($_COOKIE['thntc_vrtn_time'])){
             add_action( 'admin_notices', array($this,'th_variation_swatches_notice_unset_cookie'));
        }

    }

    function th_variation_swatches_admin_enqueue_style(){

         wp_enqueue_style( 'th-variation-swatches-notice-style', TH_VARIATION_SWATCHES_PLUGIN_URI.'notice/css/th-notice.css', array(), '1.0.0' );

    } 

    function th_variation_swatches_admin_notice() { 
      $display = isset($_GET['ntc-vrtn-disable'])?'none':'block';
    ?>

    <div class="th-variation-swatches-notice notice" style="display:<?php echo $display; ?>;">
        <div class="th-variation-swatches-notice-wrap">
            <div class="th-variation-swatches-notice-image"><img src="<?php echo esc_url( TH_VARIATION_SWATCHES_PLUGIN_URI.'notice/img/swatches-pro.png');?>" alt="<?php _e('TH Variation Swatches Pro','th-variation-swatches'); ?>"></div>
            <div class="th-variation-swatches-notice-content-wrap">
                <div class="th-variation-swatches-notice-content">
                <p class="th-variation-swatches-heading"><?php _e('Use woo swatches premium and display product attributes in an attractive and professional way.','th-variation-swatches'); ?></p>
                <p><?php _e('Premium version of swatches allow you to display styled attributes at the product page. This will definitely improve user experience and increase your sales.','th-variation-swatches'); ?></p>
                </div>
                <a target="_blank" href="<?php echo esc_url('https://themehunk.com/th-variation-swatches/');?>" class="upgradetopro-btn"><?php _e('Upgrade To Pro','th-variation-swatches');?> </a>
            </div>
            <a href="?ntc-vrtn-disable=1"  class="ntc-dismiss dashicons dashicons-dismiss dashicons-dismiss-icon"></a>
        </div>
    </div>

    <?php }

    function th_variation_swatches_notice_set_cookie() { 
 
        $visit_time = date('F j, Y  g:i a');

        $cok_time = time()+(86457*30);
 
        if(!isset($_COOKIE['thntc_vrtn_time'])) {
 
        // set a cookie for 1 year
        setcookie('thntc_vrtn_time', $cok_time, time()+(86457*30));
             
        }
 
    }

    function th_variation_swatches_notice_unset_cookie(){

            $visit_time = time();
            $cookie_time = $_COOKIE['thntc_vrtn_time'];

            if ($cookie_time < $visit_time) {
                setcookie('thntc_vrtn_time', null, strtotime('-1 day'));
            }
    }

    
}

$obj = New TH_Variation_Swatches_Notice();

}