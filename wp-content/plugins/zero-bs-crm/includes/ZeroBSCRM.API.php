<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 * V2.0
 *
 * Copyright 2020 Automattic
 *
 * Date: 05/04/2017
 */

/* ======================================================
  Breaking Checks ( stops direct access )
   ====================================================== */
    if ( ! defined( 'ZEROBSCRM_PATH' ) ) exit;
/* ======================================================
  / Breaking Checks
   ====================================================== */

#} We can do this below in the templater or templates? add_action( 'wp_enqueue_scripts', 'zeroBS_portal_enqueue_stuff' );
#} ... in the end we can just dump the above line into the templates before get_header() - hacky but works

// Adds the Rewrite Endpoint for the 'clients' area of the CRM. 
#} WH - this is dumped here now, because this whole thing is fired just AFTER init (to allow switch/on/off in main ZeroBSCRM.php)

function zeroBS_api_rewrite_endpoint(){
	add_rewrite_endpoint( 'zbs_api', EP_ROOT );
}
add_action('init','zeroBS_api_rewrite_endpoint');

/** 
 * Check the access to the API. If not, exit 
 */
function jpcrm_api_access_controller() {
	if ( ! zeroBSCRM_API_is_zbs_api_authorised() ) {
		zeroBSCRM_API_AccessDenied();
		exit();
	}
}

/**
 * Process the query and get page and items per page
 */
function jpcrm_api_process_pagination() {

    if ( isset( $_GET['page'] ) && (int) $_GET['page'] >= 0 ) {
        $page = (int) $_GET['page'];
    } else {
        $page = 0;
    }

    if ( isset( $_GET['perpage'] ) && (int) $_GET['perpage'] >= 0 ) {
        $per_page = (int) $_GET['perpage'];
    } else {
        $per_page = 10;
    }

    return array( $page, $per_page );
}

/**
 * Check and process if there is a search in the query
 */
function jpcrm_api_process_search() {
    return ( isset( $_GET['zbs_query'] ) ? sanitize_text_field( $_GET['zbs_query'] ) : '' );
}

/**
 * Check if the request match with the expected HTTP methods
 *
 * @param array $methods_allowed List of the request HTTP methods (GET, POST, PUSH, DELETE)
 * @return bool
 */
function jpcrm_api_check_http_method( $methods_allowed = array( 'GET' ) ) {

    if ( in_array( $_SERVER['REQUEST_METHOD'], $methods_allowed ) ) {
        return true;
    } else {
        echo zeroBSCRM_API_error( 'Method not allowed.',405 );
        exit();
    }
}

/**
 * Return Access Denied
 */
function zeroBSCRM_API_AccessDenied(){
    echo zeroBSCRM_API_error( 'Access Denied.',403 );
    exit();
}

/**
 * Manage an API Error response with the correct headers and encode the data to JSON
 * 
 * @param string $errorMsg
 * @param int $headerCode
 */
function zeroBSCRM_API_error($errorMsg='Error',$header_code=400){

	#} 400 = general error
	#} 403 = perms
	wp_send_json( array ("error" => $errorMsg), $header_code );
	
}


// returns the directory name for API version to use. 
// v3.0+ uses v3, rest v1.0 (was previously 'endpoints')
function zeroBSCRM_API_endpointsDir(){
	
	global $zbs;
	if ($zbs->isDAL3())
		return 'v3';
	
	return 'v1';
}


// now to locate the templates...
// http://jeroensormani.com/how-to-add-template-files-in-your-plugin/

/**
 * Locate template.
 *
 * Locate the called template.
 * Search Order:
 * 1. /templates not over-ridable
 *
 * @since 1.2.7
 *
 * @param 	string 	$template_name			Template to load.
 * @param 	string 	$string $template_path	Path to templates.
 * @param 	string	$default_path			Default path to template files.
 * @return 	string 							Path to the template file.
 */
function zeroBSCRM_API_locate_api_endpoint( $template_name, $template_path = '', $default_path = '' ) {
	// Set variable to search in zerobscrm-plugin-templates folder of theme.
	if ( ! $template_path ) :
		$template_path = 'zerobscrm-plugin-templates/';
	endif;
	// Set default plugin templates path.
	if ( ! $default_path ) :
		$default_path = ZEROBSCRM_PATH . 'api/'.zeroBSCRM_API_endpointsDir().'/'; // Path to the template folder
	endif;
	// Search template file in theme folder.
	$template = locate_template( array(
		$template_path . $template_name,
		$template_name
	) );
	// Get plugins template file.
	if ( ! $template ) :
		$template = $default_path . $template_name;
	endif;
	return apply_filters( 'zeroBSCRM_API_locate_api_endpoint', $template, $template_name, $template_path, $default_path );
}

/**
 * Get template.
 *
 * Search for the template and include the file.
 *
 * @since 1.2.7
 *
 * @see zeroBSCRM_API_get_template()
 *
 * @param string 	$template_name			Template to load.
 * @param array 	$args					Args passed for the template file.
 * @param string 	$string $template_path	Path to templates.
 * @param string	$default_path			Default path to template files.
 */
function zeroBSCRM_API_get_api_endpoint( $template_name, $args = array(), $tempate_path = '', $default_path = '' ) {
	if ( is_array( $args ) && isset( $args ) ) :
		extract( $args );
	endif;
	$template_file = zeroBSCRM_API_locate_api_endpoint( $template_name, $tempate_path, $default_path );
	if ( ! file_exists( $template_file ) ) :
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), '1.0.0' );
		return;
	endif;
	include $template_file;
}

//function similar to is_user_logged_in() and will serve an access denied template if not
function zeroBSCRM_API_is_zbs_api_authorised(){

	#} WH - I've added api_secret here to bolster security,
	#} We should switch authentication method to "headers" not parameters - will be cleaner :)

	$zbsAPIKey = ''; $zbsAPISecret = ''; 
	if (isset($_GET['api_key'])) $zbsAPIKey = sanitize_text_field($_GET['api_key']);    //in Zapier it adds it to the end of the URL
	if (isset($_GET['api_secret'])) $zbsAPISecret = sanitize_text_field($_GET['api_secret']);
	
	//the the API key is in the URL
	$APIkey_req = $zbsAPIKey;
	$APIkey_allowed = zeroBSCRM_getAPIKey();

//	if ($APIkey_req == $APIkey_allowed){

	if(hash_equals($APIkey_req, $APIkey_allowed) && !empty($zbsAPISecret)){   

		//added code below too from here: http://php.net/manual/en/function.hash-equals.php#115635

		#} THEN check secret
    	$api_secret = zeroBSCRM_getAPISecret();
    //	if ($zbsAPISecret == $api_secret) return true;	

    	if(hash_equals($zbsAPISecret, $api_secret)) return true;	
	}

	// OR we are coming from GROOVE HQ - define in wp-config.php
	if(defined('GROOVE_API_TOKEN') && isset($_GET['api_token']) && !empty($_GET['api_token'])){
		if($_GET['api_token'] == GROOVE_API_TOKEN){
			// and define that we've checked
			if (!defined('ZBSGROOVECHECKED')) define('ZBSGROOVECHECKED',time());
			return true;
		}
	}

	
	#} NOPE.
	return false;	

}

function zeroBSCRM_getAPIEndpoint(){
  return site_url( '/zbs_api/'); #, 'https' );
}

function zeroBSCRM_API_generate_api_key(){
	$api_key = zeroBSCRM_API_random_hash(22); //lets have a 22 character API
	return $api_key;
}

//from a GIST somewhere which I f**kin lost the tab for ... .lolz..
function zeroBSCRM_API_random_hash($str_length = 22) {
  $third = wp_generate_password($str_length, false);
  return 'zbscrm_' . $third;

}


/* SAME CODE AS IN PORTAL, BUT REPLACED WITH api_endpoint stuff. Templates (to return the JSON) are in /api/endpoints/ folder
*/


add_filter( 'template_include', 'zeroBSCRM_API_api_endpoint', 99 );

function zeroBSCRM_API_api_endpoint( $template ) {

	$zbsAPIQuery = get_query_var( 'zbs_api' );

	#} Debug echo 'QS:'.$zbsAPIQuery.'!'; exit();

	#} We only want to interfere where clients is set :)
	#} ... as this is called for ALL page loads
	if (isset($zbsAPIQuery) && !empty($zbsAPIQuery)){

		#} Break it up if / present
		if (strpos($zbsAPIQuery,'/'))
			$zbsAPIRequest = explode('/',$zbsAPIQuery);
		else
			#} no / in it, so must just be a 1 worder like "invoices", here just jam in array so it matches prev exploded req.
			$zbsAPIRequest = array($zbsAPIQuery);

		#} Authorise the request 


		#} Catch data coming in...
		if (!empty($zbsAPIRequest[0]) && $zbsAPIRequest[0] == 'create_customer'){

			#} $_GET['api_key'] still holds the api_key (i.e. it's not POSTED) even though it's a POST request it's posted
			#} to a URL something like https://example.com/create_customer/?api_key='j39djjpdijiejpj';
			if(!zeroBSCRM_API_is_zbs_api_authorised()){
				return zeroBSCRM_API_get_api_endpoint('denied.php');
			}else{
				return zeroBSCRM_API_get_api_endpoint('create_customer.php');
			}

		}
		#} Catch data coming in...
		else if (!empty($zbsAPIRequest[0]) && $zbsAPIRequest[0] == 'create_transaction'){

			#} $_GET['api_key'] still holds the api_key (i.e. it's not POSTED) even though it's a POST request it's posted
			#} to a URL something like https://example.com/create_customer/?api_key='j39djjpdijiejpj';
			if(!zeroBSCRM_API_is_zbs_api_authorised()){
				return zeroBSCRM_API_get_api_endpoint('denied.php');
			}else{
				return zeroBSCRM_API_get_api_endpoint('create_transaction.php');
			}

		}

		else if (!empty($zbsAPIRequest[0]) && $zbsAPIRequest[0] == 'create_event'){

			#} $_GET['api_key'] still holds the api_key (i.e. it's not POSTED) even though it's a POST request it's posted
			#} to a URL something like https://example.com/create_customer/?api_key='j39djjpdijiejpj';
			if(!zeroBSCRM_API_is_zbs_api_authorised()){
				return zeroBSCRM_API_get_api_endpoint('denied.php');
			}else{
				return zeroBSCRM_API_get_api_endpoint('create_event.php');
			}

		}


		else if (!empty($zbsAPIRequest[0]) && $zbsAPIRequest[0] == 'create_company'){

			#} $_GET['api_key'] still holds the api_key (i.e. it's not POSTED) even though it's a POST request it's posted
			#} to a URL something like https://example.com/create_customer/?api_key='j39djjpdijiejpj';
			if(!zeroBSCRM_API_is_zbs_api_authorised()){
				return zeroBSCRM_API_get_api_endpoint('denied.php');
			}else{
				return zeroBSCRM_API_get_api_endpoint('create_company.php');
			}

		}

		else if (!empty($zbsAPIRequest[0]) && $zbsAPIRequest[0] == 'incoming_email'){

			#} $_GET['api_key'] still holds the api_key (i.e. it's not POSTED) even though it's a POST request it's posted
			#} to a URL something like https://example.com/create_customer/?api_key='j39djjpdijiejpj';
			if(!zeroBSCRM_API_is_zbs_api_authorised()){
				return zeroBSCRM_API_get_api_endpoint('denied.php');
			}else{
				return zeroBSCRM_API_get_api_endpoint('incoming_email.php');
			}

		}


		else if (!empty($zbsAPIRequest[0]) && $zbsAPIRequest[0] == 'customer_search'){

			#} $_GET['api_key'] still holds the api_key (i.e. it's not POSTED) even though it's a POST request it's posted
			#} to a URL something like https://example.com/create_customer/?api_key='j39djjpdijiejpj';
			if(!zeroBSCRM_API_is_zbs_api_authorised()){
				return zeroBSCRM_API_get_api_endpoint('denied.php');
			}else{
				return zeroBSCRM_API_get_api_endpoint('customer_search.php');
			}

		}
		
		else if ( ! empty( $zbsAPIRequest[0] ) && $zbsAPIRequest[0] == 'api_status' ){

			// for this endpoint, pass all requests along
			return zeroBSCRM_API_get_api_endpoint( 'api_status.php' );

		} else {

			#} Querying API for data going out

			// look for a debug query variable that has a value
			if ( !empty( $zbsAPIRequest[0] ) ) {

				// show wp_query info if debug has value of "query"
				if ( $zbsAPIRequest[0] == 'customers' ) {
					if(!zeroBSCRM_API_is_zbs_api_authorised()){
						return zeroBSCRM_API_get_api_endpoint('denied.php');
					}else{
						return zeroBSCRM_API_get_api_endpoint('customers.php');
					}
				}

				if ( $zbsAPIRequest[0] == 'invoices' ) {
					if(!zeroBSCRM_API_is_zbs_api_authorised()){
						return zeroBSCRM_API_get_api_endpoint('denied.php');
					}else{
						return zeroBSCRM_API_get_api_endpoint('invoices.php');
					}
				}
	
				if ( $zbsAPIRequest[0] == 'quotes' ) {
					if(!zeroBSCRM_API_is_zbs_api_authorised()){
						return zeroBSCRM_API_get_api_endpoint('denied.php');
					}else{
						return zeroBSCRM_API_get_api_endpoint('quotes.php');
					}
				}


				if ( $zbsAPIRequest[0] == 'events' ) {
					if(!zeroBSCRM_API_is_zbs_api_authorised()){
						return zeroBSCRM_API_get_api_endpoint('denied.php');
					}else{
						return zeroBSCRM_API_get_api_endpoint('events.php');
					}
				}

				if ( $zbsAPIRequest[0] == 'companies' ) {
					if(!zeroBSCRM_API_is_zbs_api_authorised()){
						return zeroBSCRM_API_get_api_endpoint('denied.php');
					}else{
						return zeroBSCRM_API_get_api_endpoint('companies.php');
					}
				}


				if ( $zbsAPIRequest[0] == 'transactions' ) {
					if(!zeroBSCRM_API_is_zbs_api_authorised()){
						return zeroBSCRM_API_get_api_endpoint('denied.php');
					}else{
						return zeroBSCRM_API_get_api_endpoint('transactions.php');
					}
				}


			}

		} # / normal load

	}

	return $template;
}


if(!function_exists('hash_equals')) {
  function hash_equals($str1, $str2) {
    if(strlen($str1) != strlen($str2)) {
      return false;
    } else {
      $res = $str1 ^ $str2;
      $ret = 0;
      for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
      return !$ret;
    }
  }
}


#} avoids us writing our own Parser. In particular can use
#} https://parser.zapier.com/
#} Fills a void for now, and saves needing to fully write a Jetpack inbox / own parser
#} HOWEVER, would rather have our own - like this
#} my96ew3z@robot.jetpackcrm.com to parse and deliver the emails to 
#} their API website (managed via the licensing)
function zeroBS_inbox_api_catch($emailFields){
	global $wpdb, $ZBSCRM_t;

	$customerID = (int)zeroBS_getCustomerIDWithEmail( $emailFields['from'] );

	//if we don't have a customer, we need to create one with the email send

	$thread 	= $emailFields['thread'];
	$from 		= $emailFields['from'];
	$subject 	= $emailFields['subject'];

	if($customerID == 0){
		$customer['zbsc_email'] = $from;
		$customer['zbsc_status'] = 'Lead'; //default
		$customerID = zeroBS_addUpdateCustomer(-1, $customer);
	}


	//then if no thread ID, try and get the thread from the sender email and the subject, if a match thread it, if not new thread
	if($thread == -1){
		//strip any Re:, RE: or re:
		$subject = trim(str_ireplace('re:', '', $subject));
		$search_text = "%" . $subject. "%";
		$sql = $wpdb->prepare("SELECT zbsmail_sender_thread FROM " . $ZBSCRM_t['system_mail_hist'] . " WHERE zbsmail_receiver_email = '%s' AND zbsmail_subject LIKE %s", $from, $search_text);
		$thread = $wpdb->get_var($sql);
	}

	#} then a new thread.
	if($thread == ''){
		//then we are making a new thread. Otherwise, it will be passed via the function / other send boxes
		$sql = "SELECT MAX(zbsmail_sender_thread) as max_thread FROM " . $ZBSCRM_t['system_mail_hist'];
		$max_thread = $wpdb->get_var($sql);
		$max_thread++;
		$thread = $max_thread;
	}
	zeroBSCRM_mailTracking_logEmail(-1, $customerID, 0, '', -1, $subject,true, $emailFields['content'], $thread, $emailFields['from'], 'inbox');
}