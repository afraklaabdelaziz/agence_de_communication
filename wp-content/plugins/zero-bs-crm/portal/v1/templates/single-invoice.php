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
do_action( 'zbs_enqueue_scripts_and_styles' );
//zeroBS_portal_enqueue_stuff();

	//moved into func
    if(function_exists('zeroBSCRM_clientPortalgetEndpoint'))
        $invoice_endpoint = zeroBSCRM_clientPortalgetEndpoint('invoices');
    else
        $invoice_endpoint = 'invoices';
    

    /* old way... v3.0+ potentially uses hashes 
	$invID = get_query_var( $invoice_endpoint );
	//not used anymore? .. leave in just in case. remove later.
	$queryStr = get_query_var( 'clients' );
	#} Break it up if / present
	if (strpos($queryStr,'/'))
		$zbsPortalRequest = explode('/',$queryStr);
	else
		#} no / in it, so must just be a 1 worder like "invoices", here just jam in array so it matches prev exploded req.
		$zbsPortalRequest = array($queryStr);
	*/

	// v3.0 Hashes (or ID)
	$invIDOrHash = sanitize_text_field( get_query_var( $invoice_endpoint ) );
	$invHash = ''; $invID = -1;
	// discern if hash or id
	if (substr($invIDOrHash,0,3) == 'zh-'){
		
		// definitely hash
		$invHash = substr($invIDOrHash,3);

	} else {

		// probably ID
		$invID = (int)$invIDOrHash;

	}

	// settings
	$useHash = zeroBSCRM_getSetting('easyaccesslinks');
	
	//can view is false.
	$canView = false;
	$fullWidth = false;
	$showNav = false;
	$showViewingAsAdmin = false;
	$zbsWarn = '';

	// Using Easy-pay?
	if ($useHash == "1"){

		// ============== Brute force blocking

			// is this request from a blocked source?
			// (if tries to get *5?* hashes which are incorrect, it'll block that IP for 48h)
			if (zeroBSCRM_security_blockRequest('inveasy')){			

				// BLOCKED (this is a nefarious user.)
				$canView = false;
				$showNav = false;


			} else {

				// NOT BLOCKED

		// ============== / Brute force blocking

				// log request (start)
				$requestID = zeroBSCRM_security_logRequest('inveasy',$invHash,$invID);

				// hash okay? blocked?
				$hashOK = zeroBSCRM_invoicing_getFromHash($invHash,-1);

				if ($hashOK['success'] == 1){

					// all checks out 
					// ... this has been accessed via clients/invoices/bF6wj0pGO74eXQpYIQZ

						// log request (fini) (passed)
						zeroBSCRM_security_finiRequest($requestID);

						// prep data
						$invID = $hashOK['data']['ID'];
						$canView = true;
						//need to get customerID from invoiceID
						$cID = -1;
						$fullWidth = true;


				} else {

					// Hash failed.

					// fall back to showing on the page
					// (if has invID + logged in)
					if (is_user_logged_in() && $invID > 0){

						$uid = get_current_user_id();
						$uinfo = get_userdata( $uid );
						$cID = zeroBS_getCustomerIDWithEmail($uinfo->user_email);
						$aID = zeroBSCRM_invoice_canView($cID, (int)$invID);

						if($aID == $cID || zeroBSCRM_permsInvoices()){

							// turned out okay, user can view this inv, irrelevant of being in easypay mode,
							// ... this has been accessed via clients/invoices/123

							// log request (fini) (passed)
							zeroBSCRM_security_finiRequest($requestID);
							$canView = true;
							$showNav = true;

						} else {

							echo 'x'; exit();
							// nope, this user shouldn't be seeing
							$canView = false;

						}

					}
				}

			} // / IF NOT BLOCKED (brute force attempts)

	} else {

		// Normal mode
		// ... this should have been been accessed via clients/invoices/123

		// got inv id at least?		
		if ($invID > 0){
		
			$uid = get_current_user_id();
			$uinfo = get_userdata( $uid );
			$cID = zeroBS_getCustomerIDWithEmail($uinfo->user_email);

			// WH DAL3 note: this is a bit funky naming.
			// was: $aID = zeroBSCRM_invoice_canView($cID, (int)$invID);
			// ... but changed to $contactOwnerID and zeroBSCRM_invoice_getContactAssigned()
			// ... because that's actually what it was checking...
			$contactOwnerID = zeroBSCRM_invoice_getContactAssigned((int)$invID);

			if ($contactOwnerID == $cID || zeroBSCRM_permsInvoices()){
				//we are admin with manage invoice perms can also view
				$canView = true;
			}

			// this shows to admins viewing via portal a "this is on the portal as admin" msg
			if ($contactOwnerID != $cID && zeroBSCRM_permsInvoices()){
				$showViewingAsAdmin = true;
			}

			$portalPage = zeroBSCRM_getSetting('portalpage');
			$portalLink = get_page_link($portalPage);	
			$showNav = true;

		}

	}


	// mikes perma check
	if(isset($_GET['zbsid'])){
			$zbsClientID 	= (int)$_GET['zbsid'];
			$zbsWarn = __("You are using PLAIN permalinks. Please switch to %postname% for the proper Client Portal experience. Some features may not work in plain permalink mode","zero-bs-crm"); 
	}


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
<div id="zbs-main" class="zbs-site-main">
	<div class="zbs-client-portal-wrap main site-main zbs-post zbs-hentry">
		<div class='zbs-portal-wrapper zbs-portal-inv-single <?php if($fullWidth){ echo "fullW"; } ?>'>
			<?php
			if ($showNav){
				zeroBS_portalnav($invoice_endpoint);
			}
			if (!$canView){
				echo '<div class="zbs-alert-danger">' . __("<b>Error:</b> You are not allowed to view this Invoice","zero-bs-crm") . '</div>';
			} else { 

					// if viewing as admin
				 	if($showViewingAsAdmin){  
				 		?><div class='wrapper' style="padding-left:20px;padding-right:20px;padding-bottom:20px;">
							<div class='alert alert-info'>
								<?php _e('You are viewing this invoice in the Client Portal','zero-bs-crm'); ?>
								<br />
								[<?php _e('This message is only shown to admins','zero-bs-crm'); ?>]
								<?php ##WLREMOVE ?>
								<a style="color:orange;font-size:18px;" href="<?php echo $zbs->urls['kbclientportal']; ?>" target="_blank"><?php _e('Learn more about the client portal','zero-bs-crm'); ?></a>
								<?php ##/WLREMOVE ?>
							</div>
							<?php zeroBSCRM_portal_adminMsg(); ?>
							<div style="margin:20px;padding:10px;background:red;color:white;text-align:center;">
								<?php echo $zbsWarn; ?>
							</div>
						</div><?php 
					} ?>

					<div class='zbs-portal-wrapper-sin zbs-single-invoice-portal'>
						<?php jpcrm_portal_single_invoice($invID,$invHash); ?>
					</div>

			<?php }  ?>
			<div style="clear:both"></div>
			<?php zeroBSCRM_portalFooter(); ?>
		</div>
	</div>
</div>