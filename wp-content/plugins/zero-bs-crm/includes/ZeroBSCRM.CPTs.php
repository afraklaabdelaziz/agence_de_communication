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

/* ======================================================
  Breaking Checks ( stops direct access )
   ====================================================== */
    if ( ! defined( 'ZEROBSCRM_PATH' ) ) exit;
/* ======================================================
  / Breaking Checks
   ====================================================== */

#} These get "view" menu bar items removed, and front end redirected :) e.g. from http://demo.zbscrm.com/zerobs_invoice/auto-draft-15/ to http://demo.zbscrm.com
global $zbsCustomPostTypesToHide; $zbsCustomPostTypesToHide = array('zerobs_customer','zerobs_company','zerobs_invoice','zerobs_transaction','zerobs_form','zerobs_mailcampaign');

#} This is used for menu creation
global $zbsCustomPostTypes; $zbsCustomPostTypes = array('zerobs_customer','zerobs_company','zerobs_invoice','zerobs_quote','zerobs_transaction','zerobs_form','zerobs_mailcampaign'); # quote templates?


#} NOTE ON POSITIONS:
# menu_position
# https://codex.wordpress.org/Function_Reference/register_post_type
# http://wordpress.stackexchange.com/questions/8779/placing-a-custom-post-type-menu-above-the-posts-menu-using-menu-position
# ... this all needs a rethink

/* ======================================================
	Custom Post Types
   ====================================================== */

#} Setup Custom Post Types
function zeroBSCRM_setupPostTypes() {

	#} If in b2b mode, labels change
	$b2bMode = zeroBSCRM_getSetting('companylevelcustomers');

	$ZBSuseQuotes = zeroBSCRM_getSetting('feat_quotes');
	$ZBSuseInvoices = zeroBSCRM_getSetting('feat_invs');
	$ZBSuseForms = zeroBSCRM_getSetting('feat_forms');

	$ZBSuseTrans = zeroBSCRM_getSetting('feat_transactions');

	//Calendar Stuff
	$ZBSuseCalendar = zeroBSCRM_getSetting('feat_calendar');

	// 'zerobs_customer' only required pre v2.53+ (post migrated db)
	// ... as highlighted by this switch :)
	global $zbs;
	if ($zbs->dal_version == '1.0'){

		#} < v2.53+

		$labels = array(
			'name'                       => __( 'Contact Tags', 'Contact Tags', 'zero-bs-crm' ),
			'singular_name'              => __( 'Contact Tag', 'Customer Tag', 'zero-bs-crm' ),
			'menu_name'                  => __( 'Contact Tags', 'zero-bs-crm' ),
			'all_items'                  => __( 'All Tags', 'zero-bs-crm' ),
			'parent_item'                => __( 'Parent Tag', 'zero-bs-crm' ),
			'parent_item_colon'          => __( 'Parent Tag:', 'zero-bs-crm' ),
			'new_item_name'              => __( 'New Tag Name', 'zero-bs-crm' ),
			'add_new_item'               => __( 'Add Tag Item', 'zero-bs-crm' ),
			'edit_item'                  => __( 'Edit Tag', 'zero-bs-crm' ),
			'update_item'                => __( 'Tag Item', 'zero-bs-crm' ),
			'view_item'                  => __( 'View Tag', 'zero-bs-crm' ),
			'separate_items_with_commas' => __( 'Separate Tags with commas', 'zero-bs-crm' ),
			'add_or_remove_items'        => __( 'Add or remove Tags', 'zero-bs-crm' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'zero-bs-crm' ),
			'popular_items'              => __( 'Popular Tags', 'zero-bs-crm' ),
			'search_items'               => __( 'Search Tags', 'zero-bs-crm' ),
			'not_found'                  => __( 'Not Found', 'zero-bs-crm' ),
			'no_terms'                   => __( 'No Tags', 'zero-bs-crm' ),
			'items_list'                 => __( 'Tags list', 'zero-bs-crm' ),
			'items_list_navigation'      => __( 'Tags list navigation', 'zero-bs-crm' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
		    'capabilities' => array(
		      'manage_terms'=> 'manage_categories',
		      'edit_terms'=> 'manage_categories',
		      'delete_terms'=> 'manage_categories',
		      'assign_terms' => 'read'
		    )
		);
		register_taxonomy( 'zerobscrm_customertag', array( 'zerobscrm_customertag' ), $args );


		$labels = array(
			'name'                  => _x( 'Contacts', 'Contacts', 'zero-bs-crm' ),
			'singular_name'         => _x( 'Contact', 'Customer', 'zero-bs-crm' ),
			'menu_name'             => __( 'Contacts', 'zero-bs-crm' ),
			'name_admin_bar'        => __( 'Contact', 'zero-bs-crm' ),
			'archives'              => __( 'Contact Archives', 'zero-bs-crm' ),
			'parent_item_colon'     => jpcrm_label_company().':',
			'parent'    			=> jpcrm_label_company(),
			'all_items'             => __( 'All Contacts', 'zero-bs-crm' ),
			'add_new_item'          => __( 'Add New Contact', 'zero-bs-crm' ),
			'add_new'               => __( 'Add New', 'zero-bs-crm' ),
			'new_item'              => __( 'New Contact', 'zero-bs-crm' ),
			'edit_item'             => __( 'Edit Contact', 'zero-bs-crm' ),
			'update_item'           => __( 'Update Contact', 'zero-bs-crm' ),
			'view_item'             => __( 'View Contact', 'zero-bs-crm' ),
			'search_items'          => __( 'Search Contact', 'zero-bs-crm' ),
			'not_found'             => __( 'Not found', 'zero-bs-crm' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'zero-bs-crm' ),
			'featured_image'        => __( 'Contact Image', 'zero-bs-crm' ),
			'set_featured_image'    => __( 'Set Contact image', 'zero-bs-crm' ),
			'remove_featured_image' => __( 'Remove Contact image', 'zero-bs-crm' ),
			'use_featured_image'    => __( 'Use as Contact image', 'zero-bs-crm' ),
			'insert_into_item'      => __( 'Insert into Contact', 'zero-bs-crm' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Contact', 'zero-bs-crm' ),
			'items_list'            => __( 'Contacts list', 'zero-bs-crm' ),
			'items_list_navigation' => __( 'Contacts list navigation', 'zero-bs-crm' ),
			'filter_items_list'     => __( 'Filter Contacts list', 'zero-bs-crm' ),
		);
		$args = array(
			'label'                 => __( 'Contact', 'zero-bs-crm' ),
			'description'           => __( 'Zero-BS Contact', 'zero-bs-crm' ),
			'labels'                => $labels,
			'supports'              => array(  'thumbnail', 'taxonomies'), #, 'page-attributes'
			#'taxonomies'            => array( 'category', 'post_tag' ),
			'hierarchical'          => false, #zeroBSCRM_getSetting('companylevelcustomers'), #} Will be true if b2b on
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => "25.2",
			'menu_icon'             => 'dashicons-admin-users',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => false,
			'has_archive'           => false,		
			'exclude_from_search'   => true, #false, # Exclude from front end
			'publicly_queryable'    => false, #true, , # Exclude from front end
			'capability_type'       => 'page',
			'taxonomies' 			=> array('zerobscrm_customertag'),
		    'capabilities' => array(
  				'create_posts' => 'admin_zerobs_customers', 
		        'edit_post' => 'admin_zerobs_customers',
		        'edit_posts' => 'admin_zerobs_customers',
		        'edit_others_posts' => 'admin_zerobs_customers',
		        'publish_posts' => 'admin_zerobs_customers',
		        'read_post' => 'admin_zerobs_customers',
		        'read_private_posts' => 'admin_zerobs_customers',
		        'delete_post' => 'admin_zerobs_customers'
		    )
		);
		register_post_type( 'zerobs_customer', $args );


	}


	if ($b2bMode == 1){

		#} B2B MODE
		#} Additional cpt = Companies/organisations

		#} COMPANY 
		# (language switch)
		$companyOrOrg = zeroBSCRM_getSetting('coororg');
		$body = jpcrm_label_company(false);
		$bodyPlural = jpcrm_label_company(true);

		$labels = array(
			'name'                       => __( $body.' Tags', $body.' Tags', 'zero-bs-crm' ),
			'singular_name'              => __( $body.' Tag', $body.' Tag', 'zero-bs-crm' ),
			'menu_name'                  => __( $body.' Tags', 'zero-bs-crm' ),
			'all_items'                  => __( 'All Tags', 'zero-bs-crm' ),
			'parent_item'                => __( 'Parent Tag', 'zero-bs-crm' ),
			'parent_item_colon'          => __( 'Parent Tag:', 'zero-bs-crm' ),
			'new_item_name'              => __( 'New Tag Name', 'zero-bs-crm' ),
			'add_new_item'               => __( 'Add Tag Item', 'zero-bs-crm' ),
			'edit_item'                  => __( 'Edit Tag', 'zero-bs-crm' ),
			'update_item'                => __( 'Tag Item', 'zero-bs-crm' ),
			'view_item'                  => __( 'View Tag', 'zero-bs-crm' ),
			'separate_items_with_commas' => __( 'Separate Tags with commas', 'zero-bs-crm' ),
			'add_or_remove_items'        => __( 'Add or remove Tags', 'zero-bs-crm' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'zero-bs-crm' ),
			'popular_items'              => __( 'Popular Tags', 'zero-bs-crm' ),
			'search_items'               => __( 'Search Tags', 'zero-bs-crm' ),
			'not_found'                  => __( 'Not Found', 'zero-bs-crm' ),
			'no_terms'                   => __( 'No Tags', 'zero-bs-crm' ),
			'items_list'                 => __( 'Tags list', 'zero-bs-crm' ),
			'items_list_navigation'      => __( 'Tags list navigation', 'zero-bs-crm' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
		    'capabilities' => array(
		      'manage_terms'=> 'manage_categories',
		      'edit_terms'=> 'manage_categories',
		      'delete_terms'=> 'manage_categories',
		      'assign_terms' => 'read'
		    )
		);
		register_taxonomy( 'zerobscrm_companytag', array( 'zerobscrm_companytag' ), $args );


		$labels = array(
			'name'                  => __( $bodyPlural, 'zero-bs-crm' ),
			'singular_name'         => __( $body, 'zero-bs-crm' ),
			'menu_name'             => __( $bodyPlural, 'zero-bs-crm' ),
			'name_admin_bar'        => __( $body, 'zero-bs-crm' ),
			'archives'              => __( $body.' Archives', 'zero-bs-crm' ),
			'parent_item_colon'     => __( $body, 'zero-bs-crm' ),
			'parent'    			 => __( $body, 'zero-bs-crm' ),
			'all_items'             => __( 'All '.$bodyPlural, 'zero-bs-crm' ),
			'add_new_item'          => __( 'Add New '.$body.'', 'zero-bs-crm' ),
			'add_new'               => __( 'Add New', 'zero-bs-crm' ),
			'new_item'              => __( 'New '.$body.'', 'zero-bs-crm' ),
			'edit_item'             => __( 'Edit '.$body.'', 'zero-bs-crm' ),
			'update_item'           => __( 'Update '.$body.'', 'zero-bs-crm' ),
			'view_item'             => __( 'View '.$body.'', 'zero-bs-crm' ),
			'search_items'          => __( 'Search '.$body.'', 'zero-bs-crm' ),
			'not_found'             => __( 'Not found', 'zero-bs-crm' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'zero-bs-crm' ),
			'featured_image'        => __( $body.' Image', 'zero-bs-crm' ),
			'set_featured_image'    => __( 'Set '.$body.' image', 'zero-bs-crm' ),
			'remove_featured_image' => __( 'Remove '.$body.' image', 'zero-bs-crm' ),
			'use_featured_image'    => __( 'Use as '.$body.' image', 'zero-bs-crm' ),
			'insert_into_item'      => __( 'Insert into '.$body.'', 'zero-bs-crm' ),
			'uploaded_to_this_item' => __( 'Uploaded to this '.$body.'', 'zero-bs-crm' ),
			'items_list'            => __( $bodyPlural.' list', 'zero-bs-crm' ),
			'items_list_navigation' => __( $bodyPlural.' list navigation', 'zero-bs-crm' ),
			'filter_items_list'     => __( 'Filter '.$bodyPlural.' list', 'zero-bs-crm' ),
		);
		$args = array(
			'label'                 => __( $body, 'zero-bs-crm' ),
			'description'           => __( 'Zero-BS '.$body.'', 'zero-bs-crm' ),
			'labels'                => $labels,
			'supports'              => array(  'thumbnail', 'taxonomies'), #, 'page-attributes'
			'taxonomies'            => array( 'category', 'post_tag' ),
			'hierarchical'          => false, #zeroBSCRM_getSetting('companylevelcustomers'), #} Will be true if b2b on
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => "25.1",
			'menu_icon'             => 'dashicons-store',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => false,
			'has_archive'           => false,	
			'exclude_from_search'   => true, #false, # Exclude from front end
			'publicly_queryable'    => false, #true, , # Exclude from front end
			'capability_type'       => 'page',
			'taxonomies' 			=> array('zerobscrm_companytag'),
		    'capabilities' => array(
  				'create_posts' => 'admin_zerobs_customers', 
		        'edit_post' => 'admin_zerobs_customers',
		        'edit_posts' => 'admin_zerobs_customers',
		        'edit_others_posts' => 'admin_zerobs_customers',
		        'publish_posts' => 'admin_zerobs_customers',
		        'read_post' => 'admin_zerobs_customers',
		        'read_private_posts' => 'admin_zerobs_customers',
		        'delete_post' => 'admin_zerobs_customers'
		    )
		);
		register_post_type( 'zerobs_company', $args );





	}

	#} rest remain same

	if($ZBSuseCalendar == "1"){

		$labels = array(
			'name'                  => __( 'Task', 'Tasks', 'zero-bs-crm' ),
			'singular_name'         => __( 'Task', 'Task', 'zero-bs-crm' ),
			'menu_name'             => __( 'Task', 'zero-bs-crm' ),
			'name_admin_bar'        => __( 'Task', 'zero-bs-crm' ),
			'archives'              => __( 'Task Archives', 'zero-bs-crm' ),
			'parent_item_colon'     => jpcrm_label_company().':',
			'parent'    			=> jpcrm_label_company(),
			'all_items'             => __( 'All Tasks', 'zero-bs-crm' ),
			'add_new_item'          => __( 'Add New Task', 'zero-bs-crm' ),
			'add_new'               => __( 'Add New', 'zero-bs-crm' ),
			'new_item'              => __( 'New Task', 'zero-bs-crm' ),
			'edit_item'             => __( 'Edit Task', 'zero-bs-crm' ),
			'update_item'           => __( 'Update Task', 'zero-bs-crm' ),
			'view_item'             => __( 'View Task', 'zero-bs-crm' ),
			'search_items'          => __( 'Search Task', 'zero-bs-crm' ),
			'not_found'             => __( 'Not found', 'zero-bs-crm' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'zero-bs-crm' ),
			'featured_image'        => __( 'Task Image', 'zero-bs-crm' ),
			'set_featured_image'    => __( 'Set Task image', 'zero-bs-crm' ),
			'remove_featured_image' => __( 'Remove Task image', 'zero-bs-crm' ),
			'use_featured_image'    => __( 'Use as Task image', 'zero-bs-crm' ),
			'insert_into_item'      => __( 'Insert into Task', 'zero-bs-crm' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Task', 'zero-bs-crm' ),
			'items_list'            => __( 'Tasks list', 'zero-bs-crm' ),
			'items_list_navigation' => __( 'Tasks list navigation', 'zero-bs-crm' ),
			'filter_items_list'     => __( 'Filter Tasks list', 'zero-bs-crm' ),
		);
		$args = array(
			'label'                 => __( 'Task', 'zero-bs-crm' ),
			'description'           => __( 'Zero-BS Tasks', 'zero-bs-crm' ),
			'labels'                => $labels,
			'supports'              => array(), #, 'page-attributes'
			#'taxonomies'            => array( 'category', 'post_tag' ),
			'hierarchical'          => false, #zeroBSCRM_getSetting('companylevelcustomers'), #} Will be true if b2b on
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => "25.4",
			'menu_icon'             => 'dashicons-calendar-alt',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => false,
			'has_archive'           => false,		
			'exclude_from_search'   => true, #false, # Exclude from front end
			'publicly_queryable'    => false, #true, , # Exclude from front end
			'capability_type'       => 'page',
			'taxonomies' 			=> array(),
		    'capabilities' => array(
  				'create_posts' => 'admin_zerobs_events', 
		        'edit_post' => 'admin_zerobs_events',
		        'edit_posts' => 'admin_zerobs_events',
		        'edit_others_posts' => 'admin_zerobs_events',
		        'publish_posts' => 'admin_zerobs_events',
		        'read_post' => 'admin_zerobs_view_events',
		        'read_private_posts' => 'admin_zerobs_view_events',
		        'delete_post' => 'admin_zerobs_events'
		    )
		);
		register_post_type( 'zerobs_event', $args );


	}


	if($ZBSuseQuotes == "1"){

	    #} Using "Quote Builder" or not?
	    $useQuoteBuilder = zeroBSCRM_getSetting('usequotebuilder');

		$labels = array(
			'name'                  => __( 'Quotes', 'Quotes', 'zero-bs-crm' ),
			'singular_name'         => __( 'Quote', 'Quote', 'zero-bs-crm' ),
			'menu_name'             => __( 'Quotes', 'zero-bs-crm' ),
			'name_admin_bar'        => __( 'Quote', 'zero-bs-crm' ),
			'archives'              => __( 'Quote Archives', 'zero-bs-crm' ),
			'parent_item_colon'     => __( 'Parent Quote:', 'zero-bs-crm' ),
			'all_items'             => __( 'All Quotes', 'zero-bs-crm' ),
			'add_new_item'          => __( 'Add New Quote', 'zero-bs-crm' ),
			'add_new'               => __( 'Add New', 'zero-bs-crm' ),
			'new_item'              => __( 'New Quote', 'zero-bs-crm' ),
			'edit_item'             => __( 'Edit Quote', 'zero-bs-crm' ),
			'update_item'           => __( 'Update Quote', 'zero-bs-crm' ),
			'view_item'             => __( 'View Quote', 'zero-bs-crm' ),
			'search_items'          => __( 'Search Quote', 'zero-bs-crm' ),
			'not_found'             => __( 'Not found', 'zero-bs-crm' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'zero-bs-crm' ),
			'featured_image'        => __( 'Quote Image', 'zero-bs-crm' ),
			'set_featured_image'    => __( 'Set Quote image', 'zero-bs-crm' ),
			'remove_featured_image' => __( 'Remove Quote image', 'zero-bs-crm' ),
			'use_featured_image'    => __( 'Use as Quote image', 'zero-bs-crm' ),
			'insert_into_item'      => __( 'Insert into Quote', 'zero-bs-crm' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Quote', 'zero-bs-crm' ),
			'items_list'            => __( 'Quotes list', 'zero-bs-crm' ),
			'items_list_navigation' => __( 'Quotes list navigation', 'zero-bs-crm' ),
			'filter_items_list'     => __( 'Filter Quotes list', 'zero-bs-crm' ),
		);
		$args = array(
			'label'                 => __( 'Quote', 'zero-bs-crm' ),
			'description'           => __( 'Zero-BS Quote', 'zero-bs-crm' ),
			'labels'                => $labels,
			'supports'              => array( 'taxonomies' ), # added below 'taxonomies','editor','title'
			'taxonomies'            => array( ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => "25.3",
			'menu_icon'             => 'dashicons-clipboard',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => false,
			'has_archive'           => false,		
			'exclude_from_search'   => true, #false, # Exclude from front end
			'publicly_queryable'    => false, #true, , # Exclude from front end
			'capability_type'       => 'page',
			//'taxonomies' 			=> array('zerobscrm_customertag')		
		    'capabilities' => array(
  				'create_posts' => 'admin_zerobs_quotes', 
		        'edit_post' => 'admin_zerobs_quotes',
		        'edit_posts' => 'admin_zerobs_quotes',
		        'edit_others_posts' => 'admin_zerobs_quotes',
		        'publish_posts' => 'admin_zerobs_quotes',
		        'read_post' => 'admin_zerobs_view_quotes',
		        'read_private_posts' => 'admin_zerobs_quotes',
		        'delete_post' => 'admin_zerobs_quotes'
		    ),

		);

		#} Quote Builder wants these!
		/* - in the end I opted for custom boxes, which give us more control of the UI order.
		if ($useQuoteBuilder) {
			$args['supports'][] = 'title';
			$args['supports'][] = 'editor';
			#'taxonomies' ?
		}
		*/
		 
		
		if ($useQuoteBuilder == "1") {


			#} have to allow this
			//$args['publicly_queryable'] = true;

			#} used for exposing quotes:
			/* WH removed 09/7/18 - conflicted with new CP setup
			$args['rewrite'] = array(
					'slug' => 'proposal',
					'with_front' => true
					);
					*/


		}
				

		register_post_type( 'zerobs_quote', $args );


		#} Further... if using quote builder, we add a cpt for templates:
		if ($useQuoteBuilder == "1") {

			$labels = array(
				'name'                  => __( 'Quote Templates', 'Quote Templates', 'zero-bs-crm' ),
				'singular_name'         => __( 'Quote Template', 'Quote Template', 'zero-bs-crm' ),
				'menu_name'             => __( 'Quote Templates', 'zero-bs-crm' ),
				'name_admin_bar'        => __( 'Quote Template', 'zero-bs-crm' ),
				'archives'              => __( 'Quote Template Archives', 'zero-bs-crm' ),
				'parent_item_colon'     => __( 'Parent Quote Template:', 'zero-bs-crm' ),
				'all_items'             => __( 'All Quote Templates', 'zero-bs-crm' ),
				'add_new_item'          => __( 'Add New Quote Template', 'zero-bs-crm' ),
				'add_new'               => __( 'Add New', 'zero-bs-crm' ),
				'new_item'              => __( 'New Quote Template', 'zero-bs-crm' ),
				'edit_item'             => __( 'Edit Quote Template', 'zero-bs-crm' ),
				'update_item'           => __( 'Update Quote Template', 'zero-bs-crm' ),
				'view_item'             => __( 'View Quote Template', 'zero-bs-crm' ),
				'search_items'          => __( 'Search Quote Template', 'zero-bs-crm' ),
				'not_found'             => __( 'Not found', 'zero-bs-crm' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'zero-bs-crm' ),
				'featured_image'        => __( 'Quote Template Image', 'zero-bs-crm' ),
				'set_featured_image'    => __( 'Set Quote Template image', 'zero-bs-crm' ),
				'remove_featured_image' => __( 'Remove Quote Template image', 'zero-bs-crm' ),
				'use_featured_image'    => __( 'Use as Quote Template image', 'zero-bs-crm' ),
				'insert_into_item'      => __( 'Insert into Quote Template', 'zero-bs-crm' ),
				'uploaded_to_this_item' => __( 'Uploaded to this Quote Template', 'zero-bs-crm' ),
				'items_list'            => __( 'Quote Templates list', 'zero-bs-crm' ),
				'items_list_navigation' => __( 'Quote Templates list navigation', 'zero-bs-crm' ),
				'filter_items_list'     => __( 'Filter Quote Templates list', 'zero-bs-crm' ),
			);
			$args = array(
				'label'                 => __( 'Quote Template', 'zero-bs-crm' ),
				'description'           => __( 'Zero-BS Quote Template', 'zero-bs-crm' ),
				'labels'                => $labels,
				'supports'              => array('taxonomies','editor','title'),
				'taxonomies'            => array( ),
				'hierarchical'          => false,
				# HIDDEN, only exposed where we want it... 
				
				'public'                => false,
				'show_ui'               => true,
				'show_in_menu'          => true, // THIS MUST BE SET, then removed by  #REMOVEQUOTEMPLATE in menus file, for add-new to work for all quote users
				'menu_position'         => "25.3",
				'menu_icon'             => 'dashicons-clipboard',
				'show_in_admin_bar'     => false,
				'show_in_nav_menus'     => false,
				'can_export'            => false,
				'has_archive'           => false,		
				'exclude_from_search'   => true, #false, # Exclude from front end
				'publicly_queryable'    => false, #true, , # Exclude from front end
	
				'capability_type'       => 'page',
				//'taxonomies' 			=> array('zerobscrm_customertag')		
			    'capabilities' => array(
  					'create_posts' => 'admin_zerobs_quotes', 
			        'edit_post' => 'admin_zerobs_quotes',
			        'edit_posts' => 'admin_zerobs_quotes',
			        'edit_others_posts' => 'admin_zerobs_quotes',
			        'publish_posts' => 'admin_zerobs_quotes',
			        'read_post' => 'admin_zerobs_quotes',
			        'read_private_posts' => 'admin_zerobs_quotes',
			        'delete_post' => 'admin_zerobs_quotes'
			    )
			);

			register_post_type( 'zerobs_quo_template', $args );

			#} Note, example templates are installed via: zeroBSCRM_installDefaultContent below

		}

	}
	
	if($ZBSuseInvoices == "1"){

		$labels = array(
			'name'                  => __( 'Invoices', 'Invoices', 'zero-bs-crm' ),
			'singular_name'         => __( 'Invoice', 'Invoice', 'zero-bs-crm' ),
			'menu_name'             => __( 'Invoices', 'zero-bs-crm' ),
			'name_admin_bar'        => __( 'Invoice', 'zero-bs-crm' ),
			'archives'              => __( 'Invoice Archives', 'zero-bs-crm' ),
			'parent_item_colon'     => __( 'Parent Invoice:', 'zero-bs-crm' ),
			'all_items'             => __( 'All Invoices', 'zero-bs-crm' ),
			'add_new_item'          => __( 'Add New Invoice', 'zero-bs-crm' ),
			'add_new'               => __( 'Add New', 'zero-bs-crm' ),
			'new_item'              => __( 'New Invoice', 'zero-bs-crm' ),
			'edit_item'             => __( 'Edit Invoice', 'zero-bs-crm' ),
			'update_item'           => __( 'Update Invoice', 'zero-bs-crm' ),
			'view_item'             => __( 'View Invoice', 'zero-bs-crm' ),
			'search_items'          => __( 'Search Invoice', 'zero-bs-crm' ),
			'not_found'             => __( 'Not found', 'zero-bs-crm' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'zero-bs-crm' ),
			'featured_image'        => __( 'Invoice Image', 'zero-bs-crm' ),
			'set_featured_image'    => __( 'Set Invoice image', 'zero-bs-crm' ),
			'remove_featured_image' => __( 'Remove Invoice image', 'zero-bs-crm' ),
			'use_featured_image'    => __( 'Use as Invoice image', 'zero-bs-crm' ),
			'insert_into_item'      => __( 'Insert into Invoice', 'zero-bs-crm' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Invoice', 'zero-bs-crm' ),
			'items_list'            => __( 'Invoices list', 'zero-bs-crm' ),
			'items_list_navigation' => __( 'Invoices list navigation', 'zero-bs-crm' ),
			'filter_items_list'     => __( 'Filter Invoices list', 'zero-bs-crm' ),
		);

/*

$args = array(
			'label'                 => __( 'Customer', 'zero-bs-crm' ),
			'description'           => __( 'Zero-BS Customer', 'zero-bs-crm' ),
			'labels'                => $labels,
			'supports'              => array(  'thumbnail', 'taxonomies'), #, 'page-attributes'
			#'taxonomies'            => array( 'category', 'post_tag' ),
			'hierarchical'          => false, #zeroBSCRM_getSetting('companylevelcustomers'), #} Will be true if b2b on
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => "25.2",
			'menu_icon'             => 'dashicons-admin-users',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => false,
			'has_archive'           => false,		
			'exclude_from_search'   => true, #false, # Exclude from front end
			'publicly_queryable'    => false, #true, , # Exclude from front end
			'capability_type'       => 'page',
			'taxonomies' 			=> array('zerobscrm_customertag'),
		    'capabilities' => array(
		        'edit_post' => 'admin_zerobs_customers',
		        'edit_posts' => 'admin_zerobs_customers',
		        'edit_others_posts' => 'admin_zerobs_customers',
		        'publish_posts' => 'admin_zerobs_customers',
		        'read_post' => 'admin_zerobs_customers',
		        'read_private_posts' => 'admin_zerobs_customers',
		        'delete_post' => 'admin_zerobs_customers'
		    )
		);
		register_post_type( 'zerobs_customer', $args );

		*/



		$args = array(


			'label'                 => __( 'Invoice', 'zero-bs-crm' ),
			'description'           => __( 'Zero-BS Invoice', 'zero-bs-crm' ),
			'labels'                => $labels,
			'supports'              => array(   'taxonomies'),
			'taxonomies'            => array( ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => "25.4",
			'menu_icon'             => 'dashicons-media-text',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => false,
			'has_archive'           => false,		
			'exclude_from_search'   => true, #false, # Exclude from front end
			'publicly_queryable'    => false, #true, , # Exclude from front end
			'capability_type'       => 'page',
			//'taxonomies' 			=> array('zerobscrm_customertag')		
		    'capabilities' => array(
  				'create_posts' => 'admin_zerobs_invoices', 
		        'edit_post' => 'admin_zerobs_invoices',
		        'edit_posts' => 'admin_zerobs_invoices',
		        'edit_others_posts' => 'admin_zerobs_invoices',
		        'publish_posts' => 'admin_zerobs_invoices',
		        'read_post' => 'admin_zerobs_view_invoices',
		        'read_private_posts' => 'admin_zerobs_invoices',
		        'delete_post' => 'admin_zerobs_invoices',
		    )
		);
		register_post_type( 'zerobs_invoice', $args );

	}


	if($ZBSuseTrans == "1"){
	

	#} Transaction Tags
		$labels = array(
			'name'                       => __( 'Transaction Tags', 'Transaction Tags', 'zero-bs-crm' ),
			'singular_name'              => __( 'Transaction Tag', 'Transaction Tag', 'zero-bs-crm' ),
			'menu_name'                  => __( 'Transaction Tags', 'zero-bs-crm' ),
			'all_items'                  => __( 'All Tags', 'zero-bs-crm' ),
			'parent_item'                => __( 'Parent Tag', 'zero-bs-crm' ),
			'parent_item_colon'          => __( 'Parent Tag:', 'zero-bs-crm' ),
			'new_item_name'              => __( 'New Tag Name', 'zero-bs-crm' ),
			'add_new_item'               => __( 'Add Tag Item', 'zero-bs-crm' ),
			'edit_item'                  => __( 'Edit Tag', 'zero-bs-crm' ),
			'update_item'                => __( 'Tag Item', 'zero-bs-crm' ),
			'view_item'                  => __( 'View Tag', 'zero-bs-crm' ),
			'separate_items_with_commas' => __( 'Separate Tags with commas', 'zero-bs-crm' ),
			'add_or_remove_items'        => __( 'Add or remove Tags', 'zero-bs-crm' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'zero-bs-crm' ),
			'popular_items'              => __( 'Popular Tags', 'zero-bs-crm' ),
			'search_items'               => __( 'Search Tags', 'zero-bs-crm' ),
			'not_found'                  => __( 'Not Found', 'zero-bs-crm' ),
			'no_terms'                   => __( 'No Tags', 'zero-bs-crm' ),
			'items_list'                 => __( 'Tags list', 'zero-bs-crm' ),
			'items_list_navigation'      => __( 'Tags list navigation', 'zero-bs-crm' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
		    'capabilities' => array(
		      'manage_terms'=> 'manage_categories',
		      'edit_terms'=> 'manage_categories',
		      'delete_terms'=> 'manage_categories',
		      'assign_terms' => 'read'
		    )
		);
		register_taxonomy( 'zerobscrm_transactiontag', array( 'zerobscrm_transactiontag' ), $args );

	#} Order information custom post type
	$labels = array(
		'name'                  => __( 'Transactions', 'Transactions', 'zero-bs-crm' ),
		'singular_name'         => __( 'Transactions', 'Transaction', 'zero-bs-crm' ),
		'menu_name'             => __( 'Transactions', 'zero-bs-crm' ),
		'name_admin_bar'        => __( 'Transaction', 'zero-bs-crm' ),
		'archives'              => __( 'Transactions Archives', 'zero-bs-crm' ),
		'parent_item_colon'     => __( 'Parent Transaction:', 'zero-bs-crm' ),
		'all_items'             => __( 'All Transactions', 'zero-bs-crm' ),
		'add_new_item'          => __( 'Add New Transaction', 'zero-bs-crm' ),
		'add_new'               => __( 'Add New', 'zero-bs-crm' ),
		'new_item'              => __( 'New Transaaction', 'zero-bs-crm' ),
		'edit_item'             => __( 'Edit Transaction', 'zero-bs-crm' ),
		'update_item'           => __( 'Update Transaction', 'zero-bs-crm' ),
		'view_item'             => __( 'View Transaction', 'zero-bs-crm' ),
		'search_items'          => __( 'Search Transactions', 'zero-bs-crm' ),
		'not_found'             => __( 'Not found', 'zero-bs-crm' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'zero-bs-crm' )
	);
	$args = array(
		'label'                 => __( 'Transactions', 'zero-bs-crm' ),
		'description'           => __( 'Zero-BS Transactions', 'zero-bs-crm' ),
		'labels'                => $labels,
		'supports'              => array('taxonomies'), #'title', 'thumbnail',
		'taxonomies' 			=> array('zerobscrm_transactiontag'),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => "25.5",
		'menu_icon'             => 'dashicons-cart',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => false,
		'has_archive'           => false,		
		'exclude_from_search'   => true, #false, # Exclude from front end
		'publicly_queryable'    => false, #true, , # Exclude from front end
		'capability_type'       => 'post',
		
		    'capabilities' => array(
  				'create_posts' => 'admin_zerobs_transactions', 
		        'edit_post' => 'admin_zerobs_transactions',
		        'edit_posts' => 'admin_zerobs_transactions',
		        'edit_others_posts' => 'admin_zerobs_transactions',
		        'publish_posts' => 'admin_zerobs_transactions',
		        'read_post' => 'admin_zerobs_view_transactions',
		        'read_private_posts' => 'admin_zerobs_transactions',
		        'delete_post' => 'admin_zerobs_transactions',
		    )
	);
	register_post_type( 'zerobs_transaction', $args );
	#} Default Terms?

	}


	if($ZBSuseForms == "1"){
	#} ======================================================================
	#} ===================== Custom Post Type: Forms ========================
	#} ======================================================================

		$labels = array(
			'name'                  => __( 'Forms', 'Forms', 'zero-bs-crm' ),
			'singular_name'         => __( 'Form', 'Form', 'zero-bs-crm' ),
			'menu_name'             => __( 'Forms', 'zero-bs-crm' ),
			'name_admin_bar'        => __( 'Form', 'zero-bs-crm' ),
			'archives'              => __( 'Form Archives', 'zero-bs-crm' ),
			'all_items'             => __( 'All Forms', 'zero-bs-crm' ),
			'add_new_item'          => __( 'Add New Form', 'zero-bs-crm' ),
			'add_new'               => __( 'Add New', 'zero-bs-crm' ),
			'new_item'              => __( 'New Form', 'zero-bs-crm' ),
			'edit_item'             => __( 'Edit Form', 'zero-bs-crm' ),
			'update_item'           => __( 'Update Form', 'zero-bs-crm' ),
			'view_item'             => __( 'View Form', 'zero-bs-crm' ),
			'search_items'          => __( 'Search Form', 'zero-bs-crm' ),
			'not_found'             => __( 'Not found', 'zero-bs-crm' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'zero-bs-crm' ),
			'featured_image'        => __( 'Form Image', 'zero-bs-crm' ),
			'set_featured_image'    => __( 'Set Form image', 'zero-bs-crm' ),
			'remove_featured_image' => __( 'Remove Form image', 'zero-bs-crm' ),
			'use_featured_image'    => __( 'Use as Form image', 'zero-bs-crm' ),
			'insert_into_item'      => __( 'Insert into Form', 'zero-bs-crm' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Form', 'zero-bs-crm' ),
			'items_list'            => __( 'Forms list', 'zero-bs-crm' ),
			'items_list_navigation' => __( 'Forms list navigation', 'zero-bs-crm' ),
			'filter_items_list'     => __( 'Filter Forms list', 'zero-bs-crm' ),
		);
		$args = array(
			'label'                 => __( 'Form', 'zero-bs-crm' ),
			'description'           => __( 'Zero-BS Form', 'zero-bs-crm' ),
			'labels'                => $labels,
			'supports'              => array('title'), #, 'page-attributes'
			'taxonomies'            => array(),
			'hierarchical'          => false, #zeroBSCRM_getSetting('companylevelforms'), #} Will be true if b2b on
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => "25.9", #Above Appearance :) https://developer.wordpress.org/reference/functions/add_menu_page/#menu-structure
			'menu_icon'             => 'dashicons-welcome-widgets-menus',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => false,
			'has_archive'           => false,		
			'exclude_from_search'   => true, #false, # Exclude from front end
			'publicly_queryable'    => false, #true, , # Exclude from front end
			'capability_type'       => 'page',
			'taxonomies' 			=> array('zerobscrm_formtag'),	
		    'capabilities' => array(
  				'create_posts' => 'admin_zerobs_forms', 
		        'edit_post' => 'admin_zerobs_forms',
		        'edit_posts' => 'admin_zerobs_forms',
		        'edit_others_posts' => 'admin_zerobs_forms',
		        'publish_posts' => 'admin_zerobs_forms',
		        'read_post' => 'admin_zerobs_forms',
		        'read_private_posts' => 'admin_zerobs_forms',
		        'delete_post' => 'admin_zerobs_forms',
		    )
		);
		register_post_type( 'zerobs_form', $args );
	}


	#} ======================================================================
	#} ===================== Custom Post Type: Logs =========================
	#} ======================================================================

		$labels = array(
			'name'                       => __( 'Log Tags', 'Log Tags', 'zero-bs-crm' ),
			'singular_name'              => __( 'Log Tag', 'Log Tag', 'zero-bs-crm' ),
			'menu_name'                  => __( 'Log Tags', 'zero-bs-crm' ),
			'all_items'                  => __( 'All Tags', 'zero-bs-crm' ),
			'parent_item'                => __( 'Parent Tag', 'zero-bs-crm' ),
			'parent_item_colon'          => __( 'Parent Tag:', 'zero-bs-crm' ),
			'new_item_name'              => __( 'New Tag Name', 'zero-bs-crm' ),
			'add_new_item'               => __( 'Add Tag Item', 'zero-bs-crm' ),
			'edit_item'                  => __( 'Edit Tag', 'zero-bs-crm' ),
			'update_item'                => __( 'Tag Item', 'zero-bs-crm' ),
			'view_item'                  => __( 'View Tag', 'zero-bs-crm' ),
			'separate_items_with_commas' => __( 'Separate Tags with commas', 'zero-bs-crm' ),
			'add_or_remove_items'        => __( 'Add or remove Tags', 'zero-bs-crm' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'zero-bs-crm' ),
			'popular_items'              => __( 'Popular Tags', 'zero-bs-crm' ),
			'search_items'               => __( 'Search Tags', 'zero-bs-crm' ),
			'not_found'                  => __( 'Not Found', 'zero-bs-crm' ),
			'no_terms'                   => __( 'No Tags', 'zero-bs-crm' ),
			'items_list'                 => __( 'Tags list', 'zero-bs-crm' ),
			'items_list_navigation'      => __( 'Tags list navigation', 'zero-bs-crm' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
		    'capabilities' => array(
		      'manage_terms'=> 'manage_categories',
		      'edit_terms'=> 'manage_categories',
		      'delete_terms'=> 'manage_categories',
		      'assign_terms' => 'read'
		    )
		);
		register_taxonomy( 'zerobscrm_logtag', array( 'zerobscrm_logtag' ), $args );


		$labels = array(
			'name'                  => __( 'Logs', 'Logs', 'zero-bs-crm' ),
			'singular_name'         => __( 'Log', 'Log', 'zero-bs-crm' ),
			'menu_name'             => __( 'Logs', 'zero-bs-crm' ),
			'name_admin_bar'        => __( 'Log', 'zero-bs-crm' ),
			'archives'              => __( 'Log Archives', 'zero-bs-crm' ),
			'all_items'             => __( 'All Logs', 'zero-bs-crm' ),
			'add_new_item'          => __( 'Add New Log', 'zero-bs-crm' ),
			'add_new'               => __( 'Add New', 'zero-bs-crm' ),
			'new_item'              => __( 'New Log', 'zero-bs-crm' ),
			'edit_item'             => __( 'Edit Log', 'zero-bs-crm' ),
			'update_item'           => __( 'Update Log', 'zero-bs-crm' ),
			'view_item'             => __( 'View Log', 'zero-bs-crm' ),
			'search_items'          => __( 'Search Log', 'zero-bs-crm' ),
			'not_found'             => __( 'Not found', 'zero-bs-crm' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'zero-bs-crm' ),
			'featured_image'        => __( 'Log Image', 'zero-bs-crm' ),
			'set_featured_image'    => __( 'Set Log image', 'zero-bs-crm' ),
			'remove_featured_image' => __( 'Remove Log image', 'zero-bs-crm' ),
			'use_featured_image'    => __( 'Use as Log image', 'zero-bs-crm' ),
			'insert_into_item'      => __( 'Insert into Log', 'zero-bs-crm' ),
			'uploaded_to_this_item' => __( 'Uploaded to this Log', 'zero-bs-crm' ),
			'items_list'            => __( 'Logs list', 'zero-bs-crm' ),
			'items_list_navigation' => __( 'Logs list navigation', 'zero-bs-crm' ),
			'filter_items_list'     => __( 'Filter Logs list', 'zero-bs-crm' ),
		);
		$args = array(
			'label'                 => __( 'Log', 'zero-bs-crm' ),
			'description'           => __( 'Zero-BS Log', 'zero-bs-crm' ),
			'labels'                => $labels,
			'supports'              => array(  'thumbnail', 'taxonomies'), #, 'page-attributes'
			'taxonomies'            => array( 'category', 'post_tag' ),
			'hierarchical'          => false, #zeroBSCRM_getSetting('companylevelcustomers'), #} Will be true if b2b on
			'public'                => true,
			'show_ui'               => false,
			'show_in_menu'          => false,
			'menu_position'         => "25.9",
			'menu_icon'             => 'dashicons-admin-users',
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => false,
			'has_archive'           => false,		
			'exclude_from_search'   => true, #false, # Exclude from front end
			'publicly_queryable'    => false, #true, , # Exclude from front end
			'capability_type'       => 'post',
			'taxonomies' 			=> array('zerobscrm_logtag'),
		    'capabilities' => array(
  				'create_posts' => 'admin_zerobs_customers', 
		        'edit_post' => 'admin_zerobs_customers',
		        'edit_posts' => 'admin_zerobs_customers',
		        'edit_others_posts' => 'admin_zerobs_customers',
		        'publish_posts' => 'admin_zerobs_customers',
		        'read_post' => 'admin_zerobs_customers',
		        'read_private_posts' => 'admin_zerobs_customers',
		        'delete_post' => 'admin_zerobs_customers'
		    )
		);
		register_post_type( 'zerobs_log', $args );

		// Flush any permalinks after CPT's, if req.
		// Not req, now we've removed /proposal/ (2.89) zeroBSCRM_flushPermalinksIfReq();

}
/* ======================================================
	/ Custom Post Types
   ====================================================== */


/* ======================================================
	Custom Post Type Action Overrides
   ====================================================== */

	#http://wordpress.stackexchange.com/questions/10678/function-to-execute-when-a-post-is-moved-to-trash
	#} Overrides default "delete transaction" after-case, allowing redirect
	add_action('trash_zerobs_customer','zbsCRM_trash_customer',1,1);
	function zbsCRM_trash_customer($post_id){
	    if(!did_action('trash_post')){
	        
	        #} Redirect to our "just trashed" page
	        header("Location: ".admin_url("admin.php?page=zbs-deletion&cid=".$post_id));
	        exit();

			//$loc = 'edit.php?post_type=zerobs_customer';
			//wp_safe_redirect( $loc );
			//exit();

	    } else {


	    }
	}

	#} Overrides default "delete quote" after-case, allowing redirect
	add_action('trash_zerobs_quote','zbsCRM_trash_quote',1,1);
	function zbsCRM_trash_quote($post_id){
	    if(!did_action('trash_post')){
	        
	        #} Redirect to our "just trashed" page
	        header("Location: ".admin_url("admin.php?page=zbs-deletion&qid=".$post_id));
	        exit();

	    }
	}

	#} Overrides default "delete quote template" after-case, allowing redirect
	add_action('trash_zerobs_quo_template','zbsCRM_trash_quotetemplate',1,1);
	function zbsCRM_trash_quotetemplate($post_id){
	    if(!did_action('trash_post')){
	        
	        #} Redirect to our "just trashed" page
	        header("Location: ".admin_url("admin.php?page=zbs-deletion&qtid=".$post_id));
	        exit();

	    }
	}
	#} Overrides default "delete invoice" after-case, allowing redirect
	add_action('trash_zerobs_invoice','zbsCRM_trash_invoice',1,1);
	function zbsCRM_trash_invoice($post_id){
	    if(!did_action('trash_post')){
	        
	        #} Redirect to our "just trashed" page
	        header("Location: ".admin_url("admin.php?page=zbs-deletion&iid=".$post_id));
	        exit();

	    }
	}
	#} Overrides default "delete transaction" after-case, allowing redirect
	add_action('trash_zerobs_transaction','zbsCRM_trash_transaction',1,1);
	function zbsCRM_trash_transaction($post_id){
	    if(!did_action('trash_post')){
	        
	        #} Redirect to our "just trashed" page
	        header("Location: ".admin_url("admin.php?page=zbs-deletion&tid=".$post_id));
	        exit();

	    }
	}
	#} Overrides default "delete event" after-case, allowing redirect
	add_action('trash_zerobs_event','zbsCRM_trash_event',1,1);
	function zbsCRM_trash_event($post_id){
	    if(!did_action('trash_post')){
	        
	        #} Redirect to our "just trashed" page
	        header("Location: ".admin_url("admin.php?page=zbs-deletion&eid=".$post_id));
	        exit();

	    }
	}

/* ======================================================
	/ Custom Post Type Action Overrides
   ====================================================== */





/* ======================================================
	Custom Post Type Menu Overrides
   ====================================================== */


#} Remove "View" link for customer etc.
#} From : http://wordpress.stackexchange.com/questions/100756/wp-before-admin-bar-render-action-not-working-in-back-office
function zeroBSCRM_removeViewLinksAdminBar() {
    global $wp_admin_bar,$post,$zbsCustomPostTypesToHide;
   # if (is_user_logged_in()) {
        #if ( !is_admin()) {
          //$wp_admin_bar->remove_menu('user-info');    
          #$wp_admin_bar->remove_menu('comments');
          //$wp_admin_bar->remove_menu('site-name');
          //$wp_admin_bar->remove_menu('dashboard');
          /*$wp_admin_bar->add_menu( array(
            'id' => 'custom-account',
            'parent' => 'my-account',
            'title' => __( 'Mi cuenta', 'zero-bs-crm'),
            'href' => '/pedidos/products-page/your-account/'
            ) );*/
       # }
    #}

	if (
		#} post type:
		(isset($post) && in_array($post->post_type,$zbsCustomPostTypesToHide))
		||
		#} get
		(isset($_GET['post_type']) && in_array($_GET['post_type'],$zbsCustomPostTypesToHide))
		){
	    	
	    	#} Remove View link
	    	$wp_admin_bar->remove_menu('view');

	    	#} Remove comments
	    	$wp_admin_bar->remove_menu('comments');

		}
}
add_action('wp_before_admin_bar_render', 'zeroBSCRM_removeViewLinksAdminBar');



/*

	# ALL of the following is concerned with re-arranging the WP menu. This was a nightmare, so I've left for now. Menu's need rewriting cleanly, this can be part of that
	# WH 1.2

# http://wordpress.stackexchange.com/questions/2666/add-a-separator-to-the-admin-menu
function add_admin_menu_separator($position) {
  global $menu;
  $index = 0;
  foreach($menu as $offset => $section) {
    if (substr($section[2],0,9)=='separator')
      $index++;
    if ($offset>=$position) {
      $menu[$position] = array('','read',"separator{$index}",'','wp-menu-separator');
      break;
    }
  }
  ksort( $menu );
}*/


/* For debug!
add_action('admin_init','dump_admin_menu');
function dump_admin_menu() {
  if (is_admin()) {
    header('Content-Type:text/plain');
    var_dump($GLOBALS['menu']);
    exit;
  }
} */

/*
function zeroBSCRM_addSeperatorMenu(){

	#} find contacts + jam this above it
	$toJam = -1; if (count($GLOBALS['menu']) > 0) foreach ($GLOBALS['menu'] as $ind => $menuItem){ if ($menuItem[2] == "edit.php?post_type=zerobs_customer") $toJam = $ind; }

	#if (!empty($toJam)) add_admin_menu_separator($toJam);

}


function zeroBSCRM_sortCPTMenu(){

	global $zbsCustomPostTypes,$menu;

	$ourPages = array('sales-dash');
	foreach ($zbsCustomPostTypes as $cpt) $ourPages[] = 'edit.php?post_type='.$cpt;

	#} Cycle through proposed menu, and drag all our stuff to top :)
	$newMenu = array(); $dashItem = array(); $otherItems = array(); $crmItems = array(); $postCRMItems = array();
	foreach($menu as $offset => $section) {

		if (in_array($section[2],$ourPages)){

			$crmItems[] = $section;

		} else if ($section[2] == 'zerobscrm-dash'){

			$dashItem = $section;

		} else {
			#if (count($newMenu) == 0) 
			#	$preCRMItems[] = $section;
			#else
			#	$postCRMItems[] = $section;
			$otherItems[] = $section;
		}

	}

	#print_r(array($newMenu,$preCRMItems,$crmItems,$postCRMItems)); exit();
	#print_r(array($dashItem,$crmItems,$otherItems)); exit();

	#} Meld
	#$newMenu = $newMenu + $crmItems;
	#$newMenu = $newMenu + $postCRMItems;
	$newMenu = array_slice($otherItems,0,2) + array($dashItem) + $crmItems + array_slice($otherItems,2);

	print_r($newMenu); exit();

	$menu = $newMenu;
	
	#ksort( $menu );


}
*/

/* ======================================================
	/ Custom Post Type Menu Overrides
   ====================================================== */






/* ======================================================
	Custom Post Type - Front End Kill requests
   ====================================================== */

#} Adapted from From http://wordpress.stackexchange.com/questions/74468/how-to-set-a-custom-post-type-to-not-show-up-on-the-front-end
function zeroBSCRM_redirectCPTS() {

    global $wp_query,$post,$zbsCustomPostTypesToHide;

    if (isset($post) && in_array($post->post_type,$zbsCustomPostTypesToHide)){

    	#} unless is quotebuilder + exposed :)
    	if ($post->post_type == 'zerobs_quote'){

		    #} Using "Quote Builder" or not?
		    $useQuoteBuilder = zeroBSCRM_getSetting('usequotebuilder');

		    if ($useQuoteBuilder == "1") $skipRules = true;

		}

    	#} redir the hell away
	    if (!isset($skipRules) && (is_archive($post->post_type) || is_singular($post->post_type))){

	        $url   = get_bloginfo('url');
	        wp_redirect( esc_url_raw( $url ), 301 );
	        exit();

	    }

	}
}

add_action ( 'template_redirect', 'zeroBSCRM_redirectCPTS', 1);


/* ======================================================
	/Custom Post Type - Front End Kill requests
   ====================================================== */






/* ======================================================
	Install Default Content (if not installed)
   ====================================================== */

#} This is run by main init :)
// This ver is <DAL3, DAL3 ver in DAL3 Helpers file
function zeroBSCRM_installDefaultContent() {

		/* v3.0 this was moved to proper html templating, thankfully, removing backward support, as non critical
	global $zbs;

	#} Quote Builder, defaults
	$quoteBuilderDefaultsInstalled = zeroBSCRM_getSetting('quotes_default_templates');

	if (!is_array($quoteBuilderDefaultsInstalled)){

		#} Need installing!
		$installedQuoteTemplates = array();

		#} REMOVE PREV
			$args = array (
				'post_type'              => 'zerobs_quo_template',
				'post_status'            => 'publish',
				'posts_per_page'         => 100,
				'order'                  => 'DESC',
				'orderby'                => 'post_date',

					// KEY
				   'meta_key'   => 'zbsdefault', 
				   'meta_value' => 1
			);

			$list = get_posts( $args );
			if (count($list) > 0) foreach ($list as $template){

				wp_delete_post($template->ID,true);

			}


			#} Load content
			global $quoteBuilderDefaultTemplates;
			if(!isset($quoteBuilderDefaultTemplates)) require_once( ZEROBSCRM_INCLUDE_PATH . 'ZeroBSCRM.DefaultContent.php');

			#} Install..
			if (count($quoteBuilderDefaultTemplates) > 0) foreach ($quoteBuilderDefaultTemplates as $template){

				$newPost = wp_insert_post(array('post_title'=>$template[0],'post_content'=>$template[1],'post_status'=>'publish','post_type'=>'zerobs_quo_template','comment_status'=>'closed','meta_input'=>array('zbs_quotemplate_meta'=>array(),'zbsdefault'=>1)));

				if ($newPost > 0) $installedQuoteTemplates[] = $newPost;

			}

			#} Log installed
	  		$zbs->settings->update('quotes_default_templates',$installedQuoteTemplates);


	}


	  */

}



/* ======================================================
	/ Install Default Content (if not installed)
   ====================================================== */




/* =====
  Fixing up the internal linking
=======*/

function zeroBSCRM_removeCustomPostTypesFromTinyMCELinkBuilder($query){
    $key = false;

    $cpt_to_remove = array(
        'zerobs_customer',
        'zerobs_company',
        'zerobs_invoice',
        'zerobs_transaction',
        'zerobs_log',
        'zerobs_form',
        'zerobs_quo_template',
        'zerobs_quote',
        'zerobs_event'
    );

    foreach ($cpt_to_remove as $custom_post_type) {
        $key = array_search($custom_post_type, $query['post_type']);
        if($key){
            unset($query['post_type'][$key]);
        } 
    }
    return $query; 
}
add_filter( 'wp_link_query_args', 'zeroBSCRM_removeCustomPostTypesFromTinyMCELinkBuilder' );








/* ======================================================
	Meta Box Re-arrange
   ====================================================== */

   /* 
	
	not used in end

	#} This re-arranges the meta boxes:
   	#http://wordpress.stackexchange.com/questions/33063/how-to-change-default-position-of-wp-meta-boxes
	add_action('do_meta_boxes', 'zeroBSCRM_quote_meta_box_shuffle');

	function zeroBSCRM_quote_meta_box_shuffle(){
	    
	    remove_meta_box( 'postimagediv', 'post', 'side' );
	    add_meta_box('postimagediv', __('Featured Image', 'zero-bs-crm'), 'post_thumbnail_meta_box', 'post', 'normal', 'high');

	}

	*/


/* ======================================================
	/ Meta Box Re-arrange
   ====================================================== */
