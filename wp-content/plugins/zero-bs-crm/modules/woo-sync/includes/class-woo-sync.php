<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 *
 * WooSync
 *
 */
namespace Automattic\JetpackCRM;

// block direct access
defined( 'ZEROBSCRM_PATH' ) || exit;

#} the WooCommerce API
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

/**
 * WooSync class
 */
class Woo_Sync {


	/**
	 * Extension version.
	 *
	 * @var string
	 */
	public $version = '5.0';

	/**
	 * Extension settings key
	 *
	 * @var string
	 */
	public $config_key = 'woosync';

	/**
	 * Extension name.
	 *
	 * @var string
	 */
	public $ext_name = 'WooSync';

	/**
	 * Maximum number of WooCommerce products to retrieve into CRM product index
	 *
	 * @var int
	 */
	public $max_woo_product_index = 100;

	/**
	 * Settings object
	 *
	 * @var \WHWPConfigExtensionsLib | null
	 */
	public $settings = null; 

	/**
	 * Show extension settings tab
	 *
	 * @var string
	 */
	public $settings_tab = true;

	/**
	 * Feature class object: Background Sync
	 *
	 * @var Woo_Sync_Background_Sync | null
	 */
	public $background_sync = null;

	/**
	 * Feature class object: Contact Tabs
	 *
	 * @var Woo_Sync_Contact_Tabs | null
	 */
	public $contact_tabs = null;

	/**
	 * Feature class object: My Account Integration
	 *
	 * @var Woo_Sync_My_Account_Integration | null
	 */
	public $my_account = null;

	/**
	 * Feature class object: Woo Admin UI modifications
	 *
	 * @var Woo_Sync_Woo_Admin_Integration | null
	 */
	public $woo_ui = null;

	/**
	 * Feature class object: WooSync Segment Conditions
	 *
	 * @var Woo_Sync_Segment_Conditions | null
	 */
	public $segment_conditions = null;

	/**
	 * The single instance of the class.
	 */
	protected static $_instance = null;

	/**
	 * Slugs for internal pages
	 *
	 * @var array()
	 */
	public $slugs = array(
		'hub'                 => 'woo-sync-hub',
		'settings'            => 'woosync',
		'settings_connection' => 'connection',
	);

	/**
	 * URLs that the Woo module uses
	 *
	 * @var array()
	 */
	public $urls = array(
		'kb-woo-api-keys'   => 'https://kb.jetpackcrm.com/knowledge-base/getting-your-woocommerce-api-key-and-secret/',
		'kb-woo-map-status' => 'https://kb.jetpackcrm.com/knowledge-base/woosync-imported-all-customers-as-lead-status/',
	);

	/**
	 * Setup WooSync
	 * Note: This will effectively fire after core settings and modules loaded
	 * ... effectively on tail end of `init`
	 */
	public function __construct( ) {

		// Definitions
		$this->definitions();

		// Initialise endpoints
		$this->init_endpoints();

		// Initialise Settings
		$this->init_settings();
		
		// Initialise Features
		$this->init_features();

		// Initialise Hooks
		$this->init_hooks();

		// Add Filter buttons
		$this->include_filter_buttons();

		// Autoload page AJAX
		$this->load_ajax();

		// Register frontend/backend styles and scripts
		$this->register_styles_scripts();

	}


	/**
	 * Main Class Instance.
	 *
	 * Ensures only one instance of Woo_Sync is loaded or can be loaded.
	 *
	 * @since 2.0
	 * @static
	 * @see 
	 * @return Woo_Sync main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	/**
	 * Define any key vars.
	 */
	private function definitions(){

		define( 'JPCRM_WOO_SYNC_MODE_LOCAL', 0 );
		define( 'JPCRM_WOO_SYNC_MODE_API',   1 );

	}


	/**
	 * Initialise endpoints
	 *  (previously on `init`)
	 */
	private function init_endpoints( ) {

		add_rewrite_endpoint('invoices', EP_PAGES );

	}

	/**
	 * Initialise Settings
	 */
	private function init_settings( ) {
		
		$this->settings = new \WHWPConfigExtensionsLib( $this->config_key, $this->default_settings() );

	}

	/**
	 * Initialise Hooks
	 */
	private function init_hooks( ) {

		/*
		Welcome wizard
		This needs to fire after the object is set in $zbs->modules->woosync,
		because its slugs are used in the welcome wizard.
		*/
		add_action( 'admin_init', array( $this, 'show_welcome_wizard' ), 100 );

		// Add settings tab
		add_filter( 'zbs_settings_tabs', array( $this, 'add_settings_tab' ) );

		// Menus:

		// Adds Tools menu subitem
		add_filter( 'zbs-tools-menu', array( $this, 'add_tools_menu_sub_item_link' ) );
		// Learn menu
		add_action( 'wp_after_admin_bar_render', array( $this, 'render_learn_menu'), 12 );
		// Admin menu
		add_filter( 'zbs_menu_wpmenu', array( $this, 'add_wp_pages' ), 10, 1 );


		// JPCRM effecting:

		// Add Woo related info to CRM external source infobox
		add_filter( 'zbs_external_source_infobox_line', array( $this, 'override_crm_external_source_infobox' ), 10, 2 );

		// Pay invoice via WooCommerce checkout button
		add_filter( 'zbs_woo_pay_invoice', array( $this, 'render_pay_via_woo_checkout_button' ), 20 );

		// Hook in to Contact, Invoice, and Transaction query generation and add the quickfilter
		add_filter( 'jpcrm_contact_query_quickfilter', array( $this, 'contact_query_quickfilter_addition' ), 10, 2 );
		add_filter( 'jpcrm_invoice_query_quickfilter', array( $this, 'invoice_query_quickfilter_addition' ), 10, 2 );
		add_filter( 'jpcrm_transaction_query_quickfilter', array( $this, 'transaction_query_quickfilter_addition' ), 10, 2 );

		// Hook in to new contact log creation and add string manipulation
		add_filter( 'jpcrm_new_contact_log', array( $this, 'new_contact_log_override' ), 10, 3 );
		
		// Product index
		// #follow-on-refinements
		// add_filter( 'zbs_invpro_productindex', array( $this, 'append_woo_products_to_crm_product_index' ), 10, 1 );

	}

	/**
	 * Initialise Features
	 */
	private function init_features( ) {

		global $zbs;

		// Contact Tabs
		if ( $zbs->isDAL2() && zeroBSCRM_is_customer_view_page() ){

			require_once( JPCRM_WOO_SYNC_ROOT_PATH . 'includes/jpcrm-woo-sync-contact-tabs.php' );
			$this->contact_tabs = Woo_Sync_Contact_Tabs::instance();
			wp_enqueue_style( 'jpcrm-woo-sync-contact-tabs', plugins_url( '/css/jpcrm-woo-sync-contact-tabs.css', JPCRM_WOO_SYNC_ROOT_FILE ) );

		}

		// Settings page
		if ( jpcrm_is_settings_page() ) {

			$this->load_admin_page( 'settings/router' );

		}

		// Hub page
		if ( $this->is_hub_page() ) {

			$this->load_admin_page( 'woo-sync-hub/main' );

		}

		// Background sync
		require_once JPCRM_WOO_SYNC_ROOT_PATH . 'includes/class-woo-sync-background-sync.php';
		$this->background_sync = Woo_Sync_Background_Sync::instance();

		// My account
		require_once JPCRM_WOO_SYNC_ROOT_PATH . 'includes/class-woo-sync-my-account-integration.php';
		$this->my_account = Woo_Sync_My_Account_Integration::instance();

		// WooCommerce UI additions
		require_once JPCRM_WOO_SYNC_ROOT_PATH . 'includes/class-woo-sync-woo-admin-integration.php';
		$this->woo_ui = Woo_Sync_Woo_Admin_Integration::instance();

		// Segment conditions
		require_once( JPCRM_WOO_SYNC_ROOT_PATH . 'includes/class-woo-sync-segment-conditions.php' );
		$this->segment_conditions = Woo_Sync_Segment_Conditions::instance();

	}


	/**
	 * Autoload page AJAX
	 */
	private function load_ajax( ) {

		$admin_page_directories = jpcrm_get_directories( JPCRM_WOO_SYNC_ROOT_PATH . 'admin' );

		if ( is_array( $admin_page_directories ) ){

			foreach ( $admin_page_directories as $directory ){

				$files = scandir( JPCRM_WOO_SYNC_ROOT_PATH . 'admin/' . $directory );
				
				if ( is_array( $files ) ){

					foreach ( $files as $file ){

						// find files `*.ajax.*`
						if ( strrpos( $file, '.ajax.' ) > 0 ){

							// load it
							require_once( JPCRM_WOO_SYNC_ROOT_PATH . 'admin/' . $directory . '/' . $file );

						}

					}

				}


			}

		}

	}


	/**
	 * Include WooCommerce REST API (well, in fact, autoload /vendor)
	 */
	public function include_woocommerce_rest_api(){

		require_once ZEROBSCRM_PATH .  'vendor/autoload.php';

	}


	/**
	 * Include filter buttons
	 * (Note, requires `contact_query_quickfilter_addition()` to be hooked into `jpcrm_contact_query_quickfilter`)
	 */
	public function include_filter_buttons(){

		global $zbs, $zeroBSCRM_filterbuttons_customer;

		// Add 'is woo customer' filter button to 'all options' for contact
  		$zeroBSCRM_filterbuttons_customer['all']['woo_customer'] = array( __( 'WooCommerce', 'zero-bs-crm' ) );

  		// get current list view filters
        $custom_views = $zbs->settings->get( 'customviews2' );

  		// If we've only just activated WooSync,
  		// we add the customer filter button to the users selected filters by default (once)
  		if ( !isset( $custom_views['customer_filters']['woo_customer'] ) && !$this->settings->get( 'has_added_woofilter', false ) ){

  			// add in our filter
  			$custom_views['customer_filters']['woo_customer'] = array( __( 'WooCommerce', 'zero-bs-crm' ) );

  			// save
			$zbs->settings->update( 'customviews2', $custom_views );

			// flag so we don't keep re-adding if user removes from selection
  			$this->settings->update( 'has_added_woofilter', true );

  		}

  		// ... we also add the transaction filter button to the users selected filters by default (once)
  		if ( !isset( $custom_views['transaction_filters']['woo_transaction'] ) && !$this->settings->get( 'has_added_woo_transaction_filter', false ) ){

  			// add in our filter
  			$custom_views['transaction_filters']['woo_transaction'] = array( __( 'WooCommerce', 'zero-bs-crm' ) );

  			// save
			$zbs->settings->update( 'customviews2', $custom_views );

			// flag so we don't keep re-adding if user removes from selection
  			$this->settings->update( 'has_added_woo_transaction_filter', true );

  		}

  		// ... we also add the invoice filter button to the users selected filters by default (once)
  		if ( !isset( $custom_views['invoice_filters']['woo_invoice'] ) && !$this->settings->get( 'has_added_woo_invoice_filter', false ) ){

  			// add in our filter
  			$custom_views['invoice_filters']['woo_invoice'] = array( __( 'WooCommerce', 'zero-bs-crm' ) );

  			// save
			$zbs->settings->update( 'customviews2', $custom_views );

			// flag so we don't keep re-adding if user removes from selection
  			$this->settings->update( 'has_added_woo_invoice_filter', true );

  		}

	}


	/**
	 * Hook in to Contact query generation and add the quickfilter
	 * (Hooked into `jpcrm_contact_query_quickfilter`)
	 */
	public function contact_query_quickfilter_addition( $wheres, $quick_filter_key ) {

		global $ZBSCRM_t;

		// is a Woo customer? (Could be copied/generalised for other ext sources)
		if ( $quick_filter_key == 'woo_customer' ){
	        $wheres['is_woo_customer'] = array(
	            'ID','IN',
	            '(SELECT DISTINCT zbss_objid FROM ' . $ZBSCRM_t['externalsources'] . " WHERE zbss_objtype = " . ZBS_TYPE_CONTACT . " AND zbss_source = %s)",
	            array( 'woo' )
	        );
	    }

	    return $wheres;
	}


	/**
	 * Hook in to Invoice query generation and add the quickfilter
	 * (Hooked into `jpcrm_invoice_query_quickfilter`)
	 */
	public function invoice_query_quickfilter_addition( $wheres, $quick_filter_key ) {

		global $ZBSCRM_t;

		// is a Woo customer? (Could be copied/generalised for other ext sources)
		if ( $quick_filter_key == 'woo_invoice' ){
	        $wheres['is_woo_invoice'] = array(
	            'ID','IN',
	            '(SELECT DISTINCT zbss_objid FROM ' . $ZBSCRM_t['externalsources'] . " WHERE zbss_objtype = " . ZBS_TYPE_INVOICE . " AND zbss_source = %s)",
	            array( 'woo' )
	        );
	    }

	    return $wheres;
	}


	/**
	 * Hook in to Transaction query generation and add the quickfilter
	 * (Hooked into `jpcrm_transaction_query_quickfilter`)
	 */
	public function transaction_query_quickfilter_addition( $wheres, $quick_filter_key ) {

		global $ZBSCRM_t;

		// is a Woo customer? (Could be copied/generalised for other ext sources)
		if ( $quick_filter_key == 'woo_transaction' ){
	        $wheres['is_woo_transaction'] = array(
	            'ID','IN',
	            '(SELECT DISTINCT zbss_objid FROM ' . $ZBSCRM_t['externalsources'] . " WHERE zbss_objtype = " . ZBS_TYPE_TRANSACTION . " AND zbss_source = %s)",
	            array( 'woo' )
	        );
	    }

	    return $wheres;
	}



	/**
	 * Hook in to new contact log creation and add string manipulation
	 * (Hooked into `jpcrm_new_contact_log`)
	 */
	public function new_contact_log_override( $note_long_description, $source_key, $uid ) {

        if ( $source_key == 'woo' ){

			if ( !empty( $uid ) ){
            	$note_long_description = sprintf( __( 'Created from WooCommerce Order #%s', 'zero-bs-crm' ), $uid ) . ' <i class="fa fa-shopping-cart"></i>';
            } else {
            	$note_long_description = __( 'Created from WooCommerce Order', 'zero-bs-crm' ) . ' <i class="fa fa-shopping-cart"></i>';
            }

        }

	    return $note_long_description;
	}

	/**
	 * Register styles & scripts
	 *  (previously on `init`)
	 */
	public function register_styles_scripts() {

		// WooCommerce My Account
		wp_register_style( 'jpcrm-woo-sync-my-account', plugins_url( '/css/jpcrm-woo-sync-my-account'.wp_scripts_get_suffix().'.css', JPCRM_WOO_SYNC_ROOT_FILE ) );
		wp_register_style( 'jpcrm-woo-sync-fa', plugins_url( '/css/font-awesome.min.css', ZBS_ROOTFILE ) );

	}


	/**
	 * Intercept page load to send to Welcome Wizard
	 *  (previously on `admin_init 100`)
	 */
	public function show_welcome_wizard() {

		##WLREMOVE

		// Bail if activating from network, or bulk
		if ( wp_doing_ajax() || is_network_admin() || ! current_user_can( 'admin_zerobs_manage_options' ) ) {
			return;
		}

		// if on Woo Page
		if ( $this->is_hub_page() ){

			// check if user has been shown welcome wizard, if not show, otherwise skip
			$wizard_run_count = get_option( 'jpcrm_woo_connect_wizard_completions', 0 );
			if ( $wizard_run_count == 0 ){

				require_once( JPCRM_WOO_SYNC_ROOT_PATH . 'admin/activation/welcome-to-woo-sync.php' );
				exit();

			}
		}

		// Delete the redirect transient
		delete_transient( 'jpcrm_woosync_just_installed' );

	    ##/WLREMOVE

	}

	/**
	 * Filter settings tabs, adding this extension
	 *  (previously `load_settings_tab`)
	 *
	 * @param array $tabs
	 */
	public function add_settings_tab( $tabs ){
		
		// Append our tab if enabled
		if ( $this->settings_tab ) {
			$main_tab                     = $this->slugs['settings'];
			$connection_tab               = $this->slugs['settings_connection'];
			$tabs[ $main_tab ]            = array(
				'name' => $this->ext_name,
				'ico' => '',
				'submenu' => array(
					"{$main_tab}&subtab={$connection_tab}" => array(
						'name' => __( 'WooSync Connection', 'zero-bs-crm'),
						'ico'  => '',
					),
				),
			);
		}

		return $tabs;

	}


	/**
	 * Return default settings
	 */
	public function default_settings() {

		return require( JPCRM_WOO_SYNC_ROOT_PATH . 'includes/jpcrm-woo-sync-default-settings.php' );

	}


	/**
	 * Main page addition
	 */
	function add_wp_pages( $menu_array=array() ) {

		global $zbs;

		// Get the admin layout option 1 = Full, 2 = Slimline, 3 = CRM Only
		$menu_mode = zeroBSCRM_getSetting( 'menulayout' );

		// depending on layout option, we add sub items or main items:
		if ( $menu_mode === ZBS_MENU_SLIM ) {

			// add a sub toplevel item:
			$menu_array['zbs']['subitems']['woosync'] = array(
				'title'      => 'WooSync',
				'url'        => $this->slugs['hub'],
				'perms'      => 'admin_zerobs_manage_options',
				'order'      => 1,
				'wpposition' => 1,
				'callback'   => 'jpcrm_woosync_render_hub_page',
				'stylefuncs' => array( 'zeroBSCRM_global_admin_styles', 'jpcrm_woosync_hub_page_styles_scripts' ),
			);

		} else {

			// add a sub datatools item
			$menu_array['datatools']['subitems']['woosync'] = array(
				'ico'        => 'dashicons-admin-users',
				'title'      => 'WooSync',
				'url'        => $this->slugs['hub'],
				'perms'      => 'admin_zerobs_view_customers',
				'order'      => 10, 'wpposition' => 25,
				'subitems'   => array(),
				'callback'   => 'jpcrm_woosync_render_hub_page',
				'stylefuncs' => array( 'zeroBSCRM_global_admin_styles', 'jpcrm_woosync_hub_page_styles_scripts' ),
			);

		}

		return $menu_array;

	} 


	/**
	 * Adds Tools menu sub item
	 */
	public function add_tools_menu_sub_item_link( $menu_items ) {

		global $zbs;
		
		$menu_items[] = '<a href="' . zeroBSCRM_getAdminURL( $this->slugs['hub'] ) . '" class="item"><i class="shopping cart icon"></i> WooSync</a>';
		
		return $menu_items;

	}


	/**
	 * Output learn menu
	 */
	public function render_learn_menu(){

		if ( $this->is_hub_page() ){

			global $zbs;

			$learn_content = '<p>' . __( "Here you can import your WooCommerce data. It's important that you have setup the extension correctly, including setting the order statuses to map to contact statuses.", 'zerobscrm' ) . '</p>';
			$learn_content .= '<p>' . __( "If you do not set this up, all WooCommerce orders will be imported as contacts with default status (Lead). Hit import to get started or learn more about how to setup the extension.", 'zero-bs-crm' ) . '</p>';
			$image_url = JPCRM_WOO_SYNC_IMAGE_URL . 'learn/learn-woo-sync.png';
			
			// output
			$zbs->learn_menu->render_generic_learn_menu(
				'WooCommerce Sync',
				'',
				'',
				true,
				__( "Import WooCommerce History", "zerobscrm" ),
				$learn_content,
				$zbs->urls['woosync'],
				$image_url,
				false,
				''
			);


		}
	}

	/**
	 * Load the file for a given page
	 *
	 * @param string $page_name (e.g. `settings/main`)
	 */
	public function load_admin_page( $page_name ){
		
		jpcrm_load_admin_page( $page_name, JPCRM_WOO_SYNC_ROOT_PATH );

	}


	/**
	 * Append/override Woo related info to CRM external source infobox
	 *  (previously `transaction_to_order_link`)
	 *
	 * @param string $html
	 * @param array $external_source
	 */
	public function override_crm_external_source_infobox( $html, $external_source ) {

		global $zbs;
		
		if ( $external_source['source'] == 'woo' ){

			// retrieve origin info (where available)
			$origin_str = '';
			$origin_detail = $zbs->DAL->hydrate_origin( $external_source['origin'] );
			if ( is_array( $origin_detail ) && isset( $origin_detail['origin_type'] ) && $origin_detail['origin_type'] == 'domain' ){

				// clean the domain (at this point strip protocols)
				$clean_domain = $zbs->DAL->clean_external_source_domain_string( $origin_detail['origin'] );
				$origin_str = __( ' from ', 'zero-bs-crm' ) . '<span class="jpcrm-ext-source-domain">' . $clean_domain . '</span>';

			}

			switch ( $external_source['objtype'] ){

				case ZBS_TYPE_INVOICE:
				case ZBS_TYPE_TRANSACTION:

					// local (can show order link) or external (can't show order link)
					if ( $this->is_order_from_local_by_external_source( $external_source ) ){
					
						$html = '<div class="jpcrm-ext-source-woo-order">' . __( "Order", 'zero-bs-crm' ) . ' <span class="jpcrm-ext-source-uid">#' . $external_source['unique_id'] . '</span><a class="compact ui mini button right floated" href="' . esc_url( $this->woo_order_link( $external_source['unique_id'] ) ) . '" target="_blank">' . __( 'View Order', 'zero-bs-crm' ) . '</a></div>';
						
					} else {

						$html = '<div class="jpcrm-ext-source-woo-order">' . __( "Order", 'zero-bs-crm' ) . ' <span class="jpcrm-ext-source-uid">#' . $external_source['unique_id'] . '</span>' . $origin_str . '</div>';
						
					}

				break;

				case ZBS_TYPE_CONTACT:
				case ZBS_TYPE_COMPANY:

					// local (can show order link) or external (can't show order link)
					if ( $this->is_order_from_local_by_external_source( $external_source ) ){
					
						$html = '<div class="jpcrm-ext-source-woo-order">' . __( "Order", 'zero-bs-crm' ) . ' <span class="jpcrm-ext-source-uid">#' . $external_source['unique_id'] . '</span><a class="compact ui mini button right floated" href="' . esc_url( $this->woo_order_link( $external_source['unique_id'] ) ) . '" target="_blank">' . __( 'View Order', 'zero-bs-crm' ) . '</a></div>';
						
					} else {

						$html = '<div class="jpcrm-ext-source-woo-order">' . __( "Order", 'zero-bs-crm' ) . ' <span class="jpcrm-ext-source-uid">#' . $external_source['unique_id'] . '</span>' . $origin_str . '</div>';
						
					}

				break;

			}

		}
		
		return $html;

	}

	
	/**
	 * This checks an external source for 'origin', and if that matches local site url, returns true
	 * ... if origin is not recorded, this falls back to current setup mode (for users data pre refactor with origin (~v5))
	 *
	 * @param array $external_source
	 */
	public function is_order_from_local_by_external_source( $external_source ) {

		global $zbs;

		if ( is_array( $external_source ) && isset( $external_source['origin'] ) ){

			$origin_detail = $zbs->DAL->hydrate_origin( $external_source['origin'] );
			if ( $origin_detail['origin_type'] == 'domain' ){

				if ( $origin_detail['origin'] == site_url() ){
					return true;
				}

			}

		} else {

			// no origin, must be a pre-v5 recorded order
			// ... here we fall back to the current mode setting, which isn't ideal, but suffices
			$setup_type = $this->settings->get('wcsetuptype');
			if ( $setup_type == 0 ){
				return true;
			}


		}

		return false;

	}

	
	/**
	 * Return an admin_url for a WooCommerce order edit screen
	 *
	 * @param int $order_id
	 */
	public function woo_order_link( $order_id ) {

		return admin_url( 'post.php?post=' . $order_id . '&action=edit' );

	}


	/**
	 * Pay for invoice via WooCommerce checkout 
	 *  Intercepts pay button logic and adds pay via woo button
	 *  Does not do so for API-imported orders
	 *
	 * @param int $invoice_id
	 */
	public function render_pay_via_woo_checkout_button( $invoice_id = -1 ){

			if ( $invoice_id > 0 ){

				$api = $this->get_invoice_meta( $invoice_id, 'api' );
				$order_id = $this->get_invoice_meta( $invoice_id, 'order_id' );

				// intercept pay button and set to pay via woo checkout
				if ( empty( $api ) ){

						if ( !empty( $order_id ) ){

							remove_filter( 'invoicing_pro_paypal_button', 'zeroBSCRM_paypalbutton' , 1 );
							remove_filter( 'invoicing_pro_stripe_button', 'zeroBSCRM_stripebutton', 1 );
							$order	= wc_get_order( $order_id );
							$payment_page = $order->get_checkout_payment_url();
							$res = '<h3>' . __( "Pay Invoice", 'zero-bs-crm' ) . '</h3>';
							$res .= '<a href="' . esc_url( $payment_page ) . '" class="ui button btn">' . __( "Pay Now", 'zero-bs-crm' ) .'</a>';

							return $res;

						}
				}

				return $invoice_id;
				
			}

	} 



	/**
	 * Append WooCommerce products to CRM product index (used on invoice editor)
	 *  Applied via filter `zbs_invpro_productindex`
	 *
	 * @param array $crm_product_index
	 */
	public function append_woo_products_to_crm_product_index( $crm_product_index ){

		// Get Settings
		$setup_type = $this->settings->get('wcsetuptype');

		if ( $setup_type == 0 ){

			// Local store
			$woo_product_index = $this->get_product_list_via_local_store();

		} else {
			
			// From API-derived store
			$woo_product_index = $this->get_product_list_via_api();

		}

		// append to array
		if ( is_array( $woo_product_index ) && count( $woo_product_index ) > 0 ){

			$crm_product_index = array_merge( $woo_product_index, $crm_product_index );

		}

		return $crm_product_index;
	}


	/**
	 * Retrieve WooCommerce product list via API
	 */
	public function get_product_list_via_api(){

		$woo_product_index = array();

		try {

			// use Woo client library
			$woocommerce = $this->get_woocommerce_client(); 	

			// Set params
			$params = array( 'per_page' => $this->max_woo_product_index );
			$params = apply_filters( 'zbs-woo-product-list', $params );
			$product_list = $woocommerce->get( 'products', $params );

			// cycle through & simplify to match product index
			foreach ( $product_list as $product_data ){
				
				$index_line                = new \stdClass;
				$index_line->ID            = $product_data->id;
				$index_line->zbsprod_name  = $product_data->name;
				$index_line->zbsprod_desc  = wp_strip_all_tags($product_data->short_description);
				$index_line->zbsprod_price = $product_data->price;
			
				$woo_product_index[] = $index_line;

			}

		} catch ( HttpClientException $e ) {

			echo "<div class='ui message red'><i class='ui icon exclamation circle'></i> WooCommerce Client Error: " . print_r( $e->getMessage(), true ) . "</div>";

		}

		return $woo_product_index;

	}


	/**
	 * Retrieve WooCommerce product list via Local store
	 */
	public function get_product_list_via_local_store(){

		$woo_product_index = array();

		if ( class_exists( 'WC_Product_Query' ) ){

			$products = wc_get_products( array(
				'limit' => $this->max_woo_product_index,
			));

			foreach( $products as $product ){

				// retrieve variations
				$args = array(
					'post_type'     => 'product_variation',
					'post_status'   => array( 'private', 'publish' ),
					'numberposts'   => -1,
					'orderby'       => 'menu_order',
					'order'         => 'asc',
					'post_parent'   => $product->get_id()
				);
				$variations = get_posts( $args );

				foreach ( $variations as $variation ) {
					
					$variable_product = wc_get_product( $variation->ID ); 

					// add variation
					$index_line                = new \stdClass;
					$index_line->ID            = $variation->ID;
					$index_line->zbsprod_name  = $variable_product->get_name();
					$index_line->zbsprod_desc  = wp_strip_all_tags( $variable_product->get_short_description() );
					$index_line->zbsprod_price = $variable_product->get_price();
				
					$woo_product_index[] = $index_line;
				} 
				
				// Add main product
				$index_line                = new \stdClass;
				$index_line->ID            = $product->get_id();
				$index_line->zbsprod_name  = $product->get_name();
				$index_line->zbsprod_desc  = wp_strip_all_tags($product->get_short_description());
				$index_line->zbsprod_price = $product->get_price();
			
				$woo_product_index[] = $index_line;

			}

		}

		return $woo_product_index;

	}


	/**
	 * Returns total order count for local store
	 */
	public function get_order_count_via_local_store() {

		// retrieve generic page of orders to get total
		$args = array(
			'limit'     => 1,
			'paged'     => 1,
			'paginate'  => true,
		);
		$orders = wc_get_orders( $args );
		return $orders->total;

	}


	/**
	 * Returns the total number of woosync imported contacts present in CRM
	 */
	public function get_crm_woo_contact_count(){

		global $zbs;

		return $zbs->DAL->contacts->getContacts( array(

            'externalSource' => 'woo',
            'count'          => true,
            'ignoreowner'    => true

        ));

	}


	/**
	 * Returns the total number of woosync imported transactions present in CRM
	 */
	public function get_crm_woo_transaction_count(){

		global $zbs;

		return $zbs->DAL->transactions->getTransactions( array(

            'externalSource' => 'woo',
            'count'          => true,
            'ignoreowner'    => true

        ));

	}


	/**
	 * Returns the total value of woosync imported transactions present in CRM
	 */
	public function get_crm_woo_transaction_total(){

		global $zbs;

		return $zbs->DAL->transactions->getTransactions( array(

			// this may need status filtering. For now left as total total (MVP)

            'externalSource' => 'woo',            
            'total'          => true,
            'ignoreowner'    => true

        ));

	}


	/**
	 * Returns the most recent woo order crm transaction
	 */
	public function get_crm_woo_latest_woo_transaction(){

		global $zbs;

		$orders = $zbs->DAL->transactions->getTransactions( array(

			// this may need status filtering. For now left as total total (MVP)

            'externalSource' => 'woo',            
            'sortByField'    => 'date',
            'sortOrder'      => 'DESC',
            'page'           => 0,
            'perPage'        => 1,
            'ignoreowner'    => true

        ));

        if ( is_array( $orders ) && isset( $orders[0] ) && is_array( $orders[0] ) ){

        	return $orders[0];

        }

        return false;

	}


	/**
	 * Returns a crm transaction based on order id|origin
	 */
	public function get_transaction_from_order_id( $order_id, $origin='', $only_id=false ){

		global $zbs;

		$source_args = array(
	        'externalSource'      => 'woo',
	        'externalSourceUID'   => $order_id,
	        'origin'              => $origin	        
	    );

	    if ( $only_id ){
	    	$source_args['onlyID'] = true;
	    }

		return $zbs->DAL->transactions->getTransaction( -1, $source_args);

	}


	/**
	 * Returns settings-saved order prefix
	 *
	 * @return string Order Prefix
	 */
	public function get_prefix(){

		$settings = $this->settings->getAll();
		return $settings['wcprefix'];

	}


	/**
	 * Returns bool: is the loading page, our hub page
	 *
	 * @return bool hub page
	 */
	public function is_hub_page(){

		$page = '';

		if ( isset( $_GET['page'] ) ){
			$page = sanitize_text_field( $_GET['page'] );
		}

		if ( $page == $this->slugs['hub'] ){

			return true;

		}

		return false;

	}


	/**
	 * Processes an error string to make it more user friendly (#legacy)
	 *
	 * @return string $error
	 */
	public function process_error( $error ){

		// number 1: Invalid JSON = endpoint incorrect...
		if ( strpos( $error, 'Invalid JSON returned for' ) !== false){

			return __( "Error. Your WooCommerce endpoint may be incorrect!", 'zero-bs-crm' );

		}

		return $error;

	}


	/**
	 * Returns CRM Invoice meta with a specified key
	 *
	 * @param int $invoice_id
	 * @param string $key
	 *
	 * @return mixed $meta value
	 */
	public function get_invoice_meta( $invoice_id, $key = '' ){

		global $zbs;
		return $zbs->DAL->invoices->getInvoiceMeta( $invoice_id, 'extra_' . $key );

	}

	/**
	 * Returns future WooCommerce Bookings against a contact/object
	 *
	 * @param int $objid
	 *
	 * @return array WC Booking objects
	 */
	public function get_future_woo_bookings_for_object( $objid = -1 ){

		$bookings = array();
		if ( class_exists( 'WC_Booking_Data_Store' ) ){

			$wp_id = zeroBS_getCustomerWPID( $objid );
			if ( $wp_id > 0 ){
				$bookings = \WC_Booking_Data_Store::get_bookings_for_user(
					$wp_id,
					array(
						'orderby'    => 'start_date',
						'order'      => 'ASC',
						'date_after' => current_datetime()->setTime( 0, 0, 0, 0 )->getTimestamp() + current_datetime()->getOffset(), // gets the start of the day, respecting the current timezone (getOffset()).
					)
				);

			}

		}

		return $bookings;

	}

}