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
<div class="alignwide zbs-site-main zbs-portal-grid">
    <nav class="zbs-portal-nav">
	<?php
	//moved into func
    if(function_exists('zeroBSCRM_clientPortalgetEndpoint')){
        $tran_endpoint = zeroBSCRM_clientPortalgetEndpoint('transactions');
    }else{
        $tran_endpoint = 'transactions';
    }
	zeroBS_portalnav($tran_endpoint);
    ?>
    </nav>
	<div class='zbs-portal-content'>
		<?php

			global $zbs;
			$uid = get_current_user_id();
			$uinfo = get_userdata( $uid );
			$cID = zeroBS_getCustomerIDWithEmail($uinfo->user_email);

			if( $cID > 0 || $uinfo->has_cap( 'admin_zerobs_transactions' ) ){
			
				$customer_transactions = zeroBS_getTransactionsForCustomer($cID,true,100,0,false);

				// preview msg		
				zeroBSCRM_portal_adminPreviewMsg($cID,'margin-bottom:1em');

				// admin msg (upsell cpp) (checks perms itself, safe to run)
				zeroBSCRM_portal_adminMsg();			

				if (is_array($customer_transactions) && count($customer_transactions) > 0){

					// titled v3.0
					?><h2><?php _e('Transactions','zero-bs-crm'); ?></h2>
											<div class='zbs-entry-content zbs-responsive-table' style="position:relative;">
									<?php

					echo '<table class="table">';
									echo '<thead>';
					echo '<th>' . __('Transaction',"zero-bs-crm") . '</th>';
					echo '<th>' . __('Transaction Date',"zero-bs-crm") . '</th>';
					echo '<th>' . __('Title',"zero-bs-crm") . '</th>';
					echo '<th>' . __('Total',"zero-bs-crm") . '</th>';
									echo '</thead>';

									foreach($customer_transactions as $transaction){

						// Transaction Date
						$transaction_date = __("No date", "zero-bs-crm");
						if (isset($transaction['date_date']) && !empty($transaction['date_date'])) {
							$transaction_date = $transaction['date_date'];
						}

						// Transaction Ref
						$transaction_ref = '';
						if (isset($transaction['ref']) && !empty($transaction['ref'])) {
							$transaction_ref = $transaction['ref'];
						}

						// transactionTitle Title
						// Default value is set to '&nbsp;' to force rendering the cell. The css "empty-cells: show;" doesn't work in this type of table
						$transaction_title = '&nbsp;';
						if (isset($transaction['title']) && !empty($transaction['title'])) {
							$transaction_title = $transaction['title'];
						}

						// Transaction Value
						$transaction_value = '';
						if (isset($transaction['total']) && !empty($transaction['total'])) {
							$transaction_value = zeroBSCRM_formatCurrency($transaction['total']);
						}

						echo '<tr>';
							echo '<td data-title="' . __('Transaction',"zero-bs-crm") . '">' . $transaction_ref . '</td>';
							echo '<td data-title="' . __('Transaction Date',"zero-bs-crm") . '">' . $transaction_date . '</td>';
							echo '<td data-title="' . __('Title',"zero-bs-crm") . '"><span class="name">'.$transaction_title.'</span></td>';
							echo '<td data-title="' . __('Total',"zero-bs-crm") . '">' . $transaction_value . '</td>';
						//	echo '<td class="tools"><a href="account/invoices/274119/pdf" class="pdf_download" target="_blank"><i class="fa fa-file-pdf-o"></i></a></td>';
						echo '</tr>';
					}
					echo '</table>';
				}else{
					_e('You do not have any transactions yet.',"zero-bs-crm"); 
				}
			}
			?></div>
    </div>
    <div class="zbs-portal-grid-footer"><?php zeroBSCRM_portalFooter(); ?></div>
</div>
