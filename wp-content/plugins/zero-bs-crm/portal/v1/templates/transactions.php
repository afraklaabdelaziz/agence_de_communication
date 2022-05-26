<?php
/**
 * Transaction List
 *
 * The list of transactions made by a user (all statuses)
 *
 * @author 		ZeroBSCRM
 * @package 	Templates/Portal/Transactions
 * @see			https://jetpackcrm.com/kb/
 * @version     3.0
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Don't allow direct access

#} changed to this, so if people want to re-style then can remove_action
do_action( 'zbs_enqueue_scripts_and_styles' );
//zeroBS_portal_enqueue_stuff();
?>
<div id="zbs-main" class="zbs-site-main">
	<div class="zbs-client-portal-wrap main site-main zbs-post zbs-hentry">
	<?php
	//moved into func
    if(function_exists('zeroBSCRM_clientPortalgetEndpoint')){
        $tran_endpoint = zeroBSCRM_clientPortalgetEndpoint('transactions');
    }else{
        $tran_endpoint = 'transactions';
    }
	zeroBS_portalnav($tran_endpoint);
	?>
	<div class='zbs-portal-wrapper'>
		<?php
			global $zbs;
			$uid = get_current_user_id();
			$uinfo = get_userdata( $uid );
			$cID = zeroBS_getCustomerIDWithEmail($uinfo->user_email);

			$customer_transactions = zeroBS_getTransactionsForCustomer($cID,true,100,0,false);

			// preview msg		
			zeroBSCRM_portal_adminPreviewMsg($cID,'margin-bottom:1em');

			// admin msg (upsell cpp) (checks perms itself, safe to run)
			zeroBSCRM_portal_adminMsg();

			if(count($customer_transactions) > 0){
				echo '<table class="table">';

				echo '<th>' . __('Transaction',"zero-bs-crm") . '</th>';
				echo '<th>' . __('Transaction Date',"zero-bs-crm") . '</th>';
				echo '<th>' . __('Title',"zero-bs-crm") . '</th>';
				echo '<th>' . __('Total',"zero-bs-crm") . '</th>';
				
				foreach($customer_transactions as $ctrans){

					$tran_date = '';					
					if (isset($ctrans['created']) && !empty($ctrans['created'])){
						$dateUTS 	= strtotime($ctrans['created']);
						$tran_date 	= zeroBSCRM_date_i18n(get_option('date_format'),$dateUTS,false,true);
					}else{
						$tran_date = __("No date", "zero-bs-crm");
					}


					
					$tranAmount = 'TBC';
					if (isset($ctrans['meta']) && is_array($ctrans['meta']) && isset($ctrans['meta']['total'])) $tranAmount = zeroBSCRM_formatCurrency($ctrans['meta']['total']);// old $tranAmount = number_format($ctrans['meta']['total'],2);
					$tranItem = '';
					if (isset($ctrans['meta']) && is_array($ctrans['meta']) && isset($ctrans['meta']['item'])) $tranItem = $ctrans['meta']['item'];	
					$orderID = '';
					if (isset($ctrans['meta']) && is_array($ctrans['meta']) && isset($ctrans['meta']['orderid'])) $orderID = $ctrans['meta']['orderid'];	

					echo '<tr>';
						echo '<td>' . $orderID . '</td>';
						echo '<td>' . $tran_date . '</td>';
						echo '<td><span class="name">'.$tranItem.'</span></td>';
						echo '<td>' . $tranAmount . '</td>';
					//	echo '<td class="tools"><a href="account/invoices/274119/pdf" class="pdf_download" target="_blank"><i class="fa fa-file-pdf-o"></i></a></td>';
					echo '</tr>';
				}
				echo '</table>';
			}else{
				_e('You do not have any transactions yet.',"zero-bs-crm"); 
			}
			?>
		<div style="clear:both"></div>
		<?php zeroBSCRM_portalFooter(); ?>
		</div>
	</div>
</div>