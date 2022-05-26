<?php
/*!
 * Jetpack CRM
 * http://www.zerobscrm.com
 * V1.0
 *
 * Copyright 2020 Automattic
 *
 * Date: 26/05/16
 */

/* ======================================================
  Breaking Checks ( stops direct access )
   ====================================================== */
    if ( ! defined( 'ZEROBSCRM_PATH' ) ) exit;
/* ======================================================
  / Breaking Checks
   ====================================================== */






	#} ================================================================================

		#} Define the key the config model will use to store the config in wp options

	#} ================================================================================

		global $zeroBSCRM_Conf_Setup,$zbs;
		
		$zeroBSCRM_Conf_Setup = array(

			#} Define the key the config model will use to store the config in wp options
			'conf_key' => 'zerobscrmsettings', 

			#} Define the version of config (update as this file updated - any string)
			'conf_ver' => 'v2.2//29.06.2017', 

			#} Define the plugin name, ver and db ver (meta data stored in option)
			'conf_plugin' => 'zeroBSCRM', 
			'conf_pluginver' => false, // catch ver for now, and set up automatically in core
			'conf_plugindbver' => false,// catch ver for now, and set up automatically in core

			#} Added DMZ Config (this stores all dmz config settings)
			'conf_dmzkey' => 'zbscrm_dmz',

			#} Protected conf settings, these don't get flushed when restoring defaults
			#} NOTE: They can still be edited via usual edit funcs
			'conf_protected' => array(
				'whlang',
				'customfields',
				'customisedfields',
				#'customviews'
			)

		);

	#} ================================================================================	


	#} ================================================================================

		#} Define default config model that will be loaded on every new init of settings
		#} ... or when a user resets their settings

	#} ================================================================================

		#} Only declared here, then get's shuttled into $zeroBSCRM_Conf_Setup
		#} ... left seperate for ease of reading 
		#} global $zeroBSCRM_Conf_Def;
		$zeroBSCRM_Conf_Def = array( 

											'settingsinstalled' => 1, // check, DO NOT REMOVE (WH)

											#} Defaults		
											'wptakeovermode' => 		0, //
											'wptakeovermodeforall' => 	0,
											'customheadertext' => 		'',
											'killfrontend' => 			0, //
											'loginlogourl' => 			'',
											'filetypesupload' => 		array(
												'pdf' => 1,
												'doc' => 0,
												'docx' => 0,
												'ppt' => 0,
												'pptx' => 0,
												'xls' => 0,
												'xlsx' => 0,
												'csv' => 0,
												'png' => 0,
												'jpg' => 0,
												'jpeg' => 0,
												'gif' => 0,
												'mp3' => 0,
												'txt' => 0,
												'zip' => 0,
												'all' => 0
											),
											'showprefix' => 			1,
											'showaddress' => 			1,
											'secondaddress' => 			1,
											'countries' => 				1,
											'companylevelcustomers' => 	1, // on by default since 4.0.8
											'coororg' => 'co',
											'showthanksfooter' => 1,
											'showthankslogin' => 1, 
											'shareessentials' => 1,

											#} ======  Menu Layout - simplified default
											'menulayout' => 2,  
											
											#} ======= Currency Settings
											'currency' => 	array(
												'chr'	=> '$',
												'strval' => 'USD'
											),

											'currency_position' => 0,
											'currency_format_thousand_separator' => ',',
											'currency_format_decimal_separator' => '.',
											'currency_format_number_of_decimals' => 2,

											#} ======= / Currency Settings

											#} ======= SMTP (Mail Delivery)
											'smtpaccs' => array(),
											'smtpkey' => '', //enc key
											#} ======= / SMTP (Mail Delivery)

											#} ======= Click 2 call
											'clicktocall' => 0,
											'clicktocalltype' => 1, // 1 = tel: , 2 = callto:
											#} ======= / Click 2 call

											#} ======= AVATARS
											'avatarmode' => 1, // 1 = gravitar only, 2 = custom imgs, 3 = none
											#} ======= / AVATARS


											#} ====== global biz info Settings Tab
											'businessname' => '',
											'businessyourname' => '',
											'businessyouremail' => '',
											'businessyoururl' => '',
											'businesstel' => '',
											'twitter' => '',
											'facebook' => '',
											'linkedin' => '',
											#} ====== / global biz info


											#} ====== mail settings tab
											'unsub' => 'Click to unsubscribe: ##UNSUB-LINK##',
											'unsubpage' => -1, // this is the id of wp page where our unsub shortcode should be
											'unsubmsg' => 'You have been successfully unsubscribed.',
											'mailignoresslmismatch' => -1, // makes phpmailer ignore ssl mismatches :)
											#} ====== / global mail settings tab

											
											#} ====== Invoices
											'businessextra' => '',
											'invfromemail' => '',
											'invfromname' => '',
											'statementextra' => '', 
											'invoffset' => 0,
											'invallowoverride' => 1,
											'invtax' => 0,
											'invdis' => 0,
											'invpandp' => 0,
											'reftype' => 'autonumber',											
											'refprefix' => '',
											'refnextnum' => 1,
											'refsuffix' => '',
											'reflabel' => __( 'Reference:', 'zero-bs-crm' ),
											'inv_pdf_template' => '',
											'inv_portal_template' => '',
											'statement_pdf_template' => '',
											'invcustomfields' => '',
											'contactcustomfields' => '',
											'companycustomfields' => '',
											#} ====== / Invoices

											#} ======  Invoicing Pro
											'invpro_pay' => 2,   //default to PayPal
											'stripe_sk' => '',
											'stripe_pk' => '',
											'ppbusinessemail' => '',
											#} ====== / Invoicing Pro


											#} ======= Quotes
											'quoteoffset' => 0,
											'usequotebuilder' => 1, 
											'showpoweredbyquotes' => 1, // DEPRECATED 2.96.7, use showpoweredby
											'zbs_newquote_email_content' => '',
											'zbs_quoteacc_email_content' => '',
											'quote_pdf_template' => '',
											#} ======= / Quotes

											#} ======= Events / Task scheduler
											'taskownership' => 0,
											#} ======= / Events / Task scheduler

											#} ======= Internal Automator: Autologging: Settings
											'autolog_customer_new' => 1,
											'autolog_company_new' => 1,
											'autolog_quote_new' => 1,
											'autolog_quote_accepted' => 1,
											'autolog_invoice_new' => 1,
											'autolog_transaction_new' => 1,
											'autolog_event_new' => 1,
											'autolog_clientportal_new' => 1,
											'autolog_customer_statuschange' => 0,
											'autolog_mailcampaigns_send' => 1, 
											'autolog_mailcampaigns_open' => 1, 
											'autolog_mailcampaigns_click' => 1, 
											'autolog_mailcampaigns_unsubscribe' => 1, 
											#} ======= / Internal Automator: Autologging: Settings
											
											#} ======= Forms Settings
											'showformspoweredby' => 1, // since 2.96.7 (was showpoweredby before, but shared between forms + portal ?!)
											'usegcaptcha' => 	0,
											'gcaptchasitekey' => '',
											'gcaptchasitesecret' => '',
											#} ======= / Forms Settings

											#} ========= Portal Settings
											'showportalpoweredby' => 1, // since 2.96.7 (was showpoweredby before, but shared between forms + portal ?!)
											'portalpage'	=> 0,
											'zbs_portal_email_content' => '',
											'portalusers'   => 0,
											'portalusers_extrarole' => '',
											'portalusers_status' => 'all',
											'portal_hidefields' => 'status,email', // csv of fieldkeys to hide from edits on portal 'Your details'

											'easyaccesslinks' => 0, // hash urls invs + quotes v3.0
											'fileview' => 'table', // since 2.99.9 (moved from wp option) - is cpp specific.
											'cpp_fileview' => 'listview', // since 2.99.9.10 - cpp default

											#} ========== / Portal Settings

											#} ========= Social Settings

											'usesocial'	=> 1,

											#} ========== / Social Settings

											#} ========= Aliases/AKA Mode Settings

											'useaka'	=> 1,

											#} ========== / Aliases/AKA Mode Settings

											#} ========= Object Nav Settings

											'objnav'	=> -1,

											#} ========== / Object Nav Settings

											#} ========= Email templating & tracking Settings

											'emailtracking'	=> 1,
											'directmsgfrom' => 1,
											'emailpoweredby'	=> 1,

											#} ========== / Email templating & tracking Settings								

											#} ======= Free Extensions Settings
											'feat_quotes' => 1,
											'feat_invs' => 1,
											'feat_forms' =>  1,
											'feat_pdfinv' => 1,
											'feat_csvimporterlite' => 1,
											'feat_portal' => 1,   
											'feat_custom_fields' =>  1,
											'feat_api'			 =>  0,
											'feat_calendar'		=>	1,
											'feat_transactions' => 1, 
											'feat_jetpackforms' => 1,
											#} ======= / Free Extensions Settings

											#} ======= PDF Settings
											'pdf_fonts_installed' => 1,
											'pdf_extra_fonts_installed' => array(),
											#} ======= / PDF Settings
											
											#} ======= Custom Fields + sortables
											'showid' => 1,
											'fieldoverride' => -1, // allow fields with data to be overriden to blank (api/forms)
											'customfields' => array(

												'customers' => array(
													array('select','Source','Google,Word of mouth,Local Newspaper')
												),
												'companies' => array(),
												'quotes' => array(),
												'invoices' => array(),
												'transactions' => array()

											),
											'customfieldsearch' => -1,
											'defaultstatus' => 'Lead',
											'filtersfromstatus' => 1, // show quickfilters for all statuses
											'filtersfromsegments' => 1, // show quickfilters for all segments
											'fieldsorts' => array(

												'address' => false,
												'customers' => false,
												'quotes' => false,
												'invoices' => false,
												'transactions' => false

											),	
											'fieldhides' => array(

												'address' => false,
												'customers' => false,
												'company' => false,
												'quotes' => false,
												'invoices' => false,
												'transactions' => false

											),		
											'shippingfortransactions' => -1,	
											'paiddatestransaction' => -1,							
											#} ======= / Custom Fields + sortables

											#} ======= Migrations - this stores which have been run!
											'migrations' => array(),
											#} ======= / Migrations
											
											#} ======= Customise Fields
											'customisedfields' => array(

												// NOTE: Any changes here need to be reflected in admin pages (For now)
												// search #UPDATECUSTOMFIELDDEFAULTS

												'customers' => array(
													#} Allow people to order base fields + also modified some... via this
													#} Can remove this and will revert to default
													#} Currently: showhide, value (for now)
													#} Remember, this'll effect other areas of the CRM
													'status'=> array(
														1,'Lead,Customer,Refused,Blacklisted,Cancelled by Customer,Cancelled by Us (Pre-Quote),Cancelled by Us (Post-Quote)'
													),
													'prefix'=> array(
														1,'Mr,Mrs,Ms,Miss,Mx,Dr,Prof,Mr & Mrs'
													)
												),

												#} transaction statuses..
												'transactions' => array(
													#} Allow people to order base fields + also modified some... via this
													#} Can remove this and will revert to default
													#} Currently: showhide, value (for now)
													#} Remember, this'll effect other areas of the CRM
													
													// Note: Changes here should be refelected in `transinclude_status` below
													'status'=> array(
														1,'Succeeded,Completed,Failed,Refunded,Processing,Pending,Hold,Cancelled,Deleted'
													)
												),



												'companies' => array(
													#} Allow people to order base fields + also modified some... via this
													#} Can remove this and will revert to default
													#} Currently: showhide, value (for now)
													#} Remember, this'll effect other areas of the CRM
													'status'=> array(
														1,'Lead,Customer,Refused,Blacklisted'
													)
												),
												'quotes' => array(),
												'invoices' => array()

											),											
											'transinclude_status' => array( 'Succeeded', 'Completed', 'Failed', 'Refunded', 'Processing', 'Pending', 'Hold' ),
											'zbsfunnel' => 'Lead,Customer',
											#} ======= / Customise Fields
											
											#} ======= Customer List Settings
											'perusercustomers' => 0,
											'usercangiveownership' => 0,
											#} ======= / Customer List Settings
											
											#} ======= Customer View Layout
											// LEGACY! This has now been replaced by customviews2
											'customviews' => array(

												#} These use the zbs default funcs but can be overriden :)
												'customer' => array(

													'name' => array('Name'),
													#} Removed for Hayday
													'email' => array('Email'),
													'status' => array('Status'),
													'quotes' => array('Quotes'),
													'invoices' => array('Invoices'),
													'transactions' => array('Transactions'),
													'totalvalue' => array('Total Value'),
													'added' => array('Added')
													//'examplecustom' => array('CUSTOM FIELD','AFunctionDefinedInAddon')
													/* WHERE THIS WOULD BE IN MAIN PHP: 

													function AFunctionDefinedInAddon(){
														return 'LOL';
													}
														
														????

													*/

												)

											),										
											#} ======= / Customer View Layout
											
											#} ======= Customer View Layout v2.0
											'allowinlineedits' => -1,
											'customviews2' => array(

												#} These use the zbs default funcs but can be overriden :)
												'customer' => array(



                                                    'id' => array('ID'),
                                                    'nameavatar' => array(__('Name and Avatar',"zero-bs-crm")),
                                                    'status' => array('Status'),
                                                    'totalvalue' => array('Total Value'),
                                                    'added' => array('Added')

                                                    /* defaults changed 2.11.1 
                                                    
													'name' => array('Name'),
													#} Removed for Hayday
													'email' => array('Email'),
													'status' => array('Status'),
													'quotes' => array('Quotes'),
													'invoices' => array('Invoices'),
													'transactions' => array('Transactions'),
													'totalvalue' => array('Total Value'),
													'added' => array('Added')
													*/

												),

												'company' => array(

                                                    'id' => array('ID'),
                                                    'name' => array(__('Name',"zero-bs-crm")),
                                                    'status' => array(__('Status',"zero-bs-crm")),
                                                    'contacts' => array(__('Contacts',"zero-bs-crm")),
                                                    'added' => array(__('Added',"zero-bs-crm")),
                                                    'editlink' => array(__('Edit',"zero-bs-crm"))

												),


												'quote' => array(

                                          
                                                    'id' => array('ID'),
                                                    'title' => array('Quote Title'),
                                                    'customer' => array('Customer'),
                                                    'status' => array('Status'),
                                                    'value' => array(__('Quote Value',"zero-bs-crm")),
                                                    'editlink' => array(__('Edit',"zero-bs-crm"))

												),


												'invoice' => array(


                                                      'id' => array('ID'),

                                                      'ref' => array('Reference'),
                                                      'customer' => array('Customer'),
                                                      'status' => array('Status'),
                                                      'value' => array(__('Value',"zero-bs-crm")),
                            

                                                      'editlink' => array(__('Edit',"zero-bs-crm"))

												),


												'form' => array(

                                                    'id' => array('ID'),
                                                    'title' => array('Title'),
                                                    'style' => array(__('Style',"zero-bs-crm")),
                                                    'views' => array(__('Views',"zero-bs-crm")),
                                                    'conversions' => array(__('Conversions',"zero-bs-crm")),
                                                    'added' => array(__('Added',"zero-bs-crm")),
                                                    'editlink' => array(__('Edit',"zero-bs-crm"))
                                                    
												),

												'event' => array(

                                                    'id' => array('ID'),
                                                    'title' => array( __('Name','zero-bs-crm') ),
                                                    'desc' => array( __('Description', 'zero-bs-crm' ) ),
                                                    'start' => array( __('Starting', 'zero-bs-crm' ) ),
                                                    'end' => array( __('Finishing', 'zero-bs-crm' ) ),
                                                    'status' => array( __('Status', 'zero-bs-crm' ) ),
                                                    'assigned' => array( __('Assigned To',"zero-bs-crm" ) ),
                                                    'action' => array( __('Action', 'zero-bs-crm') )

												),


												'transaction' => array(

                                          
                                                    'id' => array('ID'),
                                                    'customer' => array(__('Customer',"zero-bs-crm")),
                                                    'status' => array(__('Status',"zero-bs-crm")),
                                                    'total' => array(__('Value',"zero-bs-crm")),
                                                    'item' => array(__('Item',"zero-bs-crm")),
                                                    'added' => array(__('Added',"zero-bs-crm")),
                                                    'editlink' => array(__('Edit Link',"zero-bs-crm"))

												),

												'transaction_filters' => array(
													'status_succeeded' => array('Succeeded'),
													'status_completed'	=> array('Completed'),
													'status_failed'	=> array('Failed'),
													'status_refunded'	=> array('Refunded')

												),



												'customer_filters' => array(

													'lead' => array( __( 'Lead', 'zero-bs-crm' ) ),
													'customer' => array( __( 'Customer', 'zero-bs-crm' ) ),
													'assigned_to_me' => array( __( 'Assigned to me', 'zero-bs-crm' ) ),

												),


												'quote_filters' => array(

											        'status_accepted' => array('Accepted'),
											        'status_notaccepted' => array('Not Accepted')
												),

												'invoice_filters' => array(

											        'status_draft'    => array( 'Draft' ),
											        'status_unpaid'   => array( 'Unpaid' ),
											        'status_paid'     => array( 'Paid' ),
											        'status_overdue'  => array( 'Overdue' ),
											        'status_deleted'  => array( 'Deleted' )

												),

												'segment' => array(

                                                    'id' => array('ID'),
                                                    'name' => array('Name'),
                                                    'audiencecount' => array('Contact Count'),
                                                    'action' => array('Action')

												),

												'form_filters' => array(
													
												),

												'event_filters' => array(

											        'status_incomplete' => array( 'Incomplete' ),
											        'status_completed' => array( 'Completed' ),
											        'next30' => array( 'Next 30 days' ),
											        'last30' => array( 'Past 30 days' ),
											        'next7' => array( 'Next 7 days' ),
											        'last7' => array( 'Past 7 days' )
												),

											),										
											#} ======= / Customer View Layout v2.0


											#} ======= Screenoptions (generic)
											'company_view_docs_columns' => array(
														'transactions' => array('date','id','total','status')
											),
											#} ======= / Screenoptions (generic)


											#} ======= List View Settings (Generic)
											'quickfiltersettings' => array(
														'notcontactedinx' => 30,
														'olderthanx' => 30 
											),
											#} ======= / List View Settings (Generic)

											#} ========== License Key (Generic) open for more info if needed (e.g sites)
											'licensingcount' => 0, // stores how many api_requests made 
											'licensingerror' => false, // stores any api_request errors hit
											'license_key' => array(
												'key' => '',
											),
											

											#} =========== / License Key (Generic)


			);




	
	#} Move defaults arr into main config
	$zeroBSCRM_Conf_Setup['conf_defaults'] = $zeroBSCRM_Conf_Def;
