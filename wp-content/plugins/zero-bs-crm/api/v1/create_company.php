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

		$json_params = file_get_contents("php://input");
		$new_company = json_decode($json_params,true);

		$we_have_tags = false;

		$coname = ''; if (isset($new_company['coname'])) $coname 		= sanitize_text_field($new_company['coname']);
		$email = ''; if (isset($new_company['email'])) $email 		= sanitize_text_field($new_company['email']);
		$status = ''; if (isset($new_company['status'])) $status 	= sanitize_text_field($new_company['status']);
		$prefix = ''; if (isset($new_company['prefix'])) $prefix     = sanitize_text_field($new_company['prefix']);

		$addr1 = ''; if (isset($new_company['addr1'])) $addr1 		= sanitize_text_field($new_company['addr1']);
		$addr2 = ''; if (isset($new_company['addr2'])) $addr2 		= sanitize_text_field($new_company['addr2']);
		$city = ''; if (isset($new_company['city'])) $city 		= sanitize_text_field($new_company['city']);
		$county = ''; if (isset($new_company['county'])) $county     = sanitize_text_field($new_company['county']);
		$post = ''; if (isset($new_company['postcode'])) $post       = sanitize_text_field($new_company['postcode']);

		$hometel = ''; if (isset($new_company['hometel'])) $hometel    = sanitize_text_field($new_company['hometel']);
		$worktel = ''; if (isset($new_company['worktel'])) $worktel    = sanitize_text_field($new_company['worktel']);
		$mobtel = ''; if (isset($new_company['mobtel'])) $mobtel     = sanitize_text_field($new_company['mobtel']);
		$notes = ''; if (isset($new_company['notes'])) $notes      = sanitize_text_field($new_company['notes']);

		$tags = false; if (isset($new_company['tags'])) $tags 		= $new_company['tags'];

		#} NEW ASSIGN TO
		$assign 	= -1; if (isset($new_company['assign'])) $assign = (int)$new_company['assign'];

		#} Santize tags
		if(is_array($tags) && count($tags) > 0){

			// basic filtering
			$customer_tags = filter_var_array($tags,FILTER_SANITIZE_STRING); 
			
			// dumb check - not empties :)
			$temptags = array(); foreach ($customer_tags as $t){
				$t2 = trim($t); if (!empty($t2)) $temptags[] = $t2;
			}

			// last check + set
			if (count($temptags) > 0) {
				$we_have_tags = true;
				$customer_tags = $temptags;
				unset($temptags);
			}
		}


		#} Declared
		$custom_fields_array = array();

		#} Custom fields
		foreach($new_company as $new_company_fieldK => $new_company_fieldV){
				if (substr($new_company_fieldK,0,2) == "cf"){
					$cf_indexname = 'zbsc_' . $new_company_fieldK;
					$custom_fields_array[$cf_indexname] =  sanitize_text_field($new_company_fieldV);
				}
		}


		#} Build pretty log msgs :)

			#} DEFAULTS
				#} Existing user updated by API
				$existingUserAPISourceShort = __('Updated by API Action','zero-bs-crm').' <i class="fa fa-random"></i>';
				$existingUserAPISourceLong = __('API Action fired to update company',"zero-bs-crm");

				#} New User from API
				$newUserAPISourceShort = __('Created from API Action','zero-bs-crm').' <i class="fa fa-random"></i>';
				$newUserAPISourceLong = __('API Action fired to create company',"zero-bs-crm");


			#} Here we catch "HTTP_USER_AGENT": "Zapier" ;)
			if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] == 'Zapier'){
				
				#} Just means this was probs fired by ZAP APP
				#} So pretty up msgs :)
				$existingUserAPISourceShort = __('Updated by Zapier (API)',"zero-bs-crm").' <i class="fa fa-random"></i>';
				$existingUserAPISourceLong = __('Zapier fired an API Action to update this customer',"zero-bs-crm");

				#} New User from api
				$newUserAPISourceShort = __('Created by Zapier (API)',"zero-bs-crm").' <i class="fa fa-random"></i>';
				$newUserAPISourceLong = __('Zapier fired an API Action to create this customer',"zero-bs-crm");

			}

				#} Actual log var passed
				$fallBackLog = array(
							'type' => 'API Action',
							'shortdesc' => $existingUserAPISourceShort,
							'longdesc' => $existingUserAPISourceLong
						);

				#} Internal automator overrides - here we pass a "customer.create" note override (so we can pass it a custom str, else we let it fall back to "created by api")
				$internalAutomatorOverride = array(

							'note_override' => array(
						
										'type' => 'API Action',
										'shortdesc' => $newUserAPISourceShort,
										'longdesc' => $newUserAPISourceLong				

							)

						);


		#} WH - don't enter blanks?
		if (!empty($email) && zeroBSCRM_validateEmail($email)){
					   

			$customer_array = array(
		    	'zbsc_email' => $email,
		    	'zbsc_status' => $status,
		    	'zbsc_prefix' => $prefix,
		    	'zbsc_coname' => $coname,
		    	'zbsc_addr1' => $addr1,
		    	'zbsc_addr2' => $addr2,
		    	'zbsc_city' => $city,
		    	'zbsc_county' => $county,
		    	'zbsc_postcode' => $post,
		    	'zbsc_hometel' => $hometel,
		    	'zbsc_worktel' => $worktel,
		    	'zbsc_mobtel' => $mobtel,
		    	'zbsc_notes' => $notes
		    );

			$update_args = array_merge($customer_array, $custom_fields_array);

			#} Status default - double-backup for api check
			if (isset($update_args) && is_array($update_args) && (is_null($update_args['zbsc_status']) || !isset($update_args['zbsc_status']) || empty($update_args['zbsc_status']))) {

				$defaultStatus = zeroBSCRM_getSetting('defaultstatus');
				$update_args['zbsc_status'] = $defaultStatus; // 'Lead';

			}


			if($we_have_tags){
				$update_args['tags'] = $customer_tags;   
			}

			// need to pass via the update_args otherwise the tags are added AFTER the automation fires...      when doing new DB we need to hook and filter up varios steps of these 

			// e.g.

			/*

				apply_filters('pre_do_this', $args);
				
				do_this
	
				apply_filters('post_do_this', $args);  // etc..

			*/

			$newCompany = zeroBS_integrations_addOrUpdateCompany('api',$email,$update_args,

		    '', #) Customer date (auto)
			
			#} Fallback log (for customers who already exist)
			$fallBackLog,

			false, #} Extra meta

			#} Internal automator overrides - here we pass a "customer.create" note override (so we can pass it a custom str, else we let it fall back to "created by API")
			$internalAutomatorOverride
			);
			// ^^ this'll be either: ID if added, no of rows if updated, or FALSE if failed to insert/update


			#} are we assigning to a user?
			if(isset($assign) && !empty($assign)){
				//set owner
				zeroBS_setOwner($newCompany, $assign);
			}




			// old way just returned what was sent...
		    //wp_send_json($json_params); //sends back to Zapier the customer that's been sent to it.

			// thorough much? lol.
			if (!empty($newCompany) && $newCompany !== false && $newCompany !== -1){

				// return what was passed...
				// this is legacy funk.. not ideal at all, should probs reload.
				$return_params = $new_company;

				// add id (if new)
				if ($newCompany > 0) 
					$return_params['id'] = $newCompany;

				// return
				wp_send_json($return_params);  

			} else {

				// fail.
				wp_send_json(array('error'=>100));  			

			}

		}

	}
	
	exit();

?>