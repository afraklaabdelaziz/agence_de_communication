<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 * V2.0
 *
 * Copyright 2020 Automattic
 *
 * Date: 06/04/17
 */

/* ======================================================
  Breaking Checks ( stops direct access )
   ====================================================== */
    if ( ! defined( 'ZEROBSCRM_PATH' ) ) exit;
/* ======================================================
  / Breaking Checks
   ====================================================== */

   
	if (!zeroBSCRM_API_is_zbs_api_authorised()){

		   #} NOPE
		   zeroBSCRM_API_AccessDenied(); 
		   exit();

	} else {
	

		$zbs_cust_search = ''; if (isset($_GET['zbs_query'])) $zbs_cust_search =  sanitize_text_field($_GET['zbs_query']);

		if(isset($_GET['email'])){
			//searching email, so lets use that to override - should only be ONE match - return financial data (performant)
			$zbs_cust_search = sanitize_text_field( $_GET['email'] );
			$customer_matches = zeroBS_searchCustomers($zbs_cust_search,true);
			$customer_matches = $customer_matches[0];

			//so only one match, Groove Sidebar has extra information, will do this way, for file compatibility
			if(isset($_GET['api_token']) && defined('ZBSGROOVECHECKED')){
				//then it's coming from Groove, so send back total value and last purchased information
				$customerID = $customer_matches['id'];
				$total_value = zeroBS_customerTotalValue($customerID, $customer_matches['invoices'], $customer_matches['transactions']);
				$customer_matches['total_value'] = $total_value;


				//also needs
				/**
				 * purchase_item
				 * purchase_value
				 * purchase_date
				 */

				if (isset($customer_matches['transactions']) && is_array($customer_matches['transactions']) && count($customer_matches['transactions']) > 0){				

					$customer_matches['purchase_item'] = $customer_matches['transactions'][0]['meta']['item'];
					$customer_matches['purchase_value'] = $customer_matches['transactions'][0]['meta']['total'];
					$customer_matches['purchase_date'] = $customer_matches['transactions'][0]['created'];

				}

				 /* should also format the bl00dy dates */


			}

			wp_send_json($customer_matches);
		}else{
			// could be more matches (don't return financial data - unperformant)
			$customer_matches = zeroBS_integrations_searchCustomers($zbs_cust_search);
			wp_send_json($customer_matches);

		}



	} 

	exit();

?>