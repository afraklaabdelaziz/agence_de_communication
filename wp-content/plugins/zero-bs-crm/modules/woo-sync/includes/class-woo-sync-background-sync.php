<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 *
 * WooSync: Background Sync
 *
 */
namespace Automattic\JetpackCRM;

// block direct access
defined( 'ZEROBSCRM_PATH' ) || exit;

#} the WooCommerce API
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use Automattic\JetpackCRM\Missing_Settings_Exception;

/**
 * WooSync Background Sync class
 */
class Woo_Sync_Background_Sync {


	/**
	 * Number of orders to process per job
	 */
	private $orders_per_page = 10;
	
	/**
	 * Number of pages of orders to process per job
	 */
	private $pages_per_job = 1;
	
	/**
	 * If set to true this will echo progress of a sync job.
	 */
	public $debug = false;

	/**
	 * The single instance of the class.
	 */
	protected static $_instance = null;

	/**
	 * Setup WooSync Background Sync
	 * Note: This will effectively fire after core settings and modules loaded
	 * ... effectively on tail end of `init`
	 */
	public function __construct( ) {

		// Initialise Hooks
		$this->init_hooks();

		// Schedule cron
		$this->schedule_cron();

	}
		

	/**
	 * Main Classs Instance.
	 *
	 * Ensures only one instance of Woo_Sync_Background_Sync is loaded or can be loaded.
	 *
	 * @since 2.0
	 * @static
	 * @see 
	 * @return Woo_Sync_Background_Sync main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	/**
	 * Returns main class instance
	 */
	public function woosync(){

		global $zbs;
		return $zbs->modules->woosync;

	}


	/**
	 * Returns full settings array from main settings class
	 */
	public function settings(){

		return $this->woosync()->settings->getAll();

	}


	/**
	 * Returns 'local' or 'api'
	 *  (whichever mode is selected in settings)
	 */
	public function import_mode(){

		// settings
		$settings = $this->settings();
		return (int)$settings['wcsetuptype'];

	}
	

	/**
	 * Returns WooCommerce Client instance
	 *  built using settings based keys etc.
	 *
	 * @return string Order Prefix
	 */
	public function get_woocommerce_client(){

		$settings = $this->settings();

		$domain     = 	$settings['wcdomain'];
		$key        = 	$settings['wckey'];
		$secret     = 	$settings['wcsecret'];
		$prefix     = 	$settings['wcprefix'];

		if ( !empty( $key ) && !empty( $secret ) && !empty( $domain ) ){

			// include the rest API files
			$this->woosync()->include_woocommerce_rest_api();

			return new Client(
				$domain, 
				$key, 
				$secret,
				[
					// https://github.com/woocommerce/wc-api-php
					'version' => 'wc/v3',

					// https://stackoverflow.com/questions/42186757/woocommerce-woocommerce-rest-cannot-view-status-401
					'query_string_auth' => true,

				]
			);

		} else {

			$missing = array();
			if ( empty( $key ) ){
				$missing[] = 'key';
			}
			if ( empty( $secret ) ){
				$missing[] = 'secret';
			}
			if ( empty( $domain ) ){
				$missing[] = 'domain';
			}

	       	throw new Missing_Settings_Exception( 101, 'Failed to load WooCommerce API Library', array( 'missing' => $missing ) );

		}

		return false;

	}

	
	/**
	 * If $this->debug is true, outputs passed string
	 *
	 * @param string - Debug string
	 */
	private function debug( $str ){

		if ( $this->debug ){

			echo '[' . zeroBSCRM_locale_utsToDatetime( time() ) . '] ' . $str . '<br>';

		}

	}


	/**
	 * Initialise Hooks
	 */
	private function init_hooks( ) {

		// cron
		add_action( 'jpcrm_woosync_sync', array( $this, 'cron_job' ) );

		// Syncing based on WooCommerce hooks:

		// Order changes:
		add_action( 'woocommerce_order_status_changed',    array( $this, 'add_update_from_woo_order' ), 1, 1 );
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'add_update_from_woo_order' ), 100, 1 );
		add_action( 'woocommerce_deposits_create_order',   array( $this, 'add_update_from_woo_order' ), 100, 1 );
		add_action( 'wp_trash_post',                       array( $this, 'woocommerce_order_trashed' ), 10, 1 );
		add_action( 'before_delete_post',                  array( $this, 'woocommerce_order_deleted' ), 10, 1 );	

		// Catch WooCommerce customer address changes and update contact:
		add_action( 'woocommerce_customer_save_address',   array( $this, 'update_contact_address_from_wp_user' ), 10, 3 ); 

		// add our cron task to the core crm cron monitor list
		add_filter( 'jpcrm_cron_to_monitor',               array( $this, 'add_cron_monitor' ) );

	}


	/**
	 * Setup cron schedule
	 */
	private function schedule_cron( ) {

		// schedule it
		if ( ! wp_next_scheduled( 'jpcrm_woosync_sync' ) ) {
		  wp_schedule_event( time(), '5min', 'jpcrm_woosync_sync' );
		}	

	}


	/**
	 * Run cron job
	 */
	public function cron_job(){

		$settings = $this->settings();
		if ( $settings['wcsetuptype'] == 1 ){

			// define global to mark this as a cron call
			define( 'jpcrm_woosync_cron_running', 1 );

			// fire job
			$this->sync_orders();

		}

	}

	/**
	 * Returns bool as to whether or not the current call was made via cron
	 */
	private function is_cron(){

		return defined( 'jpcrm_woosync_cron_running' );

	}


	/**
	 * Filter call to add the cron zbssendbot to the watcher system
	 *
	 * @param array $crons
	 * @return array
	 */
	function add_cron_monitor( $crons ) {

		if ( is_array( $crons ) ) {

			$crons[ 'jpcrm_woosync_sync' ] = '5min'; //'hourly';
		}

		return $crons;
	}


	/**
	 * Main job function: using established settings, this will retrieve and import orders
	 *  from WooCommerce into CRM. This can be called in three 'modes'
	 *    - via cron (as defined by `jpcrm_woosync_cron_running`)
	 *    - via AJAX (if not via cron and not in debug mode)
	 *    - for debug (if $this->debug is set) This is designed to be called inline and will output progress of sync job
	 *
	 *
	 * @return mixed (int|json)
	 *   - if cron originated: a count of orers imported is returned
	 *   - if not cron originated (assumes AJAX):
	 *      - if completed sync: JSON summary info is output and then exit() is called
	 *      - else count of orders imported is returned
	 */
	public function sync_orders(){

		$this->debug( 'Fired `sync_orders()`.' );

		// check not currently running
		if ( defined( 'jpcrm_woosync_running' ) ){

			$this->debug( 'Attempted to run `sync_orders()` when job already in progress.' );

			// return blocker error
			return array( 'status' => 'job_in_progress' );

		}

		// if not already completed...
		// ... and it's a cron check-in
		if ( !$this->is_cron() ){

			$completed_state = $this->first_import_completed();

			if ( $completed_state ){

				$this->debug( 'Attempted to run `sync_orders()` when sync already completed. (Try this page with `&definitely_restart_sync=1`.)' );

				// return completed error
				return array( 'status' => 'sync_completed' );

			}

		} else {

			// cron run needs to pull a new page
			$completed_state = false;

		}

		// blocker
		define( 'jpcrm_woosync_running', 1 );

		$this->debug( 'Running Import of ' . $this->pages_per_job . ' Pages' );

		// do x pages
		for ( $i = 0; $i < $this->pages_per_job; $i++ ){

			// return completed
			if ( $completed_state ){

				$this->debug( 'Sync Completed.' );

				return array( 'status' => 'sync_completed' );

			}

			// get last working position
			$next_page = $this->resume_from_page();

			// ... if for some reason we've got a negative, start from scratch.
			if ( $next_page < 1 ){

				$next_page = 1;

			}

			// import the page of orders
			// This always returns the count of imported orders, 
			//   unless 100% sync is reached, at which point it will exit (if called via AJAX)
			//   for now, we don't need to track the return
			$this->import_page_of_orders( $next_page );

			// set the next page to import
			$this->set_resume_from_page( $next_page + 1 );

			$completed_state = $this->first_import_completed();

		}


		// return completed / status
		if ( $this->first_import_completed() ){

			$this->debug( 'Sync Completed.' );

			return array( 'status' => 'sync_completed' );

		} else {


			$this->debug( 'Sync Job finished with orders outstanding: ' . $this->percentage_completed() . '% complete.' );

			return array( 
			
				'status'                => 'sync_part_complete',
				'percentage_completed'  => $this->percentage_completed(),
				'next_page'             => $this->resume_from_page()
			
			);

		}

	}


	/**
	 * Retrieve and process 1 page of WooCommerce orders via API or from local store
	 *
	 * @param int $page_no - the page number to start from
	 *
	 * @return mixed (int|json)
	 *   - if cron originated: a count of orers imported is returned
	 *   - if not cron originated (assumes AJAX):
	 *      - if completed sync: JSON summary info is output and then exit() is called
	 *      - else count of orders imported is returned
	 */
	private function import_page_of_orders( $page_no ){

		$this->debug( 'Fired `import_page_of_orders( ' . $page_no . ' )`, importing from ' . $this->import_mode() . '.' );

		// store/api switch
		if ( $this->import_mode() == JPCRM_WOO_SYNC_MODE_API ){

			// API 
			return $this->import_orders_from_api( $page_no );

		} else {

			return $this->import_orders_from_store( $page_no );

		}


	}


	/**
	 * Retrieve and process a page of WooCommerce orders from local store
	 *  Previously `get_orders_from_store`
	 *
	 * @param int $page_no 
	 *
	 * @return mixed (int|json)
	 *   - if cron originated: a count of orers imported is returned
	 *   - if not cron originated (assumes AJAX):
	 *      - if completed sync: JSON summary info is output and then exit() is called
	 *      - else count of orders imported is returned
	 */
	public function import_orders_from_store( $page_no = -1 ){

		// Where we're trying to run without WooCommerce, fail.
		if ( !function_exists( 'wc_get_orders' ) ){

			$this->debug( 'Unable to import as it appears WooCommerce is not installed.' );
			return false;

		}

	    // retrieve orders
	    $orders = wc_get_orders(array(
	        'limit'    => $this->orders_per_page,
	        'paged'    => $page_no,
	        'paginate' => true
	    ));

	    // count the pages and break if we have nothing to import
	    if ( $orders->max_num_pages == 0 ) {

	    	// we're at 100%, mark sync complete
	    	$this->set_import_first_import_status( true );

	    	// if cron, we just return count
	    	if ( $this->is_cron() ){

	    		return 0;

	    	} else {

				$this->debug( 'Sync Completed before any work done (`import_orders_from_store()`)' );

	    		// will be AJAX origin call, return more detail
	    		echo json_encode( array(

	    			'status'                => 'sync_completed',
	    			'page_no'               => $page_no,
    				'orders_imported'       => 0,
	    			'percentage_completed'  => 100,
	    			'error'                 => 'no_orders',
	    			'error_message'         => __( 'You do not have any WooCommerce orders to import. Try adding some.', 'zero-bs-crm' )

	    		));
	    		exit();

	    	}

	    }

	    // we have some pages to process, so proceed
	    $orders_imported = 0;

	    // cycle through orders from store and import
	    foreach ( $orders->orders as $order ) {			

			// We previously used the wp cpt ID, see #1982
			// In case we hit issues where a user sees dupes from this, we'll store any != in an extra meta
			$order_post_id = $order->get_id();
			
			// Refunds don't have 'get_order_number', so we are using their id as a fallback.
			if ( method_exists( $order, 'get_order_number' ) ) {
				$order_id = $order->get_order_number();
			} else {
				$order_id = $order->get_id();
			}
			
			if ( !empty( $order_id ) ){

				$this->debug( 'Importing order id: ' . $order_id . '(' . $order_post_id . ')' );

				// if order post id is the same as order id, clear it
				if ( $order_id == $order_post_id ){
					$order_post_id = false;
				}
				
				// this seems perhaps unperformant given we have the `order` object
				// ... and this function re-get's the order object, but it's centralised and useful (and #legacy)
				$this->add_update_from_woo_order( $order_id, $order_post_id );

				$orders_imported++;

			}

	    }

	    // check for completion
	    if ( $page_no == $orders->max_num_pages ) {

	    	// we're at 100%, mark sync complete
	    	$this->set_import_first_import_status( true );

	    	// if cron, we just return count
	    	if ( $this->is_cron() ){

	    		return $orders_imported;

	    	} else {

				$this->debug( 'Sync Completed after work done (`import_orders_from_store()`)' );

	    		// will be AJAX origin call, return more detail
	    		echo json_encode( array(

	    			'status'                => 'sync_completed',
	    			'page_no'               => $page_no,
    				'orders_imported'       => $orders_imported,
	    			'percentage_completed'  => 100

	    		));
	    		exit();

	    	}

	    }

	    // There's still pages to go then:

	    // return the count
    	return $orders_imported;

	}


	/**
	 * Retrieve and process a page of WooCommerce orders via API
	 *  Previously `get_orders_from_api`
	 *
	 * @param int $page_no
	 *
	 * @return mixed (int|json)
	 *   - if cron originated: a count of orers imported is returned
	 *   - if not cron originated (assumes AJAX):
	 *      - if completed sync: JSON summary info is output and then exit() is called
	 *      - else count of orders imported is returned
	 */
	public function import_orders_from_api( $page_no = -1 ){

		global $zbs;

	    try {

	    	// retrieve domain from settings
	    	$settings = $this->settings();

	    	// get client
	        $woocommerce = $this->get_woocommerce_client();

	    	// clock origin
	    	$origin = '';
	    	$domain = $settings['wcdomain'];
	    	if ( !empty( $domain ) ){

	    		// if Domain
	    		if ( $domain ){
	    			$origin = $zbs->DAL->add_origin_prefix( $domain, 'domain' );
	    		}

	    	}

	        // retrieve orders
	        // https://woocommerce.github.io/woocommerce-rest-api-docs/v3.html?php#parameters
	        $orders = $woocommerce->get( 'orders', array( 
	        	'page'     => $page_no,
	        	'per_page'    => $this->orders_per_page
	        ));

	        // retrieve page count from headers:
	        $last_response           = $woocommerce->http->getResponse();
	        $response_headers        = $last_response->getHeaders();
	        $total_pages             = $response_headers['X-WP-TotalPages'];

	        $this->debug( 'API Response:<pre>' . var_export( array(

	        	'orders_retrieved' => count( $orders ),
	        	//'last_response' => $last_response,
	        	//'response_headers' => $response_headers,
	        	'total_pages' => $total_pages

	        ), true ) . '</pre>' );

		    // count the pages and break if we have nothing to import
		    if ( $total_pages == 0 ) {

		    	// we're at 100%, mark sync complete
		    	$this->set_import_first_import_status( true );

		    	// if cron, we just return count
		    	if ( $this->is_cron() ){

		    		return 0;

		    	} else {

					$this->debug( 'Sync Completed before any work done (`import_orders_from_api()`)' );

		    		// will be AJAX origin call, return more detail
		    		echo json_encode( array(

		    			'status'                => 'sync_completed',
		    			'page_no'               => $page_no,
	    				'orders_imported'       => 0,
		    			'percentage_completed'  => 100,
		    			'error'                 => 'no_orders',
		    			'error_message'         => __( 'There were no WooCommerce orders to import from this API endpoint.', 'zero-bs-crm' )

		    		));
		    		exit();

		    	}

		    }

		    // we have some pages to process, so proceed
		    $orders_imported = 0;

	        // cycle through orders
	        foreach ( $orders as $order ) {

				$this->debug( 'Importing order id: ' . $order->id . ' (becoming: ' . $this->woosync()->get_prefix() . $order->id . ')' );

	        	// prefix ID
	            $order->id = $this->woosync()->get_prefix() . $order->id;

	            // translate order data to crm objects
	            $crm_objects = $this->woocommerce_api_order_to_crm_objects( $order, $origin );

	            // import crm objects
	            $this->import_crm_object_data( $crm_objects );

	            $orders_imported++;

	        }


		    // check for completion
		    if ( $page_no == $total_pages ) {

		    	// we're at 100%, mark sync complete
		    	$this->set_import_first_import_status( true );

		    	// if cron, we just return count
		    	if ( $this->is_cron() ){

		    		return $orders_imported;

		    	} else {

					$this->debug( 'Sync Completed after work done (`import_orders_from_api()`)' );

		    		// will be AJAX origin call, return more detail
		    		echo json_encode( array(

		    			'status'                => 'sync_completed',
		    			'page_no'               => $page_no,
	    				'orders_imported'       => $orders_imported,
		    			'percentage_completed'  => 100

		    		));
		    		exit();

		    	}

		    }

		    // There's still pages to go then:

		    // return:
	    	return $orders_imported;

	    } catch ( HttpClientException $e ) {

			$this->debug( 'Sync Failed in `import_orders_from_api()`, WooCommerce REST API error: ' . $e->getMessage() );

    		echo json_encode( array(

    			'status'                => 'error',
    			'page_no'               => $page_no,
				'orders_imported'       => 0,
    			'percentage_completed'  => 100,
    			'error'                 => 'woo_client_error',
    			'error_message'         => $this->woosync()->process_error( $e->getMessage() )

    		));
	        exit;

	    } catch ( Missing_Settings_Exception $e ){

	    	// missing settings means couldn't load lib.

	    	// compile string of what's missing
	    	$missing_string = '';
	    	$missing_data = $e->get_error_data();
	    	if ( is_array( $missing_data ) && isset( $missing_data['missing'] ) ){
	    		$missing_string = '<br>' . __( 'Missing:', 'zero-bs-crm' ) . ' ' . implode( ', ', $missing_data['missing'] );
	    	}


			$this->debug( 'Sync Failed in `import_orders_from_api()` due to missing settings (could not, therefore, load WooCommerce API Connection): ' . $e->getMessage() . $missing_string );

    		echo json_encode( array(

    			'status'                => 'error',
    			'page_no'               => $page_no,
				'orders_imported'       => 0,
    			'percentage_completed'  => 100,
    			'error'                 => 'woo_client_error',
    			'error_message'         => $this->woosync()->process_error( $e->getMessage() )

    		));
	        exit;

	    }

	}


	/**
	 * Add or Update an order from WooCommerce
	 *  (previously `add_order_from_id`)
	 *
	 * @param int $order_id Order id from WooCommerce.
	 * @param int $order_post_id Order post id from WooCommerce, (if different than the $order_id, we add this as extrameta)
	 */
	public function add_update_from_woo_order( $order_id, $order_post_id=false ){

		global $zbs;

		// This is only fired from local store calls, so let's retrieve the local domain as origin
    	$origin = '';
		$domain = site_url();
		if ( $domain ){
			$origin = $zbs->DAL->add_origin_prefix( $domain, 'domain' );
		}
	    			
		// gather up order data
	    $order           = wc_get_order( $order_id );
	    $raw_order_data  = $order->get_data();
	    $extra_meta      = array();

	    // if we have order post id, add it
	    if ( $order_post_id ){
	    	$extra_meta['order_post_id'] = $order_post_id;
	    }	    

	    // consolidate data
	    $tidy_order_data = $this->woocommerce_order_to_crm_objects( 
	    	$raw_order_data, 
	    	$order, 
	    	$order_id,
	    	'',
	    	'',
	    	false,
	    	array(),
	    	$origin,
	    	$extra_meta
	    );

	    // import data
	    $this->import_crm_object_data( $tidy_order_data );

	}


	/**
	 * Set's a completion status for woo order imports
	 *
	 * @param string|bool $status = 'yes|no' (#legacy) or 'true|false' 
	 *
	 * @return bool $status
	 */
	public function set_import_first_import_status( $status ){

		$status_bool = false;

		if ( $status == 'yes' || $status === true ){

			$status_bool = true;

		}

	    update_option( 'zbs_woo_first_import_complete', $status_bool );

	    return $status_bool;

	}


	/**
	 * Returns a completion status for woo order imports
	 *
	 * @return bool $status
	 */
	public function first_import_completed(){

		$status_bool = false;

		$setting = get_option('zbs_woo_first_import_complete');

		if ( $setting == 'yes' || $setting === true || $setting == 1 ){

			$status_bool = true;

		}

	    return $status_bool;

	}


	/**
	 * Sets current working page index (to resume from)
	 *
	 * @return int $page
	 */
	public function set_resume_from_page( $page_no ){

	    update_option( 'zbs_woo_resume_sync', $page_no );

	    return $page_no;

	}


	/**
	 * Return current working page index (to resume from)
	 *
	 * @return int $page
	 */
	public function resume_from_page(){

	    return get_option( 'zbs_woo_resume_sync', 0 );

	}


	/**
	 * Adds or updates crm objects related to a processed woocommerce order
	 *  (requires that the $order_data has been passed through `woocommerce_order_to_crm_objects`)
	 *  Previously `import_woocommerce_order_from_order_data`
	 *
	 * @param array $crm_object_data (Woo Order data passed through `woocommerce_order_to_crm_objects`)
	 * @return int $transaction_id
	 */
	public function import_crm_object_data( $crm_object_data ){

		global $zbs;

	    $settings   = $this->settings();

	    // Add/update contact from cleaned order data, (previously `add_or_update_contact_from_order_data`)
		$contact_id = -1;
		if ( isset( $crm_object_data['contact'] ) && isset( $crm_object_data['contact']['email'] ) ){

			// Add the contact
			$contact_id = $zbs->DAL->contacts->addUpdateContact(array(        
	            'data'         => $crm_object_data['contact'],
	            'extraMeta'    => $crm_object_data['contact_extra_meta']	            
	        ));


		}

		// if contact: add logs, contact id relations to objects, and addupdate company
	    if ( $contact_id > 0 ) {

	    	$this->debug( 'Contact added/updated #' . $contact_id );

			// contact logs
			if ( is_array( $crm_object_data['contact_logs'] ) ){

				foreach ( $crm_object_data['contact_logs'] as $log ){

					// add log
					$log_id = $zbs->DAL->logs->addUpdateLog(array(

						'id'			=> -1,
						'owner'			=> -1,

						// fields (directly)
						'data'			=> array(

							'objtype' 	=> ZBS_TYPE_CONTACT,
							'objid' 	=> $contact_id,
							'type' 		=> $log['type'],
							'shortdesc' => $log['shortdesc'],
							'longdesc' 	=> $log['longdesc'],

							'meta' 		=> array(),
							'created'	=> -1
						)

					));

				}

			}

	    	// add contact ID relationship to the related objects
	        $crm_object_data['transaction']['contacts'] = array( $contact_id );
	        $crm_object_data['invoice']['contacts']     = array( $contact_id );

	    	// Add/update company (if using b2b mode, and successfully added/updated contact):
	        $b2b_mode = zeroBSCRM_getSetting( 'companylevelcustomers' );
	        if ( $b2b_mode && isset( $crm_object_data['company']['name'] ) && !empty( $crm_object_data['company']['name'] ) ) {
	            
	            // Add the company
				$company_id = $zbs->DAL->companies->addUpdateCompany(array(        
		            'data'         => $crm_object_data['company']
		        ));

		        if ( $company_id > 0 ){

		            $this->debug( 'Company added/updated #' . $company_id );
	        	
	        		// inject into transaction data too
	        		$crm_object_data['transaction']['companies'] = array( $company_id );

		        } else {

		            $this->debug( 'Company import failed: <code>' . json_encode( $crm_object_data['company'] ) . '</code>' );

		        }

	        } 

	    } else {

	    	// failed to add contact? 
	    	$this->debug( 'Contact import failed, or there was no contact to import. Contact Data: <code>' . json_encode( $crm_object_data['contact'] ) . '</code>' );

	    }

	    // Add/update invoice (if enabled) (previously `add_or_update_invoice`)
	    if ( $settings['wcinv'] == 1 ) {
	       
	       	// retrieve existing invoice
	       	// note this is substituting $crm_object_data['invoice']['existence_check_args'] for what should be $args, but it works
			$invoice_id = $zbs->DAL->invoices->getInvoice( -1, $crm_object_data['invoice']['existence_check_args'] );

			// add logo if invoice doesn't exist yet
			if ( !$invoice_id ) {
				$crm_object_data['invoice']['logo_url'] = jpcrm_business_logo_url();
			}

			$invoice_id = $zbs->DAL->invoices->addUpdateInvoice( array(
				'id'         => $invoice_id,
				'data'       => $crm_object_data['invoice'],
				'extraMeta'  => ( isset( $crm_object_data['invoice']['extra_meta'] ) ? $crm_object_data['invoice']['extra_meta'] : -1 )
			) );

	        // link the transaction to the invoice
	        if ( !empty( $invoice_id ) ) {

	        	$this->debug( 'Added invoice #' . $invoice_id );
	        
	        	$crm_object_data['transaction']['invoice_id'] = $invoice_id;
	        
	        } else {

	            $this->debug( 'invoice import failed: <code>' . json_encode( $crm_object_data['invoice'] ) . '</code>' );

	        }

	    }

	    // Add/update transaction (previously `add_or_update_transaction`)
	    // note this is substituting $crm_object_data['invoice']['existence_check_args'] for what should be $args, but it works
		$existing_transaction_id = $zbs->DAL->transactions->getTransaction( -1, $crm_object_data['transaction']['existence_check_args'] );
		
	    if ( !empty( $existing_transaction_id ) ){
	    	$this->debug( 'Existing transaction #' . $existing_transaction_id );
	    }

		$args = array(
			'id'        => $existing_transaction_id,
			'owner'     => -1,
			'data'      => $crm_object_data['transaction']
		);

		// got any extra meta?
		if ( isset( $crm_object_data['transaction_extra_meta'] ) && is_array( $crm_object_data['transaction_extra_meta'] ) ){

			$args['extraMeta'] = $crm_object_data['transaction_extra_meta'];

		}
		
		$transaction_id = $zbs->DAL->transactions->addUpdateTransaction( $args );

	    if ( !empty( $transaction_id ) ) {
	    	
	    	$this->debug( 'Added/Updated transaction #' . $transaction_id );

	    } else {

            $this->debug( 'Transaction import failed: <code>' . json_encode( $crm_object_data['transaction'] ) . '</code>' );

        }

        // Secondary transactions (Refunds)
        if ( is_array( $crm_object_data['secondary_transactions'] ) ){

        	foreach ( $crm_object_data['secondary_transactions'] as $sub_transaction ){

        		// slightly modified version of above transaction insert logic.
        		$existing_transaction_id = $zbs->DAL->transactions->getTransaction( -1, $sub_transaction['existence_check_args'] );
		
				// debug
			    if ( !empty( $existing_transaction_id ) ){
			    	$this->debug( 'Sub transaction: Existing transaction #' . $existing_transaction_id );
			    }

			    // build arguments
				$args = array(
					'id'        => $existing_transaction_id,
					'owner'     => -1,
					'data'      => $sub_transaction
				);

				// if we have transaction id, also inject it as a parent (this gets caught by the UI to give a link back)
				if ( isset( $transaction_id ) && !empty( $contact_id ) ){
					$args['data']['parent'] = $transaction_id;
				}

				// if we have contact id, also inject it
				if ( isset( $contact_id ) && !empty( $contact_id ) ){
					$args['data']['contacts'] = array( $contact_id );
				}

				// if we have company id, also inject it
				if ( isset( $company_id ) && !empty( $company_id ) ){
					$args['data']['companies'] = array( $company_id );
				}

				// if we have invoice_id, inject it
				// ... this makes our double entry invoices work.
				if ( isset( $invoice_id ) && !empty( $invoice_id ) ){

					$args['data']['invoice_id'] = $invoice_id;

				}

				// pass any extra meta along
				if ( isset( $sub_transaction['extra_meta'] ) && is_array( $sub_transaction['extra_meta'] ) ){

					$args['extraMeta'] = $sub_transaction['extra_meta'];
					unset( $args['data']['extra_meta'] );

				}

				$sub_transaction_id = $zbs->DAL->transactions->addUpdateTransaction( $args );

	    		$this->debug( 'Added/Updated Sub-transaction (Refund) #' . $sub_transaction_id );

        	}

        }
		
		return $transaction_id;

	}


	/**
	 * Translates a local store order into an import-ready crm objects array
	 *  previously `tidy_order_from_store`
	 *
	 * @param $order_data
	 * @param $order
	 * @param $order_id
	 * @param $order_items
	 * @param $api
	 * @param $order_tags
	 * @param $origin
	 * @param $extra_meta
	 *
	 * @return array of various objects (contact|company|transaction|invoice)
	 */
	public function woocommerce_order_to_crm_objects(
	    $order_data,
	    $order,
	    $order_id,
	    $order_items = '',
	    $item_title = '',
	    $from_api = false,
	    $order_tags = array(),
	    $origin = '',
	    $extra_meta = array()
	){

		global $zbs;

	    // get settings
	    $settings = $this->settings();

	    // build arrays
	    $data = array(
	        'contact'                 => array(),
	        'contact_extra_meta'      => array(),
	        'contact_logs'            => array(),
	        'company'                 => false,
	        'invoice'                 => false,
	        'transaction'             => false,
	        'secondary_transactions'  => array(),
	        'lineitems'               => array(),
	    );

    	// Below we sometimes need to do some type-conversion, (e.g. dates), so here we retrieve our 
    	// crm contact custom fields to use the types...
    	$custom_fields = $zbs->DAL->getActiveCustomFields( array( 'objtypeid' => ZBS_TYPE_CONTACT ) );

	    // initialise dates
	    $contact_creation_date         = -1;
	    $contact_creation_date_uts     = -1;
	    $transaction_creation_date_uts = -1;
	    $invoice_creation_date_uts     = -1;

	    // Tag customer setting i.e. do we want to tag with every product name
	    // Will be useful to be able to filter Sales Dashboard by Product name eventually
	    $tag_contact_with_item     = false;
	    $tag_transaction_with_item = false;
	    $tag_invoice_with_item     = false;
	    $tag_with_coupon           = false;
	    $tag_product_prefix = ( isset( $settings['wctagproductprefix'] ) ) ? zeroBSCRM_textExpose( $settings['wctagproductprefix'] ) : '';
	    $tag_coupon_prefix = ( isset( $settings['wctagcouponprefix'] ) ) ? zeroBSCRM_textExpose( $settings['wctagcouponprefix'] ) : '';
	    if ( isset( $settings['wctagcust'] ) && $settings['wctagcust'] == 1 ) {

	        $tag_contact_with_item = true;

	    }
	    if ( isset( $settings['wctagtransaction'] ) && $settings['wctagtransaction'] == 1 ) {

	        $tag_transaction_with_item = true;

	    }
	    if ( isset( $settings['wctaginvoice'] ) && $settings['wctaginvoice'] == 1 ) {

	        $tag_invoice_with_item = true;

	    }
	    if ( isset( $settings['wctagcoupon'] ) && $settings['wctagcoupon'] == 1 ) {

	        $tag_with_coupon = true;

	    }

	    // pre-processing from the $order_data
	    $order_status   = $order_data['status'];
	    $order_currency = $order_data['currency'];

	    // Add external source
	    $data['source'] = array(
	        'externalSource'      => 'woo',
	        'externalSourceUID'   => $order_id,
	        'origin'              => $origin,
	        'onlyID'              => true
	    );	      	

	    // Dates:
	    if ( !$from_api ) {

	        // from local store

	        if ( isset( $order_data['date_created'] ) && !empty( $order_data['date_created'] ) ) {

	            $contact_creation_date         = $order_data['date_created']->date("Y-m-d h:m:s");
	            $contact_creation_date_uts     = $order_data['date_created']->date("U");
	            $transaction_creation_date_uts = $order_data['date_created']->date("U");
	            $invoice_creation_date_uts     = $order_data['date_created']->date("U");

	        }

	    } else {

	        // from API
	        // dates are strings in API.
	        $contact_creation_date         = $order_data['date_created'];
	        $contact_creation_date_uts     = strtotime($order_data['date_created']);
	        $transaction_creation_date_uts = strtotime($order_data['date_created']);
	        $invoice_creation_date_uts     = strtotime($order_data['date_created']);

	    }

	    // ==== Contact

	    // Always use contact email, not billing email:
	    // We've hit issues based on adding a Jetpack CRM contact based on billing email if they have a WP user attached
	    // with a different email. The $order_data['customer_id'] will = 0 for guest or +tive for users. This way we will always
	    // store the contact against the contact email (and not the billing email)
	    $contact_email = '';
	    $billing_email = '';

	    if ( isset( $order_data['customer_id']) && $order_data['customer_id'] > 0 ) {

	        // then we have an existing user. Get the WP email
	        $user          = get_user_by( 'id', $order_data['customer_id'] );
	        $contact_email = $user->user_email;
	        if ( isset($order_data['billing']['email'] ) ) {
	            $billing_email = $order_data['billing']['email'];
	        }

	    } else {

	        if ( isset( $order_data['billing']['email'] ) ) {
	            $billing_email = $order_data['billing']['email'];
	            $contact_email = $billing_email;
	        }

	    }

	    // we only add a contact whom has an email
	    if ( !empty( $contact_email ) ) {

	        $data['contact']['status']     = $this->woocommerce_order_status_to_contact_status( $order_status );
	        $data['contact']['created']    = $contact_creation_date_uts;
	        $data['contact']['email']      = $contact_email;
	        $data['contact']['externalSources'] = array(array(
		            'source' => 'woo',
		            'uid'    => $order_id,
		            'origin' => $origin,
		            'owner'  => 0 // for now we hard-type no owner to avoid ownership issues. As we roll out fuller ownership we may want to adapt this.
		    ));

	        if ( isset( $order_data['billing']['first_name'] ) ){
	            $data['contact']['fname'] = $order_data['billing']['first_name'];
	        }

	        if ( isset( $order_data['billing']['last_name'] ) ){
	            $data['contact']['lname'] = $order_data['billing']['last_name'];
	        }

	        // if we've not got any fname/lname and we do have 'customer_id' attribute (wp user id)
	        // ... check the wp user to see if they have a display name we can use.
	        if ( isset( $order_data['customer_id'] ) && $order_data['customer_id'] > 0 ){

	        	// retrieve wp user
				$woo_customer_meta 	= get_user_meta( $order_data['customer_id'] );

				// fname
		        if ( isset( $woo_customer_meta['first_name'] )
		        	&&
		        	( !isset( $data['contact']['fname'] ) || empty( $data['contact']['fname'] ) )
		        ){
		       
		        	$data['contact']['fname'] = $woo_customer_meta['first_name'][0];
		 	  
		 	    }

				// lname
		        if ( isset( $woo_customer_meta['last_name'] )
		        	&&
		        	( !isset( $data['contact']['lname'] ) || empty( $data['contact']['lname'] ) )
		        ){
		       
		        	$data['contact']['lname'] = $woo_customer_meta['last_name'][0];
		 	  
		 	    }


			}


	        if ( isset( $order_data['billing']['address_1'] ) ){
	            $data['contact']['addr1'] = $order_data['billing']['address_1'];
	        }

	        if ( isset( $order_data['billing']['address_2'] ) ){
	            $data['contact']['addr2'] = $order_data['billing']['address_2'];
	        }

	        if ( isset( $order_data['billing']['city'] ) ){
	            $data['contact']['city'] = $order_data['billing']['city'];
	        }

	        if ( isset( $order_data['billing']['state'] ) ){
	            $data['contact']['county'] = $order_data['billing']['state'];
	        }

	        if ( isset( $order_data['billing']['postcode'] ) ){
	            $data['contact']['postcode'] = $order_data['billing']['postcode'];
	        }

	        if ( isset( $order_data['billing']['country'] ) ){
	            $data['contact']['country'] = $order_data['billing']['country'];
	        }

	        if ( isset( $order_data['billing']['phone'] ) ){
	            $data['contact']['hometel'] = $order_data['billing']['phone'];
	        }

	        // if setting: copy shipping address
	        if ( $settings['wccopyship'] ) {
	            if ( isset( $order_data['shipping']['address_1'] ) ){
	                $data['contact']['secaddr1'] = $order_data['shipping']['address_1'];
	            }

	            if ( isset( $order_data['shipping']['address_2'] ) ){
	                $data['contact']['secaddr2'] = $order_data['shipping']['address_2'];
	            }

	            if ( isset( $order_data['shipping']['city'] ) ){
	                $data['contact']['seccity'] = $order_data['shipping']['city'];
	            }

	            if ( isset( $order_data['shipping']['state'] ) ){
	                $data['contact']['seccounty'] = $order_data['shipping']['state'];
	            }

	            if ( isset( $order_data['shipping']['postcode'] ) ){
	                $data['contact']['secpostcode'] = $order_data['shipping']['postcode'];
	            }

	            if ( isset( $order_data['shipping']['country'] ) ){
	                $data['contact']['seccountry'] = $order_data['shipping']['country'];
	            }

	        }

	        // Store the billing email as an alias, and as an extraMeta (for later potential origin work)
	        if ( !empty($billing_email) ) {

	            $data['contact_extra_meta']['billingemail'] = $billing_email;

	        	// we only need to add the alias if it's different to the $contact_email
	            if ( $billing_email !== $contact_email ) {
	            	$data['contact']['aliases']                 = array($billing_email);
	            }

	        }

	        // Store any customer notes
	        if ( isset( $order_data['customer_note']) && !empty($order_data['customer_note'] ) ){

	            // Previously `notes` field, refactor into core moved this into log addition
	            $data['contact_logs'][] = array(

	                'type'      => 'note',
	                'shortdesc' => __('WooCommerce Customer notes', 'zero-bs-crm'),
	                'longdesc'  => __('WooCommerce Customer notes:', 'zero-bs-crm') . ' ' . $order_data['customer_note'],

	            );

	        }

	        // Retrieve any WooCommerce Checkout metaa data & try to store it against contact if match custom fields
	        // Returns array of WC_Meta_Data objects https://woocommerce.github.io/code-reference/classes/WC-Meta-Data.html
	        // Filters to support WooCommerce Checkout Field Editor, Field editor Pro etc.
	        /*

	            [1] => WC_Meta_Data Object
                (
                    [current_data:protected] => Array
                        (
                            [id] => 864
                            [key] => tax-id
                            [value] => 12345
                        )

                    [data:protected] => Array
                        (
                            [id] => 864
                            [key] => tax-id
                            [value] => 12345
                        )

                )

            */
	        if ( isset( $order_data['meta_data'] ) && is_array( $order_data['meta_data'] ) ){

	        	// Cycle through them and pick out matching fields
	        	foreach ( $order_data['meta_data'] as $wc_meta_data_object ){

	        		// retrieve data
	        		$meta_data = $wc_meta_data_object->get_data();

	        		if ( is_array( $meta_data ) ){

	        			// process it, only adding if not already set (to avoid custom checkout overriding base fields)
	        			$key = $zbs->DAL->makeSlug( $meta_data['key'] );

	        			if ( !empty( $key ) && !isset( $data['contact'][ $key ] ) ){

	        				$value = $meta_data['value'];

	        				// see if we have a matching custom field to infer type conversions from:
	        				if ( isset( $custom_fields[ $key ] ) ){

	        					// switch on type
	        					switch ( $custom_fields[ $key ][0] ){

	        						case 'date':

	        							// May 29, 2022 => UTS
	        							$value = strtotime( $value );

	        							break;

	        					}

	        				}

	        				// simplistic add
	        				$data['contact'][ $key ] = $value;

	        				// filter through any mods
	        				$data['contact'] = $this->filter_checkout_contact_fields( $key, $value, $data['contact'], $order, $custom_fields );

	        			}

	        		}

	        	}

	        }

	        // WooCommerce Checkout Add-ons fields support, where installed
	        $data['contact'] = $this->checkout_add_ons_add_field_values( $order_id, $data['contact'], $custom_fields );

	    }

	    // ==== Company (where available)

	    if ( isset( $order_data['billing']) && isset($order_data['billing']['company'] ) ){

	        // Build fields for company
	        $data['company'] = array(
	            'status' => __('Customer', 'zero-bs-crm'),
	            'name'   => $order_data['billing']['company'],
	            'created'     => $contact_creation_date_uts,
	            'externalSources' => array(array(
		            'source' => 'woo',
		            'uid'    => $order_id,
		            'origin' => $origin,
		            'owner'  => 0 // for now we hard-type no owner to avoid ownership issues. As we roll out fuller ownership we may want to adapt this.
		        ))
	        );

	        if ( isset( $order_data['billing']['address_1']) && !empty($order_data['billing']['address_1'] ) ){
	            $data['company']['addr1'] = $order_data['billing']['address_1'];
	        }

	        if ( isset( $order_data['billing']['address_2']) && !empty($order_data['billing']['address_2'] ) ){
	            $data['company']['addr2'] = $order_data['billing']['address_2'];
	        }

	        if ( isset( $order_data['billing']['city']) && !empty($order_data['billing']['city'] ) ){
	            $data['company']['city'] = $order_data['billing']['city'];
	        }

	        if ( isset( $order_data['billing']['state']) && !empty($order_data['billing']['state'] ) ){
	            $data['company']['county'] = $order_data['billing']['state'];
	        }

	        if ( isset( $order_data['billing']['country']) && !empty($order_data['billing']['country'] ) ){
	            $data['company']['country'] = $order_data['billing']['country'];
	        }

	        if ( isset( $order_data['billing']['postcode']) && !empty($order_data['billing']['postcode'] ) ){
	            $data['company']['postcode'] = $order_data['billing']['postcode'];
	        }

	        if ( isset( $order_data['billing']['phone']) && !empty($order_data['billing']['phone'] ) ){
	            $data['company']['maintel'] = $order_data['billing']['phone'];
	        }

	        if ( isset( $order_data['billing']['email']) && !empty($order_data['billing']['email'] ) ){
	            $data['company']['email'] = $order_data['billing']['email'];
	        }

	    }

	    
	    // ==== Transaction

	    // prep dates
	    $transaction_paid_date          = -1;
	    $transaction_paid_date_uts      = -1;
	    $transaction_completed_date     = -1;
	    $transaction_completed_date_uts = -1;

	    // if we have a paid date, set the invoice status and clock the paid time
	    if ( array_key_exists( 'date_paid', $order_data ) && !empty( $order_data['date_paid'] ) ){

	        $transaction_paid_date_uts = $order_data['date_paid']->date("U");
	        $invoice_status            = __("Paid", "zero-bs-crm");

	    } else {

	        $invoice_status = __("Unpaid", "zero-bs-crm");

	    }

	    // retrieve completed date, where available
	    if ( array_key_exists('date_completed', $order_data ) && !empty( $order_data['date_completed'] ) ){

	        $transaction_completed_date_uts = $order_data['date_completed']->date("U");

	    }

	    // Retrieve and process order line items
	    if ( !$from_api ) {

	    	$item_title = '';
	        $order_items = $order->get_items();

	        foreach ( $order_items as $item_key => $item ) {

	        	// first item gets item name
	            if ( empty( $item_title ) ) {

	                $item_title = $item->get_name();

	            } else {

	                $item_title = __("Multiple Items", "zero-bs-crm");

	            }

	            // retrieve item data
	            $item_data         = $item->get_data();

	            // attributes not yet translatable but originally referenced: `variation_id|tax_class|subtotal_tax`
	            $data['lineitems'][] = array(
	                'order'    => $order_id, // passed as parameter to this function
	                'currency' => $order_currency,
	                'quantity' => $item_data['quantity'],
	                'price'    => $item_data['subtotal'] / $item_data['quantity'],
	                'total'    => $item_data['total'],
	                'title'    => $item_data['name'],
	                'desc'     => $item_data['name'] . ' (#' . $item_data['product_id'] . ')',
	                'tax'      => $item_data['total_tax'],
	                'shipping' => 0,
	            );

	            // add to tags where not alreday present
	            if ( !in_array( $item_data['name'], $order_tags ) ){
	            	$order_tags[] = $tag_product_prefix . $item_data['name'];
	            }
	            
	        }


	    	// if the order has a coupon. Tag the contact with that coupon too, but only if from same store.
	        if ( $tag_with_coupon ) {
	        
	        	foreach ( $order->get_coupon_codes() as $coupon_code ) {
		            
		            $order_tags[] = $tag_coupon_prefix . $coupon_code;

		        }

		    }

	    } else {

	        // API response returns these differently
	        $data['lineitems'] = $order_items;

	    }

	    // tags (contact)
	    if ( $tag_contact_with_item ) {

	        $data['contact']['tags']     = $order_tags;
	        $data['contact']['tag_mode'] = 'append';

	    }

	    // fill out transaction header (object)
	    $data['transaction'] = array(

	        'ref'             => $order_id,
	        'title'           => $item_title,
	        'status'          => $order_status,
	        'total'           => $order_data['total'],
	        'date'            => $transaction_creation_date_uts,
	        'created'         => $transaction_creation_date_uts,
	        'date_completed'  => $transaction_completed_date_uts,
	        'date_paid'       => $transaction_paid_date_uts,
	        'externalSources' => array(array(
	            'source' => 'woo',
	            'uid'    => $order_id,
	            'origin' => $origin,
		        'owner'  => 0 // for now we hard-type no owner to avoid ownership issues. As we roll out fuller ownership we may want to adapt this.
	        )),
	        'currency'        => $order_currency,
	        'net'             => ( $order_data['total'] - $order_data['discount_total'] - $order_data['total_tax'] - $order_data['shipping_total'] ),
	        'tax'             => $order_data['total_tax'],
	        'fee'             => 0,
	        'discount'        => $order_data['discount_total'],
	        'shipping'        => $order_data['shipping_total'],
	        'existence_check_args' => $data['source'],
	        'lineitems'       => $data['lineitems'],

	    );

	    // tags (transaction)
	    if ( $tag_transaction_with_item ) {

	        $data['transaction']['tags']     = $order_tags;
	        $data['transaction']['tag_mode'] = 'append';

	    }

	    // any extra meta?
	    if ( is_array( $extra_meta ) && count( $extra_meta ) > 0 ){

	    	$data['transaction_extra_meta'] = $extra_meta;

	    }

	    // Sub-transactions (refunds)
	    if ( method_exists( $order, 'get_refunds' ) ) {

	    	// process refunds
		    $refunds = $order->get_refunds();
		    if ( is_array( $refunds ) ){

		    	// cycle through and add as secondary transactions
		    	foreach ( $refunds as $refund ){

		    		// retrieve refund data
		    		$refund_data = $refund->get_data();

		    		// process the refund as a secondary transaction
		    		// This mimicks the main transaction, taking from the refund object where sensible
		    		$refund_id = $refund->get_id();
		    		$refund_title = sprintf( __( 'Refund against transaction #%s', 'zero-bs-crm'), $order_id );
		    		$refund_description = $refund_title . "\r\n" . __( 'Reason: ', 'zero-bs-crm' ) . $refund_data['reason'];
		    		$refund_date_uts = strtotime( $refund_data['date_created']->__toString() ); 		
		    		if ( isset( $refund_data['currency'] ) && !empty( $refund_data['currency'] ) ){
		    			$refund_currency = $refund_data['currency'];
		    		} else {
		    			$refund_currency = $order_currency;
		    		}

		    		$refund_transaction = array(

				        'ref'                    => $refund_id,
				        'type'                   => __( 'Refund', 'zero-bs-crm' ),
				        'title'                  => $refund_title,
				        'status'                 => __( 'Refunded', 'zero-bs-crm' ),
				        'total'                  => -$refund_data['total'],
				        'desc'                   => $refund_description,
				        'date'                   => $refund_date_uts,
				        'created'                => $refund_date_uts,
				        'date_completed'         => $transaction_completed_date_uts,
				        'date_paid'              => $transaction_paid_date_uts,
				        'externalSources'        => array(array(
				            'source' => 'woo',
				            'uid'    => $refund_id, // rather than order_id, here we use the refund item id 
				            'origin' => $origin,
					        'owner'  => 0 // for now we hard-type no owner to avoid ownership issues. As we roll out fuller ownership we may want to adapt this.
				        )),
				        'currency'               => $refund_currency,
				        'net'                    => -( $refund_data['total'] - $refund_data['discount_total'] - $refund_data['total_tax'] - $refund_data['shipping_total'] ),
				        'tax'                    => $refund_data['total_tax'],
				        'fee'                    => 0,
				        'discount'               => $refund_data['discount_total'],
				        'shipping'               => $refund_data['shipping_total'],
				        'existence_check_args'   => array(
					        'externalSource'      => 'woo',
					        'externalSourceUID'   => $refund_id,
					        'origin'              => $origin,
					        'onlyID'              => true
					    ),
				        'lineitems'              => array(
				        	// here we roll a single refund line item
				        	array(
				                'order'    => $refund_id,
				                'currency' => $refund_currency,
				                'quantity' => 1,
				                'price'    => -$refund_data['total'],
				                'total'    => -$refund_data['total'],
				                'title'    => $refund_title,
				                'desc'     => $refund_description,
				                'tax'      => $refund_data['total_tax'],
				                'shipping' => 0,
				            )
				        ),
				        'extra_meta'             => array() // this is caught to insert as extraMeta

				    );

				    // Add any extra meta we can glean in case future useful:
				    $refund_transaction['extra_meta']['order_id'] = $order_id; // backtrace
				    if ( isset( $refund_data['refunded_by'] ) && !empty( $refund_data['refunded_by'] ) ){
				    	$refund_transaction['extra_meta']['refunded_by'] = $refund_data['refunded_by'];
				    }
				    if ( isset( $refund_data['refunded_payment'] ) && !empty( $refund_data['refunded_payment'] ) ){
				    	$refund_transaction['extra_meta']['refunded_payment'] = $refund_data['refunded_payment'];
				    }

		    		// add it to the stack
		    		$data['secondary_transactions'][] = $refund_transaction;

		    	}


		    }

		}
	    
	    // ==== Invoice
	    $data['invoice'] = array();
	    if ( $settings['wcinv'] == 1 ) {

			$data['invoice'] = array(
			    'id_override'       => $order_id,
			    'status'            => $invoice_status,
			    'currency'          => $order_currency,
			    'date'              => $invoice_creation_date_uts,
			    'due_date'          => $invoice_creation_date_uts,
			    'total'             => $order_data['total'],
			    'discount'          => $order_data['discount_total'],
			    'discount_type'     => 'm',
			    'shipping'          => $order_data['shipping_total'],
			    'shipping_tax'      => $order_data['shipping_tax'],
			    'tax'               => $order_data['total_tax'],
			    'ref'               => $item_title,
			    'hours_or_quantity' => 1,
			    'lineitems'         => $data['lineitems'],
			    'created'           => $invoice_creation_date_uts,
			    'externalSources'   => array(array(
			        'source' => 'woo',
			        'uid'    => $order_id,
	            	'origin' => $origin,
		           	'owner'  => 0 // for now we hard-type no owner to avoid ownership issues. As we roll out fuller ownership we may want to adapt this.
			    )),
			    'existence_check_args' => $data['source'],
			    'extra_meta'        => array(
			        'order_id' => $order_id,
			        'api'      => $from_api,
			    ));

	        // tags (invoice)
	        if ( $tag_invoice_with_item ) {	            

		        $data['invoice']['tags']     = $order_tags;
		        $data['invoice']['tag_mode'] = 'append';

	        }

	    }

	    return $data;
	}


	/**
	 * Translates an API order into an import-ready crm objects array
	 *  previously `tidy_order_from_api`
	 *
	 * @param $order
	 *
	 * @return array of various objects (contact|company|transaction|invoice)
	 */
	public function woocommerce_api_order_to_crm_objects( $order, $origin = '' ){

	    // $order_status is the WooCommerce order status
		$settings = $this->settings();
		$tag_with_coupon = false;
	    $tag_product_prefix = ( isset( $settings['wctagproductprefix'] ) ) ? zeroBSCRM_textExpose( $settings['wctagproductprefix'] ) : '';
	    $tag_coupon_prefix = zeroBSCRM_textExpose( $settings['wctagcouponprefix'] );
	    if ( $settings['wctagcoupon'] == 1 ) {

	        $tag_with_coupon = true;

	    }

	    // Translate API order into local order equivalent
	    $order_data = array(

	        'status'         => $order->status,
	        'currency'       => $order->currency,
	        'date_created'   => $order->date_created_gmt,
	        'customer_id'    => 0, // will be 0 from the API.
	        'billing'        => array(
	            'company'    => $order->billing->company,
	            'email'      => $order->billing->email,
	            'first_name' => $order->billing->first_name,
	            'last_name'  => $order->billing->last_name,
	            'address_1'  => $order->billing->address_1,
	            'address_2'  => $order->billing->address_2,
	            'city'       => $order->billing->city,
	            'state'      => $order->billing->state,
	            'postcode'   => $order->billing->postcode,
	            'country'    => $order->billing->country,
	            'phone'      => $order->billing->phone,
	        ),
	        'shipping'       => array(
	            'address_1' => $order->shipping->address_1,
	            'address_2' => $order->shipping->address_2,
	            'city'      => $order->shipping->city,
	            'state'     => $order->shipping->state,
	            'postcode'  => $order->shipping->postcode,
	            'country'   => $order->shipping->country,
	        ),
	        'total'          => $order->total,
	        'discount_total' => $order->discount_total,
	        'shipping_total' => $order->shipping_total,
	        'shipping_tax'   => $order->shipping_tax,
	        'total_tax'      => $order->total_tax,

	    );

	    $order_line_items = array();
	    $order_tags       = array();
	    $item_title       = '';

	    // cycle through line items and process
	    foreach ( $order->line_items as $line_item_key => $line_item ) {

	        if ( empty( $item_title ) ) {

	            $item_title = $line_item->name;

	        } else {

	            $item_title = __( 'Multiple Items', 'zero-bs-crm' );

	        }

	        $order_line_items[] = array(
	            'order'    => $order->id,
	            'quantity' => $line_item->quantity,
	            'price'    => $line_item->price,
	            'currency' => $order_data['currency'],
	            'total'    => $line_item->subtotal,
	            'title'    => $line_item->name,
	            'desc'     => $line_item->name . ' (#' . $line_item->product_id . ')',
	            'tax'      => $line_item->total_tax,
	            'shipping' => 0,
	        );
	        
	        if ( !in_array( $line_item->name, $order_tags ) ){
	        	
	        	$order_tags[] = $tag_product_prefix . $line_item->name;

	        }
	    }

	    // catch coupon_lines and tag if tagging
	    // https://woocommerce.github.io/woocommerce-rest-api-docs/v3.html?php#coupon-lines-properties
        if ( $tag_with_coupon && isset( $order->coupon_lines ) ) {
        
        	foreach ( $order->coupon_lines as $coupon_line ) {
	            
	            $order_tags[] = $tag_coupon_prefix . $coupon_line->code;

	        }

	    }

	    // Finally translate through `woocommerce_order_to_crm_objects` with the argument `$from_api = true` so it skips local store parts of the process
	    return $this->woocommerce_order_to_crm_objects( 
	    	$order_data,
	    	$order,
	    	$order->id,
	    	$order_line_items,
	    	$item_title,
	    	true,
	    	$order_tags,
	    	$origin
	    );

	}


	/**
	 * Translates a WooCommerce order status to a CRM contact resultant status
	 *  previously `apply_status`
	 *
	 * @param string $order_status
	 *
	 * @return string contact status
	 */
	public function woocommerce_order_status_to_contact_status( $order_status ){

	    // $order_status is the WooCommerce order status
		$settings = $this->settings();

	    // retrieve default status
	    $default_status = zeroBSCRM_getSetting( 'defaultstatus' );

	    // mappings
	    $zeroBSCRM_orderTosetting = array(
	        'completed'  => 'wccompleted',
	        'on-hold'    => 'wconhold',
	        'cancelled'  => 'wccancelled',
	        'processing' => 'wcprocessing',
	        'refunded'   => 'wcrefunded',
	        'failed'     => 'wcfailed',
	        'pending'    => 'wcpending',
	    );

	    // get the mappings setting from woocommerce
	    if ( array_key_exists( $order_status, $zeroBSCRM_orderTosetting ) && isset( $settings[$zeroBSCRM_orderTosetting[$order_status]] ) ){

	        $order_status = $settings[$zeroBSCRM_orderTosetting[$order_status]];

	    } else {

	        $order_status = $default_status;

	    }

	    if ( !isset($order_status) || $order_status == -1 ) {

	        return $default_status;

	    } else {

	        return $order_status;

	    }

	}


	/**
	 * Update contact address of a wp user (likely WooCommerce user)
	 *
	 * @param int $user_id (WordPress user id)
	 * @param string $address_type (e.g. `billing`)
	 */
	public function update_contact_address_from_wp_user( $user_id = -1, $address_type = 'billing' ){

		global $zbs;

		// retrieve contact ID from WP user ID
		$contact_id 	= $zbs->DAL->contacts->getContact(array(
			'WPID'      => $user_id,
			'onlyID'    => true
		));

		if ( $contact_id > 0 ){

			// retrieve customer data from WP user ID
			$woo_customer_meta 	= get_user_meta( $user_id );
			
			if ( $address_type == 'billing' ){

				$data = array(
						'addr1' 	=> $woo_customer_meta['billing_address_1'][0],
						'addr2' 	=> $woo_customer_meta['billing_address_2'][0],
						'city' 		=> $woo_customer_meta['billing_city'][0],
						'county' 	=> $woo_customer_meta['billing_state'][0],
						'country' 	=> $woo_customer_meta['billing_country'][0],
						'postcode' 	=> $woo_customer_meta['billing_postcode'][0],
				);

			} else {
			
				$data = array(
						'secaddr1' 		=> $woo_customer_meta['shipping_address_1'][0],
						'secaddr2' 		=> $woo_customer_meta['shipping_address_2'][0],
						'seccity' 		=> $woo_customer_meta['shipping_city'][0],
						'seccounty' 	=> $woo_customer_meta['shipping_state'][0],
						'seccountry' 	=> $woo_customer_meta['shipping_country'][0],
						'secpostcode' 	=> $woo_customer_meta['shipping_postcode'][0],
				);
			
			}

			// addUpdate as limited fields
			$limited_fields_array = array();
			foreach ( $data as $k => $v ){

				$limited_fields_array[] = array(

					'key' => 'zbsc_' .$k,
					'val' => $v,
					'type'=> '%s'

				);

			}

			// then addUpdate
			$zbs->DAL->contacts->addUpdateContact(array(

				'id'             => $contact_id,
				'limitedFields'  => $limited_fields_array

			));

		}

	}


	/**
	 * Attempts to return the percentage completed of a sync
	 *
	 * @return int|bool - percentage completed, or false if not attainable
	 */
	public function percentage_completed() {

		// retrieve working page
		$page_no = $this->resume_from_page();

		// account for our forward stepping
		$page_no = $page_no - 1;

		// could probably abstract the retrieval of orders for more nesting. For now it's fairly DRY as only in 2 places.

		// store/api switch
		if ( $this->import_mode() == JPCRM_WOO_SYNC_MODE_API ) {

			// API
			try {

				// get client
				$woocommerce = $this->get_woocommerce_client();

				// retrieve orders
				// https://woocommerce.github.io/woocommerce-rest-api-docs/v3.html?php#parameters
				$orders = $woocommerce->get(
					'orders',
					array(
						'page'  => 1,
						'per_page' => $this->orders_per_page,
					)
				);

				// retrieve page count from headers:
				$last_response           = $woocommerce->http->getResponse();
				$response_headers        = $last_response->getHeaders();
				$total_pages             = $response_headers['X-WP-TotalPages'];

				// calculate completeness
				$percentage_completed = 0;
				if ( $page_no > 0 && $total_pages > 0 ) {

					$percentage_completed = (int)( $page_no / $total_pages * 100 );

				}

			} catch ( HttpClientException $e ) {

				// failed to connect
				return false;

			} catch ( Missing_Settings_Exception $e ) {

				// missing settings means couldn't load lib.
				return false;

			}


		} else {

			// Local store

			// Where we're trying to run without WooCommerce, fail.
			if ( !function_exists( 'wc_get_orders' ) ) {

				$this->debug( 'Unable to return percentage completed as it appears WooCommerce is not installed.' );

			} else {

				// retrieve orders (just to get total page count (≖_≖ ))
				$orders = wc_get_orders(
					array(
						'limit'    => 1, // no need to retrieve more than one order here
						'paged'    => 1,
						'paginate' => true,
					)
				);

				$this->debug( 'Percentage completed: Page no ' . $page_no . ' / ' . $orders->max_num_pages );

				// calculate completeness
				$percentage_completed = 0;
				if ( $page_no > 0 && $orders->max_num_pages > 0 ) {

						$percentage_completed = $page_no / $orders->max_num_pages * 100;

				}
				$this->debug( 'Percentage completed: ' . $percentage_completed . '%' );

			}

		}

		// return
		if ( $percentage_completed >= 0 ) {

			return $percentage_completed;

		}

		return false;

	}


	/**
	 * Filter contact data passed through the woo checkout
	 * .. allows us to hook in support for things like WooCommerce Checkout Field Editor
	 *
	 * @param array $field_key
	 * @param array $field_value
	 * @param array $contact_data
	 * @param array $order - WooCommerce order object passed down
	 * @param array $custom_fields - CRM Contact custom fields details
	 * 
	 * @return array ($contact_data potentially modified)
	 */
	private function filter_checkout_contact_fields( $field_key, $field_value, $contact_data, $order, $custom_fields ) {

	    // Checkout Field Editor custom fields support, (where installed)
	    // https://woocommerce.com/products/woocommerce-checkout-field-editor/
	    if ( function_exists( 'wc_get_custom_checkout_fields' ) ) {
	    	
	    	$contact_data = $this->checkout_field_editor_filter_field( $field_key, $field_value, $contact_data, $order, $custom_fields );
	    
	    }


	    // Checkout Field Editor Pro custom fields support, (where installed)
	    // https://wordpress.org/plugins/woo-checkout-field-editor-pro/
	    if ( class_exists( 'THWCFD' ) ) {
	    	
	    	$contact_data = $this->checkout_field_editor_pro_filter_field( $field_key, $field_value, $contact_data, $order, $custom_fields );
	    
	    }

	    
	    return $contact_data;

	}


	/**
	 * Filter to add Checkout Field Editor custom fields support, where installed
	 * https://woocommerce.com/products/woocommerce-checkout-field-editor/
	 *
	 * @param array $field_key
	 * @param array $field_value
	 * @param array $contact_data
	 * @param array $order - WooCommerce order object passed down
	 * @param array $custom_fields - CRM Contact custom fields details
	 * 
	 * @return array ($contact_data potentially modified)
	 */
	private function checkout_field_editor_filter_field( $field_key, $field_value, $contact_data, $order, $custom_fields ) {

	    // Checkout Field Editor custom fields support, (where installed)
	    if ( function_exists( 'wc_get_custom_checkout_fields' ) ) {

	    	// get full fields
	    	$fields_info = wc_get_custom_checkout_fields( $order );

	    	// catch specific cases
	    	if ( isset( $fields_info[ $field_key ] ) ){

	    		// format info from Checkout Field Editor
	    		$field_info = $fields_info[ $field_key ];

	    		switch ( $field_info['type'] ){

	    			// multiselect
	    			case 'multiselect':

	    				// here the value will be a csv with extra padding (spaces we don't store)
	    				$contact_data[ $field_key ] = str_replace( ', ', ',', $field_value );

	    				break;

	    			// checkbox, singular
	    			case 'checkbox':

	    				// here the value will be 1 if it's checked, 
	    				// but in CRM we only have 'checkboxes' plural, so here we convert '1' to a checked matching box
	    				// Here if checked, we'll check the first available checkbox
	    				if ( $field_value == 1 ){

	    					// get value
	    					if ( isset( $custom_fields[ $field_key ] ) ){

	    						$fields_csv = $custom_fields[ $field_key ][2];
	    						if ( strpos( $fields_csv, ',' ) ){
	    							$field_value = substr( $fields_csv, 0, strpos( $fields_csv, ',' ) );
	    						} else {
	    							$field_value = $fields_csv;
	    						}

	    					}

	    					$contact_data[ $field_key ] = $field_value;

	    				}


	    				break;

	    		}


	    	}


	    }

	    return $contact_data;

	}

	
	/**
	 * Filter to add Checkout Field Editor Pro (Checkout Manager) for WooCommerce support, where installed
	 * https://wordpress.org/plugins/woo-checkout-field-editor-pro/
	 *
	 * @param array $field_key
	 * @param array $field_value
	 * @param array $contact_data
	 * @param array $order - WooCommerce order object passed down
	 * @param array $custom_fields - CRM Contact custom fields details
	 * 
	 * @return array ($contact_data potentially modified)
	 */
	private function checkout_field_editor_pro_filter_field( $field_key, $field_value, $contact_data, $order, $custom_fields ) {

	    // Checkout Field Editor custom fields support, (where installed)
	    if ( class_exists( 'THWCFD' ) ) {

			// see if we have a matching custom field to infer type conversions from:
			if ( isset( $custom_fields[ $field_key ] ) ){

				// switch on type
				switch ( $custom_fields[ $field_key ][0] ){

	    			// checkbox, singular
	    			case 'checkbox':

	    				// here the value will be 1 if it's checked, 
	    				// but in CRM we only have 'checkboxes' plural, so here we convert '1' to a checked matching box
	    				// Here if checked, we'll check the first available checkbox
	    				if ( $field_value == 1 ){

	    					// get value
	    					if ( isset( $custom_fields[ $field_key ] ) ){

	    						$fields_csv = $custom_fields[ $field_key ][2];
	    						if ( strpos( $fields_csv, ',' ) ){
	    							$field_value = substr( $fields_csv, 0, strpos( $fields_csv, ',' ) );
	    						} else {
	    							$field_value = $fields_csv;
	    						}

	    					}

	    					$contact_data[ $field_key ] = $field_value;

	    				}


	    				break;

				}

			}			

	    }

	    return $contact_data;

	}


	/**
	 * Filter to add WooCommerce Checkout Add-ons fields support, where installed
	 * https://woocommerce.com/products/woocommerce-checkout-add-ons/
	 *
	 * @param array $order_id - WooCommerce order id
	 * @param array $contact_data
	 * @param array $custom_fields - CRM Contact custom fields details
	 * 
	 * @return array ($contact_data potentially modified)
	 */
	private function checkout_add_ons_add_field_values( $order_id, $contact_data, $custom_fields ) {

		global $zbs;

	    // WooCommerce Checkout Add-ons fields support, where installed
	    if ( function_exists( 'wc_checkout_add_ons' ) ) {

	    	$checkout_addons_instance = wc_checkout_add_ons();
	    	$field_values = $checkout_addons_instance->get_order_add_ons( $order_id );
	    	
	    	// Add any fields we have saved in Checkout Add-ons,
	    	// note this overrides any existing values, if conflicting
	    	if ( is_array( $field_values ) ){

	    		/* Example
	    		    Array(
		    		    [de22a81] => Array
				        (
				            [name] => tax-id-2
				            [checkout_label] => tax-id-2
				            [value] => 999
				            [normalized_value] => 999
				            [total] => 0
				            [total_tax] => 0
				            [fee_id] => 103
				        )
				    )
			    */

	    		foreach ( $field_values as $checkout_addon_key => $checkout_addon_field ){

	    			$field_key = $zbs->DAL->makeSlug( $checkout_addon_field['name'] );

	    			// brutal addition/override of any fields passed
	    			$contact_data[ $field_key ] = $checkout_addon_field['value'];

	    			// all array-type values (multi-select etc.) can be imploded for our storage:
	    			// multiselect, multicheckbox
	    			if ( is_array( $contact_data[ $field_key ] ) ){

	    				// note we used `normalized_value` not `value`, because that matches our custom field storage
	    				// ... e.g. "Blue" = `normalized_value`, "blue" = value (but we store case)
	    				$contact_data[ $field_key ] = implode( ',', $checkout_addon_field['normalized_value'] );

	    			}

	    			// see if we have a matching custom field to infer type conversions from:
    				if ( isset( $custom_fields[ $field_key ] ) ){

    					// switch on type
    					switch ( $custom_fields[ $field_key ][0] ){

			    			// Select, radio
			    			case 'select':
			    			case 'radio':

			    				// note we used `normalized_value` not `value`, because that matches our custom field storage
			    				// ... e.g. "Blue" = `normalized_value`, "blue" = value (but we store case)
			    				$contact_data[ $field_key ] = $checkout_addon_field['normalized_value'];

			    				break;

			    			// checkbox, singular
			    			case 'checkbox':

			    				// here the value will be 1 if it's checked, 
			    				// but in CRM we only have 'checkboxes' plural, so here we convert '1' to a checked matching box
			    				// Here if checked, we'll check the first available checkbox
			    				if ( $contact_data[ $field_key ] == 1 ){

			    					// get value
			    					if ( isset( $custom_fields[ $field_key ] ) ){

			    						$fields_csv = $custom_fields[ $field_key ][2];
			    						if ( strpos( $fields_csv, ',' ) ){
			    							$contact_data[ $field_key ] = substr( $fields_csv, 0, strpos( $fields_csv, ',' ) );
			    						} else {
			    							$contact_data[ $field_key ] = $fields_csv;
			    						}

			    					}

			    				}

			    				break;

    					}

    				}


	    		}

	    	}
	    
	    
	    }

	    return $contact_data;

	}

  


	/**
	 * Catches trashing of WooCommerce orders and (optionally) removes transactions from CRM
	 *
	 * @param int $post_id
	 */
	public function woocommerce_order_trashed( $post_id ){

		// retrieve action
	    $delete_action = $this->woosync()->settings->get( 'auto_trash', false );

	    // action?
	    if ( $delete_action == 'do_nothing'){
	    	return;			
	    }	    

	    // act
	    $this->woocommerce_order_removed( $post_id, $delete_action );

	}

	/**
	 * Catches deletion of WooCommerce orders and (optionally) removes transactions from CRM
	 *
	 * @param int $post_id
	 */
	public function woocommerce_order_deleted( $post_id ){

		// retrieve action
	    $delete_action = $this->woosync()->settings->get( 'auto_delete', false );

	    // action?
	    if ( $delete_action == 'do_nothing'){
	    	return;			
	    }	

	    // act
	    $this->woocommerce_order_removed( $post_id, $delete_action );

	}
	

	/**
	 * Catches deletion of WooCommerce orders and (optionally) removes transactions from CRM
	 *
	 * @param int $post_id
	 * @param str $delete_action
	 */
	private function woocommerce_order_removed( $post_id, $delete_action ){

		global $zbs;

		// was it an order that was deleted?
	    $post_type = get_post_type( $post_id );
	    if ( $post_type !== 'shop_order' ) {
	        return;
	    }

	    // catch default
	    if ( empty( $delete_action ) ){
	    	$delete_action = 'change_status';
	    }

	    // retrieve order
		$order = wc_get_order( $post_id );

		// descern order_id
		$order_id = $order->get_id();
		if ( method_exists( $order, 'get_order_number' ) ){
			$order_id = $order->get_order_number();
		}

		// get transaction
		$transaction_id = $this->woosync()->get_transaction_from_order_id( $order_id, '', true );

		if ( $transaction_id > 0 ){

			// retrieve any associated invoices
			$invoice_id = $zbs->DAL->transactions->get_transaction_invoice_id( $transaction_id );

			// act
		    switch ( $delete_action ){

		    	// change the transaction (and invoice) status to 'Deleted'
		    	case 'change_status':

		    		// set status
		    		$zbs->DAL->transactions->setTransactionStatus( $transaction_id, __( 'Deleted', 'zero-bs-crm' ) );

		    		// Also change the status on any woo-created associated invoice
		    		if ( $invoice_id > 0 ){
		    			$zbs->DAL->invoices->setInvoiceStatus( $invoice_id, __( 'Deleted', 'zero-bs-crm' ) );
		    		}

		    		break;

		    	// Delete the transaction (and invoice) and add log to contact
		    	case 'hard_delete_and_log':

		    		// delete transaction
		    		$zbs->DAL->transactions->deleteTransaction( array(
			            'id'            => $transaction_id
			        ));

		    		// Also delete any woo-created associated invoice
		    		if ( $invoice_id > 0 ){
		    			$zbs->DAL->invoices->deleteInvoice( array(
				            'id'            => $invoice_id
				        ));
		    		}

			        // get contact(s) to add log to
			        // only 1:1 via ui currently, but is support for many in DAL
			        $contacts = $zbs->DAL->transactions->get_transaction_contacts( $transaction_id );

			        if ( is_array( $contacts ) ){
			        
			        	foreach ( $contacts as $contact ){

				    		// add log
							$zbs->DAL->logs->addUpdateLog(array(

								'data'			=> array(

									'objtype' 	=> ZBS_TYPE_CONTACT,
									'objid' 	=> $contact['id'],
									'type' 		=> 'transaction_deleted',
									'shortdesc' => __( 'WooCommerce Order Deleted', 'zero-bs-crm'),
									'longdesc' 	=> sprintf( __( 'Transaction #%s was removed from your CRM after the related WooCommerce order #%s was deleted.', 'zero-bs-crm'), $transaction_id, $order_id )

								)

							));


			        	}

			        }
		    		

		    		break;


		    }		    

		}

	}


}