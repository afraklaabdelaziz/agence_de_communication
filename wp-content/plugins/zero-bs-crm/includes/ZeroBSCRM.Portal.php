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


// returns the directory name for Portal version to use. 
// v3.0+ uses v3, rest v1.0 (was previously 'endpoints')
function zeroBSCRM_portal_verDir(){
	
	global $zbs;
	if ($zbs->isDAL3())
		return 'v3';
	
	return 'v1';
}

// Sorts out the stylesheet includes
function zeroBS_portal_enqueue_stuff() {

    global $zbs;
    wp_enqueue_style('zbs-portal', ZEROBSCRM_URL . 'css/ZeroBSCRM.public.portal.'.zeroBSCRM_portal_verDir().wp_scripts_get_suffix().'.css', array(), $zbs->version );
    wp_enqueue_style('zbs-fa', ZEROBSCRM_URL . 'css/font-awesome.min.css', array(), $zbs->version );
    do_action('zbs_enqueue_portal', 'zeroBS_portal_enqueue_stuff');

}
add_action('zbs_enqueue_scripts_and_styles', 'zeroBS_portal_enqueue_stuff');

function zeroBSCRM_portal_themeSupport($classes=array()){

	$theme_slug = get_stylesheet();

	switch($theme_slug){

		case 'twentyseventeen':

			$classes[] ='zbs-theme-support-2017';
			break;

		case 'twentynineteen':

			$classes[] = 'zbs-theme-support-2019';
			break;

		case 'twentytwenty':

			$classes[] = 'zbs-theme-support-2020';
			break;

		case 'twentytwentyone':

			$classes[] = 'zbs-theme-support-2021';
			break;

		case 'twentytwentytwo':

			$classes[] = 'zbs-theme-support-2022';
			break;

	}

	return $classes;

}
// Basic theme support (here for now, probs needs option)
add_filter( 'body_class', 'zeroBSCRM_portal_themeSupport' );

#} We can do this below in the templater or templates? add_action( 'wp_enqueue_scripts', 'zeroBS_portal_enqueue_stuff' );
#} ... in the end we can just dump the above line into the templates before get_header() - hacky but works

// Adds the Rewrite Endpoint for the 'clients' area of the CRM. 
#} WH - this is dumped here now, because this whole thing is fired just AFTER init (to allow switch/on/off in main ZeroBSCRM.php)
/*
function zeroBS_portal_rewrite_endpoint(){
	add_rewrite_endpoint( 'clients', EP_ROOT );
}
add_action('init','zeroBS_portal_rewrite_endpoint');
*/

#function zeroBS_add_endpoints() {
#	add_rewrite_endpoint( 'clients', EP_ROOT );
#}
#add_action( 'init', 'zeroBS_add_endpoints');

/* Login link will therefore be
   <site_root>/clients/
   and <site_root>/clients/login 

Will be the equivalent of doing <site_root>?clients=login

Other URLS

What about <site_root>/clients/invoices/invoice_id
*/


// now to locate the templates...
// http://jeroensormani.com/how-to-add-template-files-in-your-plugin/

/**
 * Locate template.
 *
 * Locate the called template.
 * Search Order:
 * 1. /themes/theme/zerobscrm-plugin-templates/$template_name
 * 2. /themes/theme/$template_name
 * 3. /plugins/portal/v3/templates/$template_name.
 *
 * @since 1.2.7
 *
 * @param 	string 	$template_name			Template to load.
 * @param 	string 	$string $template_path	Path to templates.
 * @param 	string	$default_path			Default path to template files.
 * @return 	string 							Path to the template file.
 */
function zeroBS_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	// Set variable to search in zerobscrm-plugin-templates folder of theme.
	if ( ! $template_path ) :
		$template_path = 'zerobscrm-plugin-templates/';
	endif;
	// Set default plugin templates path.
	if ( ! $default_path ) :
		$default_path = ZEROBSCRM_PATH . 'portal/'.zeroBSCRM_portal_verDir().'/templates/'; // Path to the template folder
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
	return apply_filters( 'zeroBS_locate_template', $template, $template_name, $template_path, $default_path );
}

/**
 * Get template.
 *
 * Search for the template and include the file.
 *
 * @since 1.2.7
 *
 * @see zeroBS_get_template()
 *
 * @param string 	$template_name			Template to load.
 * @param array 	$args					Args passed for the template file.
 * @param string 	$string $template_path	Path to templates.
 * @param string	$default_path			Default path to template files.
 */
function zeroBS_get_template( $template_name, $args = array(), $tempate_path = '', $default_path = '' ) {

	if ( is_array( $args ) && isset( $args ) ) :
		extract( $args );
	endif;	
	$template_file = zeroBS_locate_template( $template_name, $tempate_path, $default_path );
	if ( ! file_exists( $template_file ) ) :
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), '1.0.0' );
		return;
	endif;
	include_once $template_file;
}

// This block of code makes sure our shortcode renders in the correct place in the theme and not above the header 
// which is the case if include_once fix only was applied.
add_filter( 'aioseo_conflicting_shortcodes', 'jpcrm_filter_conflicting_shortcodes' );
function jpcrm_filter_conflicting_shortcodes( $conflictingShortcodes ) {
   $conflictingShortcodes = array_merge( $conflictingShortcodes, [
		'Jetpack CRM Client Portal' => '[jetpackcrm_clientportal]'
   ] );
   return $conflictingShortcodes;
}



// Functions that were in the template file
function jpcrm_portal_save_details(){
	if($_POST['save'] == 1){
		$uid = get_current_user_id();
		$uinfo = get_userdata( $uid );
		$cID = zeroBS_getCustomerIDWithEmail($uinfo->user_email);

		// added !empty check - because if logged in as admin, saved deets, it made a new contact for them
		if((int)$_POST['customer_id'] == $cID && !empty($cID)){

			// handle the password fields, if set.
			if(isset($_POST['password']) && !empty($_POST['password']) && isset($_POST['password2']) && !empty($_POST['password2']) ){

				if($_POST['password'] != $_POST['password2']){
					echo "<div class='zbs_alert danger'>" . __("Passwords do not match","zero-bs-crm") . "</div>";
				} else {
					// update password
					wp_set_password( sanitize_text_field($_POST['password']), $uid);

					// log password change
					zeroBS_addUpdateLog(
						$cID,
						-1,
						-1,
						array(
							'type' => __( 'Password updated via Client Portal', 'zero-bs-crm' ),
							'shortdesc' => __( 'Contact changed their password via the Client Portal', 'zero-bs-crm' ),
							'longdesc' => '',
						),
						'zerobs_customer'
					);

					// display message
					echo "<div class='zbs_alert'>" . __( 'Password updated.', 'zero-bs-crm' ) . "</div>";
					// update any details as well
					jpcrm_portal_update_details_from_post($cID);
				}
			} else {
				// update any details as well
				jpcrm_portal_update_details_from_post($cID);
			}
		}
	}
}

// this handles contact detail updates via $_POST from the client portal
// this is a #backward-compatibility landmine; proceed with caution (see gh-1642)
function jpcrm_portal_update_details_from_post($cID=-1 ){

	global $zbs, $zbsCustomerFields;

	/**
	 * This gets fields hidden in Client Portal settings.
	 * Eventually we should expand this to preprocess and filter
	 * the following fields altogether if disabled:
	 *   - countries: zeroBSCRM_getSetting('countries')
	 *   - second addresses: zeroBSCRM_getSetting('secondaddress')
	 *   - all addresses: zeroBSCRM_getSetting('showaddress')
	 *   - not sure what this is: $zbs->settings->get('fieldhides')
	*/
	$hidden_fields = $zbs->settings->get( 'portal_hidefields' );
	$hidden_fields = !empty( $hidden_fields ) ? explode( ',', $hidden_fields ) : array();

	// get existing contact data
	$old_contact_data = $zbs->DAL->contacts->getContact( $cID );

	// downgrade to old-style second address keys so that field names match the object generated by zeroBS_buildCustomerMeta()
	$key_map = array(
		'secaddr_addr1' => 'secaddr1',
		'secaddr_addr2' => 'secaddr2',
		'secaddr_city' => 'seccity',
		'secaddr_county' => 'seccounty',
		'secaddr_country' => 'seccountry',
		'secaddr_postcode' => 'secpostcode'
	);
	foreach ( $key_map as $newstyle_key => $oldstyle_key ) {
		if ( isset( $old_contact_data[$newstyle_key] ) ){
			$old_contact_data[$oldstyle_key] = $old_contact_data[$newstyle_key];
			unset($old_contact_data[$newstyle_key]);
		}
	}

	// create new (sanitised) contact data from $_POST
	$new_contact_data = zeroBS_buildCustomerMeta($_POST, $old_contact_data);

	// process fields
	$fields_to_change = array();
	foreach ( $new_contact_data as $key => $value ) {
		// if invalid or unauthorised field, keep old value
		if ( !isset( $zbsCustomerFields[$key] ) || in_array( $key, $hidden_fields ) ) {
			$new_contact_data[$key] = $old_contact_data[$key];
		}
		// collect fields that changed
		elseif ( $old_contact_data[$key] != $value ) {
			$fields_to_change[] = $key;
		}
	}

	// update contact if fields changed
	if ( count( $fields_to_change ) > 0 ) {

		$cID = $zbs->DAL->contacts->addUpdateContact(
			array(
				'id'    =>  $cID,
				'data'  => $new_contact_data,
				'do_not_update_blanks' => false
			)
		);


		// update log if contact update was successful
		if ( $cID ){

			// build long description string for log
			$longDesc = '';
			foreach ( $fields_to_change as $field ) {
				if ( !empty( $longDesc ) ) {
					$longDesc .= '<br>';
				}
				$longDesc .= sprintf( '%s: <code>%s</code> → <code>%s</code>', $field, $old_contact_data[$field], $new_contact_data[$field]);
			}

			zeroBS_addUpdateLog(
				$cID,
				-1,
				-1,
				array(
					'type' => __( 'Details updated via Client Portal', 'zero-bs-crm' ),
					'shortdesc' => __( 'Contact changed some of their details via the Client Portal', 'zero-bs-crm' ),
					'longdesc' => $longDesc,
				),
				'zerobs_customer'
			);

			echo "<div class='zbs_alert'>" . __( 'Details updated.', 'zero-bs-crm') . "</div>";

		}
		else {
			echo "<div class='zbs-alert-danger'>" . __( 'Error updating details!', 'zero-bs-crm' ) . "</div>";
		}
	}

	return $cID;
}

function jpcrm_portal_details_RenderAdminNotice() {
	global $zbs;

	$admin_message = '<b>' . __( 'Admin notice:', 'zero-bs-crm' ) . '</b><br>';
	$admin_message .= __( 'This is the Client Portal contact details page. This will show the contact their details and allow them change information in the fields below. You can hide fields from this page in <i>Settings → Client Portal → Fields to hide on Portal</i>.', 'zero-bs-crm' );
	##WLREMOVE
	$admin_message .= '<br><br><a href="' . $zbs->urls['kbclientportal'] . '" target="_blank">' . __( 'Learn more', 'zero-bs-crm' ) . '</a>';
	##/WLREMOVE

	?>
	<div class='alert alert-info' style="font-size: 0.8em;text-align: left;margin-top:0px;">
	<?php echo $admin_message ?>
	</div><?php
}

function jpcrm_portal_details_RenderTextField($fieldK, $fieldV, $value){
	// added zbs-text-input class 5/1/18 - this allows "linkify" automatic linking
	// ... via js
	//  mike-label
	?>
	<p>
		<label class='label' for="<?php echo $fieldK; ?>"><?php _e($fieldV[1],"zero-bs-crm"); ?>:</label>
		<input type="text" name="zbsc_<?php echo $fieldK; ?>" id="<?php echo $fieldK; ?>" class="form-control widetext" placeholder="<?php if (isset($fieldV[2])) echo $fieldV[2]; ?>" value="<?php if (isset($value)) echo $value; ?>" autocomplete="zbscontact-<?php echo time(); ?>-<?php echo $fieldK; ?>" />
	</p>
	<?php
}

function jpcrm_portal_details_RenderPriceField($fieldK, $fieldV, $value){
	?><p>
		<label for="<?php echo $fieldK; ?>"><?php _e($fieldV[1],"zero-bs-crm"); ?>:</label>
		<?php echo zeroBSCRM_getCurrencyChr(); ?> <input style="width: 130px;display: inline-block;;" type="text" name="zbsc_<?php echo $fieldK; ?>" id="<?php echo $fieldK; ?>" class="form-control  numbersOnly" placeholder="<?php if (isset($fieldV[2])) echo $fieldV[2]; ?>" value="<?php if (isset($value)) echo $value; ?>" autocomplete="zbscontact-<?php echo time(); ?>-<?php echo $fieldK; ?>" />
	</p><?php
}

function jpcrm_portal_details_RenderDateField($fieldK, $fieldV, $value){
	/* skipping DATE custom fields for v3.0, lets see if they're asked for...
	... if so, then rewrite this whole linkage (as above to match zeroBSCRM_html_editFields() style)
	... because 'date' here is a UTS, and we'll need date picker etc.

	?><tr class="wh-large"><th><label for="<?php echo $fieldK; ?>"><?php _e($fieldV[1],"zero-bs-crm"); ?>:</label></th>
	<td>
		<input type="text" name="zbsc_<?php echo $fieldK; ?>" id="<?php echo $fieldK; ?>" class="form-control zbs-date" placeholder="<?php if (isset($fieldV[2])) echo $fieldV[2]; ?>" value="<?php if (isset($value)) echo $value; ?>" autocomplete="zbscontact-<?php echo time(); ?>-<?php echo $fieldK; ?>" />
	</td></tr><?php
	*/
}

function jpcrm_portal_details_RenderSelectField($fieldK, $fieldV, $value){
	?>
	<p>
		<label class='label' for="<?php echo $fieldK; ?>"><?php _e($fieldV[1],"zero-bs-crm"); ?>:</label>
		<select name="zbsc_<?php echo $fieldK; ?>" id="<?php echo $fieldK; ?>" class="form-control zbs-watch-input" autocomplete="zbscontact-<?php echo time(); ?>-<?php echo $fieldK; ?>">
			<?php
			// pre DAL 2 = $fieldV[3], DAL2 = $fieldV[2]
			$options = array();
			if (isset($fieldV[3]) && is_array($fieldV[3])) {
				$options = $fieldV[3];
			} else {
				// DAL2 these don't seem to be auto-decompiled?
				// doing here for quick fix, maybe fix up the chain later.
				if (isset($fieldV[2])) $options = explode(',', $fieldV[2]);
			}

			if (isset($options) && count($options) > 0){

				//catcher
				echo '<option value="" disabled="disabled"';
				if (!isset($value) || (isset($value)) && empty($value)) echo ' selected="selected"';
				echo '>'.__('Select',"zero-bs-crm").'</option>';

				foreach ($options as $opt){

					echo '<option value="'.$opt.'"';
					if (isset($value) && strtolower($value) == strtolower($opt)) echo ' selected="selected"';
					// __ here so that things like country lists can be translated
					echo '>'.__($opt,"zero-bs-crm").'</option>';

				}

			} else echo '<option value="">'.__('No Options',"zero-bs-crm").'!</option>';

			?>
		</select>
		<input type="hidden" name="zbsc_<?php echo $fieldK; ?>_dirtyflag" id="zbsc_<?php echo $fieldK; ?>_dirtyflag" value="0" />
	</p>
	<?php
}

function jpcrm_portal_details_RenderTelephoneField($fieldK, $fieldV, $value, $zbsCustomer){
	$click2call = 0;
	?><p>
	<label for="<?php echo $fieldK; ?>"><?php _e($fieldV[1],"zero-bs-crm");?>:</label>
	<input type="text" name="zbsc_<?php echo $fieldK; ?>" id="<?php echo $fieldK; ?>" class="form-control zbs-tel" placeholder="<?php if (isset($fieldV[2])) echo $fieldV[2]; ?>" value="<?php if (isset($value)) echo $value; ?>" autocomplete="zbscontact-<?php echo time(); ?>-<?php echo $fieldK; ?>" />
	<?php if ($click2call == "1" && isset($zbsCustomer[$fieldK]) && !empty($zbsCustomer[$fieldK])) echo '<a href="'.zeroBSCRM_clickToCallPrefix().$zbsCustomer[$fieldK].'" class="button"><i class="fa fa-phone"></i> '.$zbsCustomer[$fieldK].'</a>'; ?>
	<?php
	if ($fieldK == 'mobtel'){

		$sms_class = 'send-sms-none';
		$sms_class = apply_filters('zbs_twilio_sms', $sms_class);
		do_action('zbs_twilio_nonce');

		$customerMob = ''; if (is_array($zbsCustomer) && isset($zbsCustomer[$fieldK]) && isset($contact['id'])) $customerMob = zeroBS_customerMobile($contact['id'],$zbsCustomer);

		if (!empty($customerMob)) echo '<a class="' . $sms_class . ' button" data-smsnum="' . $customerMob .'"><i class="mobile alternate icon"></i> '.__('SMS','zero-bs-crm').': ' . $customerMob . '</a>';
	}

	?>
	</p>
	<?php
}

function jpcrm_portal_details_RenderEmailField($fieldK, $fieldV, $value){
	// added zbs-text-input class 5/1/18 - this allows "linkify" automatic linking
	// ... via js <div class="zbs-text-input">
	// removed from email for now zbs-text-input

	?><p>
	<label for="<?php echo $fieldK; ?>"><?php _e($fieldV[1],"zero-bs-crm"); ?>:</label>
	<div class="<?php echo $fieldK; ?>">
		<input type="text" name="zbsc_<?php echo $fieldK; ?>" id="<?php echo $fieldK; ?>" class="form-control zbs-email" placeholder="<?php if (isset($fieldV[2])) echo $fieldV[2]; ?>" value="<?php if (isset($value)) echo $value; ?>" autocomplete="off" />
	</div>
	</p><?php
}

function jpcrm_portal_details_RenderTextAreaField($fieldK, $fieldV, $value) {
	?><p>
	<label for="<?php echo $fieldK; ?>"><?php _e($fieldV[1],"zero-bs-crm"); ?>:</label>
	<textarea name="zbsc_<?php echo $fieldK; ?>" id="<?php echo $fieldK; ?>" class="form-control" placeholder="<?php if (isset($fieldV[2])) echo $fieldV[2]; ?>" autocomplete="zbscontact-<?php echo time(); ?>-<?php echo $fieldK; ?>"><?php if (isset($value)) echo zeroBSCRM_textExpose($value); ?></textarea>
	</p><?php
}

function jpcrm_portal_details_RenderCountryListField($fieldK, $fieldV, $value, $showCountryFields) {
	$countries = zeroBSCRM_loadCountryList();

	if ($showCountryFields == "1"){

		?><p>
		<label for="<?php echo $fieldK; ?>"><?php _e($fieldV[1],"zero-bs-crm"); ?>:</label>
		<select name="zbsc_<?php echo $fieldK; ?>" id="<?php echo $fieldK; ?>" class="form-control" autocomplete="zbscontact-<?php echo time(); ?>-<?php echo $fieldK; ?>">
			<?php

			if (isset($countries) && count($countries) > 0){

				//catcher
				echo '<option value="" disabled="disabled"';
				if (!isset($value) || (isset($value)) && empty($value)) echo ' selected="selected"';
				echo '>'.__('Select',"zero-bs-crm").'</option>';

				foreach ($countries as $countryKey => $country){

					// temporary fix for people storing "United States" but also "US"
					// needs a migration to iso country code, for now, catch the latter (only 1 user via api)

					echo '<option value="'.$country.'"';
					if (isset($value) && (
							strtolower($value) == strtolower($country)
							||
							strtolower($value) == strtolower($countryKey)
						)) echo ' selected="selected"';
					echo '>'.$country.'</option>';

				}

			} else echo '<option value="">'.__('No Countries Loaded',"zero-bs-crm").'!</option>';

			?>
		</select>
		</p><?php

	}
}

function jpcrm_portal_details_RenderRadioField($fieldK, $fieldV, $value, $postPrefix) {
	?><p>
	<label for="<?php echo $fieldK; ?>"><?php _e($fieldV[1],"zero-bs-crm"); ?>:</label>
	<div class="zbs-field-radio-wrap">
		<?php

		// pre DAL 2 = $fieldV[3], DAL2 = $fieldV[2]
		$options = false;
		if (isset($fieldV[3]) && is_array($fieldV[3])) {
			$options = $fieldV[3];
		} else {
			// DAL2 these don't seem to be auto-decompiled?
			// doing here for quick fix, maybe fix up the chain later.
			if (isset($fieldV[2])) $options = explode(',', $fieldV[2]);
		}

		if (isset($options) && is_array($options) && count($options) > 0 && $options[0] != ''){

			$optIndex = 0;

			foreach ($options as $opt){
				// <label><input type="radio" name="group1" id="x" /> <span>Label text x</span></label>
				echo '<div class="zbs-radio">';
				echo '<label for="'.$fieldK.'-'.$optIndex.'"><input type="radio" name="'.$postPrefix.$fieldK.'" id="'.$fieldK.'-'.$optIndex.'" value="'.$opt.'"';
				if (isset($value) && $value == $opt) echo ' checked="checked"';
				echo ' /> <span>'.$opt.'</span></label></div>';

				$optIndex++;
			}

		} else echo '-';

		?>
	</div>
	</p><?php
}

function jpcrm_portal_details_RenderCheckboxField($fieldK, $fieldV, $value, $postPrefix) {
	?><p>
	<label for="<?php echo $fieldK; ?>"><?php _e($fieldV[1],"zero-bs-crm"); ?>:</label>
	<div class="zbs-field-checkbox-wrap">
		<?php

		// pre DAL 2 = $fieldV[3], DAL2 = $fieldV[2]
		$options = false;
		if (isset($fieldV[3]) && is_array($fieldV[3])) {
			$options = $fieldV[3];
		} else {
			// DAL2 these don't seem to be auto-decompiled?
			// doing here for quick fix, maybe fix up the chain later.
			if (isset($fieldV[2])) $options = explode(',', $fieldV[2]);
		}

		// split fields (multi select)
		$dataOpts = array();
		if (isset($value) && !empty($value)){
			$dataOpts = explode(',', $value);
		}

		if (isset($options) && is_array($options) && count($options) > 0 && $options[0] != ''){

			$optIndex = 0;

			foreach ($options as $opt){
				echo '<div class="zbs-cf-checkbox">';
				echo '<label for="'.$fieldK.'-'.$optIndex.'"><input type="checkbox" name="'.$postPrefix.$fieldK.'-'.$optIndex.'" id="'.$fieldK.'-'.$optIndex.'" value="'.$opt.'"';
				if (in_array($opt, $dataOpts)) echo ' checked="checked"';
				echo ' /> <span>'.$opt.'</span></label></div>';

				$optIndex++;

			}

		} else echo '-';

		?>
	</div>
	</p><?php
}

function jpcrm_portal_details_RenderFieldByType($type, $fieldK, $fieldV, $value, $postPrefix, $showCountryFields, $zbsCustomer) {
	switch ($type){
		case 'text':
			jpcrm_portal_details_RenderTextField($fieldK, $fieldV, $value);
			break;
		case 'price':
			jpcrm_portal_details_RenderPriceField($fieldK, $fieldV, $value);
			break;
		case 'date':
			jpcrm_portal_details_RenderDateField($fieldK, $fieldV, $value);
			break;

		case 'select':
			jpcrm_portal_details_RenderSelectField($fieldK, $fieldV, $value);
			break;

		case 'tel':
			jpcrm_portal_details_RenderTelephoneField($fieldK, $fieldV, $value, $zbsCustomer);
			break;

		case 'email':
			jpcrm_portal_details_RenderEmailField($fieldK, $fieldV, $value);
			break;

		case 'textarea':
			jpcrm_portal_details_RenderTextAreaField($fieldK, $fieldV, $value);
			break;

		// Added 1.1.19
		case 'selectcountry':
			jpcrm_portal_details_RenderCountryListField($fieldK, $fieldV, $value, $showCountryFields);
			break;

		// 2.98.5 added autonumber, checkbox, radio
		case 'autonumber':
			// NOT SHOWN on portal :)
			break;

		// radio
		case 'radio':
			jpcrm_portal_details_RenderRadioField($fieldK, $fieldV, $value, $postPrefix);
			break;

		case 'checkbox':
			jpcrm_portal_details_RenderCheckboxField($fieldK, $fieldV, $value, $postPrefix);
			break;
	}
}

function jpcrm_portal_details_GetValue($fieldK, $zbsCustomer) {
	// get a value (this allows field-irrelevant global tweaks, like the addr catch below...)
	$value = '';
	if (isset($zbsCustomer[$fieldK])) $value = $zbsCustomer[$fieldK];

	// #backward-compatibility
	// contacts got stuck in limbo as we upgraded db in 2 phases.
	// following catches old str and modernises to v3.0
	// make addresses their own objs 3.0+ and do away with this.
	// ... hard typed to avoid custom field collisions, hacky at best.
	switch ($fieldK){

		case 'secaddr1':
			if (isset($zbsCustomer['secaddr_addr1'])) $value = $zbsCustomer['secaddr_addr1'];
			break;

		case 'secaddr2':
			if (isset($zbsCustomer['secaddr_addr2'])) $value = $zbsCustomer['secaddr_addr2'];
			break;

		case 'seccity':
			if (isset($zbsCustomer['secaddr_city'])) $value = $zbsCustomer['secaddr_city'];
			break;

		case 'seccounty':
			if (isset($zbsCustomer['secaddr_county'])) $value = $zbsCustomer['secaddr_county'];
			break;

		case 'seccountry':
			if (isset($zbsCustomer['secaddr_country'])) $value = $zbsCustomer['secaddr_country'];
			break;

		case 'secpostcode':
			if (isset($zbsCustomer['secaddr_postcode'])) $value = $zbsCustomer['secaddr_postcode'];
			break;
	}

	return $value;
}



#} MS - can you make this work with templates, couldn't so dumped (dumbly) here for now:
function zeroBSCRM_portalFooter(){
	// won't return ours :/
	//return zeroBS_get_template('footer.php');
	##WLREMOVE
	$showPoweredBy = zeroBSCRM_getSetting('showportalpoweredby');
    if ($showPoweredBy == "1"){ 
    	global $zbs; ?><div class="zerobs-portal-poweredby" style="font-size:11px;position:absolute;bottom:25px;right:50px;font-size:12px;"><?php _e('Powered by',"zero-bs-crm"); ?> <a href="<?php echo $zbs->urls['home']; ?>" target="_blank">Jetpack CRM</a></div><?php 
    } 
	##/WLREMOVE
}


// checks if a user has "enabled" or "disabled" access
function zeroBSCRM_portalIsUserEnabled(){

	// cached?
	if (defined('ZBS_CURRENT_USER_DISABLED')) return false;

	global $wpdb;
	$uid = get_current_user_id();
	$cID = zeroBS_getCustomerIDFromWPID($uid);

	// these ones definitely work
    $uinfo = get_userdata( $uid );
    $potentialEmail = ''; if (isset($uinfo->user_email)) $potentialEmail = $uinfo->user_email;
    $cID = zeroBS_getCustomerIDWithEmail($potentialEmail);

	$disabled = zeroBSCRM_isCustomerPortalDisabled($cID);

	if (!$disabled) return true;

	// cache to avoid multi-check
	define('ZBS_CURRENT_USER_DISABLED',true);
	return false;

}


function zeroBS_portalnav( $selected_item = 'dashboard', $do_echo = true ){

	global $wp_query;

	$nav_html = '';
	$zbsWarn = '';
  	$dash_link = zeroBS_portal_link('dash');
	$the_vars = $wp_query->query_vars;

	$nav_items = array(
		'dashboard' 	=> array('name' => 'Dashboard', 'icon' => 'fa-dashboard','slug'=>''),
		'details' 		=> array('name' => 'Your Details', 'icon' => 'fa-user', 'slug' => 'details'),
	);

	if(zeroBSCRM_getSetting('feat_invs') > 0){
		$nav_items['invoices'] = array('name' => 'Invoices', 'icon' => 'fa-file-text-o', 'slug'=>'invoices');
	}

	if(zeroBSCRM_getSetting('feat_quotes') > 0){
		$nav_items['quotes'] 	= array('name' => 'Quotes', 'icon' => 'fa-clipboard', 'slug' => 'quotes');
	}

	if(zeroBSCRM_getSetting('feat_transactions') > 0){
		$nav_items['transactions'] 	= array('name' => 'Transactions', 'icon' => 'fa-shopping-cart', 'slug' => 'transactions');
	}

	$nav_items = apply_filters('zbs_portal_nav_menu_items', $nav_items);

	$nav_items = apply_filters('zbs_portal_nav_menu_items_no_endpoint', $nav_items);

	$allowed_keys = array('dashboard', 'thanks', 'cancel', 'logout', 'pn');

	$nav_html .= '	<ul id="zbs-nav-tabs">';
		
	$portalPageID = zeroBSCRM_getSetting('portalpage');
	$slug = get_permalink( $portalPageID );

	if ( empty( $slug ) ) {
	    $slug = home_url( 'clients' );
	}

	foreach ( $nav_items as $menu_name => $menu_data ) {

		//make sure our standard keys stick - only in CPP
		if(class_exists( 'ZeroBSCRM_ClientPortalPro' )){
			if(!in_array($menu_name, $allowed_keys)){
				if(!array_key_exists('show', $menu_data)){
					continue;
				}
			}
		}

	    if(function_exists('zeroBSCRM_clientPortalgetEndpoint') && $menu_name != 'dashboard' && $menu_name != 'logout'){
            $menu_name = zeroBSCRM_clientPortalgetEndpoint($menu_name);
		}

		if($menu_name == 'dashboard'){
			$link = esc_url( $slug );
		}else{
			if ( array_key_exists( 'slug', $menu_data) ) {
				$link = esc_url( $slug . $menu_data['slug'] );
			}
		}

		if ( $menu_name == $selected_item ) {
			$class='active';
		} else {
			$class='na';
		}
		//produce the menu from the array of menu items (easier to extend :-) ).
		// WH: this assumes icon, otehrwise it'll break! :o
		$nav_html .= "<li class='".$class."'><a href='" . $link ."'><i class='fa ".$menu_data['icon']."'></i>". __($menu_data['name'],'zero-bs-crm') . "</a></li>";

	}

	$zbs_logout_text = __('Log out',"zero-bs-crm");
	$zbs_logout_text = apply_filters('zbs_portal_logout_text', $zbs_logout_text);

	$zbs_logout_icon = 'fa-sign-out';
	$zbs_logout_icon = apply_filters('zbs_portal_logout_icon', $zbs_logout_icon);

	$nav_html .= "<li class='na'><a href='". wp_logout_url( $dash_link ) . "'><i class='fa ".$zbs_logout_icon."' aria-hidden='true'></i>" . $zbs_logout_text . "</a></li>";
	$nav_html .= '</ul>';

	// echo or return nav HTML depending on flag; defaults to echo (legacy support)
	if ( $do_echo ) {
		echo $nav_html;
	}
	else {
		return $nav_html;
	}
}


// first avail action which makes sense is to use template_redirect
// this tries its best to efficiently catch custom endpoints without affecting perf
add_action('template_redirect','zeroBSCRM_clientPortal_catchCustomPageEndpoints');
function zeroBSCRM_clientPortal_catchCustomPageEndpoints(){

	global $post;

	// only fire in most meaningful situations

	// if front-end
	// if $post
	// if is page
	// does have (eventual) parent of client portal page?
	if (!is_admin() &&
		isset($post) && is_object($post) &&
		is_page() && 
		zeroBSCRM_clientPortal_isChildOfPortalPage()){

		// is probably a custom page
		
			// this would work if we were rewrite ruling the page to /clients to fire 'zeroBSCRM_clientPortal_shortcode'
			// ... but we're not
			//add_action('zbs_portal_' . $post->post_name . '_endpoint', 'zeroBSCRM_clientPortal_customPage', 999, 1);

			// so achieve the same, buy overriding template_include
			add_filter('template_include','zeroBSCRM_clientPortal_customPage');

	}

}


#} Version 2.86+ run the client portal from a shortcode (translatable and more flexible)
function zeroBSCRM_portal_endpoint() {
	global $zbs;

	$nav_items = array(
		'dashboard' 	=> array('name' => 'Dashboard', 'icon' => 'fa-dashboard','slug'=>''),
		'details' 		=> array('name' => 'Your Details', 'icon' => 'fa-user', 'slug' => 'details'),
		'thanks' 		=> array('name' => 'Thank you', 'icon' => 'fa-user', 'slug' => 'thanks'),
		'cancel' 		=> array('name' => 'Payment Cancelled', 'icon' => 'fa-user', 'slug' => 'cancel'),
	);
	

	if(zeroBSCRM_getSetting('feat_invs') > 0){
		$nav_items['invoices'] = array('name' => 'Invoices', 'icon' => 'fa-file-text-o', 'slug'=>'invoices');
	}

	if(zeroBSCRM_getSetting('feat_quotes') > 0){
		$nav_items['quotes'] 	= array('name' => 'Quotes', 'icon' => 'fa-clipboard', 'slug' => 'quotes');
	}

	if(zeroBSCRM_getSetting('feat_transactions') > 0){
		$nav_items['transactions'] 	= array('name' => 'Transactions', 'icon' => 'fa-shopping-cart', 'slug' => 'transactions');
	}

	$nav_items = apply_filters('zbs_portal_nav_menu_items', $nav_items);

	foreach ($nav_items as $k => $v){
		if (array_key_exists('slug', $v)){
			add_rewrite_endpoint( $v['slug'], EP_ROOT | EP_PAGES );	
		}	
	}

	$nav_items = apply_filters('zbs_portal_nav_menu_items_no_endpoint', $nav_items);

	//catches the payments.. not modifiable
	add_rewrite_endpoint( 'pn', EP_ROOT | EP_PAGES );

	
}
add_action( 'init', 'zeroBSCRM_portal_endpoint' );

function zeroBSCRM_portal_query_vars( $vars ) {

	$nav_items = array(
		'dashboard' 	=> array('name' => 'Dashboard', 'icon' => 'fa-dashboard','slug'=>''),
		'details' 		=> array('name' => 'Your Details', 'icon' => 'fa-user', 'slug' => 'details'),
		'thanks' 		=> array('name' => 'Thank you', 'icon' => 'fa-user', 'slug' => 'thanks'),
		'cancel' 		=> array('name' => 'Payment Cancelled', 'icon' => 'fa-user', 'slug' => 'cancel'),
	);


	if(zeroBSCRM_getSetting('feat_invs') > 0){
		$nav_items['invoices'] = array('name' => 'Invoices', 'icon' => 'fa-file-text-o', 'slug'=>'invoices');
	}

	if(zeroBSCRM_getSetting('feat_quotes') > 0){
		$nav_items['quotes'] 	= array('name' => 'Quotes', 'icon' => 'fa-clipboard', 'slug' => 'quotes');
	}

	if(zeroBSCRM_getSetting('feat_transactions') > 0){
		$nav_items['transactions'] 	= array('name' => 'Transactions', 'icon' => 'fa-shopping-cart', 'slug' => 'transactions');
	}

	
	$nav_items = apply_filters('zbs_portal_nav_menu_items', $nav_items);

	//query vars here.
	foreach ($nav_items as $k => $v){
		if (array_key_exists('slug', $v)){
			$vars[] = $v['slug'];	
		}	
	}

	$nav_items = apply_filters('zbs_portal_nav_menu_items_no_endpoint', $nav_items);

	//payment pages
	$vars[] = 'pn';   //this is the endpoint for payment notifications.
	
    return $vars;
}
add_filter( 'query_vars', 'zeroBSCRM_portal_query_vars', 0 );


// wh: lets us check early on in the action stack to see if page is ours
// ... wrote so could make styles conditional in cpp.
// THIS (zeroBSCRM_clientPortal_isPortalPage) only works after 'wp' in action order (needs wp_query->query_var)
// This is also used by zeroBSCRM_isClientPortalPage in Admin Checks (which affects force redirect to dash, so be careful)
function zeroBSCRM_clientPortal_isPortalPage(){

	if (!is_admin()){

		// front end

			// got endpoint?
			return zeroBSCRM_clientPortal_isOurEndpoint();

	}

	return false;

}


// is a child, or a child of a child, of the client portal main page
function zeroBSCRM_clientPortal_isChildOfPortalPage(){ 
	
	global $post; 
	
	if (!is_admin() && function_exists('zeroBSCRM_getSetting') && zeroBSCRM_isExtensionInstalled('portal')){

		$portalPage = (int)zeroBSCRM_getSetting('portalpage');
		
		if ($portalPage > 0 && isset($post) && is_object($post)){

			if ( is_page() && ($post->post_parent == $portalPage) ) {
					return true;
			} else { 

				// check 1 level deeper
				if ($post->post_parent > 0){

					$parentsParentID = (int)wp_get_post_parent_id($post->post_parent);
					
					if ($parentsParentID > 0 && ($parentsParentID == $portalPage) ) return true;

				}
				return false; 
			}
		}
	}
	return false;

}
 

// returns true if current page loaded has an endpoint that matches ours
// THIS (zeroBSCRM_clientPortal_isPortalPage) only works after 'wp' in action order (needs wp_query->query_var)
function zeroBSCRM_clientPortal_isOurEndpoint(){

	global $wp_query;

	// we get the post id (which will be the page id) + compare to our setting
	$portalPage = zeroBSCRM_getSetting('portalpage');
	if (!empty($portalPage) && $portalPage > 0 && isset($wp_query->post) && gettype($wp_query->post) == 'object' && isset($wp_query->post->ID) && $wp_query->post->ID == $portalPage) return true;

	if (zeroBSCRM_clientPortal_isChildOfPortalPage()) return true;

	/* The following will work for all pages except the dash, and only late in the wp_query action run
	... so I've used the above instead 

	$allowed_endpoints = array(
		'quotes',
		'invoices',
		'transactions',
		'details',
		'thanks',
		'cancel',
		'pn',
		'denied',
	);
	$allowed_endpoints = apply_filters('zbs_portal_endpoints', $allowed_endpoints);
	$the_vars = $wp_query->query_vars;
	foreach($the_vars as $k => $v){
		//run through the query vars and find ours
		if(in_array($k, $allowed_endpoints)){
			//we have a match
			return true;
		}

	}
	*/

	return false;
}

function zeroBSCRM_clientPortal_shortcode(){
	// This function is being called by a shortcode (add_shortcode) and should never return any output (e.g. echo).
	// The implementation is old and removing all the output requires a lot of work. This is a quick workaround to fix it.
	ob_start();
	// this checks that we're on the front-end
	// ... a necessary step, because the editor (wp) now runs the shortcode on loading (probs gutenberg)
	// ... and because this should RETURN, instead it ECHO's directly
	// ... it should not run on admin side, because that means is probs an edit page!
	if ( !is_admin() ) {
		global $wp_query;

		$allowed_endpoints = array(
			'quotes',
			'invoices',
			'transactions',
			'details',
			'thanks',
			'cancel',
			'pn',
			'denied',
		);

		$allowed_endpoints = apply_filters( 'zbs_portal_endpoints', $allowed_endpoints );
		$endpoint_value = '';
		$the_vars = $wp_query->query_vars;
		$our_endpoint = false;
		foreach( $the_vars as $k => $v ) {
			//run through the query vars and find ours
			if ( in_array( $k, $allowed_endpoints ) ) {
				//we have a match
				$our_endpoint = true;
				$endpoint = $k;
				$endpoint_value = $v;
			}
		}

		if ( !$our_endpoint ) {
			$endpoint = 'dashboard';
		}

		//used to control which action to call
		$endpoint = apply_filters( 'zbs_portal_endpoint_to_action', $endpoint );

		if( $endpoint == 'pn' ) {
			//does not seem to redirect.
			//capture the payment side of things
			do_action('zerobscrm_portal_pn');
		} else {
			if ( $endpoint_value != '' ) {
				do_action( 'zbs_portal_' . $endpoint . '_single_endpoint' );
			} else {
				do_action( 'zbs_portal_' . $endpoint . '_endpoint' );
			}
		}
	}
	$result = ob_get_contents();
	ob_end_clean();
	return $result;
}

add_shortcode('jetpackcrm_clientportal', 'zeroBSCRM_clientPortal_shortcode');
add_shortcode('zerobscrm_clientportal', 'zeroBSCRM_clientPortal_shortcode');


// this catches failed logins, checks if from our page, then redirs
// From mr pippin https://pippinsplugins.com/redirect-to-custom-login-page-on-failed-login/
add_action( 'wp_login_failed', 'zeroBSCRM_portal_login_fail' );  // hook failed login
function zeroBSCRM_portal_login_fail( $username ) {

	$referrer = '';
	if(array_key_exists('HTTP_REFERER', $_SERVER)){
		$referrer = $_SERVER['HTTP_REFERER'];  // where did the post submission come from?
	}

     // if there's a valid referrer, and it's not the default log-in screen + it's got our post
     if ( !empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') && isset($_POST['fromzbslogin'])) {
          wp_redirect(zeroBS_portal_link('dash') . '?login=failed' );  // let's append some information (login=failed) to the URL for the theme to use
          exit;
	 }
	 
	

}


#} The below loads in based on the endpoint above.
add_action('zbs_portal_dashboard_endpoint', 'zeroBSCRM_clientPortal_dashboard');
function zeroBSCRM_clientPortal_dashboard(){

	//REST endpoint here skips the is_admin test too,
	if(!is_user_logged_in()){
	   return zeroBS_get_template( 'login.php' );
	}else if(!zeroBSCRM_is_rest() && !is_admin()){
		if (!zeroBSCRM_portalIsUserEnabled())

			echo zeroBS_get_template('disabled.php');
		else {

			//add actions for additional content
			do_action('zbs_pre_dashboard_content');
			
			echo zeroBS_get_template('dashboard.php');

			//add actions for additional content
			do_action('zbs_post_dashboard_content');

		}
	}
}



add_action('zbs_portal_details_endpoint', 'zeroBSCRM_clientPortal_details');
function zeroBSCRM_clientPortal_details(){

	if(!is_user_logged_in()){
		return zeroBS_get_template('login.php');
	}else{
		
		if (!zeroBSCRM_portalIsUserEnabled())
			return zeroBS_get_template('disabled.php');
		else {

			//add actions for additional content
			do_action('zbs_pre_details_content');
			
			return zeroBS_get_template('details.php');

			//add actions for additional content
			// WH NOTE: This'll NEVER fire?
			do_action('zbs_post_details_content');

		}
	}
}

add_action('zbs_portal_invoices_endpoint', 'zeroBSCRM_clientPortal_invoices');
function zeroBSCRM_clientPortal_invoices(){

	if(!is_user_logged_in()){
		return zeroBS_get_template('login.php');
	}else{
		if (!zeroBSCRM_portalIsUserEnabled()){
			return zeroBS_get_template('disabled.php');
		} else {

			//add actions for additional content
			do_action('zbs_pre_invoices_content');

			return zeroBS_get_template('invoices.php');

			//add actions for additional content
			// WH NOTE: This'll NEVER fire?
			do_action('zbs_post_invoices_content');

		}
	}

}

add_action('zbs_portal_invoices_single_endpoint', 'zeroBSCRM_clientPortal_single_invoice');
function zeroBSCRM_clientPortal_single_invoice($inv){

	// Does settings allow hashes here?
	$useHash = zeroBSCRM_getSetting('easyaccesslinks');

	if (!is_user_logged_in() && $useHash == "0"){

		// is not logged in, & is not hash-enabled:
		return zeroBS_get_template('login.php');
	
	} else {
	
		// is either logged in, or is hash-enabled

		if (!zeroBSCRM_portalIsUserEnabled() && $useHash == "0"){
		
			return zeroBS_get_template('disabled.php');
		
		} else {

			//add actions for additional content
			do_action('zbs_pre_single_invoices_content');

			// checks out, load inv
			return zeroBS_get_template('single-invoice.php');
	
			//add actions for additional content
			// WH NOTE: This'll NEVER fire?
			do_action('zbs_post_single_invoices_content');

		}
	
	}

}

add_action('zbs_portal_quotes_endpoint', 'zeroBSCRM_clientPortal_quotes');
function zeroBSCRM_clientPortal_quotes(){

	if(!is_user_logged_in()){
		return zeroBS_get_template('login.php');
	}else{
		
		if (!zeroBSCRM_portalIsUserEnabled())
			return zeroBS_get_template('disabled.php');
		else {

			//add actions for additional content
			do_action('zbs_pre_quotes_content');

			return zeroBS_get_template('quotes.php');

			//add actions for additional content
			// WH NOTE: This'll NEVER fire?
			do_action('zbs_post_quotes_content');

		}
	}

}

add_action('zbs_portal_quotes_single_endpoint', 'zeroBSCRM_clientPortal_single_quote');
function zeroBSCRM_clientPortal_single_quote(){

	// Does settings allow hashes here?
	$useHash = zeroBSCRM_getSetting('easyaccesslinks');

	if (!is_user_logged_in() && $useHash == "0"){

		// is not logged in, & is not hash-enabled:
		return zeroBS_get_template('login.php');
	
	} else {
	
		// is either logged in, or is hash-enabled

		if (!zeroBSCRM_portalIsUserEnabled() && $useHash == "0"){
		
			return zeroBS_get_template('disabled.php');
		
		} else {

			//add actions for additional content
			do_action('zbs_pre_single_quotes_content');

			// checks out, load inv
			return zeroBS_get_template('single-quote.php');

			//add actions for additional content
			// WH NOTE: This'll NEVER fire?
			do_action('zbs_post_single_quotes_content');

		}
	
	}
}

add_action('zbs_portal_transactions_endpoint', 'zeroBSCRM_clientPortal_transactions');
function zeroBSCRM_clientPortal_transactions(){

	if(!is_user_logged_in()){
		return zeroBS_get_template('login.php');
	}else{
		
		if (!zeroBSCRM_portalIsUserEnabled())
			return zeroBS_get_template('disabled.php');
		else {

			//add actions for additional content
			do_action('zbs_pre_quotes_content');

			return zeroBS_get_template('transactions.php');

			//add actions for additional content
			// WH NOTE: This'll NEVER fire?
			do_action('zbs_post_quotes_content');

		}
	}
}

add_action('zbs_portal_thanks_endpoint', 'zeroBSCRM_clientPortal_thanks');
function zeroBSCRM_clientPortal_thanks(){
	// Does settings allow hashes here?
	$useHash = zeroBSCRM_getSetting('easyaccesslinks');

	if(!is_user_logged_in()){
	   echo zeroBS_get_template( 'login.php' );
	}else{
		if (!zeroBSCRM_portalIsUserEnabled() && $useHash == "0"){
			echo zeroBS_get_template('disabled.php');
		}else {

			//add actions for additional content
			do_action('zbs_pre_thanks_content');

			echo zeroBS_get_template('thank-you.php');

			//add actions for additional content
			do_action('zbs_post_thanks_content');

		}
	}

}

add_action('zbs_portal_cancel_endpoint', 'zeroBSCRM_clientPortal_cancel');
function zeroBSCRM_clientPortal_cancel(){

	if(!is_user_logged_in()){
	   echo zeroBS_get_template( 'login.php' );
	}else{
		if (!zeroBSCRM_portalIsUserEnabled() && $useHash == "0"){
			echo zeroBS_get_template('disabled.php');
		} else {

			//add actions for additional content
			do_action('zbs_pre_cancel_content');

			echo zeroBS_get_template('cancelled.php');

			//add actions for additional content
			do_action('zbs_post_cancel_content');

		}

	}

}

function zeroBSCRM_clientPortal_customPage(){

	// because we're hijacking the running roder with template_include
	// .. we need get_header and get_footer here
	// but otherwise this mimics actions such as zeroBSCRM_clientPortal_single_invoice
	get_header();

	if (!is_user_logged_in()){
		echo zeroBS_get_template('login.php');
	} else{
		
		if (!zeroBSCRM_portalIsUserEnabled())
			echo zeroBS_get_template('disabled.php');
		else {

			// add actions for additional content
			do_action('zbs_pre_custompage_content');
			
			echo zeroBS_get_template('custom-page.php');

		}
	}

	get_footer();

}

// upsell shown to admins across whole portal as they view as admin
function zeroBSCRM_portal_adminMsg(){

	global $zbs;

	// temp fix
    if (current_user_can( 'admin_zerobs_manage_options' ) && !function_exists('zeroBSCRM_cpp_register_endpoints')){// !zeroBSCRM_isExtensionInstalled('clientportalpro')){

	 ##WLREMOVE ?>

	<script type="text/javascript">
		jQuery(function(){
			jQuery('#zbs-close-cpp-note').on( 'click', function(){
				jQuery('.zbs-client-portal-pro-note').remove();
			});
		});
	</script>
	<?php ##/WLREMOVE

	}

	return '';

}

function zeroBSCRM_portal_adminPreviewMsg($cID=-1,$extraCSS=''){
	
	// permalinks warning
	if (function_exists('zeroBSCRM_portal_plainPermaCheck')) zeroBSCRM_portal_plainPermaCheck();

 	//removed. is garish on first install. Upsell in better ways.
}

function zeroBSCRM_portal_plainPermaCheck(){

	// permalinks warning
	if (current_user_can( 'admin_zerobs_manage_options' )){
	   //allow for anyone who may be testing with DEFAULT permalinks on (but they should really NOT use default in production)
	   $permalink_structure = get_option('permalink_structure');
	   if($permalink_structure == ''){
		   $zbsWarn = __("Please Note: You are using PLAIN permalinks. Please switch to %postname% for the proper Client Portal experience (WordPress Settings->Permalinks). Some features may not work in plain permalink mode. This Permalink mode is not recommended for production installations.","zero-bs-crm"); 
               ?>
			   <div style="margin:20px;padding:10px;background:red;color:white;text-align:center;">
				   <?php echo $zbsWarn; ?>
			   </div>
		   <?php
	   }
	}

}


//the invoice endpoint
// #backward-compatibility; let's use jpcrm_get_portal_endpoint() when possible
function zeroBSCRM_portal_get_invoice_endpoint(){
    
    // default 
    $endpoint = 'invoices';
	
    // set in cpp?
	if (function_exists('zeroBSCRM_clientPortalgetEndpoint')) $endpoint = zeroBSCRM_clientPortalgetEndpoint('invoices');

	// catch any somehow empties
	if (empty($endpoint)) $endpoint = 'invoices';

	return $endpoint;
}

#} New functions here. Used as NAMED in WooSync. Please do not rename and not tell me as need to update WooSync if so
#} 1. The invoice list function 
/**
 * 
 * @param $link and $endpoint as they will differ between Portal and WooCommerce My Account
 * 
 */
function zeroBSCRM_portal_list_invoices($link = '', $endpoint = ''){

	global $zbs;

	if ($zbs->isDAL3())
		return zeroBSCRM_portal_list_invoices_v3($link,$endpoint);
	else
		return zeroBSCRM_portal_list_invoices_prev3($link,$endpoint);

} 

#} 1. The invoice list function (v3)
/**
 * 
 * @param $link and $endpoint as they will differ between Portal and WooCommerce My Account
 * 
 */
function zeroBSCRM_portal_list_invoices_v3($link = '', $endpoint = ''){

	global $wpdb;
	$uid = get_current_user_id();
	$uinfo = get_userdata( $uid );
	$cID = zeroBS_getCustomerIDWithEmail($uinfo->user_email);
	$is_invoice_admin = $uinfo->has_cap( 'admin_zerobs_invoices' );

	// if the current is a valid contact or a WP user with permissions to view invoices...
	if( $cID > 0 || $is_invoice_admin ){

		// this allows the current admin to see all invoices even if they're a contact
		if ( $is_invoice_admin ) {
			$cID = -1;
			jpcrm_portal_viewing_as_admin( __( 'Admins will see all invoices below, but clients will only see invoices assigned to them.', 'zero-bs-crm' ) );
		}

		// get invoices
		$customer_invoices = zeroBS_getInvoicesForCustomer($cID,true,100,0,false);

		// if there are more than zero invoices...
		if(count($customer_invoices) > 0){

			global $zbs;
			?><?php

			// capture output buffer: this isn't ideal but since other extensions modify this table with existing hooks, it's the best we can do.
			ob_start();
			foreach($customer_invoices as $cinv){

				//invstatus check
				$inv_status = $cinv['status'];

				// id
				$idStr = '#'.$cinv['id'];
				if (isset($cinv['id_override']) && !empty($cinv['id_override'])) $idStr = $cinv['id_override'];

				// skip drafts if not an admin with invoice access
				if ( $inv_status == __( 'Draft', 'zero-bs-crm' ) && !$is_invoice_admin ){
					continue;
				}

				if (!isset($cinv['due_date']) || empty($cinv['due_date']) || $cinv['due_date'] == -1)
					//no due date;
					$due_date_str = __("No due date", "zero-bs-crm");
				else
					$due_date_str = $cinv['due_date_date'];
				
				// view on portal (hashed?)
				$invoiceURL = zeroBSCRM_portal_linkObj($cinv['id'],ZBS_TYPE_INVOICE); //zeroBS_portal_link('invoices',$invoiceID);

				$idLinkStart = ''; $idLinkEnd = '';
				if (!empty($invoiceURL)){
					$idLinkStart = '<a href="'. $invoiceURL .'">'; $idLinkEnd = '</a>';
				}

				echo '<tr>';
					echo '<td data-title="' . $zbs->settings->get('reflabel') . '">'. $idLinkStart.$idStr . ' '. __('(view)', 'zero-bs-crm') . $idLinkEnd.'</td>';
					echo '<td data-title="' . __('Date',"zero-bs-crm") . '">' . $cinv['date_date'] . '</td>';
					echo '<td data-title="' . __('Due Date',"zero-bs-crm") . '">' . $due_date_str . '</td>';
					echo '<td data-title="' . __('Total',"zero-bs-crm") . '">' . zeroBSCRM_formatCurrency($cinv['total']) . '</td>';
					echo '<td data-title="' . __('Status',"zero-bs-crm") . '"><span class="status '. $inv_status .'">'.$cinv['status'].'</span></td>';

					do_action('zbs-extra-invoice-body-table', $cinv['id']);

				//	echo '<td class="tools"><a href="account/invoices/274119/pdf" class="pdf_download" target="_blank"><i class="fa fa-file-pdf-o"></i></a></td>';
				echo '</tr>';
			}
			$invoices_to_show = ob_get_contents();
			ob_end_clean();

			if ( !empty( $invoices_to_show ) ) {
				// there are invoices to show to this user, so build table
				echo '<table class="table zbs-invoice-list">';
					echo '<thead>';
						echo '<th>' . $zbs->settings->get('reflabel') . '</th>';
						echo '<th>' . __('Date','zero-bs-crm') . '</th>';
						echo '<th>' . __('Due Date','zero-bs-crm') . '</th>';
						echo '<th>' . __('Total','zero-bs-crm') . '</th>';
						echo '<th>' . __('Status','zero-bs-crm') . '</th>';
						do_action('zbs-extra-invoice-header-table');
					echo '</thead>';
					echo $invoices_to_show;
				echo '</table>';
			}
			else {
				// no invoices to show...might have drafts but no admin perms
				_e( 'You do not have any invoices yet.', 'zero-bs-crm' );
			}
		}else{
			// invoice object count for current user is 0
			_e( 'You do not have any invoices yet.', 'zero-bs-crm' );
		}
	}else{
		// not a valid contact or invoice admin user
		_e( 'You do not have any invoices yet.', 'zero-bs-crm' );
	}

}

#} 1. The invoice list function (pre v3)
/**
 * 
 * @param $link and $endpoint as they will differ between Portal and WooCommerce My Account
 * 
 */
function zeroBSCRM_portal_list_invoices_prev3($link = '', $endpoint = ''){

	global $wpdb;
	$uid = get_current_user_id();
	$uinfo = get_userdata( $uid );
	$cID = zeroBS_getCustomerIDWithEmail($uinfo->user_email);

	if($cID > 0){
		$currencyChar = zeroBSCRM_getCurrencyChr();
		$customer_invoices = zeroBS_getInvoicesForCustomer($cID,true,100,0,false);
		if(count($customer_invoices) > 0){
			echo '<table class="table zbs-invoice-list">';
            echo '<thead>';
			echo '<th>' . __('#','zero-bs-crm') . '</th>';
			echo '<th>' . __('Date','zero-bs-crm') . '</th>';
			echo '<th>' . __('Due Date','zero-bs-crm') . '</th>';
			echo '<th>' . __('Total','zero-bs-crm') . '</th>';
			echo '<th>' . __('Status','zero-bs-crm') . '</th>';
			do_action('zbs-extra-invoice-header-table');
            echo '</thead>';


			foreach($customer_invoices as $cinv){

				//invstatus check
				$inv_status = strtolower($cinv['meta']['status']);

				if($inv_status == 'draft'){
					continue;
				}
				//defaults for meta values
				if(!array_key_exists('date', $cinv['meta'])){
					$cinv['meta']['date'] = date('m/d/Y h:i:s a', time());
				}

				if(!array_key_exists('due', $cinv['meta'])){
					$cinv['meta']['due'] = -1;
				}

				// Replaced this with code taken from fixed AJAX listViewRetrieve 2.99.9.10 Oct 19
				//$invoice_due_date = zeroBSCRM_locale_utsToDate(strtotime($cinv['meta']['date']));
				$invoiced_uts = zeroBSCRM_locale_dateToUTS($cinv['meta']['date']);
				$invoiceDate = zeroBSCRM_date_i18n(-1, $invoiced_uts);

				// due?
				if (isset($cinv['meta']['due'])) {
					$due = (int)$cinv['meta']['due'];

					if ($due <= 0){
						$dueDate = $invoiceDate;
					} else {
						// calc
						$dueDate = zeroBSCRM_date_i18n(-1, ($invoiced_uts+($due*86400)));
					}
				}

				echo '<tr>';
					echo '<td data-title="' . __('#',"zero-bs-crm") . '"><a href="'. esc_url($link .  $endpoint .  '/' . $cinv['id']) .'">#'. $cinv['zbsid'] . __(' (view)', 'zero-bs-crm') . '</a></td>';
					echo '<td data-title="' . __('Date',"zero-bs-crm") . '">' . $invoiceDate . '</td>';
					echo '<td data-title="' . __('Due Date',"zero-bs-crm") . '">' . $dueDate . '</td>';
					echo '<td data-title="' . __('Total',"zero-bs-crm") . '">' . zeroBSCRM_formatCurrency($cinv['meta']['val']) . '</td>';
					echo '<td data-title="' . __('Status',"zero-bs-crm") . '"><span class="status '. $inv_status .'">'.$cinv['meta']['status'].'</span></td>';

					do_action('zbs-extra-invoice-body-table', $cinv['id']);

				//	echo '<td class="tools"><a href="account/invoices/274119/pdf" class="pdf_download" target="_blank"><i class="fa fa-file-pdf-o"></i></a></td>';
				echo '</tr>';
			}
			echo '</table>';
		}else{
			_e('You do not have any invoices yet.',"zero-bs-crm");
		}
	}else{
		_e('You do not have any invoices yet.',"zero-bs-crm");
	}


}

#} 2. The single invoice display
function jpcrm_portal_single_invoice($invID = -1,$invHash=''){

	echo zeroBSCRM_invoice_generatePortalInvoiceHTML($invID, $invHash);

}

// #backward-compatibility
// this is only used by WooSync when using the view-invoice endpoint, which
// requires a permissions check
function zeroBSCRM_portal_single_invoice($invID = -1,$invHash=''){

	// get obj_id from endpoint
	$obj_id = sanitize_text_field( get_query_var( 'view-invoice' ) );

	// check for proper perms
	if ( jpcrm_can_current_wp_user_view_object( $obj_id, ZBS_TYPE_INVOICE ) ) {
		jpcrm_portal_single_invoice($invID, $invHash);
	}
	else {
		jpcrm_show_single_obj_error_and_die();
	}

}


/* 
* Generates HTML for portal single email
*
* Previously called zeroBSCRM_quote_generatePortalQuoteHTML
*/
function jpcrm_portal_single_quote( $quote_id = -1, $quote_hash='' ){

	global $post, $zbs;

	$quote_data = zeroBS_getQuote( $quote_id, true );
	$quote_content = '';
	$acceptable = false;

	if ( !$quote_data ) {
		// something is wrong...abort!
		jpcrm_show_single_obj_error_and_die();
	}

	if ( $zbs->isDAL3() ) {

		// dal3

		// content
		if ( isset( $quote_data['content'] ) ) {
			$quote_content = $quote_data['content'];
		}

		// hash (if not passed)
		if ( isset( $quote_data['hash'] ) ) {
			$quote_hash = $quote_data['hash'];
		}

		//  acceptable?
		if ( empty( $quote_data['accepted'] ) ) {
			$acceptable = true;
		} else {
			// setting this shows it at base of quote, when accepted
			if ( $quote_data['accepted'] > 0 ) {
				$acceptedDate = $quote_data['accepted_date'];
			}
		}


	} else {

		// pre dal3

		// content
		if ( isset( $quote_data['quotebuilder'] ) && isset( $quote_data['quotebuilder']['content'] ) ) {
			$quote_content = $quote_data['quotebuilder']['content'];
		}

		//  acceptable?
		if ( empty( $quote_data['meta']['accepted'] ) ) {
			$acceptable = true;
		}

	} ?>
	<div id="zerobs-proposal-<?php echo $quote_id; ?> main" class="zerobs-proposal entry-content hentry" style="margin-bottom:50px;margin-top:0px;">

		<div class="zerobs-proposal-body"><?php echo zeroBSCRM_io_WPEditor_DBToHTML( $quote_content ); ?></div>

		<?php
		  if ( $acceptable ) {
				// js-exposed success/failure messages
				?>
					<div id="zbs-quote-accepted-<?php echo $quote_id ?>" class="alert alert-success" style="display:none;margin-bottom:5em;">
						<?php _e( 'Quote accepted, Thank you.', 'zero-bs-crm' ); ?>
					</div>
					<div id="zbs-quote-failed-<?php echo $quote_id ?>" class="alert alert-warning" style="display:none;margin-bottom:5em;">
						<?php _e( 'Quote could not be accepted at this time.', 'zero-bs-crm' ); ?>
					</div>
					<div class="zerobs-proposal-actions" id="zerobs-proposal-actions-<?php echo $quote_id; ?>">
						<h3><?php _e( 'Accept Quote?', 'zero-bs-crm' ); ?></h3>

						<button id="zbs-proposal-accept" class="button btn btn-large btn-success button-success" type="button"><?php _e( 'Accept', 'zero-bs-crm' ); ?></button>

				<?php
			}
			if ( isset( $acceptedDate ) ) {
				?>

					<div class="zerobs-proposal-actions" id="zerobs-proposal-actions-<?php echo $quote_id; ?>">
						<h3><?php _e( 'Accepted', 'zero-bs-crm' ); ?> <?php echo $acceptedDate; ?></h3>
					</div>

				<?php
			}
			
			##WLREMOVE 
			$showPoweredBy = zeroBSCRM_getSetting( 'showportalpoweredby' ) == "1";
			if ( $showPoweredBy ) {
				global $zbs;
				?>
					<div class="zerobs-proposal-poweredby"><?php _e( 'Proposals Powered by' , 'zero-bs-crm' ); ?> <a href="<?php echo $zbs->urls['home']; ?>" target="_blank">Jetpack CRM</a></div>
				<?php
			}
			##/WLREMOVE
		?>

	</div>
	<div style="clear:both"></div>
	<script type="text/javascript">
		var jpcrm_proposal_data = {
			'quote_id': '<?php echo $quote_id; ?>',
			'quote_hash': '<?php echo $quote_hash; ?>',
			'proposal_nonce': '<?php echo wp_create_nonce( 'zbscrmquo-nonce' );?>',
			'ajax_url': '<?php echo esc_url( admin_url( 'admin-ajax.php') ); ?>'
		};
	</script>
	<?php
	wp_enqueue_script('jpcrm_public_proposal_js', plugins_url('/js/ZeroBSCRM.public.proposals'.wp_scripts_get_suffix().'.js',ZBS_ROOTFILE), array( 'jquery' ), $zbs->version);

}

/*
* Outputs html which shows 'you are viewing this as an admin' dialog on portal pages
*/
function jpcrm_portal_viewing_as_admin( $admin_message = '' ){

	global $zbs;

	?><div class='wrapper' style="padding-left:20px;padding-right:20px;padding-bottom:20px;">
		
		<div class='alert alert-info'>
			<?php _e('You are viewing the Client Portal as an admin','zero-bs-crm'); ?>
			<br />
			[<?php _e('This message is only shown to admins','zero-bs-crm'); ?>]
			<?php ##WLREMOVE ?>
			<br /><a style="color:orange;font-size:18px;" href="<?php echo $zbs->urls['kbclientportal']; ?>" target="_blank"><?php _e('Learn more about the client portal','zero-bs-crm'); ?></a>
			<?php ##/WLREMOVE ?>
		</div>

		<?php zeroBSCRM_portal_adminMsg(); ?>

		<?php if ( !empty( $admin_message ) ) { ?>
		<div style="margin:20px;padding:10px;background:red;color:white;text-align:center;">
			<?php echo $admin_message; ?>
		</div>
		<?php } ?>

	</div><?php 

}

/**
 * Gets current object ID based on portal page URL.
 * 
 * @param   int $obj_type_id  object type ID
 * 
 * @return	int
 * @return	false if invalid object, bad permissions, or any other failure
 */
function jpcrm_get_obj_id_from_current_portal_page_url( $obj_type_id ) {

	global $zbs;

	// limit client portal invoice/quote view to DAL3
	if ( !$zbs->isDAL3() ) {
		return false;
	}

	// get object ID from URL
	$obj_id_or_hash = jpcrm_get_portal_single_objid_or_hash( $obj_type_id );

	// valid obj id or hash?
	if ( empty( $obj_id_or_hash ) ) {
		return false;
	}

	// if a hash...
	if ( jpcrm_is_easy_access_hash( $obj_id_or_hash ) ) {

		// fail if access via hash is not allowed
		if ( !jpcrm_can_access_portal_via_hash( $obj_type_id ) ) {
			return false;
		}

		// retrieve obj ID by hash
		$obj_id = jpcrm_get_obj_id_by_hash( $obj_id_or_hash, $obj_type_id );

		// was an invalid hash
		if ( !$obj_id ) {
			return false;
		}

	}
	else {

		// not a hash, so cast to int
		$obj_id = (int)$obj_id_or_hash;

		// fail if current user isn't allowed
		if ( !jpcrm_can_current_wp_user_view_object( $obj_id, $obj_type_id ) ) {
			return false;
		}

	}

	return $obj_id;
}

/**
 * Gets current objid or hash from query parameters
 * For use on singles, which are potentially easy-access calls
 * 
 * @param   int $obj_type_id  object type ID
 * 
 * @return	str - object id or easy-access hash, or ''
 */
function jpcrm_get_portal_single_objid_or_hash( $obj_type_id ){

	$endpoint = jpcrm_get_portal_endpoint( $obj_type_id );

	// fail if bad endpoint
	if ( !$endpoint ) {
		return '';
	}

	return sanitize_text_field( get_query_var( $endpoint ) );

}

/**
 * Returns bool if current portal access is provided via easy-access hash
 * 
 * @return	bool - true if current access is via hash
 */
function jpcrm_portal_access_is_via_hash( $obj_type_id ){

	return jpcrm_is_easy_access_hash( jpcrm_get_portal_single_objid_or_hash( $obj_type_id ) );

}



/**
 * Gets client portal endpoint name for a given object type.
 * 
 * @param   int $obj_type_id  object type ID
 * 
 * @return	str
 * @return	bool false if endpoint is not supported
 */
function jpcrm_get_portal_endpoint( $obj_type_id ) {
	$endpoint = '';

	// determine which endpoint we are on
	switch ( $obj_type_id ) {
		case ZBS_TYPE_INVOICE:
			$endpoint = 'invoices';
			break;
		case ZBS_TYPE_QUOTE:
			$endpoint = 'quotes';
			break;
		default:
			$endpoint = false;
	}

	// fail if endpoint is not supported
	if ( !$endpoint ) {
		return false;
	}

	// support custom endpoints if enabled in Client Portal Pro
	if ( function_exists( 'zeroBSCRM_clientPortalgetEndpoint' ) ) {
		$endpoint = zeroBSCRM_clientPortalgetEndpoint( $endpoint );
	}

	return $endpoint;
}

/**
 * Determines if the string is an easy-access hash or not.
 * 
 * @param   str $obj_id_or_hash
 * 
 * @return  bool
 */
function jpcrm_is_easy_access_hash( $obj_id_or_hash='' ) {
	if ( substr( $obj_id_or_hash, 0, 3 ) == 'zh-' ) {
		return true;
	}
	return false;
}

/**
 * Returns security request name by object type.
 * 
 * @param   str $raw_obj_hash
 * @param   int $obj_type_id
 * 
 * @return  int id
 * @return  bool false if no match
 */
function jpcrm_get_obj_id_by_hash( $raw_obj_hash, $obj_type_id ) {

	// remove 'zh-' prefix
	$obj_hash = substr( $raw_obj_hash, 3 );

	$security_request_name = jpcrm_get_easy_access_security_request_name_by_obj_type( $obj_type_id );

	// log request
	$request_id = zeroBSCRM_security_logRequest( $security_request_name, $obj_hash );

	// check hash
	$obj = zeroBSCRM_hashes_GetObjFromHash( $obj_hash, -1, $obj_type_id );

	// bad hash
	if ( !$obj['success'] ) {
		return false;
	}

	$obj_id = (int)$obj['data']['ID'];

	// clear request
	zeroBSCRM_security_finiRequest( $request_id );

	return $obj_id;

}

/**
 * Shows an object load error and dies.
 */
function jpcrm_show_single_obj_error_and_die() {
	$err = '<center>';
	$err .= '<h3>'.__('Error loading object','zero-bs-crm').'</h3>';
	$err .= __('Either this object does not exist or you do not have permission to view it.', 'zero-bs-crm');
	$err .= '</center>';
	echo $err;
	die();
}

/**
 * Redirect CRM contacts to Client Portal after login.
 *
 * @param   str $redirect_to            The redirect destination URL.
 * @param   int $requested_redirect_to  The requested redirect destination URL passed as a parameter.
 * @param   WP_User $wp_user            WP_User object if login was successful, WP_Error object otherwise.
 *
 * @return  str $redirect_to
 */
add_action( 'login_redirect', 'jpcrm_redirect_contacts_upon_login', 10, 3 );
function jpcrm_redirect_contacts_upon_login( $redirect_to, $request, $wp_user ) {

	if ( isset( $wp_user->roles ) && in_array( 'zerobs_customer', $wp_user->roles ) ) {
		$redirect_to = zeroBS_portal_link();
	}

	return $redirect_to;
}
