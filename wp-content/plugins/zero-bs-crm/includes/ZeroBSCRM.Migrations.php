<?php
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 * V1.1.18
 *
 * Copyright 2020 Automattic
 *
 * Date: 30/08/16
 */

/* ======================================================
  Breaking Checks ( stops direct access )
   ====================================================== */
    if ( ! defined( 'ZEROBSCRM_PATH' ) ) exit;
/* ======================================================
  / Breaking Checks
   ====================================================== */





/* ======================================================
	MIGRATION FUNCS
   ====================================================== */

global $zeroBSCRM_migrations; $zeroBSCRM_migrations = array(
	'240', // Refresh user roles
	'288', // build client portal page (moved to shortcodes) if using
	'2963', // 2.96.3 - installs page templates
	'29999', // Flush permalinks 
	'410', // 4.10.0 - Assert that dompdf fonts are installed
	'411', // 4.11.0 - Ensure upload folders are secure
	'50', // 5.0 - Alter external sources table for existing users (added origin)

	/*
	'123','127',
	'216','22',
	,'241','242','250','2531',
	'270', // DAL 2.0
	'275',
	'280','281',
	'287',
	'2943', // 2.94.2 rebuild roles (added logs perms) + notice for mail delivery peeps (not using wp-mail)
	'295', // 2.94 - mikes alter of sys emails table + reset user roles (Added emails)
	'2952', // 2.95.2 - adds cron manager table silently (mc2 prep)
	'2962', // 2.96.2 - although set to 2953 as less so will run in v2.96.1 also
	'2964', // 2.96.4 - FIX for missing 2.96.3 extra template for 'password reset email for cp'
	'2966',	// 2.96.6 - adds extra template for 'pdf statement'
	'2972', // 2.97.2 - adds db performance improvements for contacts retrieved via tag (including adding indexes)
	'2974', // 2.97.4 - fixes duplicated email templates (found on 2 installs so far)
	'2975', // 2.97.5 - (actually included in 2.97.4) corrects borked external sources setup.
	'2977', // 2.97.7 - Fixes an index to allow non-uniques (for user screen options)
	'2984', // 2.98.4 - Fixes segment conditions bug
	'2981',	// 2.98.1 - add in the invoice tax table
	'2999', // 2.99.0 - install tables for DAL3.0	
		'3000', // 3.0 - Migrate all the THINGS
	'305', // 3.0.5 - catch instances where really old installs saved customer statuses as trans statuses gh-179
	'308', // 3.0.8 - Anyone with pdf module installed already, install pdf fonts for them
	'3012', // 3.0.12 - Remove any uploads\ directory which may have been accidentally created pre 2.96.6			
	'3013', // 3.0.13 - Mark any wp options we've set as autoload=false, where they are not commonly needed (perf)
	'3014', // 3.0.14 - Correct any wrongly permified transaction statuses 'to include'
	'3017', // 3.0.17 - Change line item quantity to a decimal
  	'3018', // 3.0.18 - Catch any Contact date custom fields (which were in date format pre v3) and convert them to UTS as v3 expects
	'3019', // 3.0.19 - Migrate the SMTP passwords
	'402', // 4.0.2 - Fix the transactions data
	'407', // 4.0.7 - corrects outdated event notification template
    '408', // 4.0.8 - Add default reference type of invoices & Update the existing template for email notifications (had old label)
	//'4010', // 4.0.10 - Jan sale notification 
	'450', // 4.5.0 - Adds indexing protection to directories with potentially sensitive .html files


*/
	);

global $zeroBSCRM_migrations_requirements; $zeroBSCRM_migrations_requirements = array(
		//'270' => array('preload'),
		'288' => array('isDAL2','postsettings'),
		//'3000' => array('preload','isDAL2'),
		//'3014' => array('isDAL3','postsettings'),
		//'3018' => array('isDAL3','postsettings'),
		//'408' => array('isDAL3','postsettings'),
	);


// mark's a migration complete
function zeroBSCRM_migrations_markComplete($migrationKey=-1,$logObj=false){

	global $zeroBSCRM_migrations;

	if (!empty($migrationKey) && in_array($migrationKey, $zeroBSCRM_migrations)) {

		$completedMigrations = zeroBSCRM_migrations_getCompleted();
		$completedMigrations[] = $migrationKey;

		// we're using wp options because they're reliable OUTSIDE of the scope of our settings model
		// ... which has changed through versions 
		// the separation here is key, at 2.88 WH discovered much re-running + pain due to this.
		// stick to a separate migration system (away from zbssettings)
	    update_option('zbsmigrations',$completedMigrations, false);

		// log opt?
	    update_option('zbsmigration'.$migrationKey,array('completed'=>time(),'meta'=>$logObj), false);

	}
}

// gets the list of completed migrations
function zeroBSCRM_migrations_getCompleted(){

	// we're using wp options because they're reliable OUTSIDE of the scope of our settings model
	// ... which has changed through versions 
	// the separation here is key, at 2.88 WH discovered much re-running + pain due to this.
	// stick to a separate migration system (away from zbssettings)

	// BUT WAIT! hilariously, for those who already have finished migrations, this'll re-run them
	// ... so here we 'MIGRATE' the migrations :o ffs
	global $zbs; $migrations = $zbs->settings->get('migrations'); if (isset($migrations) && is_array($migrations) && count($migrations) > 0) {
	
		$existingMigrationsMigration = get_option( 'zbsmigrationsdal', -1);

		if ($existingMigrationsMigration == -1){
			// copy over +
			// to stop this ever rerunning + confusing things, we set an option to say migrated the migrations, LOL
			update_option('zbsmigrations',$migrations, false);
			update_option('zbsmigrationsdal',2, false);
		}
	}

	// normal return
	return get_option( 'zbsmigrations', array() );

}

// gets details on a migration
function zeroBSCRM_migrations_geMigration($migrationKey=''){

	// we're using wp options because they're reliable OUTSIDE of the scope of our settings model
	// ... which has changed through versions 
	// the separation here is key, at 2.88 WH discovered much re-running + pain due to this.
	// stick to a separate migration system (away from zbssettings)
	$finished = false; $migrations = zeroBSCRM_migrations_getCompleted(); if (in_array($migrationKey,$migrations)) $finished = true;

	return array($finished,get_option('zbsmigration'.$migrationKey,false));

}

function zeroBSCRM_migrations_run($settingsArr=false){

	global $zeroBSCRM_migrations,$zeroBSCRM_migrations_requirements;

	    // catch migration block removal (can be run from system status):
	    if (current_user_can('admin_zerobs_manage_options') && isset($_GET['resetmigrationblock']) && wp_verify_nonce( $_GET['_wpnonce'], 'resetmigrationblock' ) ){

	        // unblock migration blocks
	        delete_option('zbsmigrationpreloadcatch');
	        delete_option('zbsmigrationblockerrors');

	        // flag
	        $migrationBlocksRemoved = true;
	    }

	#} Check if we've been stumped by blocking errs, and STOP migrating if so
	$blockingErrs = get_option( 'zbsmigrationblockerrors', false);
    if ($blockingErrs !== false && !empty($blockingErrs)) return false;

	#} load migrated list if not loaded
	$migratedAlreadyArr = zeroBSCRM_migrations_getCompleted();

	#} Run count
	$migrationRunCount = 0;

	#} cycle through any migrations + fire if not fired.
	if (count($zeroBSCRM_migrations) > 0) foreach ($zeroBSCRM_migrations as $migration){

		if (!in_array($migration,$migratedAlreadyArr) && function_exists('zeroBSCRM_migration_'.$migration)) {

			$run = true;

			// check reached state
			if (isset($zeroBSCRM_migrations_requirements[$migration])){


				// 'preload' requirement means this migration needs to run AFTER a reload AFTER the previous migration
				// ... so if preload here, we kill this loop, if prev migrations have run
				if (in_array('preload', $zeroBSCRM_migrations_requirements[$migration]) && $migrationRunCount > 0){

					// ... as a catch to stop infinite reloads, we check whether more than 3 of these have run in a row, and we stop that.
					$previousAttempts = get_option( 'zbsmigrationpreloadcatch', array());
					if (!is_array($previousAttempts)) $previousAttempts = array();
					if (!isset($previousAttempts[$migration])) $previousAttempts[$migration] = 1;
					if ($previousAttempts[$migration] < 5){

						// update count
						$previousAttempts[$migration]++;
						update_option('zbsmigrationpreloadcatch', $previousAttempts, false);

						// stop running migrations, reload the page
						header("Refresh:0");
						exit();

					} else {

						// set a global which'll show up on systemstatus if this state occurs.
						update_option('zbsmigrationblockerrors', $migration, false);					

						// expose an error that the world's about to rupture
					    add_action('after-zerobscrm-admin-init','zeroBSCRM_adminNotices_majorMigrationError');
			    		add_action( 'admin_notices', 'zeroBSCRM_adminNotices_majorMigrationError' );

					}

				}				

				// assume func
				foreach ($zeroBSCRM_migrations_requirements[$migration] as $check){

					// skip 'preload', dealt with above
					if ($check !== 'preload'){

						$checkFuncName = 'zeroBSCRM_migrations_checks_'.$check;
						if (!call_user_func($checkFuncName)) $run = false;

					}
				}
				

			}

			// go
			if ($run) {

				// run migration
				call_user_func('zeroBSCRM_migration_'.$migration);
				
				// update count
				$migrationRunCount++;

			}
		}

	}

}

// Migration dependency check for DAL2
function zeroBSCRM_migrations_checks_isDAL2(){

	global $zbs; return $zbs->isDAL2();

}
// Migration dependency check for DAL3
function zeroBSCRM_migrations_checks_isDAL3(){

	global $zbs; return $zbs->isDAL3();

}

function zeroBSCRM_migrations_checks_postsettings(){

	global $zbs;
	/* didn't work:
	if (isset($zbs->settings) && method_exists($zbs->settings,'get')){
		$possiblyInstalled = $zbs->settings->get('settingsinstalled',true);
		if (isset($possiblyInstalled) && $possiblyInstalled > 0) return true;
	} */
	// HARD DB settings check
	try {
		$potentialDBSetting = $zbs->DAL->getSetting(array('key' => 'settingsinstalled','fullDetails' => false));	

		if (isset($potentialDBSetting) && $potentialDBSetting > 0) {

			return true;

		}

	} catch (Exception $e){

	}

	return false;
}

// general migration mechanism error
function zeroBSCRM_adminNotices_majorMigrationError(){

     //pop in a Notify Me Notification here instead....?
	 if (get_current_user_id() > 0){

	     // already sent?
	     $msgSent = get_transient('zbs-migration-general-errors');
	     if (!$msgSent){

	       zeroBSCRM_notifyme_insert_notification(get_current_user_id(), -999, -1, 'migration.blocked.errors','migration.blocked.errors');
	       set_transient( 'zbs-migration-general-errors', 20, 24 * 7 * HOUR_IN_SECONDS );

	    }

	}

}

/* ======================================================
	/ MIGRATION FUNCS
   ====================================================== */



/* ======================================================
	MIGRATIONS
   ====================================================== */

	/*
	* Migration 2.4 - Refresh user roles
	*/
	function zeroBSCRM_migration_240(){

		#} Glob
		global $zbs, $zeroBSCRM_Conf_Setup; #req

		#} This function migrates users from before ver 2.4

		  #} re-add/remove any roles :)

			    // roles
				zeroBSCRM_clearUserRoles();

				// roles + 
				zeroBSCRM_addUserRoles();

	    	zeroBSCRM_migrations_markComplete('240',array('updated'=>1));
			


	}


	/*
	* Migration 2.88 - build client portal page (moved to shortcodes) if using
	*/
	function zeroBSCRM_migration_288(){

		global $zbs;

		zeroBSCRM_portal_checkCreatePage();
		
		zeroBSCRM_migrations_markComplete('288',array('updated'=>'1'));

	}


	/*
	* Migration 2.4 - Refresh user roles
	*  Previously this was a number of template related migrations
	*  for v5 we combined these, though in time the need for this method of install should be done away with
	*  Previously, migrations: 2.96.3, 2.96.4, 2.96.6, 2.97.4, 4.0.7, 4.0.8
	*/
	function zeroBSCRM_migration_2963(){
		
		global $zbs, $wpdb, $ZBSCRM_t;

		#} Check + create
		zeroBSCRM_checkTablesExist();

		#} Make the DB emails...
		zeroBSCRM_populateEmailTemplateList();


		// ===== Previously: Migration 2.96.3 - adds new template for 'client portal pw reset'

		#} default is admin email and CRM name	
		//now all done via zeroBSCRM_mailDelivery_defaultFromname
		$from_name = zeroBSCRM_mailDelivery_defaultFromname();

		/* This wasn't used in end, switched to default mail delivery opt 
		$from_address = zeroBSCRM_mailDelivery_defaultEmail();; //default WordPress admin email ?
		$reply_to = '';
		$cc = ''; */
		$deliveryMethod = zeroBSCRM_getMailDeliveryDefault(); 
		
		$ID = 6;
		$reply_to = '';
		$cc = '';
		$bcc = '';

		#} The email stuff...
		$subject = __("Your Client Portal Password", 'zero-bs-crm');
		$content = zeroBSCRM_mail_retrieveDefaultBodyTemplate('clientportalpwreset');
		$active = 1; //1 = true..
		if(zeroBSCRM_mailTemplate_exists($ID) == 0){
			$content = zeroBSCRM_mailTemplate_processEmailHTML($content);
			//zeroBSCRM_insertEmailTemplate($ID,$from_name,$from_address,$reply_to,$cc,$bcc,$subject,$content,$active);
			zeroBSCRM_insertEmailTemplate($ID,$deliveryMethod,$bcc,$subject,$content,$active);
		}

		// ===== / Previously: Migration 2.96.3


		// ===== Previously: last one hadn't got the html file, this ADDS file proper :)

		#} default is admin email and CRM name	
		//now all done via zeroBSCRM_mailDelivery_defaultFromname
		$from_name = zeroBSCRM_mailDelivery_defaultFromname();

		/* This wasn't used in end, switched to default mail delivery opt 
		$from_address = zeroBSCRM_mailDelivery_defaultEmail();; //default WordPress admin email ?
		$reply_to = '';
		$cc = ''; */
		$deliveryMethod = zeroBSCRM_getMailDeliveryDefault(); 
		
		$ID = 6;
		$reply_to = '';
		$cc = '';
		$bcc = '';

		// BRUTAL DELETE old one
		$wpdb->delete( $ZBSCRM_t['system_mail_templates'], array( 'zbsmail_id' => $ID ) );

		#} The email stuff...
		$subject = __("Your Client Portal Password", 'zero-bs-crm');
		$content = zeroBSCRM_mail_retrieveDefaultBodyTemplate('clientportalpwreset');
		
		$active = 1; //1 = true..
		if(zeroBSCRM_mailTemplate_exists($ID) == 0){
			$content = zeroBSCRM_mailTemplate_processEmailHTML($content);
			//zeroBSCRM_insertEmailTemplate($ID,$from_name,$from_address,$reply_to,$cc,$bcc,$subject,$content,$active);
			zeroBSCRM_insertEmailTemplate($ID,$deliveryMethod,$bcc,$subject,$content,$active);
		}

		// ===== / Previously: last one hadn't got the html file, this ADDS file proper :)


		// ===== Previously: adds template for 'invoice summary statement sent'

		#} default is admin email and CRM name	
		//now all done via zeroBSCRM_mailDelivery_defaultFromname
		$from_name = zeroBSCRM_mailDelivery_defaultFromname();

		/* This wasn't used in end, switched to default mail delivery opt 
		$from_address = zeroBSCRM_mailDelivery_defaultEmail();; //default WordPress admin email ?
		$reply_to = '';
		$cc = ''; */
		$deliveryMethod = zeroBSCRM_getMailDeliveryDefault(); 
		
		$ID = 7;
		$reply_to = '';
		$cc = '';
		$bcc = '';
		
		#} The email stuff...
		$subject = __("Your Statement", 'zero-bs-crm');
		$content = zeroBSCRM_mail_retrieveDefaultBodyTemplate('invoicestatementsent');

		// BRUTAL DELETE old one
		$wpdb->delete( $ZBSCRM_t['system_mail_templates'], array( 'zbsmail_id' => $ID ) );
		
		$active = 1; //1 = true..
		if(zeroBSCRM_mailTemplate_exists($ID) == 0){
			$content = zeroBSCRM_mailTemplate_processEmailHTML($content);
			//zeroBSCRM_insertEmailTemplate($ID,$from_name,$from_address,$reply_to,$cc,$bcc,$subject,$content,$active);
			zeroBSCRM_insertEmailTemplate($ID,$deliveryMethod,$bcc,$subject,$content,$active);
		}

		// ===== / Previously: adds template for 'invoice summary statement sent'


		// ===== Previously: 2.97.4 - fixes duplicated email templates (found on 2 installs so far)

		// 7 template emails up to here :)
		for ($i = 0; $i <= 7; $i++){

			// count em
			$sql = $wpdb->prepare("SELECT ID FROM " . $ZBSCRM_t['system_mail_templates'] . " WHERE zbsmail_id = %d GROUP BY ID ORDER BY zbsmail_id DESC, zbsmail_lastupdated DESC", $i);
			$r = $wpdb->get_results($sql, ARRAY_A);

				// if too many, delete oldest (few?)
				if (is_array($r) && count($r) > 1){

					$count = 0;

					// first stays, as the above selects in order by last updated
					foreach ($r as $x){

						// if already got one, delete this (extra)
						if ($count > 0){

							// BRUTAL DELETE old one
							$wpdb->delete( $ZBSCRM_t['system_mail_templates'], array( 'ID' => $x['ID'] ) );

						}

						$count++;

					}

				}

		}
		
		// ===== / Previously: 2.97.4 - fixes duplicated email templates (found on 2 installs so far)
		

		// ===== Previously: 4.0.7 - corrects outdated event notification template

		// retrieve existing template - hardtyped
		$existingTemplate = $wpdb->get_var('SELECT zbsmail_body FROM '.$ZBSCRM_t['system_mail_templates'].' WHERE ID = 6');

		// load new
		$newTemplate = zeroBSCRM_mail_retrieveDefaultBodyTemplate('eventnotification');

		// back it up into a WP option if was different
	    if ($existingTemplate !== $newTemplate) update_option('jpcrm_eventnotificationtemplate',$existingTemplate, false);

		// overwrite
		$sql = "UPDATE " . $ZBSCRM_t['system_mail_templates'] . " SET zbsmail_body = %s WHERE ID = 6";
		$q = $wpdb->prepare($sql,array($newTemplate));
		$wpdb->query($q);
		
		// ===== / Previously: 4.0.7 - corrects outdated event notification template
		

		// ===== Previously: 4.0.8 - Set the default reference type for invoices & Update the existing template for email notifications (had old label)
        
        if ( $zbs->DAL->invoices->getFullCount() > 0 ) {
            // The user has used the invoice module. Default reference type = manual
            $zbs->settings->update( 'reftype', 'manual' );
        }


        // Update the existing template for email notifications (had old label)     
		global $ZBSCRM_t,$wpdb;

		// retrieve existing template - hardtyped
		$existingTemplate = $wpdb->get_var('SELECT zbsmail_body FROM '.$ZBSCRM_t['system_mail_templates'].' WHERE ID = 4');

		// load new
		$newTemplate = zeroBSCRM_mail_retrieveDefaultBodyTemplate('invoicesent');

		// back it up into a WP option if was different
	    if ($existingTemplate !== $newTemplate) update_option('jpcrm_invnotificationtemplate',$existingTemplate, false);

		// overwrite
		$sql = "UPDATE " . $ZBSCRM_t['system_mail_templates'] . " SET zbsmail_body = %s WHERE ID = 4";
		$q = $wpdb->prepare($sql,array($newTemplate));
		$wpdb->query($q);

		// ===== / Previously: 4.0.8 - Set the default reference type for invoices & Update the existing template for email notifications (had old label)

		zeroBSCRM_migrations_markComplete('2963',array('updated'=>'1'));

	}


	/*
	* Migration 2.99.99 - set permalinks to flush (was used with v3.0 migration, left in tact as portal may be dependent)
	*/
	function zeroBSCRM_migration_29999(){

		// set permalinks to flush, this'll cause them to be refreshed on 3000 migration
		// ... as that has preload setting
		zeroBSCRM_rewrite_setToFlush();

		// fini
		zeroBSCRM_migrations_markComplete('29999',array('updated'=>1));

	}


	/*
	* Migration 4.10.0 - For those with dompdf installed, re-ensure that our global fonts are installed
	*/
	function zeroBSCRM_migration_410(){

		global $zbs;

		$shouldBeInstalled = zeroBSCRM_getSetting( 'feat_pdfinv' );
		if ( $shouldBeInstalled == "1" ){

	        // install pdf fonts 
			$fonts = $zbs->get_fonts();
			$fonts->retrieve_and_install_default_fonts();

		}

		// fini
		zeroBSCRM_migrations_markComplete( '410', array( 'updated' => 1 ) );

	}

	/*
	* Migration 4.11.0 - secure upload folders
    *  previously:
    *  4.5.0 - Adds indexing protection to directories with potentially sensitive .html files
	*  4.11.0 - secure upload folders
	*/
	function zeroBSCRM_migration_411(){

		$wp_uploads_dir = wp_upload_dir();

		// directories to secure
		// if these ever expand beyond this we should move the list to core & manage periodic checks
		$directories = array(

			ZEROBSCRM_PATH . 'templates/',
			ZEROBSCRM_PATH . 'templates/emails/',
			ZEROBSCRM_PATH . 'templates/invoices/',
			ZEROBSCRM_PATH . 'templates/quotes/',

			$wp_uploads_dir['basedir'] . '/' . 'zbscrm-store/',
			$wp_uploads_dir['basedir'] . '/' . 'zbscrm-store/_wip/',

		);

		// secure them!
		foreach ( $directories as $directory ){
			jpcrm_secure_directory_from_external_access( $directory, true );
		}

		// mark complete
		zeroBSCRM_migrations_markComplete('411',array('updated'=>1));

	}

	/*
	* Migration 5.0 - Alter external sources table for existing users (added origin)
	*/
	function zeroBSCRM_migration_50(){

		global $zbs, $wpdb, $ZBSCRM_t;

		// external source tweak
		if ( !zeroBSCRM_migration_tableHasColumn( $ZBSCRM_t['externalsources'], 'zbss_origin' ) ){

			$sql = "ALTER TABLE " . $ZBSCRM_t['externalsources'] . " ADD COLUMN `zbss_origin` VARCHAR(400) NULL DEFAULT NULL AFTER `zbss_uid`, ADD INDEX (zbss_origin);";
			$wpdb->query( $sql );

		}

		// add transaction status

		// build string
    $transaction_statuses = zeroBSCRM_getTransactionsStatuses(true);
    $deleted_string = __( 'Deleted', 'zero-bs-crm' );
    if ( !in_array( $deleted_string, $transaction_statuses ) ){
      $transaction_statuses[] = $deleted_string;
    }
    $transaction_statuses_str = implode( ',', $transaction_statuses );

    // update
    $customisedFields = $zbs->settings->get('customisedfields');
    $customisedFields['transactions']['status'][1] = $transaction_statuses_str;   
    $zbs->settings->update('customisedfields',$customisedFields);


		// mark complete
		zeroBSCRM_migrations_markComplete( '50', array( 'updated' => 1 ) );

	}


/* ======================================================
	/ MIGRATIONS
   ====================================================== */


/* ======================================================
   MIGRATION Helpers
   ====================================================== */

   // simplistic arr manager
   function zeroBSCRM_migration_addErrToStack($err=array(),$errKey=''){

   		if ($errKey !== ''){

   			$existing = get_option($errKey, array());

   			// catch err in err stack.
   			if (!is_array($existing)) $existing = array();

   			// add + update
   			$existing[] = $err;
			update_option( $errKey, $existing, false);

			return true;

   		}

   		return false;
   }

   // checks if a column already exists
   // note $tableName is used unchecked
   function zeroBSCRM_migration_tableHasColumn( $table_name, $column_name ){

   		global $wpdb;

   		if ( !empty( $table_name ) && !empty( $column_name ) ){

   			$query = $wpdb->prepare( "SHOW COLUMNS FROM " . $table_name . " LIKE %s", $column_name );
	
	   		$row = $wpdb->get_results( $query );
			
			if ( is_array( $row ) && count( $row ) > 0 ){

				return true;

			}

		}

		return false;

   }

   /*
   * Verifies if a mysql table has an index for a column
   */
   function jpcrm_migration_table_has_index( $table_name, $column_name ){

   		global $wpdb;

		$query = $wpdb->prepare( "SHOW INDEX FROM " . $table_name . " WHERE Column_name = %s", $column_name );
		$row = $wpdb->get_results( $query );

		if ( is_array( $row ) && count( $row ) > 0){

			return true;

		}

		return false;
		
   }

   /**
	* Retrieves the data typo of the given colemn name in the given table name.
	* It's worth noting that it will have the size of the field too, so `int(10)`
	* rather than just `int`.
	*
	* @param $table_name string The table name to query.
	* @param $column_name string The column name to query.
	*
	* @return string|false The column type as a string, or `false` on failure.
	*/
   function zeraBSCRM_migration_get_column_data_type( $table_name, $column_name ) {
	   global $wpdb;

	   $column = $wpdb->get_row( $wpdb->prepare( 
		   "SHOW COLUMNS FROM $table_name LIKE %s",
		   $column_name ) );
	   return empty( $column ) ? false : $column->Type;
   }

/* ======================================================
   / MIGRATION Helpers
   ====================================================== */
