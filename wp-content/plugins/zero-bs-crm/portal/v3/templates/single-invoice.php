<?php
/**
 * Single Invoice Template
 *
 * The single invoice template
 *
 * @author 		ZeroBSCRM
 * @package 	Templates/Portal/Invoice
 * @see			https://kb.jetpackcrm.com/
 * @version     3.0
 * 
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Don't allow direct access

// Enqueuement
do_action( 'zbs_enqueue_scripts_and_styles' );

// get raw id or hash from URL
$obj_id = jpcrm_get_obj_id_from_current_portal_page_url( ZBS_TYPE_INVOICE );

// fail if invalid object or no permissions to view it
if ( !$obj_id ) {
  jpcrm_show_single_obj_error_and_die();
}

// plain permalinks won't work
if(isset($_GET['zbsid'])){
  $zbsWarn = __("You are using PLAIN permalinks. Please switch to %postname% for the proper Client Portal experience. Some features may not work in plain permalink mode","zero-bs-crm"); 
}

$show_nav = ( zeroBSCRM_portalIsUserEnabled() || !jpcrm_portal_access_is_via_hash( ZBS_TYPE_INVOICE ) ) ;
?>
<style>
.stripe-button-el{
  background: none !important;
  border: 0px !important;
  box-shadow: none !important;
}
.zbs-back-to-invoices a:hover{
  text-decoration:none;
}
</style>

<div class="alignwide zbs-site-main zbs-portal-grid<?php echo $show_nav?'':' no-nav' ?>">
  <?php if ( $show_nav ) { ?>
    <nav class="zbs-portal-nav"><?php echo zeroBS_portalnav( jpcrm_get_portal_endpoint( ZBS_TYPE_INVOICE ), false ); ?></nav>
  <?php } ?>
  <div class='zbs-portal-content zbs-portal-invoices-list'>
    <div class='zbs-entry-content zbs-single-invoice-portal' style="position:relative;">
      <?php jpcrm_portal_single_invoice($obj_id, true); ?>
    </div>
  </div>
  <div class="zbs-portal-grid-footer"><?php zeroBSCRM_portalFooter(); ?></div>
</div>
