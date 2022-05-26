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
<div id="zbs-main" class="zbs-site-main">
	<div class="zbs-client-portal-wrap main site-main zbs-post zbs-hentry">
	<?php
	//moved into func
    if(function_exists('zeroBSCRM_clientPortalgetEndpoint')){
        $quote_endpoint = zeroBSCRM_clientPortalgetEndpoint('quotes');
    }else{
        $quote_endpoint = 'quotes';
    }
	zeroBS_portalnav($quote_endpoint);
	?>
<div class='zbs-portal-wrapper zbs-portal-invoices-list'>

<?php
	global $wpdb;
	$uid = get_current_user_id();
	$uinfo = get_userdata( $uid );
	$cID = zeroBS_getCustomerIDWithEmail($uinfo->user_email);
	$currencyChar = zeroBSCRM_getCurrencyChr();

	// preview msg		
	zeroBSCRM_portal_adminPreviewMsg($cID,'margin-bottom:1em');

	// admin msg (upsell cpp) (checks perms itself, safe to run)
	zeroBSCRM_portal_adminMsg();

	$customer_quotes = zeroBS_getQuotesForCustomer($cID,true,100,0,false);


	if(count($customer_quotes) > 0){
		echo '<table class="table">';

		echo '<th>' . __('#',"zero-bs-crm") . '</th>';
		echo '<th>' . __('Date',"zero-bs-crm") . '</th>';
		echo '<th>' . __('Title',"zero-bs-crm") . '</th>';
		echo '<th>' . __('Total',"zero-bs-crm") . '</th>';
		echo '<th>' . __('Status',"zero-bs-crm") . '</th>';
		// echo '<th>' . __('Download PDF',"zero-bs-crm") . '</th>';

		foreach($customer_quotes as $cquo){


			 if (isset($cquo['meta']['date']) && !empty($cquo['meta']['date'])){
				$dateUTS = zeroBSCRM_locale_dateToUTS($cquo['meta']['date']);
				$dateFormatted = zeroBSCRM_date_i18n(get_option('date_format'),$dateUTS,false,true);
			 }else{
				 $dateFormatted = __("No date", "zero-bs-crm");
			 }

			$quote_stat = zeroBS_getQuoteStatus($cquo);

			$numFormatted = ''; if (isset($cquo['meta']['val']) && !empty($cquo['meta']['val'])){
				try {
					$numFormatted = number_format($cquo['meta']['val'],2);
				} catch (Exception $e){
					// nout
				}
			}

			echo '<tr>';
			//	echo '<td><a href="'. home_url('?p='.$cquo['id']) .'">#'. $cquo['zbsid'] . __(' (view)', 'zero-bs-crm') . '</a></td>';
			echo '<td><a href="'. esc_url($portalLink . $quote_endpoint . '/' . $cquo['id']) .'">#'. $cquo['zbsid'] . __(' (view)', 'zero-bs-crm') . '</a></td>';
				echo '<td>' . $dateFormatted . '</td>';
				echo '<td><span class="name">'.$cquo['meta']['name'].'</span></td>';
				echo '<td>' . $currencyChar . $numFormatted . '</td>';
				echo '<td><span class="status">'.$quote_stat.'</span></td>';
			//	echo '<td class="tools"><a href="account/invoices/274119/pdf" class="pdf_download" target="_blank"><i class="fa fa-file-pdf-o"></i></a></td>';
			echo '</tr>';
		}
		echo '</table>';
	}else{
		_e('You do not have any quotes yet.',"zero-bs-crm"); 
	}
	?>

		<?php zeroBSCRM_portalFooter(); ?>
		<div style="clear:both"></div>
		</div>
	</div>
</div>