<?php
/**
 * Single Quote Template
 *
 * The Single Quote Portal Page 
 *
 * @author 		ZeroBSCRM
 * @package 	Templates/Portal/Quote
 * @see			https://kb.jetpackcrm.com/
 * @version     3.0
 * 
 */



if ( ! defined( 'ABSPATH' ) ) exit; // Don't allow direct access
do_action( 'zbs_enqueue_scripts_and_styles' );
//zeroBS_portal_enqueue_stuff();

	//not used anymore? .. leave in just in case. remove later.
	$queryStr = get_query_var( 'clients' );
	#} Break it up if / present
	if (strpos($queryStr,'/'))
		$zbsPortalRequest = explode('/',$queryStr);
	else
		#} no / in it, so must just be a 1 worder like "invoices", here just jam in array so it matches prev exploded req.
		$zbsPortalRequest = array($queryStr);

	// ! end not used

	//moved into func
    if(function_exists('zeroBSCRM_clientPortalgetEndpoint')){
        $quote_endpoint = zeroBSCRM_clientPortalgetEndpoint('quotes');
    }else{
        $quote_endpoint = 'quotes';
    }

	$quoteID = get_query_var( $quote_endpoint );

?>
<style>
.zerobs-proposal-body{
    font-size: 16px;
    background: #FFFFFF;
    box-shadow: 0px 1px 2px 0 rgba(34,36,38,0.15);
    margin: 1rem 0em;
    padding: 20px;
    border-radius: 0.28571429rem;
    border: 1px solid rgba(34,36,38,0.15);
    margin-top: -32px;
}
.zerobs-proposal-body li, .zerobs-proposal-body li span{
	padding:5px;
	line-height: 18px;
}
.zerobs-proposal-body table td, table tbody th {
    border: 1px solid #ddd;
    padding: 8px;
    font-size: 16px;
}
.zerobs-proposal-body ul{
	padding-left:20px;
}
</style>


<div id="zbs-main" class="zbs-site-main">
	<div class="zbs-client-portal-wrap main site-main zbs-post zbs-hentry">
	<?php
	zeroBS_portalnav($quote_endpoint);
	?>
	<div class='zbs-portal-wrapper zbs-portal-quote-single'>


	<?php
		//in the portal, so can get the current user ID...
		$uid = get_current_user_id();
		$uinfo = get_userdata( $uid );
		$cID = zeroBS_getCustomerIDWithEmail($uinfo->user_email);
		if(!$cID){
			$cID = -999;
		}


		// WH DAL3 note: this is a bit funky naming.
		// was: $aID = zeroBSCRM_quote_canView($cID, (int)$quoteID);
		// ... but changed to $contactOwnerID and zeroBSCRM_quote_getContactAssigned()
		// ... because that's actually what it was checking..
		$contactOwnerID = zeroBSCRM_quote_getContactAssigned((int)$quoteID);

		if ( zeroBSCRM_permsQuotes() ){ ?>
		<div class='wrapper'>
				<?php if($contactOwnerID != $cID){  ?>
				<div class='alert alert-info'>
					Hi, you are viewing this quote in the Client Portal<br />(this message is only shown to admins). 
					<?php ##WLREMOVE ?>
					<br />Learn more about the client portal <a style="color:orange;font-size:18px;" href="<?php echo $zbs->urls['kbclientportal']; ?>" target="_blank">here</a>
					<?php ##/WLREMOVE ?>
				</div>
				<?php zeroBSCRM_portal_adminMsg(); ?>
				<?php } ?>
				<div class='zbs-portal-wrapper-sin zbs-single-quote-portal' style="padding:20px;">
					<?php
					echo jpcrm_portal_single_quote($quoteID);
					?>
				</div>
			<?php	}else  if($contactOwnerID != $cID){
				echo '<div class="zbs-alert-danger">' . __("<b>Error:</b> You are not allowed to view this Quote","zero-bs-crm") . '</div>';
			}else{ ?>
				<div class='zbs-portal-wrapper-sin zbs-single-invoice-portal'>
					<?php
					echo jpcrm_portal_single_quote($quoteID);
					?>
				</div>
			<?php	}  ?>
		<div style="clear:both"></div>
		<?php zeroBSCRM_portalFooter(); ?>
		</div>
	</div>
</div>