<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 * V1.20
 *
 * Copyright 2020 Automattic
 *
 * Date: 01/11/16
 */
use Automattic\JetpackCRM\Segment_Condition_Exception;

/* ======================================================
  Breaking Checks ( stops direct access )
   ====================================================== */
    if ( ! defined( 'ZEROBSCRM_PATH' ) ) exit;
/* ======================================================
  / Breaking Checks
   ====================================================== */


/* ======================================================
	Admin AJAX
   ====================================================== */



  add_action( 'wp_ajax_jpcrm_hide_woo_promo', 'jpcrm_hide_woo_promo' );
  function jpcrm_hide_woo_promo(){
    if ( current_user_can( 'activate_plugins' ) ) {
      $option = update_option( 'jpcrm_hide_woo_promo', 'hide', false );
      wp_send_json_success();
    }
  }


  add_action( 'wp_ajax_jpcrm_hide_track_notice', 'jpcrm_hide_track_notice' );
  function jpcrm_hide_track_notice(){
    if ( current_user_can( 'activate_plugins' ) ) {
      $option = update_option( 'jpcrm_hide_track_notice', 'hide', false );
      wp_send_json_success();
    }
  }

  add_action( 'wp_ajax_jpcrm_hide_feature_alert', 'jpcrm_hide_feature_alert' );
  function jpcrm_hide_feature_alert(){
    if ( current_user_can( 'activate_plugins' ) && isset( $_POST['feature_alert'] ) ){
      $option = 'jpcrm_hide_' . sanitize_text_field( $_POST['feature_alert'] );
      update_option( $option, true, false );
      wp_send_json_success();
    }
  }

#} Dash date range picker keeping as AJAX longer term be good to have this as WP REST API and React led.
add_action('wp_ajax_jetpackcrm_dash_refresh', 'jetpackcrm_dash_refresh');
function jetpackcrm_dash_refresh(){

	check_ajax_referer( 'zbs_dash_count', 'security' );  //nonce it up...


	// note for WH - looking at the DAL, we can probably extract these into DAL3 helpers?
	global $zbs, $wpdb, $ZBSCRM_t;
  
  // Table names $ZBSCRM_t['contacts'] and $ZBSCRM_t['transactions']


  	// the settings for the totals row in the dash.
	$cid = get_current_user_id();
	$settings_dashboard_total_contacts = get_user_meta($cid, 'settings_dashboard_total_contacts' ,true);
	$settings_dashboard_total_transactions  = get_user_meta($cid, 'settings_dashboard_total_transactions' ,true);

	/**
	 * [06-Nov-2020 09:10:44 UTC] Array
	* (
	*    [action] => jetpackcrm_dash_refresh
	*    [start_date] => 2019-11-06
	*    [end_date] => 2020-11-06
	* )
	*/

	$start_date = sanitize_text_field($_POST['start_date']);
	$end_date = sanitize_text_field($_POST['end_date']);

	$start_date = strtotime($start_date);
	$end_date = date_create( $end_date )->setTime( 23, 59, 59 )->getTimestamp();

	$summary = array();

	// we rely on DAL3 calls for the summary boxes
	if ( $zbs->isDAL3() ) {

		$range_params = array(
			'count'=>true,
			'newerThan'=>$start_date,
			'olderThan'=>$end_date
		);

		$summary[] = array(
			'label' => __( 'Contacts', 'zero-bs-crm' ),
			'range_total' => zeroBSCRM_prettifyLongInts( $zbs->DAL->getContacts( $range_params ) ),
			'alltime_total_str' => sprintf( __( '%s total', 'zero-bs-crm' ), zeroBSCRM_prettifyLongInts( $zbs->DAL->contacts->getFullCount() ) ),
			'link' => zbsLink($zbs->slugs['managecontacts']),
		);

		if ( zeroBSCRM_getSetting( 'feat_transactions' ) > 0 ) {
			$summary[] = array(
				'label' => __( 'Transactions', 'zero-bs-crm' ),
				'range_total' => zeroBSCRM_prettifyLongInts( $zbs->DAL->transactions->getTransactions( $range_params ) ),
				'alltime_total_str' => sprintf( __( '%s total', 'zero-bs-crm' ), zeroBSCRM_prettifyLongInts( $zbs->DAL->transactions->getFullCount() ) ),
				'link' => zbsLink($zbs->slugs['managetransactions']),
			);
		}

		if ( zeroBSCRM_getSetting('feat_quotes') > 0 ) {
			$summary[] = array(
				'label' => __( 'Quotes', 'zero-bs-crm' ),
				'range_total' => zeroBSCRM_prettifyLongInts( $zbs->DAL->quotes->getQuotes( $range_params ) ),
				'alltime_total_str' => sprintf( __( '%s total', 'zero-bs-crm' ), zeroBSCRM_prettifyLongInts($zbs->DAL->quotes->getFullCount() ) ),
				'link' => zbsLink($zbs->slugs['managequotes']),
			);
		}

		if ( zeroBSCRM_getSetting('feat_invs') > 0 ) {
			$summary[] = array(
				'label' => __( 'Invoices', 'zero-bs-crm' ),
				'range_total' => zeroBSCRM_prettifyLongInts( $zbs->DAL->invoices->getInvoices( $range_params ) ),
				'alltime_total_str' => sprintf( __( '%s total', 'zero-bs-crm' ), zeroBSCRM_prettifyLongInts( $zbs->DAL->invoices->getFullCount() ) ),
				'link' => zbsLink($zbs->slugs['manageinvoices']),
			);
		}

	}

	//next we want the contact chart which is total contacts between the dates grouped by day, week, month, year
	$sql 			= $wpdb->prepare("SELECT count(ID) as count, YEAR(FROM_UNIXTIME(zbsc_created)) as year FROM " . $ZBSCRM_t['contacts'] . " WHERE zbsc_created > %d AND zbsc_created < %d GROUP BY year ORDER BY year", $start_date, $end_date);
	$yearly 		= $wpdb->get_results($sql);

	$sql 			= $wpdb->prepare("SELECT count(ID) as count, zbsc_created as ts, MONTH(FROM_UNIXTIME(zbsc_created)) as month, YEAR(FROM_UNIXTIME(zbsc_created)) as year FROM " . $ZBSCRM_t['contacts'] . " WHERE zbsc_created > %d AND zbsc_created < %d GROUP BY month, year ORDER BY year, month", $start_date, $end_date);
	$monthly 		= $wpdb->get_results($sql);

	$sql 			= $wpdb->prepare("SELECT count(ID) as count, zbsc_created as ts, WEEK(FROM_UNIXTIME(zbsc_created)) as week, YEAR(FROM_UNIXTIME(zbsc_created)) as year FROM " . $ZBSCRM_t['contacts'] . " WHERE zbsc_created > %d AND zbsc_created < %d GROUP BY week, year ORDER BY year, week", $start_date, $end_date);
	$weekly			= $wpdb->get_results($sql);

	$sql 			= $wpdb->prepare("SELECT count(ID) as count, zbsc_created as ts, DAY(FROM_UNIXTIME(zbsc_created)) as day, MONTH(FROM_UNIXTIME(zbsc_created)) as month, YEAR(FROM_UNIXTIME(zbsc_created)) as year FROM " . $ZBSCRM_t['contacts'] . " WHERE zbsc_created > %d AND zbsc_created < %d GROUP BY day, month, year ORDER BY year, month, day", $start_date, $end_date);
	$daily			= $wpdb->get_results($sql);


	$zeros = jetpackcrm_create_zeros_array($start_date, $end_date);



	//get the data ready for the charts
	foreach($yearly as $k => $v){
		$zeros['year'][$v->year] = $v->count; 
	}

	//convert the monthly array into a zero padded one
	foreach($monthly as $k => $v){
		$the_month = date("M y", $v->ts);
		$zeros['month'][$the_month] = $v->count;
	}

	foreach($weekly as $k => $v){
		$the_month = date("W Y", $v->ts);
		$zeros['week'][$the_month] = $v->count;
	}

	foreach($daily as $k => $v){
		$the_month = date("d M y", $v->ts);
		$zeros['day'][$the_month] = $v->count;
	}

	$year_labels 	= array_keys($zeros['year']);
	$month_labels 	= array_keys($zeros['month']);
	$week_labels 	= array_keys($zeros['week']);
	$day_labels 	= array_keys($zeros['day']);
	
	$chart['yearly'] = array(
		'labels' 	=> $year_labels,
		'data'		=> array_values($zeros['year'])
	);

	$chart['monthly'] = array(
		'labels' 	=> $month_labels,
		'data'		=> array_values($zeros['month'])
	);

	$chart['weekly'] = array(
		'labels' 	=> $week_labels,
		'data'		=> array_values($zeros['week'])
	);


	$chart['daily'] = array(
		'labels' 	=> $day_labels,
		'data'		=> array_values($zeros['day'])
	);

	//the final output
	$r = array(
		'summary' => $summary,
		//semantic needs the words not the numeral for the class. stored this in the FormatHelpers.php from SO.
		'boxes'   => jetpackcrm_convertNumberToWord( count( $summary ) ),
		'chart'  => $chart
	);

	echo json_encode($r);
	die();
}



   #} Store ZBS Dashboard User Display Preferences
   #} Set Transients for checking of the subscription status..
   add_action('wp_ajax_zbs_dash_setting', 'zbs_dash_setting');
   function zbs_dash_setting(){

   		check_ajax_referer( 'zbs_dash_setting', 'security' );  //nonce it up...

   		// perms?
   		if (zeroBSCRM_permsCustomers()){

	   		// acceptable opts - from /includes/ZeroBSCRM.AdminPages.php 
	   		$acceptableSettingKeys = array('settings_dashboard_total_contacts','settings_dashboard_total_leads','settings_dashboard_total_customers','settings_dashboard_total_transactions','settings_dashboard_sales_funnel','settings_dashboard_revenue_chart','settings_dashboard_recent_activity','settings_dashboard_latest_contacts');

	   		// retrieve
	   		$cid = get_current_user_id();
	   		$settingKey = sanitize_text_field($_POST['the_setting']);
	   		
	   		if (in_array($settingKey, $acceptableSettingKeys)){

		   		$is_checked = (int)sanitize_text_field($_POST['is_checked']);
		   		if ($is_checked < 0) $is_checked = -1;

		   		// was/is storing these as str's of bools? weird. not sure why. For now, ducktape:
		   		if ($is_checked > 0) 
		   			$is_checked = 'true';
		   		else
		   			$is_checked = 'false';

		   		// update user meta, if legit.
		   		update_user_meta($cid,$settingKey,$is_checked);

				// No rights or failed key match
				zeroBSCRM_sendJSONSuccess(array('fini'=>1));

		   	}

		}

		// No rights or failed key match
		zeroBSCRM_sendJSONError(array('no-action-or-rights'=>1));
   }


   	# Generate New WP User...
	add_action('wp_ajax_zbs_new_user', 'zeroBSCRM_generateClientPortalUser');
	function zeroBSCRM_generateClientPortalUser(){
		
		// Nonce check
		check_ajax_referer( 'newwp-ajax-nonce', 'security' );

		// Perms check
		if (zeroBSCRM_permsCustomers()){	  	

			$email = ''; $cID = -1;
			if (isset($_POST['email']) && !empty($_POST['email'])) $email = sanitize_text_field($_POST['email']);
			if (isset($_POST['cid']) && !empty($_POST['cid'])) $cID = (int)sanitize_text_field($_POST['cid']);
			if (!zeroBSCRM_validateEmail($email)) $email = '';

			// $email_exists will be either false/int (id of wp user)
			$email_exists = email_exists( $email );

			if ( !empty($cID) && null == $email_exists && !empty($email)) {

				global $zbs;

				// retrieve fname, lname if available
				$fname = ''; $lname = '';
				if ($zbs->isDAL2()){

					$fields = $zbs->DAL->contacts->getContact($cID,array('withCustomFields' => false,'fields'=>array('zbsc_addr1','zbsc_prefix','zbsc_fname','zbsc_lname'),'ignoreowner' => true));

					if (isset($fields['fname'])) {
			            $fname = $fields['fname'];
			        }
			        if (isset($fields['lname'])) {
			            $lname = $fields['lname'];
			        }
				}

				// create user
				$created = zeroBSCRM_createClientPortalUser($cID,$email,12,$fname,$lname);

				$m['message'] = 'User Created';
				$m['success'] = true;
				$m['user_id'] = $created;
				echo json_encode($m);
				die();

			} else {

				// if has wp id, & contact ID is set
				if (is_int($email_exists) && $cID > 0){

					// link the user to the WordPress ID...
					zeroBSCRM_setClientPortalUser($cID,$email_exists);

				}

				$m['message'] = __('User Already Exists or Invalid Email','zero-bs-crm');
				$m['success'] = true;
				$m['email'] = $email;
				echo json_encode($m);
				die();

			}

		}
	}


   # apply action to portal user (enable disable)
	add_action('wp_ajax_zbsPortalAction', 'zeroBSCRM_AJAX_zbsPortalAction');
	function zeroBSCRM_AJAX_zbsPortalAction(){
		
		check_ajax_referer( 'zbsportalaction-ajax-nonce', 'security' );


		// can manage users?
		if (zeroBSCRM_permsCustomers()){

	  		// sanitize?
			$action = sanitize_text_field($_POST['portalAction']);
			$cID = (int)sanitize_text_field($_POST['cid']);
			if (!empty($action) && !empty($cID))

				switch ($action){

					//enable
					case 'enable':

						// fire dal enable
						zeroBSCRM_customerPortalDisableEnable($cID,'enable');

						// send success
						zeroBSCRM_sendJSONSuccess(array('success'=>1));


						break;
					//disable
					case 'disable':

						// fire dal disable
						zeroBSCRM_customerPortalDisableEnable($cID,'disable');

						// send success
						zeroBSCRM_sendJSONSuccess(array('success'=>1));

						break;
   					# Reset client portal password 
					case 'resetpw':

						// fire dal disable
						$newpw = zeroBSCRM_customerPortalPWReset($cID);

						// send success
						zeroBSCRM_sendJSONSuccess(array('success'=>1,'pw'=>$newpw));

						break;

				}

			}

		zeroBSCRM_sendJSONError(array('no-action-or-rights'=>1));
	}



	# AJAX email template population (as backup)
	add_action('wp_ajax_zbs_create_email_templates', 'zbs_create_email_templates');
	function zbs_create_email_templates(){
		check_ajax_referer( 'zbs_create_email_nonce', 'security' );
		#} only allow admin to do this?
		$m = array();
		if(zeroBSCRM_isZBSAdminOrAdmin()){
			zeroBSCRM_checkTablesExist();
			zeroBSCRM_populateEmailTemplateList();
			$m['message'] = "emails created";
		}else{
			$m['message'] = "no permissions";
		}
		echo json_encode($m);
		die();
	}


	# save email template 
	add_action('wp_ajax_zbs_save_email_status','zbs_save_email_status');
	function zbs_save_email_status(){

		$m = array(); 

		global $wpdb, $ZBSCRM_t;

		#} nonce..
		check_ajax_referer( 'zbs-save-email_active', 'security' );
		if(zeroBSCRM_isZBSAdminOrAdmin()){
			//our variables
			$the_id = (int)sanitize_text_field($_POST['id']);
			$a_or_i = sanitize_text_field($_POST['status']);

			//the emails are $ZBSCRM_t['system_mail_templates']

			if($a_or_i == 'a'){
				//turning active

				if ($wpdb->update( 
				$ZBSCRM_t['system_mail_templates'], 
				array( 
				'zbsmail_active' => 1,
				'zbsmail_lastupdated' 	=> time(),
				), 
				array( // where
				'zbsmail_id'  => $the_id,
				),
				array( 
				'%d',    //zbs_site
				'%d',    //zbs_team
				),
				array(
				'%d'
				)
				) !== false){

				$m['message'] = 'success turned active';
				$m['id'] = $the_id;
				$m['type'] = $a_or_i;

				} else {

				$m['message'] = 'insert failed';
				$m['id'] = $the_id;
				$m['type'] = $a_or_i;

				}



			}else if($a_or_i == 'i'){


			if ($wpdb->update( 
				$ZBSCRM_t['system_mail_templates'], 
				array( 
				'zbsmail_active' => 0,
				'zbsmail_lastupdated' 	=> time(),
				), 
				array( // where
				'zbsmail_id'  => $the_id,
				),
				array( 
				'%d',    //zbs_site
				'%d',    //zbs_team
				),
				array(
				'%d'
				)
				) !== false){

				$m['message'] = 'success turned inactive';
				$m['id'] = $the_id;
				$m['type'] = $a_or_i;

				} else {

				$m['message'] = 'insert failed';
				$m['id'] = $the_id;
				$m['type'] = $a_or_i;

				}



			}
		}else{
			$m['message'] = 'no perms';
		}

		echo json_encode($m);
		die();
		//nonce field is zbs-save-email_active

	}


##WLREMOVE
// Wizard Finish Step
// AJAX function for installing demo content
add_action( 'wp_ajax_nopriv_zbs_wizard_fin', 'zbs_wizard_fin' );
add_action( 'wp_ajax_zbs_wizard_fin', 'zbs_wizard_fin' );
function zbs_wizard_fin() {

	// nonce to bounce out if not from right page
	check_ajax_referer( 'zbswf-ajax-nonce', 'security' );
	// only admin can do this too (extra security layer)
	if ( current_user_can( 'manage_options' ) ) {

		global $zbs;

		// Retrieve post
		$crm_name = sanitize_text_field( $_POST['zbs_crm_name'] );
		$crm_curr = sanitize_text_field( $_POST['zbs_crm_curr'] ); // GBP
		$crm_type = sanitize_text_field( $_POST['zbs_crm_type'] );
		$crm_other = sanitize_text_field( $_POST['zbs_crm_other'] );
		$crm_menu_style = (int)sanitize_text_field( $_POST['zbs_crm_menu_style'] ); // 1 2 3
		$crm_share = empty( $_POST['zbs_crm_share_essentials'] ) ? 0 : 1;

		$crm_enable_quotes = empty( $_POST['zbs_quotes'] ) ? 0 : 1;
		$crm_enable_invoices = empty( $_POST['zbs_invoicing'] ) ? 0 : 1;
		$crm_enable_woo_module = empty( $_POST['jpcrm_woo_module'] ) ? 0 : 1;

		$bn = sanitize_text_field( $_POST['zbs_crm_subblogname'] );
		$fn = sanitize_text_field( $_POST['zbs_crm_first_name'] );
		$ln = sanitize_text_field( $_POST['zbs_crm_last_name'] );
		$em = sanitize_text_field( $_POST['zbs_crm_email'] );
		$emv = zeroBSCRM_validateEmail( $em );
		$crm_sub = empty( $_POST['zbs_crm_subscribed'] ) ? 0 : 1;

		// Just to pass for smm:
		$crm_enable_forms = 1;
		$crm_override = 0;
		$crm_url = '';

		// Save down initial options as option bk
		$initOptions = array(
			'share' => $crm_share,
			'bn'    => $bn,
			'fn'    => $fn,
			'ln'    => $ln,
			'em'    => $em,
			'emv'   => $emv,
			'smm'   => time(),
			'n'     => $crm_name,
			'u'     => $crm_url,
			'o'     => $crm_other,
			's'     => $crm_sub,
			't'     => $crm_type,
			'ov'    => $crm_override,
			'eq'    => $crm_enable_quotes,
			'ei'    => $crm_enable_invoices,
			'ef'    => $crm_enable_forms,
			'ew'    => $crm_enable_woo_module,
			'ems'   => $crm_menu_style,
			'v'     => $zbs->version,
			'cu'    => $crm_curr,
		);
		update_option( 'zbs_initopts_' . time(), $initOptions, false );

		// Note: this only shares if "share essentials" has been ticked...
		// ... or email subscribe (where upon our server ignores customer data except email sub details)
		if ( is_callable( 'curl_init' ) && ( $crm_share === 1 || $crm_sub === 1 ) ) {

			$crm_url = home_url();
			$current_user = wp_get_current_user();

			// pass whether we are sharing essentials
			$m = $initOptions;

			$response = wp_remote_post(
				$zbs->urls['smm'],
				array(
					'method'      => 'POST',
					'timeout'     => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array(),
					'body'        => $m,
					'cookies'     => array(),
				)
			);

		}

		// Header text
		$zbs->settings->update( 'customheadertext', $crm_name );

		// load currency list
		global $whwpCurrencyList;
		if ( !isset( $whwpCurrencyList ) ) {
			require_once( ZEROBSCRM_INCLUDE_PATH . 'wh.currency.lib.php' );
		}

		// Currency (Grim but will work for now)
		$currSetting = array( 'chr' => '$', 'strval' => 'USD' );
		if ( !empty( $crm_curr ) ) {
			foreach ( $whwpCurrencyList as $currencyObj ) {
				if ( $currencyObj[1] === $crm_curr ) {
					$currSetting['chr'] = $currencyObj[0];
					$currSetting['strval'] = $currencyObj[1];
					break;
				}
			}
		}

		// Save currency
		$zbs->settings->update( 'currency', $currSetting );

		// Save Share Essentials
		$zbs->settings->update( 'shareessentials', $crm_share );

		// Menu style
		switch ( $crm_menu_style ) {

			case 1:
				// full (normal) wp
				$zbs->settings->update( 'menulayout', 1 );
				break;

			case 2:
				// slimline
				$zbs->settings->update( 'menulayout', 2 );
				break;

			case 3:
				// override
				$zbs->settings->update( 'wptakeovermodeforall', 1 );
				$zbs->settings->update( 'menulayout', 3 );
				break;

		}

		// Enable/disable extensions
		if ( $crm_enable_quotes === 1 ) {
			zeroBSCRM_extension_install_quotebuilder();
		} else {
			zeroBSCRM_extension_uninstall_quotebuilder();
		}

		if ( $crm_enable_invoices === 1 ) {

			zeroBSCRM_extension_install_invbuilder();
			// This assumes they want pdf inv too ;)
			zeroBSCRM_extension_install_pdfinv();

		} else {

			zeroBSCRM_extension_uninstall_invbuilder();

		}

		if ( $crm_enable_forms === 1 ) {
			zeroBSCRM_extension_install_forms();
		} else {
			zeroBSCRM_extension_uninstall_forms();
		}

		if ( $crm_enable_woo_module === 1 ) {
			zeroBSCRM_extension_install_woo_sync();
		} else {
			zeroBSCRM_extension_uninstall_woo_sync();
		}

		// Tax tables, defaults
		// added basic in v3.0, this would be great to expand if we get operational country (assume by ip?)
		// based on currency, not ideal.
		$currentTaxTables = zeroBSCRM_taxRates_getTaxTableArr();
		if ( is_array( $currentTaxTables ) && count( $currentTaxTables ) === 0 && is_array( $currSetting ) && isset( $currSetting['strval'] ) ) {

			$ratesToAdd = array();

			// this can be factored out into a single 'setup packs' file ++
			switch ( $currSetting['strval'] ) {

				case 'USD':
					// state based, MEH. leave for v3.1+
					break;

				case 'GBP':
					$ratesToAdd[] = array(
						'name' => 'VAT',
						'rate' => 20.0,
					);
					break;

			}

			// add any
			if ( count( $ratesToAdd ) > 0 ) {
				foreach ( $ratesToAdd as $rate ) {

					zeroBSCRM_taxRates_addUpdateTaxRate(
						array(
							// fields (directly)
							'data' => $rate,
						)
					);
				}
			}

		}

		// log successful wizard completion
		update_option( 'jpcrm_wizard_completed', 1 );

		$r['message'] = 'success';
		$r['success'] = 1;
		echo json_encode( $r );
		die();
	} else {
		$r['message'] = 'Unauthorised to do this...';
		$r['success'] = 0;
		echo json_encode( $r );
		die();
	}
}
##/WLREMOVE

	#} General App Helpers - log user closing a modal (see also zeroBSCRM_getCloseState)
	// basically log a dismissed dialog..
	add_action( 'wp_ajax_logclose', 'zeroBSCRM_AJAX_logClose' );
	function zeroBSCRM_AJAX_logClose(){

		#} Check nonce
		check_ajax_referer( 'zbscrmjs-glob-ajax-nonce', 'sec' );  //nonce to bounce out if not from right page

		if(zeroBSCRM_permsCustomers()){
			#} This is a list of keys that can be "set"
			#} e.g. if this is fired for "pdfinvinstall" it's saying user has X'd the "Want to install PDF invoicing? modal from Invoice builder"
			$potentialClosers = array('pdfinvinstall','v3prep2997');
			$potentialKey = ''; if (isset($_POST['closing']) && !empty($_POST['closing']) && in_array($_POST['closing'],$potentialClosers)) $potentialKey = sanitize_text_field($_POST['closing']);

			#} Only has one val, sets as the time...

			#} Brutally add option
			update_option('zbs_closers_'.$potentialKey,time(), false);
		}

		header('Content-Type: application/json');
		echo json_encode(array('fini'=>1));
		exit();
	}


	/*
	* set_jpcrm_transient
	* Sets a JPCRM transient
	*/
	add_action( 'wp_ajax_jpcrmsettransient', 'jpcrm_set_jpcrm_transient' );
	function jpcrm_set_jpcrm_transient(){

		// Check Nonce
		check_ajax_referer( 'jpcrm-set-transient-nonce', 'sec' );

		// Check permissions
		// > Backend JPCRM user or WP Admin
		if ( zeroBSCRM_permsIsZBSUserOrAdmin() ){

			global $zbs;

			// retrieve data
			$transientKey = ''; 
			$transientValue = '';
			$transientExpiration = 0;

			if ( isset($_POST['transient-key']) && !empty($_POST['transient-key']) ) {
			
				$transientKey = sanitize_text_field($_POST['transient-key']);
			
			}

			if ( isset($_POST['transient-value']) && !empty($_POST['transient-value']) ) {
			
				$transientValue = sanitize_text_field($_POST['transient-value']);
			
			}

			if ( isset($_POST['transient-expiration']) && !empty($_POST['transient-expiration']) ) {
			
				$transientExpiration = (int)sanitize_text_field($_POST['transient-expiration']);
			
			}

			// Check that this transient is on the "allowed list"
			if ( !empty($transientKey) && array_key_exists($transientKey,$zbs->transients) ){

				// within our realm, set
				set_transient($transientKey, $transientValue, $transientExpiration);

			}
			
		}

		zeroBSCRM_sendJSONSuccess(array('fini'=>1));

	}


	#} Feedback
	add_action( 'wp_ajax_markFeedback', 'zeroBSCRM_AJAX_markFeedback' );
	function zeroBSCRM_AJAX_markFeedback(){

		if(zeroBSCRM_permsCustomers()){
			$feedbackVal = 'nope'; if (isset($_POST['feedbackgiven'])) $feedbackVal = 'yep';
			update_option('zbsfeedback',$feedbackVal, false);
		}
		header('Content-Type: application/json');
		echo json_encode(array('fini'=>1));
		exit();
	}



	#} Retrieve list of invoice deets for customer ID
	add_action( 'wp_ajax_getinvs', 'zeroBSCRM_AJAX_getCustInvs' );
	function zeroBSCRM_AJAX_getCustInvs(){

		#} Check nonce
		check_ajax_referer( 'zbscrmjs-glob-ajax-nonce', 'sec' );  //nonce to bounce out if not from right page

		$ret = array();

		#} If perms?
		if (zeroBSCRM_permsCustomers()){

			#} Retrieve ID
			$cID = -1; if (isset($_POST['cid'])) $cID = (int)sanitize_text_field($_POST['cid']);

			if ($cID > 0){

				#} Retrieve the customers invoices:
				$ret = zeroBS_getInvoicesForCustomer($cID,true,100);

			}

		}

		header('Content-Type: application/json');
		echo json_encode($ret);
		exit();
	}



	#} Remove file
	add_action( 'wp_ajax_delFile', 'zeroBSCRM_removeFile' );
	function zeroBSCRM_removeFile(){

		#} req
		$res = false; $errors = array();

		#} Check nonce
		check_ajax_referer( 'zbscrmjs-ajax-nonce', 'sec' );

		#} Check perms
		if (
			($_POST['zbsfType'] == 'customer' && zeroBSCRM_permsCustomers()) ||
			($_POST['zbsfType'] == 'company' && zeroBSCRM_permsCustomers()) ||
			($_POST['zbsfType'] == 'quotes' && zeroBSCRM_permsQuotes()) ||
			($_POST['zbsfType'] == 'invoices' && zeroBSCRM_permsInvoices()) 
			){

			#} Retrieve deets
		    if (isset($_POST['zbsDel']) && !empty($_POST['zbsDel'])){

		    	#} Type? ID?
		    	if (isset($_POST['zbsCID']) && !empty($_POST['zbsCID'])) {

		    		$objectID = (int)sanitize_text_field( $_POST['zbsCID'] );
		    		$fileType = sanitize_text_field( $_POST['zbsfType'] ); # assured as checked by if above (customer, quotes, invoices)
		    		$zbsDel = sanitize_text_field($_POST['zbsDel']);

			        #} potentially csv of to-delete
			        if (strpos('#'.$zbsDel,',') > 0)
			            $delFiles = explode(',',$zbsDel);
			        else
			            $delFiles = array($zbsDel);

			        if (count($delFiles) > 0) foreach ($delFiles as $delFile){

			            $deleted = zeroBS_removeFile($objectID,$fileType,$delFile);
			            if ($deleted !== true) $errors[] = $deleted;

			        }

			        $res = true;

			    }


		    }


		}


		header('Content-Type: application/json');
		echo json_encode(array('res'=>$res,'errors'=>$errors));
		exit();
	} 



	#} Filter customers + retrieve count
	add_action( 'wp_ajax_filterCustomers', 'zeroBSCRM_AJAX_filterCustomers' );
	function zeroBSCRM_AJAX_filterCustomers(){

		#} req
		$res = false;

		#} Check nonce
		check_ajax_referer( 'zbscrmjs-ajax-nonce', 'sec' );

		if (!zeroBSCRM_permsCustomers()) exit('{processed:-1}');


		#} Running this auto-pulls POSTED filters + finds customers

			#} Apply filters - it's funky to have to force this :/
			global $zbsCustomerFiltersInEffect;
			$zbsCustomerFiltersInEffect = zbs_customerFiltersGetApplied(); 

			#} Retrieve
			$res = zeroBS__customerFiltersRetrieveCustomerCountAndTopCustomers();
			$res['filters_in_effect'] = $zbsCustomerFiltersInEffect;


		header('Content-Type: application/json');
		echo json_encode($res);
		exit();
	}




	// Add log
	add_action( 'wp_ajax_zbsaddlog', 'zeroBSCRM_AJAX_addLog' );
	function zeroBSCRM_AJAX_addLog() {

		header( 'Content-Type: application/json' );

		// req
		$res = -1;

		// Check nonce
		check_ajax_referer( 'zbscrmjs-ajax-nonce-logs', 'sec' );

		// brutal
		if ( !zeroBSCRM_permsCustomers() ) {
			exit('{processed:-1}');
		}


		// Retrieve vars - this allows notes against ALL post types (just by id)
		if ( !empty( $_POST['zbsnagainstid'] ) ) {
			$zbsNoteAgainstPostID = (int)sanitize_text_field( $_POST['zbsnagainstid'] );
		}
		if ( !empty( $_POST['zbsntype'] ) ) {
			$zbsNoteType = sanitize_text_field( $_POST['zbsntype'] );
		}
		if ( !empty( $_POST['zbsnshortdesc'] ) ) {
			$zbsNoteShortDesc = zeroBSCRM_preDBStr(sanitize_text_field( $_POST['zbsnshortdesc'] ) );
		}

		$zbsNoteLongDesc = '';
		if ( !empty( $_POST['zbsnlongdesc'] ) ) {
			$zbsNoteLongDesc = zeroBSCRM_preDBStr( zeroBSCRM_textProcess( zeroBSCRM_stripExceptLineBreaks( nl2br( $_POST['zbsnlongdesc'] ) ) ) );
		}

		$zbsNoteObjType = '';
		if ( !empty( $_POST['zbsnobjtype'] ) ) {
			$zbsNoteObjType = zeroBSCRM_textProcess( $_POST['zbsnobjtype'] );
		}
		
		// optional: logid to overwrite:
		$zbsNoteIDtoUpdate = -1;
		if ( !empty( $_POST['zbsnoverwriteid'] ) ) {
			$zbsNoteIDtoUpdate = (int)sanitize_text_field($_POST['zbsnoverwriteid']);
		}

		// Validate
		if (
			!empty( $zbsNoteAgainstPostID ) && $zbsNoteAgainstPostID > 0 && 
			!empty( $zbsNoteType ) &&
			!empty( $zbsNoteShortDesc )
		) {

			// Only raw checked... but proceed. (ADD or Update?) (if $zbsNoteIDtoUpdate = -1 it'll add, else it'll overwrite)
			$res = zeroBS_addUpdateLog(
				$zbsNoteAgainstPostID,
				$zbsNoteIDtoUpdate,
				-1,
				array(
					// Anything here will get wrapped into an array and added as the meta vals
					'type'      => $zbsNoteType,
					'shortdesc' => $zbsNoteShortDesc,
					'longdesc'  => $zbsNoteLongDesc,
				),
				$zbsNoteObjType
			);

		}

		echo json_encode( array( 'processed' => $res ) );
		exit();
	}


	// Update log
	add_action( 'wp_ajax_zbsupdatelog', 'zeroBSCRM_AJAX_updateLog' );
	function zeroBSCRM_AJAX_updateLog(){

		header( 'Content-Type: application/json' );

		// req
		$res = -1;

		// Check nonce
		check_ajax_referer( 'zbscrmjs-ajax-nonce-logs', 'sec' );

		// brutal
		if ( !zeroBSCRM_permsLogsAddEdit() ) {
			exit('{processed:-1}');
		}

		// Retrieve vars - this allows notes against ALL post types (just by id)
		if ( !empty( $_POST['zbsnprevid'] ) ) {
			$zbsNoteID = (int)sanitize_text_field( $_POST['zbsnprevid'] );
		}
		if ( !empty( $_POST['zbsnagainstid'] ) ) {
			$zbsNoteAgainstPostID = (int)sanitize_text_field( $_POST['zbsnagainstid'] );
		}
		if ( !empty( $_POST['zbsntype'] ) ) {
			$zbsNoteType = sanitize_text_field( $_POST['zbsntype'] );
		}
		if ( !empty( $_POST['zbsnshortdesc'] ) ) {
			$zbsNoteShortDesc = zeroBSCRM_preDBStr( sanitize_text_field( $_POST['zbsnshortdesc'] ) );
		}

		$zbsNoteLongDesc = '';
		if ( !empty( $_POST['zbsnlongdesc'] ) ) {
			$zbsNoteLongDesc = zeroBSCRM_preDBStr( zeroBSCRM_textProcess( zeroBSCRM_stripExceptLineBreaks( nl2br( $_POST['zbsnlongdesc'] ) ) ) );
		}

		$zbsNoteObjType = '';
		if ( !empty( $_POST['zbsnobjtype'] ) ) {
			$zbsNoteObjType = zeroBSCRM_textProcess( $_POST['zbsnobjtype'] );
		}

		// Validate
		if (
			!empty( $zbsNoteID ) && $zbsNoteID > 0 && 
			!empty( $zbsNoteAgainstPostID ) && $zbsNoteAgainstPostID > 0 && 
			!empty( $zbsNoteType ) &&
			!empty( $zbsNoteShortDesc )
		) {

			// Only raw checked... but proceed. (Update?) (if $zbsNoteIDtoUpdate = -1 it'll add, else it'll overwrite)
			$newOrUpdatedLogID = zeroBS_addUpdateLog(
				$zbsNoteAgainstPostID,
				$zbsNoteID,
				-1,
				array(
					// Anything here will get wrapped into an array and added as the meta vals
					'type'      => $zbsNoteType,
					'shortdesc' => $zbsNoteShortDesc,
					'longdesc'  => $zbsNoteLongDesc,
				),
				$zbsNoteObjType
			);

			$res = $newOrUpdatedLogID;

			// Internal Automator
			if ( !empty( $res ) ) {
				zeroBSCRM_FireInternalAutomator(
					'log.update',
					array(
						'id'            =>$zbsNoteID,
						'logagainst'    =>$zbsNoteAgainstPostID,
						'logtype'       => $zbsNoteType,
						'logshortdesc'  => $zbsNoteShortDesc,
						'loglongdesc'   => $zbsNoteLongDesc,
					)
				);
			}

		}

		echo json_encode( array( 'processed' => $res ) );
		exit();
	}

	#} Del log
	add_action( 'wp_ajax_zbsdellog', 'zeroBSCRM_AJAX_deleteLog' );
	function zeroBSCRM_AJAX_deleteLog(){

		header('Content-Type: application/json');

		#} req
		$res = -1;

		#} Check nonce
		check_ajax_referer( 'zbscrmjs-ajax-nonce-logs', 'sec' );

		#} brutal
	    // from 2.94.2 uses sub perms
	    // if (!zeroBSCRM_permsCustomers()) exit('{processed:-1}');
	    if (!zeroBSCRM_permsLogsDelete()) exit('{processed:-1}');
	    #if (!current_user_can('edit_page', $post_id)) return;


		#} Retrieve vars - this allows notes against ALL post types (just by id)
		if (isset($_POST['zbsnid']) && !empty($_POST['zbsnid'])) $zbsNoteID = (int)sanitize_text_field($_POST['zbsnid']);

		#} Validate
		if (
			isset($zbsNoteID) &&
			!empty($zbsNoteID)
		){

			global $zbs;

			#} Brutal
			if ($zbs->isDAL2()){
				$res = $zbs->DAL->logs->deleteLog(array('id'=>$zbsNoteID));
			} else {

				// DAL 1
				$res = wp_delete_post($zbsNoteID,false); #} Don't force delete (leaves a kind of audit trail for now...?)

				if (isset($res) && isset($res->ID)) {

					$res = 1;

					#} Internal Automator
					zeroBSCRM_FireInternalAutomator('log.delete',array(
						'id'=>$zbsNoteID
						));
					
				} else $res = -1;

			}

		}


		echo json_encode(array('processed'=>$res));
		exit();
	}

/* ======================================================
	/ Admin AJAX
====================================================== */







/* ======================================================
	Admin AJAX: Quote Builder
====================================================== */

add_action( 'wp_ajax_zbs_get_quote_template', 'ZeroBSCRM_get_quote_template' );
function ZeroBSCRM_get_quote_template(){

	#} Starting
	$content = array();

	#} Check nonce
	check_ajax_referer( 'quo-ajax-nonce', 'security' );  //nonce..

	#} brutal
	if (!zeroBSCRM_permsCustomers()) exit('{processed:-1}');
	if (!zeroBSCRM_permsQuotes()) exit('{processed:-1}');

	#} Retrive deets
	$customer_ID = -1; if (isset($_POST['cust_id'])) $customer_ID = (int)sanitize_text_field($_POST['cust_id']);
	$quote_template_ID = -1; if (isset($_POST['quote_type'])) $quote_template_ID = (int)sanitize_text_field($_POST['quote_type']);
	
	// <DAL3
	$quote_title = ''; if (isset($_POST['quote_title'])) $quote_title = sanitize_text_field($_POST['quote_title']);
	$quote_val = ''; if (isset($_POST['quote_val'])) $quote_val = sanitize_text_field($_POST['quote_val']);
	$quote_date = ''; if (isset($_POST['quote_dt'])) $quote_date = sanitize_text_field($_POST['quote_dt']);

	#} needs at least customer id + template id
	if ($customer_ID !== -1 && $quote_template_ID !== -1){

		global $zbs;

		// DEBUG: print_r($_POST['quote_fields']); exit();
		// DAL3+ takes all quote inputs into account and fills out based on these (quote_fields), not above
		if (isset($_POST['quote_fields']) && $zbs->isDAL3() && is_array($_POST['quote_fields'])){

			// retrieve basics over above
			if (isset($_POST['quote_fields']['zbscq_title']) && !empty($_POST['quote_fields']['zbscq_title'])) $quote_title = sanitize_text_field( $_POST['quote_fields']['zbscq_title'] );
			if (isset($_POST['quote_fields']['zbscq_value']) && !empty($_POST['quote_fields']['zbscq_value'])) $quote_val = sanitize_text_field( $_POST['quote_fields']['zbscq_value'] );
			if (isset($_POST['quote_fields']['zbscq_date']) && !empty($_POST['quote_fields']['zbscq_date'])) $quote_date = sanitize_text_field( $_POST['quote_fields']['zbscq_date'] );
		}

		#} Fill out rest
		$your_biz_name = zeroBSCRM_getSetting('businessname');
		$customerName = zeroBS_getCustomerNameShort($customer_ID);
		$contact_object = zeroBS_getCustomer( $customer_ID );
		#$customerMeta = zeroBS_getCustomerMeta($customer_ID);
		#$fname = $customerMeta['fname'];
		#$lname = $customerMeta['lname'];
		$bizState = '[STATE]'; # NOT EASILY ACCESSIBLE FROM YOUR SETTINGS... suggest we add to inv settings, addr proper.

		// load templater
		$placeholder_templating = $zbs->get_templating();

		#} Load template
		$quoteTemplate = zeroBS_getQuoteTemplate($quote_template_ID);

		if (isset($quoteTemplate) && is_array($quoteTemplate) && isset($quoteTemplate['content'])){

			// if no title/value is passed at this point, but there is one seet in quote template, we should use those values
			if ( empty( $quote_title )  && !empty ( $quoteTemplate['title'] )) $quote_title = $quoteTemplate['title'];
			if ( empty( $quote_val )  && !empty ( $quoteTemplate['value'] )) $quote_val = $quoteTemplate['value'];
			
			// catch empty pass...
			if (empty($quote_title)) $quote_title = '[QUOTETITLE]';
			if (empty($quote_val)) $quote_val = '[QUOTEVALUE]';
			if (empty($quote_date)) $quote_date = date('d/m/Y',time());

			$workingHTML = zeroBSCRM_io_WPEditor_DBToHTML($quoteTemplate['content']);

			// replacements
			$replacements = $placeholder_templating->get_generic_replacements();

			$replacements['quote-title'] = $quote_title;
			$replacements['quote-value'] = zeroBSCRM_formatCurrency($quote_val);
			$replacements['quote-date'] = $quote_date;
			$replacements['biz-state'] = $bizState;
			$replacements['contact-fullname'] = $customerName;

			$workingHTML = $placeholder_templating->replace_placeholders( array(  'global', 'contact', 'quote' ), $workingHTML, $replacements, array( ZBS_TYPE_CONTACT => $contact_object ) );

			// if DAL3, also replace any custom fields
			if (isset($_POST['quote_fields']) && $zbs->isDAL3() && is_array($_POST['quote_fields'])){

				//$cF = $zbs->settings->get('customfields');
				$cF = $zbs->DAL->getActiveCustomFields(array('objtypeid'=>ZBS_TYPE_QUOTE));
				
				if (isset($cF) && is_array($cF)){ // &&isset($cF['quotes'])

					foreach ($cF as $k => $f){ //['quotes']

						// annoyingly proper key is stored in [3] ?
						$key = ''; if (is_array($f) && isset($f[3])) $key = $f[3];

						if (!empty($key)){

							$v = ''; if (isset($_POST['quote_fields']['zbscq_'.$key])) $v = sanitize_text_field( $_POST['quote_fields']['zbscq_'.$key] );

							// allow upper or lower to catch various uses
							$workingHTML = str_replace('##QUOTE-'.strtoupper($key).'##',$v,$workingHTML);
							$workingHTML = str_replace('##QUOTE-'.strtolower($key).'##',$v,$workingHTML);
							$workingHTML = str_replace('##quote-'.strtolower($key).'##',$v,$workingHTML);

						}

					}
				}

			}
			#} replace the rest (#fname, etc)
			// WH: moved to nice filter :) $workingHTML = zeroBSCRM_replace_customer_placeholders($customer_ID, $workingHTML);
			$workingHTML = apply_filters( 'zerobscrm_quote_html_generate', $workingHTML, $customer_ID );


			#} set return
			$content['html'] = $workingHTML;
			$content['template_title'] = $quoteTemplate['title'];
			$content['template_value'] = $quoteTemplate['value'];
			$content['template_notes'] = $quoteTemplate['notes'];

			#} return
			wp_send_json($content);

		} // / if content

	} // / if vars

	wp_send_json(array('error'=>1));
}




// Send a quote via email
add_action( 'wp_ajax_jpcrm_quotes_send_quote', 'jpcrm_ajax_quote_send_email' );
function jpcrm_ajax_quote_send_email(){

	// Check nonce
	check_ajax_referer( 'edit-nonce-quote', 'sec' );

	// Check Permissions
    if (!zeroBSCRM_permsCustomers()) exit('{processed:-1}');
    if (!zeroBSCRM_permsQuotes()) exit('{processed:-1}');

    // Retrive details
    $quoteID = -1; 		if (isset($_POST['qid'])) $quoteID =  (int)sanitize_text_field($_POST['qid']);
    $target_email = ''; if (isset($_POST['em'])) $target_email =  sanitize_text_field($_POST['em']);
    $contactID = -1; 	if (isset($_POST['cid'])) $contactID =  (int)sanitize_text_field($_POST['cid']);
    $companyID = -1; 	if (isset($_POST['coid'])) $companyID =  (int)sanitize_text_field($_POST['coid']); // track if companyID - not wired in via fronend yet, but will work
	$attachAssignedDocs = false; $attachAsPDF = false;
	if (isset($_POST['attachassoc']) && $_POST['attachassoc'] == 1) $attachAssignedDocs = true;
	if (isset($_POST['attachpdf']) && $_POST['attachpdf'] == 1) $attachAsPDF = true;

	// validate the email
	if (!zeroBSCRM_validateEmail($target_email) || empty($target_email)){
		zeroBSCRM_sendJSONError( array( 'message' => __('Invalid email','zero-bs-crm') ), 400 );
	}

	// Check id
	if ($quoteID == -1){
		zeroBSCRM_sendJSONError( array( 'message' => __('Invalid parameters','zero-bs-crm') ), 400 );
	}

	global $zbs;

	// rather than provide backward compatibility here, we're only supporting v3 DAL onward for quote email sending.	
	if (!$zbs->isDAL3()){
		zeroBSCRM_sendJSONError( array( 'message' => __('Requires CRM update','zero-bs-crm') ), 400 );
	}


	// as of 4.0.8 no need to check if the email template is switched to active.. (always is)
	//$active = zeroBSCRM_get_email_status(ZBSEMAIL_NEWQUOTE);

	// retrieve quote
	$quote = $zbs->DAL->quotes->getQuote($quoteID,array(
            'withLineItems'     => true,
            'withCustomFields'  => true,
            'withAssigned'      => true,
            'withTags'          => true,
            'withOwner'         => true,
            'withFiles'			=> true
    ));

	// retrieve assoc records
	// .. this would lead tracking to assign to whomever is assigned the quote, yet we pass this from front-end, arguably this makes more sense, but leaving for us to finalise contact<->company
 	// $contactID = -1;  if (is_array($quote) && isset($quote['contact']) && is_array($quote['contact']) && count($quote['contact']) > 0) $contactID = $quote['contact'][0]['id'];
 	// $companyID = -1;  if (is_array($quote) && isset($quote['company']) && is_array($quote['company']) && count($quote['company']) > 0) $companyID = $quote['company'][0]['id'];

    // ==========================================================================================
    // =================================== MAIL SENDING =========================================

	// Attachments?
    $attachments = array();
    if ($attachAssignedDocs){
    	if (isset($quote['files']) && is_array($quote['files']) && count($quote['files']) > 0){

    		// cycle through files + add as attachments
    		// we pass as 2part array so they don't have their funky md5 prefixes..
    		foreach($quote['files'] as $file){

                $filename = basename($file['file']);
                // if in privatised system, ignore first hash in name
                if (isset($file['priv'])){

                    $filename = substr($filename,strpos($filename, '-')+1);
                }

                $attachments[] = array($file['file'],'x'.$filename);

            }
    	}
    }

    // Attach as PDF?
    if ($attachAsPDF){

    	// make pdf.
        $pdf_file = jpcrm_quote_generate_pdf($quoteID);

        // attach it
        if ($pdf_file !== false){

            $attachments[] = array($pdf_file,'quote.pdf');

        }

        // NOTE: for security / hygiene, we delete this PDF after email is sent

    }


    // generate html
    $emailHTML = zeroBSCRM_quote_generateNotificationHTML($quoteID,true);

      // build send array
      $mailArray = array(
        'toEmail' => $target_email,
        'toName' => '',
        'subject' => zeroBSCRM_mailTemplate_getSubject(ZBSEMAIL_NEWQUOTE),
        'headers' => zeroBSCRM_mailTemplate_getHeaders(ZBSEMAIL_NEWQUOTE),
        'body' => $emailHTML,
        'textbody' => '',
        'attachments' => $attachments,
        'options' => array(
          'html' => 1
        )
      );

      // track if contactID
      if ($contactID > 0){

      	// senderWPID = -12 = new quote email to contact
        $mailArray['tracking'] = array( 
          // tracking :D (auto-inserted pixel + saved in history db)
          'emailTypeID' => ZBSEMAIL_NEWQUOTE,
          'targetObjID' => $contactID,
          'senderWPID' => -12,
          'associatedObjID' => $quoteID
        );

      }

      // track if companyID - not wired in via fronend yet, but will work
      if ($companyID > 0){

      	// senderWPID = -17 = new quote email to company
        $mailArray['tracking'] = array( 
          // tracking :D (auto-inserted pixel + saved in history db)
          'emailTypeID' => ZBSEMAIL_NEWQUOTE,
          'targetObjID' => $companyID,
          'senderWPID' => -17,
          'associatedObjID' => $quoteID
        );

      }

      // Sends email, including tracking, via setting stored route out, (or default if none)
      // and logs trcking :)

        // discern delivery method
        $mailDeliveryMethod = zeroBSCRM_mailTemplate_getMailDelMethod(ZBSEMAIL_NEWQUOTE);
        if (!isset($mailDeliveryMethod) || empty($mailDeliveryMethod)) $mailDeliveryMethod = -1;

        // send
        $sent = zeroBSCRM_mailDelivery_sendMessage($mailDeliveryMethod,$mailArray);

        // delete any gen'd pdf's
        if ($attachAsPDF && $pdf_file !== false){

            // delete the PDF file once it's been read (i.e. emailed)
            unlink($pdf_file); 

        }

    // =================================== / MAIL SENDING =======================================
    // ==========================================================================================


    if ($sent){

	    // send result
		zeroBSCRM_sendJSONSuccess(array('message'=>'sent'));

	} else {

		// send err
	    zeroBSCRM_sendJSONError(array('message'=>__('not sent','zero-bs-crm')));

	}

	exit();

}


/**
* AJAX: Accept a Quote
* Quotes can be accepted by logged-in users or via easy-access links
*/
add_action( 'wp_ajax_nopriv_zbs_quotes_accept_quote', 	'ZeroBSCRM_accept_quote' );
add_action( 'wp_ajax_zbs_quotes_accept_quote', 			'ZeroBSCRM_accept_quote' );

function ZeroBSCRM_accept_quote() {
	// We probably want to see all errors:
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	#} Check nonce
	check_ajax_referer( 'zbscrmquo-nonce', 'sec' );
	
	$quoteID = isset( $_POST['zbs-quote-id'] ) ? (int) $_POST["zbs-quote-id"] : 0;

	#} Got quote ID?
	if ( empty( $quoteID ) || $quoteID < 0 ) {
		zeroBSCRM_sendJSONError( array( 'noparams' => 1 ), 400 );
	} // / posted data

	// If nonced & has quote id, verify user can 'accept'
	// .. either has quoteHASH which matches ID, (easy access)
	// .. or is logged in client

	// easy access links? (hashed)
	$quoteHash =  zeroBSCRM_getSetting( 'easyaccesslinks' ) && isset($_POST["zbs-quote-hash"]) 
		? sanitize_text_field($_POST["zbs-quote-hash"]) 
		: '';

	// Either easy access links are disabled or no hash is supplied
	if ( empty( $quoteHash ) ) {
		$uinfo = wp_get_current_user();

		// validate that this has been posted by the contact associated with the quote
		if ( ! $uinfo->ID 
			|| zeroBS_getCustomerIDWithEmail( $uinfo->user_email ) !== zeroBSCRM_quote_getContactAssigned( $quoteID )
			|| zeroBSCRM_permsQuotes()
		) {
			zeroBSCRM_sendJSONError( array( 'access'=>1 ), 403 );
		}
	} elseif ( ! zeroBSCRM_quotes_getFromHash( $quoteHash, -1 )['success'] ) {
		zeroBSCRM_sendJSONError( [ 'hash' => 1 ], 403 );
	}

	// We can accept the quote 

	// mark quote as accepted
	zeroBS_markQuoteAccepted( $quoteID );

	// Send notification to creator/owner of quote
	// ..if the email notification for quote acceptence is active..
	if (zeroBSCRM_get_email_status(ZBSEMAIL_QUOTEACCEPTED)){

		// get owner details
		$quoteOwnerEmail = zeroBS_getObjOwnerWPEmail($quoteID,'zerobs_quote');

		if (!empty($quoteOwnerEmail) && zeroBSCRM_validateEmail($quoteOwnerEmail)){
			zbs_send_quote_accept_email( $quoteID, $quoteOwnerEmail );
		} // / if has owner with valid email

	} // / if email notification active

	// success
	zeroBSCRM_sendJSONSuccess(array('success'=>1));
}


/* ======================================================
	/ Admin AJAX: Quote Builder
====================================================== */

/**
 * Sends the notification emal to the quote owner, informing them that
 * the quote has been accepted.
 *
 * @param int $quoteID The ID of the accepted quote.
 * @param string $quoteOwnerEmail The email address to send the
 *  notification to.
 * @return array An array of one or two elements. The first is a boolean
 *  showing whether the email was successfully sent. The second is any
 *  error messoge.
 */
function zbs_send_quote_accept_email( $quoteID, $quoteOwnerEmail ) {

	$quoteOwnerWPID = zeroBS_getOwner($quoteID,false,ZBS_TYPE_QUOTE);

	// generate html
	$emailHTML = zeroBSCRM_quote_generateAcceptNotifHTML($quoteID,'',true);

	// build send array
	$mailArray = array(
		'toEmail' => $quoteOwnerEmail,
		'toName' => '',
		'subject' => zeroBSCRM_mailTemplate_getSubject(ZBSEMAIL_QUOTEACCEPTED),
		'headers' => zeroBSCRM_mailTemplate_getHeaders(ZBSEMAIL_QUOTEACCEPTED),
		'body' => $emailHTML,
		'textbody' => '',
		'options' => array(
			'html' => 1
		),
		'tracking' => array( 
			// tracking :D (auto-inserted pixel + saved in history db)
			'emailTypeID' => ZBSEMAIL_QUOTEACCEPTED,
			'targetObjID' => $quoteOwnerWPID,
			'senderWPID' => -11,
			'associatedObjID' => $quoteID // none
		)
	);

	// Sends email, including tracking, via setting stored route out, (or default if none)
	// and logs trcking :)

	// discern del method
	$mailDeliveryMethod = zeroBSCRM_mailTemplate_getMailDelMethod(ZBSEMAIL_QUOTEACCEPTED);
	if (!isset($mailDeliveryMethod) || empty($mailDeliveryMethod)) $mailDeliveryMethod = -1;

	// send
	return zeroBSCRM_mailDelivery_sendMessage($mailDeliveryMethod,$mailArray);
}



/* ======================================================
	Admin AJAX: Front End Forms
====================================================== */


	function zbs_lead_form_views(){

	    global $zbs;

		// fired via AJAX on page view (uniqued by cookie - test will send on each page refresh...)
		// will not have a nonce available since from another site. 
		// only passing a form ID (which is (int) set and then updating a counter
		$form_id = (int)sanitize_text_field($_POST['id']);   

        if( $zbs->isDAL3() ) {
            $form_views = $zbs->DAL->forms->add_form_view( $form_id );
        } else {
            $form_views = get_post_meta($form_id,'zbs_form_views',true);
            if($form_views == ''){
                $form_views = 1;
            }else{
                $form_views++;
            }
            update_post_meta($form_id, 'zbs_form_views', $form_views);
        }

		echo json_encode(array('view_logged'=>'true'));
		exit();

	}
	add_action('wp_ajax_nopriv_zbs_lead_form_views','zbs_lead_form_views');
	add_action( 'wp_ajax_zbs_lead_form_views', 'zbs_lead_form_views' );

	#} Handle form submissions interesting to see how this works cross domain...
	function zbs_lead_form_capture() {
	    /**
	     * At this point, $_GET/$_POST variable are available
	     *
	     * We can do our normal processing here
	     */ 
	   
	    global $zbs;

	   	#} Declare this...
	    $r = array();

	    // reCaptcha check first (if present):
		$reCaptcha = zeroBSCRM_getSetting('usegcaptcha');
		$reCaptchaKey = zeroBSCRM_getSetting('gcaptchasitekey');
		$reCaptchaSecret = zeroBSCRM_getSetting('gcaptchasitesecret');

		if ($reCaptcha && !empty($reCaptchaKey) && !empty($reCaptchaSecret)){

			#} Assume fail
			$reCaptchaOkay = false;
		   
			#} Retrieve from post
			$possibleCaptchaResponse = ''; if (isset($_POST['recaptcha']) && !empty($_POST['recaptcha'])) $possibleCaptchaResponse = sanitize_text_field($_POST['recaptcha']);

			#} Validate it
			$gSays = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
					    'method'      => 'POST',
					    'timeout'     => 45,
					    'redirection' => 5,
					    'httpversion' => '1.0',
					    'blocking'    => true,
					    'headers'     => array(),
					    'body'        => array(
											'secret' => $reCaptchaSecret,
											'response' => $possibleCaptchaResponse
											# not req 'remoteip' => zeroBSCRM_getRealIpAddr()
										),
					    'cookies'     => array()
					    )
					);

			#} Should be a response json obj
			if (!empty($gSays)) {
				#} get it
				$gSaysObj = json_decode(wp_remote_retrieve_body($gSays));

				if (isset($gSaysObj->success) && $gSaysObj->success) $reCaptchaOkay = true;

			}

			#} Fail?
			if (!$reCaptchaOkay){

				#} AXE IT
		    	$r['message'] = 'Nope.';
		    	$r['code'] = 'recaptcha';
		    	echo json_encode($r);
		    	wp_die();

			}

		}

		#} All need this, (if no form id, is dodgy?)
	    $zbs_form_id = -1;
	    if (isset($_POST['zbs_form_id']) && !empty($_POST['zbs_form_id'])) $zbs_form_id = (int)sanitize_text_field($_POST['zbs_form_id']);  //each form has an ID so we can track the conversions

		#} Fail?
		if (empty($zbs_form_id)){

			#} AXE IT
	    	$r['message'] = 'Nope.';
	    	$r['code'] = 'form';
	    	echo json_encode($r);
	    	wp_die();

		}

	    //honeypot
	    $zbs_honey = sanitize_text_field($_POST['zbs_hpot_email']);  //this should be blank
	    if($zbs_honey != ''){
	    	//then this is likely a spambot who has filled in the form since its hidden from humans
	    	$r['message'] = 'This is a honeypot.. something has gone wrong can alert the member on response';
	    	$r['code'] = 'honey';
	    	echo json_encode($r);
	    	wp_die();
	    } else {


			#} Added here: REQUIRE email... 
			if (isset($_POST['zbs_email']) && !empty($_POST['zbs_email']) && zeroBSCRM_validateEmail($_POST['zbs_email'])){

				#} Email is OKAY!
				#} For now do nothing here

			} else {

				#} AXE IT
		    	$r['message'] = 'Email Required.';
		    	$r['code'] = 'emailfail';
		    	echo json_encode($r);
		    	wp_die();

			}

	    	// do our usual processing
	    	$zbs_form_style = (string)sanitize_text_field($_POST['zbs_form_style']);

	    	#} WH add - filter any not mentioned here
	    	if (!in_array($zbs_form_style,array('zbs_simple','zbs_naked','zbs_cgrab'))) $zbs_form_style = '';

			#} NOTE! at this point form id hasn't been validated... could be random number!




			#} "Form x filled out from y" (will be added as note / meta)

				#} form str
				$form_details = zeroBS_getForm($zbs_form_id);
				if (isset($form_details['title'])) $formTitle = $form_details['title'] . ' (#' . $zbs_form_id . ')';
				else $formTitle = '#'.$zbs_form_id;

				#} pid is now passed, however it will only be passed on embed's
				$pageID = ''; if (isset($_POST['pid']) && !empty($_POST['pid'])) $pageID = (int)sanitize_text_field($_POST['pid']);
				$fromPageName = ''; if (!empty($pageID)) $fromPageName = get_the_title($pageID);

				#} Form style str
				$formStyle = ''; 
				if ($zbs_form_style == 'zbs_simple') $formStyle = 'Simple';
				if ($zbs_form_style == 'zbs_naked') $formStyle = 'Naked';
				if ($zbs_form_style == 'zbs_cgrab') $formStyle = 'Content Grab';
				$formStyleStr = ''; if (!empty($formStyle)) $formStyleStr = ' ('.$formStyle.')';

				#} Could add these:
				#videoTNT_retrieveDom(get_bloginfo('wpurl')).' at '.date("F j, Y, g:i a")
				#videoTNT_getRealIpAddr()

				#} Build str's - refactor at some point... rough first fix
				if (!empty($pageID)){
					
					#} Shortcode form

						#} Existing user signed a form
						$existingUserFormSourceShort = 'User completed form <i class="fa fa-wpforms"></i>';
						$existingUserFormSourceLong = 'Form <span class="zbsEmphasis">'.$formTitle.'</span>'.$formStyleStr.', which was filled out from the page: <span class="zbsEmphasis">'.$fromPageName.'</span> (#'.$pageID.')';

						#} New User from form
						$newUserFormSourceShort = 'Created from Form Capture <i class="fa fa-wpforms"></i>';
						$newUserFormSourceLong = 'User created from the form <span class="zbsEmphasis">'.$formTitle.'</span>'.$formStyleStr.', which was filled out from the page: <span class="zbsEmphasis">'.$fromPageName.'</span> (#'.$pageID.')';


				} else {
				
					#} embed 

						#} Existing user signed a form
						$existingUserFormSourceShort = 'User completed form <i class="fa fa-wpforms"></i>';
						$existingUserFormSourceLong = 'Form <span class="zbsEmphasis">'.$formTitle.'</span>'.$formStyleStr.', which was filled out from an externally embedded form.';

						#} New User from form
						$newUserFormSourceShort = 'Created from Form Capture <i class="fa fa-wpforms"></i>';
						$newUserFormSourceLong = 'User created from the form <span class="zbsEmphasis">'.$formTitle.'</span>'.$formStyleStr.', which was filled out from an externally embedded form.';

				}

				#} Actual log var passed
				$fallBackLog = array(
							'type' => 'Form Filled',#'form_filled',
							'shortdesc' => $existingUserFormSourceShort,
							'longdesc' => $existingUserFormSourceLong
						);

				#} Internal automator overrides - here we pass a "customer.create" note override (so we can pass it a custom str, else we let it fall back to "created by form")
				$internalAutomatorOverride = array(

							'note_override' => array(
						
										'type' => 'Form Filled',#'form_filled',
										'shortdesc' => $newUserFormSourceShort,
										'longdesc' => $newUserFormSourceLong				

							)

						); 



			// TO LATER DO:
			// Log above notes as meta vals... e.g. user has completed form 1, 2, and 5

			// TO LATER DO: 
			// COMBINE THE FOLLOWING RETRIEVES... no need to have seperate input gathering...

	    	switch($zbs_form_style){

	    		case "zbs_simple":

		    		//simple just has email
		    		$zbs_email = sanitize_text_field($_POST['zbs_email']); #} This is validated above, but sanitize just in case!
		    		//have added a new 'form' for 'externals'
					$cID = zeroBS_integrations_addOrUpdateCustomer('form',$zbs_email,
						array(

					    	#} Removed this, as it'll default to lead if it's not already customer! 
							#} re-added as temp fix... WH 18/10/16
							#} changed to __() to support translation MS 06/09/19
					    	'zbsc_status' => __('Lead','zero-bs-crm'),


					    	'zbsc_email' => $zbs_email,
					    ),
					    
					    '', #) Customer date (auto)
						
						#} Fallback log (for customers who already exist)
						$fallBackLog,

						false, #} Extra meta

						#} Internal automator overrides - here we pass a "customer.create" note override (so we can pass it a custom str, else we let it fall back to "created by form")
						$internalAutomatorOverride

					);

					// 2.97.7 - added this:
					// if autolog for contact creation = off, still add the message to form:
					$autoLogCCreation = zeroBSCRM_getSetting('autolog_customer_new');
					if ($autoLogCCreation <= 0 && $cID > 0){

						// add form log manually
						$logID = $zbs->DAL->addUpdateLog(array(

							// fields (directly)
							'data'			=> array(

								'objtype' 	=> ZBS_TYPE_CONTACT,
								'objid' 	=> $cID,
								'type' 		=> zeroBSCRM_permifyLogType('Form Filled'),
								'shortdesc' => __('Customer added via Form Submit','zero-bs-crm'),
								'longdesc' 	=> "<blockquote>".__('Customer added via Form Submit','zero-bs-crm')."</blockquote>"
								
							)));
					}

	    		break;

	    		case "zbs_naked":

	    			#} Naked only has name + email?

		    		//validate these...  (use functions in form save down...)
		    		$zbs_email = sanitize_text_field($_POST['zbs_email']); #} This is validated above, but sanitize just in case!
		    		$zbs_fname = sanitize_text_field($_POST['zbs_fname']);
		    		#$zbs_lname = sanitize_text_field($_POST['zbs_lname']);
		    		#$zbs_notes = "Customer Form Submit Message:\r\n===========\r\n".sanitize_text_field($_POST['zbs_notes'])."\r\n===========\r\n";

		    		//have added a new 'form' for 'externals'
					zeroBS_integrations_addOrUpdateCustomer('form',$zbs_email,
						array(
							
					    	#} Removed this, as it'll default to lead if it's not already customer! 
							#} re-added as temp fix... WH 18/10/16
							#} changed to __() to support translation MS 06/09/19
					    	'zbsc_status' => __('Lead','zero-bs-crm'),

				    	'zbsc_email' => $zbs_email,
				    	'zbsc_fname' => $zbs_fname
				    	#'zbsc_lname' => $zbs_lname,
				    	#'zbsc_notes' => $zbs_notes,
				    	),
					    
					    '', #) Customer date (auto)
						
						#} Fallback log (for customers who already exist)
						$fallBackLog,

						false, #} Extra meta

						#} Internal automator overrides - here we pass a "customer.create" note override (so we can pass it a custom str, else we let it fall back to "created by form")
						$internalAutomatorOverride
				    );

	    		break;
	    		case "zbs_cgrab":

		    		//validate these...  (use functions in form save down...)
		    		$zbs_email = sanitize_text_field($_POST['zbs_email']); #} This is validated above, but sanitize just in case!
		    		$zbs_fname = sanitize_text_field($_POST['zbs_fname']);
		    		$zbs_lname = sanitize_text_field($_POST['zbs_lname']);
		    		# Raw: $zbs_notes = "Customer Form Submit Message:\r\n===========\r\n".zeroBSCRM_textProcess($_POST['zbs_notes'])."\r\n===========\r\n";
		    		# HTML:
		    			$formMessage = zeroBSCRM_textProcess($_POST['zbs_notes']);
		    			$zbs_notes = "<blockquote>Customer Form Submit Message:<br />===========<br />".$formMessage."<br />===========</blockquote>";


		    			#} 27/09/16 WH - rather than pass as note field, add to log:

		    				#} for if user exists:
		    				$fallBackLog['longdesc'] .= $zbs_notes;

		    				#} for if user is fresh:
		    				$internalAutomatorOverride['note_override']['longdesc'] .= $zbs_notes;


		    		//have added a new 'form' for 'externals'
					$cID = zeroBS_integrations_addOrUpdateCustomer('form',$zbs_email,
						array(
							
					    	#} Removed this, as it'll default to lead if it's not already customer! 
							#} re-added as temp fix... WH 18/10/16
							#} changed to __() to support translation MS 06/09/19
					    	'zbsc_status' => __('Lead','zero-bs-crm'),

				    	'zbsc_email' => $zbs_email,
				    	'zbsc_fname' => $zbs_fname,
				    	'zbsc_lname' => $zbs_lname,
				    	#} Removed this and added to logs (just above!) 'zbsc_notes' => $zbs_notes,
				    	),
					    
					    '', #) Customer date (auto)
						
						#} Fallback log (for customers who already exist)
						$fallBackLog,

						false, #} Extra meta

						#} Internal automator overrides - here we pass a "customer.create" note override (so we can pass it a custom str, else we let it fall back to "created by form")
						$internalAutomatorOverride
				    ); 

					// 2.97.7 - added this:
					// if autolog for contact creation = off, still add the message to form:
					$autoLogCCreation = zeroBSCRM_getSetting('autolog_customer_new');
					if ($autoLogCCreation <= 0){

						global $zbs;

						// add form log manually
						$logID = $zbs->DAL->addUpdateLog(array(

							// fields (directly)
							'data'			=> array(

								'objtype' 	=> ZBS_TYPE_CONTACT,
								'objid' 	=> $cID,
								'type' 		=> zeroBSCRM_permifyLogType('Form Filled'),
								'shortdesc' => $fallBackLog['shortdesc'],
								'longdesc' 	=> $fallBackLog['longdesc'],

								'meta' 		=> array('message'=>$formMessage)
								
							)));
					}

	    		break;
	    		default:
	    		exit();  //if not one of our cases then die.
	    	}

            #} TODO we could add some tracking here (e.g. "originated from form x on page y")

            //update the counter for "conversions"
            if( $zbs->isDAL3() ) {
                $zbs->DAL->forms->add_form_conversion( $zbs_form_id );
            } else {
                $zbs_form_conversions = get_post_meta($zbs_form_id, 'zbs_form_conversions', true);
                if ($zbs_form_conversions == '') {
                    $zbs_form_conversions = 1;
                } else {
                    $zbs_form_conversions++;
                }
                update_post_meta($zbs_form_id, 'zbs_form_conversions', $zbs_form_conversions);  //increment the conversions counter...
            }

            // return
	    	$r['message'] = 'Contact received.';
	    	$r['code'] = 'success';
	    	echo json_encode($r);
	    	die(); 

	    }

	}
	add_action('wp_ajax_nopriv_zbs_lead_form_capture','zbs_lead_form_capture');
	add_action( 'wp_ajax_zbs_lead_form_capture', 'zbs_lead_form_capture' );

	/* POST ACTIONS
	add_action( 'admin_post_nopriv_zbs_lead_form_capture', 'zbs_lead_form_capture' );
	add_action( 'admin_post_zbs_lead_form_capture', 'zbs_lead_form_capture' );
	*/

/* ======================================================
	/ Admin AJAX: Front End Forms
====================================================== */




/* ======================================================
	Admin AJAX: Mail Delivery - SMTP tests etc.
====================================================== */


	#} quickly checks if ports are open (pre smtp check)
	add_action( 'wp_ajax_zbs_maildelivery_validation_smtp_ports', 'zeroBSCRM_AJAX_mailDelivery_validateSMTPPorts' );
	function zeroBSCRM_AJAX_mailDelivery_validateSMTPPorts(){

		#} Check nonce
		check_ajax_referer( 'wpzbs-ajax-nonce', 'sec' );  //nonce to bounce out if not from right page

		#} Perms?
		if (!zeroBSCRM_permsMailCampaigns()) exit();

		#} Retrieve...
		$sendFromName = ''; if (isset($_POST['sendFromName'])) $sendFromName = sanitize_text_field($_POST['sendFromName']);
		$sendFromEmail = ''; if (isset($_POST['sendFromEmail'])) $sendFromEmail = sanitize_text_field($_POST['sendFromEmail']);
		$smtpHost = ''; if (isset($_POST['smtpHost'])) $smtpHost = sanitize_text_field($_POST['smtpHost']);
		$smtpPort = ''; if (isset($_POST['smtpPort'])) $smtpPort = sanitize_text_field($_POST['smtpPort']);
		$smtpUser = ''; if (isset($_POST['smtpUser'])) $smtpUser = sanitize_text_field($_POST['smtpUser']);
		$smtpPass = ''; if (isset($_POST['smtpPass'])) $smtpPass = sanitize_text_field($_POST['smtpPass']);

		#} ... validate
		$res = array('debugs'=>array()); $okay = false;
		if (!empty($smtpPort)){
			
			$okay = true;

			// has smtpPort

			// port check (local)
			/* leave to smtp wiz for now */
			/*$localPortCheck = zeroBSCRM_mailDelivery_checkPort($smtpPort,$smtpHost);
			if (!$localPortCheck[0]){
				$res['debugs'][] = __('Your server seems to be blocking outbound traffic for this port: '.$smtpPort.', it will not be possible to send mail while this port is blocked.','zero-bs-crm'); 
				$okay = false;
			} */
			// remote + local, one test :)
			$remotePortCheck = zeroBSCRM_mailDelivery_checkPort($smtpPort,$smtpHost);
			if (!$remotePortCheck[0]){
				$res['debugs'][] = sprintf( __( 'The CRM cannot connect to %s on port %s. This may not matter, as it will try other ports for you below.', 'zero-bs-crm' ), $smtpHost, $smtpPort ); 
				$okay = false;
			} 		



		}

		$res['open'] = $okay;
		header('Content-Type: application/json');
		// requires zeroBSCRM_utf8ize for proper debug passing
		echo json_encode(zeroBSCRM_utf8ize($res));
		exit();


	}


	#} Attempts to validate mail delivery SMTP settings, send test email, & save's if validated
	add_action( 'wp_ajax_zbs_maildelivery_validation_smtp', 'zeroBSCRM_AJAX_mailDelivery_validateSMTP' );
	function zeroBSCRM_AJAX_mailDelivery_validateSMTP(){

		#} Check nonce
		check_ajax_referer( 'wpzbs-ajax-nonce', 'sec' );  //nonce to bounce out if not from right page

		#} Perms?
		if (!zeroBSCRM_permsMailCampaigns()) exit();

		#} Retrieve...
		$sendFromName = ''; if (isset($_POST['sendFromName'])) $sendFromName = sanitize_text_field($_POST['sendFromName']);
		$sendFromEmail = ''; if (isset($_POST['sendFromEmail'])) $sendFromEmail = sanitize_text_field($_POST['sendFromEmail']);
		$smtpHost = ''; if (isset($_POST['smtpHost'])) $smtpHost = sanitize_text_field($_POST['smtpHost']);
		$smtpPort = ''; if (isset($_POST['smtpPort'])) $smtpPort = sanitize_text_field($_POST['smtpPort']);
		$smtpUser = ''; if (isset($_POST['smtpUser'])) $smtpUser = sanitize_text_field($_POST['smtpUser']);
		$smtpPass = ''; if (isset($_POST['smtpPass'])) $smtpPass = sanitize_text_field($_POST['smtpPass']);

		#} ... validate
		$res = array('debugs'=>array()); 
		if (!empty($sendFromName)){

			// has name

			if (!empty($sendFromEmail) && zeroBSCRM_validateEmail($sendFromEmail)){		

				// let's try and probe SMTP :)
				$attemptedSend = zeroBSCRM_mailDelivery_checkSMTPDetails($sendFromName,$sendFromEmail,$smtpHost,$smtpPort,$smtpUser,$smtpPass);

					if (is_array($attemptedSend) && isset($attemptedSend['sent']) && $attemptedSend['sent']){

						// passed the test, but might have been a latter config, so need to use whichever was the lasting config from the above func!
						if (isset($attemptedSend['finset']) && is_array($attemptedSend['finset'])){

								// no checks here. :o
								$smtpHost = $attemptedSend['finset']['host'];
								$smtpPort = $attemptedSend['finset']['port'];
								// these two won't be different..
								//$smtpUser = $attemptedSend['finset']['user'];
								//$smtpPass = $attemptedSend['finset']['pass'];
								$smtpSecurity = $attemptedSend['finset']['security'];

								// save it 
								global $zbs;
								$existingZBSSMTPAccs = zeroBSCRM_getSetting('smtpaccs');
		  						if (!is_array($existingZBSSMTPAccs)) $existingZBSSMTPAccs = array();

								#} Encrypt password - this doesn't do much, anyone with PHP access COULD undo this
								#} Is only used here to make harder for opportunitists :)
								if ( ! function_exists( 'zeroBSCRM_encrypt' ) ) {
									require( ZEROBSCRM_INCLUDE_PATH . 'ZeroBSCRM.Encryption.php' );
								}

								#} Encrypt PW:
								$encryptionKey = zeroBSCRM_getSetting( 'smtpkey' );
								if ( empty( $encryptionKey ) ) {
									// Creating a 256 bit key. This might need to change if the algorithm changes
									$encryptionKey = openssl_random_pseudo_bytes( 32 );
									$zbs->settings->update( 'smtpkey', bin2hex( $encryptionKey ) );
								} else { 
									$encryptionKey = hex2bin( $encryptionKey );
								}

								$encPW = zeroBSCRM_encrypt( $smtpPass, $encryptionKey );


								#} BLATENTLY breaking the rules by base64encoding these here.... see phpe_enc
								#} But they wouldn't sit well in db any other way?

		  						#} Build arr
		  						$settingsArr = array(

		  							'mode' => 'smtp',
		  							'fromemail' => $sendFromEmail,
		  							'fromname' => $sendFromName,
		  							'host' => $smtpHost,
		  							'port' => $smtpPort,
		  							'user' => $smtpUser,
		  							'pass' => $encPW,
		  							'sec' => $smtpSecurity,
		  							'veri' => time() // verified

		  						);
		  						$settingsKey = zeroBSCRM_mailDelivery_makeKey($settingsArr);

		  						if (!isset($existingZBSSMTPAccs[$settingsKey]))
		  							$existingZBSSMTPAccs[$settingsKey] = $settingsArr;
		  						else
		  							exit('{errors:[{keyerrors:1}]}');

		  						#} Update
								global $zbs;
		  						$zbs->settings->update('smtpaccs',$existingZBSSMTPAccs);
		  						

								// fini
								$res['success'] = 1;

								// add debugs to response (2.94.2 - help debugging)
								if (isset($attemptedSend['debugs']) && is_array($attemptedSend['debugs'])) $res['debugs'] = array_merge($res['debugs'],$attemptedSend['debugs']);


						} else {

								// send seemed to succeed, but func didn't give settings back?!?!
								if (!isset($res['errors']) || !is_array($res['errors'])) $res['errors'] = array();
								$res['errors']['settingspasserror'] = 1;

								// add debugs to response (2.94.2 - help debugging)
								if (isset($attemptedSend['debugs']) && is_array($attemptedSend['debugs'])) $res['debugs'] = array_merge($res['debugs'],$attemptedSend['debugs']);

						}

					} else {

							// send error
							if (!isset($res['errors']) || !is_array($res['errors'])) $res['errors'] = array();
							$res['errors']['senderror'] = 1;

							// add debugs to response (2.94.2 - help debugging)
							if (isset($attemptedSend['debugs']) && is_array($attemptedSend['debugs'])) $res['debugs'] = array_merge($res['debugs'],$attemptedSend['debugs']);
					}


			} else {

				// no good email
				if (!isset($res['errors']) || !is_array($res['errors'])) $res['errors'] = array();
				$res['errors']['bademail'] = 1;

			}


		} else {

			// no name?
			if (!isset($res['errors']) || !is_array($res['errors'])) $res['errors'] = array();
			$res['errors']['nameempty'] = 1;

		}

		header('Content-Type: application/json');
		// requires zeroBSCRM_utf8ize for proper debug passing
		echo json_encode(zeroBSCRM_utf8ize($res));
		exit();

	}

	#} Attempts to validate wp mail settings, send test email,  & save's if validated
	add_action( 'wp_ajax_zbs_maildelivery_validation_wp_mail', 'zeroBSCRM_AJAX_mailDelivery_validateWPMail' );
	function zeroBSCRM_AJAX_mailDelivery_validateWPMail(){

		#} Check nonce
		check_ajax_referer( 'wpzbs-ajax-nonce', 'sec' );  //nonce to bounce out if not from right page

		#} Perms?
		if (!zeroBSCRM_permsMailCampaigns()) exit('{permserror:1}');

		#} Retrieve...
		$sendFromName = ''; if (isset($_POST['sendFromName'])) $sendFromName = sanitize_text_field($_POST['sendFromName']);
		$sendFromEmail = ''; if (isset($_POST['sendFromEmail'])) $sendFromEmail = sanitize_text_field($_POST['sendFromEmail']);

		#} ... validate
		$res = array(); 
		if (!empty($sendFromName)){


			if (!empty($sendFromEmail) && zeroBSCRM_validateEmail($sendFromEmail)){

				// checks out, send a test :)


					$subject = '[Jetpack CRM] Mail Delivery Routine';
					$headers = array('Content-Type: text/html; charset=UTF-8');	
					$headers[] = 'From: '.$sendFromName.' <'.$sendFromEmail.'>';

				    // See .mail-templating.php
				    $body = zeroBSCRM_mailDelivery_generateTestHTML(true);

					// sends to itself to test
					$sent = wp_mail( $sendFromEmail, $subject, $body, $headers );


					if ($sent){

						#} Save record.
						global $zbs;
						$existingZBSSMTPAccs = zeroBSCRM_getSetting('smtpaccs');
  						if (!is_array($existingZBSSMTPAccs)) $existingZBSSMTPAccs = array();

  						#} Build arr
  						$settingsArr = array(

  							'mode' => 'wp_mail',
  							'fromemail' => $sendFromEmail,
  							'fromname' => $sendFromName,
  							'replyto' => $sendFromEmail,
  							'cc' => '',
  							'bcc' => '',
  							'veri' => time() // verified

  						);
  						$settingsKey = zeroBSCRM_mailDelivery_makeKey($settingsArr);

  						/* Switched to settings key mode 
  						// brutal add to arr (if email not present)
  						$ind = -1; if (count($existingZBSSMTPAccs) > 0) foreach ($existingZBSSMTPAccs as $indx => $acc){

  							if (isset($acc['fromemail']) && !empty($acc['fromemail']) && $acc['fromemail'] == $sendFromEmail) $ind = $indx;
  						}
  						if ($ind > -1){

  							// replace
  							$existingZBSSMTPAccs[$ind] = $settingsArr;

  						} else {

  							// new 
  							$existingZBSSMTPAccs[] = $settingsArr;

  						} */
  						if (!isset($existingZBSSMTPAccs[$settingsKey]))
  							$existingZBSSMTPAccs[$settingsKey] = $settingsArr;
  						else
  							exit('{keyerror:1}');

  						#} Update
  						$zbs->settings->update('smtpaccs',$existingZBSSMTPAccs);

  						// if ONLY 1 installed, set as default
  						if (count($existingZBSSMTPAccs) == 1) $zbs->settings->update('smtpaccsdef',$settingsKey);

						// fini
						$res['success'] = 1;


					} else {

							// send error
							if (!isset($res['errors']) || !is_array($res['errors'])) $res['errors'] = array();
							$res['errors']['senderror'] = 1;

					}


			} else {

				// no good email
				if (!isset($res['errors']) || !is_array($res['errors'])) $res['errors'] = array();
				$res['errors']['bademail'] = 1;

			}


		} else {

			// no name?
			if (!isset($res['errors']) || !is_array($res['errors'])) $res['errors'] = array();
			$res['errors']['nameempty'] = 1;

		}


		header('Content-Type: application/json');
		echo json_encode($res);
		exit();

	}


	#} Attempts to send a test email from a stored mail delivery method
	add_action( 'wp_ajax_zbs_maildelivery_test', 'zeroBSCRM_AJAX_mailDelivery_testEmail' );
	function zeroBSCRM_AJAX_mailDelivery_testEmail(){

		#} Check nonce
		check_ajax_referer( 'wpzbs-ajax-nonce', 'sec' );  //nonce to bounce out if not from right page

		#} Perms?
		if (!zeroBSCRM_permsMailCampaigns()) exit();

		#} Starting
		$res = array();

	    #} Retrive deets
	    $mailDeliveryIndxKey = -1; if (isset($_POST['indx'])) $mailDeliveryIndxKey =  sanitize_text_field($_POST['indx']);
	    $sendToEmail = ''; if (isset($_POST['em'])) $sendToEmail =  sanitize_text_field($_POST['em']);

		//validate the email
		if (!zeroBSCRM_validateEmail($sendToEmail)){
			$r['message'] = 'Not a valid email';
			echo json_encode($r);
			die();
		}

		#} Check id + perms + em
		if ($mailDeliveryIndxKey <= -1 || empty($mailDeliveryIndxKey) || empty($sendToEmail)){
			die();
		}
	    
	    // load acc
	    // no need, now done in zeroBSCRM_mailDelivery_sendMessage $mailDeliveryDetails = zeroBSCRM_mailDelivery_retrieveACCByKey($mailDeliveryIndxKey);	


		$subject = 'Mail Delivery Routine Test';
		##WLREMOVE
			$subject = '[Jetpack CRM] '.$subject;
		##/WLREMOVE

		// this'll get set by zeroBSCRM_mailDelivery_sendMessage - $headers = array('Content-Type: text/html; charset=UTF-8');	
		// this'll get set by zeroBSCRM_mailDelivery_sendMessage - $headers[] = 'From: '.$sendFromName.' <'.$sendFromEmail.'>';

	    // See .mail-templating.php
	    $body = zeroBSCRM_mailDelivery_generateTestHTML(true);

	    // build send array
	    $mailArray = array(
	    	'toEmail' => $sendToEmail,
	    	'toName' => '',
	    	'subject' => $subject,
	    	'headers' => -1,
	    	'body' => $body,
	    	'textbody' => '',
	    	'options' => array(
	    		'html' => 1
	    	)
	    );

		// sends to itself to test
		$sent = zeroBSCRM_mailDelivery_sendMessage($mailDeliveryIndxKey,$mailArray);
		if (is_array($sent) && $sent[0]){

			// fini
			zeroBSCRM_sendJSONSuccess( $res );

		} else {

			// error
			if (!isset($res['errors']) || !is_array($res['errors'])) $res['errors'] = array();
			$res['errors']['sendfail'] = 1;
			zeroBSCRM_sendJSONError( $res );

		}
	}




	#} Attempts to remove a delivery route
	add_action( 'wp_ajax_zbs_maildelivery_remove', 'zeroBSCRM_AJAX_mailDelivery_removeMailDelivery' );
	function zeroBSCRM_AJAX_mailDelivery_removeMailDelivery(){

		#} Check nonce
		check_ajax_referer( 'wpzbs-ajax-nonce', 'sec' );  //nonce to bounce out if not from right page

		#} Perms?
		if (!zeroBSCRM_permsMailCampaigns()) exit();

		#} Starting
		$res = array();

	    #} Retrive deets
	    $mailDeliveryIndxKey = -1; if (isset($_POST['indx'])) $mailDeliveryIndxKey =  sanitize_text_field($_POST['indx']);

		#} Check id + perms + em
		if ($mailDeliveryIndxKey <= -1 || empty($mailDeliveryIndxKey)){
			die();
		}
	    
	    global $zbs; 
	    $currentMailDeliveryAccs = zeroBSCRM_getSetting('smtpaccs');
	    if (is_array($currentMailDeliveryAccs)){

	    	// unset this one if exists
	    	if (isset($currentMailDeliveryAccs[$mailDeliveryIndxKey])) unset($currentMailDeliveryAccs[$mailDeliveryIndxKey]);


			// kill default (if was set, set it to another, or empty)
			$existingDefault = $zbs->settings->get('smtpaccsdef');
			if ($existingDefault == $mailDeliveryIndxKey){

				if (count($currentMailDeliveryAccs) > 0){
					// has others, so choose first one :)
					$keys = array_keys($currentMailDeliveryAccs);
					$key = ''; if (isset($keys[0])) $key = $keys[0];
					$zbs->settings->update('smtpaccsdef',$key);
				} else // just unset default, is empty
					$zbs->settings->update('smtpaccsdef','');

			}

	    	// update
	    	$zbs->settings->update('smtpaccs',$currentMailDeliveryAccs);

			// fini - lazy nocheck
			$res['success'] = 1;

	    } else {

	    	// brutal force array
	    	$zbs->settings->update('smtpaccs',array());

			// kill default (none left)
			$zbs->settings->update('smtpaccsdef','');

			// fini - lazy nocheck
			$res['success'] = 1;

	    }

		if (!isset($res['success'])){

			// error
			if (!isset($res['errors']) || !is_array($res['errors'])) $res['errors'] = array();
			$res['errors']['sendfail'] = 1;

		}


		header('Content-Type: application/json');
		echo json_encode($res);
		exit();

	}




	#} Attempts to set a delivery route default
	add_action( 'wp_ajax_zbs_maildelivery_setdefault', 'zeroBSCRM_AJAX_mailDelivery_setMailDeliveryAsDefault' );
	function zeroBSCRM_AJAX_mailDelivery_setMailDeliveryAsDefault(){

		#} Check nonce
		check_ajax_referer( 'wpzbs-ajax-nonce', 'sec' );  //nonce to bounce out if not from right page

		#} Perms?
		if (!zeroBSCRM_permsMailCampaigns()) exit();

		#} Starting
		$res = array();

	    #} Retrive deets
	    $mailDeliveryIndxKey = -1; if (isset($_POST['indx'])) $mailDeliveryIndxKey =  sanitize_text_field($_POST['indx']);

		#} Check id + perms + em
		if ($mailDeliveryIndxKey <= -1 || empty($mailDeliveryIndxKey)){
			die();
		}
	    
	    // brutal setting
	    global $zbs; 
    	$zbs->settings->update('smtpaccsdef',$mailDeliveryIndxKey);

		// fini - lazy nocheck
		$res['success'] = 1;


		header('Content-Type: application/json');
		echo json_encode($res);
		exit();

	}



/* ======================================================
	/ Admin AJAX: Mail Delivery - SMTP tests etc.
====================================================== */


/* ======================================================
	Admin AJAX: Customer Record stuff
====================================================== */

	#} Add/remove aliases
	add_action( 'wp_ajax_addAlias', 'zeroBSCRM_AJAX_addAlias' );
	function zeroBSCRM_AJAX_addAlias(){

		#} Check nonce
		check_ajax_referer( 'zbscrmjs-ajax-nonce', 'sec' );

		#} Check perms
		if (!zeroBSCRM_permsCustomers()) { header('Content-Type: application/json'); exit('{err:1}'); }

		#} Proceed :)
		$passBack = array();

			$custID = -1; if (isset($_POST['cid'])) $custID = (int)sanitize_text_field($_POST['cid']);
			$alias = ''; if (isset($_POST['aka'])) $alias = sanitize_text_field($_POST['aka']);

			#} Any good?
			if (!empty($custID) && !empty($alias)){

				// check if already exists as alias
				if (zeroBS_canUseCustomerAlias($alias) == false){

					$passBack['fail'] = 'existing';

				} else {

					// all good, proceed

					$passBack['res'] = zeroBS_addCustomerAlias($custID,$alias);

					#} For now, no checks :)

				}

				#} Return
				header('Content-Type: application/json'); 
				echo json_encode($passBack);
				exit();

			}


		// err really :o
		header('Content-Type: application/json'); 
		exit('[]');



	}
	add_action( 'wp_ajax_removeAlias', 'zeroBSCRM_AJAX_removeAlias' );
	function zeroBSCRM_AJAX_removeAlias(){

		#} Check nonce
		check_ajax_referer( 'zbscrmjs-ajax-nonce', 'sec' );

		#} Check perms
		if (!zeroBSCRM_permsCustomers()) { header('Content-Type: application/json'); exit('{err:1}'); }

		#} Proceed :)
		$passBack = array();

			$custID = -1; if (isset($_POST['cid'])) $custID = (int)sanitize_text_field($_POST['cid']);
			$aliasID = -1; if (isset($_POST['akaid'])) $aliasID = (int)sanitize_text_field($_POST['akaid']);

			#} Any good?
			if (!empty($custID) && !empty($aliasID)){

				// NOTE: by passing cust + alias id's, rather than just ALIAS id, we do ANOTHER check to make sure 
				// that user's deleting smt they mean to (this is also pre-emptive for provider-platform + ownership rights)
				$passBack['res'] = zeroBS_removeCustomerAliasByID($custID,$aliasID);

				#} For now, no checks :)

					#} Return
					header('Content-Type: application/json'); 
					echo json_encode($passBack);
					exit();

			}


		// err really :o
		header('Content-Type: application/json'); 
		exit('[]');


	}

/* ======================================================
	/ Admin AJAX: Customer Record stuff
====================================================== */



/* ======================================================
	Admin AJAX: List View (API STYLE)
====================================================== */

  //may want to rewrite similar to zeroBSCRM_AJAX_updateListViewFilterButtons(), or even merge the two functions
	#} Update Columns - list view column update
	add_action( 'wp_ajax_updateListViewColumns', 'zeroBSCRM_AJAX_updateListViewColumns' );
	function zeroBSCRM_AJAX_updateListViewColumns(){

		#} Check nonce
		check_ajax_referer( 'zbscrmjs-ajax-nonce', 'sec' );

		#} Check perms
		if (!zeroBSCRM_isZBSAdminOrAdmin()) { header('Content-Type: application/json'); exit('{err:1}'); }

			global $zbs;

			#} Retrieve type + columns arr
			$listtype = sanitize_text_field($_POST['listtype']);
			$listColumns = $_POST['v']; // NEEDS SANITATION!

			/* debug 
			header('Content-Type: application/json'); 
			echo json_encode($listColumns);
			exit(); */

			/*
	        #} Centralised into ZeroBSCRM.List.Columns.php 30/7/17
	        global $zeroBSCRM_columns_customer;
	        $defaultColumns = $zeroBSCRM_columns_customer['default'];
	        $allColumns = $zeroBSCRM_columns_customer['all'];
	        */
	        $customViews = $zbs->settings->get('customviews2');  

			#} switch by type
			switch ($listtype){

				case 'customer':  			

		        #} Brutal save over anyway..

					#} Use existing (stores all types of custom views - not just this one)
		        	$newCustomViews = $customViews;
		        	$passBack = array();

		        	#} Build
		        	$newCustomerColumns = array(); foreach ($listColumns as $colKey => $colVal){

		        		$newCustomerColumns[$colVal['fieldstr']] = array(__($colVal['namestr'],"zero-bs-crm"));
		        		$passBack[] = array('fieldstr' => __($colVal['fieldstr'],"zero-bs-crm"), 'namestr' => __($colVal['namestr'],"zero-bs-crm"));

		        	}

		        	#} Update	    			
		        	$newCustomViews['customer'] = $newCustomerColumns;
					$zbs->settings->update( 'customviews2' , $newCustomViews);


					#} Return
					header('Content-Type: application/json'); 
					echo json_encode($passBack);
					exit();

					break;

				case 'company':

		        #} Brutal save over anyway..

					#} Use existing (stores all types of custom views - not just this one)
		        	$newCustomViews = $customViews;
		        	$passBack = array();

		        	#} Build
		        	$newCoColumns = array(); foreach ($listColumns as $colKey => $colVal){

		        		$newCoColumns[$colVal['fieldstr']] = array(__($colVal['namestr'],"zero-bs-crm"));
		        		$passBack[] = array('fieldstr' => __($colVal['fieldstr'],"zero-bs-crm"), 'namestr' => __($colVal['namestr'],"zero-bs-crm"));

		        	}

		        	#} Update	    			
		        	$newCustomViews['company'] = $newCoColumns;
					$zbs->settings->update( 'customviews2' , $newCustomViews);


					#} Return
					header('Content-Type: application/json'); 
					echo json_encode($passBack);
					exit();

					break;

				case 'quote':

		        #} Brutal save over anyway..

					#} Use existing (stores all types of custom views - not just this one)
		        	$newCustomViews = $customViews;
		        	$passBack = array();

		        	#} Build
		        	$newQuoColumns = array(); foreach ($listColumns as $colKey => $colVal){

		        		$newQuoColumns[$colVal['fieldstr']] = array(__($colVal['namestr'],"zero-bs-crm"));
		        		$passBack[] = array('fieldstr' => __($colVal['fieldstr'],"zero-bs-crm"), 'namestr' => __($colVal['namestr'],"zero-bs-crm"));

		        	}

		        	#} Update	    			
		        	$newCustomViews['quote'] = $newQuoColumns;
					$zbs->settings->update( 'customviews2' , $newCustomViews);


					#} Return
					header('Content-Type: application/json'); 
					echo json_encode($passBack);
					exit();

					break;

				case 'invoice':

		        #} Brutal save over anyway..

					#} Use existing (stores all types of custom views - not just this one)
		        	$newCustomViews = $customViews;
		        	$passBack = array();

		        	#} Build
		        	$newInvColumns = array(); foreach ($listColumns as $colKey => $colVal){

		        		$newInvColumns[$colVal['fieldstr']] = array(__($colVal['namestr'],"zero-bs-crm"));
		        		$passBack[] = array('fieldstr' => __($colVal['fieldstr'],"zero-bs-crm"), 'namestr' => __($colVal['namestr'],"zero-bs-crm"));

		        	}

		        	#} Update	    			
		        	$newCustomViews['invoice'] = $newInvColumns;
					$zbs->settings->update( 'customviews2' , $newCustomViews);


					#} Return
					header('Content-Type: application/json'); 
					echo json_encode($passBack);
					exit();

					break;

				case 'transaction':    			

		        #} Brutal save over anyway..

					#} Use existing (stores all types of custom views - not just this one)
		        	$newCustomViews = $customViews;
		        	$passBack = array();

		        	#} Build
		        	$newTransColumns = array(); foreach ($listColumns as $colKey => $colVal){

		        		$newTransColumns[$colVal['fieldstr']] = array(__($colVal['namestr'],"zero-bs-crm"));
		        		$passBack[] = array('fieldstr' => __($colVal['fieldstr'],"zero-bs-crm"), 'namestr' => __($colVal['namestr'],"zero-bs-crm"));

		        	}

		        	#} Update	    			
		        	$newCustomViews['transaction'] = $newTransColumns;
					$zbs->settings->update( 'customviews2' , $newCustomViews);


					#} Return
					header('Content-Type: application/json'); 
					echo json_encode($passBack);
					exit();

					break;

				case 'form':

		        #} Brutal save over anyway..

					#} Use existing (stores all types of custom views - not just this one)
		        	$newCustomViews = $customViews;
		        	$passBack = array();

		        	#} Build
		        	$newFormsColumns = array(); foreach ($listColumns as $colKey => $colVal){

		        		$newFormsColumns[$colVal['fieldstr']] = array(__($colVal['namestr'],"zero-bs-crm"));
		        		$passBack[] = array('fieldstr' => __($colVal['fieldstr'],"zero-bs-crm"), 'namestr' => __($colVal['namestr'],"zero-bs-crm"));

		        	}

		        	#} Update	    			
		        	$newCustomViews['form'] = $newFormsColumns;
					$zbs->settings->update( 'customviews2' , $newCustomViews);


					#} Return
					header('Content-Type: application/json'); 
					echo json_encode($passBack);
					exit();

					break;

				case 'segment':			

		        #} Brutal save over anyway..

					#} Use existing (stores all types of custom views - not just this one)
		        	$newCustomViews = $customViews;
		        	$passBack = array();

		        	#} Build
		        	$newColumns = array(); foreach ($listColumns as $colKey => $colVal){

		        		$newColumns[$colVal['fieldstr']] = array($colVal['namestr']);
		        		$passBack[] = array('fieldstr' => $colVal['fieldstr'], 'namestr' => $colVal['namestr']);

		        	}

		        	#} Update	    			
		        	$newCustomViews['segment'] = $newColumns;
					$zbs->settings->update( 'customviews2' , $newCustomViews);


					#} Return
					header('Content-Type: application/json'); 
					echo json_encode($passBack);
					exit();

					break;

				case 'event':

					#} Use existing (stores all types of custom views - not just this one)
		        	$newCustomViews = $customViews;
		        	$passBack = array();

		        	#} Build
		        	$newEventColumns = array(); foreach ($listColumns as $colKey => $colVal){

		        		$newEventColumns[$colVal['fieldstr']] = array(__($colVal['namestr'],"zero-bs-crm"));
		        		$passBack[] = array('fieldstr' => __($colVal['fieldstr'],"zero-bs-crm"), 'namestr' => __($colVal['namestr'],"zero-bs-crm"));

		        	}

		        	// Update	    			
		        	$newCustomViews['event'] = $newEventColumns;
					$zbs->settings->update( 'customviews2' , $newCustomViews);


					#} Return
					header('Content-Type: application/json'); 
					echo json_encode($passBack);
					exit();

					break;

				default: 

					// err really :o
					header('Content-Type: application/json'); 
					exit('[]');

					break;

			}


		exit();

	}


	#} Update filter buttons
	add_action( 'wp_ajax_updateListViewFilterButtons', 'zeroBSCRM_AJAX_updateListViewFilterButtons' );
	function zeroBSCRM_AJAX_updateListViewFilterButtons(){

		#} Check nonce
		check_ajax_referer( 'zbscrmjs-ajax-nonce', 'sec' );

		#} Check perms
		if (!current_user_can('administrator')) {
      header('Content-Type: application/json');
      exit('{err:1}');
    }

		#} Retrieve type + columns arr
		$listtype = sanitize_text_field($_POST['listtype']);
    $acceptableListViewTypes = array('customer','company','quote','invoice','transaction','form');
    
    //if filter list param doesn't exist, exit
    //if filter list isn't an array, exit
    //if list view type is bad, exit
    if (
        !isset($_POST['new_filter_buttons']) ||
        !is_array($_POST['new_filter_buttons']) ||
        !in_array($listtype,$acceptableListViewTypes,true)
       ) {
      header('Content-Type: application/json'); 
			exit('[]');
    }
		$new_filter_buttons = $_POST['new_filter_buttons'];
    
    //get list of valid filters
    $filter_str = 'zeroBSCRM_filterbuttons_'.$listtype;
    global $$filter_str;
    $all_filters = ${$filter_str}['all'];
    
  	//build list of filters buttons
    $new_filter_settings = array();
    $passback = array();
    foreach ($new_filter_buttons as $buttonKey => $buttonVal) {
      //skip any malformed field names
      if (!isset($buttonVal['fieldstr'])) continue;
      //skip any filters that don't match those already in the system
      if (!isset($all_filters[$buttonVal['fieldstr']])) continue;
      
      $label_field_name = $buttonVal['fieldstr'];
      //Rather than relying on a string passed arbitrarily, use the info from the stored filter
      $label_pretty_name = $all_filters[$buttonVal['fieldstr']][0];

      //will need to remove translation bits once filter labels are customizable
      $new_filter_settings[$label_field_name] = array(__($label_pretty_name,"zero-bs-crm"));
      $passback[] = array('fieldstr' => $label_field_name, 'namestr' => __($label_pretty_name,"zero-bs-crm"));
  	}

  	//get and update custom views setting
    global $zbs;
    $custom_views = $zbs->settings->get('customviews2');
  	$custom_views[$listtype.'_filters'] = $new_filter_settings;
		$zbs->settings->update( 'customviews2' , $custom_views );

    //return buttons JSON
		header('Content-Type: application/json'); 
		echo json_encode($passback);
		exit();

	}

	#} Retrieves data sets for list views, with passed params :)
	add_action( 'wp_ajax_retrieveListViewData', 'zeroBSCRM_AJAX_listViewRetrieveData' );
	function zeroBSCRM_AJAX_listViewRetrieveData(){

		#} req
		$res = false;

		#} Check nonce
		check_ajax_referer( 'zbscrmjs-ajax-nonce', 'sec' );

		global $zbs;

		#} Retrieve params
		$pArray = array(); if (isset($_POST['v']) && is_array($_POST['v'])) $pArray = $_POST['v'];

		// to properly sanitize, we hand-pass each var here, rather than trust the array :)
		// else defaults :)
		$listViewParams = array(
			'listtype' 	=> (isset($pArray['listtype'])) ? sanitize_text_field( $pArray['listtype'] ) : '',
			'columns'	=> array(),
			'editinline' => (isset($pArray['editinline'])) ? sanitize_text_field( $pArray['editinline'] ) : '',
			'retrieved' => (isset($pArray['retrieved'])) ? false : true, // doesn't look like this is used
            'count' => (isset($pArray['count'])) ? (int)sanitize_text_field( $pArray['count'] ) : 20,
           	'pagination' => (isset($pArray['pagination'])) ? sanitize_text_field( $pArray['pagination'] ) : true, 
            'paged' => (isset($pArray['paged'])) ? (int)sanitize_text_field( $pArray['paged'] ) : 1,
            'filters' => array(),
            'sort' => (isset($pArray['sort'])) ? sanitize_text_field( $pArray['sort'] ) : false,
            'sortorder' => (isset($pArray['sortorder'])) ? sanitize_text_field( $pArray['sortorder'] ) : false,
            'pagekey' => (isset($pArray['pagekey'])) ? sanitize_text_field( $pArray['pagekey'] ) : ''
		); 

		// deal with arrayed items

			// cols
			if (isset($_POST['v']) && is_array($_POST['v']) && isset($_POST['v']['columns']) && is_array($_POST['v']['columns'])){

				foreach ($_POST['v']['columns'] as $colIndx => $col){

					// check 
					if (isset($col['namestr']) && isset($col['fieldstr'])){ // removed v3.0.5 - think legacy, if no issue by 3.1, kill this comment. : && isset($col['inline'])

						// sanitize + add
						$listViewParams['columns'][] = array(

							'namestr' => (isset($col['namestr'])) ? sanitize_text_field( $col['namestr'] ) : '',
							'fieldstr' => (isset($col['fieldstr'])) ? sanitize_text_field( $col['fieldstr'] ) : '',
							'inline' => (isset($col['inline'])) ? (int)sanitize_text_field( $col['inline'] ) : -1

						);

					}

				}

			} // /cols


			// filters
			// could do with refactoring to account for multi-dimensionality more elegantly
			if (isset($_POST['v']) && is_array($_POST['v']) && isset($_POST['v']['filters']) && is_array($_POST['v']['filters'])){

				foreach ($_POST['v']['filters'] as $filterIndx => $filter){

					// check (if tags, will be 0 indexed index)
					$filterIndexStr = sanitize_text_field( $filterIndx );
					if (is_array($filter)){

						foreach ($filter as $filterSubIndx => $filterSub){

							if (!is_int($filterSubIndx)) $filterSubIndx = sanitize_text_field( $filterSubIndx );

							// can be an array or a string, so allow multidimension:						
							if (is_array($filterSub)){

								foreach ($filterSub as $filterSubSubIndx => $filterSubSub){

									if (!is_int($filterSubSubIndx)) $filterSubSubIndx = sanitize_text_field( $filterSubSubIndx );

									if (!isset($listViewParams['filters'][$filterIndexStr][$filterSubIndx]) || !is_array($listViewParams['filters'][$filterIndexStr][$filterSubIndx])) $listViewParams['filters'][$filterIndexStr][$filterSubIndx] = array();
									$listViewParams['filters'][$filterIndexStr][$filterSubIndx][$filterSubSubIndx] = sanitize_text_field( $filterSubSub );

								}

							} elseif (is_string($filterSub)){

								if (!isset($listViewParams['filters'][$filterIndexStr]) || !is_array($listViewParams['filters'][$filterIndexStr])) $listViewParams['filters'][$filterIndexStr] = array();
								$listViewParams['filters'][$filterIndexStr][$filterSubIndx] = sanitize_text_field( $filterSub );


							}
						}

					} elseif (is_string($filter)){

						// e.g. s = test
						$listViewParams['filters'][$filterIndexStr] = sanitize_text_field( $filter );

					}

				}

			}

		// / sanitising 
			
		if (isset($listViewParams) && gettype($listViewParams) == 'array' && isset($listViewParams['listtype'])){

			// if it's not got columns, do this, for now.
			if (!isset($listViewParams['columns']) || !is_array($listViewParams['columns'])) $listViewParams['columns'] = array();

			global $zbs;

			#} check perms first
			if ($listViewParams['listtype'] == 'customer' && !zeroBSCRM_permsViewCustomers()) zeroBSCRM_sendJSONError(array('no-action-or-rights'=>1));
			if ($listViewParams['listtype'] == 'company' && !zeroBSCRM_permsViewCustomers()) zeroBSCRM_sendJSONError(array('no-action-or-rights'=>1));
			if ($listViewParams['listtype'] == 'segment' && !zeroBSCRM_permsViewCustomers()) zeroBSCRM_sendJSONError(array('no-action-or-rights'=>1));
			if ($listViewParams['listtype'] == 'quote' && !zeroBSCRM_permsViewQuotes()) zeroBSCRM_sendJSONError(array('no-action-or-rights'=>1));
			if ($listViewParams['listtype'] == 'quotetemplate' && !zeroBSCRM_permsViewQuotes()) zeroBSCRM_sendJSONError(array('no-action-or-rights'=>1));
			if ($listViewParams['listtype'] == 'invoice' && !zeroBSCRM_permsViewInvoices()) zeroBSCRM_sendJSONError(array('no-action-or-rights'=>1));
			if ($listViewParams['listtype'] == 'transaction' && !zeroBSCRM_permsViewTransactions()) zeroBSCRM_sendJSONError(array('no-action-or-rights'=>1));


			#} Check for screen options (perpage)
			$pageKey = ''; $per_page = 20; 
			if (isset($listViewParams['pagekey']) && !empty($listViewParams['pagekey'])){

				// has a key, get screen opts
				$screenOpts = $zbs->userScreenOptions($listViewParams['pagekey']);
				if (is_array($screenOpts)){

					if (isset($screenOpts['perpage'])) $per_page = (int)$screenOpts['perpage'];
					// catch
					if ($per_page < 1) $per_page = 20;

				}

			}

			#} generate a 'col list' quickly (for all type list views)
			$columnsRequired = array(); foreach ($listViewParams['columns'] as $col) $columnsRequired[] = $col['fieldstr'];

			// default return, regardless of type (allows us to keep main generic)
			$res = array('objects'=>array(),'objectcount'=>-1,'paged'=>1);

			switch ($listViewParams['listtype']){

			/* ==============================================================================
			 ===================== CUSTOMER ============================================== */

				#} Customer list view :)
				case 'customer':

					#} Build query
					// now got by screenopt above $per_page = 20;
					$page_number = 0;
					$possibleSearchTerm = '';
					$withInvoices = false;
					$withQuotes = false;
					$withTransactions = false;
					$argsOverride = false;
					$possibleCoID = '';
					$possibleTagIDs = '';
					$possibleQuickFilters = '';
					$inArray = '';
					$withTags = false;
					$withAssigned = false;
					$withCompany = false;
					$latestLog = false;
					$withValues = false; // DAL3.0

					#} Sorting
					$sortField = 'id';
					$sortOrder = 'desc';

					#} Catch filters :)

						#} Search
						if (isset($listViewParams['filters']) && isset($listViewParams['filters']['s']) && !empty($listViewParams['filters']['s'])) $possibleSearchTerm = $listViewParams['filters']['s'];

						#} Tags
						if (isset($listViewParams['filters']) && isset($listViewParams['filters']['tags']) && is_array($listViewParams['filters']['tags'])) {

							$possibleTagIDs = array();
							foreach ($listViewParams['filters']['tags'] as $tagObj) {
							
								// DAL1: 
								if (isset($tagObj['term_id'])) $possibleTagIDs[] = $tagObj['term_id'];
								// DAL2: 
								if (isset($tagObj['id'])) $possibleTagIDs[] = $tagObj['id'];
							}

						}

						#} QuickFilters
						if (isset($listViewParams['filters']) && isset($listViewParams['filters']['quickfilters']) && is_array($listViewParams['filters']['quickfilters'])) {

							$possibleQuickFilters = array();
							foreach ($listViewParams['filters']['quickfilters'] as $quickFilter) $possibleQuickFilters[] = $quickFilter;

						}




					#} Total val present?
					if (in_array('totalvalue', $columnsRequired)){

						if ($zbs->isDAL3()){

							$withValues = true;

						} else {
							// old way
							$withInvoices = true;
							$withTransactions = true;
						}

					}
					#} Quote val present? // ONLY WORKS DAL3
					if (in_array('quotesvalue', $columnsRequired)){

						$withValues = true;

					}
					#} Invoices val present? // ONLY WORKS DAL3
					if (in_array('invoicesvalue', $columnsRequired)){

						$withValues = true;

					}
					#} trans val present? // ONLY WORKS DAL3
					if (in_array('transactionsvalue', $columnsRequired)){

						$withValues = true;

					}

					#} Tags
					if (in_array('tagged', $columnsRequired)){

						$withTags = true;

					}

					#} Quotes
					if (in_array('hasquote', $columnsRequired) || in_array('quotecount', $columnsRequired) || in_array('quotetotal', $columnsRequired)){

						$withQuotes = true;

					}

					#} Invoices
					if (in_array('hasinvoice', $columnsRequired) || in_array('invoicecount', $columnsRequired) || in_array('invoicetotal', $columnsRequired)){

						$withInvoices = true;

					}

					#} Trans
					if (in_array('hastransactions', $columnsRequired) || in_array('transactioncount', $columnsRequired) || in_array('transactiontotal', $columnsRequired)){

						$withTransactions = true;

					}

					#} Assigned to
					if (in_array('assigned', $columnsRequired)){

						$withAssigned = true;

					}

					#} Company
					if (in_array('company', $columnsRequired)){

						$withCompany = true;

					}

					#} latest log

						// see if in notcontactedin (quickfilter)
						$hasQuickFilterForLogs = false; if (is_array($possibleQuickFilters) && count($possibleQuickFilters) > 0) foreach ($possibleQuickFilters as $pqf) if (substr($pqf,0,14) == 'notcontactedin') $hasQuickFilterForLogs = true;

						if (in_array('latestlog', $columnsRequired) || in_array('lastcontacted',$columnsRequired) || $hasQuickFilterForLogs){

							$latestLog = true;

						}

					#} Catch paging :)

						if (isset($listViewParams['paged']) && !empty($listViewParams['paged'])) {

							$possiblePage = (int)$listViewParams['paged'];
							if ($possiblePage > 0){

								// NVM! // it'll come in +1 (because this is zero-indexed, where as js is +1)
								$page_number = $possiblePage;
							}

						}
						//$res['paged'] = $page_number;

					#} Catch sorting

						if (isset($listViewParams['sort']) && !empty($listViewParams['sort'])) {

							$possSortField = $listViewParams['sort'];

							// DB1 sorts:
							if (!$zbs->isDAL2()){

								#} Actually these need translating for now..
								switch($possSortField){

									case 'id':

										$sortField = 'post_id';

										break;

									// for now, sort nameavatar + name by post_title :)
									case 'name':

										$sortField = 'post_title';

										break;

									// for now, sort lname by post_excerpt (using lazily for sort pre db2)
									case 'lname':

										// this trick doesn't work - get_posts won't let you sort by post_excerpt!
										//$sortField = 'post_excerpt';

										break;

									case 'nameavatar':

										$sortField = 'post_title';

										break;

									default:

										$sortField = '';

										break;

								}


							} else {


								// DAL2 - allow all fields for now :) (little interpretation needed)
								if (!empty($possSortField) && $possSortField != false && $possSortField != "false"){
									$sortField = $possSortField;

									// ... though if id... 
									if ($sortField == 'zbsc_id') $sortField = 'ID';

									// ... and this
									if ($sortField == 'added') $sortField = 'created';
									if ($sortField == 'nameavatar') $sortField = 'fullname';
									if ($sortField == 'name') $sortField = 'fullname';
									if ($sortField == 'assigned') $sortField = 'zbs_owner';
								}

							}

							if (!empty($sortField)){
								
								$sortOrder = 'desc'; if (isset($listViewParams['sortorder']) && !empty($listViewParams['sortorder'])) $sortOrder = $listViewParams['sortorder'];

							}

						}


					#} Retrieve data
					//($withFullDetails=false,$perPage=10,$page=0,$withInvoices=false,$withQuotes=false,$searchPhrase='',$withTransactions=false,$argsOverride=false,$companyID=false, $hasTagIDs='', $inArr = '')
					$customers = zeroBS_getCustomers(true,$per_page,$page_number,$withInvoices,$withQuotes,$possibleSearchTerm,$withTransactions,$argsOverride,$possibleCoID,$possibleTagIDs,$inArray,$withTags,$withAssigned,$latestLog,$sortField,$sortOrder,$possibleQuickFilters,false,$withValues);

					#} If using pagination, also return total count
					if (isset($listViewParams['pagination']) && $listViewParams['pagination']){
						
						$res['objectcount'] = zeroBS_getCustomersCountIncParams($possibleSearchTerm,$argsOverride,$possibleCoID,$possibleTagIDs,$inArray,$possibleQuickFilters);

					}

					#} Tidy

	                    // glob as used below. not pretty
	                    global $companyNameCache;
						$companyNameCache = array();

					if (count($customers) > 0) {
					    foreach ($customers as $customer) {

                            // DAL3 now processes these in the OBJ class (starting to centralise properly.)
                            if ($zbs->isDAL3()){

                                $res['objects'][] = $zbs->DAL->contacts->listViewObj($customer,$columnsRequired);

                            } else {

                                // <DAL3

                                $resArr = $customer;

                                // catch autodraft errors pre dal2
                                $includeContact = true;

                                if (!$zbs->isDAL2()){
                                    if (isset($customer['name']) && $customer['name'] == 'Auto Draft') $includeContact = false;
                                }

                                if ($includeContact){

                                    //if (!$zbs->isDAL2())
                                    //	$resArr['avatar'] = '';
                                    //if (isset($resArr['email']))
                                    $resArr['avatar'] = zeroBS_customerAvatar($resArr['id']);


                                    #} Format the date in the list view..
                                    if (!$zbs->isDAL2()){

                                        $formatted_date = zeroBSCRM_date_i18n(-1, strtotime($resArr['created']));
                                        $resArr['created'] = $formatted_date;

                                    } else {

                                        // DAL2 use the uts
                                        $resArr['created'] = zeroBSCRM_date_i18n(-1, $resArr['createduts']);

                                    }


                                    #} Custom columns

                                        #} Total value
                                        if (in_array('totalvalue', $columnsRequired)){

                                            // DAL2 way, brutal effort.
                                            $resArr['totalvalue'] = zeroBSCRM_formatCurrency(zeroBS_customerTotalValue($customer['id'],$customer['invoices'],$customer['transactions']));

                                        }


                                        #} Quotes
                                        if (in_array('quotecount', $columnsRequired)){

                                            $resArr['quotes'] = $customer['quotes'];

                                        }
                                        if (in_array('quotetotal', $columnsRequired)){

                                            // DAL2 way, brutal effort.
                                            $resArr['quotestotal'] = zeroBSCRM_formatCurrency(zeroBS_customerQuotesValue($customer['id'],$customer['quotes']));

                                        }

                                        #} Invoices
                                        if (in_array('invoicecount', $columnsRequired)){

                                            $resArr['invoices'] = $customer['invoices'];

                                        }
                                        if (in_array('invoicetotal', $columnsRequired)){

                                            // DAL2 way, brutal effort.
                                            $resArr['invoicestotal'] = zeroBSCRM_formatCurrency(zeroBS_customerInvoicesValue($customer['id'],$customer['invoices']));

                                        }

                                        #} Transactions
                                        if (in_array('transactioncount', $columnsRequired)){

                                            $resArr['transactions'] = $customer['transactions'];

                                        }

                                        // < 3.0
                                        if (in_array('transactiontotal', $columnsRequired)){

                                            // DAL2 way, brutal effort.
                                            $resArr['transactionstotal'] = zeroBSCRM_formatCurrency(zeroBS_customerTransactionsValue($customer['id'],$customer['transactions']));

                                        }

                                        #} Tags
                                        if (in_array('tagged', $columnsRequired)){

                                            $resArr['tags'] = $customer['tags'];

                                        }

                                        #} Quotes
                                        if (in_array('hasquote', $columnsRequired)){

                                            $resArr['quotes'] = $customer['quotes'];

                                        }

                                        #} Invoices
                                        if (in_array('hasinvoice', $columnsRequired)){

                                            $resArr['invoices'] = $customer['invoices'];

                                        }

                                        // DAL2 :)
                                        if (isset($customer['lastcontacted'])){

                                            $resArr['lastcontacted'] = $customer['lastcontacted'];

                                        }

                                        #} latest log
                                        //if (in_array('latestlog', $columnsRequired)){
                                        if (isset($customer['lastlog'])){

                                            $resArr['lastlog'] = $customer['lastlog'];

                                            // only DAL1 :
                                            if (!$zbs->isDAL2() && isset($customer['lastcontactlog'])) $resArr['lastcontactlog'] = $customer['lastcontactlog'];

                                        }


                                        #} Assigned to
                                        if (in_array('assigned', $columnsRequired)){

                                            $resArr['owner'] = $customer['owner'];

                                            // Actually needs owner obj!
                                            if ($zbs->isDAL2() && isset($resArr['owner']) && !empty($resArr['owner'])) {

                                                $resArr['owner'] = zeroBS_getOwnerObj($resArr['owner']);
                                            }
                                        }

                                        #} Company
                                        if (in_array('company',$columnsRequired)){

                                            $resArr['company'] = false;

                                            #} Co Name Default
                                            $coName = '';

                                            // get
                                            $coID = zeroBS_getCustomerCompanyID($resArr['id']);//get_post_meta($post->ID,'zbs_company',true);
                                            if (!empty($coID)){

                                                // cache as we go
                                                if (!isset($companyNameCache[$coID])){

                                                    // get
                                                    $co = zeroBS_getCompany($coID);
                                                    if (isset($co) && isset($co['meta']) && isset($co['meta']['coname'])) $coName = $co['meta']['coname'];
                                                    # Shouldn't need this? WH attempted fix for caching, not here tho..
                                                    if (empty($coName) && isset($co['coname'])) $coName = $co['coname'];
                                                    if (empty($coName)) $coName = jpcrm_label_company().' #'.$co['id'];

                                                    // cache
                                                    $companyNameCache[$coID] = $coName;

                                                } else {
                                                    $coName = $companyNameCache[$coID];
                                                }
                                            }

                                            if ($coID > 0){
                                                $resArr['company'] = array('id'=>$coID,'name'=>$coName);
                                            }

                                        }

                                $res['objects'][] = $resArr;

                            } // / if include
                        } // / if not DAL3
                    } // / foreach
                }
				break;

			/* =================== / CUSTOMER ===============================================
			 ============================================================================= */



			/* ==============================================================================
			 ===================== COMPANY =============================================== */

				#} Company list view :) - ADDED BY MIKE 
				case 'company':

					#} Build query
					// now got by screenopt above $per_page = 20; 
					$page_number = 0;
					$possibleSearchTerm = '';
					$argsOverride = false;
					$possibleCoID = '';
					$possibleTagIDs = '';
					$possibleQuickFilters = '';
					$inArray = '';
					$withTags = false;
					$withAssigned = false;
					$latestLog = false;
					$withQuotes = false;
					$withInvoices = false;
					$withTransactions = false;
					$withValues = false;

					#} Sorting
					$sortField = 'id';
					$sortOrder = 'desc';

					#} Catch filters :)

						#} Search
						if (isset($listViewParams['filters']) && isset($listViewParams['filters']['s']) && !empty($listViewParams['filters']['s'])) $possibleSearchTerm = $listViewParams['filters']['s'];

						#} Tags
						if (isset($listViewParams['filters']) && isset($listViewParams['filters']['tags']) && is_array($listViewParams['filters']['tags'])) {

							$possibleTagIDs = array();
							foreach ($listViewParams['filters']['tags'] as $tagObj){
							
								// DAL2: 
								if (isset($tagObj['term_id'])) $possibleTagIDs[] = $tagObj['term_id'];
								// V3+: 
								if (isset($tagObj['id'])) $possibleTagIDs[] = $tagObj['id'];
							}

						}

						#} QuickFilters
						if (isset($listViewParams['filters']) && isset($listViewParams['filters']['quickfilters']) && is_array($listViewParams['filters']['quickfilters'])) {

							$possibleQuickFilters = array();
							foreach ($listViewParams['filters']['quickfilters'] as $quickFilter) $possibleQuickFilters[] = $quickFilter;

						}


					#} Tags
					if (in_array('tagged', $columnsRequired)){

						$withTags = true;

					}

					#} Assigned to
					if (in_array('assigned', $columnsRequired)){

						$withAssigned = true;

					}
					if (in_array('quotecount', $columnsRequired)){

						$withQuote = true;

					}
					if (in_array('invoicecount', $columnsRequired)){

						$withInvoices = true;

					}
					if (in_array('transactioncount', $columnsRequired)){

						$withTransactions = true;

					}


					#} Total val present?
					if (in_array('totalvalue', $columnsRequired)){

						if ($zbs->isDAL3()){

							$withValues = true;

						} else {
							// old way
							$withInvoices = true;
							$withTransactions = true;
						}

					}
					#} Quote val present?
					if (in_array('quotesvalue', $columnsRequired)){

						$withValues = true;

					}
					#} Invoices val present?
					if (in_array('invoicesvalue', $columnsRequired)){

						$withValues = true;

					}
					#} trans val present?
					if (in_array('transactiontotal', $columnsRequired) || in_array('transactionsvalue', $columnsRequired)){

						$withValues = true;

					}

					#} latest log

						// see if in notcontactedin (quickfilter)
						$hasQuickFilterForLogs = false; if (is_array($possibleQuickFilters) && count($possibleQuickFilters) > 0) foreach ($possibleQuickFilters as $pqf) if (substr($pqf,0,14) == 'notcontactedin') $hasQuickFilterForLogs = true;

						if (in_array('latestlog', $columnsRequired) || in_array('lastcontacted',$columnsRequired) || $hasQuickFilterForLogs){

							$latestLog = true;

						}

					#} Catch paging :)

						if (isset($listViewParams['paged']) && !empty($listViewParams['paged'])) {

							$possiblePage = (int)$listViewParams['paged'];
							if ($possiblePage > 0){

								// NVM! // it'll come in +1 (because this is zero-indexed, where as js is +1)
								$page_number = $possiblePage;
							}

						}
						//$res['paged'] = $page_number;

					#} Catch sorting

						if (isset($listViewParams['sort']) && !empty($listViewParams['sort'])) {

							$possSortField = $listViewParams['sort'];

								// DAL3+ not req:
								if (!$zbs->isDAL3()){

									#} Actually these need translating for now..
									switch($possSortField){

										case 'id':

											$sortField = 'post_id';

											break;

										// for now, sort nameavatar + name by post_title :)
										case 'name':

											$sortField = 'post_title';

											break;

										case 'nameavatar':

											$sortField = 'post_title';

											break;

										default:

											$sortField = '';

											break;

									}

								} else {

									// DAL3: allow all fields for now :) (little interpretation needed)
									if (!empty($possSortField) && $possSortField != false && $possSortField != "false"){
										$sortField = $possSortField;

										// ... and this
										if ($sortField == 'added') $sortField = 'created';
										if ($sortField == 'nameavatar') $sortField = 'fullname'; // TEMP
										if ($sortField == 'name') $sortField = 'fullname'; // TEMP
										if ($sortField == 'assigned') $sortField = 'zbs_owner'; // TEMP

									}
								}

							if (!empty($sortField)){
								
								$sortOrder = 'desc'; if (isset($listViewParams['sortorder']) && !empty($listViewParams['sortorder'])) $sortOrder = $listViewParams['sortorder'];

							}

						}


					#} Retrieve data
					//$withFullDetails=false,$perPage=10,$page=0,$searchPhrase='',$argsOverride=false, $hasTagIDs='', $inArr = '',$withTags=false,$withAssigned=false,$withLastLog=false,$sortByField='',$sortOrder='DESC',$quickFilters=false
					$companies = zeroBS_getCompaniesv2(true,$per_page,$page_number,$possibleSearchTerm,$argsOverride,$possibleTagIDs,$inArray,$withTags,$withAssigned,$latestLog,$sortField,$sortOrder,$possibleQuickFilters,$withTransactions,$withInvoices,$withQuotes,$withValues);

					#} If using pagination, also return total count
					if (isset($listViewParams['pagination']) && $listViewParams['pagination']){
						
						$res['objectcount'] = zeroBS_getCompaniesv2CountIncParams($possibleSearchTerm,$argsOverride,$possibleTagIDs,$inArray,$withTags,$withAssigned,$latestLog,$sortField,$sortOrder,$possibleQuickFilters);

					}


					#} Tidy
					if (count($companies) > 0) foreach ($companies as $company) {

						// DAL3 now processes these in the OBJ class (starting to centralise properly.)
						if ($zbs->isDAL3()){

							$res['objects'][] = $zbs->DAL->companies->listViewObj($company,$columnsRequired);

						} else {

							// <DAL3 Variant, bodgy/rough:
							$resArr = $company['meta'];

							$resArr['id'] = $company['id'];
						
							$resArr['name'] = $company['coname'];
							$resArr['avatar'] = zeroBS_customerAvatar($resArr['id']);

							#} Format the date in the list view..
							/*
							$d = new DateTime($company['created']);
							$formatted_date = $d->format(zeroBSCRM_getDateFormat());
							$resArr['added'] = $formatted_date;
							*/

							$formatted_date = zeroBSCRM_date_i18n(-1, strtotime($company['created']));
							$resArr['added'] = $formatted_date;

							#} Custom columns

								#} Tags
								if (in_array('tagged', $columnsRequired)){

									$resArr['tags'] = $company['tags'];

								}

								#} Assigned to
								if (in_array('assigned', $columnsRequired)){

									$resArr['owner'] = $company['owner'];

								}

								#} latest log
								//if (in_array('latestlog', $columnsRequired)){
								if (isset($company['lastlog'])){

									$resArr['lastlog'] = $company['lastlog'];
									$resArr['lastcontactlog'] = $company['lastcontactlog'];

								}

								#} Transactions
								if (in_array('transactioncount', $columnsRequired)){

									$resArr['transactions'] = $company['transactions'];
									
								}
								if (in_array('transactiontotal', $columnsRequired)){

									// for now this falls back on to 
									// zeroBS_customerTransactionsValue 
									// ... but is safe for now.
									$resArr['transactionstotal'] = zeroBSCRM_formatCurrency(zeroBS_customerTransactionsValue(-1,$company['transactions']));

								}

								// avatar mode
								$avatarMode = zeroBSCRM_getSetting( 'avatarmode' );

								#} Contacts at company
								$contactsAtCo = zeroBS_getCustomers(true,1000,0,false,false,'',false,false,$company['id']);

								$contactStr = '';
								foreach($contactsAtCo as $contact){

									if ( $avatarMode !== 3 ) {
										$contactStr .= zeroBS_getCustomerIcoLinked($contact['id']); // or zeroBS_getCustomerIcoLinkedLabel?
									} else {
										// no avatars, use labels
										$contactStr .= zeroBS_getCustomerLinkedLabel($contact['id']);
									}

								}

								$resArr['contacts'] = $contactStr;


							$res['objects'][] = $resArr;

						} // / <DAL3 variant

					}
				break;

			/* =================== / COMPANY ===============================================
			 ============================================================================= */



			/* ==============================================================================
			 ===================== QUOTE ================================================= */

				#} Quote List View
				case 'quote':

					#} Build query
					// now got by screenopt above $per_page = 20; 
					$page_number = 0;
					$possibleSearchTerm = '';
					$argsOverride = false;
					$possibleQuickFilters = '';
					$possibleTagIDs = '';
					$inArray = '';
					$withCustomer = false;

					#} Sorting
					$sortField = 'id';
					$sortOrder = 'desc';

					#} Catch filters :)

						#} Search
						if (isset($listViewParams['filters']) && isset($listViewParams['filters']['s']) && !empty($listViewParams['filters']['s'])) $possibleSearchTerm = $listViewParams['filters']['s'];

						#} Tags
						if (isset($listViewParams['filters']) && isset($listViewParams['filters']['tags']) && is_array($listViewParams['filters']['tags'])) {

							$possibleTagIDs = array();
							foreach ($listViewParams['filters']['tags'] as $tagObj){
							
								// DAL2: 
								if (isset($tagObj['term_id'])) $possibleTagIDs[] = $tagObj['term_id'];
								// V3+: 
								if (isset($tagObj['id'])) $possibleTagIDs[] = $tagObj['id'];
							}

						}
						
						#} QuickFilters
						if (isset($listViewParams['filters']) && isset($listViewParams['filters']['quickfilters']) && is_array($listViewParams['filters']['quickfilters'])) {

							$possibleQuickFilters = array();
							foreach ($listViewParams['filters']['quickfilters'] as $quickFilter) $possibleQuickFilters[] = $quickFilter;

						}

					#} Assigned to
					if (in_array('customer', $columnsRequired)){

						$withCustomer = true;

					}

					#} Catch paging :)

						if (isset($listViewParams['paged']) && !empty($listViewParams['paged'])) {

							$possiblePage = (int)$listViewParams['paged'];
							if ($possiblePage > 0){

								// NVM! // it'll come in +1 (because this is zero-indexed, where as js is +1)
								$page_number = $possiblePage;
							}

						}
						//$res['paged'] = $page_number;

					#} Catch sorting

						if (isset($listViewParams['sort']) && !empty($listViewParams['sort'])) {

							$possSortField = $listViewParams['sort'];

							// DAL3+ not req:
							if (!$zbs->isDAL3()){

								#} Actually these need translating for now..
								switch($possSortField){

									case 'id':

										$sortField = 'post_id';

										break;

									default:

										$sortField = '';

										break;

								}

							} else {

								// DAL3: allow all fields for now :) (little interpretation needed)
								if (!empty($possSortField) && $possSortField != false && $possSortField != "false"){
									
									$sortField = $possSortField;

									// ... and this
									if ($sortField == 'added') $sortField = 'created';
									if ($sortField == 'nameavatar') $sortField = 'fullname'; // TEMP
									if ($sortField == 'name') $sortField = 'fullname'; // TEMP
									if ($sortField == 'assigned') $sortField = 'zbs_owner'; // TEMP
									
								}
							}

							if (!empty($sortField)){
								
								$sortOrder = 'desc'; if (isset($listViewParams['sortorder']) && !empty($listViewParams['sortorder'])) $sortOrder = $listViewParams['sortorder'];

							}

						}

					#} Retrieve data
					// $withFullDetails=false,$perPage=10,$page=0,$withCustomerDeets=false,$searchPhrase='',$inArray=array(),$sortByField='',$sortOrder='DESC',$quickFilters=array()
					$quotes = zeroBS_getQuotes(true,$per_page,$page_number,true,$possibleSearchTerm,$inArray,$sortField,$sortOrder,$possibleQuickFilters,$possibleTagIDs);

					#} If using pagination, also return total count
					if (isset($listViewParams['pagination']) && $listViewParams['pagination']){
						
						$res['objectcount'] = zeroBS_getQuotesCountIncParams(true,$per_page,$page_number,true,$possibleSearchTerm,$inArray,$sortField,$sortOrder,$possibleQuickFilters,$possibleTagIDs);

					}


					#} Tidy
					if (count($quotes) > 0) foreach ($quotes as $quote) {

						// DAL3 now processes these in the OBJ class (starting to centralise properly.)
						if ($zbs->isDAL3()){

							$res['objects'][] = $zbs->DAL->quotes->listViewObj($quote,$columnsRequired);

						} else {

							// <DAL3

							$resArr = $quote['meta'];

							$resArr['id'] = $quote['id'];
							$resArr['zbsid'] = $quote['zbsid'];


							#} Custom columns

								#} Status
						        $resArr['statusint'] = zeroBS_getQuoteStatus($quote,true);

						        if ($resArr['statusint'] == -2){

						                #} is published
						                $resArr['status'] = '<span class="ui label orange">'.__('Not accepted yet',"zero-bs-crm").'</span>';

						        } else if ($resArr['statusint'] == -1){

						                #} not yet published
						                $resArr['status'] = '<span class="ui label grey">'.__('Draft',"zero-bs-crm").'</span>';


						        } else {

						        	#} Accepted
						        	$resArr['status'] = '<span class="ui label green">'.__('Accepted',"zero-bs-crm").' ' . date(zeroBSCRM_getDateFormat(),$resArr['statusint']) . '</span>';

						        } 

								#} customer
								if (in_array('customer', $columnsRequired)){

									if (isset($quote['customer'])) $resArr['customer'] = $quote['customer'];
									if (isset($resArr['customer'])){

										/* old way...
										// customer name
										$resArr['customer']['fullname'] = zeroBS_customerName('',$resArr['customer'],false,false);

										// grav
										$resArr['customer']['avatar'] = zeroBSCRM_getGravatarURLfromEmail($resArr['customer']['email']);
										*/
		
										// w adapted so same func can be used (generic) js side
										// works with zeroBSCRMJS_listView_generic_customer
										// provides a simplified ver of customer obj (4 data transit efficiency/exposure)
										$email = ''; 
										if (isset($quote['customer']['meta']['email'])) $email = $quote['customer']['meta']['email'];
										if (isset($quote['customer']['email']) && !empty($quote['customer']['email'])) $email = $quote['customer']['email'];
										$resArr['customer'] = array(

											'id' 		=> $quote['customer']['id'],
											'avatar'	=> zeroBS_customerAvatar($quote['customer']['id']),
											'fullname'	=> zeroBS_customerName('',$quote['customer'],false,false),
											'email' 	=> $email
										);
										if (in_array('assignedobj', $columnsRequired)) $resArr['customer']['owner'] = zeroBS_getOwner($quote['customer']['id'],true,'zerobs_customer');

									}

								}

								#} Format the date in the list view..
								$d = new DateTime($quote['created']);
								$formatted_date = $d->format(zeroBSCRM_getDateFormat());
								$resArr['added'] = $formatted_date;

								$resArr['val'] = zeroBSCRM_formatCurrency($resArr['val']);

								$res['objects'][] = $resArr;

						} // / <DAL3
					} // / foreach 
				break;


			/* =================== / QUOTE ==================================================
			 ============================================================================= */



			/* ==============================================================================
			 ===================== INVOICE =============================================== */

				case 'invoice':

					#} Build query
					// now got by screenopt above $per_page = 20; 
					$page_number = 0;
					$argsOverride = false;
					$possibleCoID = '';
					$possibleQuickFilters = '';
					$possibleSearchTerm = '';
					$possibleTagIDs = '';
					$inArray = '';
					$withCustomer = false;

					#} Sorting
					$sortField = 'id';
					$sortOrder = 'desc';


					#} Filters

						#} Search
						if (isset($listViewParams['filters']) && isset($listViewParams['filters']['s']) && !empty($listViewParams['filters']['s'])) $possibleSearchTerm = $listViewParams['filters']['s'];

						#} Tags
						if (isset($listViewParams['filters']) && isset($listViewParams['filters']['tags']) && is_array($listViewParams['filters']['tags'])) {

							$possibleTagIDs = array();
							foreach ($listViewParams['filters']['tags'] as $tagObj){
							
								// DAL2: 
								if (isset($tagObj['term_id'])) $possibleTagIDs[] = $tagObj['term_id'];
								// V3+: 
								if (isset($tagObj['id'])) $possibleTagIDs[] = $tagObj['id'];
							}

						}

						#} QuickFilters
						if (isset($listViewParams['filters']) && isset($listViewParams['filters']['quickfilters']) && is_array($listViewParams['filters']['quickfilters'])) {

							$possibleQuickFilters = array();
							foreach ($listViewParams['filters']['quickfilters'] as $quickFilter) $possibleQuickFilters[] = $quickFilter;

						}

					#} Assigned to
					if (in_array('customer', $columnsRequired)){

						$withCustomer = true;

					}

					#} Catch paging :)

						if (isset($listViewParams['paged']) && !empty($listViewParams['paged'])) {

							$possiblePage = (int)$listViewParams['paged'];
							if ($possiblePage > 0){

								// NVM! // it'll come in +1 (because this is zero-indexed, where as js is +1)
								$page_number = $possiblePage;
							}

						}
						//$res['paged'] = $page_number;

					#} Catch sorting

						if (isset($listViewParams['sort']) && !empty($listViewParams['sort'])) {

							$possSortField = $listViewParams['sort'];

							// DAL3+ not req:
							if (!$zbs->isDAL3()){

								#} Actually these need translating for now..
								switch($possSortField){

									case 'id':

										$sortField = 'post_id';

										break;

									default:

										$sortField = '';

										break;

								}

							} else {

								// DAL3: allow all fields for now :) (little interpretation needed)
								if (!empty($possSortField) && $possSortField != false && $possSortField != "false"){
									
									$sortField = $possSortField;

									// ... and this
									if ($sortField == 'added') $sortField = 'created';
									if ($sortField == 'nameavatar') $sortField = 'fullname'; // TEMP
									if ($sortField == 'name') $sortField = 'fullname'; // TEMP
									if ($sortField == 'assigned') $sortField = 'zbs_owner'; // TEMP
									
								}
							}

							if (!empty($sortField)){
								
								$sortOrder = 'desc'; if (isset($listViewParams['sortorder']) && !empty($listViewParams['sortorder'])) $sortOrder = $listViewParams['sortorder'];

							}

						}


					#} Retrieve data
					// MS: $invoices = zeroBS_getInvoicesv2(true,$per_page,$page_number,true,$possibleSearchTerm,$possibleTagIDs,$inArray,$possibleQuickFilters);
					// WH: Moved back to original
					$invoices = zeroBS_getInvoices(true,$per_page,$page_number,$withCustomer,$possibleSearchTerm,$inArray,$sortField,$sortOrder,$possibleQuickFilters,$possibleTagIDs);

					#} If using pagination, also return total count
					if (isset($listViewParams['pagination']) && $listViewParams['pagination']){
						
						$res['objectcount'] = zeroBS_getInvoicesCountIncParams(true,$per_page,$page_number,$withCustomer,$possibleSearchTerm,$inArray,$sortField,$sortOrder,$possibleQuickFilters,$possibleTagIDs);

					}


					#} Tidy
					if (count($invoices) > 0) foreach ($invoices as $invoice) {

						// DAL3 now processes these in the OBJ class (starting to centralise properly.)
						if ($zbs->isDAL3()){

							$res['objects'][] = $zbs->DAL->invoices->listViewObj($invoice,$columnsRequired);

						} else {

							// <DAL3

							$resArr = array();

							// not sure about all of these, inv mapping seems quite messy... TODO - redo inv mapping with new db
							if (in_array('customer', $columnsRequired)){
								if (isset($invoice['customer'])){

									/* cleaner... 
									$resArr['customername'] 	=     $invoice['customer']['name'];
									$resArr['email'] 			=     $invoice['customer']['email'];
									$resArr['customerid'] 		= 	  $invoice['customer']['id'];
									$resArr['avatar'] 		= zeroBSCRM_getGravatarURLfromEmail($resArr['email']);
									*/
			
									// w adapted so same func can be used (generic) js side
									// works with zeroBSCRMJS_listView_generic_customer
									// provides a simplified ver of customer obj (4 data transit efficiency/exposure)
									$email = ''; 
									if (isset($invoice['customer']['meta']['email'])) $email = $invoice['customer']['meta']['email'];
									if (isset($invoice['customer']['email']) && !empty($invoice['customer']['email'])) $email = $invoice['customer']['email'];
									$resArr['customer'] = array(

										'id' 		=> $invoice['customer']['id'],
										'avatar'	=> zeroBS_customerAvatar($invoice['customer']['id']),
										'fullname'	=> zeroBS_customerName('',$invoice['customer'],false,false),
										'email' 	=> $email

									);
									if (in_array('assignedobj', $columnsRequired)) $resArr['customer']['owner'] = zeroBS_getOwner($invoice['customer']['id'],true,'zerobs_customer');

								}
							} else $resArr['customer'] = false;

							// company info, if present :)
							if (isset($invoice['company']) && is_array($invoice['company'])){

									$resArr['company'] = array(
										//debug 'x' => $transaction['company'],
										'id' 		=> $invoice['company']['id'],
										'fullname'	=> $invoice['company']['name']//zeroBS_companyName('',$transaction['company'],false,false)

									);
									if (in_array('assignedobj', $columnsRequired)) $resArr['company']['owner'] = zeroBS_getOwner($invoice['company']['id'],true,'zerobs_company');

							} else $resArr['company'] = false;


							$resArr['meta']		= $invoice['meta'];

							/* Moved to JS - drawing should be done there, not here...
							switch($invoice['meta']['status']){
								case 'Draft':
								$color = 'grey';
								break;
								case 'Unpaid':
								$color = 'orange';
								break;
								case 'Paid':
								$color = 'green';
								break;
								case 'Overdue':
								$color = 'red';
								break;

							}

							$resArr['status']		= "<span class='ui label ".$color."'>" . $invoice['meta']['status'] . "</span>";
							*/

							$resArr['id'] = $invoice['id'];
							$resArr['zbsid'] = $invoice['zbsid'];

							// title... I suspect you mean ref?
							$resArr['title'] = ''; if (isset($invoice['meta']['name'])) $resArr['title'] = $invoice['meta']['name'];
							if (isset($invoice['meta']['ref']) && empty($resArr['title'])) $resArr['title'] = $invoice['meta']['ref'];

							// status
							$resArr['status'] = ''; if (isset($invoice['meta']['status'])) $resArr['status'] = $invoice['meta']['status'];

							// wh: had to add due date here for new list view col manager 2.95+
							$resArr['duedate'] = -1;

							// date?
							if (isset($invoice['meta']['date'])) {
						//		$d = new DateTime($invoice['meta']['date']);
						//		$invoiceatted_date = $d->format(zeroBSCRM_getDateFormat());

								$invoiceatted_date = zeroBSCRM_date_i18n(-1, strtotime($invoice['meta']['date']));

								$resArr['added'] = $invoiceatted_date;

								// due?
								if (isset($invoice['meta']['due'])) {

									$due = (int)$invoice['meta']['due'];
									if ($due <= 0) 
										$resArr['duedate'] = $resArr['added'];
									else {
										// calc
										$resArr['duedate'] = zeroBSCRM_date_i18n(-1, (strtotime($invoice['meta']['date'])+($due*86400)));
									}

								}
							}

							//format currency handles if the amount is blank (sends it to 0)
							// WH: YES but it doesn't check if isset / stop php notice $resArr['value'] = zeroBSCRM_formatCurrency($invoice['meta']['val']);
							$resArr['value'] = zeroBSCRM_formatCurrency(0); if (isset($invoice['meta']['val'])) $resArr['value'] = zeroBSCRM_formatCurrency($invoice['meta']['val']);

							$res['objects'][] = $resArr;

						} // / is <DAL3
					} // / foreach
				break;


			/* =================== / INVOICE ================================================
			 ============================================================================= */



			/* ==============================================================================
			 ===================== TRANSACTION =========================================== */

				#} Transaction list view :) 
				case 'transaction':

					#} Build query
					// now got by screenopt above $per_page = 20; 
					$page_number          = 0;
					$possibleSearchTerm   = '';
					$argsOverride         = false;
					$possibleCoID         = '';
					$possibleTagIDs       = '';
					$possibleQuickFilters = '';
					$inArray              = '';
					$withTags             = false;
					$withCustomer         = true;
					$latestLog            = false;
					$external_source_uid  = true;

					#} Sorting
					$sortField = 'id';
					$sortOrder = 'desc';

					#} Catch filters :)

						#} Search
						if (isset($listViewParams['filters']) && isset($listViewParams['filters']['s']) && !empty($listViewParams['filters']['s'])) $possibleSearchTerm = $listViewParams['filters']['s'];

						#} Tags
						if (isset($listViewParams['filters']) && isset($listViewParams['filters']['tags']) && is_array($listViewParams['filters']['tags'])) {

							$possibleTagIDs = array();
							foreach ($listViewParams['filters']['tags'] as $tagObj){
							
								// DAL2: 
								if (isset($tagObj['term_id'])) $possibleTagIDs[] = $tagObj['term_id'];
								// V3+: 
								if (isset($tagObj['id'])) $possibleTagIDs[] = $tagObj['id'];
							}

						}

						#} QuickFilters
						if (isset($listViewParams['filters']) && isset($listViewParams['filters']['quickfilters']) && is_array($listViewParams['filters']['quickfilters'])) {

							$possibleQuickFilters = array();
							foreach ($listViewParams['filters']['quickfilters'] as $quickFilter) $possibleQuickFilters[] = $quickFilter;

						}


					#} Tags
					if (in_array('tagged', $columnsRequired)){

						$withTags = true;

					}


					#} Assigned to
					if (in_array('customer', $columnsRequired)){

						$withCustomer = true;

					}

					#} Catch paging :)

						if (isset($listViewParams['paged']) && !empty($listViewParams['paged'])) {

							$possiblePage = (int)$listViewParams['paged'];
							if ($possiblePage > 0){

								// NVM! // it'll come in +1 (because this is zero-indexed, where as js is +1)
								$page_number = $possiblePage;
							}

						}
						//$res['paged'] = $page_number;

					#} Catch sorting

						if (isset($listViewParams['sort']) && !empty($listViewParams['sort'])) {

							$possSortField = $listViewParams['sort'];

							// DAL3+ not req:
							if (!$zbs->isDAL3()){

								#} Actually these need translating for now..
								switch($possSortField){

									case 'id':

										$sortField = 'post_id';

										break;

									default:

										$sortField = '';

										break;

								}

							} else {

								// DAL3: allow all fields for now :) (little interpretation needed)
								if (!empty($possSortField) && $possSortField != false && $possSortField != "false"){
									
									$sortField = $possSortField;

									// ... and this
									if ($sortField == 'added') $sortField = 'created';
									if ($sortField == 'nameavatar') $sortField = 'fullname'; // TEMP
									if ($sortField == 'name') $sortField = 'fullname'; // TEMP
									if ($sortField == 'assigned') $sortField = 'zbs_owner'; // TEMP
									
								}
							}

							if (!empty($sortField)){
								
								$sortOrder = 'desc'; if (isset($listViewParams['sortorder']) && !empty($listViewParams['sortorder'])) $sortOrder = $listViewParams['sortorder'];

							}

						}

					// Retrieve data
					$transactions = zeroBS_getTransactions( true, $per_page, $page_number, $withCustomer, $possibleSearchTerm, $possibleTagIDs, $inArray, $sortField, $sortOrder, $withTags, $possibleQuickFilters, $external_source_uid );

					// If using pagination, also return total count
					if (isset($listViewParams['pagination']) && $listViewParams['pagination']){
						
						$res['objectcount'] = zeroBS_getTransactionsCountIncParams(true,$per_page,$page_number,$withCustomer,$possibleSearchTerm,$possibleTagIDs,$inArray,$sortField,$sortOrder,$withTags,$possibleQuickFilters);

					}

					// Tidy
					if (count($transactions) > 0) foreach ($transactions as $transaction) {

						// DAL3 now processes these in the OBJ class (starting to centralise properly.)
						if ($zbs->isDAL3()){

							$res['objects'][] = $zbs->DAL->transactions->listViewObj($transaction,$columnsRequired);

						} else {

							// <DAL3
						
							$resArr = $transaction['meta'];
							$resArr['total'] = zeroBSCRM_formatCurrency($resArr['total']);
							$resArr['orderid'] = strlen($transaction['meta']['orderid']) > 7 ? substr($transaction['meta']['orderid'],0,7)."..." : $transaction['meta']['orderid'];
							$resArr['id'] = $transaction['id'];
							$resArr['status'] = ucfirst($transaction['meta']['status']); 

							// This wasn't working: $d = new DateTime($transaction['meta']['date']); 
							// ... so I added the correct field (post_date) to getTransactions and piped in here
							$d = new DateTime($transaction['date']); 
							$formatted_date = $d->format(zeroBSCRM_getDateFormat());
							$resArr['added'] = $formatted_date;

							if (isset($transaction['customer']) && is_array($transaction['customer'])){

									// w adapted so same func can be used (generic) js side
									// works with zeroBSCRMJS_listView_generic_customer
									// provides a simplified ver of customer obj (4 data transit efficiency/exposure)
									$email = ''; 
									if (isset($transaction['customer']['meta']['email'])) $email = $transaction['customer']['meta']['email'];
									if (isset($transaction['customer']['email']) && !empty($transaction['customer']['email'])) $email = $transaction['customer']['email'];
									$resArr['customer'] = array(

										'id' 		=> $transaction['customer']['id'],
										'avatar'	=> zeroBS_customerAvatar($transaction['customer']['id']),
										'fullname'	=> zeroBS_customerName('',$transaction['customer'],false,false),
										'email' 	=> $email

									);
									if (in_array('assignedobj', $columnsRequired)) $resArr['customer']['owner'] = zeroBS_getOwner($transaction['customer']['id'],true,'zerobs_customer');

							} else $resArr['customer'] = false;

							if (isset($transaction['company']) && is_array($transaction['company'])){

									$resArr['company'] = array(
										//debug 'x' => $transaction['company'],
										'id' 		=> $transaction['company']['id'],
										'fullname'	=> $transaction['company']['name']//zeroBS_companyName('',$transaction['company'],false,false)

									);
									if (in_array('assignedobj', $columnsRequired)) $resArr['company']['owner'] = zeroBS_getOwner($transaction['company']['id'],true,'zerobs_company');

							} else $resArr['company'] = false;

							#} Tags
							if (in_array('tagged', $columnsRequired)){

								$resArr['tags'] = $transaction['tags'];

							}

							$res['objects'][] = $resArr;

						} // / <DAL3
					} // / foreach
				break;


			/* =================== / TRANSACTION ============================================
			 ============================================================================= */



			/* ==============================================================================
			 ===================== FORM ================================================== */

				#} Form list view :) ADDED BY MS - WARY ABOUT WHAT TO COMMENT OUT HERE
				case 'form':

					#} Build query
					// now got by screenopt above $per_page = 20; 
					$page_number = 0;
					$possibleSearchTerm = '';
					$withInvoices = false;
					$withQuotes = false;
					$withTransactions = false;
					$argsOverride = false;
					$possibleCoID = '';
					$possibleTagIDs = '';
					$possibleQuickFilters = '';
					$inArray = '';
					$withTags = false;
					$withAssigned = false;
					$latestLog = false;

					#} Sorting
					$sortField = 'id';
					$sortOrder = 'desc';



					#} Search
					if (isset($listViewParams['filters']) && isset($listViewParams['filters']['s']) && !empty($listViewParams['filters']['s'])) $possibleSearchTerm = $listViewParams['filters']['s'];

					#} Tags
					if (isset($listViewParams['filters']) && isset($listViewParams['filters']['tags']) && is_array($listViewParams['filters']['tags'])) {

						$possibleTagIDs = array();
						foreach ($listViewParams['filters']['tags'] as $tagObj){
						
							// DAL2: 
							if (isset($tagObj['term_id'])) $possibleTagIDs[] = $tagObj['term_id'];
							// V3+: 
							if (isset($tagObj['id'])) $possibleTagIDs[] = $tagObj['id'];
						}

					}




					#} Catch paging :)

						if (isset($listViewParams['paged']) && !empty($listViewParams['paged'])) {

							$possiblePage = (int)$listViewParams['paged'];
							if ($possiblePage > 0){

								// NVM! // it'll come in +1 (because this is zero-indexed, where as js is +1)
								$page_number = $possiblePage;
							}

						}
						//$res['paged'] = $page_number;

					#} Catch sorting

						if (isset($listViewParams['sort']) && !empty($listViewParams['sort'])) {

							$possSortField = $listViewParams['sort'];

							// DAL3+ not req:
							if (!$zbs->isDAL3()){

								#} Actually these need translating for now..
								switch($possSortField){

									case 'id':

										$sortField = 'post_id';

										break;

									default:

										$sortField = '';

										break;

								}

							} else {

								// DAL3: allow all fields for now :) (little interpretation needed)
								if (!empty($possSortField) && $possSortField != false && $possSortField != "false"){
									
									$sortField = $possSortField;

									// ... and this
									if ($sortField == 'added') $sortField = 'created';
									if ($sortField == 'nameavatar') $sortField = 'fullname'; // TEMP
									if ($sortField == 'name') $sortField = 'fullname'; // TEMP
									if ($sortField == 'assigned') $sortField = 'zbs_owner'; // TEMP
									
								}
							}

							if (!empty($sortField)){
								
								$sortOrder = 'desc'; if (isset($listViewParams['sortorder']) && !empty($listViewParams['sortorder'])) $sortOrder = $listViewParams['sortorder'];

							}

						}


					#} Retrieve data
					//($withFullDetails=false,$perPage=10,$page=0,$withInvoices=false,$withQuotes=false,$searchPhrase='',$withTransactions=false,$argsOverride=false,$companyID=false, $hasTagIDs='', $inArr = '')

					#} Retrieve data
					// old
					// $withFullDetails=false,$perPage=10,$page=0,$withCustomerDeets=false, $possibleSearchTerm,$possibleTagIDs,$inArray,$possibleQuickFilters

					// new 
					//

					$forms = zeroBS_getForms(false,$per_page,$page_number,$possibleSearchTerm,$inArray,$sortField,$sortOrder,$possibleQuickFilters,$possibleTagIDs);



					#} If using pagination, also return total count
					if (isset($listViewParams['pagination']) && $listViewParams['pagination']){
						
						$res['objectcount'] = zeroBS_getFormsCountIncParams(false,$per_page,$page_number,$possibleSearchTerm,$inArray,$sortField,$sortOrder,$possibleQuickFilters,$possibleTagIDs);

					}

					#} Tidy
					if (count($forms) > 0) foreach ($forms as $form) {

						// DAL3 now processes these in the OBJ class (starting to centralise properly.)
						if ($zbs->isDAL3()){

							$res['objects'][] = $zbs->DAL->forms->listViewObj($form,$columnsRequired);

						} else {

							// <DAL3
							$resArr['style'] 		= get_post_meta($form['id'], 'zbs_form_style',true);
							$resArr['views'] 		= get_post_meta($form['id'], 'zbs_form_views',true);
							$resArr['conversions'] 	= get_post_meta($form['id'], 'zbs_form_conversions',true);
							$resArr['id'] = $form['id'];
							$resArr['title'] = $form['title'];

							$d = new DateTime($form['created']);
							$formatted_date = $d->format(zeroBSCRM_getDateFormat());

							$resArr['added'] = $formatted_date;
							$res['objects'][] = $resArr;

						} // / <DAL3
					} // / foreach
				break;


			/* =================== / FORM ===================================================
			 ============================================================================= */



			/* ==============================================================================
			 ===================== SEGMENT =============================================== */
								

				case 'segment':

					#} Build query
					// now got by screenopt above $per_page = 20; 
					$page_number = 0;
					$ownerID = -99;
					$possibleSearchTerm = '';
					$withAudienceCount = false;
					$argsOverride = false;
					$inArray = '';

					#} Sorting
					$sortField = 'ID';
					$sortOrder = 'DESC';

					#} Catch filters :)

						#} Search
						if (isset($listViewParams['filters']) && isset($listViewParams['filters']['s']) && !empty($listViewParams['filters']['s'])) $possibleSearchTerm = $listViewParams['filters']['s'];


					#} latest log
					if (in_array('audiencecount', $columnsRequired)){

						$withAudienceCount = true;

					}

					#} Catch paging :)

						if (isset($listViewParams['paged']) && !empty($listViewParams['paged'])) {

							$possiblePage = (int)$listViewParams['paged'];
							if ($possiblePage > 0){

								// NVM! // it'll come in +1 (because this is zero-indexed, where as js is +1)
								$page_number = $possiblePage;
							}

						}
						//$res['paged'] = $page_number;

					#} Catch sorting

						if (isset($listViewParams['sort']) && !empty($listViewParams['sort'])) {

							$possSortField = $listViewParams['sort'];

								#} Actually these need translating for now..
								switch($possSortField){

									case 'id':

										$sortField = 'ID';

										break;

									case 'name':

										$sortField = 'zbsseg_name';

										break;

									case 'added':

										$sortField = 'zbsseg_created';

										break;

									// todo
									/*case 'audiencecount':


										$sortField = 'post_title';

										break;*/

									default:

										$sortField = '';

										break;

								}

							if (!empty($sortField)){
								
								$sortOrder = 'DESC'; if (isset($listViewParams['sortorder']) && !empty($listViewParams['sortorder'])) $sortOrder = strtoupper($listViewParams['sortorder']);

							}

						}


					#} Retrieve data
					$segments = $zbs->DAL->segments->getSegments($ownerID,$per_page,$page_number,false,$possibleSearchTerm,$inArray,$sortField,$sortOrder);

					#} If using pagination, also return total count
					if (isset($listViewParams['pagination']) && $listViewParams['pagination']){
						
						$res['objectcount'] = $zbs->DAL->segments->getSegmentsCountIncParams($ownerID,$per_page,$page_number,false,$possibleSearchTerm,$inArray,$sortField,$sortOrder);

					}

					#} No need to tidy from our straight-from-db stuff
					// actually I do, to simplify ui

					/* MOVED THIS INTO DAL
					#} Tidy
					if (count($segments) > 0) foreach ($segments as $segment) {
						$resArr = array();
						$resArr['id'] = $segment->zbssegid;
						$resArr['created'] = $segment->zbsseg_created;
						$resArr['lastupdated'] = $segment->zbsseg_lastupdated;
						$resArr['lastcompiled'] = $segment->zbsseg_lastcompiled;
						$resArr['name'] = $segment->zbsseg_name;
						$res['objects'][] = $resArr;

					} */

					$res['objects'] = $segments;

				break;


			/* =================== / SEGMENT ================================================
			 ============================================================================= */


			/* ==============================================================================
			 ===================== QUOTE TEMPLATE ======================================== */

				case 'quotetemplate':

					#} Build query
					// now got by screenopt above $per_page = 20; 
					$page_number = 0;
					$possibleSearchTerm = '';
					$withInvoices = false;
					$withQuotes = false;
					$withTransactions = false;
					$argsOverride = false;
					$possibleCoID = '';
					$possibleTagIDs = '';
					$possibleQuickFilters = '';
					$inArray = '';
					$withTags = false;
					$withAssigned = false;
					$latestLog = false;

					#} Sorting
					$sortField = 'id';
					$sortOrder = 'desc';

					#} Search
					if (isset($listViewParams['filters']) && isset($listViewParams['filters']['s']) && !empty($listViewParams['filters']['s'])) $possibleSearchTerm = $listViewParams['filters']['s'];

					#} Catch paging :)

						if (isset($listViewParams['paged']) && !empty($listViewParams['paged'])) {

							$possiblePage = (int)$listViewParams['paged'];
							if ($possiblePage > 0){

								// NVM! // it'll come in +1 (because this is zero-indexed, where as js is +1)
								$page_number = $possiblePage;
							}

						}
						//$res['paged'] = $page_number;

					#} Catch sorting

						if (isset($listViewParams['sort']) && !empty($listViewParams['sort'])) {

							$possSortField = $listViewParams['sort'];

							// DAL3: allow all fields for now :) (little interpretation needed)
							if (!empty($possSortField) && $possSortField != false && $possSortField != "false"){
								
								$sortField = $possSortField;

								// ... and this
								if ($sortField == 'added') $sortField = 'created';
								if ($sortField == 'assigned') $sortField = 'zbs_owner';
								
							}

							if (!empty($sortField)){
								
								$sortOrder = 'desc'; if (isset($listViewParams['sortorder']) && !empty($listViewParams['sortorder'])) $sortOrder = $listViewParams['sortorder'];

							}

						}


					#} Retrieve data
					$quoteTemplates = zeroBS_getQuoteTemplates(false,$per_page,$page_number,$possibleSearchTerm);

					#} If using pagination, also return total count
					if (isset($listViewParams['pagination']) && $listViewParams['pagination']){
						
						$res['objectcount'] = zeroBS_getQuoteTemplatesCountIncParams(false,$per_page,$page_number,$possibleSearchTerm);

					}

					#} Tidy
					if (count($quoteTemplates) > 0) foreach ($quoteTemplates as $quoteTemplate) {

						// DAL3 now processes these in the OBJ class (starting to centralise properly.)
						$res['objects'][] = $zbs->DAL->quotetemplates->listViewObj($quoteTemplate,$columnsRequired);

					} // / foreach

				break;


			/* =================== / QUOTE TEMPLATE =========================================
			 ============================================================================= */



			/* ==============================================================================
			 ===================== EVENT ================================================= */

				case 'event':

					// build query
					$page_number = 0;
					$possibleSearchTerm = '';
					$argsOverride = false;
					$possibleTagIDs = '';
					$possibleQuickFilters = array();
					$inArray = '';
					$withTags = false;
					$withAssigned = false;

					#} Sorting
					$sortField = 'id';
					$sortOrder = 'desc';



					// Search
					if ( !empty( $listViewParams['filters']['s'] ) ) $possibleSearchTerm = $listViewParams['filters']['s'];

					// Tags
					if ( !empty( $listViewParams['filters']['tags'] ) && is_array( $listViewParams['filters']['tags'] ) ) {

						$possibleTagIDs = array();
						foreach ($listViewParams['filters']['tags'] as $tagObj){
						
							// DAL2: 
							if (isset($tagObj['term_id'])) $possibleTagIDs[] = $tagObj['term_id'];
							// V3+: 
							if (isset($tagObj['id'])) $possibleTagIDs[] = $tagObj['id'];
						}

					}

					// QuickFilters
					if (isset($listViewParams['filters']) && isset($listViewParams['filters']['quickfilters']) && is_array($listViewParams['filters']['quickfilters'])) {

						foreach ($listViewParams['filters']['quickfilters'] as $quickFilter) $possibleQuickFilters[] = $quickFilter;

					}

					// Catch paging :)
					if (isset($listViewParams['paged']) && !empty($listViewParams['paged'])) {

						$possiblePage = (int)$listViewParams['paged'];
						if ($possiblePage > 0){

							// NVM! // it'll come in +1 (because this is zero-indexed, where as js is +1)
							$page_number = $possiblePage;
						}

					}

					// Catch sorting
					if (isset($listViewParams['sort']) && !empty($listViewParams['sort'])) {

						$possSortField = $listViewParams['sort'];

						// DAL3: allow all fields for now :) (little interpretation needed)
						if (!empty($possSortField) && $possSortField != false && $possSortField != "false"){
							
							$sortField = $possSortField;

							switch ( $sortField ){

								case 'added':
									$sortField = 'created';
									break;								
								case 'assigned':
									$sortField = 'zbs_owner';
									break;
								case 'status':
									$sortField = 'zbse_complete';
									break;
								case 'start':
								case 'end':
								case 'title':
								case 'desc':
									$sortField = 'zbse_' . $sortField;
									break;

							}


							
						}

						if (!empty($sortField)){
							
							$sortOrder = 'desc'; if (isset($listViewParams['sortorder']) && !empty($listViewParams['sortorder'])) $sortOrder = $listViewParams['sortorder'];

						}

					}

					if ( $zbs->isDAL3() ){

						//if ($page_number < 0) $page_number = 0;

						// make ARGS
						$args = array(

							'withAssigned'  => true,
							'withOwner'		=> true,

							'isTagged'  	=> $possibleTagIDs,

							'sortByField' 	=> $sortField,
							'sortOrder' 	=> $sortOrder,

							'page'			=> $page_number,
							'perPage'		=> $per_page,

							'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_EVENT)

						);

						// owner
						//if ($ownedByID > 0) $args['ownedBy'] = $ownedByID;


						// search term
						if ( !empty( $possibleSearchTerm ) ) $args['searchPhrase'] = $possibleSearchTerm;

						// filters
						foreach ( $possibleQuickFilters as $quick_filter ){

							switch ( $quick_filter ){

								case 'status_incomplete':

									$args['isIncomplete'] = true;

									break;

								case 'status_completed':

									$args['isComplete'] = true;

									break;

								case 'next30':

									$args['datedAfter'] = time() - ( 60 * 60 ); // add an hour's leeway
									$args['datedBefore'] = strtotime( '1 month' );

									break;

								case 'last30':

									$args['datedAfter'] = strtotime( '-1 months' );
									$args['datedBefore'] = strtotime( '+1 days' );

									break;

								case 'next7':

									$args['datedAfter'] = time() - ( 60 * 60 ); // add an hour's leeway
									$args['datedBefore'] = strtotime( '+7 days' );

									break;

								case 'last7':

									$args['datedAfter'] = strtotime( '-7 days' );
									$args['datedBefore'] = strtotime( '+1 days' );

									break;

							}


						}

						$events = $zbs->DAL->events->getEvents( $args );

						#} If using pagination, also return total count
						if ( isset($listViewParams['pagination']) && $listViewParams['pagination'] ){
							
							// get count
							$args['count'] = true;
							$args['page'] = -1;
							$args['perPage'] = -1;

							$res['objectcount'] = $zbs->DAL->events->getEvents( $args );

						}



					} else {

						// fallback < DAL3

						// Retrieve data
						$events = zeroBS_getEvents(false, $per_page, $page_number, false, $possibleSearchTerm, $sortField, $sortOrder, $possibleTagIDs );

						#} If using pagination, also return total count
						if (isset($listViewParams['pagination']) && $listViewParams['pagination']){
							
							$res['objectcount'] = zeroBS_getEventsCountIncParams(false, $per_page, $page_number, false, $possibleSearchTerm, $sortField, $sortOrder, $possibleTagIDs );

						}



					}



					#} Tidy
					if (count($events) > 0) foreach ($events as $event) {

						// DAL3 now processes these in the OBJ class (starting to centralise properly.)
						if ( $zbs->isDAL3() ){

							$res['objects'][] = $zbs->DAL->events->listViewObj( $event, $columnsRequired );

						} else {

							// <DAL3
							// only offering very limited support here for < DAL3.
							$resArr['id'] = $event['id'];
							$resArr['title'] = $event['title'];

							$d = new DateTime($event['created']);
							$formatted_date = $d->format(zeroBSCRM_getDateFormat());

							$resArr['added'] = $formatted_date;
							$res['objects'][] = $resArr;

						} // / <DAL3

					} // / foreach

				break;


			/* =================== / EVENT ==================================================
			 ============================================================================= */



			

				#} Default = non hard typed listtype !
				default:

					// allow bolt-ins from extensions (mailcamps uses this)
					// funcs which fire here have to return internally, they can't rely on $res return
					do_action('zerobs_ajax_list_view_'.$listViewParams['listtype'],$listViewParams);

					// err really

				break;

			}
		}

		// debug $res = array(isset($listViewParams),gettype($listViewParams) == 'array',isset($listViewParams['listtype']));

		header('Content-Type: application/json');
		echo json_encode($res);
		exit();

	}

	#} Enact some bulk action :)
	add_action( 'wp_ajax_enactListViewBulkAction', 'zeroBSCRM_AJAX_enactListViewBulkAction' );
	function zeroBSCRM_AJAX_enactListViewBulkAction(){

		#} Check nonce
		check_ajax_referer( 'zbscrmjs-ajax-nonce', 'sec' );

		#} Check perms
		if (!zeroBSCRM_permsCustomers()) { header('Content-Type: application/json'); exit('{err:1}'); }

		// ret
		$passBack = array();

			global $zbs;

			#} Retrieve
			$objtype = ''; if (isset($_POST['objtype'])) $objtype = sanitize_text_field($_POST['objtype']);
			$actionstr = ''; if (isset($_POST['actionstr'])) $actionstr = sanitize_text_field($_POST['actionstr']);
			$idsToChange = zeroBSCRM_dataIO_postedArrayOfInts($_POST['ids']);

			// Check ID's legit
			$legitIDs = array(); if (is_array($idsToChange) && count($idsToChange) > 0) foreach ($idsToChange as $id){

				$intID = (int)$id;
				if ($intID > 0) $legitIDs[] = $intID;

			}

			// Any ID's to process?
			if (count($legitIDs) > 0){

				// Switch by type
				switch ($objtype){

					case 'customer':

							// Actions:
							switch ($actionstr){

								// delete customers
								case 'delete':

									// delete sub stuff?
									$leaveOrphans = true; 

									if ( isset( $_POST['leaveorphans'] )) {
										if($_POST['leaveorphans'] == "0"){
											$leaveOrphans = false;
										}
									}

									// cycle through + delete (should have sanity checked via SWAL)
									$deleted = 0;
									foreach ($legitIDs as $id){

										// delete all orphans
										zeroBS_deleteCustomer($id,$leaveOrphans);
										$deleted++;

									}

									$passBack['deleted'] = $deleted;

									#} Return
									header('Content-Type: application/json'); 
									echo json_encode($passBack);
									exit();


									break;

								// change status
								case 'changestatus':
									$new_status = isset($_POST['newstatus']) ? sanitize_text_field($_POST['newstatus']) : '';
									$accepted = 0;

									$valid_statuses = zeroBSCRM_getCustomerStatuses(true);

									// legit status?
									if (in_array($new_status, $valid_statuses)){

										// cycle through + mark
										foreach ($legitIDs as $id){

											// Update contact status
											$zbs->DAL->contacts->setContactStatus( $id, $new_status );

											$accepted++;
										}
									} else {
										zeroBSCRM_API_error('Invalid status!');
									}

									$passBack['accepted'] = $accepted;

									#} Return
									header('Content-Type: application/json'); 
									echo json_encode($passBack);
									exit();


									break;

								// add tag(s) to customers
								case 'addtag':

									zeroBSCRM_bulkAction_enact_addTags($legitIDs,ZBS_TYPE_CONTACT,'zerobscrm_customertag');

									break;

								// remove tag(S) from customers
								case 'removetag':

									zeroBSCRM_bulkAction_enact_removeTags($legitIDs,ZBS_TYPE_CONTACT,'zerobscrm_customertag');

									break;

								// merge customers
								case 'merge':

									// merge which into which
									$dominant = false; if (isset($_POST['dominant']) && !empty($_POST['dominant'])) $dominant = (int)sanitize_text_field($_POST['dominant']);
									$slave = false; if (!empty($dominant)){

										// discern slave (should only ever be 2 id's)
										foreach ($legitIDs as $id){
											if ($id != $dominant) $slave = $id;
										}

									}

									if (!empty($dominant) && !empty($slave)){

										$passBack['merged'] = zeroBSCRM_mergeCustomers($dominant,$slave);

									} else {

										$passBack = false;

									}

									#} Return
									header('Content-Type: application/json'); 
									echo json_encode($passBack);
									exit();


									break;

							}


						#} Return - will be an error if here, really!?!? should be passsing headers as such.
						header('Content-Type: application/json'); 
						echo json_encode($passBack);
						exit();

						break;



					case 'company':

						// check id's legit
						$legitIDs = array(); if (is_array($idsToChange) && count($idsToChange) > 0) foreach ($idsToChange as $id){

							$intID = (int)$id;
							if ($intID > 0) $legitIDs[] = $intID;

						}


						if (count($legitIDs) > 0){

							// actions:
							switch ($actionstr){

								// delete company
								case 'delete':

									// delete sub stuff?
									$leaveOrphans = true; 
									
									if ( isset( $_POST['leaveorphans'] )) {
										if($_POST['leaveorphans'] == "0"){
											$leaveOrphans = false;
										}
									}

									// cycle through + delete (should have sanity checked via SWAL)
									$deleted = 0;
									foreach ($legitIDs as $id){

										// delete all orphans
										zeroBS_deleteCompany($id,$leaveOrphans);
										$deleted++;

									}

									$passBack['deleted'] = $deleted;

									#} Return
									header('Content-Type: application/json'); 
									echo json_encode($passBack);
									exit();


									break;

								// add tag(s) to company(s)
								case 'addtag':
									
									zeroBSCRM_bulkAction_enact_addTags($legitIDs,ZBS_TYPE_COMPANY,'zerobscrm_companytag');

									break;

								// remove tag(S) from company(s)
								case 'removetag':

									zeroBSCRM_bulkAction_enact_removeTags($legitIDs,ZBS_TYPE_COMPANY,'zerobscrm_companytag');

									break;

							}

						} else {

							// NO IDS!

						}


						#} Return - will be an error if here, really!?!? should be passsing headers as such.
						header('Content-Type: application/json'); 
						echo json_encode($passBack);
						exit();

						break;




					case 'quote':

						// check id's legit
						$legitIDs = array(); if (is_array($idsToChange) && count($idsToChange) > 0) foreach ($idsToChange as $id){

							$intID = (int)$id;
							if ($intID > 0) $legitIDs[] = $intID;

						}


						if (count($legitIDs) > 0){

							// actions:
							switch ($actionstr){

								// delete quote
								case 'delete':

									// cycle through + delete (should have sanity checked via SWAL)
									$deleted = 0;
									foreach ($legitIDs as $id){

										// delete all orphans
										if ($zbs->isDAL3())
											$zbs->DAL->quotes->deleteQuote(array(
									            'id'            => $id,
									            'saveOrphans'   => true));
										else
											zeroBS_deleteGeneric($id);

										$deleted++;

									}

									$passBack['deleted'] = $deleted;

									#} Return
									header('Content-Type: application/json'); 
									echo json_encode($passBack);
									exit();


									break;

								// mark accepted
								case 'markaccepted':

									// cycle through + mark
									$accepted = 0;
									foreach ($legitIDs as $id){

						        		#} Update quote as accepted (should verify this worked...)
						        		zeroBS_markQuoteAccepted($id,zeroBS_getCurrentUserUsername());

										$accepted++;

									}

									$passBack['accepted'] = $accepted;

									#} Return
									header('Content-Type: application/json'); 
									echo json_encode($passBack);
									exit();


									break;

								// mark unaccepted
								case 'markunaccepted':

									// cycle through + mark
									$unaccepted = 0;
									foreach ($legitIDs as $id){

						        		#} Update quote as unaccepted (should verify this worked...)
						        		zeroBS_markQuoteUnAccepted($id,zeroBS_getCurrentUserUsername());

										$unaccepted++;

									}

									$passBack['unaccepted'] = $unaccepted;

									#} Return
									header('Content-Type: application/json'); 
									echo json_encode($passBack);
									exit();


									break;

								// add tag(s) to quote(s)
								case 'addtag':
									
									zeroBSCRM_bulkAction_enact_addTags($legitIDs,ZBS_TYPE_QUOTE,'zerobscrm_quotetag');

									break;

								// remove tag(S) from quote(s)
								case 'removetag':

									zeroBSCRM_bulkAction_enact_removeTags($legitIDs,ZBS_TYPE_QUOTE,'zerobscrm_quotetag');

									break;

							}

						} else {

							// NO IDS!

						}


						#} Return - will be an error if here, really!?!? should be passsing headers as such.
						header('Content-Type: application/json'); 
						echo json_encode($passBack);
						exit();

						break;


					case 'invoice':

						// check id's legit
						$legitIDs = array(); if (is_array($idsToChange) && count($idsToChange) > 0) foreach ($idsToChange as $id){

							$intID = (int)$id;
							if ($intID > 0) $legitIDs[] = $intID;

						}


						if (count($legitIDs) > 0){

							// actions:
							switch ($actionstr){

								// delete quote
								case 'delete':

									// cycle through + delete (should have sanity checked via SWAL)
									$deleted = 0;
									foreach ($legitIDs as $id){

										// delete all orphans
										if ($zbs->isDAL3())
											$zbs->DAL->invoices->deleteInvoice(array(
									            'id'            => $id,
									            'saveOrphans'   => true));
										else
											zeroBS_deleteGeneric($id);

										$deleted++;

									}

									$passBack['deleted'] = $deleted;

									#} Return
									header('Content-Type: application/json'); 
									echo json_encode($passBack);
									exit();


									break;

								// change status
								case 'changestatus':

									$accepted = 0;

									// legit status?
									$statusStr = sanitize_text_field($_POST['newstatus']);
									if ( in_array( $statusStr, zeroBSCRM_getInvoicesStatuses() ) ){

										// cycle through + mark
										foreach ($legitIDs as $id){
											
							        		#} Update invoice status (should verify this worked...)
							        		zeroBS_updateInvoiceStatus($id,$statusStr);

											$accepted++;

										}

									}

									$passBack['accepted'] = $accepted;

									#} Return
									header('Content-Type: application/json'); 
									echo json_encode($passBack);
									exit();


									break;

								// add tag(s) to invoice(s)
								case 'addtag':
									
									zeroBSCRM_bulkAction_enact_addTags($legitIDs,ZBS_TYPE_INVOICE,'zerobscrm_invoicetag');

									break;

								// remove tag(S) from invoice(s)
								case 'removetag':

									zeroBSCRM_bulkAction_enact_removeTags($legitIDs,ZBS_TYPE_INVOICE,'zerobscrm_invoicetag');

									break;


							}

						} else {

							// NO IDS!

						}


						#} Return - will be an error if here, really!?!? should be passsing headers as such.
						header('Content-Type: application/json'); 
						echo json_encode($passBack);
						exit();

						break;

					case 'transaction':

						// check id's legit
						$legitIDs = array(); if (is_array($idsToChange) && count($idsToChange) > 0) foreach ($idsToChange as $id){

							$intID = (int)$id;
							if ($intID > 0) $legitIDs[] = $intID;

						}


						if (count($legitIDs) > 0){

							// actions:
							switch ($actionstr){

								// delete transaction(s)
								case 'delete':

									// cycle through + delete (should have sanity checked via SWAL)
									$deleted = 0;
									foreach ($legitIDs as $id){

										// delete all orphans
										if ($zbs->isDAL3())
											$zbs->DAL->transactions->deleteTransaction(array(
									            'id'            => $id,
									            'saveOrphans'   => true));
										else
											zeroBS_deleteGeneric($id);

										$deleted++;

									}

									$passBack['deleted'] = $deleted;

									#} Return
									header('Content-Type: application/json'); 
									echo json_encode($passBack);
									exit();


									break;

								// add tag(s) to transaction(s)
								case 'addtag':
									
									zeroBSCRM_bulkAction_enact_addTags($legitIDs,ZBS_TYPE_TRANSACTION,'zerobscrm_transactiontag');

									break;

								// remove tag(S) from transaction(s)
								case 'removetag':

									zeroBSCRM_bulkAction_enact_removeTags($legitIDs,ZBS_TYPE_TRANSACTION,'zerobscrm_transactiontag');

									break;


							}

						} else {

							// NO IDS!

						}


						#} Return - will be an error if here, really!?!? should be passsing headers as such.
						header('Content-Type: application/json'); 
						echo json_encode($passBack);
						exit();

						break;
					
					case 'form':

						// check id's legit
						$legitIDs = array(); if (is_array($idsToChange) && count($idsToChange) > 0) foreach ($idsToChange as $id){

							$intID = (int)$id;
							if ($intID > 0) $legitIDs[] = $intID;

						}


						if (count($legitIDs) > 0){

							// actions:
							switch ($actionstr){

								// delete quote
								case 'delete':

									// cycle through + delete (should have sanity checked via SWAL)
									$deleted = 0;
									foreach ($legitIDs as $id){

										// delete all orphans
										if ($zbs->isDAL3())
											$zbs->DAL->forms->deleteForm(array(
									            'id'            => $id,
									            'saveOrphans'   => true));
										else
											zeroBS_deleteGeneric($id);

										$deleted++;

									}

									$passBack['deleted'] = $deleted;

									#} Return
									header('Content-Type: application/json'); 
									echo json_encode($passBack);
									exit();


									break;


							}

						} else {

							// NO IDS!

						}


						#} Return - will be an error if here, really!?!? should be passsing headers as such.
						header('Content-Type: application/json'); 
						echo json_encode($passBack);
						exit();

						break;


					case 'segment':

						// check id's legit
						$legitIDs = array(); if (is_array($idsToChange) && count($idsToChange) > 0) foreach ($idsToChange as $id){

							$intID = (int)$id;
							if ($intID > 0) $legitIDs[] = $intID;

						}


						if (count($legitIDs) > 0){

							// actions:
							switch ($actionstr){

								// delete segments
								case 'delete':

									// cycle through + delete (should have sanity checked via SWAL)
									$deleted = 0;
									foreach ($legitIDs as $id){

										// delete
										$zbs->DAL->segments->deleteSegment(array('id'=>$id));
										$deleted++;

									}

									$passBack['deleted'] = $deleted;

									#} Return
									header('Content-Type: application/json'); 
									echo json_encode($passBack);
									exit();


									break;

							}

						} else {

							// NO IDS!

						}


						#} Return - will be an error if here, really!?!? should be passsing headers as such.
						header('Content-Type: application/json'); 
						echo json_encode($passBack);
						exit();

						break;
					
					case 'quotetemplate':

						// check id's legit
						$legitIDs = array(); if (is_array($idsToChange) && count($idsToChange) > 0) foreach ($idsToChange as $id){

							$intID = (int)$id;
							if ($intID > 0) $legitIDs[] = $intID;

						}


						if (count($legitIDs) > 0){

							// actions:
							switch ($actionstr){

								// delete segments
								case 'delete':

									// cycle through + delete (should have sanity checked via SWAL)
									$deleted = 0;
									foreach ($legitIDs as $id){

										// delete
										$zbs->DAL->quotetemplates->deleteQuotetemplate(array('id'=>$id));
										$deleted++;

									}

									$passBack['deleted'] = $deleted;

									#} Return
									header('Content-Type: application/json'); 
									echo json_encode($passBack);
									exit();


									break;

							}

						} else {

							// NO IDS!

						}


						#} Return - will be an error if here, really!?!? should be passsing headers as such.
						header('Content-Type: application/json'); 
						echo json_encode($passBack);
						exit();

						break;
					
					case 'event':

						// check id's legit
						$legitIDs = array(); if (is_array($idsToChange) && count($idsToChange) > 0) foreach ($idsToChange as $id){

							$intID = (int)$id;
							if ($intID > 0) $legitIDs[] = $intID;

						}


						if (count($legitIDs) > 0){

							// actions:
							switch ($actionstr){

								// delete quote
								case 'delete':

									// cycle through + delete (should have sanity checked via SWAL)
									$deleted = 0;
									foreach ($legitIDs as $id){

										// delete all orphans
										if ($zbs->isDAL3())
											$zbs->DAL->events->deleteEvent(array(
									            'id'            => $id,
									            'saveOrphans'   => true));
										else
											zeroBS_deleteGeneric($id);

										$deleted++;

									}

									$passBack['deleted'] = $deleted;

									#} Return
									header('Content-Type: application/json'); 
									echo json_encode($passBack);
									exit();

									break;

									// add tag(s) to transaction(s)
									case 'addtag':
										
										zeroBSCRM_bulkAction_enact_addTags($legitIDs,ZBS_TYPE_EVENT,'zerobscrm_transactiontag');

										break;

									// remove tag(S) from transaction(s)
									case 'removetag':

										zeroBSCRM_bulkAction_enact_removeTags($legitIDs,ZBS_TYPE_EVENT,'zerobscrm_transactiontag');

										break;

									// mark completed
									case 'markcomplete':

										// cycle through + mark
										$completed = 0;
										foreach ($legitIDs as $id){

							        		// update event as completed
							        		$zbs->DAL->events->setEventCompleteness( $id, 1 );

											$completed++;

										}

										$passBack['completed'] = $completed;

										#} Return
										header('Content-Type: application/json'); 
										echo json_encode($passBack);
										exit();


										break;

									// mark completed
									case 'markincomplete':

										// cycle through + mark
										$incompleted = 0;
										foreach ($legitIDs as $id){

							        		// update event as completed
							        		$zbs->DAL->events->setEventCompleteness( $id, -1 );

											$incompleted++;

										}

										$passBack['incompleted'] = $incompleted;

										#} Return
										header('Content-Type: application/json'); 
										echo json_encode($passBack);
										exit();


										break;


							}

						} else {

							// NO IDS!

						}


						#} Return - will be an error if here, really!?!? should be passsing headers as such.
						header('Content-Type: application/json'); 
						echo json_encode($passBack);
						exit();

						break;

					default: 

						// err really :o
						header('Content-Type: application/json'); 
						exit('[]');

						break;

				}

			} else {

				// NO IDS!

			}


		exit();

	}


	/**
	 * Adds tags to any object (for bulk action AJAX requests called in zeroBSCRM_AJAX_enactListViewBulkAction())
	 *
	 * @param array objIDs      	Array of object id (int)s
	 * @param int objTypeInt  		ZBS_TYPE (if DAL3) or -1 (if <DAL3)
	 * @param string objTaxonomy   	wp taxonomy (if <DAL3) or '' (if DAL3)
	 *
	 * @return json success/error
	 */
	function zeroBSCRM_bulkAction_enact_addTags($objIDs=array(),$objTypeInt=-1,$objTaxonomy=''){
			
			global $zbs;
			
			// return
			$passBack = array();

			// retrieve tag (array of id's)
			$tagArr = zeroBSCRM_dataIO_postedArrayOfInts($_POST['tags']);
			$tagIDs = array();
			if (is_array($tagArr) && count($tagArr) > 0) foreach ($tagArr as $t){

				$tInt = (int)$t;
				if ($tInt > 0) $tagIDs[] = $tInt;

			}

			if (count($tagIDs) > 0){

				// tags to add 

					// cycle through + add tag
					$tagged = 0;
					foreach ($objIDs as $id){

						// pass as array of term ID's :)
						if ($zbs->isDAL3()){
							
							// DAL3
							$zbs->DAL->addUpdateObjectTags(array(
									'objid' 		=> $id,
									'objtype' 		=> $objTypeInt,
									'tagIDs'		=> $tagIDs,
									'mode'			=> 'append'
							));
							
						} else {

							// DAL2<=
							zeroBSCRM_DAL2_set_post_terms($id,$tagIDs,$objTaxonomy,true);

						}

						// no checks.?
						$tagged++;

					}

					$passBack['tagged'] = $tagged;

					#} Return
					zeroBSCRM_sendJSONSuccess($passBack);
					exit();

			} else {

				// no tags

			}

		// err
		zeroBSCRM_sendJSONError(-1);
		exit();

	}


	/**
	 * Remove tags from any object (for bulk action AJAX requests called in zeroBSCRM_AJAX_enactListViewBulkAction())
	 *
	 * @param array objIDs      	Array of object id (int)s
	 * @param int objTypeInt  		ZBS_TYPE (if DAL3) or -1 (if <DAL3)
	 * @param string objTaxonomy   	wp taxonomy (if <DAL3) or '' (if DAL3)
	 *
	 * @return json success/error
	 */
	function zeroBSCRM_bulkAction_enact_removeTags($objIDs=array(),$objTypeInt=-1,$objTaxonomy=''){
			
			global $zbs;
			
			// return
			$passBack = array();

			// retrieve tag (array of id's)
			$tagArr = zeroBSCRM_dataIO_postedArrayOfInts($_POST['tags']);
			$tagIDs = array();
			if (is_array($tagArr) && count($tagArr) > 0) foreach ($tagArr as $t){

				$tInt = (int)$t;
				if ($tInt > 0) $tagIDs[] = $tInt;

			}

			if (count($tagIDs) > 0){

				// tags to add 

					// cycle through + remove tags
					$untagged = 0;
					foreach ($objIDs as $id){

						// pass as array of term ID's :)
						// https://codex.wordpress.org/Function_Reference/wp_remove_object_terms
						if ($zbs->isDAL3())
							$zbs->DAL->addUpdateObjectTags(array(
									'objid' 		=> $id,
									'objtype' 		=> $objTypeInt,
									'tagIDs'		=> $tagIDs,
									'mode' 			=> 'remove'
							));
						else
							zeroBSCRM_DAL2_remove_object_terms($id,$tagIDs,$objTaxonomy);

						// no checks.?
						$untagged++;

					}

					$passBack['untagged'] = $untagged;

					#} Return
					zeroBSCRM_sendJSONSuccess($passBack);
					exit();
        
			} else {

				// no tags

			}

		// err
		zeroBSCRM_sendJSONError(-1);
		exit();


	}


/* ======================================================
	/ Admin AJAX: List View (API STYLE)
====================================================== */

/* ======================================================
	Admin AJAX: Segments
====================================================== */

#} Preview a segment
add_action('wp_ajax_zbs_segment_previewsegment', 'zeroBSCRM_AJAX_previewSegment');
function zeroBSCRM_AJAX_previewSegment(){
	
	#} Check nonce
	check_ajax_referer( 'zbs-ajax-nonce', 'sec' );
	
	// either way
	header('Content-Type: application/json');

  	if ( current_user_can( 'admin_zerobs_customers' ) ) {  

  		global $zbs;

  		// sanitize?
		$segmentID = -1; if (isset($_POST['sID'])) $segmentID = (int)sanitize_text_field( $_POST['sID'] );
		$segmentTitle = __('Untitled Segment',"zero-bs-crm"); if (isset($_POST['sTitle'])) $segmentTitle = sanitize_text_field( $_POST['sTitle'] );
		$segmentMatchType = 'all'; if (isset($_POST['sMatchType'])) $segmentMatchType = sanitize_text_field( $_POST['sMatchType'] );
		$segmentConditions = array(); if (isset($_POST['sConditions'])) $segmentConditions = zeroBSCRM_segments_filterConditions($_POST['sConditions'],false);

		// optional 2.90+ can just pass id and this'll fill the conditions from saved
		if ($segmentID > 0 && count($segmentConditions) == 0){

			$potentialSegment = $zbs->DAL->segments->getSegment($segmentID,true);
			if (is_array($potentialSegment) && isset($potentialSegment['id'])){
				$segment = $potentialSegment;
				$segmentConditions = $segment['conditions'];
				$segmentMatchType = $segment['matchtype'];
				$segmentTitle = $segment['name'];
			}
		}

		try {

			// attempt to build a top 5 customer list + total count for segment
			$ret = $zbs->DAL->segments->previewSegment($segmentConditions,$segmentMatchType);

		} catch ( Segment_Condition_Exception $exception ){

            // We're missing the condition class for one or more of this segment's conditions.
            $zbs->DAL->segments->segment_error_condition_missing( $segmentID, $exception );

            // return error str
            $error_string =  $exception->get_error_code();

            // return fail
            zeroBSCRM_sendJSONError( array(
            	'count' => 0,
            	'error' => $error_string
            ) );
            exit();
           
        }

		if (is_array($ret) && isset($ret['count'])){

			// return id / fail
			echo json_encode($ret);
			exit();

		}



	}

	// empty handed
	echo json_encode(array('count'=>0));
	exit();
}
#} Save a segment down (update or add)
add_action('wp_ajax_zbs_segment_savesegment', 'zeroBSCRM_AJAX_saveSegment');
function zeroBSCRM_AJAX_saveSegment(){
	
	#} Check nonce
	check_ajax_referer( 'zbs-ajax-nonce', 'sec' );
	
	// either way
	header('Content-Type: application/json');

  	if ( current_user_can( 'admin_zerobs_customers' ) ) {  

  		global $zbs;

  		// sanitize?
		$segmentID = -1; if (isset($_POST['sID'])) $segmentID = (int)sanitize_text_field( $_POST['sID'] );
		$segmentTitle = __('Untitled Segment',"zero-bs-crm"); if (isset($_POST['sTitle'])) $segmentTitle = sanitize_text_field(zeroBSCRM_textProcess( $_POST['sTitle'] ));
		$segmentMatchType = 'all'; if (isset($_POST['sMatchType'])) $segmentMatchType = sanitize_text_field( $_POST['sMatchType'] );
		$segmentConditions = array(); if (isset($_POST['sConditions'])) $segmentConditions = zeroBSCRM_segments_filterConditions($_POST['sConditions']);

		// nice and simple, push to DAL (empty template ID will get created, else updated)
		$segmentID = $zbs->DAL->segments->addUpdateSegment($segmentID,-1,$segmentTitle,$segmentConditions,$segmentMatchType,true);
		
		if (!empty($segmentID)){

			// return id / fail
			echo json_encode(array('id'=>$segmentID));
			exit();

		}



	}

	// empty handed
	exit();
}


/* ======================================================
	/ Admin AJAX: Segments
====================================================== */
	

/* ======================================================
	Admin AJAX: Top Menu
====================================================== */
#} This is our toggle full screen mode for users to be able to control whether the CRM is fullscreen or not.
add_action('wp_ajax_zbs_admin_top_menu_save', 'zeroBSCRM_admin_top_menu_save');
function zeroBSCRM_admin_top_menu_save(){
	#} Check nonce
	check_ajax_referer( 'zbscrmjs-ajax-nonce-topmenu', 'sec' );
	if(zeroBSCRM_permsIsZBSUserOrAdmin()){
		#} current user
		$cid = get_current_user_id();
		$hide = (int)sanitize_text_field($_POST['hide']);
		update_user_meta($cid,'zbs-hide-wp-menus', $hide);
	}
	wp_die();
}

/* ======================================================
	/ Admin AJAX: Top Menu
====================================================== */

	

/* ======================================================
	Admin AJAX: Tag Management
====================================================== */

add_action('wp_ajax_zbs_add_tag', 'zeroBSCRM_AJAX_addTag');
function zeroBSCRM_AJAX_addTag(){

	#} Check nonce
	check_ajax_referer( 'zbscrmjs-ajax-nonce', 'sec' );  //nonce to bounce out if not from right page

	#} Permission
	if(zeroBSCRM_permsIsZBSUserOrAdmin()){

		#} Get
		$objType = -1; if (isset($_POST['objtype']) && !empty($_POST['objtype'])) $objType = sanitize_text_field( $_POST['objtype'] );
		$objTag = ''; if (isset($_POST['tag']) && !empty($_POST['tag'])) $objTag = sanitize_text_field( $_POST['tag'] );

		if (empty($objType)) {
			zeroBSCRM_sendJSONError(array('notag'=>1));
			exit();
		}

		global $zbs;

		// this converts 'contact' => 1 and weeds out any wrongly-typed obj types
		$objTypeID = $zbs->DAL->objTypeID($objType);

		if ($objTypeID !== -1 && $objTypeID > 0){


			// addtag to (OBJ) (WILL BE DAL2)
			$tagID = $zbs->DAL->addUpdateTag(array(

				'id' 			=> -1,

				// fields (directly)
				'data'			=> array(

					'objtype' 		=> $objTypeID,
					'name' 			=> $objTag,
					//'slug' 			=> '',
					//'owner'			=> -1

				)
			));

			if (!empty($tagID)) {

				// retrieve just-made slug
                $slug = $zbs->DAL->getTag($tagID,array('objtype' => $objTypeID,'onlySlug' => true));

				zeroBSCRM_sendJSONSuccess(array('id'=>$tagID,'slug'=>$slug));
			}

		} // if objtype match

	}

	zeroBSCRM_sendJSONError(array('dataerr'=>1));
	exit();

}



add_action('wp_ajax_zbs_delete_tag', 'zeroBSCRM_AJAX_deleteTag');
function zeroBSCRM_AJAX_deleteTag(){

	#} Check nonce
	check_ajax_referer( 'zbscrmjs-ajax-nonce', 'sec' );  //nonce to bounce out if not from right page

	#} Permission
	if(zeroBSCRM_permsIsZBSUserOrAdmin()){

		#} Get
		//$objType = -1; if (isset($_POST['objtype']) && !empty($_POST['objtype'])) $objType = (int)sanitize_text_field( $_POST['objtype'] );
		$objTagID = -1; if (isset($_POST['tagid']) && !empty($_POST['tagid'])) $objTagID = (int)sanitize_text_field( $_POST['tagid'] );

		if (empty($objTagID)) {
			zeroBSCRM_sendJSONError(array('notag'=>1));
			exit();
		}

		global $zbs;

		if ($objTagID !== -1 && $objTagID > 0){


			// addtag to (OBJ) (WILL BE DAL2)
			$res = $zbs->DAL->deleteTag(array(

				'id' 			=> $objTagID,
				'deleteLinks' 	=> true

			));
			
			zeroBSCRM_sendJSONSuccess(array('res'=>$res));

		} // if objtype match

	}

	zeroBSCRM_sendJSONError(array('dataerr'=>1));
	exit();

}


#} Preview a tagged group
add_action('wp_ajax_zbs_tags_previewtagged', 'zeroBSCRM_AJAX_previewTagged');
function zeroBSCRM_AJAX_previewTagged(){
	
	#} Check nonce
	check_ajax_referer( 'zbs-ajax-nonce', 'sec' );
	
	// either way
	header('Content-Type: application/json');

  	if ( current_user_can( 'admin_zerobs_customers' ) ) {  

  		global $zbs;

  		// sanitize?
		$tagID = -1; if (isset($_POST['tagID'])) $tagID = (int)sanitize_text_field( $_POST['tagID'] );
		$tagMatchType = 'hastag'; if (isset($_POST['tagMatchType'])) $tagMatchType = sanitize_text_field( $_POST['tagMatchType'] );

		// build quick search 
		$contactArgs = array(
				'withCustomFields' => false, // not req
				'page' => 0,
				'perPage' => 5,
				'ignoreowner' => true
				);

		if ($tagMatchType == 'hastag') $contactArgs['isTagged'] = $tagID;
		if ($tagMatchType == 'nohastag') $contactArgs['isNotTagged'] = $tagID;

		// this is to get just the total count
		$countContactGetArgs = $contactArgs;
        $countContactGetArgs['perPage'] = 100000;
        $countContactGetArgs['count'] = true;

		// attempt to build a top 5 customer list + total count for this
		$ret = array(
                        // DEBUG 
                        //'args' => $contactArgs, // TEMP - remove this
                        'count'=>$zbs->DAL->contacts->getContacts($countContactGetArgs),
                        'list'=>$zbs->DAL->contacts->getContacts($contactArgs)
                    );

		if (is_array($ret) && isset($ret['count'])){

			// return id / fail
			echo json_encode($ret);
			exit();

		}



	}

	// empty handed
	echo json_encode(array('count'=>0));
	exit();
}

/* ======================================================
	/ Admin AJAX: Tag Management
====================================================== */




/* ======================================================
	Admin AJAX: Screen options DAL2
====================================================== */

	#} Feedback
	add_action( 'wp_ajax_save_zbs_screen_options', 'zeroBSCRM_AJAX_saveScreenOptions' );
	function zeroBSCRM_AJAX_saveScreenOptions(){

		#} Check nonce
		check_ajax_referer( 'zbscrmjs-ajax-nonce', 'sec' );  //nonce to bounce out if not from right page

		#} Check is logged in legit user
		if (!zeroBS_canUpdateScreenOptions()) zeroBSCRM_sendJSONError(array('err'=>'rights'));
		
		global $zbs;

		#} This is the filtering model for all screenoptions :)
		$screenOptionsFilters = array(

			// order of metaboxes for 'normal' area of page
		    'mb_normal'   => array(
                            'filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_FORCE_ARRAY,
                           ),
			// order of metaboxes for 'side' area of page
			// e.g. 'key','key2'
		    'mb_side'   => array(
                            'filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_FORCE_ARRAY,
                           ), 
			// list of hidden metaboxes
			// e.g. 'key','key2'
		    'mb_hidden'   => array(
                            'filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_FORCE_ARRAY,
                           ), 
			// list of minimised metaboxes
			// e.g. 'key','key2'
		    'mb_mini'   => array(
                            'filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_FORCE_ARRAY,
                           ),

		    // for now, this is a catchall :)
		    'pageoptions' => array(
                            'filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_FORCE_ARRAY,
                           ),

		    // selected table columns (currently just co view)
		    'tablecolumns' => array(
                            'filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_FORCE_ARRAY,
                           ),

		    // perpage (only used for list pages, just an int)
		    'perpage' => FILTER_VALIDATE_INT

		);


		$screenOpts = array(); $pageKey = '';
		if (isset($_POST['screenopts'])) {

			// get
			$screenOpts = $_POST['screenopts'];

			// sanitize - http://php.net/manual/en/function.filter-var-array.php
			$screenOpts = filter_var_array($screenOpts, $screenOptionsFilters);

		}
		if (isset($_POST['pagekey'])) $pageKey = sanitize_text_field( $_POST['pagekey'] );

		if (!empty($pageKey)){

			#} Brutally update
			$zbs->DAL->updateUserSetting($zbs->user(),'screenopts_'.$pageKey,$screenOpts);

			zeroBSCRM_sendJSONSuccess(array('fini'=>1));
			exit();

		} 

		zeroBSCRM_sendJSONError(array('err'=>'pagekey'));
		exit();

	}


/* ======================================================
	/ Admin AJAX: Screen options DAL2
====================================================== */




/* ======================================================
	Admin AJAX: Inline Editor
====================================================== */


   	#} Save any inline-edits
	add_action('wp_ajax_zbs_list_save_inline_edit', 'zeroBSCRM_AJAX_listViewInlineEdit_save');
	function zeroBSCRM_AJAX_listViewInlineEdit_save(){
		
		#} Nonce
		check_ajax_referer( 'zbscrmjs-ajax-nonce', 'sec' );  //nonce to bounce out if not from right page

		global $zbs;

		#} DAL2 check
		if (!$zbs->isDAL2()) zeroBSCRM_sendJSONError(array('no-action-or-rights'=>1));

		#} Retrieve deets
		$listtype = sanitize_text_field($_POST['listtype']);
		$id = (int)sanitize_text_field($_POST['id']);
		$field = sanitize_text_field($_POST['field']);
		$v = sanitize_text_field($_POST['v']);

		switch ($listtype){


			case 'customer':

				#} Perms	
				if (!zeroBSCRM_permsCustomers()) zeroBSCRM_sendJSONError(array('no-action-or-rights'=>1));

				#} check deets
				if ($id > 0 && !empty($field)){

					$success = false;
					switch ($field){

						case 'status':
							$success = $zbs->DAL->contacts->setContactStatus( $id, $v );
							break;
						case 'assigned':
							$success = $zbs->DAL->contacts->setContactOwner( $id, $v );
							break;

					}

					if ( $success ) {
						zeroBSCRM_sendJSONSuccess(array('success'=>1));
					}

				}

				break;





		}

		zeroBSCRM_sendJSONError(array('no-action-or-rights'=>1));
	}



/* ======================================================
	/ Admin AJAX: Inline Editor
====================================================== */


/* ======================================================
	ZBS Invoicing
   ====================================================== */

#} AJAX Send Inv
add_action( 'wp_ajax_zbs_invoice_send_invoice', 'zbs_invoice_send_invoice' );
function zbs_invoice_send_invoice(){

    check_ajax_referer( 'inv-ajax-nonce', 'security' );
	
	$zbs_invID = -1; $em = ''; $r = array();
	if (isset($_POST['id']) && !empty($_POST['id'])) $zbs_invID = (int)sanitize_text_field($_POST['id']);  //accepts the post ID
	if (isset($_POST['em']) && !empty($_POST['em'])) $em = sanitize_text_field($_POST['em']);

	// v3.0 changed var and added a few more:
	$attachAssignedDocs = false; $attachAsPDF = false;
	if (isset($_POST['email']) && !empty($_POST['email'])) $em = sanitize_text_field($_POST['email']);
	if (isset($_POST['attachassoc']) && $_POST['attachassoc'] == 1) $attachAssignedDocs = true;
	if (isset($_POST['attachpdf']) && $_POST['attachpdf'] == 1) $attachAsPDF = true;

    #debug $r['em'] = $em;
    #debug $r['id'] = $zbs_invID;

	//validate the email
	if (!zeroBSCRM_validateEmail($em)){

	    zeroBSCRM_sendJSONError(array('message'=>__('Not valid','zero-bs-crm')));
	    exit();

	}

	#} Check id + perms + em
	if ($zbs_invID <= 0 || empty($em) || !zeroBSCRM_permsInvoices()){
		
	    zeroBSCRM_sendJSONError(array('message'=>__('Not valid','zero-bs-crm')));
	    exit();

	}

	global $zbs;

	if ($zbs->isDAL3())
		$sent = zeroBSCRM_AJAX_sendInvoiceEmail_v3($em,$zbs_invID,$attachAssignedDocs,$attachAsPDF);
	else
		$sent = zeroBSCRM_AJAX_sendInvoiceEmail_prev3($em,$zbs_invID);
    
    if ($sent){

	    // send result
		zeroBSCRM_sendJSONSuccess(array('message'=>'sent'));

	} else {

		// send err
	    zeroBSCRM_sendJSONError(array('message'=>__('not sent','zero-bs-crm')));

	}

	// whatever:
	exit();

}

// v3.0+ send email for an invoice
function zeroBSCRM_AJAX_sendInvoiceEmail_v3($email='',$invoiceID=-1,$attachAssignedDocs=false,$attachAsPDF=false){

	global $zbs;

	$biz_name = zeroBSCRM_getSetting('businessname');
	$biz_extra = zeroBSCRM_getSetting('businessextra');

	// retrieve inv
	$invoice = $zbs->DAL->invoices->getInvoice($invoiceID,array(
            // with what?
            'withLineItems'     => true,
            'withCustomFields'  => true,
            'withTransactions'  => true,
            'withAssigned'      => true,
            'withTags'          => true,
            'withOwner'         => true,
            'withFiles'			=> true

    ));

	// retrieve assoc records
 	$contactID = -1;  if (is_array($invoice) && isset($invoice['contact']) && is_array($invoice['contact']) && count($invoice['contact']) > 0) $contactID = $invoice['contact'][0]['id'];
 	$companyID = -1;  if (is_array($invoice) && isset($invoice['company']) && is_array($invoice['company']) && count($invoice['company']) > 0) $companyID = $invoice['company'][0]['id'];                
    // now $contactID $cID =  get_post_meta($zbs_invID, 'zbs_customer_invoice_customer',true);

    #} check if the email is active..
    $active = zeroBSCRM_get_email_status(ZBSEMAIL_EMAILINVOICE);
    if (zeroBSCRM_validateEmail($email) && $invoiceID > 0 && $active){

        // send welcome email (tracking will now be dealt with by zeroBSCRM_mailDelivery_sendMessage)

        // ==========================================================================================
        // =================================== MAIL SENDING =========================================

		// Attachments?
        $attachments = array();
        if ($attachAssignedDocs){
        	if (isset($invoice['files']) && is_array($invoice['files']) && count($invoice['files']) > 0){

        		// cycle through files + add as attachments
        		// we pass as 2part array so they don't have their funky md5 prefixes..
        		foreach($invoice['files'] as $invFile){

                    $filename = basename($invFile['file']);
                    // if in privatised system, ignore first hash in name
                    if (isset($invFile['priv'])){

                        $filename = substr($filename,strpos($filename, '-')+1);
                    }

                    $attachments[] = array($invFile['file'],'x'.$filename);

                }
        	}
        }

        // Attach as PDF?
        if ($attachAsPDF){

        	// make pdf.

            // generate the PDF
            $pdfFileLocation = zeroBSCRM_generateInvoicePDFFile($invoiceID);

            if ($pdfFileLocation !== false){

                // attach inv 
                $attachments[] = array($pdfFileLocation,'invoice.pdf');

            }

            // NOTE: for security / hygiene, we delete this PDF after email is sent

        }
        
        // generate html
        $emailHTML = zeroBSCRM_invoice_generateNotificationHTML($invoiceID,true);

          // build send array
          $mailArray = array(
            'toEmail' => $email,
            'toName' => '',
            'subject' => zeroBSCRM_mailTemplate_getSubject(ZBSEMAIL_EMAILINVOICE),
            'headers' => zeroBSCRM_mailTemplate_getHeaders(ZBSEMAIL_EMAILINVOICE),
            'body' => $emailHTML,
            'textbody' => '',
            'attachments' => $attachments,
            'options' => array(
              'html' => 1
            )
          );
          // track if contactID
          if ($contactID > 0){

          	// senderWPID = -14 = new inv email to contact
            $mailArray['tracking'] = array( 
              // tracking :D (auto-inserted pixel + saved in history db)
              'emailTypeID' => ZBSEMAIL_EMAILINVOICE,
              'targetObjID' => $contactID,
              'senderWPID' => -14,
              'associatedObjID' => $invoiceID
            );

          }
          // track if companyID
          if ($companyID > 0){

          	// senderWPID = -16 = new inv email to contact
            $mailArray['tracking'] = array( 
              // tracking :D (auto-inserted pixel + saved in history db)
              'emailTypeID' => ZBSEMAIL_EMAILINVOICE,
              'targetObjID' => $companyID,
              'senderWPID' => -16,
              'associatedObjID' => $invoiceID
            );

          }

          // DEBUG echo 'Sending:<pre>'; print_r($mailArray); echo '</pre>Result:';

          // Sends email, including tracking, via setting stored route out, (or default if none)
          // and logs trcking :)

            // discern del method
            $mailDeliveryMethod = zeroBSCRM_mailTemplate_getMailDelMethod(ZBSEMAIL_EMAILINVOICE);
            if (!isset($mailDeliveryMethod) || empty($mailDeliveryMethod)) $mailDeliveryMethod = -1;

            // send
            $sent = zeroBSCRM_mailDelivery_sendMessage($mailDeliveryMethod,$mailArray);

            // delete any gen'd pdf's
            if ($attachAsPDF && $pdfFileLocation !== false){

                // delete the PDF file once it's been read (i.e. emailed)
                unlink($pdfFileLocation); 

            }

        // =================================== / MAIL SENDING =======================================
        // ==========================================================================================

		// once the invoice is sent it will mark it as unpaid (automatically)
		// (if is draft)
		if (isset($invoice['status']) && $invoice['status'] == __('Draft','zero-bs-crm')){

			$zbs->DAL->invoices->setInvoiceStatus($invoiceID,__('Unpaid','zero-bs-crm'));

		}

		return true;

    } else {

    	// err 
    	return false;
    
    }

}

// <v3.0 send email for an invoice
function zeroBSCRM_AJAX_sendInvoiceEmail_prev3($em='',$zbs_invID=-1){

	// removed from it's header func for v3.0 split

    //need to sort this later when new invoice DB is out
    $email = $em;
    $cID =  get_post_meta($zbs_invID, 'zbs_customer_invoice_customer',true);
	$biz_name = zeroBSCRM_getSetting('businessname');
	$biz_extra = zeroBSCRM_getSetting('businessextra');

    /* WH REMOVED for 2.80+ - to be built into normal mail delivery method, for now, kill these see if anyone moans
    $attachments = array();
	//invoice attachments (actually called invoices but these now can be things like toggl timesheet reports(?) or T&Cs....
	$zbsCustomerInvoices = get_post_meta($zbs_invID, 'zbs_customer_invoices', true);
    if($zbsCustomerInvoices){
        foreach($zbsCustomerInvoices as $invoice){
        	$attachments[] = $invoice['file'];
        }
    }

    */

    /* Old method:

    $subject = zeroBSCRM_mailTemplate_getSubject(3);
    $headers = array('Content-Type: text/html; charset=UTF-8');
    

    $active = zeroBSCRM_get_email_status(3);
    if($active){
        #} open tracking here uses the customer ID and the item ID..  (item being invoice email)
    	$body = zeroBSCRM_mailTracking_addPixel($body, -10, $cID, $email, $zbs_invID);
        #} get the new headers from the template
        $headers = zeroBSCRM_mailTemplate_getHeaders(3);
	   wp_mail( $email, $subject, $body, $headers, $attachments );
        zeroBSCRM_mailTracking_logEmail(3, $cID, -10, $email, $zbs_invID);
    } */


    #} check if the email is active..
    $active = zeroBSCRM_get_email_status(ZBSEMAIL_EMAILINVOICE);
    if ($active){

        // send welcome email (tracking will now be dealt with by zeroBSCRM_mailDelivery_sendMessage)

        // ==========================================================================================
        // =================================== MAIL SENDING =========================================

		// Attachments?
        $attachments = array();
        $zbsSendAttachments = get_post_meta($zbs_invID, 'zbs_inv_sendattachments', true);
        if ($zbsSendAttachments == "1"){
        	$invFiles = get_post_meta($zbs_invID, 'zbs_customer_invoices', true);
        	if (is_array($invFiles) && count($invFiles) > 0){

        		// cycle through files + add as attachments
        		// we pass as 2part array so they don't have their funky md5 prefixes..
        		foreach($invFiles as $invFile){

                    $filename = basename($invFile['file']);
                    // if in privatised system, ignore first hash in name
                    if (isset($invFile['priv'])){

                        $filename = substr($filename,strpos($filename, '-')+1);
                    }

                    $attachments[] = array($invFile['file'],$filename);

                }
        	}
        }


        // generate html
        $emailHTML = zeroBSCRM_invoice_generateNotificationHTML($zbs_invID,true);

          // build send array
          $mailArray = array(
            'toEmail' => $email,
            'toName' => '',
            'subject' => zeroBSCRM_mailTemplate_getSubject(ZBSEMAIL_EMAILINVOICE),
            'headers' => zeroBSCRM_mailTemplate_getHeaders(ZBSEMAIL_EMAILINVOICE),
            'body' => $emailHTML,
            'textbody' => '',
            'attachments' => $attachments,
            'options' => array(
              'html' => 1
            ),
            'tracking' => array( 
              // tracking :D (auto-inserted pixel + saved in history db)
              'emailTypeID' => ZBSEMAIL_EMAILINVOICE,
              'targetObjID' => $cID,
              'senderWPID' => -14, // wh added -14
              'associatedObjID' => $zbs_invID
            )
          );

          // DEBUG echo 'Sending:<pre>'; print_r($mailArray); echo '</pre>Result:';

          // Sends email, including tracking, via setting stored route out, (or default if none)
          // and logs trcking :)

            // discern del method
            $mailDeliveryMethod = zeroBSCRM_mailTemplate_getMailDelMethod(ZBSEMAIL_EMAILINVOICE);
            if (!isset($mailDeliveryMethod) || empty($mailDeliveryMethod)) $mailDeliveryMethod = -1;

            // send
            $sent = zeroBSCRM_mailDelivery_sendMessage($mailDeliveryMethod,$mailArray);


        // =================================== / MAIL SENDING =======================================
        // ==========================================================================================

		//once the invoice is sent it will mark it as unpaid (automatically)
		$zbs_inv_meta = get_post_meta($zbs_invID,'zbs_customer_invoice_meta', true); 

		$zbs_inv_meta['status'] = 'Unpaid';
		update_post_meta($zbs_invID,'zbs_customer_invoice_meta', $zbs_inv_meta);

		return true;
    }

    return false;

}


#} AJAX Send Inv
add_action( 'wp_ajax_zbs_invoice_send_statement', 'zeroBSCRM_AJAX_sendStatement' );
function zeroBSCRM_AJAX_sendStatement(){

	#} Check nonce
	check_ajax_referer( 'zbscrmjs-glob-ajax-nonce', 'sec' );  //nonce to bounce out if not from right page

	$cID = -1; $em = ''; $r = array();
	if (isset($_POST['cid']) && !empty($_POST['cid'])) $cID = (int)sanitize_text_field($_POST['cid']);  //accepts the post ID
	if (isset($_POST['em']) && !empty($_POST['em'])) $em = sanitize_text_field($_POST['em']);


	//validate the email
	if (!zeroBSCRM_validateEmail($em)){

		$r['error'] = __('Not a valid email','zero-bs-crm');
		zeroBSCRM_sendJSONError($r);
		exit();

	} else $email = $em;

	#} Check id + perms + em
	if ($cID <= 0 || empty($email) || !zeroBSCRM_permsInvoices()){
		
		$r['error'] = '';
		zeroBSCRM_sendJSONError($r);
		exit();

	}

	// ==== BUILD STATEMENT PDF

		// generates pdf file
		$statementPDFfilepath = zeroBSCRM_invoicing_generateStatementPDF($cID,false);

		// check worked
		if (!file_exists($statementPDFfilepath)){

			$r['error'] = '';
			zeroBSCRM_sendJSONError($r);
			exit();

		}

	// ==== SEND VIA EMAIL ATTACHMENT 
    // ==========================================================================================
    // =================================== MAIL SENDING =========================================

	// Attachment
    $attachments = array(
    	array($statementPDFfilepath,__('statement','zero-bs-crm').'.pdf')
    );

    // generate html
    $emailHTML = zeroBSCRM_statement_generateNotificationHTML($cID,true);

      // build send array
      $mailArray = array(
        'toEmail' => $email,
        'toName' => '',
        'subject' => zeroBSCRM_mailTemplate_getSubject(ZBSEMAIL_STATEMENT),
        'headers' => zeroBSCRM_mailTemplate_getHeaders(ZBSEMAIL_STATEMENT),
        'body' => $emailHTML,
        'textbody' => '',
        'attachments' => $attachments,
        'options' => array(
          'html' => 1
        ),
        'tracking' => array( 
          // tracking :D (auto-inserted pixel + saved in history db)
          'emailTypeID' => ZBSEMAIL_STATEMENT,
          'targetObjID' => $cID,
          'senderWPID' => -15, // wh added -15 you have a statement sent to customer,
          'associatedObjID' => -1
        )
      );

      // DEBUG echo 'Sending:<pre>'; print_r($mailArray); echo '</pre>Result:';

      // Sends email, including tracking, via setting stored route out, (or default if none)
      // and logs trcking :)

        // discern del method
        $mailDeliveryMethod = zeroBSCRM_mailTemplate_getMailDelMethod(ZBSEMAIL_STATEMENT);
        if (!isset($mailDeliveryMethod) || empty($mailDeliveryMethod)) $mailDeliveryMethod = -1;

        // send
        $sent = zeroBSCRM_mailDelivery_sendMessage($mailDeliveryMethod,$mailArray);


    // =================================== / MAIL SENDING =======================================
    // ==========================================================================================

    // DELETE statement 
        //delete the PDF file once it's been read (i.e. sent)
        unlink($statementPDFfilepath);

	$r['success'] = __('Sent','zero-bs-crm');
	zeroBSCRM_sendJSONSuccess($r);
	exit();
    
}

/* replaced with zeroBSCRM_retrieve
function zbs_get_content($URL){
	  //need to wrap this in if function exists...
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $URL);
      $data = curl_exec($ch);
      curl_close($ch);
      return $data;
} */

add_action( 'wp_ajax_zbs_invoice_mark_paid', 'zbs_invoice_mark_paid' );
function zbs_invoice_mark_paid(){

	#} get if poss
	$zbs_invID = -1;
	if (isset($_POST['id']) && !empty($_POST['id'])) $zbs_invID = (int)sanitize_text_field($_POST['id']);  //accepts the post ID

	#} Check id + perms + em
	if ($zbs_invID < 1 || !zeroBSCRM_permsInvoices()){

		die();

	} else {

		#} Continue

		//once the invoice is sent it will mark it as unpaid (automatically)
		$zbs_inv_meta = get_post_meta($zbs_invID,'zbs_customer_invoice_meta', true); 
		$zbs_inv_meta['status'] = 'Paid';
		update_post_meta($zbs_invID,'zbs_customer_invoice_meta', $zbs_inv_meta);
		
		//all OK .... 
		$r['message'] = 'All done OK';
		echo json_encode($r);

	}
	
	die(); //exiting ... yarp..
}

#} and send test so they can test before actually sending the invoice
add_action( 'wp_ajax_zbs_invoice_send_test_invoice', 'zbs_invoice_send_test_invoice' );
function zbs_invoice_send_test_invoice(){
	
	check_ajax_referer( 'inv-ajax-nonce', 'security' );    
	$zbs_invID = -1; $em = ''; $r = array();

	if (isset($_POST['id']) && !empty($_POST['id'])) $zbs_invID = (int)sanitize_text_field($_POST['id']);  //accepts the post ID
	if (isset($_POST['em']) && !empty($_POST['em'])) $em = sanitize_text_field($_POST['em']);

    #debug 
    $r['em'] = $em;
    #debug $r['id'] = $zbs_invID;

	//validate the email
	if (!zeroBSCRM_validateEmail($em)){
		$r['message'] = 'Not a valid email';
		echo json_encode($r);
		die();
	} else $email = $em;

	#} Check id + perms + em
	if ($zbs_invID <= 0 || empty($em) || !zeroBSCRM_permsInvoices()){
		die();
	}
	
	$body = zeroBSCRM_invoice_generateNotificationHTML($zbs_invID,true);

	$biz_name = zeroBSCRM_getSetting('businessname');
	$biz_extra = zeroBSCRM_getSetting('businessextra');

	$subject = '[Test Email] You have received an invoice';
	$headers = array('Content-Type: text/html; charset=UTF-8');
	$attachments = array();

	/* WH did unbeknownst, seperately //invoice attachments (actually called invoices but these now can be things like toggl timesheet reports(?) or T&Cs....
	$zbsCustomerInvoices = get_post_meta($zbs_invID, 'zbs_customer_invoices', true);
    foreach($zbsCustomerInvoices as $invoice){
    	$attachments[] = $invoice['file'];
	}
	*/
		// Attachments?
        $attachments = array();
        $zbsSendAttachments = get_post_meta($zbs_invID, 'zbs_inv_sendattachments', true);
        if ($zbsSendAttachments == "1"){
        	$invFiles = get_post_meta($zbs_invID, 'zbs_customer_invoices', true);
        	if (is_array($invFiles) && count($invFiles) > 0){

        		// cycle through files + add as attachments
        		// we pass as 2part array so they don't have their funky md5 prefixes..
        		foreach($invFiles as $invFile){

                    $filename = basename($invFile['file']);
                    // if in privatised system, ignore first hash in name
                    if (isset($invFile['priv'])){

                        $filename = substr($filename,strpos($filename, '-')+1);
                    }

                    $attachments[] = array($invFile['file'],$filename);

                }
        	}
        }
	


	//ah.. still uses WP mail - but this should still be sending.
//	wp_mail( $email, $subject, $body, $headers, $attachments );

	/* new HTML send - to code up with actual invoice html (i.e. replace the body, properly) */

//	$html = zeroBSCRM_mailTemplate_emailPreview($emailtab);



  /* old way


	wp_mail( $test_email, $subject, $html, $headers );

  */
  

    // discern del method
    $mailDeliveryMethod = zeroBSCRM_mailTemplate_getMailDelMethod(ZBSEMAIL_EMAILINVOICE);
    if (!isset($mailDeliveryMethod) || empty($mailDeliveryMethod)) $mailDeliveryMethod = -1;

	// build send array
	$mailArray = array(
	  'toEmail' => $email,
	  'toName' => '',
	  'subject' => $subject,
	  'headers' => $headers,
	  'body' => $body,
	  'textbody' => '',
	  'attachments' => $attachments,
	  'options' => array(
		'html' => 1
	  )
	);

	// Sends email
	$sent = zeroBSCRM_mailDelivery_sendMessage($mailDeliveryMethod,$mailArray);



	//sends the invoice via wp_mail (for now)... 
	$r['message'] = 'All done OK';
	echo json_encode($r);
	die(); //exiting ... yarp..
}


/* Not req.
function my_custom_email_content_type() {
    return 'text/html';
}
*/

#} We need to set the from email (mail campaigns may do this too?)
#} REMOVED - THESE FILTERS CHANGE IT FOR EVERYTHING. BEST DONE VIA THE HEADERS passed to wp_mail..
/* add_filter( 'wp_mail_from', 'zbs_wp_mail_from' );
function zbs_wp_mail_from( $original_email_address ) {
	$f = zeroBSCRM_getSetting('invfromemail');
	if($f == ''){
	return $original_email_address;
	}else{
	return $f;    
	}
}

add_filter( 'wp_mail_from_name', 'zbs_wp_mail_from_name' );
function zbs_wp_mail_from_name( $original_email_from ) {
  	$n = zeroBSCRM_getSetting('invfromname');
  	if($n == ''){
  		return $original_email_from;
  	}else{
  		return $n;
	}
}
*/
	add_action( 'wp_ajax_zbs_get_invoice_data', 'zeroBSCRM_AJAX_getInvoice' );
	 function zeroBSCRM_AJAX_getInvoice(){

	    // check nonce
	    check_ajax_referer( 'zbscrmjs-ajax-nonce', 'sec' );

	    // check perms
		if (!zeroBSCRM_permsIsZBSUser()) {
			zeroBSCRM_sendJSONError();
			exit();
		}

	    // build + return
	    $invID = -1; if (isset($_POST['invid'])) $invID = (int)$_POST['invid'];

	    if ($invID > 0){
	        
	        // retrieve ID
	        $invID = (int)sanitize_text_field( $_POST['invid'] );

	        // retrieve obj to return
	        $data = zeroBSCRM_invoicing_getInvoiceData($invID);
	        
	        // pass back in json
	        zeroBSCRM_sendJSONSuccess($data);
	        exit();

	    } else {

	        // pass -1 if it is a new invoice (vs edit invoice)
	        // defaults (invoice_id) will be the next available ID? 
	        // WH how do we handle the "New" creation want it to return defaults
	        // but if a new invoice, the $objID will be -1? 
	        // WP makes and 'auto-draft' and gets that postID 
	        // so if 2 people make an invoice at once, it won't use the same ID.
	        // probably need to consider this and race conditions on save or smt?
	        // WH Notes: Agreed, for now just rolling this in, to discuss, (perhaps v3.1?)

	        // build default
	        $data['invoiceObj'] = zeroBSCRM_get_invoice_defaults(-1);
	        $data['tax_linesObj'] = zeroBSCRM_getTaxTableArr();

	        // pass back in json
	        zeroBSCRM_sendJSONSuccess($data);
	        exit();
	        
	    }
	    
	    // exit json
	    zeroBSCRM_sendJSONError(array('here'));
	    exit();
	 }

/* ======================================================
	/ ZBS Invoicing
   ====================================================== */


/* ======================================================
	Admin AJAX: New Feedback
====================================================== */



	#} General Helpers - sends us back a feedback comment
	add_action( 'wp_ajax_zbsbfeedback', 'zeroBSCRM_AJAX_betaFeedback' );
	function zeroBSCRM_AJAX_betaFeedback(){

		#} Check nonce
		check_ajax_referer( 'zbscrmjs-glob-ajax-nonce', 'sec' );  //nonce to bounce out if not from right page

		// is admin side, so trust up to this point. (check has zbs rights though)
		if (!zeroBSCRM_permsIsZBSUser()) {
			zeroBSCRM_sendJSONError();
			exit();
		}

		// retrieve deets
		$comm = '';
		if ( isset( $_POST['comm'] ) && ! empty( $_POST['comm'] ) ) {
			$comm = zeroBSCRM_textProcess( $_POST['comm'] );
		}
		$email = '';
		if ( isset( $_POST['email'] ) && ! empty( $_POST['email'] ) ) {
			$email = sanitize_email( $_POST['email'] );
		}
		$page = '';
		if ( isset( $_POST['page'] ) && ! empty( $_POST['page'] ) ) {
			$page = sanitize_text_field( $_POST['page'] );
		}
		$area = '';
		if ( isset( $_POST['area']) && !empty($_POST['area'])) $area = sanitize_text_field($_POST['area']);

		// simple send :) (via their default mail route)
		global $zbs;

			$title = 'Feedback: '.$area;
			$content = '<p>Feedback from: '.$email.'</p>';
			$content .= '<p>User: '.zeroBSCRM_currentUser_displayName().'</p>';
			$content .= '<p>Area: '.$area.'</p>';
			$content .= '<p>Page: '.$page.'</p>';
			$content .= '<p>Site: '.get_site_url().'</p>';
			$content .= '<hr /><p>Comment:</p><hr /><div>'.zeroBSCRM_textExpose($comm).'</div>';

			// use this template, is useful :)
			$emailHTML = jpcrm_mailTemplates_generic_msg(true,$content,$title);

            // build send array
            $mailArray = array(
            'toEmail' => $zbs->urls['betafeedbackemail'],
            'toName' => 'Support',
            'subject' => $title,
            'headers' => array(),
            'body' => $emailHTML,
            'textbody' => '',
            'options' => array(
              'html' => 1
            ),
            'tracking' => false
            );

            $sent = zeroBSCRM_mailDelivery_sendMessage(-1,$mailArray);

		header('Content-Type: application/json');
		echo json_encode(array('fini'=>1));
		exit();
	}

/* ======================================================
	/ Admin AJAX: Beta Feedback
====================================================== */



/* ======================================================
	Admin AJAX: Events
====================================================== */

add_action( 'wp_ajax_mark_task_complete', 'zeroBSCRM_ajax_mark_task_complete' );
function zeroBSCRM_ajax_mark_task_complete(){

    check_ajax_referer('zbscrmjs-glob-ajax-nonce','sec');

    if (!zeroBSCRM_permsEvents()){

      zeroBSCRM_sendJSONError(array('permission_error'=>1));
      exit();

    }

    global $zbs;

    if (isset($_POST['way']) && isset($_POST['taskID'])){

        $way    = sanitize_text_field($_POST['way']);
        $taskID = (int)sanitize_text_field($_POST['taskID']);

        if ($zbs->isDAL3()){

          // 3.0
          if ($way == 'complete') $newStatus = 1;
          if ($way == 'incomplete') $newStatus = -1;
          
          if (isset($newStatus))
            $zbs->DAL->events->setEventCompleteness($taskID, $newStatus);
          else {
            zeroBSCRM_sendJSONError(array('nostatus'=>1));
            exit();
          }


        } else {

          // <3.0
          $task_meta = zeroBSCRM_task_getMeta($taskID);

          if($way == 'complete'){
              $task_meta['complete'] = 1;
          }
          if($way == 'incomplete'){
              $task_meta['complete'] = -1;
          }
          zeroBSCRM_task_updateMeta($taskID, $task_meta);

        }

        $m['message'] = 'Marked ' . $way;
        echo json_encode($m,true);
        die();

    }

    zeroBSCRM_sendJSONError(array('noparams'=>1));
    exit();

}

/* ======================================================
	/ Admin AJAX: Events
====================================================== */

	// sends a proper error response
	function zeroBSCRM_sendJSONError($errObj='', $status_code = 500 ){
		wp_send_json_error( $errObj, $status_code );
	}

	function zeroBSCRM_sendJSONSuccess($successObj=''){

		header('Content-Type: application/json');
		echo json_encode($successObj,true);
		exit();
	}
