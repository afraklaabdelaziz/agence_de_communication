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
$invoice_endpoint = zeroBSCRM_portal_get_invoice_endpoint();

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
<div id="zbs-main" class="zbs-site-main">
	<div class="zbs-client-portal-wrap main site-main zbs-post zbs-hentry">
		<?php zeroBS_portalnav($invoice_endpoint); ?>
		<div class='zbs-portal-wrapper zbs-portal-invoices-list'>
		<?php zeroBSCRM_portal_list_invoices($portalLink, $invoice_endpoint); ?>
			<div style="clear:both"></div>
			<?php zeroBSCRM_portalFooter(); ?>
		</div>
	</div>
</div>