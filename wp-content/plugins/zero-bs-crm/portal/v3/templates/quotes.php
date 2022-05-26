<?php
/**
 * Quote List Page
 *
 * This list of Quotes for the Portal
 *
 * @author 		ZeroBSCRM
 * @package 	Templates/Portal/Quotes
 * @see			https://jetpackcrm.com/kb/
 * @version     3.0
 * 
 */



if ( ! defined( 'ABSPATH' ) ) exit; // Don't allow direct access

$ZBSuseQuotes = zeroBSCRM_getSetting('feat_quotes');
//zeroBS_portal_enqueue_stuff();

do_action( 'zbs_enqueue_scripts_and_styles' );

$portalLink = zeroBS_portal_link();

if($ZBSuseQuotes < 0){
        status_header( 404 );
        nocache_headers();
        include( get_query_template( '404' ) );
        die();
}

add_action( 'wp_enqueue_scripts', 'zeroBS_portal_enqueue_stuff' );
?>
<div class="alignwide zbs-site-main zbs-portal-grid">
    <nav class="zbs-portal-nav">
    <?php
        //moved into func
        if(function_exists('zeroBSCRM_clientPortalgetEndpoint')){
            $quote_endpoint = zeroBSCRM_clientPortalgetEndpoint('quotes');
        }else{
            $quote_endpoint = 'quotes';
        }
        zeroBS_portalnav($quote_endpoint);
    ?>
    </nav>


    <div class="zbs-portal-content">
<?php
	global $wpdb;
	$uid = get_current_user_id();
	$uinfo = get_userdata( $uid );
	$cID = zeroBS_getCustomerIDWithEmail($uinfo->user_email);
	$is_quote_admin = $uinfo->has_cap( 'admin_zerobs_quotes' );

	// if the current is a valid contact or a WP user with permissions to view quotes...
	if( $cID > 0 || $is_quote_admin ){
		$currencyChar = zeroBSCRM_getCurrencyChr();

		// this allows the current admin to see all quotes even if they're a contact
		if ( $is_quote_admin ) {
			$cID = -1;
			jpcrm_portal_viewing_as_admin( __( 'Admins will see all quotes below, but clients will only see quotes assigned to them.', 'zero-bs-crm' ) );
		}

		// get quotes
		$customer_quotes = zeroBS_getQuotesForCustomer($cID,true,100,0,false);

			?><h2><?php _e('Quotes','zero-bs-crm'); ?></h2>
							<div class='zbs-entry-content zbs-responsive-table' style="position:relative;">
									<?php

		// if there are more than zero quotes...
		if(count($customer_quotes) > 0){

			$quotes_to_show = '';
			foreach($customer_quotes as $cquo){

				// skip drafts if not an admin with quote access
				if ( $cquo['status'] == -1 && !$is_quote_admin ) {
					continue;
				}
				// Quote Date
				$quote_date = __("No date", "zero-bs-crm");
				if (isset($cquo['date_date']) && !empty($cquo['date_date'])) {
					$quote_date = $cquo['date_date'];
				}

				// Quote Status
				$quote_stat = zeroBS_getQuoteStatus($cquo);

				// Quote Value
				$quote_value = '';
				if (isset($cquo['value']) && !empty($cquo['value'])){
					$quote_value = zeroBSCRM_formatCurrency($cquo['value']);
				}
			
						// view on portal (hashed?)
						$quote_url = zeroBSCRM_portal_linkObj($cquo['id'],ZBS_TYPE_QUOTE);

				// Quote Title
				// Default value is set to '&nbsp;' to force rendering the cell. The css "empty-cells: show;" doesn't work in this type of table
				$quote_title = '&nbsp;';
				if (isset($cquo['title']) && !empty($cquo['title'])){
					$quote_title = $cquo['title'];
				}

				$quotes_to_show .= '<tr>';
				$quotes_to_show .= '<td data-title="' . __('#',"zero-bs-crm") . '"><a href="'. $quote_url .'">#'. $cquo['id'] .' '. __('(view)','zero-bs-crm') . '</a></td>';
				$quotes_to_show .= '<td data-title="' . __('Date',"zero-bs-crm") . '">' . $quote_date . '</td>';
				$quotes_to_show .= '<td data-title="' . __('Title',"zero-bs-crm") . '"><span class="name">'.$quote_title.'</span></td>';
				$quotes_to_show .= '<td data-title="' . __('Total',"zero-bs-crm") . '">' . $quote_value . '</td>';
				$quotes_to_show .= '<td data-title="' . __('Status',"zero-bs-crm") . '"><span class="status">'.$quote_stat.'</span></td>';
				$quotes_to_show .= '</tr>';
			}

			if ( !empty( $quotes_to_show ) ) {
				// there are quotes to show to this user, so build table
				echo '<table class="table">';
					echo '<thead>';
						echo '<th>' . __('#',"zero-bs-crm") . '</th>';
						echo '<th>' . __('Date',"zero-bs-crm") . '</th>';
						echo '<th>' . __('Title',"zero-bs-crm") . '</th>';
						echo '<th>' . __('Total',"zero-bs-crm") . '</th>';
						echo '<th>' . __('Status',"zero-bs-crm") . '</th>';
					echo '</thead>';
					echo $quotes_to_show;
				echo '</table>';
			}
			else {
				// no quotes to show...might have drafts but no admin perms
				_e('You do not have any quotes yet.',"zero-bs-crm");
			}
		}else{
			// quote object count for current user is 0
			_e('You do not have any quotes yet.',"zero-bs-crm");
		}
	}else{
		// not a valid contact or quote admin user
		_e( 'You do not have any quotes yet.', 'zero-bs-crm' );
	}
	?>
		</div>
	</div>
	<div class="zbs-portal-grid-footer"><?php zeroBSCRM_portalFooter(); ?></div>
</div>
