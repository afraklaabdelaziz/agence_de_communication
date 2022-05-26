<?php
/**
 * Invoice List Page
 *
 * The list of Invoices for the Portal 
 *
 * @author 		ZeroBSCRM
 * @package 	Templates/Portal/Invoices
 * @see			https://jetpackcrm.com/kb/
 * @version     3.0
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Don't allow direct access

do_action( 'zbs_enqueue_scripts_and_styles' );
//zeroBS_portal_enqueue_stuff();

$ZBSuseInvoices = zeroBSCRM_getSetting('feat_invs');

if($ZBSuseInvoices < 0){
        status_header( 404 );
        nocache_headers();
        include( get_query_template( '404' ) );
        die();
}


$portalLink = zeroBS_portal_link();
$invoice_endpoint = jpcrm_get_portal_endpoint( ZBS_TYPE_INVOICE );

?>
<style>
.zbs-portal-invoices-list .paid {
    background: green;
    color: white;
    font-weight: 700;
    line-height: 35px;
    border-radius: 0px !important;
}
</style>
<div class="alignwide zbs-site-main zbs-portal-grid">
    <nav class="zbs-portal-nav">
		<?php zeroBS_portalnav($invoice_endpoint); ?>
    </nav>
    <div class='zbs-portal-content zbs-portal-invoices-list'>
        <h2><?php _e('Invoices','zero-bs-crm'); ?></h2>
        <div class='zbs-entry-content zbs-responsive-table' style="position:relative;">
		    <?php zeroBSCRM_portal_list_invoices($portalLink, $invoice_endpoint); ?>
        </div>
    </div>
    <div class="zbs-portal-grid-footer"><?php zeroBSCRM_portalFooter(); ?></div>
</div>
