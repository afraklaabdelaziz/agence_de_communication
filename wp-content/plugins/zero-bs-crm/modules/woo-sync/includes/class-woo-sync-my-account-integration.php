<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 *
 * WooSync: WooCommerce My Account integration
 *
 */
namespace Automattic\JetpackCRM;

// block direct access
defined( 'ZEROBSCRM_PATH' ) || exit;

/**
 * WooSync My Account integration class
 */
class Woo_Sync_My_Account_Integration {


	/**
	 * The single instance of the class.
	 */
	protected static $_instance = null;

	/**
	 * Setup WooSync
	 * Note: This will effectively fire after core settings and modules loaded
	 * ... effectively on tail end of `init`
	 */
	public function __construct( ) {

		// Initialise Hooks
		$this->init_hooks();

		// Styles and scripts
		$this->register_styles_scripts();

	}
		

	/**
	 * Main Classs Instance.
	 *
	 * Ensures only one instance of Woo_Sync_My_Account_Integration is loaded or can be loaded.
	 *
	 * @since 2.0
	 * @static
	 * @see 
	 * @return Woo_Sync_My_Account_Integration main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	/**
	 * Initialise Hooks
	 */
	private function init_hooks( ) {

		// Add menu item to Woo My Account
		add_filter( 'woocommerce_account_menu_items', array( $this, 'append_items_to_woo_menu' ), 99, 1 );

		// Expose invoice content:
		add_action( 'woocommerce_account_invoices_endpoint', array( $this, 'render_invoice_list' ) );

		// Enqueue styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );

		// Expose CRM fields for editing on My Account (where specified in `wcport` setting)	
		add_action( 'woocommerce_edit_account_form', array( $this, 'render_additional_crm_fields_on_my_account' ), 10, 0 ); 

		// Save any changes to CRM fields as submitted from My Account page (where used `$this->render_additional_crm_fields_on_my_account`)
		add_action( 'woocommerce_save_account_details', array( $this, 'save_my_account_crm_field_changes'), 10, 1 );

		// See also $zbs->wordpress_user_integration->wordpress_profile_update (catches woo my account + wp profile update changes)

	}


	/**
	 * Get Invoice List
	 */
	public function render_invoice_list(){

		global $zbs;

		$settings = $zbs->modules->woosync->settings->getAll();
		if ( 
			array_key_exists( 'wcacc', $settings) && 
			$settings['wcacc'] &&
			function_exists( 'zeroBSCRM_portal_list_invoices' )
		){

			if ( $this->check_customer_has_invoices() ){
				
				zeroBSCRM_portal_list_invoices();

			} else {

				echo __( 'No invoices available.', 'zero-bs-crm' );

			}

		} else {

			echo __( 'This feature is disabled.', 'zero-bs-crm' );

		}

	}


	/**
	 * Does the current logged in customer have invoices?
	 *
	 * @return bool
	 */
	private function check_customer_has_invoices(){

		$wordpress_user_id = get_current_user_id();
		$uinfo = get_userdata( $wordpress_user_id );
		$contact_id = zeroBS_getCustomerIDWithEmail( $uinfo->user_email );

		if ( $contact_id > 0 ){

			$customer_invoices = zeroBS_getInvoicesForCustomer( $contact_id, true, 100, 0, false );
			
			if ( count( $customer_invoices ) > 0){

				return true;

			}

		}

		return false;
	}


	/**
	 * Appends our menu items (e.g. `Your Invoices`) to the Woo menu stack
	 *  To be fired via hook: `woocommerce_account_menu_items`
	 */
	public function append_items_to_woo_menu( $items ){

		global $zbs;

		$my_account_invoices_enabled = zeroBSCRM_getSetting( 'feat_invs' ) > 0;
		$wc_settings = $zbs->modules->woosync->settings->getAll();

		if ( $my_account_invoices_enabled && $wc_settings['wcacc'] ){

			$modified_items =  array( 'invoices' => __( 'Your Invoices', 'zero-bs-crm' ) );
   			$modified_items = array_slice( $items, 0, 2, true ) + $modified_items + array_slice( $items, 2, count( $items ), true );
			
			$items = $modified_items;

		} 

		return $items;

	}


	/**
	 * Register styles and scripts
	 */
	public function register_styles_scripts() {

    	wp_register_style( 'jpcrm-woo-sync-my-account', plugins_url( '/css/jpcrm-woo-sync-my-account'.wp_scripts_get_suffix().'.css', JPCRM_WOO_SYNC_ROOT_FILE ) );
		wp_register_style( 'jpcrm-woo-sync-fa', plugins_url( '/css/font-awesome.min.css', ZBS_ROOTFILE ) );

	}


	/**
	 * Enqueue styles and scripts
	 */
	public function enqueue_styles_scripts(){

		$account_page_id = get_option('woocommerce_myaccount_page_id');

		if ( is_page( $account_page_id ) ){

			wp_enqueue_style( 'jpcrm-woo-sync-my-account' );
			wp_enqueue_style( 'jpcrm-woo-sync-fa'	);

		}

	}



	/**
	 * Render CRM fields for editing on My Account (where specified in `wcport` setting)
	 * WH note: This could make use of a central functions for a chunk of it (e.g. shared with portal/front-end exposed fields?)
	 */
	public function render_additional_crm_fields_on_my_account() { 

		// make action magic happen here... 
		global $zbs, $zbsCustomerFields, $zbsFieldsEnabled;

		$settings = $zbs->modules->woosync->settings->getAll();

		if ( array_key_exists( 'wcport', $settings ) ){
	
			// Retrieve current user data
			$wordpress_user_id            = get_current_user_id();
			$uinfo          = get_userdata( $wordpress_user_id );
			$contact_id     = zeroBS_getCustomerIDWithEmail($uinfo->user_email);
			$crm_contact    = zeroBS_getCustomerMeta($contact_id);

			// Field models/settings

			// Fields pulled from contact model
			$fields = $zbsCustomerFields;

			// Retireve fields show/hide statuses
			$fields_to_show = $settings['wcport'];
			$fields_to_hide = $zbs->settings->get('fieldhides');	
			$fields_to_show_on_woo_my_account = explode(",", $fields_to_show);

            // Fields to hide for front-end situations (Portal)
            $fields_to_hide_on_portal = $zbs->DAL->fields_to_hide_on_frontend( ZBS_TYPE_CONTACT );

            // Portal hide field setting (overrides global setting ^)
			$portal_hide_fields_setting = $zbs->settings->get('portal_hidefields');
			if ( isset( $portal_hide_fields_setting ) ){

				$portal_hide_fields_setting_array = explode( ',', $portal_hide_fields_setting );
				if ( is_array( $portal_hide_fields_setting_array ) ){

					$fields_to_hide_on_portal = $portal_hide_fields_setting_array;
				}

			}

			// Address/contact settings
			$show_addresses = zeroBSCRM_getSetting('showaddress');
			$show_second_address = zeroBSCRM_getSetting('secondaddress');
			$show_address_country_field = zeroBSCRM_getSetting('countries');
			$click2call = false;

			// Legacy: This global holds "enabled/disabled" for specific fields... ignore unless you're WH or ask
			if ( $show_second_address == "1" ) {
				$zbsFieldsEnabled['secondaddress'] = true;
			} 
		
			// Track group element state
			$open_field_group   = false;
			$field_group_key    = '';

			?>
			<input type="hidden" name="zbs_customer_id" id="zbs_customer_id" value="<?php echo $contact_id; ?>" />
			<?php

			// Cycle through fields and op
			foreach ( $fields as $field_key => $field_value ){

				// Hard global front-end & specific Woo My Account blocking of some fields
				if (
					// Global block
					!in_array( $field_key, $fields_to_hide_on_portal )
					&& // Woo My Account settings specific block
					in_array( $field_key, $fields_to_show_on_woo_my_account )
					&& // Hard-hidden by opt override (on off for second address, mostly)
					!( isset( $field_value['opt'] ) && ( !isset( $zbsFieldsEnabled[ $field_value['opt'] ] ) || !$zbsFieldsEnabled[ $field_value['opt']] ) )
					&& // or is hidden by checkbox? 
					!( isset( $fields_to_hide['customer'] ) && is_array( $fields_to_hide['customer'] ) && in_array( $field_key, $fields_to_hide['customer'] ) )
				){

				/*

					The following is in many ways a refactor of `core/portal/v3/templates/details.php` and `MetaBoxes3.Contacts.php`
					... in the end the my account logic doesn't even use grouping :FACAEPALM:
					... leaving this commented in case we can use it to replace the above code ^

					// If previous field group was open and different, close it
					if (
						$open_field_group &&
							( 
								// group different
								( isset( $field_value['area'] ) && $field_value['area'] !== $field_group_key)
								|| // no group
								( !isset( $field_value['area'] ) && $field_group_key !== '' )
							)
						){

							// will need closing
							$close_table = true;

							// if main address, leave open (Legacy)
							if ( $field_group_key == 'Main Address' ){
								$close_table = false;
							}


                            // close
                            echo '</table></div>';
                            if ( $close_table ) {
                            	echo '</td></tr>';
                            }

					}

					// Deal with groups (`areas`)
					if ( isset( $field_value['area'] ) ){

						// First in a grouping? (assumes in sequential grouped order of fields)
						if ( $field_group_key != $field_value['area'] ){

							// Set area
							$field_group_key = $field_value['area'];

							// Hydrate label
							$field_group_label = str_replace(' ','_',$field_group_key);
							$field_group_label = strtolower($field_group_label);
						
							// Add address related adaptions
							if ( $show_second_address != "1" ) {
								$field_group_label .= "_100w";
							}
							if ( $show_addresses == "0" ) {
								$field_group_label .= " zbs-hide";
							}


							// Table open (unless merging address groups to allow side-by-side)
							$open_table = true; 
							if ( $field_group_key == 'Second Address' ) {
								$open_table = false;
							}


							// CSS classes for hiding address
							$row_class = '';
							$group_class = '';

							// if addresses turned off, hide the lot
							if ( $show_addresses != "1" ) {

								// addresses turned off
								$row_class = 'zbs-hide';
								$group_class = 'zbs-hide';								

							} else { 

								// addresses turned on
								if ( $field_group_key == 'Second Address' ){

									// if we're in second address grouping:

										// if second address turned off
										if ( $show_second_address != "1" ){

											$row_class = 'zbs-hide';
											$group_class = 'zbs-hide';

										}

								}

							}

							// / address  modifiers
										
							// Set this (need it to close groups)
							$open_field_group = true;

						}


					} else {

						// No groupings!
						$field_group_key = '';

					}

				// / grouping
				
				*/
				
					// Output all fields with a field format type
					if ( isset( $field_value[0] ) ){

						// Output Fields in Woo matching format (<p> per line)
						?><p class="form-row"><?php

						// Split by field format
						switch ( $field_value[0] ){

							case 'text':

								?>
								<label for="<?php echo $field_key; ?>"><?php _e( $field_value[1], "zero-bs-crm" ); ?></label>
								<input type="text" name="zbsc_<?php echo $field_key; ?>" id="<?php echo $field_key; ?>" class="input-text" style="width: 100%;padding: 15px;margin-bottom: 18px;" placeholder="<?php if (isset($field_value[2])) echo $field_value[2]; ?>" value="<?php if (isset($crm_contact[$field_key])) echo $crm_contact[$field_key]; ?>" autocomplete="zbscontact-<?php echo time(); ?>-<?php echo $field_key; ?>" />
								<?php

								break;

							case 'price':

								?><label for="<?php echo $field_key; ?>"><?php _e($field_value[1],"zero-bs-crm"); ?></label>
									<?php echo zeroBSCRM_getCurrencyChr(); ?> <input style="width: 130px;display: inline-block;;" type="text" name="zbsc_<?php echo $field_key; ?>" id="<?php echo $field_key; ?>" class="form-control  numbersOnly" placeholder="<?php if (isset($field_value[2])) echo $field_value[2]; ?>" value="<?php if (isset($crm_contact[$field_key])) echo $crm_contact[$field_key]; ?>" autocomplete="zbscontact-<?php echo time(); ?>-<?php echo $field_key; ?>" />
								<?php

								break;


							case 'date':

								?><label for="<?php echo $field_key; ?>"><?php _e($field_value[1],"zero-bs-crm"); ?></label>
									<input type="text" name="zbsc_<?php echo $field_key; ?>" id="<?php echo $field_key; ?>" class="form-control zbs-date" placeholder="<?php if (isset($field_value[2])) echo $field_value[2]; ?>" value="<?php if (isset($crm_contact[$field_key])) echo $crm_contact[$field_key]; ?>" autocomplete="zbscontact-<?php echo time(); ?>-<?php echo $field_key; ?>" />
								<?php

								break;

							case 'select':

								?><label for="<?php echo $field_key; ?>"><?php _e($field_value[1],"zero-bs-crm"); ?></label>
									<select name="zbsc_<?php echo $field_key; ?>" id="<?php echo $field_key; ?>" class="form-control zbs-watch-input" autocomplete="zbscontact-<?php echo time(); ?>-<?php echo $field_key; ?>">
										<?php
											// pre DAL 2 = $field_value[3], DAL2 = $field_value[2]
											$options = array(); 
											if (isset($field_value[3])) {

												$options = $field_value[3];

											} else {

												// DAL2 these don't seem to be auto-decompiled
												if ( isset( $field_value[2] ) ) {
													$options = explode( ',', $field_value[2] );
												}

											}

											if (isset($options) && count($options) > 0){

												echo '<option value="" disabled="disabled"';
												if (
														!isset( $crm_contact[$field_key] )
														|| 
														( isset( $crm_contact[$field_key] ) && empty( $crm_contact[$field_key] ) )
													) {

													echo ' selected="selected"';

												}
												echo '>' . __( 'Select', "zero-bs-crm" ) . '</option>';

												foreach ($options as $opt){

													echo '<option value="'.$opt.'"';

													if ( isset( $crm_contact[$field_key] ) && strtolower( $crm_contact[$field_key] ) == strtolower( $opt ) ){
														
														echo ' selected="selected"'; 

													}

													// __ here so that things like country lists can be translated
													echo '>' . __( $opt, "zero-bs-crm" ) . '</option>';

												}

											} else echo '<option value="">' . __( 'No Options', "zero-bs-crm" ) . '!</option>';

										?>
									</select>
								<?php

								break;

							case 'tel':

								?><label for="<?php echo $field_key; ?>"><?php _e($field_value[1],"zero-bs-crm");?></label>
									<input type="text" name="zbsc_<?php echo $field_key; ?>" id="<?php echo $field_key; ?>" class="form-control zbs-tel" placeholder="<?php if (isset($field_value[2])) echo $field_value[2]; ?>" value="<?php if (isset($crm_contact[$field_key])) echo $crm_contact[$field_key]; ?>" autocomplete="zbscontact-<?php echo time(); ?>-<?php echo $field_key; ?>" />
									<?php

									if ( $click2call == "1" && isset( $crm_contact[$field_key] ) && !empty( $crm_contact[$field_key] ) ) {
										
										echo '<a href="' . zeroBSCRM_clickToCallPrefix() . $crm_contact[$field_key] . '" class="button"><i class="fa fa-phone"></i> ' . $crm_contact[$field_key] . '</a>';
									
									}

									if ( $field_key == 'mobtel' ){

										// Twilio hook-in
										do_action( 'zbs_twilio_nonce' );

										// Twilio filtering for css classes
										$sms_class = 'send-sms-none';
										$sms_class = apply_filters( 'zbs_twilio_sms', $sms_class ); ;

										$contact_mobile = ''; 
										if ( is_array( $crm_contact ) && isset( $crm_contact[$field_key] ) && isset( $contact['id'] ) ){
										
											$contact_mobile = zeroBS_customerMobile( $contact['id'], $crm_contact );

										}
										
										if ( !empty( $contact_mobile) ){
											echo '<a class="' . $sms_class . ' button" data-smsnum="' . $contact_mobile .'"><i class="mobile alternate icon"></i> ' . __( 'SMS', 'zero-bs-crm' ) . ': ' . $contact_mobile . '</a>';
										}

									}

										?>
								<?php

								break;

							case 'email':

								?><label for="<?php echo $field_key; ?>"><?php _e( $field_value[1], "zero-bs-crm" ); ?>:</label>
									<input type="text" name="zbsc_<?php echo $field_key; ?>" id="<?php echo $field_key; ?>" class="form-control zbs-email" placeholder="<?php if (isset($field_value[2])) echo $field_value[2]; ?>" value="<?php if (isset($crm_contact[$field_key])) echo $crm_contact[$field_key]; ?>" autocomplete="off" />
								<?php

								break;

							case 'textarea':

								?><label for="<?php echo $field_key; ?>"><?php _e( $field_value[1], "zero-bs-crm" ); ?>:</label>
									<textarea name="zbsc_<?php echo $field_key; ?>" id="<?php echo $field_key; ?>" class="form-control" placeholder="<?php if (isset($field_value[2])) echo $field_value[2]; ?>" autocomplete="zbscontact-<?php echo time(); ?>-<?php echo $field_key; ?>"><?php if (isset($crm_contact[$field_key])) echo zeroBSCRM_textExpose($crm_contact[$field_key]); ?></textarea>
								<?php

								break;

							#} Added 1.1.19 
							case 'selectcountry':

								$countries = zeroBSCRM_loadCountryList();

								if ( $show_address_country_field == "1" ){

								?><label for="<?php echo $field_key; ?>"><?php _e( $field_value[1], "zero-bs-crm" ); ?></label>
									<select name="zbsc_<?php echo $field_key; ?>" id="<?php echo $field_key; ?>" class="form-control" autocomplete="zbscontact-<?php echo time(); ?>-<?php echo $field_key; ?>">
										<?php

											// got countries?
											if ( isset( $countries ) && count( $countries ) > 0 ){

												echo '<option value="" disabled="disabled"';
												if ( 
													!isset( $crm_contact[$field_key] ) 
													||
													( isset( $crm_contact[$field_key] ) && empty( $crm_contact[$field_key] ) ) ){

													 echo ' selected="selected"';

												}
												echo '>' . __( 'Select', "zero-bs-crm" ) . '</option>';

												foreach ($countries as $countryKey => $country){

													// temporary fix for people storing "United States" but also "US"
													// needs a migration to iso country code, for now, catch the latter (only 1 user via api)

													echo '<option value="' . $country . '"';
													if ( 
														isset( $crm_contact[$field_key] ) 
														&& 
														( 
															strtolower( $crm_contact[$field_key] ) == strtolower( $country )
															||
															strtolower( $crm_contact[$field_key] ) == strtolower( $countryKey )
														)
													){
														
														echo ' selected="selected"'; 

													}

													echo '>' . $country . '</option>';

												}

											} else echo '<option value="">' . __( 'No Countries Loaded', "zero-bs-crm" ) . '!</option>';

										?>
									</select><?php

								}

								break;


						}

					}

					?></p><?php

				} // / not in 'hard do not show' list

			} // foreach field
		
		} // if array key does not exist

	}
	

	/**
	 * Save any changes made from extra field additions on My Account page (via `$this->render_additional_crm_fields_on_my_account`)
	 *
	 * @param int $wordpress_user_id
	 */
	public function save_my_account_crm_field_changes( $wordpress_user_id ) {

		global $zbs;

		$contact_id = zeroBS_getCustomerIDFromWPID( $wordpress_user_id );

		// Here we check for fields already updatead via core WordPress User integration
		if ( defined( 'JPCRM_PROFILE_UPDATE_CHANGES' ) ){
			$do_not_update = JPCRM_PROFILE_UPDATE_CHANGES;
		} else {
			$do_not_update = array();
		}

		if ( $contact_id > 0 ){

			$limited_fields = array();

			foreach ( $_POST as $k => $v ){

				if ( $k == 'account_first_name' && !in_array( 'fname', $do_not_update ) ){

					$limited_fields[] = array(
						'key' => 'zbsc_fname',
						'val' => $v,
						'type'=> '%s'
					);

				}

				if ( $k == 'account_last_name' && !in_array( 'lname', $do_not_update ) ){

					$limited_fields[] = array(
						'key' => 'zbsc_lname',
						'val' => $v,
						'type'=> '%s'
					);            

				}

				if ( $k == 'account_email' && !in_array( 'email', $do_not_update ) ){
					
					$limited_fields[] = array(
						'key' => 'zbsc_email',
						'val' => $v,
						'type'=> '%s'
					);

				}

				// Generic catch of fields
				$zbsc_string = "zbsc_";
				if ( substr( $k, 0, strlen( $zbsc_string ) ) === $zbsc_string ){

					// catch blockable changes
					if ( $k == 'zbsc_fname' && in_array( 'fname', $do_not_update ) ){
						continue;
					}
					if ( $k == 'zbsc_lname' && in_array( 'lname', $do_not_update ) ){
						continue;
					}
					if ( $k == 'zbsc_email' && in_array( 'email', $do_not_update ) ){
						continue;
					}

					$limited_fields[] = array(
						'key' => $k,
						'val' => $v,
						'type'=> '%s'
					);

				}
			}
			
			$zbs->DAL->contacts->addUpdateContact(array(
				'id' => $contact_id,
				'limitedFields' => $limited_fields
			));

		}

	}
	
}