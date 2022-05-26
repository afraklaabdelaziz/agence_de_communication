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


/* ======================================================
   Helper funcs to slowly replace DAL -> DAL2
   These translate old -> new (but should slowly be phased out in general logic)
   THEY, or their old namesakes, SHOULD NOT BE USED IN NEW CODE! USE DAL2 DIRECTLY

   Note:
		
		This file contains:

		1) Translated functions from DAL1 -> DAL2 (first half) (for objets contacts, logs etc)
		2) UNtranslated functions from DAL1 (second half) concerning objects such as companies, transactions etc.

		... this file is ultimately for those who have migrated from DAL1 -> DAL2, but not yet to DAL2.5

   ====================================================== */

// ====================================================================================================================================
// ====================================================================================================================================
// ==================== MIGRATED DAL2 DB FUNCS ========================================================================================
// ====================================================================================================================================
// ====================================================================================================================================
   

function zeroBS_getCustomer($cID=-1,$withInvoices=false,$withQuotes=false,$withTransactions=false){

	global $zbs; return $zbs->DAL->getContact($cID,array(

			// with what?
			'withCustomFields'	=> true,
			'withQuotes' 		=> $withQuotes,
			'withInvoices' 		=> $withInvoices,
			'withTransactions' 	=> $withTransactions,
			'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)
			));

}

function zeroBS_getCustomerName($contactID=-1){

	global $zbs; return $zbs->DAL->getContactFullNameEtc($contactID,array(),array(
			'incFirstLineAddr' 	=> true,
			'incID'				=> true
			));

}
function zeroBS_customerName($contactID='',$contactArr=false,$incFirstLineAddr=true,$incID=true){
	
	global $zbs; return $zbs->DAL->getContactFullNameEtc($contactID,$contactArr,array(
			'incFirstLineAddr' 	=> $incFirstLineAddr,
			'incID'				=> $incID
			));
	
	
}
function zeroBS_getCustomerNameShort($contactID=-1){

	global $zbs; return $zbs->DAL->getContactFullNameEtc($contactID,array(),array(
			'incFirstLineAddr' 	=> false,
			'incID'				=> false
			));
}

function zeroBS_customerAddr($contactID='',$contactArr=array(),$addrFormat = 'short',$delimiter= ', '){
	
	global $zbs; return $zbs->DAL->getContactAddress($contactID,array(),array(
			'addrFormat'		=> $addrFormat,
			'delimiter'			=> $delimiter
			));

}

#} Returns a str of address, ($third param = 'short','full')
#} Pass an ID OR a customerMeta array (saves loading ;) - in fact doesn't even work with ID yet... lol)
function zeroBS_customerSecondAddr($contactID='',$contactArr=array(),$addrFormat = 'short',$delimiter= ', '){
	
	global $zbs; return $zbs->DAL->getContact2ndAddress($contactID,array(),array(
			'addrFormat'		=> $addrFormat,
			'delimiter'			=> $delimiter
			));

}

function zeroBS_customerEmail($contactID='',$contactArr=false){
	
	global $zbs; return $zbs->DAL->getContactEmail($contactID);

}

/**
 * Retrieves all emails againast a contact
 * For DAL2, this is only the main
 *
 * @var int contactID
 */
function zeroBS_customerEmails($contactID=''){
	
	global $zbs; return array($zbs->DAL->contacts->getContactEmail($contactID));

}

function zeroBS_customerMobile($contactID='',$contactArr=false){
	
	global $zbs; return $zbs->DAL->getContactMobile($contactID);

}


function zeroBS_customerAvatar($contactID='',$contactArr=false){
	
	global $zbs; return $zbs->DAL->getContactAvatar($contactID);

}

function zeroBS_customerAvatarHTML($contactID='',$contactArr=false,$size=100,$extraClasses=''){
	
	global $zbs; return $zbs->DAL->getContactAvatarHTML($contactID,$size,$extraClasses);

}


function zeroBS_customerCountByStatus($status=''){
	
	global $zbs; return $zbs->DAL->getContactCount(array(
			'withStatus' => $status,
			'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)));

}
function zeroBS_customerCount(){

	global $zbs; return $zbs->DAL->getContactCount(array('ignoreowner' => zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)));

}

#} Retrieves company post id if associated with customer
// note 2.5+ can have multiple co's, but this'll only return first ID, need to move away from this
function zeroBS_getCustomerCompanyID($cID=-1){

	global $zbs; $coArr = $zbs->DAL->getContactCompanies($cID);
	if (is_array($coArr) && count($coArr) > 0) return $coArr[0]['id'];

	return false;
}

#} sets company id associated with customer (note this'll override any existing val)
// note 2.5+ can have multiple co's, but this'll only add first ID, need to move away from this
function zeroBS_setCustomerCompanyID($cID=-1,$coID=-1){

	global $zbs;
	if (!empty($cID) && !empty($coID)) {
							
		return $zbs->DAL->addUpdateContactCompanies(array(
					'id' 			=> $cID,
					'companyIDs' 	=> array($coID)));
						
	}

	return false;
}
function zbsCRM_addUpdateCustomerCompany($customerID=-1,$companyID=-1){

	global $zbs;
	if (!empty($customerID) && !empty($companyID)) {
							
		return $zbs->DAL->addUpdateContactCompanies(array(
					'id' 			=> $customerID,
					'companyIDs' 	=> array($companyID)));
						
	}

	return false;

}

function zeroBS_getCustomerCount($companyID=false){

	global $zbs;

	if (!empty($companyID)){

		return $zbs->DAL->getContactCount(array('inCompany' => $companyID,'ignoreowner'=>true));

	} else {

		return $zbs->DAL->getContactCount(array('ignoreowner'=>true));

	}

	return 0;
}



#} Retrieves wp id for a customer
function zeroBS_getCustomerWPID($cID=-1){

	global $zbs; return $zbs->DAL->getContactWPID($cID);

}

#} Retrieves wp id for a customer
function zeroBS_getCustomerIDFromWPID($wpID=-1){

	global $zbs; return $zbs->DAL->getContact(-1,array(
			'WPID'=>$wpID,
			'onlyID'=>1,
			'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)));

}

#} Sets a WP id against a customer
function zeroBS_setCustomerWPID($cID=-1,$wpID=-1){

	global $zbs; return $zbs->DAL->addUpdateContactWPID(array('id'=>$cID,'WPID'=>$wpID));

}

function zeroBS_getCustomerOwner($customerID=-1){

	// Actually, this one is more accurate, as returns obj :) global $zbs; return $zbs->DAL->getContactOwner($customerID);
	return zeroBS_getOwner($customerID,true,'zerobs_customer');

}

function zeroBSCRM_getCustomerTags($hide_empty=false){
	
	global $zbs; 
	
	return $zbs->DAL->getTagsForObjType(array(
			'objtypeid'=>ZBS_TYPE_CONTACT,
			'excludeEmpty'=>$hide_empty,
			'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)));
	
}

// either or 
function zeroBSCRM_setContactTags($cID=-1,$tags=array(),$tagIDs=array(),$mode='replace'){

	if ($cID > 0){

		$args = array(

            'id'            => $cID,

            // EITHER of the following:
            //'tagIDs'        => -1,
            //'tags'          => -1,

            'mode'          => $mode
			);

			// got tags?
			if (count($tags) > 0) 
				$args['tags'] = $tags;
			else if (count($tagIDs) > 0)
				$args['tagIDs'] = $tagIDs;
			else
				return false;

		global $zbs;
		return $zbs->DAL->addUpdateContactTags($args);


	}

	return false;

}
function zeroBSCRM_getContactTagsArr($hide_empty=true){
	
	global $zbs; 
	
	return $zbs->DAL->getTagsForObjType(array(
		'objtypeid'=>ZBS_TYPE_CONTACT,
		'excludeEmpty'=>$hide_empty,
		'withCount' => true,
		'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)));
	
}
function zeroBS_getCustomerIcoHTML($cID=-1){

	$thumbHTML = '<i class="fa fa-user" aria-hidden="true"></i>';

	global $zbs; $thumbURL = $zbs->DAL->getContactAvatarURL($cID);
	if (!empty($thumbURL)) {

		$thumbHTML = '<img src="'.$thumb_url.'" alt="" />';

	}

	return '<div class="zbs-co-img">'.$thumbHTML.'</div>';
}


function zeroBS_getCustomerIDWithEmail($custEmail=''){
	/**
	 *  @var $custEmail the customer email you want to check if a contact exists for
	 * 
	 *  @return returns return $potentialRes->ID from $zbs->DAL->getContact()..
	 * 
	 */

	global $zbs; 
	return $zbs->DAL->getContact(-1,array(
			'email'=>$custEmail,
			'onlyID'=>true,
			'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)));

}

function zeroBS_searchCustomers($querystring='',$withMoneyData=false){

	// here I've shoehorned old into new,
	// NOTE: 
	// this WONT return same exact fields

	$args = array(
			'searchPhrase' => $querystring,
			'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT));

	if ($withMoneyData){

			$args['withInvoices'] = true;
			$args['withTransactions'] = true;
	}

	global $zbs; return $zbs->DAL->getContacts($args);
}


function zeroBSCRM_customerPortalDisableEnable($cID=-1,$enableOrDisable='disable'){

		if (!empty($cID)){
		
			global $zbs; 

			if ($enableOrDisable == 'disable')
				//return update_post_meta($cID, 'zbs_portal_disabled', true);
				return $zbs->DAL->updateMeta(ZBS_TYPE_CONTACT,$cID,'portal_disabled',true);
	        else 
	        	//return delete_post_meta($cID, 'zbs_portal_disabled');
	        	//delete meta does not seem to work
				return $zbs->DAL->updateMeta(ZBS_TYPE_CONTACT,$cID,'portal_disabled',false);

    }

    return false;

}

function zeroBSCRM_customerPortalPWReset($cID=-1){

		if (!empty($cID)){
		
			global $zbs; 

			$wpID = zeroBS_getCustomerWPID($cID);
			$contactEmail = zeroBS_customerEmail($cID);

			if ($wpID > 0 && !empty($contactEmail)){

				// generate new pw
				$newPassword = wp_generate_password( 12, false );

				// update
				wp_set_password($newPassword,$wpID);

				// email?

					#} check if the email is active..
					$active = zeroBSCRM_get_email_status(ZBSEMAIL_CLIENTPORTALPWREST);

					if ($active){

						// send welcome email (tracking will now be dealt with by zeroBSCRM_mailDelivery_sendMessage)

						// ==========================================================================================
						// =================================== MAIL SENDING =========================================

						// generate html
						$emailHTML = zeroBSCRM_Portal_generatePWresetNotificationHTML($newPassword,true, $contactEmail);

		                  // build send array
		                  $mailArray = array(
		                    'toEmail' => $contactEmail,
		                    'toName' => '',
		                    'subject' => zeroBSCRM_mailTemplate_getSubject(ZBSEMAIL_CLIENTPORTALPWREST),
		                    'headers' => zeroBSCRM_mailTemplate_getHeaders(ZBSEMAIL_CLIENTPORTALPWREST),
		                    'body' => $emailHTML,
		                    'textbody' => '',
		                    'options' => array(
		                      'html' => 1
		                    ),
		                    'tracking' => array( 
		                      // tracking :D (auto-inserted pixel + saved in history db)
		                      'emailTypeID' => ZBSEMAIL_CLIENTPORTALPWREST,
		                      'targetObjID' => $cID,
		                      'senderWPID' => -10,
		                      'associatedObjID' => -1 // none
		                    )
		                  );

		                  // DEBUG echo 'Sending:<pre>'; print_r($mailArray); echo '</pre>Result:';

		                  // Sends email, including tracking, via setting stored route out, (or default if none)
		                  // and logs trcking :)

							// discern del method
							$mailDeliveryMethod = zeroBSCRM_mailTemplate_getMailDelMethod(ZBSEMAIL_CLIENTPORTALPWREST);
							if (!isset($mailDeliveryMethod) || empty($mailDeliveryMethod)) $mailDeliveryMethod = -1;

							// send
							$sent = zeroBSCRM_mailDelivery_sendMessage($mailDeliveryMethod,$mailArray);


						// =================================== / MAIL SENDING =======================================
						// ==========================================================================================

					}


					return $newPassword;

			} // if wpid

    }

    return false;

}

// Returns bool of whether or not a specific customer can access client portal
function zeroBSCRM_isCustomerPortalDisabled($cID=-1){

		#} No Contact ID, no entry
		$cID = (int)$cID;
		if ($cID < 1) 
			return true;
		else {

			// return check
			global $zbs;
			return $zbs->DAL->getContactMeta($cID,'portal_disabled');

	    }

	// default = closed door
    return true;

}

// loads customer record + creates a portal user for record
// replaces zeroBSCRM_genPortalUser
function zeroBSCRM_createClientPortalUserFromRecord($cID=-1){

	if (!empty($cID)){

		global $zbs;

		// existing? 
		$existing = zeroBSCRM_getClientPortalUserID($cID);
		if (!empty($existing) || $existing > 0) return false;

		$email = $zbs->DAL->getContactEmail($cID);
		$contact = $zbs->DAL->getContact($cID,array('fields'=>array('zbsc_fname','zbsc_lname')));
		$fname = ''; if (isset($contact['fname']) && !empty($contact['fname'])) $fname = $contact['fname'];
		$lname = ''; if (isset($contact['lname']) && !empty($contact['lname'])) $lname = $contact['lname'];

		// fire
		return zeroBSCRM_createClientPortalUser($cID,$email,12,$fname,$lname);

	} 

	return false;

}

function zeroBSCRM_getClientPortalUserID($cID=-1){

	if (!empty($cID)){

			global $zbs;

			// existing?
			$existingUserID = $zbs->DAL->getContactWPID($cID);

			//first lets check if a user already exists with that email..
			$email = $zbs->DAL->getContactEmail($cID);
			if (!empty($email)){
				$userID = email_exists($email);
				if($userID != null && $userID != $existingUserID){
					//update_post_meta($cID, 'zbs_portal_wpid', $userID);
					$zbs->DAL->addUpdateContactWPID(array('id'=>$cID,'WPID'=>$userID));
				}
			}else{
				//no email in meta, but might be linked?
				//$userID = get_post_meta($cID, 'zbs_portal_wpid', true);
				$userID = $zbs->DAL->getContactWPID($cID);
			}
        return $userID;
    }
    return false;
}

function zeroBSCRM_getClientPortalUserWPObj($cID=-1){

		if (!empty($cID)){

			global $zbs;

        	//$user_id = zeroBSCRM_getClientPortalUserID($cID);
        	$user_id = $zbs->DAL->getContactWPID($cID);

        return new WP_User( $user_id );

    }

    return false;

}

// Function to update the zbs<->wp user link
function zeroBSCRM_setClientPortalUser($cID=-1,$wpUserID=-1){

	if ($cID > 0 && $wpUserID > 0){

		// update via meta
		update_post_meta($cID, 'zbs_wp_user_id', $email_exists);
		return true;

	}

	return false;

}

function zeroBSCRM_createClientPortalUser($cID=-1,$email='',$passwordLength=12,$fname='',$lname=''){

	if (!empty($cID) && !empty($email) && zeroBSCRM_validateEmail($email)){

		global $zbs;

		if ( null == email_exists( $email )) {

			// any extra assigned role? (from settings)
    		$extraRole = zeroBSCRM_getSetting('portalusers_extrarole');

			$password = wp_generate_password( $passwordLength, false );
			$user_id = wp_create_user( $email, $password, $email);
			$wpUserDeets = array(
					'ID'          =>    $user_id,
					'nickname'    =>    $email,
				);
			if (isset($fname) && !empty($fname)) $wpUserDeets['first_name'] = $fname;
			if (isset($lname) && !empty($lname)) $wpUserDeets['last_name'] = $lname;

			wp_update_user($wpUserDeets);

			// create user
			$user = new WP_User( $user_id );

			if ($user->exists()){

				// add meta
				//update_post_meta($cID, 'zbs_portal_wpid', $user_id);
				$zbs->DAL->addUpdateContactWPID(array('id'=>$cID,'WPID'=>$user_id));
				
				// add role(s)
				$user->set_role( 'zerobs_customer' );   //lets create a Jetpack CRM Customer Role...
				if (!empty($extraRole)) $user->add_role( $extraRole ); // https://stackoverflow.com/questions/27420705/how-to-assign-multiple-roles-for-a-user-in-wordpress
				
				/* pre sysemail + delivery methods

					// send welcome email
					$body = zeroBSCRM_Portal_generateNotificationHTML($password,true, $email);
					
					//tack on tracking deets.. (customer).. 
					$body = zeroBSCRM_mailTracking_addPixel($body, -10, $cID, $email, -1);

					$data = zeroBSCRM_mailTemplate_get(ZBSEMAIL_CLIENTPORTALWELCOME);
					$form = $data[0];
					$subject = $form->zbsmail_subject;

					#} get the new headers from the template from the new UI for editing these..
					$headers = zeroBSCRM_mailTemplate_getHeaders(ZBSEMAIL_CLIENTPORTALWELCOME);


					//still uses wp_mail here... 
					if(zeroBSCRM_get_email_status(ZBSEMAIL_CLIENTPORTALWELCOME)){

						wp_mail(  $email, $subject, $body, $headers );	
						
						#}CLIENT PORTAL IS ID = 1
						zeroBSCRM_mailTracking_logEmail(ZBSEMAIL_CLIENTPORTALWELCOME, $cID, -10, $email);
						
					}

				*/

				#} check if the email is active..
				$active = zeroBSCRM_get_email_status(ZBSEMAIL_CLIENTPORTALWELCOME);
				if ($active){

					// send welcome email (tracking will now be dealt with by zeroBSCRM_mailDelivery_sendMessage)

					// ==========================================================================================
					// =================================== MAIL SENDING =========================================

					// generate html
					$emailHTML = zeroBSCRM_Portal_generateNotificationHTML($password,true, $email);

	                  // build send array
	                  $mailArray = array(
	                    'toEmail' => $email,
	                    'toName' => '',
	                    'subject' => zeroBSCRM_mailTemplate_getSubject(ZBSEMAIL_CLIENTPORTALWELCOME),
	                    'headers' => zeroBSCRM_mailTemplate_getHeaders(ZBSEMAIL_CLIENTPORTALWELCOME),
	                    'body' => $emailHTML,
	                    'textbody' => '',
	                    'options' => array(
	                      'html' => 1
	                    ),
	                    'tracking' => array( 
	                      // tracking :D (auto-inserted pixel + saved in history db)
	                      'emailTypeID' => ZBSEMAIL_CLIENTPORTALWELCOME,
	                      'targetObjID' => $cID,
	                      'senderWPID' => -10,
	                      'associatedObjID' => -1 // none
	                    )
	                  );

	                  // DEBUG echo 'Sending:<pre>'; print_r($mailArray); echo '</pre>Result:';

	                  // Sends email, including tracking, via setting stored route out, (or default if none)
	                  // and logs trcking :)

						// discern del method
						$mailDeliveryMethod = zeroBSCRM_mailTemplate_getMailDelMethod(ZBSEMAIL_CLIENTPORTALWELCOME);
						if (!isset($mailDeliveryMethod) || empty($mailDeliveryMethod)) $mailDeliveryMethod = -1;

						// send
						$sent = zeroBSCRM_mailDelivery_sendMessage($mailDeliveryMethod,$mailArray);


					// =================================== / MAIL SENDING =======================================
					// ==========================================================================================

				}

				// IA
                zeroBSCRM_FireInternalAutomator('clientwpuser.new',array(
                    'id'=>$user_id,
                    'againstid' => $cID,
                    'userEmail'=> $email
                    ));

            } // / if successfully created

		} // / if not taken

	} // / if not empty

	return false;

}



// THIS IS NOW DEPRECATED db2+
// (META used to be all deets, it's now normal deets - as table)
#} Quick wrapper to future-proof.
#} Should later replace all get_post_meta's with this
function zeroBS_getCustomerMeta($cID=-1){

	// zeroBSCRM_DEPRECATEDMSG('Use of function: zeroBS_getCustomerMeta');

	global $zbs;

	//if (!empty($cID)) return get_post_meta($cID, 'zbs_customer_meta', true);
	// Return contact directly DB2+
	if (!empty($cID)) return $zbs->DAL->getContact($cID,array('ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)));

	return false;

}
// generates a 'demo' customer object (excluding custom fields)
function zeroBS_getDemoCustomer(){

	global $zbs, $zbsCustomerFields;

	$ret = array();

	$demoData = array(

		'status' => array('Lead','Customer'),
		'prefix' => array('Mr', 'Mrs', 'Miss'),
		'fname' => array('John','Jim','Mike','Melvin','Janet','Jennifer','Judy','Julie'),
		'lname' => array('Smith','Jones','Scott','Filbert'),
		'fullname' => array('John Smith','Jim Ellison','Mike Myers','Melvin Malcolms'),
		'addr1' => array('101 Red Street','26 Somerset Street','1 London Road'),
		'addr2' => array('Winchester','Leeds Village','Webleck'),
		'city' => array('London','Los Angeles','Leeds','Exeter'),
		'county' => array('London','Hertfordshire','California','Buckinghamshire'),
		'postcode' => array('A1 1XU','AO12 3RR','E1 3XG','M1 3LF'),
		'secaddr_addr1' => array('101 Red Street','26 Somerset Street','1 London Road'),
		'secaddr_addr2' => array('Winchester','Leeds Village','Webleck'),
		'secaddr_city' => array('London','Los Angeles','Leeds','Exeter'),
		'secaddr_county' => array('London','Hertfordshire','California','Buckinghamshire'),
		'secaddr_postcode' => array('A1 1XU','AO12 3RR','E1 3XG','M1 3LF'),
		'hometel' => array('01010 123 345', '01234 546 789'),
		'worktel' => array('01010 123 345', '01234 546 789'),
		'mobtel' => array('07812 345 678'),
		'email' => array('random@email.com','not.real@gmail.com','nonsense@email.com')

	);

	foreach ($zbsCustomerFields as $fK => $fV){

		$ret[$fK] = '';
		if (isset($demoData[$fK])) $ret[$fK] = $demoData[$fK][mt_rand(0, count($demoData[$fK]) - 1)];

	}

	// add fullname
	$ret['fullname'] = $demoData['fullname'][mt_rand(0, count($demoData['fullname']) - 1)];

	// fill in some randoms
	$ret['status'] = $demoData['status'][mt_rand(0, count($demoData['status']) - 1)];

	return $ret;

}
function zeroBS_getCustomerExtraMetaVal($cID=-1,$extraMetaKey=false){

	if (!empty($cID) && !empty($extraMetaKey)) {

		global $zbs;

		// quick
		$cleanKey = strtolower(str_replace(' ','_',$extraMetaKey));

		//return get_post_meta($cID, 'zbs_customer_extra_'.$cleanKey, true);
		return $zbs->DAL->getContactMeta($cID,'extra_'.$cleanKey);

	}

	return false;

}

#} sets an extra meta val
function zeroBS_setCustomerExtraMetaVal($cID=-1,$extraMetaKey=false,$extraMetaVal=false){

	if (!empty($cID) && !empty($extraMetaKey)) {

		// quick
		$cleanKey = strtolower(str_replace(' ','_',$extraMetaKey));

		global $zbs;

		//return update_post_meta($cID, 'zbs_customer_extra_'.$cleanKey, $extraMetaVal);
		return $zbs->DAL->updateMeta(ZBS_TYPE_CONTACT,$cID,'extra_'.$extraMetaKey,$extraMetaVal);

	}

	return false;

}
function zeroBS_getCustomerSocialAccounts($cID=-1){

	global $zbs;

	//if (!empty($cID)) return get_post_meta($cID, 'zbs_customer_socials', true);
	if (!empty($cID)) return $zbs->DAL->getContactSocials($cID);

	return false;
}
function zeroBS_updateCustomerSocialAccounts($cID=-1,$accArray=array()){

	if (!empty($cID) && is_array($accArray)) //return update_post_meta($cID, 'zbs_customer_socials', $accArray);
		
		global $zbs;
		#} Enact
		return $zbs->DAL->addUpdateContact(array(
			'id'			=>	$cID,
			'limitedFields'	=>array(
				array('key'=>'zbsc_tw','val'=>$accArray['tw'],'type'=>'%s'),
				array('key'=>'zbsc_li','val'=>$accArray['li'],'type'=>'%s'),
				array('key'=>'zbsc_fb','val'=>$accArray['fb'],'type'=>'%s')
				)));

	return false;
}


function zeroBSCRM_getCustomerFiles($cID=-1){

		if (!empty($cID)){

			global $zbs;

	        //return get_post_meta($cID, 'zbs_customer_files', true);
			//return $zbs->DAL->getContactMeta($cID,'files');
			return zeroBSCRM_files_getFiles('customer',$cID);

	    }

    return false;

}
// maintainIndexs keeps the files original index .e.g. 1,2 so that can match when doing portal stuff (as we're using legacy indx)
function zeroBSCRM_getCustomerPortalFiles($cID=-1){

		if (!empty($cID)){

			global $zbs;

	        //return get_post_meta($cID, 'zbs_customer_files', true);
			//return $zbs->DAL->getContactMeta($cID,'files');
			$ret = array(); $files = zeroBSCRM_files_getFiles('customer',$cID);
			$fileIndex = 0;
			if (is_array($files)) foreach ($files as $f){
			
				// APPROVED portal files
				if (isset($f['portal']) && $f['portal'] == 1) $ret[$fileIndex] = $f;

				$fileIndex++;

			}
			return $ret;

	    }

    return false;

}
function zeroBSCRM_updateCustomerFiles($cID=-1,$filesArray=false){

		if (!empty($cID)){

			global $zbs;

	        //return update_post_meta($cID, 'zbs_customer_files', $filesArray); 
			return $zbs->DAL->updateMeta(ZBS_TYPE_CONTACT,$cID,'files',$filesArray);

	    }

    return false;

}












/* 

	DEPRECATED! Use zeroBSCRM_getContactExternalSource + be aware of new format.

*/
function zeroBS_getCustomerExternalSource($cID=-1){

	zeroBSCRM_DEPRECATEDMSG('CRM Function Deprecated in v2.4+. Please use zeroBS_getExternalSource()');

	// just pass proper method now 
	return zeroBS_getExternalSource($cID,ZBS_TYPE_CONTACT);


}

/*
	|=======================================
	|	zeroBSCRM_updateContactExternalSource
	|=======================================
	| Store into a persistant meta value the "external source" for the user
	| the source will be an array of 
	| name (e.g PayPal, Stripe, Google Ads) as well as
	| HTTP referrer [i.e. who sent the lead]
	| Visitor Cookie [i.e. has this person been linked to a vistor cookie at all]
	| IF linked to a Visitor Cookie, then this can show more info in the "acquisition area"
	| ======================================

	WH:Suggest, as previously the system had ability for multiple sources (think, from stripe but also from csv),
	for clean migration, add that ability, so:

	array(
	
		'source' => 'stripe', // NOTE LOWER CASE!
		'uid' => 'dave@x.com', // Unique ID used by (stripe)
		
		// FOLLOWING NO LONGER USED (split into tracking table separate :) DB2) 
		'secondarysources' => array(),  // probs not gonna use, but if we don't, let's remove this later
		'tracking' => array(
				'referrer' 		=> 
				'ga_source' 	=>
				'ga_medium' 	=>
				'ga_name' 		=>
				'ga_term'		=>  
				'ga_content' 	=>
				'import'		=> 
		),
		'meta' => array(
		
			'formid' => 123
	
		)

	)

	NOTE: GENERIC - can be for any object type 
	(IN THIS CASE we're only using for CO/CUST)

	///================================= DAL2: 

	.. I've written two tables to form the same func as this started to: externalsources + tracking
	.. I'll remove tracking stuff from this, which'll need writing on its own later (ZBS Leads?)



	WH NOTES: 11/1/19
	:SADFACE:
	Turns out this was being used by transactions + companies in OLD DAL, and muddying up all the data. crap.
	As of 2.97.5 this is fixed by forcing pass of objType here + the migration
	... DAL2 rest of objects need to abide by this system!
*/

function zeroBS_updateExternalSource($objType=-1, $objID = -1, $source=array(), $owner=-1){

	if ($objType > 0 && $objID > 0){

		global $zbs;

		/* Parse mid-term array into what we're using here:

                $extSourceArr = array(
                    'source' => $approvedExternalSource,
                    'uid' => $externalID,
                    'secondarysources' => array(), // probs not gonna use, but if we don't, let's remove this later
                    'tracking' => array(
                      // empty for now
                    ),
                    'meta' => array(
                      // empty for now
                    ));

			.. but I've now switched these out for this:

                $extSourceArr = array(
                    'source' => $approvedExternalSource,
                    'uid' => $externalID
                    );

			.. BUT avoid using this func in future, use DAL specific

		*/

			//BUG - we were blanking out the $source here which we pass above???
			$zbs_source =''; $uid =''; 

			if (is_array($source) && isset($source['source'])) $zbs_source = $source['source'];
			if (is_array($source) && isset($source['uid'])) $uid = $source['uid'];

			// check this
			if (empty($zbs_source)) return false;

			// we still need our old support here.. otherwise transactions (which also use this)
			// will add duplicates as the meta check in zeroBS_getTransactionIDWithExternalSource
			// will be blank.. copied from ZeroBSCRM.DAL.LegacySupport.php
			if (isset($zbs_source) && isset($uid)){

				// ONLY do this for non DAL2 obj (trans + co as at 2.97.5)
				if ($objType == 5 || $objType == 2) update_post_meta($objID, 'zbs_obj_ext_'.$zbs_source, $uid);

			}


			// save down .. this isn't actually "returning" anything as mentioned in my review
			// ISSUE now caused in sync extensions that NEED the meta stored above (as checks for this when adding)
			// WH RESOLVED These two in 2.97.5, should be solid now :) ^^
			return $zbs->DAL->addUpdateExternalSource(array('data'=>array(
				// as of 2.97.5: 'contactID'=>$objID,
				'objectID' => $objID,
				'objectType' => $objType,
				'source'=>$zbs_source,
				'uid'=>$uid,
				'owner'=>$owner))
			);





	}
	
	return false;

}

// NOTE this only returns 1 (first source)
// ... can now have multiples (theoretically?)
function zeroBS_getExternalContactSource($contactID = -1){

	if ($contactID > 0){

		global $zbs;
	
		// could just use zeroBS_getExternalSource(1,1)
		$extSources = $zbs->DAL->getExternalSourcesForContact(array(
			
			'contactID'=>$contactID,

			'sortByField' 	=> 'ID',
			'sortOrder' 	=> 'ASC',
			'page'			=> 0,
			'perPage'		=> 1,
			'ignoreowner'	=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)
			));

		if (count($extSources) > 0) return $extSources[0];

	} 

	return array();

}

function zeroBS_getExternalSource($objID=-1,$objType=-1){

	if ($objID > 0 && $objType > 0){

		global $zbs;
	
		$extSources = $zbs->DAL->getExternalSource(-1,array(
			
            'objectID'         => $objID, 
            'objectType'       => $objType, 
			'ignoreowner'	=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_COMPANY)

			));

		if (is_array($extSources) && count($extSources) > 0){
			if (isset($extSources['id']))
				return $extSources;
			else
				return $extSources[0];
		}

	} 

	return array();

}

// returns a legacy style list (array of possible sources, key = stripe, val = email)
function zeroBS_getExternalSourceLegacyList($objID = -1,$objType=-1){

	if ($objID > 0 && $objType > 0){
	
        // here we have to translate into old method, for haste..
        $extRecord = zeroBS_getExternalSource($objID,$objType);

        // build an array of 'sources'
        $extList = array();

        // primary source
        if (isset($extRecord['source'])) $extList[$extRecord['source']] = $extRecord['uid'];

        return $extList;
	
	} 

	return array();

}

//new wrapper to get custom fields ONLY for a contact
function zeroBS_getContactCustomFields($cid = -1,$includeHidden=false){

	global $zbs,  $zbsFieldsEnabled;

	if($cid > 0){

		//which fields are active for the contact.
  		$fieldHideOverrides = $zbs->settings->get('fieldhides'); $fieldOverrides = array(); if (isset($fieldHideOverrides['customer'])) $fieldOverrides = $fieldHideOverrides['customer'];
		$custFields = $zbs->DAL->getActiveCustomFields(array('objtypeid'=>ZBS_TYPE_CONTACT));
		$customerMeta = zeroBS_getCustomerMeta($cid);
		$res = array();
		if (is_array($custFields)) foreach($custFields as $k => $v){

			// if hidden hide, unless $includeHidden
			if ($includeHidden || (!$includeHidden && !in_array($k, $fieldOverrides))){

				$val = ''; if (isset($customerMeta[$v[3]])) $val = $customerMeta[$v[3]];
				$res[] = array(
					'id'	=> $v[3],
					'name'	=> $v[1],
					'value'	=> $val,
					'type'	=> $v[0]
				);

			}
		}
		return $res;
	}else{
		return false;
	}
}



#} As of v1.1 can pass searchphrase
#} As of v1.2 can pass tags
#} As of v2.2 has associated func getCustomersCountIncParams for getting the TOTAL for a search (ignoring pages)
#} As of v2.2 can also get ,$withTags=false,$withAssigned=false,$withLastLog=false
#} As of v2.2 can also pass quickfilters (Damn this has got messy): lead, customer, over100, over200, over300, over400, over500
	// ... in array like ('lead')
#} 2.52+ AVOID using this, call getContacts directly plz, this is just for backward compatibility :)
function zeroBS_getCustomers(
		$withFullDetails=false, 
		$perPage=10,
		$page=0,
		$withInvoices=false,
		$withQuotes=false,
		$searchPhrase='',
		$withTransactions=false,
		$argsOverride=false,
		$companyID=false,
		$hasTagIDs='',
		$inArr = '',
		$withTags=false,
		$withAssigned=false,
		$withLastLog=false,
		$sortByField='ID',
		$sortOrder='DESC',
		$quickFilters=false,
		$ownedByID = false){

	#} Query Performance index
	#global $zbsQPI; if (!isset($zbsQPI)) $zbsQPI = array();
	#$zbsQPI['retrieveCustomers2getCustomers'] = zeroBSCRM_mtime_float();

	// $withFullDetails = irrelevant with new DB2 (always returns)
	// $argsOverride CAN NO LONGER WORK :)
	if ($argsOverride !== false) zeroBSCRM_DEPRECATEDMSG('Use of $argsOverride in zeroBS_getCustomers is no longer relevant (DB2)');

	global $zbs;

		// this needs translating for new dbfields:
		// FOR NOW
		if ($sortByField == 'post_id') $sortByField = 'ID';
		if ($sortByField == 'post_title') $sortByField = 'zbsc_lname';
		if ($sortByField == 'post_excerpt') $sortByField = 'zbsc_lname';
		
		/* we need to prepend zbsc_ when not using cf */
	    $custFields = $zbs->DAL->getActiveCustomFields(array('objtypeid'=>ZBS_TYPE_CONTACT));

		// needs to check if field name is custom field:
		$sortIsCustomField = false; if (is_array($custFields) && array_key_exists($sortByField,$custFields)) $sortIsCustomField = true;
		if (!$sortIsCustomField && $sortByField != 'ID') $sortByField = 'zbsc_'.$sortByField;


		// catch empties
		if (empty($sortByField)) $sortByField = 'ID';
		if (empty($sortOrder)) $sortOrder = 'desc';

		// legacy from dal1
		$actualPage = $page;
		if (!$zbs->isDAL2()) $actualPage = $page-1;  // only DAL1 needed this
		if ($actualPage < 0) $actualPage = 0;

		// make ARGS
		$args = array(

			// Search/Filtering (leave as false to ignore)
			'searchPhrase' => $searchPhrase,
			'inCompany'		=> $companyID,
			'inArr'			=> $inArr,
			'quickFilters'	=> $quickFilters,
			'isTagged'		=> $hasTagIDs,
			'ownedBy' 		=> $ownedByID,

			'withCustomFields'	=> true,
			'withQuotes' 		=> $withQuotes,
			'withInvoices' 		=> $withInvoices,
			'withTransactions' 	=> $withTransactions,
			'withLogs' 			=> false,
			'withLastLog'		=> $withLastLog,
			'withTags' 			=> $withTags,
			'withOwner' 		=> $withAssigned,

			'sortByField' 	=> $sortByField,
			'sortOrder' 	=> $sortOrder,
			'page'			=> $actualPage,
			'perPage'		=> $perPage,

			'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)


		);

		// here ignore owners = true the default, because we're not really forcing ownership anywhere overall,
		// when we do, we should change this/make it check
		if ($ownedByID !== false) {

			$args['ignoreowner'] = false;

		}

		//if (get_current_user_id() == 12) {
			//print_r($args);
		//}

		return $zbs->DAL->getContacts($args);

}

#} As of 2.2 - matches getCustomers but returns a total figure (no deets)
// NOTE, params are same except first 5 + withTransactions removed:
// $withFullDetails=false,$perPage=10,$page=0,$withInvoices=false,$withQuotes=false,$withTransactions=false,
// - trimmed returns for efficiency (is just a count really :o dirty.)
// https://codex.wordpress.org/Class_Reference/WP_Query
function zeroBS_getCustomersCountIncParams(
	$searchPhrase='',
	$argsOverride=false,
	$companyID=false, 
	$hasTagIDs='', 
	$inArr = '',
	$quickFilters=''){


	// $withFullDetails = irrelevant with new DB2 (always returns)
	// $argsOverride CAN NO LONGER WORK :)
	if ($argsOverride !== false) zeroBSCRM_DEPRECATEDMSG('Use of $argsOverride in zeroBS_getCustomersCountIncParams is no longer relevant (DB2)');

	global $zbs;

		// make ARGS
		$args = array(

			// Search/Filtering (leave as false to ignore)
			'searchPhrase' => $searchPhrase,
			'inCompany'		=> $companyID,
			'inArr'			=> $inArr,
			'quickFilters'	=> $quickFilters,
			'isTagged'		=> $hasTagIDs,

			// just count
			'count'			=>true,

			'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)

		);

		return (int)$zbs->DAL->getContacts($args);

}



#} same as above but wrapped in contact view link
function zeroBS_getCustomerIcoLinked($cID=-1,$incName=false,$extraClasses = '',$maxSize=100){

	$extraHTML = ''; if ($incName){

		$cName = zeroBS_getCustomerNameShort($cID);

		if (!empty($cName)) $extraHTML = '<span class="">'.$cName.'</span>';
	}

	return '<div class="zbs-co-img'.$extraClasses.'"><a href = "'. zbsLink('view',$cID,'zerobs_customer') .'">' . zeroBS_customerAvatarHTML($cID,-1,$maxSize).'</a>'.$extraHTML.'</div>';

}


#} same as above but wrapped in contact view link + semantic ui label img link
function zeroBS_getCustomerIcoLinkedLabel($cID=-1){

	$extraHTML = ''; 
	$cName = zeroBS_getCustomerNameShort($cID);
	if (!empty($cName)) 
		$extraHTML = '<span>'.$cName.'</span>';
	else {
		$cEmail = zeroBS_customerEmail($cID);
		if (!empty($cEmail)) $extraHTML = '<span>'.$cEmail.'</span>';
	}

	$extraClasses = ' ui image label';

	return '<a href="'. zbsLink('view',$cID,'zerobs_customer') .'" class="'.$extraClasses.'">' . zeroBS_customerAvatarHTML($cID).$extraHTML.'</a>';

}


#} same as above but with no image (for non-avatar mode)
function zeroBS_getCustomerLinkedLabel($cID=-1){

	$extraHTML = ''; 
	$cName = zeroBS_getCustomerNameShort($cID);
	if (!empty($cName)) 
		$extraHTML = '<span>'.$cName.'</span>';
	else {
		$cEmail = zeroBS_customerEmail($cID);
		if (!empty($cEmail)) $extraHTML = '<span>'.$cEmail.'</span>';
	}
	// for empties, add no
	if (empty($extraHTML)) $extraHTML = '<span>#'.$cID.'</span>';

	$extraClasses = ' ui label';

	return '<a href="'. zbsLink('view',$cID,'zerobs_customer') .'" class="'.$extraClasses.'">' .$extraHTML.'</a>';

}


/* Centralised delete customer func, including sub-element removal */
function zeroBS_deleteCustomer($id=-1,$saveOrphans=true){

	if (!empty($id)){

		global $zbs;

		return $zbs->DAL->deleteContact(array('id'=>$id,'saveOrphans'=>$saveOrphans));

	}

	return false;
}


function zeroBS_getCustomerIDWithExternalSource($externalSource='',$externalID=''){

	global $zbs;

	#} No empties, no random externalSources :)
	if (!empty($externalSource) && !empty($externalID) && array_key_exists($externalSource,$zbs->external_sources)){

		#} If here, is legit.
		$approvedExternalSource = $externalSource;

		global $zbs;

		return $zbs->DAL->getContact(-1,array(
				'externalSource' 	=> $approvedExternalSource,
				'externalSourceUID' => $externalID,
				'onlyID'			=> true,
				'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)
			));

	}


	return false;

}


//#MS new (middleman) until DB3.0 this was not looking at the new external sources table, so did not return an ID
#} ExternalID is name in this case :)
function zeroBS_getTransactionIDWithExternalSource($transactionExternalSource='',$transactionExternalID=''){

	/* this should be from $zbs now :)
	global $zbscrmApprovedExternalSources;
	$zbscrmApprovedExternalSources = apply_filters('zbs_approved_sources', $zbscrmApprovedExternalSources);
	*/

	// retrieve external sources from $zbs now
	global $zbs, $wpdb;

	//table name here
	$external_sources_table = $wpdb->prefix . 'zbs_externalsources';


	$ret = false;

	#} No empties, no random externalSources :)
	if (!empty($transactionExternalSource) && !empty($transactionExternalID) && array_key_exists($transactionExternalSource,$zbs->external_sources)){
			#} Check the external sources table for a match (obj_type = 5)
			$sql = $wpdb->prepare("SELECT zbss_objid FROM " . $external_sources_table . " WHERE zbss_source = %s AND zbss_uid = %s AND zbss_objtype = 5 LIMIT 1", $transactionExternalSource, $transactionExternalID);
			#} Returns false if not found
			$ret = $wpdb->get_var($sql);
	}


	return $ret;

}

function zeroBSCRM_getCustomerTagsByID($cID=-1,$justIDs=false){

		if (!empty($cID)){
		
			global $zbs;

			return $zbs->DAL->getTagsForObjID(array(
				'objtypeid'=>ZBS_TYPE_CONTACT,
				'objid'=>$cID,
				'onlyID'=>$justIDs,
				'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)));

		}

}



#} Note this hasn't been rigged to return invoices etc.
function zeroBS_getExternalSourceCustomers($externalSource='',$withFullDetails=false,$page=0,$perPage=100){

	if (!empty($externalSource)){

		global $zbs;

		return $zbs->DAL->getContacts(array(

			'externalSource' => $externalSource,
			// withFullDetails is inherent in db2 :)

			'page' => $page,
			'perPage' => $perPage,

			'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)

			));

	}

	return false;

}

function zeroBS_getExternalSourceCustomerCount($externalSource=''){

	if (!empty($externalSource)){

		global $zbs;

		return $zbs->DAL->getContacts(array(

			'externalSource' 	=> $externalSource,
			'count' 			=> true,
			'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)

			));

	}

	return false;

}
// NOTE: $objType is temporary until DB2 fully rolled out all tables
function zeroBS_getOwner($postID=-1,$withDeets=true,$objType=false){

	if ($postID !== -1){

		$retObj = false;


		// BECAUSE db2 doesn't have all objects as tables, find out the type + then switch here
		// ... we need to pass object really to third param, until we switch DB over
		if (!$objType) $objType = get_post_type($postID);

		// if not new db2:
		if ($objType !== false && $objType !== 'zerobs_customer'){

			$userIDofOwner = get_post_meta($postID, 'zbs_owner', true);

		} else {

			global $zbs;

			// DB2 (no post id)
			$userIDofOwner = $zbs->DAL->getContactOwner($postID);

		}

		// it may happen that some users don't have rights to get users? (thought)

		if (isset($userIDofOwner) && !empty($userIDofOwner)){

			// check if user can be owner (is zbs admin)
			// check on the assign, is less performance impacting
			// if (! user_can($userIDofOwner,'admin_zerobs_usr') return false;

			if ($withDeets){

				#} Retrieve owner deets
				$retObj = zeroBS_getOwnerObj($userIDofOwner);

			} else return $userIDofOwner;

		}
				
		
		return $retObj;

	} 

	return false;
}

function zeroBS_getOwnerObj($wpUserID=-1){

	if ($wpUserID !== -1){

		return array(

						'ID'=> $wpUserID,
						'OBJ'=> get_userdata($wpUserID)
				);

	}

	return false;
}

// NOTE: $objType is temporary until DB2 fully rolled out all tables
function zeroBS_setOwner($postID=-1,$ownerID=-1,$objType=false){

	if ($postID !== -1){
        
        // here we check that the potential owner CAN even own
        if (!user_can($ownerID,'admin_zerobs_usr')) return false;

		// BECAUSE db2 doesn't have all objects as tables, find out the type + then switch here
		// ... we need to pass object really to third param, until we switch DB over
		if (!$objType) $objType = get_post_type($postID);

		// if not new db2:
		if ($objType !== false && $objType !== 'zerobs_customer'){

			return update_post_meta($postID, 'zbs_owner', (int)$ownerID);

		} else {

			global $zbs;

			return $zbs->DAL->addUpdateContact(array(
					'id'			=>	$postID,
					'limitedFields'	=>array(
						array('key'=>'zbs_owner','val'=>$ownerID,'type'=>'%d')
						)));
		}

	} 

	return false;
}



#} Needed for Dave, added to core (but also to a custom extension for him). having it here too
#} will mean when we move DB his code won't break. PLS dont rename
function zeroBS_getAllContactsForOwner($owner=-1, $page=1){

	if (!empty($owner)){

		global $zbs;

		return $zbs->DAL->getContacts(array(

			'ownedBy' => $owner,
			'perPage' => 10,
			'page' 	  => $page

			));

	}

	return false;
}


function zeroBSCRM_mergeCustomers($dominantID=-1,$slaveID=-1){

   	if (!empty($dominantID) && !empty($slaveID)){

   		// load both
   		$master = zeroBS_getCustomer($dominantID);
   		$slave = zeroBS_getCustomer($slaveID,true,true,true);

   		if (isset($master['id']) && isset($slave['id'])){

   			global $zbs;

   			try {

   				// all set, merge
   				$changes = array();
   				$conflictingChanges = array();

   				$fieldPrefix = ''; if (!$zbs->isDAL2()) $fieldPrefix = 'zbsc_';

   				// copy details from slave fields -> master fields 
   					// where detail not present?
   					// into second address?

   					$masterNewMeta = false;
   					$masterHasSecondAddr = false;  // this'll let us copy over first from slave if empty :)
					$slaveHasFirstAddr = false; $slaveHasSecondAddr = false; $slaveFirstAddrStr = ''; $slaveSecondAddrStr = '';

					// if this gets filled, it'll be added as aka below
					$slaveEmailAddress = false;

   					// because these are just arrays (in meta) - we do a kind of compare, save a new ver, 
   					// ..and add any mismatches to conflicting changes in a meaningful way

					// DB2 converted these from obj[meta] -> obj

					// first, just copy through slave email if present
					if (isset($slave['email']) && !empty($slave['email'])) $slaveEmailAddress = $slave['email'];

					// we start with the master :)
					$masterNewMeta = $master;

   					global $zbsCustomerFields, $zbsAddressFields;

   					// first, any empties (excluding addr) in master, get patched from secondary
            		foreach ($zbsCustomerFields as $fieldKey => $fieldDeets){

            			// ignore addrs here
            			if (!isset($fieldDeets['migrate']) || $fieldDeets['migrate'] != 'addresses'){
            				// present in master?
            				if (!isset($master[$fieldKey]) || empty($master[$fieldKey])){

            					// NOT PRESENT IN MASTER

            					// was not set, or empty, in master
            					// present in slave?
            					if (isset($slave[$fieldKey]) && !empty($slave[$fieldKey])){

            						// a change :) - note requires zbsc_ here for some annoying reason, leaving for now
            						$masterNewMeta[$fieldPrefix.$fieldKey] = $slave[$fieldKey];

            						//hopefully DB2 doesnt..
            						// Does for now lol $masterNewMeta[$fieldKey] = $slave[$fieldKey];
            						$changes[] = __('Copied field',"zero-bs-crm").' "'.$fieldDeets[1].'" '.__('from secondary record over main record, (main was empty).',"zero-bs-crm");

            					}

            				} else {

            					// if slave had value?
            					// (no need to worry about emails, dealt with separately)
            					if (isset($slave[$fieldKey]) && !empty($slave[$fieldKey]) && $fieldKey !== 'email'){

                					// master val already present, conflicting change:
                					$conflictingChanges[] = __('Field not copied',"zero-bs-crm").' "'.$fieldDeets[1].'" '.__('from secondary record over main record, (main had value). Value was',"zero-bs-crm").' "'.$slave[$fieldKey].'"';

                				}

            				}


            			} else {

            				// ADDRESSES. Here we just use the foreach to check if the master has any secaddr fields
            				// just sets a flag used below in logic :)
            				if (substr($fieldKey, 0, 8) == 'secaddr_'){

            					// check presence (of any secaddr_ field)
            					if (isset($master[$fieldKey]) && !empty($master[$fieldKey])) $masterHasSecondAddr = true;

            					// does slave have secondary?
            					if (isset($slave[$fieldKey]) && !empty($slave[$fieldKey])) {
            						
            						// clearly has (bits of) second addr
            						$slaveHasSecondAddr = true;

            						// we also build this str which'll be shown as conflicting change (so we don't "loose" this data)
            						if (!empty($slaveSecondAddrStr)) $slaveSecondAddrStr .= ', ';
            						$slaveSecondAddrStr .= $slave[$fieldKey];

            					}

            				} else {

            					// first address
            					if (isset($slave[$fieldKey]) && !empty($slave[$fieldKey])) {

            						// clearly has (bits of) first addr
            						$slaveHasFirstAddr = true;

            						// we also build this str which'll be shown as conflicting change (so we don't "loose" this data)
            						if (!empty($slaveFirstAddrStr)) $slaveFirstAddrStr .= ', ';
            						$slaveFirstAddrStr .= $slave[$fieldKey];

            					}
            					
            				}

            			}

            		}


   					// addr's

   					// if master has no sec addr, just copy first addr from slave :)
   					if (!$masterHasSecondAddr){

   						// copy first addr from slave
   						foreach ($zbsAddressFields as $addrFieldKey => $addrFieldDeets){

   							// from slave first to master second - note requires zbsc_ here for some annoying reason, leaving for now
   							// Hopefully db2 doesnt 
   							$masterNewMeta[$fieldPrefix.'secaddr_'.$addrFieldKey] = $slave[$addrFieldKey];
   							// Does for now lol $masterNewMeta['secaddr_'.$addrFieldKey] = $slave[$addrFieldKey];


   						}
   						$changes[] = __('Copied address from secondary record into "secondary address" for main record',"zero-bs-crm");

   						// any second addr from slave just goes into logs
   						if ($slaveHasSecondAddr){

   								// provide old addr string			   								
								$conflictingChanges[] = __('Address not copied. Secondary address from secondary record could not be copied (master already had two addresses).',"zero-bs-crm")."\r\n".__('Address',"zero-bs-crm").': '."\r\n".$slaveSecondAddrStr;


   						}


   					} else {

   						// master already has two addresses, dump (any) secondary addresses into conflicting changes

   						if ($slaveHasFirstAddr){

   								// provide old addr string			   								
								$conflictingChanges[] = __('Address not copied. Address from secondary record could not be copied (master already had two addresses).',"zero-bs-crm")."\r\n".__('Address',"zero-bs-crm").': '."\r\n".$slaveFirstAddrStr;


   						}
   						if ($slaveHasSecondAddr){

   								// provide old addr string			   								
								$conflictingChanges[] = __('Address not copied. Secondary address from secondary record could not be copied (master already had two addresses).',"zero-bs-crm")."\r\n".__('Address',"zero-bs-crm").': '."\r\n".$slaveSecondAddrStr;


   						}
   					}


   				// assign social profiles from slave -> master
   				// GET THESE BEFORE updating!
   				$masterSocial = zeroBS_getCustomerSocialAccounts($dominantID);
   				$slaveSocial = zeroBS_getCustomerSocialAccounts($slaveID);

	            // UPDATE MASTER META:
	            zeroBS_addUpdateCustomer($dominantID,$masterNewMeta,'','','',false,false,false,-1,$fieldPrefix);

   				$masterNewSocial = $masterSocial;

		        global $zbsSocialAccountTypes;

		        if (count($zbsSocialAccountTypes) > 0) {

		        	foreach ($zbsSocialAccountTypes as $socialKey => $socialAccType){

			        		// master / slave has this acc?
			        		// for simplicity (not perf.) we grab which has which, first
			        		$masterHas = false; $slaveHas = false;
			        		if (is_array($masterSocial) && isset($masterSocial[$socialKey]) && !empty($masterSocial[$socialKey])) { $masterHas = true; }
			        		if (is_array($slaveSocial) && isset($slaveSocial[$socialKey]) && !empty($slaveSocial[$socialKey])) { $slaveHas = true; }

			        		// what's up.
			        		if ($masterHas && $slaveHas){

			        			// conflicting change
			        			$conflictingChanges[] = __('Social account not copied.',"zero-bs-crm").' "'.$socialAccType['name'].'" of "'.$slaveSocial[$socialKey].'" '.__('from secondary record (master already has a ',"zero-bs-crm").$socialAccType['name'].' '.__('account.',"zero-bs-crm");


			        		} elseif ($masterHas && !$slaveHas){

			        			// no change

			        		} elseif ($slaveHas && !$masterHas){

			        			// copy slave -> master
			        			$masterNewSocial[$socialKey] = $slaveSocial[$socialKey];
			   					$changes[] = __('Copied social account from secondary record into main record',"zero-bs-crm").' ('.$socialAccType['name'].').';


			        		}

			        }

			        // UPDATE SOCIAL
			        zeroBS_updateCustomerSocialAccounts($dominantID,$masterNewSocial);

			    }


   				// assign files from slave -> master

   				/* Array
				(
				    [0] => Array
				        (
				            [file] => /app/public/wp-content/uploads/zbscrm-store/aa250965422e9aea-Document-20243.pdf
				            [url] => http://zbsphp5.dev/wp-content/uploads/zbscrm-store/aa250965422e9aea-Document-20243.pdf
				            [type] => application/pdf
				            [error] => 
				            [priv] => 1
				        )

				)
				*/

   					$slaveFiles = zeroBSCRM_getCustomerFiles($slaveID);

   					if (is_array($slaveFiles) && count($slaveFiles) > 0){

   						$masterFiles = zeroBSCRM_getCustomerFiles($dominantID);

   						if (!is_array($masterFiles)) $masterFiles = array();

   						foreach ($slaveFiles as $zbsFile){

   							// add
   							$masterFiles[] = $zbsFile;

   							// changelog

                                $filename = basename($zbsFile['file']);

                                // if in privatised system, ignore first hash in name
                                if (isset($zbsFile['priv'])){

                                    $filename = substr($filename,strpos($filename, '-')+1);
                                }

   							$changes[] = __('Moved file to main record',"zero-bs-crm").' ('.$filename.')';


   						}

   						// save master files
   						zeroBSCRM_updateCustomerFiles($dominantID,$masterFiles);


   					}

   				// assign company from slave -> master

   					$masterCompany = zeroBS_getCustomerCompanyID($dominantID);
   					$slaveCompany = zeroBS_getCustomerCompanyID($slaveID);
   					if (empty($masterCompany)){

   						// slave co present, update main
   						if (!empty($slaveCompany)){

   							zeroBS_setCustomerCompanyID($dominantID,$slaveCompany);
   							$changes[] = __('Assigned main record to secondary record\'s '.jpcrm_label_company(),"zero-bs-crm").' (#'.$slaveCompany.').';


   						}


   					} else {

   						// master has co already, does slave?
   						if (!empty($slaveCompany) && $slaveCompany != $masterCompany){
							
							// conflicting change
			        		$conflictingChanges[] = __('Secondary contact was assigned to '.jpcrm_label_company().', whereas main record was assigned to another '.jpcrm_label_company().'.',"zero-bs-crm").' (#'.$slaveCompany.').';


   						}

   					}

   				// assign quotes from slave -> master

   					// got quotes?
   					if (is_array($slave['quotes']) && count($slave['quotes']) > 0){

                        $quoteOffset = zeroBSCRM_getQuoteOffset();

   						foreach ($slave['quotes'] as $quote){

                                // id for passing to logs
   								$qID = '';
                                #TRANSITIONTOMETANO
                                if (isset($quote['zbsid'])) $qID = $quote['zbsid'];

                                // for quotes, we just "switch" the owner meta :)
                                zeroBSCRM_changeQuoteCustomer($quote['id'],$dominantID);
                                $changes[] = __('Assigned quote from secondary record onto main record',"zero-bs-crm").' (#'.$qID.').';
                                

   						}



   					} // / has quotes

   				// assign invs from slave -> master

   					// got invoices?
   					if (is_array($slave['invoices']) && count($slave['invoices']) > 0){

   						foreach ($slave['invoices'] as $invoice){

                                // for invs, we just "switch" the owner meta :)
                                zeroBSCRM_changeInvoiceCustomer($invoice['id'],$dominantID);
                                $changes[] = __('Assigned invoice from secondary record onto main record',"zero-bs-crm").' (#'.$invoice['id'].').';
                                

   						}


   					} // / has invoices


   				// assign trans from slave -> master

   					// got invoices?
   					if (is_array($slave['transactions']) && count($slave['transactions']) > 0){

   						foreach ($slave['transactions'] as $transaction){

                                // for trans, we just "switch" the owner meta :)
                                zeroBSCRM_changeTransactionCustomer($transaction['id'],$dominantID);
                                $changes[] = __('Assigned transaction from secondary record onto main record',"zero-bs-crm").' (#'.$transaction['id'].').';
                                

   						}


   					} // / has invoices



   				// assign events from slave -> master

   					// get events
   					$events = zeroBS_getEventsByCustomerID($slaveID,true,10000,0);
   					if (is_array($events) && count($events) > 0){

   						foreach ($events as $event){

                                // for events, we just "switch" the meta val :)
                                zeroBSCRM_changeEventCustomer($event['id'],$dominantID);
                                $changes[] = __('Assigned event from secondary record onto main record',"zero-bs-crm").' (#'.$event['id'].').';
                                

   						}



   					} // / has invoices



   				// assign logs(?) from slave -> master

   					// for now save these as a random text meta against customer (not sure how to expose as of yet, but don't want to loose)
   					$slaveLogs = zeroBSCRM_getContactLogs($slaveID,true,10000,0); // id created name meta
   					if (is_array($slaveLogs) && count($slaveLogs) > 0){

   						/* in fact, just save as json encode :D - rough but quicker
   						// brutal str builder.
   						$logStr = '';

   						foreach ($slaveLogs as $log){

   							if (!empty($logStr)) $logStr .= "\r\n";


   						} */

        				//update_post_meta($dominantID, 'zbs_merged_customer_log_bk_'.time(), json_encode($slaveLogs)); 
        				// no $change here, as this is kinda secret, kthx
						$zbs->DAL->updateMeta(ZBS_TYPE_CONTACT,$dominantID,'merged_customer_log_bk_'.time(),$slaveLogs);

   					}


   				// assign tags(?) from slave -> master
   					
   					// get slave tags as ID array
   					$slaveTagsIDs = zeroBSCRM_getCustomerTagsByID($slaveID,true);
   					if (is_array($slaveTagsIDs) && count($slaveTagsIDs) > 0){

							// add tags to master (append mode)
							//wp_set_object_terms($dominantID, $slaveTagsIDs, 'zerobscrm_customertag', true );
   							$zbs->DAL->addUpdateObjectTags(array(
							'objid' 		=> $dominantID,
							'objtype' 		=> ZBS_TYPE_CONTACT,
							'tagIDs'		=> $slaveTagsIDs,
							'mode'			=> 'append'
							));
							$changes[] = __('Tagged main record with',"zero-bs-crm").' '.count($slaveTagsIDs).' '.__('tags from secondary record.',"zero-bs-crm");


   					}

   				// AKA / alias

   					// second email -> alias first
   					if (!empty($slaveEmailAddress)){


   						// add as alias
   						zeroBS_addCustomerAlias($dominantID,$slaveEmailAddress);
   						$changes[] = __('Added secondary record email as alias/aka of main record',"zero-bs-crm").' ('.$slaveEmailAddress.')';


   					}



   				// Customer image

   					//(for now, left to die)


   				// delete slave
   				zeroBS_deleteCustomer($slaveID,false);
   				$changes[] = __('Removed secondary record',"zero-bs-crm").' (#'.$slaveID.')';

   				// assign log for changes + conflicting changes

   					// strbuild
   					$shortDesc ='"'.$slave['name'].'" (#'.$slave['id'].') '.__('into this record',"zero-bs-crm");
   					$longDesc = '';

   						// changes 
   						if (is_array($changes) && count($changes) > 0) {

   							$longDesc .= '<strong>'.__('Record Changes',"zero-bs-crm").':</strong><br />';

   							// cycle through em
   							foreach ($changes as $c){

   								$longDesc .= '<br />'.$c;
   								
   							}

   						} else {

   							$longDesc .= '<strong>'.__('No Changes',"zero-bs-crm").'</strong>';

   						}

   						// conflicting changes
   						if (is_array($conflictingChanges) && count($conflictingChanges) > 0) {

   							$longDesc .= '<br />=============================<br /><strong>'.__('Conflicting Changes',"zero-bs-crm").':</strong><br />';

   							// cycle through em
   							foreach ($conflictingChanges as $c){

   								$longDesc .= '<br />'.$c;

   							}

   						} else {

   							$longDesc .= '<br />=============================<br /><strong>'.__('No Conflicting Changes',"zero-bs-crm").'</strong>';

   						}


   					// MASTER LOG :D
   					zeroBS_addUpdateContactLog($dominantID,-1,-1,array(
   						'type' => 'Bulk Action: Merge',
   						'shortdesc' => $shortDesc,
   						'longdesc' => $longDesc)
   					);

   					return true;

   			} catch (Exception $e){

   				// failed somehow! 
   				echo 'ERROR:'.$e->getMessage();

   			}

   		} // / if id's

   	}

   	return false;

}

/* 

	Raw first func, not to be used DIRECTLY... use wrapper if writing extension - see docs there 

	#} Needs fully formed cFields array with prefix to cfields zbsc_ e.g. "zbsc_status" or "zbsc_fname"
	#} Passing it an ID will update rather than insert
	#} Passing it a non correct Post type will crap it out... not exposable function... for WOODY only for now.. lol. Use extension function above not this.

	
	#} RE: FallbackLogs
		Passing an array like this:

			array(
				'type' => 'Form Filled',#'form_filled',
				'shortdesc' => 'Dude filled out the form x on y',
				'longdesc' => ''
			)
			
		(Long desc is optional)

	#} CURRENT Note Types: 

        'note': { label: 'Note', ico: 'fa-sticky-note-o' },
        'call': { label: 'Call', ico: 'fa-phone-square' },
        'email': { label: 'Email', ico: 'fa-envelope-o' },
        'meeting': { label: 'Meeting', ico: 'fa-users' },
        'quote__sent': { label: 'Quote: Sent', ico: 'fa-share-square-o' },
        'quote__accepted': { label: 'Quote: Accepted', ico: 'fa-thumbs-o-up' },
        'quote__refused': { label: 'Quote: Refused', ico: 'fa-ban' },
        'invoice__sent': { label: 'Invoice: Sent', ico: 'fa-share-square-o' },
        'invoice__part_paid': { label: 'Invoice: Part Paid', ico: 'fa-money' },
        'invoice__paid': { label: 'Invoice: Paid', ico: 'fa-money' },
        'invoice__refunded': { label: 'Invoice: Refunded', ico: 'fa-money' },
        'transaction': { label: 'Transaction', ico: 'fa-credit-card' },
        'tweet': { label: 'Tweet', ico: 'fa-twitter' },
        'facebook_post': { label: 'Facebook Post', ico: 'fa-facebook-official' },
        'created': { label: 'Created', ico: 'fa-plus-circle' },
        'updated': { label: 'Updated', ico: 'fa-pencil-square-o' },
        'quote_created': { label: 'Quote Created', ico: 'fa-plus-circle' },
        'invoice_created': { label: 'Invoice Created', ico: 'fa-plus-circle' },
        'form_filled': { label: 'Form Filled', ico: 'fa-wpforms'}


	#} RE: $extraMeta (This isn't used anywhere yet, talk to WH before using)

		... this is a key value array passable to add extra values to customers
		... should look like:

		$extraMeta = array(

			array('key_here',12345),
			array('another','what')

		)

		... which will add the following meta to a customer:

		zbs_customer_extra_key_here = 12345
		zbs_customer_extra_another = what

		... BRUTALLY - no checking, just overwrites! (so be careful)



	#} Re: $automatorPassthrough

		... adding anything here allows it to be passed through to the internal automator (which currently sets notes)
		... this means you can pass an array with note str overrides... e.g.

		array(

			'note_override' => array(
		
						'type' => 'Form Filled',#'form_filled',
						'shortdesc' => 'Dude filled out the form x on y',
						'longdesc' => ''					

			)

		)

		... see recipes to see what's useful :)

*/
function zeroBS_addUpdateCustomer(

		$cID = -1,
		$cFields = array(),

		$externalSource='',
		$externalID='',
		$customerDate='',

		$fallBackLog = false,
		$extraMeta = false,
		$automatorPassthrough = false,
		$owner = -1,

		$metaBuilderPrefix = 'zbsc_'

		){

	#} return
	$ret = false;

	global $zbs;

	#} Basics - /--needs status
	#} 27/09/16 - WH - Removed need for zeroBS_addUpdateCustomer to have a "status" passed with customer (defaults to lead for now if not present)
	if (isset($cFields) && count($cFields) > 0){ #} && isset($cFields['zbsc_status'])

		#} New flag
		$newCustomer = false; $originalStatus = '';



			if ($cID > 0){

				#} Retrieve / check?
				#} Na... v1.1 .lol
				#} lol we're now 1.1.18 and still not validating here :o
				$postID = $cID;

				#} Build "existing meta" to pass, (so we only update fields pushed here)
				//DB2 $existingMeta = zeroBS_getCustomerMeta($postID);
				$existingMeta = $zbs->DAL->getContact($postID,array());

				#} status changed? WH is this the correct meta, i.e. isn't it just status? 
				#} WHLOOK - think this should be just $existingMeta['status']
				#} WHLOOK - see zbs_customer_meta in wp_postmeta table (see dump below)
				#} WHLOOK - I've tested the below and it logs status changes OK. 
				if (isset($existingMeta) && is_array($existingMeta) && isset($existingMeta['status']) && !empty($existingMeta['status'])) $originalStatus = $existingMeta['status'];

				/*
				a:26:{s:6:"status";s:4:"Lead";s:6:"prefix";s:3:"Mrs";s:5:"fname";s:5:"Wendy";s:3:"cf3";s:7:"Gee Wai";s:5:"lname";s:3:"Lam";s:5:"addr1";s:0:"";s:5:"addr2";s:0:"";s:4:"city";s:0:"";s:6:"county";s:0:"";s:8:"postcode";s:0:"";s:7:"country";s:0:"";s:8:"addr_cf1";s:0:"";s:13:"secaddr_addr1";s:0:"";s:13:"secaddr_addr2";s:0:"";s:12:"secaddr_city";s:0:"";s:14:"secaddr_county";s:0:"";s:16:"secaddr_postcode";s:0:"";s:15:"secaddr_country";s:0:"";s:11:"secaddr_cf1";s:0:"";s:7:"hometel";s:11:"07544154582";s:7:"worktel";s:11:"07544154582";s:6:"mobtel";s:11:"07544154582";s:5:"email";s:21:"wendy.lam86@gmail.com";s:5:"notes";s:0:"";s:3:"cf1";s:0:"";s:3:"cf2";s:0:"";}
				*/


				#} need to check the dates here. If a date is passed which is BEFORE the current "created" date then overwrite the date with the new date. If a date is passed which is AFTER the current "created" date, then do not update the date..
				/*
					reasons that a date might be before the created:-
						* we are importing history and transaction dates are in NEW -> OLD order in API (eg Stripe)
						* we have imported from PayPal, but the customer had a stripe transaction BEFORE this
						* we have imported from Groove, and a ticket was opened BEFORE they purchased 
						* for new "VIEW" UI of activity, the customerMeta CREATED date will always be the start of the record
						* when using the "VISITORS" extension, it's possible that the first VISIT (cookie'd) will preceed this
						* so when capturing email (and visitor has cookie zbscrm_visitor_id) it will update to first visit
						* zbscrm_visitor_id_browsing_history 

				*/
				#} date changed - created is only in the wp_posts table in DB v1.0
				//WH: This is already got in DB2 above $customerWPpost = get_post($postID);

				$originalDate = time();
				if (isset($existingMeta) && is_array($existingMeta) && isset($existingMeta['createduts']) && !empty($existingMeta['createduts'])) $originalDate = $existingMeta['createduts'];

				if (!empty($customerDate) && $customerDate != ''){

					#} DATE PASSED TO THE FUNCTION
					$customerDateTimeStamp = strtotime($customerDate);
					#} ORIGINAL POST CREATION DATE 
					// no need, db2 = UTS $originalDateTimeStamp = strtotime($originalDate);
					$originalDateTimeStamp = $originalDate;

					#} Compare, if $customerDateTimeStamp < then update with passed date
					if($customerDateTimeStamp < $originalDateTimeStamp){

						// straight in there :)
						  $zbs->DAL->addUpdateContact(array(
								'id'			=>	$postID,
								'limitedFields'	=>array(
									array('key'=>'zbsc_created','val'=>$customerDateTimeStamp,'type'=>'%d')
									)));
					}
				}

			// WH changed 20/05/18 
			// 20/05/18 - Previously this would reload the EXISTING database data 
			// THEN 'override' any passed fields
			// THEN save that down
			// ... this was required when we used old meta objs. (pre db2)
			// ... so if we're now DAL2, we can do away with that and simply pass what's to be updated and mode do_not_update_blanks
			$existingMeta = array();


			} else {

				// DB2: Probably can rethink this whole func, (do we even need it?) e.g. header post mentality used here
				// for now I've just edited in place, but def refactor in time
				
				#} Set flag
				$newCustomer = true;

				
				
				/*
				#} Build header 
				$headerPost = array(
										'post_type' => 'zerobs_customer',
										'post_status' => 'publish',
										'comment_status' => 'closed'
									);

				#} This still could do with Validation, eventually.
				if (!empty($customerDate)) $headerPost['post_date'] = $customerDate;

				#} Insert
				$postID = wp_insert_post($headerPost);




				// straight in there :) don't really need to header-line it, but for now just doing
			  	$postID = $zbs->DAL->addUpdateContact(array(
					'id'			=>	-1,
					'limitedFields'	=>array(
						array('key'=>'zbsc_created','val'=>$customerDate,'type'=>'%d')
						)));
				*/
		
				#} Set up empty meta arr

					#} DATE PASSED TO THE FUNCTION
					$customerDateTimeStamp = strtotime($customerDate);
					#} DAL2 needs timestamp :)
					$existingMeta = array('created' => $customerDateTimeStamp);

			}

			/* dealt with in DAL2 now :)
			#} Note this is only for customers which already exist... 
			#} Default status setting using existing meta as wrapper... will be overwritten with whatever is passed, if passed... #defaultstatus
			if (isset($existingMeta) && is_array($existingMeta) && !isset($existingMeta['status'])){ //(!isset($existingMeta['zbsc_status']) || empty($existingMeta['zbsc_status']))) {

				$defaultStatus = zeroBSCRM_getSetting('defaultstatus');
				$existingMeta['status'] = $defaultStatus; // 'Lead';

			}
			*/


			#} Build meta

			#} Build using centralised func below, passing any existing meta (updates not overwrites)
			$zbsCustomerMeta = zeroBS_buildCustomerMeta($cFields,$existingMeta,$metaBuilderPrefix,'',true);
			

			/* dealt with in DAL2 now :)
			// log any change of status
			if (!empty($zbsCustomerMeta['status']) && !empty($originalStatus) && $zbsCustomerMeta['status'] != $originalStatus){

				// status change
				$statusChange = array(
					'from' => $originalStatus,
					'to' => $zbsCustomerMeta['status']
					);
			} */


			/* dealt with in DAL2 now :)
			#} If no status, and default is specified in settings, add that in :)
			if (is_null($zbsCustomerMeta['status']) || !isset($zbsCustomerMeta['status']) || empty($zbsCustomerMeta['status'])){

				$defaultStatusStr = zeroBSCRM_getSetting('defaultstatus');

				// allow "empties" if (!empty($defaultStatusStr)) 
				$zbsCustomerMeta['status'] = $defaultStatusStr;

			}
			*/


            $we_have_tags = false; //set to false.. duh..

            # TAG customer (if exists) - clean etc here too 
            if(!empty($cFields['tags'])){
				$tags 		= $cFields['tags'];
				#} Santize tags
				if(is_array($tags)){
					$customer_tags = filter_var_array($tags,FILTER_SANITIZE_STRING); 
					$we_have_tags = true;
				}

                if($we_have_tags){

                	$zbsCustomerMeta['tags'] = array();
					foreach($customer_tags as $cTag){

						// find/add tag
						//wp_set_object_terms($postID , $cTag, 'zerobscrm_customertag', true );
						$tagID = $zbs->DAL->addUpdateTag(array(
							'data'=>array(
								'objtype' 		=> ZBS_TYPE_CONTACT,
								'name' 			=> $cTag
								)));

						if (!empty($tagID)) $zbsCustomerMeta['tags'][] = $tagID;

					}
				}
			}

			#} Add external source/externalid
			#} No empties, no random externalSources :)
			$extSourceArr = -1; $approvedExternalSource = ''; #} As this is passed to automator :)
			if (!empty($externalSource) && !empty($externalID) && array_key_exists($externalSource,$zbs->external_sources)){

				#} If here, is legit.
				$approvedExternalSource = $externalSource;

				#} Add/Update record flag
                // 2.4+ Migrated away from this method to new update_post_meta($postID, 'zbs_customer_ext_'.$approvedExternalSource, $externalID);
                // 2.52+ Moved to new DAL method :)
                
                $extSourceArr = array(
                    'source' => $approvedExternalSource,
                    'uid' => $externalID
                    );

               	// add/update
                // DB2, this is just used below :)zeroBS_updateExternalSource($postID,$extSourceArr);
                $zbsCustomerMeta['externalSources'] = array($extSourceArr);

			} #} Otherwise will just be a random customer no ext source

			#} Got owner?
			if ($owner !== -1) $zbsCustomerMeta['owner'] = $owner;

			#} Update record (All IA is now fired intrinsicly )
            // DB2 update_post_meta($postID, 'zbs_customer_meta', $zbsCustomerMeta);
			return $zbs->DAL->addUpdateContact(array(
					'id'	=>	$cID,
					'data' 	=> $zbsCustomerMeta,
					'extraMeta' => $extraMeta,
					'automatorPassthrough' => $automatorPassthrough,
					'fallBackLog' => $fallBackLog
					));


			/* This now get's passed above, and dealt with by DAL
            #} Any extra meta keyval pairs?
            $confirmedExtraMeta = false;
            if (isset($extraMeta) && is_array($extraMeta)) {

            	$confirmedExtraMeta = array();

                	foreach ($extraMeta as $k => $v){

                	#} This won't fix stupid keys, just catch basic fails... 
                	$cleanKey = strtolower(str_replace(' ','_',$k));

                	#} Brutal update
                	//update_post_meta($postID, 'zbs_customer_extra_'.$cleanKey, $v);
                	$zbs->DAL->updateMeta(ZBS_TYPE_CONTACT,$postID,'extra_'.$cleanKey,$v);

                	#} Add it to this, which passes to IA
                	$confirmedExtraMeta[$cleanKey] = $v;

                }

            } */


            /* NOW DEALT WITH IN DAL2 :)


			#} INTERNAL AUTOMATOR 
			#} & 
			#} FALLBACKS

			if ($newCustomer){

				#} Add to automator
				zeroBSCRM_FireInternalAutomator('customer.new',array(
	                'id'=>$postID,
	                'customerMeta'=>$zbsCustomerMeta,
	                'extsource'=>$approvedExternalSource,
	                'automatorpassthrough'=>$automatorPassthrough, #} This passes through any custom log titles or whatever into the Internal automator recipe.
	                'customerExtraMeta'=>$confirmedExtraMeta #} This is the "extraMeta" passed (as saved)
	            ));


				// (WH) Moved this to fire on the IA... 
				// do_action('zbs_new_customer', $postID);   //fire the hook here...

			} else {

				#} Customer Update here (automator)?
				#} TODO


				#} FALLBACK 
				#} (This fires for customers that weren't added because they already exist.)
				#} e.g. x@g.com exists, so add log "x@g.com filled out form"
				#} Requires a type and a shortdesc
				if (
					isset($fallBackLog) && is_array($fallBackLog) 
					&& isset($fallBackLog['type']) && !empty($fallBackLog['type'])
					&& isset($fallBackLog['shortdesc']) && !empty($fallBackLog['shortdesc'])
				){

					#} Brutal add, maybe validate more?!

					#} Long desc if present:
					$zbsNoteLongDesc = ''; if (isset($fallBackLog['longdesc']) && !empty($fallBackLog['longdesc'])) $zbsNoteLongDesc = $fallBackLog['longdesc'];

						#} Only raw checked... but proceed.
						$newOrUpdatedLogID = zeroBS_addUpdateContactLog($postID,-1,-1,array(
							#} Anything here will get wrapped into an array and added as the meta vals
							'type' => $fallBackLog['type'],
							'shortdesc' => $fallBackLog['shortdesc'],
							'longdesc' => $zbsNoteLongDesc
						));


				}



	            // catch dirty flag (update of status) (note, after update_post_meta - as separate)
	            //if (isset($_POST['zbsc_status_dirtyflag']) && $_POST['zbsc_status_dirtyflag'] == "1"){
				// actually here, it's set above
				if (isset($statusChange) && is_array($statusChange)){

	                // status has changed

	                // IA
	                zeroBSCRM_FireInternalAutomator('customer.status.update',array(
	                    'id'=>$postID,
	                    'againstid' => $postID,
	                    'userMeta'=> $zbsCustomerMeta,
	                    'from' => $statusChange['from'],
	                    'to' => $statusChange['to']
	                    ));

	            }


			} */



			#} REQ?
			#} MAKE SURE if you change any post_name features you also look at: "NAMECHANGES" in this file (when a post updates it'll auto replace these...)
	        #$newCName = zeroBS_customerName('',$zbsMeta,true,false)


			#} Return customerID if success :)
			//$ret = $postID;



	}



	return $ret;

}





function zeroBSCRM_getLog($lID=-1){

	if ($lID !== -1){

		global $zbs;

		return $zbs->DAL->getLog(array(
			'id' => $lID,
			'incMeta' => true,
			'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)));

	} 

	return false;
}

// NOTE This is not to be used, as LOGS can now be against other objs, not just customer etc!!!
function zeroBSCRM_getLogs($customerID=false,$withFullDetails=false,$perPage=100,$page=0,$searchPhrase='',$argsOverride=false){

	zeroBSCRM_DEPRECATEDMSG('zeroBSCRM_getLogs has been replaced by zeroBSCRM_getContactLogs etc. or (better still) DAL2 calls direct');
	exit();

}

function zeroBSCRM_getContactLogs($customerID=-1,$withFullDetails=false,$perPage=100,$page=0,$searchPhrase='',$argsOverride=false){
			

		if (!empty($customerID) && $customerID !== -1 && $customerID !== false){

			global $zbs;
			return $zbs->DAL->getLogsForObj(array(

					'objtype' => ZBS_TYPE_CONTACT,
					'objid' => $customerID,

					'searchPhrase' 	=> $searchPhrase,

					'incMeta' 	=> $withFullDetails,

					'sortByField' 	=> 'zbsl_created',
					'sortOrder' 	=> 'DESC',
					'page'			=> $page,
					'perPage'		=> $perPage,
					'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)

				));

		}
}

function zeroBSCRM_getObjCreationLog($objID=-1,$objType=ZBS_TYPE_CONTACT){
			

		if (!empty($objID) && $objID !== -1 && $objID !== false){

			global $zbs;
			return $zbs->DAL->getLogsForObj(array(

					'objtype' => $objType,
					'objid' => $objID,

					'notetype' 	=> 'Created',

					'incMeta' 	=> true,

					'sortByField' 	=> 'zbsl_created',
					'sortOrder' 	=> 'ASC',
					'page'			=> 0,
					'perPage'		=> 1,
					'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)

				));

		}
}

function zeroBSCRM_logTypeStrToDB($str=''){

	$x = str_replace(' ','_',$str);
	$x = str_replace(':','_',$x);
	return strtolower($x);

}

function zeroBSCRM_getAllContactLogs($withFullDetails=false,$perPage=100,$page=0,$searchPhrase='',$argsOverride=false){
			
	global $zbs;
	return $zbs->DAL->getLogsForANYObj(array(

			'objtype' => ZBS_TYPE_CONTACT,

			'searchPhrase' 	=> $searchPhrase,

			'incMeta' 	=> $withFullDetails,

			'sortByField' 	=> 'zbsl_created',
			'sortOrder' 	=> 'DESC',
			'page'			=> $page,
			'perPage'		=> $perPage,
			'ignoreowner'	=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)

		));
}
function zeroBSCRM_getCompanyLogs($companyID=false,$withFullDetails=false,$perPage=100,$page=0,$searchPhrase='',$argsOverride=false){
			
		/* if (!empty($companyID)){
			/* not yet migrated
			global $zbs;

			return $zbs->DAL->getLogsForObj(array(

					'objtype' => ZBS_TYPE_COMPANY,
					'objid' => $companyID,

					'searchPhrase' 	=> $searchPhrase,

					'incMeta' 	=> $withFullDetails,

					'sortByField' 	=> 'zbsl_created',
					'sortOrder' 	=> 'DESC',
					'page'			=> $page,
					'perPage'		=> $perPage,
					'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)

				));
			
		}*/

		return zeroBSCRM_getCPTLogs($companyID,$withFullDetails,$perPage,$page,$searchPhrase,$argsOverride);
}


#} Wrapper func for "logs" against customer
function zeroBSCRM_getCPTLogs($customerID=false,$withFullDetails=false,$perPage=100,$page=0,$searchPhrase='',$argsOverride=false){

	
		#} If $argsOverride is passed, override all of this :)
		if (is_array($argsOverride)){

			$args = $argsOverride;

		} else {

				#} Normal args build

					#} Page legit? - lazy check
					if ($perPage < 0) $perPage = 100; else $perPage = (int)$perPage;

					$args = array (
						'post_type'              => 'zerobs_log',
						'post_status'            => 'publish',
						'posts_per_page'         => $perPage,
						'order'                  => 'DESC',
						'orderby'                => 'post_date'
					);
					
					#} Add page if page... - dodgy meh
					$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
					if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

					#} Add search phrase... if #WH v1.1 https://codex.wordpress.org/Class_Reference/WP_Query#Parameters
					if (!empty($searchPhrase)) $args['s'] = $searchPhrase;

					#} in cust
					if ($customerID > 0){
						// NOTE: , this is confusing because 'owner' is contact here
					   $args['meta_key']   = 'zbs_logowner';
					   $args['meta_value'] = (int)$customerID;

					}

					#Debug print_r($args); # exit();

		}

		$logList = get_posts( $args );

		#} QPI
		#$zbsQPI['retrieveCustomers2getCustomers'] = round(zeroBSCRM_mtime_float() - #$zbsQPI['retrieveCustomers2getCustomers'],2).'s';
		#$zbsQPI['retrieveCustomers2getCustomersFill'] = zeroBSCRM_mtime_float();

		$ret = array();

		foreach ($logList as $logEle){


		/*
			$retObj = array(

				'id' => 	$logEle->ID,
				'created' => $logEle->post_date_gmt,
				'name' => 	$logEle->post_title,
				'author'=>get_the_author_meta('display_name',$logEle->post_author),
				'authorid' => $logEle->post_author

			);

			#} Full details?
			if ($withFullDetails) {
				
				$retObj['meta'] 		= get_post_meta($logEle->ID, 'zbs_log_meta', true);
				if ($customerID > 0){
					#} no need to get it :)
					$retObj['owner'] = $customerID;
				} else {
					$retObj['owner'] 		= get_post_meta($logEle->ID, 'zbs_logowner', true);
				}

			}
		*/

			global $zbs;

			// BECAUSE in DAL2 we moved all data into 1 object, we get meta here, but then we 'copy it' into main ret obj :)
			$retObjMeta = get_post_meta($logEle->ID, 'zbs_log_meta', true);
			$retObj = $retObjMeta;
			// if compatability support (e.g. old extensions present), also dump this into a sub-array
			// (gross, inefficient etc.)
			if (!is_array($retObj)) $retObj = array();
			// for now, just leaving in until we move all logs if ($zbs->db1CompatabilitySupport) 
				$retObj['meta'] = $retObjMeta;

			$retObj['id'] = $logEle->ID;
			$retObj['owner'] = get_post_meta($logEle->ID, 'zbs_logowner', true);
			$retObj['created'] = $logEle->post_date_gmt;
			$retObj['createduts'] = strtotime($logEle->post_date_gmt); // from 2.99 needs this (ahead of DAL2 release, makes logs time-relative inc timezone offset)
			$retObj['name'] = $logEle->post_title;
			$retObj['author'] = get_the_author_meta('display_name',$logEle->post_author);
			$retObj['authorid'] = $logEle->post_author;



			$ret[] = $retObj;


		}



		return $ret;
}


function zeroBSCRM_getMostRecentLog($objID=false,$withFullDetails=false,$restrictToTypes=false){

	zeroBSCRM_DEPRECATEDMSG('zeroBSCRM_getMostRecentLog has been replaced by zeroBSCRM_getMostRecentContactLog etc. or (better still) DAL2 calls direct');
	exit();
}

function zeroBSCRM_getMostRecentContactLog($objID=false,$withFullDetails=false,$restrictToTypes=false){

		if (!empty($objID)){

			global $zbs;

			return $zbs->DAL->getLogsForObj(array(

					'objtype' => ZBS_TYPE_COMPANY,
					'objid' => $objID,

					'notetypes' => $restrictToTypes,

					'incMeta' 	=> $withFullDetails,

					'sortByField' 	=> 'zbsl_created',
					'sortOrder' 	=> 'DESC',
					'page'			=> 0,
					'perPage'		=> 1,
					'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)

				));

		}
}

function zeroBSCRM_getMostRecentCompanyLog($objID=false,$withFullDetails=false,$restrictToTypes=false){

		if (!empty($objID)){

			global $zbs;

			return $zbs->DAL->getLogsForObj(array(

					'objtype' => ZBS_TYPE_COMPANY,
					'objid' => $objID,

					'notetypes' => $restrictToTypes,

					'incMeta' 	=> $withFullDetails,

					'sortByField' 	=> 'zbsl_created',
					'sortOrder' 	=> 'DESC',
					'page'			=> 0,
					'perPage'		=> 1,
					'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)

				));

		}
}

function zeroBS_searchLogs($querystring){

	global $zbs;

	return $zbs->DAL->getLogsForANYObj(array(
			'searchPhrase' => $querystring,
			'perPage' => 100,
			'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)
		));
}

function zeroBS_allLogs(){

	global $zbs;

	return $zbs->DAL->getLogsForANYObj(array(
			'perPage' => 100,
			'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)
		));
}




function zeroBS_addUpdateLog(

		$cID = -1,
		$logID = -1,
		$logDate = -1,

		/* 
		#} Process with metaboxes.php funcs, is easier :)

			$zbsNoteAgainstPostID
			$zbsNoteType
			$zbsNoteShortDesc
			$zbsNoteLongDesc

			NOTE!: as of 31/05/17 WOODY started putting 
			'meta_assoc_id' in these - e.g. if it's an 'email sent' log, this meta_assoc_id will be the CAMPAIGN id
			'meta_assoc_src' would then be mailcamp

		*/

		$noteFields = array(),

		$objType='',
		$owner = -1

		){


		if ($objType == 'zerobs_customer'){
			//zeroBSCRM_DEPRECATEDMSG('zeroBS_addUpdateLog has been replaced by zeroBS_addUpdateContactLog etc. or (better still) DAL2 calls direct');
			return zeroBS_addUpdateContactLog($cID,$logID,$logDate,$noteFields,$owner);
		} else 
			// fallback
			return zeroBS_addUpdateCPTLog($cID,$logID,$logDate,$noteFields);

}
function zeroBS_addUpdateContactLog(
		$cID = -1,
		$logID = -1,
		$logDate = -1,
		$noteFields = array(),
		$owner = -1
		){

		$logType = ''; $logShortDesc = ''; $logLongDesc = ''; $logMeta = -1; $logCreated = -1;
		if (isset($noteFields['type'])) $logType = zeroBSCRM_permifyLogType($noteFields['type']);
		if (isset($noteFields['shortdesc'])) $logShortDesc = $noteFields['shortdesc'];
		if (isset($noteFields['longdesc'])) $logLongDesc = $noteFields['longdesc'];
		if (isset($noteFields['meta'])) $logMeta = $noteFields['meta'];						
		if ($logDate !== -1) 
			$logCreated = strtotime($logDate);
		else 
			$logCreated = -1;

		global $zbs;

		return $zbs->DAL->addUpdateLog(array(

				'id'			=> $logID,
				'owner'			=> $owner,

				// fields (directly)
				'data'			=> array(

					'objtype' 	=> ZBS_TYPE_CONTACT,
					'objid' 	=> $cID,
					'type' 		=> $logType,
					'shortdesc' => $logShortDesc,
					'longdesc' 	=> $logLongDesc,

					'meta' 		=> $logMeta,

					'created'	=> $logCreated
					
				)));

}
// STILL OLD STYLE, FOR NOW!
function zeroBS_addUpdateCPTLog(

		$cID = -1,
		$logID = -1,
		$logDate = -1,
		$noteFields = array()

		){

	#} return
	$ret = false;


	#} Basics - needs status
	if (isset($cID) && $cID > 0 && isset($noteFields) && count($noteFields) > 0 && isset($noteFields['type']) && isset($noteFields['shortdesc'])){


			#} Save / update log
			if (isset($logID) && !empty($logID) && $logID > 0){

				#} existing to update
				$logPostID = $logID;

					#} Add meta vals - brutal override.
					update_post_meta($logPostID,'zbs_log_meta',$noteFields);
					#} shouldn't be movable... update_post_meta($logPostID,'zbs_logowner',$cID);

			} else {

				#} New log 
					if($logDate == -1){
					$logPostID = wp_insert_post(array(
						'post_type' => 'zerobs_log',
						'post_status' => 'publish',
						'comment_status' => 'closed',
						'page_title' => $noteFields['type'].': '.sanitize_text_field($noteFields['shortdesc'].substr(0,150))
					));			
					}else{
					$logPostID = wp_insert_post(array(
						'post_type' => 'zerobs_log',
						'post_status' => 'publish',
						'comment_status' => 'closed',
						'post_date'		=> $logDate,
						'page_title' => $noteFields['type'].': '.sanitize_text_field($noteFields['shortdesc'].substr(0,150))
					));							
					}
		

					if ($logPostID){

						#} Add meta vals
						update_post_meta($logPostID,'zbs_log_meta',$noteFields);
						update_post_meta($logPostID,'zbs_logowner',$cID);


					}


			}


			#} Return customerID if success :)
			$ret = $logPostID;


	}



	return $ret;

}




// allows us to lazily 'hotswap' wp_set_post_terms in extensions (e.g. pre DAL2 it'll just fire wp_set_post_terms)
// ... here it does DAL2 equiv
// WH Note: if using old WP method (wp_set_post_terms) can pass tags or tagIDS - DB2 currently only accepts tagIDs - to add in
// ... to get around this I've temp added $usingTagIDS=true flag
function zeroBSCRM_DAL2_set_post_terms($cID=-1,$tags=array(),$taxonomy='zerobscrm_customertag',$append=true,$usingTagIDS=true){

	// we only switch out for customer tags, rest just go old way
	if ($taxonomy == 'zerobscrm_customertag'){

		global $zbs;

		$mode = 'replace'; if ($append) $mode = 'append';

		$fieldName = 'tagIDs'; if (!$usingTagIDS) $fieldName = 'tags';

		return $zbs->DAL->addUpdateObjectTags(array(
														'objid' 		=> $cID,
														'objtype' 		=> ZBS_TYPE_CONTACT,
														$fieldName		=> $tags,
														'mode'			=> $mode
												));

	} else {

		return wp_set_post_terms($cID,$tags,$taxonomy,$append);
		
	}

}

// allows us to lazily 'hotswap' wp_set_object_terms in extensions (e.g. pre DAL2 it'll just fire wp_set_object_terms)
// ... here it does DAL2 equiv
// WH Note: if using old WP method (wp_set_object_terms) can pass tags or tagIDS - DB2 currently only accepts tagIDs - to add in
// ... to get around this I've temp added $usingTagIDS=true flag
function zeroBSCRM_DAL2_set_object_terms($cID=-1,$tags=array(),$taxonomy='zerobscrm_customertag',$append=true,$usingTagIDS=true){

	// we only switch out for customer tags, rest just go old way
	if ($taxonomy == 'zerobscrm_customertag'){

		global $zbs;

		$mode = 'replace'; if ($append) $mode = 'append';

		$fieldName = 'tagIDs'; if (!$usingTagIDS) $fieldName = 'tags';

		return $zbs->DAL->addUpdateObjectTags(array(
														'objid' 		=> $cID,
														'objtype' 		=> ZBS_TYPE_CONTACT,
														$fieldName		=> $tags,
														'mode'			=> $mode
												));

	} else {

		//https://codex.wordpress.org/Function_Reference/wp_set_object_terms
		return wp_set_object_terms($cID,$tags,$taxonomy,$append);
		
	}

}

// allows us to lazily 'hotswap' wp_set_object_terms in extensions (e.g. pre DAL2 it'll just fire wp_set_object_terms)
// ... here it does DAL2 equiv
// WH Note: if using old WP method (wp_remove_object_terms) can pass tags or tagIDS - DB2 currently only accepts tagIDs - to add in
// ... to get around this I've temp added $usingTagIDS=true flag
function zeroBSCRM_DAL2_remove_object_terms($cID=-1,$tags=array(),$taxonomy='zerobscrm_customertag',$usingTagIDS=true){

	// we only switch out for customer tags, rest just go old way
	if ($taxonomy == 'zerobscrm_customertag'){

		global $zbs;

		$fieldName = 'tagIDs'; if (!$usingTagIDS) $fieldName = 'tags';

		return $zbs->DAL->addUpdateObjectTags(array(
														'objid' 		=> $cID,
														'objtype' 		=> ZBS_TYPE_CONTACT,
														$fieldName		=> $tags,
														'mode' 			=> 'remove'
												));

	} else {

		//https://codex.wordpress.org/Function_Reference/wp_remove_object_terms
		return wp_remove_object_terms($cID,$tags,$taxonomy);
		
	}

}

// returns a system setting for ignore ownership
// ... ownership ignored, unless the setting is on + not admin
function zeroBSCRM_DAL2_ignoreOwnership($objType=1){

	global $zbs;

	// FOR NOW EVERYONE CAN SEE EVERYTHING
	// Later add - strict ownership? isn't this a platform UPSELL?
	// if ($zbs->settings->get('perusercustomers') && !current_user_can('administrator')) return false;

	return true;

}

function zeroBSCRM_DEPRECATEDMSG($msg=''){

	echo '<div class="zbs info msg">'.$msg.'</div>';

}


// quick linking to edit pages etc. Imperitive as used to Translate DAL1->DAL2
function zbsLink($key='',$id=-1,$type='zerobs_customer',$prefixOnly=false,$taxonomy=false){
	
	global $zbs;

	switch ($key){

		case 'view':
			if ($id > 0) {
				if ($type == 'zerobs_customer' || $type == ZBS_TYPE_CONTACT)
					return admin_url('admin.php?page=zbs-add-edit&action=view&zbsid='.$id);
				elseif ($type == 'zerobs_company' || $type == ZBS_TYPE_COMPANY)
					return admin_url('admin.php?page=zbs-add-edit&action=view&zbstype=company&zbsid='.$id);
				else
					return admin_url('post.php?action=edit&post='.$id);
			} else if ($prefixOnly){
				if ($type == 'zerobs_customer' || $type == ZBS_TYPE_CONTACT)
					return admin_url('admin.php?page=zbs-add-edit&action=view&zbsid=');
				elseif ($type == 'zerobs_company' || $type == ZBS_TYPE_COMPANY)
					return admin_url('admin.php?page=zbs-add-edit&action=view&zbstype=company&zbsid=');
				else
					return admin_url('post.php?action=edit&post=');
			}
			break;
		case 'edit':
			if ($id > 0) {
				if ($type == 'zerobs_customer' || $type == ZBS_TYPE_CONTACT)
					return admin_url('admin.php?page=zbs-add-edit&action=edit&zbsid='.$id);
				else if ($type == 'segment' || $type == ZBS_TYPE_SEGMENT)
					return admin_url('admin.php?page=zbs-add-edit&action=edit&zbstype='.$zbs->DAL->objTypeKey(ZBS_TYPE_SEGMENT).'&zbsid='.$id);
				else if ($type == 'zerobs_quote')
					return get_edit_post_link($id);
				else
					return admin_url('post.php?action=edit&post='.$id);
			} else if ($prefixOnly){
				if ($type == 'zerobs_customer' || $type == ZBS_TYPE_CONTACT)
					return admin_url('admin.php?page=zbs-add-edit&action=edit&zbsid=');
				else if ($type == 'segment' || $type == ZBS_TYPE_SEGMENT)
					return admin_url('admin.php?page=zbs-add-edit&action=edit&zbstype='.$zbs->DAL->objTypeKey(ZBS_TYPE_SEGMENT).'&zbsid=');
				else
					return admin_url('post.php?action=edit&post=');
			}
			break;
		case 'create':
			if (!empty($type) && $type != 'zerobs_customer' && $type != ZBS_TYPE_CONTACT && $type != 'segment' && $type != ZBS_TYPE_SEGMENT && $type != 'mailcampaign' && $type != 'mailsequence') 
				return admin_url('post-new.php?post_type='.$type);
			else if ($type == 'zerobs_customer' || $type == ZBS_TYPE_CONTACT) 
				return admin_url('admin.php?page=zbs-add-edit&action=edit&zbstype='.$zbs->DAL->objTypeKey(ZBS_TYPE_CONTACT));
			else if ($type == 'segment' || $type == ZBS_TYPE_SEGMENT) 
				return admin_url('admin.php?page=zbs-add-edit&action=edit&zbstype='.$zbs->DAL->objTypeKey(ZBS_TYPE_SEGMENT));
			else if ($type == 'mailcampaign' || $type == 'mailsequence'){
				global $zeroBSCRM_MailCampaignsslugs; if (isset($zeroBSCRM_MailCampaignsslugs)){
					return admin_url('admin.php?page='.$zeroBSCRM_MailCampaignsslugs['editcamp']);
				}
			}
			break;
		case 'tags':
			if (!empty($type) && $type != 'zerobs_customer' && $type != ZBS_TYPE_CONTACT && !empty($taxonomy)){
				return admin_url('edit-tags.php?taxonomy='.$taxonomy.'&post_type='.$type);
			} else if ($type == 'zerobs_customer' || $type == ZBS_TYPE_CONTACT){
					return admin_url('admin.php?page='.$zbs->slugs['tagmanager'].'&tagtype=contact');
			}
			break;

		case 'listtagged':
			switch ($type){

				case 'zerobs_customer':
					return admin_url('admin.php?page='.$zbs->slugs['managecontacts'].'&zbs_tag='.$taxonomy);
					break;
				case 'zerobs_transaction':
					return admin_url('admin.php?page='.$zbs->slugs['managetransactions'].'&zbs_tag='.$taxonomy);
					break;
			}
			break;

		case 'email':
			switch ($type){

				case 'zerobs_customer':
					if ($prefixOnly)
						return zeroBSCRM_getAdminURL($zbs->slugs['emails']).'&zbsprefill=';
					else
						return zeroBSCRM_getAdminURL($zbs->slugs['emails']).'&zbsprefill='.$id;
					break;

			}
			break;

	}
	

	// if $key isn't in switch, assume it's a slug :)
	return admin_url('admin.php?page='.$key);

	// none? DASH then!
	//return admin_url('admin.php?page=zerobscrm-dash');

}



// ====================================================================================================================================
// ====================================================================================================================================
// ==================== / MIGRATED DAL2 DB FUNCS ======================================================================================
// ====================================================================================================================================
// ====================================================================================================================================
   







// ====================================================================================================================================
// ====================================================================================================================================
// ==================== UNMIGRATED DAL1 DB FUNCS ======================================================================================
// ====================================================================================================================================
// ====================================================================================================================================
   
/* ======================================================
   The following is OLD CPT funcs that used to remain in DAL.php
   These FUNCS get replaced in DAL2.5 by DAL2.Helpers.php
   DAL 1 funcs
		- The following remains unchanged from DAL1
		- ... because in DAL2 we only migrated some of the objects (not all)   
		- ... leaving these in-tact
		- ... DAL2.Helpers.php supercedes this file and has all functions rewritten
   ====================================================== */


/* ======================================================
  Wrapper (Helper) Functions
   ====================================================== */


function zeroBS_getAssigneeEmail($cID =-1){
  if ($cID !== -1){
    
    //$userID = get_post_meta($cID, 'zbs_owner', true );
    $ownerID = zeroBS_getOwner($cID,false);

    if (!empty($ownerID)){
      #} return 
        return get_the_author_meta( 'user_email', $ownerID );
      }
  }
  return false;
}

function zeroBS_getUserMobile($uID =-1){
	if ($uID !== -1){
		if (!empty($uID)){
			$mobile_number = get_user_meta( 'mobile_number', $uID );
			$mobile_number = apply_filters( 'zbs_filter_mobile', $mobile_number); 
			return $mobile_number;
		}
		return false;
	}
}

function zeroBS_getAssigneeMobile($cID =-1){
	if ($cID !== -1){

	    //$userID = get_post_meta($cID, 'zbs_owner', true );
	    $ownerID = zeroBS_getOwner($cID,false);

		if (!empty($ownerID)){
			$mobile_number = get_user_meta( 'mobile_number', $ownerID );
			$mobile_number = apply_filters( 'zbs_filter_mobile', $mobile_number, $ownerID); 
			return $mobile_number;
		}
		return false;
	}
}

	#} Retrieves the user email which "created" a post (of any type)
	#} Originally named zeroBS_getCreatorEmail (changed v3.0.13+)
	#} (hence latter two vars are just fill-in to avoid notices)
	function zeroBS_getObjOwnerWPEmail($postID =-1, $objType = 'zerobs_customer', $objTypeID=ZBS_TYPE_CONTACT){

		return zeroBS_post_getCreatorEmail($postID);

	}

   # Quote Status (from list view)
   // if returnAsInt - will return -1 for not published, -2 for not accepted, or 14int timestamp for accepted
    function zeroBS_getQuoteStatus( $item=false, $returnAsInt=false ) {

        #} marked accepted?
        $acceptedArr = false;
        if (isset($item['meta']) && isset($item['meta']['accepted'])) $acceptedArr = $item['meta']['accepted'];

        # HERE TODO:
        # if acceptedArr = output "accepted xyz"
        # else if !templated outut "not yet published"
        # else if templated output "not yet accepted"

        if (is_array($acceptedArr)){

            if ($returnAsInt) return $acceptedArr[0];

            $td = '<strong>'.__('Accepted',"zero-bs-crm").' ' . date(zeroBSCRM_getDateFormat(),$acceptedArr[0]) . '</strong>';

        } else {
                
            #} get extra deets
            $zbsTemplated = get_post_meta($item['id'], 'templated', true);
            if (!empty($zbsTemplated)) {
                
                if ($returnAsInt) return -2;

                #} is published
                $td = '<strong>'.__('Created, not yet accepted',"zero-bs-crm").'</strong>';

            } else {

                if ($returnAsInt) return -1;

                #} not yet published
                $td = '<strong>'.__('Not yet published',"zero-bs-crm").'</strong>';

            }


        }


        return $td;
    }



    #} Minified get all settings

    function zeroBSCRM_getAllSettings(){

		global $zbs;

		$zbs->checkSettingsSetup();
    	return $zbs->settings->getAll();
    	
    }

	#} Minified get setting func
	function zeroBSCRM_getSetting($key,$freshFromDB=false){

		global $zbs;

		$zbs->checkSettingsSetup();
		return $zbs->settings->get($key,$freshFromDB);

	}

	// checks if a setting is set to 1
	function zeroBSCRM_isSettingTrue($key){
		global $zbs;
		$setting = $zbs->settings->get($key);
		if ($setting == "1") return true;
		return false;
	}

	function zeroBSCRM_getNextQuoteID(){

		#} Get offset, if used :) and add to default (0)
		$defaultStartingQuoteID = zeroBSCRM_getQuoteOffset();

		#} Retrieves option, and returns, is dumb for now.
		return (int)get_option('quoteindx',$defaultStartingQuoteID)+1;

	}

	function zeroBSCRM_setMaxQuoteID($newMax=0){

		$existingMax = zeroBSCRM_getNextQuoteID();

		if ($newMax >= $existingMax){

			#} harsh override! doesn't even check! :o
			return (int)update_option('quoteindx',$newMax, false);

		}

		return false;
	}

	function zeroBSCRM_getNextInvoiceID(){

		#} Get offset, if used :) and add to default (0)
		$defaultStartingInvoiceID = zeroBSCRM_getInvoiceOffset();

		#} Retrieves option, and returns, is dumb for now.
		return (int)get_option('invoiceindx',$defaultStartingInvoiceID)+1;

	}

	function zeroBSCRM_setMaxInvoiceID($newMax=0){

		$existingMax = zeroBSCRM_getNextInvoiceID();

		if ($newMax >= $existingMax){

			#} harsh override! doesn't even check! :o
			return (int)update_option('invoiceindx',$newMax, false);

		}

		return false;

	}

	#} Minified get offset func
	function zeroBSCRM_getQuoteOffset(){

		global $zbs;
		$offset = (int)$zbs->settings->get('quoteoffset');

		if (empty($offset) || $offset < 0) $offset = 0;

		return $offset;

	}

	#} Minified get offset func
	function zeroBSCRM_getInvoiceOffset(){

		global $zbs;
		$offset = (int)$zbs->settings->get('invoffset');

		if (empty($offset) || $offset < 0) $offset = 0;

		return $offset;

	}
	// SEE getCustomersCountIncParams
	//function zeroBS_getCustomerCountIncParams($companyID=false){}

/* ======================================================
  / Wrapper (Helper) Functions
   ====================================================== */




/* ======================================================
   DAL Functions
   ====================================================== */




/* Company ver */
function zeroBS_addUpdateCompany(

		$coID = -1,
		$coFields = array(),
		$externalSource='',
		$externalID='',
		$companyDate='',
		$fallBackLog = false,
		$extraMeta = false,
		$automatorPassthrough = false

		){


	#} return
	$ret = false;


	#} Basics - /--needs status
	#} 27/09/16 - WH - Removed need for zeroBS_addUpdateCustomer to have a "status" passed with customer (defaults to lead for now if not present)
	if (isset($coFields) && count($coFields) > 0){ #} && isset($coFields['zbsc_status'])

	global $zbs;

		#} New flag
		$newCompany = false;


			if ($coID > 0){

				#} Retrieve / check?
				#} Na... v1.1 .lol
				#} lol we're now 1.1.18 and still not validating here :o
				$postID = $coID;

				#} Build "existing meta" to pass, (so we only update fields pushed here)
				$existingMeta = zeroBS_getCompanyMeta($postID);

			} else {

				#} Set flag
				$newCompany = true;

				#} Build header 
				$headerPost = array(
										'post_type' => 'zerobs_company',
										'post_status' => 'publish',
										'comment_status' => 'closed'
									);

				#} This still could do with Validation, eventually.
				if (!empty($companyDate)) $headerPost['post_date'] = $companyDate;

				#} Insert
				$postID = wp_insert_post($headerPost);

				#} Set up empty meta arr
				$existingMeta = array();

			}

			#} Default status setting using existing meta as wrapper... will be overwritten with whatever is passed, if passed... #defaultstatus
			if (isset($existingMeta) && is_array($existingMeta) && !isset($existingMeta['zbsc_status'])) $existingMeta['zbsc_status'] = 'Lead';

			#} Add external source/externalid
			#} No empties, no random externalSources :)
			$approvedExternalSource = ''; #} As this is passed to automator :)
			if (!empty($externalSource) && !empty($externalID) && array_key_exists($externalSource,$zbs->external_sources)){

				#} If here, is legit.
				$approvedExternalSource = $externalSource;

				#} Add/Update record flag
                // 2.4+ Migrated away from this method to new update_post_meta($postID, 'zbs_company_ext_'.$approvedExternalSource, $externalID);
                // 2.52+ Moved to new DAL method :)
                
                $extSourceArr = array(
                    'source' => $approvedExternalSource,
                    'uid' => $externalID
                    );

               	// add/update (type = 2 = company)
                zeroBS_updateExternalSource(2,$postID,$extSourceArr);             

			} #} Otherwise will just be a random customer no ext source

			#} Build meta
			if (!empty($postID)){

				#} Build using centralised func below, passing any existing meta (updates not overwrites)
				$zbsCompanyMeta = zeroBS_buildCompanyMeta($coFields,$existingMeta);

				#} Update record
                update_post_meta($postID, 'zbs_company_meta', $zbsCompanyMeta);

                #} Any extra meta keyval pairs?
                if (isset($extraMeta) && is_array($extraMeta)) foreach ($extraMeta as $k => $v){

                	#} This won't fix stupid keys, just catch basic fails... 
                	$cleanKey = strtolower(str_replace(' ','_',$k));

                	#} Brutal update
                	update_post_meta($postID, 'zbs_company_extra_'.$cleanKey, $v);

                }

			}

			#} INTERNAL AUTOMATOR 
			#} & 
			#} FALLBACKS
			if ($newCompany){

				#} Add to automator
				zeroBSCRM_FireInternalAutomator('company.new',array(
	                'id'=>$postID,
	                'companyMeta'=>$zbsCompanyMeta,
	                'extsource'=>$approvedExternalSource,
	                'automatorpassthrough'=>$automatorPassthrough #} This passes through any custom log titles or whatever into the Internal automator recipe.
	            ));

			} else {

				#} Customer Update here (automator)?
				#} TODO


				#} FALLBACK 
				#} (This fires for customers that weren't added because they already exist.)
				#} e.g. x@g.com exists, so add log "x@g.com filled out form"
				#} Requires a type and a shortdesc
				if (
					isset($fallBackLog) && is_array($fallBackLog) 
					&& isset($fallBackLog['type']) && !empty($fallBackLog['type'])
					&& isset($fallBackLog['shortdesc']) && !empty($fallBackLog['shortdesc'])
				){

					#} Brutal add, maybe validate more?!

					#} Long desc if present:
					$zbsNoteLongDesc = ''; if (isset($fallBackLog['longdesc']) && !empty($fallBackLog['longdesc'])) $zbsNoteLongDesc = $fallBackLog['longdesc'];

						#} Only raw checked... but proceed.
						$newOrUpdatedLogID = zeroBS_addUpdateLog($postID,-1,-1,array(
							#} Anything here will get wrapped into an array and added as the meta vals
							'type' => $fallBackLog['type'],
							'shortdesc' => $fallBackLog['shortdesc'],
							'longdesc' => $zbsNoteLongDesc
						),'zerobs_company');


				}


			}




			#} REQ?
			#} MAKE SURE if you change any post_name features you also look at: "NAMECHANGES" in this file (when a post updates it'll auto replace these...)
	        #$newCName = zeroBS_customerName('',$zbsMeta,true,false)


			#} Return customerID if success :)
			$ret = $postID;


	}



	return $ret;

}

function zeroBS_buildCompanyMeta($arraySource=array()){

	$zbsCompanyMeta = array();

        global $zbsCompanyFields;

        foreach ($zbsCompanyFields as $fK => $fV){

            $zbsCompanyMeta[$fK] = '';

            if (isset($arraySource['zbsc_'.$fK])) {

                switch ($fV[0]){

                    case 'tel':

                        // validate tel?
                        $zbsCompanyMeta[$fK] = sanitize_text_field($arraySource['zbsc_'.$fK]);
                        preg_replace("/[^0-9 ]/", '', $zbsCompanyMeta[$fK]);
                        break;

                    case 'price':

                        // validate tel?
                        $zbsCompanyMeta[$fK] = sanitize_text_field($arraySource['zbsc_'.$fK]);
                        $zbsCompanyMeta[$fK] = preg_replace('@[^0-9\.]+@i', '-', $zbsCompanyMeta[$fK]);
                        $zbsCompanyMeta[$fK] = floatval($zbsCompanyMeta[$fK]);
                        break;


                    case 'textarea':

                        $zbsCompanyMeta[$fK] = zeroBSCRM_textProcess($arraySource['zbsc_'.$fK]);

                        break;


                    default:

                        $zbsCompanyMeta[$fK] = sanitize_text_field($arraySource['zbsc_'.$fK]);

                        break;


                }


            }


        }

    return $zbsCompanyMeta;
}






// ALIASES - BECAUSE these are CUSTOM TABLE, no need to migrate with DB2 :D
#} See if already in use/exists
function zeroBS_canUseCustomerAlias($alias=''){

	if (!empty($alias)) {

		// is customer with this email?
		$existing = zeroBS_getCustomerIDWithEmail($alias);

		if (!empty($existing)) return false; 

		global $wpdb,$ZBSCRM_t;

		$query = $wpdb->prepare( "SELECT ID FROM ".$ZBSCRM_t['aka']." WHERE aka_type = %d AND aka_alias = %s", zeroBS_getAKAType('customer'), $alias);

		$aliasID = $wpdb->get_var($query);

		// has alias in there already?
		if (!empty($aliasID)) return false;

		// usable
		return true;

	}

	return false;
}

#} Get specific alias if exists
function zeroBS_getCustomerAlias($cID=-1,$alias=''){

	if (!empty($cID) && !empty($alias)) {

		global $wpdb,$ZBSCRM_t;

		$query = $wpdb->prepare( "SELECT ID,aka_alias,aka_create,aka_lastupdated FROM ".$ZBSCRM_t['aka']." WHERE aka_type = %d AND aka_id = %d AND aka_alias = %s", zeroBS_getAKAType('customer'), $cID, $alias);


		$alias = $wpdb->get_row($query, ARRAY_A);

		// check it + return
		if (is_array($alias)) return $alias;

	}

	return false;
}

#} Get specific alias if exists
function zeroBS_getCustomerAliasByID($cID=-1,$aliasID=-1){

	if (!empty($cID) && !empty($aliasID)) {

		global $wpdb,$ZBSCRM_t;

		$query = $wpdb->prepare( "SELECT ID,aka_alias,aka_created,aka_lastupdated FROM ".$ZBSCRM_t['aka']." WHERE aka_type = %d AND aka_id = %d AND ID = %d", zeroBS_getAKAType('customer'), $cID, $aliasID);

		$alias = $wpdb->get_row($query, ARRAY_A);

		// check it + return
		if (is_array($alias)) return $alias;

	}

	return false;
}

#} Get All Aliases against a contact.
function zeroBS_getCustomerAliases($cID=-1){

	if (!empty($cID)) {

		global $wpdb,$ZBSCRM_t;

		$query = $wpdb->prepare( "SELECT ID,aka_alias,aka_created,aka_lastupdated FROM ".$ZBSCRM_t['aka']." WHERE aka_type = %d AND aka_id = %d", zeroBS_getAKAType('customer'), $cID );

		$aliases = $wpdb->get_results($query, ARRAY_A);

		// check it + return
		if (is_array($aliases) && count($aliases) > 0) return $aliases;

	}

	return false;
}

#} add Aliases to a contact.
function zeroBS_addCustomerAlias($cID=-1,$alias=''){

	if (!empty($cID) && !empty($alias)) {

		// check not already there
		$existing = zeroBS_getCustomerAlias($cID,$alias);


		if (!is_array($existing)){

			// insert

			global $wpdb,$ZBSCRM_t;

			if ($wpdb->insert( 
				$ZBSCRM_t['aka'], 
				array( 
					'aka_type' => zeroBS_getAKAType('customer'), 
					'aka_id' => $cID , 
					'aka_alias' => $alias , 
					'aka_created' => time() , 
					'aka_lastupdated' => time()
				), 
				array( 
					'%d', 
					'%d' , 
					'%s' , 
					'%d' , 
					'%d' 
				) 
			)){

				// success
				return $wpdb->insert_id;

			} else {
				return false;
			}

		} else {

			// return true, already exists
			return true;

		}

	}

	return false;
}

#} add Aliases to a contact.
function zeroBS_removeCustomerAlias($cID=-1,$alias=''){

	if (!empty($cID) && !empty($alias)) {

		// check there/find ID
		$existing = zeroBS_getCustomerAlias($cID,$alias);

		if (is_array($existing)){

			// just brutal :)

			global $wpdb,$ZBSCRM_t;
		
			return $wpdb->delete($ZBSCRM_t['aka'], array( 'ID' => $existing['ID'] ), array( '%d' ) );

		}	

	}

	return false;
}

#} add Aliases to a contact.
function zeroBS_removeCustomerAliasByID($cID=-1,$aliasID=-1){

	if (!empty($cID) && !empty($aliasID)) {

		// check there/find ID
		$existing = zeroBS_getCustomerAliasByID($cID,$aliasID);

		if (is_array($existing)){

			// just brutal :)

			global $wpdb,$ZBSCRM_t;
		
			return $wpdb->delete($ZBSCRM_t['aka'], array( 'ID' => $existing['ID'] ), array( '%d' ) );

		}	

	}

	return false;
}

// Alias "type"
// returns a hard-typed "type"
// e.g. 1 = customer, 2 = company etc.
function zeroBS_getAKAType($typestr=''){


	switch($typestr){

		case 'customer':

			return 1;

			break;


	}

	return -1;

}

	

function zeroBS_getTransactionsRange($ago, $period){

	$args = array(
	    'post_type' => 'zerobs_transaction',
	    'post_status' => 'publish',
	    'orderby' => 'date',
	    'order' => 'DESC',
		'posts_per_page'=>-1,

	    // Using the date_query to filter posts from last week
	    'date_query' => array(
	        array(
	            'after' => $ago . ' ' .$period. ' ago'
	        )
	    )
	); 

	$query = new WP_Query( $args );
	return $query->posts;
}


#} SIMPLE MIKE FUNCTION TO NICELY ADD AN EVENT. :) 
function zeroBS_addUpdateEvent($eventID = -1, $eventFields = array()){

	/*
	
		-EVENT FIELDS ARE
		$event_fields = array(

			'title' => event title
			'customer' => ID of the customer the event is for (if any)
			'notes' => customer notes string
			'to' => to date, format date('m/d/Y H') . ":00:00";
			'from' => from date, format date('m/d/Y H') . ":00:00";
			'notify' => 0 or 24 (never or 24 hours before)
			'complete' => 0 or 1 (boolean),
			'owner' => who owns the event (-1 for no one),
			'event_id' => the event ID


		);

	*/

	#FORMIKENOTES - is this as safe as sanitize_text_fields throughout?
	$zbsEventMeta = filter_var_array($eventFields,FILTER_SANITIZE_STRING); 
	//updating....
	if($eventID > 0){

		$post_id = $eventID;

		#FORMIKENOTES - should check is valid here... and eventually, that user has rights 

		$headerPost = array(
			'ID'           => (int)$eventID,
			'post_title'   => $zbsEventMeta['title'],
		);
		//Update the post into the database
		wp_update_post( $headerPost );
		$zbsEventMeta['event_id'] = $eventID;
		update_post_meta($eventID, 'zbs_event_meta', $zbsEventMeta);

		$zbsActionMeta = array();
		$zbsActionMeta['notify'] =  $zbsEventMeta['notify'];
		$zbsActionMeta['complete'] = $zbsEventMeta['complete'];
		#} different array in a different key..  not the tidiest! ;/
		// See #FORMIKENOTES - you were using $post_id here - presume you mean event id - changed
		update_post_meta($eventID,'zbs_event_actions', $zbsActionMeta);

		if($zbsEventMeta['owner'] > 0){
			zeroBS_setOwner($eventID, $zbsEventMeta['owner']);
		}



		do_action('zbs-event-updated', $zbsEventMeta, $post_id);

		//fire this thing.. :)
		$eventCustomerID = -1; if (isset($zbsEventMeta['customer'])) $eventCustomerID = $zbsEventMeta['customer'];
        zeroBSCRM_FireInternalAutomator('event.updated',array(
            'id'=>$eventID,
            'eventMeta'=>$zbsEventMeta,
            'againstid' => $eventCustomerID,
            'automatorpassthrough'=>false #} This passes through any custom log titles or whatever into the Internal automator recipe.
        ));

	
	} else {

		//adding a new

		//sanitize the event fields


		#} Build header 
		$headerPost = array(
								'post_type' => 'zerobs_event',
								'post_status' => 'publish',
								'comment_status' => 'closed',
								'post_title' => $zbsEventMeta['title']
							);
		#} Insert
		$post_id = wp_insert_post($headerPost);
		$zbsEventMeta['event_id'] = $post_id;

		update_post_meta($post_id, 'zbs_event_meta', $zbsEventMeta);

		#} Actions 
		/*
			
			= zbs_event_actions = array(

				'notify' =>  0 or 24 (never or 24 hours before)
				'complete' => 1 or 0 (boolean)

			)

		*/

		$zbsActionMeta['notify'] =  $zbsEventMeta['notify'];
		$zbsActionMeta['complete'] = $zbsEventMeta['complete'];
		#} different array in a different key..  not the tidiest! ;/
		update_post_meta($post_id,'zbs_event_actions', $zbsActionMeta);

		if($zbsEventMeta['owner'] > 0){
			zeroBS_setOwner($post_id, $zbsEventMeta['owner']);
		}

		do_action('zbs-event-added', $zbsEventMeta, $post_id);

		//fire this thing.. :)
		$eventCustomerID = -1; if (isset($zbsEventMeta['customer'])) $eventCustomerID = $zbsEventMeta['customer'];
        zeroBSCRM_FireInternalAutomator('event.new',array(
            'id'=>$post_id,
            'eventMeta'=>$zbsEventMeta,
            'againstid' => $eventCustomerID,
            'automatorpassthrough'=>false #} This passes through any custom log titles or whatever into the Internal automator recipe.
        ));


	}

	return $post_id;
}






/* Centralised delete company func, including sub-element removal */
function zeroBS_deleteCompany($id=-1,$saveOrphans=true){

	if (!empty($id)){

		// delete orphans?
		if (!$saveOrphans){

			// delete contacts
			$contactsAtCo = zeroBS_getCustomers(true,10000,0,false,false,'',false,false,$id);
			foreach ($contactsAtCo as $contact){

				// delete post AND IT'S ORPHANS? - not forced?
				$res = zeroBS_deleteCustomer($contact['id'],$saveOrphans);

			} unset($trans);

		}

		// delete actual post - not forced?
		$res = wp_delete_post($id,false);


	}

	return false;
}


/* Centralised delete func - QUITE dodgy, no checks?!*/
function zeroBS_deleteGeneric($id=-1){

	if (!empty($id)){

		// delete actual post - not forced?
		$res = wp_delete_post($id,false);


	}

	return false;
}


#} Quick wrapper to future-proof.
#} Should later replace all get_post_meta's with this
function zeroBS_getCompanyMeta($coID=-1){

	if (!empty($coID)) return get_post_meta($coID, 'zbs_company_meta', true);

	return false;

}

// No longer req. post 2.4!
// use generic zeroBS_getExternalSource
function zeroBS_getCompanyExternalSource($cID=-1){

	$ret = array();

	if ($cID !== -1){

		#} Find external sources :)
		//global $zbscrmApprovedExternalSources;

		if (count($zbs->external_sources)) foreach ($zbs->external_sources as $srcKey => $srcDeet){
        
        	$possMeta = get_post_meta($cID,'zbs_company_ext_'.$srcKey,true);

        	#} In case of company, possMeta here will be the company name! Can override..

        	if (!empty($possMeta)){

        		#} Add it - srcdeet 0 = "PayPal" Possmeta = "uniqueid"
        		$ret[$srcKey] = array($srcDeet[0],'Created from "'.$possMeta.'"');#$possMeta);

        	}

        }

	} 


	return $ret;


}

function zeroBS_getCompanyIDWithName($coName=''){

	$ret = false;

	#} No empties, no validation, either.
	if (!empty($coName)){

		#} LOTS OF OPTIONS HERE: 
		#http://stackoverflow.com/questions/3591295/how-can-i-get-a-post-by-title-in-wordpress
		#http://wordpress.stackexchange.com/questions/18703/wp-query-with-post-title-like-something

		/*
				This is cleanest, but because we're messing with the title to include address, it'll lead to issues.
		
		$potentialCompany = get_page_by_title($coName, OBJECT, 'zerobs_company');

		if (isset($potentialCompany) && $potentialCompany != null && isset($potentialCompany->ID)) return $potentialCompany->ID;
		*/		

		#} Will find the post, if exists, no dealing with dupes here, yet?
		$args = array (
			'post_type'              => 'zerobs_company',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'order'                  => 'DESC',
			'orderby'                => 'post_date',
			'meta_query' => array( 
				#} Only works because we set this whenever title changes in
				#} zbsCustomer_updateCompanyNameInPostTitle
				array(
					'key' => 'zbs_company_nameperm',
					'value' => $coName, 
					'compare' => '='
				)
			)

		);

		$potentialCustomerList = get_posts( $args );

		if (count($potentialCustomerList) > 0){

			if (isset($potentialCustomerList[0]) && isset($potentialCustomerList[0]->ID)){

				$ret = $potentialCustomerList[0]->ID;

			}

		}
		

	}


	return $ret;


}

#} ExternalID is name in this case :)
function zeroBS_getCompanyIDWithExternalSource($externalSource='',$externalID=''){

	global $zbs;

	$ret = false;

	#} No empties, no random externalSources :)
	if (!empty($externalSource) && !empty($externalID) && array_key_exists($externalSource,$zbs->external_sources)){

		#} If here, is legit.
		$approvedExternalSource = $externalSource;

		#} Will find the post, if exists, no dealing with dupes here, yet?
		$args = array (
			'post_type'              => 'zerobs_company',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'order'                  => 'DESC',
			'orderby'                => 'post_date',

				// post v2.4 this is genericified:
				//'meta_key'   => 'zbs_company_ext_'.$approvedExternalSource,
				'meta_key'   => 'zbs_obj_ext_'.$approvedExternalSource,		
			   
			   'meta_value' => $externalID
		);

		$potentialCompanyList = get_posts( $args );

		if (count($potentialCompanyList) > 0){

			if (isset($potentialCompanyList[0]) && isset($potentialCompanyList[0]->ID)){

				$ret = $potentialCompanyList[0]->ID;

			}

		}

	}


	return $ret;

}



function zeroBS_getCompany($coID=-1,$withTransactions=false){

	if ($coID !== -1){

		$retObj = array(
						'id'=>$coID,
						'meta'=>get_post_meta($coID, 'zbs_company_meta', true),
						
						#} Isn't this making 2 queries? Unnecessary?
						// WH Changed this to create a proper formatted output
						// as this does WP default: 
						//'created' => get_the_date('',$coID),
						// e.g. April 27, 2018
						'created' => get_the_date('Y-m-d H:i:s',$coID),

						#} As of v1.1
						'name' =>get_the_title($coID)
					);

		// Debug echo 'created: '.$retObj['created'].'<br>'.get_the_date('Y-m-d H:i:s',$coID).'<br>';
		
		// DAL2->3 Compatability
		// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
		// ... once migrated to DAL3 fully, this will no longer happen.
		$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
		// / DAL2->3 Compatability

		#} ... brutal for mvp
		if ($withTransactions){
			
			#} only gets first 10k?
			$retObj['transactions'] = zeroBS_getTransactionsForCompany($coID,false,10000);

			// as of 2.97.4 also gets invoices:
			$retObj['invoices'] = zeroBS_getInvoicesForCompany($coID,true,10000);

		}
		
		return $retObj;

	} 

	return false;


}


#} Get the COUNT of companies etc..
function zeroBS_companyCount(){
	
	$zbsCo = wp_count_posts('zerobs_company');

	if (isset($zbsCo->publish))
		return $zbsCo->publish;
	else
		return 0;
}


function zeroBS_invCount(){
	
	$zbsInvs = wp_count_posts('zerobs_invoice');

	if (isset($zbsInvs->publish))
		return $zbsInvs->publish;
	else
		return 0;
}

function zeroBS_quoCount(){

	$zbsQuos = wp_count_posts('zerobs_quote');
	
	if (isset($zbsQuos->publish))
		return $zbsQuos->publish;
	else
		return 0;
}

function zeroBS_tranCount(){
	
	$zbsTrans = wp_count_posts('zerobs_transaction');
	
	if (isset($zbsTrans->publish))
		return $zbsTrans->publish;
	else
		return 0;
}


#} new lightweight fast code for getting ID and name
function zeroBS_getCompaniesForTypeahead($searchQueryStr=''){


	//gets them all, from a brutal SQL
	global $wpdb;

		if (!empty($searchQueryStr)){

			// param query
			$sql = "SELECT ID as id, post_title as name, post_date as created FROM $wpdb->posts WHERE post_type = 'zerobs_company' AND post_status = 'publish' AND post_title LIKE %s";
			$q = $wpdb->prepare($sql,'%'.$searchQueryStr.'%');
			$results = $wpdb->get_results($q, ARRAY_A);

		} else {

			// straight query
			$sql = "SELECT ID as id, post_title as name, post_date as created FROM $wpdb->posts WHERE post_type = 'zerobs_company' AND post_status = 'publish'";
			$results = $wpdb->get_results($sql, ARRAY_A);
		}

	return $results;

}


#} Wrapper func for "company" type customers
// note: $inCountry returns with address 1 or 2 in country (added for logReporter for Miguel (custom extension WH))
// note: $withStatus returns with specific status  (added for logReporter for Miguel (custom extension WH))
function zeroBS_getCompanies($withFullDetails=false,$perPage=10,$page=0,$searchPhrase='',$argsOverride=false,$withInvoices=false,$withQuotes=false,$withTransactions=false,$inCountry=false,$ownedByID=false,$withStatus=false,$inArr=false){


		#} If $argsOverride is passed, override all of this :)
		if (is_array($argsOverride)){

			$args = $argsOverride;

		} else {

				#} Normal args build

					#} Page legit? - lazy check
					if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

					$args = array (
						'post_type'              => 'zerobs_company',
						'post_status'            => 'publish',
						'posts_per_page'         => $perPage,
						'order'                  => 'DESC',
						'orderby'                => 'post_date'
					);
					
					#} Add page if page... - dodgy meh
					$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
					if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

					#} Add search phrase... if #WH v1.1 https://codex.wordpress.org/Class_Reference/WP_Query#Parameters
					//if (!empty($searchPhrase)) $args['s'] = $searchPhrase;

					$extraMetaQueries = array();


					#} MS addition. This will search through the customer meta (all of it)
					#} need to rethink search for new DB - this should search all meta..
					// WH: This is rough, will produce false-negatives, will fix this in DAL2
					if (!empty($searchPhrase)){
						$extraMetaQueries[] = array(
					           'key' => 'zbs_company_meta',
					           //'value' => '%'.$q.'%',
					           'value' => $searchPhrase,
					           'compare' => 'LIKE'
					        );
					}

					// potentially multiple	
					$extraMetaQueries = array();

					// in country?
					if (!empty($inCountry)){
						
						//$q = '"country";s:'.strlen($inCountry).':"'.$inCountry.'"';

							$extraMetaQueries[] = array(
					           'key' => 'zbs_company_meta',
					           	//'value' => '%'.$q.'%',
            					//'value' => sprintf(':"%s";', $inCountry),
					           'value' => '"'.$inCountry.'"',
					           'compare' => 'LIKE'
					        );

						/*$q = '"secaddr_country";s:'.strlen($inCountry).':"'.$inCountry.'"';

							$extraMetaQueries[] = array(
					           'key' => 'zbs_company_meta',
					           //'value' => '%'.$q.'%',
					           'value' => $q,
					           'compare' => 'LIKE'
					        );

					    // or
						$extraMetaQueries['relation'] = 'OR';*/

							// gross.
							//$args['meta_query'] = $extraMetaQueries;
							// ^^ set below now
					} 

					// with status?
					if (!empty($withStatus)){

						$extraMetaQueries = array();
						
						//$q = '"country";s:'.strlen($inCountry).':"'.$inCountry.'"';

							$extraMetaQueries[] = array(
					           'key' => 'zbs_company_meta',
					           	//'value' => '%'.$q.'%',
            					//'value' => sprintf(':"%s";', $inCountry),
					           'value' => '"'.$withStatus.'"',
					           'compare' => 'LIKE'
					        );

						/*$q = '"secaddr_country";s:'.strlen($inCountry).':"'.$inCountry.'"';

							$extraMetaQueries[] = array(
					           'key' => 'zbs_company_meta',
					           //'value' => '%'.$q.'%',
					           'value' => $q,
					           'compare' => 'LIKE'
					        );

					    // or
						$extraMetaQueries['relation'] = 'OR';*/

							// gross.
							//$args['meta_query'] = $extraMetaQueries;
							// ^^ set below
					} 

					if (count($extraMetaQueries) > 0) $args['meta_query'] = $extraMetaQueries;

					if (!empty($ownedByID)){

						if (!isset($args['meta_query'])) $args['meta_query'] = array();

						$extraMetaQueries[] = array(
				           'key' => 'zbs_owner',
				           'value' => $ownedByID,
				           'compare' => '='
				        );

						
					}


					if(is_array($inArr)){    //query by posts in an array
						$args['post__in'] = $inArr;
					}

					#Debug print_r($args); # exit();
					// gross.
					if (count($extraMetaQueries) > 0) $args['meta_query'] = $extraMetaQueries;

					#Debug  echo '<h2>SQL:</h2><pre>'; print_r($args); echo '</pre>';


		}

		$companyList = get_posts( $args );
        /* DEBUG 
        $companyList = new WP_Query($args);
		//echo '<h2>SQL:</h2><pre>'; echo $GLOBALS['wp_query']->request; echo '</pre>';
		echo '<h2>SQL:</h2><pre>'; echo "Last SQL-Query: {$companyList->request}"; echo '</pre>';*/


		#} QPI
		#$zbsQPI['retrieveCustomers2getCustomers'] = round(zeroBSCRM_mtime_float() - #$zbsQPI['retrieveCustomers2getCustomers'],2).'s';
		#$zbsQPI['retrieveCustomers2getCustomersFill'] = zeroBSCRM_mtime_float();

		$ret = array();

		foreach ($companyList as $coEle){

			$retObj = array(

				'id' => 	$coEle->ID,
				'created' => $coEle->post_date_gmt,
				#} As of v1.1
				'name' => 	$coEle->post_title

			);

			#} Full details?
			if ($withFullDetails) {
				
				$retObj['meta'] 		= get_post_meta($coEle->ID, 'zbs_company_meta', true);

				$retObj['name'] = ''; if (is_array($retObj['meta']) && isset($retObj['meta']['coname'])) $retObj['name'] = $retObj['meta']['coname'];

				// DAL2->3 Compatability
				// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
				// ... once migrated to DAL3 fully, this will no longer happen.
				$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
				// / DAL2->3 Compatability

				#} And any sub's... not a great way of doing here :/
				$retObj['contacts'] = zeroBS_getCustomers(false,1000,0,false,false,'',false,false,$coEle->ID);
			}


			$ret[] = $retObj;


		}



		return $ret;

}



/* Wrote this in a rush, not really req. as fixed main func
// rough and ready - no paging even, returns all :) - WH to properly consider in time
function zeroBS_getCompaniesByCountry($withFullDetails=false,$withInvoices=false,$withQuotes=false,$withTransactions=false){

	global $wpdb;

	// brutal check for now
	$qStr = $inCountry;

	// SQL
	$query = "SELECT $wpdb->wp_posts.* FROM $wpdb->wp_posts  INNER JOIN $wpdb->wp_postmeta ON ( $wpdb->wp_posts.ID = $wpdb->wp_postmeta.post_id ) WHERE 1=1  AND ( ";
	$query .= "( $wpdb->wp_postmeta.meta_key = 'zbs_company_meta' AND $wpdb->wp_postmeta.meta_value LIKE '%\"".$qStr."\"%' )";
	$query .= ") AND $wpdb->wp_posts.post_type = 'zerobs_company' AND (($wpdb->wp_posts.post_status = 'publish')) GROUP BY $wpdb->wp_posts.ID ORDER BY $wpdb->wp_posts.post_date DESC LIMIT 0, 100000";

	
	$companyList =  $wpdb->get_results($querystr, OBJECT);

	// following copied from main getCompanies...

		$ret = array();

		foreach ($companyList as $coEle){

			$retObj = array(

				'id' => 	$coEle->ID,
				'created' => $coEle->post_date_gmt,
				#} As of v1.1
				'name' => 	$coEle->post_title

			);

			#} Full details?
			if ($withFullDetails) {
				
				$retObj['meta'] 		= get_post_meta($coEle->ID, 'zbs_company_meta', true);

				$retObj['name'] = $retObj['meta']['coname'];

				#} And any sub's... not a great way of doing here :/
				$retObj['contacts'] = zeroBS_getCustomers(false,1000,0,false,false,'',false,false,$coEle->ID);
			}


			$ret[] = $retObj;


		}



		return $ret;

}
*/

// MS Cloned from getCustomers
// ... WH slightly cleaned
// ... NEEDS DB2 to wipe these out (centralise to 1 get func per type with $args)
function zeroBS_getCompaniesv2($withFullDetails=false,$perPage=10,$page=0,$searchPhrase='',$argsOverride=false, $hasTagIDs='', $inArr = '',$withTags=false,$withAssigned=false,$withLastLog=false,$sortByField='',$sortOrder='DESC',$quickFilters=false,$withTransactions=false){

	#} Query Performance index
	#global $zbsQPI; if (!isset($zbsQPI)) $zbsQPI = array();
	#$zbsQPI['retrieveCustomers2getCustomers'] = zeroBSCRM_mtime_float();

	#} Rough way of doing it, but up to v2.2 no good way of filtering out order value customers except with this..
	$postQueryFilters = array(); $extraMetaQueries = array();


		#} If $argsOverride is passed, override all of this :)
		if (is_array($argsOverride)){

			$args = $argsOverride;

		} else {

				#} Normal args build

					#} Page legit? - lazy check
					if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

					$args = array (
						'post_type'              => 'zerobs_company',
						'post_status'            => 'publish',
						'posts_per_page'         => $perPage,
						'order'                  => 'DESC',
						'orderby'                => 'post_date'
					);
					
					
					#} FROM BatchTagger.php ext: there's a weiedness with the page offset for getCustomers, for now workaround it here, later change in main DAL
						#} e.g. pass this 0, then seperate page of 1, and both return same
					#} Add page if page... - dodgy meh
					$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
					if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

					#} Add search phrase... if #WH v1.1 https://codex.wordpress.org/Class_Reference/WP_Query#Parameters
				//	if (!empty($searchPhrase)) $args['s'] = $searchPhrase;


					#} MS addition. This will search through the customer meta (all of it)
					#} need to rethink search for new DB - this should search all meta..
					if (!empty($searchPhrase)){
						$extraMetaQueries[] = array(
					           'key' => 'zbs_company_meta',
					           //'value' => '%'.$q.'%',
					           'value' => $searchPhrase,
					           'compare' => 'LIKE'
					        );
					}


					if (count($extraMetaQueries) > 0){

						// gross.
						$args['meta_query'] = $extraMetaQueries;

					}

					#} has tag .... http://wordpress.stackexchange.com/questions/165610/get-posts-under-custom-taxonomy
					if(!empty($hasTagIDs)){
						$args['tax_query'] = array(
                                array(
                                'taxonomy' => 'zerobscrm_companytag',
       							'field' => 'term_id',
                				'terms' => $hasTagIDs,
                                )
                            );
					}

					if(!empty($inArr)){    //query by posts in an array
						$args['post__in'] = $inArr;
					}

					#} Sort by (works for ID, name at the moment)
					$acceptableSortFields = array('post_id','post_title');
					if (isset($sortByField) && !empty($sortByField) && in_array($sortByField,$acceptableSortFields) && !empty($sortOrder)){

						$args['order'] = $sortOrder; // we're trusting that this is right
						$args['orderby'] = $sortByField;

					}


					#Debug print_r($args); # exit();




					#} Quick filters - here for 2.2, probably needs refactoring (or db change)
					if (is_array($quickFilters) && count($quickFilters) > 0){

						// SELECT * FROM `zbscrm_demo_postmeta` WHERE meta_key = 'zbs_customer_meta'
						$extraMetaQueries = array();

						foreach ($quickFilters as $qFilter){

							// catch these "status" ones separately, can probs do away with lead/customer after this :)
							if (substr($qFilter,0,7) == 'status_'){

								$qFilterStatus = substr($qFilter,7);
								$qFilterStatus = str_replace('_',' ',$qFilterStatus);

								$q = '"status";s:'.strlen($qFilterStatus).':"'.ucwords($qFilterStatus).'"';
									$extraMetaQueries[] = array(
							           'key' => 'zbs_company_meta',
							           //'value' => '%'.$q.'%',
							           'value' => $q,
							           'compare' => 'LIKE'
							        );

							} else {

								// normal/hardtyped

								switch ($qFilter){


									case 'lead':

										// hack "leads only"
										//SELECT * FROM `zbscrm_demo_postmeta` WHERE meta_value LIKE '%"status";s:4:"Lead"%'
										$q = '"status";s:4:"Lead"';
										$extraMetaQueries[] = array(
								           'key' => 'zbs_company_meta',
								           //'value' => '%'.$q.'%',
								           'value' => $q,
								           'compare' => 'LIKE'
								        );

										break;


									case 'customer':

										// hack "leads only"
										//SELECT * FROM `zbscrm_demo_postmeta` WHERE meta_value LIKE '%"status";s:8:"Customer"%'
										$q = '"status";s:8:"Customer"';
										$extraMetaQueries[] = array(
								           'key' => 'zbs_company_meta',
								           //'value' => '%'.$q.'%',
								           'value' => $q,
								           'compare' => 'LIKE'
								        );

										break;


								}  // / switch

							} // / hardtyped


						}


					}

		}


		if (count($extraMetaQueries) > 0){
			$args['meta_query'] = $extraMetaQueries;
		}
		$companyList = get_posts( $args );



		//print_r($args); exit();
		//echo 'Cust list: '.count($companyList);
		



		#} QPI
		#$zbsQPI['retrieveCustomers2getCustomers'] = round(zeroBSCRM_mtime_float() - #$zbsQPI['retrieveCustomers2getCustomers'],2).'s';
		#$zbsQPI['retrieveCustomers2getCustomersFill'] = zeroBSCRM_mtime_float();

		$ret = array();
		/* WH: Don't think need this anymore

		$args['posts_per_page'] = -1;
		$args['offset'] = 0;

		//total results..  same but without pagination (to get total count...)
		$filterList = count(get_posts($args));
		*/



			// brutal filtering for notcontactedinx, gross performant until new db
			// here just check if any to apply (rather htan checking for each cust)
			// ONLY one at play really is notcontactedin for Miguel (2.4) (until newdb)
			$notcontactedinDays = -1;
			if (is_array($quickFilters) && count($quickFilters) > 0) {

				foreach ($quickFilters as $qFilter){ 

					if (substr($qFilter,0,14) == 'notcontactedin'){

						// check
						$notcontactedinDays = (int)substr($qFilter,14);
						$notcontactedinDaysSeconds = $notcontactedinDays*86400;

					}

				}

			}

		foreach ($companyList as $companyEle){



			$retObj = array(

				'id' => 	$companyEle->ID,
				'created' => $companyEle->post_date_gmt,
				#} As of v1.1
				'coname' => 	$companyEle->post_title

			);

			//$retObj['filterTot'] = $filterList;
			//$retObj['filterPages'] = (int)ceil($filterList / $perPage);


			#} Full details?
			if ($withFullDetails) {
				
				$retObj['meta'] 		= get_post_meta($companyEle->ID, 'zbs_company_meta', true);

				// DAL2->3 Compatability
				// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
				// ... once migrated to DAL3 fully, this will no longer happen.
				$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
				// / DAL2->3 Compatability
			}



			#} With tags?
			if ($withTags){
				
				// https://codex.wordpress.org/Function_Reference/wp_get_object_terms#Argument_Options
				$args = array(
					'order' => 'ASC',
					'orderby' => 'name'
				);
				$retObj['tags'] = zeroBSCRM_getCompanyTagsByID($companyEle->ID);//wp_get_object_terms($companyEle->ID,'zerobscrm_customertag',$args);

			} 

			#} With Assigned?
			if ($withAssigned){

				$retObj['owner'] = zeroBS_getOwner($companyEle->ID);
				// ID + OBJ

			}

			#} With most recent log?
			if ($withLastLog){

				$retObj['lastlog'] = zeroBSCRM_getMostRecentCompanyLog($companyEle->ID,true);
				$retObj['lastcontactlog'] = zeroBSCRM_getMostRecentCompanyLog($companyEle->ID,true,array('Call','Email'));

			}

			#} With trans
			if ($withTransactions){

				$retObj['transactions'] = zeroBS_getTransactionsForCompany($companyEle->ID,false,100);

			}


			// brutal filtering for notcontactedinx, gross performant until new db
			//if (is_array($quickFilters) && count($quickFilters) > 0) {
			// just this for now
			if ($notcontactedinDays > 0){

				if (!isset($retObj['lastcontactlog']['created']) || strtotime($retObj['lastcontactlog']['created']) < $notcontactedinDaysSeconds) $ret[] = $retObj;
				
			} else {

				// usual
				$ret[] = $retObj;

			}

		}


		#} QPI
		#$zbsQPI['retrieveCustomers2getCustomersFill'] = round(zeroBSCRM_mtime_float() - #$zbsQPI['retrieveCustomers2getCustomersFill'],2).'s';




		return $ret;



}

// MS Cloned from getCustomers
// ... WH slightly cleaned
// ... NEEDS DB2 to wipe these out (centralise to 1 get func per type with $args)
// ... THIS IS A CLONE of getCompaniesv2 which just returns a TOTAL count 
function zeroBS_getCompaniesv2CountIncParams($searchPhrase='',$argsOverride=false, $hasTagIDs='', $inArr = '',$withTags=false,$withAssigned=false,$withLastLog=false,$sortByField='',$sortOrder='DESC',$quickFilters=false){

	#} Query Performance index
	#global $zbsQPI; if (!isset($zbsQPI)) $zbsQPI = array();
	#$zbsQPI['retrieveCustomers2getCustomers'] = zeroBSCRM_mtime_float();

	#} Rough way of doing it, but up to v2.2 no good way of filtering out order value customers except with this..
	$postQueryFilters = array(); $extraMetaQueries = array();


		#} If $argsOverride is passed, override all of this :)
		if (is_array($argsOverride)){

			$args = $argsOverride;

		} else {

				#} Normal args build

					$args = array (
						'post_type'              => 'zerobs_company',
						'post_status'            => 'publish',
						'posts_per_page'         => 10000000,
						'offset'				 => 0,
						//'order'                => 'DESC',
						'orderby'                => 'none', // no order should be quicker sql
						'nopaging' 				 => true, // show all

						'fields'				 => 'ids' // this forces just a list of ID's to be returned :DB_CHARSET
					);
					

					#} Add search phrase... if #WH v1.1 https://codex.wordpress.org/Class_Reference/WP_Query#Parameters
				//	if (!empty($searchPhrase)) $args['s'] = $searchPhrase;


					#} MS addition. This will search through the customer meta (all of it)
					#} need to rethink search for new DB - this should search all meta..
					if (!empty($searchPhrase)){
						$extraMetaQueries[] = array(
					           'key' => 'zbs_company_meta',
					           //'value' => '%'.$q.'%',
					           'value' => $searchPhrase,
					           'compare' => 'LIKE'
					        );
					}


					if (count($extraMetaQueries) > 0){

						// gross.
						$args['meta_query'] = $extraMetaQueries;

					}

					#} has tag .... http://wordpress.stackexchange.com/questions/165610/get-posts-under-custom-taxonomy
					if(!empty($hasTagIDs)){
						$args['tax_query'] = array(
                                array(
                                'taxonomy' => 'zerobscrm_companytag',
       							'field' => 'term_id',
                				'terms' => $hasTagIDs,
                                )
                            );
					}

					if(!empty($inArr)){    //query by posts in an array
						$args['post__in'] = $inArr;
					}

					#} Sort by (works for ID, name at the moment)
					$acceptableSortFields = array('post_id','post_title');
					if (isset($sortByField) && !empty($sortByField) && in_array($sortByField,$acceptableSortFields) && !empty($sortOrder)){

						$args['order'] = $sortOrder; // we're trusting that this is right
						$args['orderby'] = $sortByField;

					}


					#Debug print_r($args); # exit();




					#} Quick filters - here for 2.2, probably needs refactoring (or db change)
					if (is_array($quickFilters) && count($quickFilters) > 0){

						// SELECT * FROM `zbscrm_demo_postmeta` WHERE meta_key = 'zbs_customer_meta'
						$extraMetaQueries = array();

						foreach ($quickFilters as $qFilter){

							// catch these "status" ones separately, can probs do away with lead/customer after this :)
							if (substr($qFilter,0,7) == 'status_'){

								$qFilterStatus = substr($qFilter,7);
								$qFilterStatus = str_replace('_',' ',$qFilterStatus);

								$q = '"status";s:'.strlen($qFilterStatus).':"'.ucwords($qFilterStatus).'"';
									$extraMetaQueries[] = array(
							           'key' => 'zbs_company_meta',
							           //'value' => '%'.$q.'%',
							           'value' => $q,
							           'compare' => 'LIKE'
							        );

							} else {

								// normal/hardtyped

								switch ($qFilter){


									case 'lead':

										// hack "leads only"
										//SELECT * FROM `zbscrm_demo_postmeta` WHERE meta_value LIKE '%"status";s:4:"Lead"%'
										$q = '"status";s:4:"Lead"';
										$extraMetaQueries[] = array(
								           'key' => 'zbs_company_meta',
								           //'value' => '%'.$q.'%',
								           'value' => $q,
								           'compare' => 'LIKE'
								        );

										break;


									case 'customer':

										// hack "leads only"
										//SELECT * FROM `zbscrm_demo_postmeta` WHERE meta_value LIKE '%"status";s:8:"Customer"%'
										$q = '"status";s:8:"Customer"';
										$extraMetaQueries[] = array(
								           'key' => 'zbs_company_meta',
								           //'value' => '%'.$q.'%',
								           'value' => $q,
								           'compare' => 'LIKE'
								        );

										break;


								}  // / switch

							} // / hardtyped


						}


					}

		}


		if (count($extraMetaQueries) > 0){
			$args['meta_query'] = $extraMetaQueries;
		}
		
		//return count(get_posts( $args ));

		$companiesList = get_posts( $args );

				// brutal check to see if a notinx days is present
				if (is_array($quickFilters) && count($quickFilters) > 0) {

					$inc = true;

					foreach ($quickFilters as $qFilter){ 

						if (substr($qFilter,0,14) == 'notcontactedin'){

							$notcontactedin = (int)substr($qFilter,14);

						}

					}

				}
				// end check


		if (!isset($notcontactedin)){

			// normal
			return count($companiesList);

		} else {

			// not contacted inx 
			$daysSeconds = $notcontactedin*86400;

			$ret = array();

			foreach ($companiesList as $companyEleID){


				$lastLog = zeroBSCRM_getMostRecentCompanyLog($companyEleID,true,array('Call','Email'));

				// check
				if (!isset($lastLog['created']) || strtotime($lastLog['created']) < $daysSeconds){
					$ret[] = $companyEleID;
				}
					
			}

			return count($ret);

		} // end notcontactedinx

}


/* exploring perf tweaks
#} Exploring sql ver of prev
function zeroBS_getCustomersSQL(){

	global $wpdb;
	#$wpdb->query('SET SESSION group_concat_max_len = 10000'); // necessary to get more than 1024 characters in the GROUP_CONCAT columns below
	$query = "
	SELECT posts.post_type,posts.ID,posts.post_date_gmt,
	(SELECT meta_value FROM `zbscrm_demo_postmeta` WHERE post_id = posts.ID AND meta_key = CONCAT('zbs_customer_' , substr(posts.post_type,8) , '_meta')) meta
	FROM $wpdb->posts posts
	LEFT JOIN $wpdb->postmeta ON posts.ID = postmeta.post_id
	WHERE 
	posts.post_type IN ('zerobs_quote','zerobs_invoice')
	AND posts.post_status = 'publish'
	AND postmeta.meta_value = $customerID
	ORDER BY ID DESC limit 0,10000";

} */



function zeroBS_getInvoices($withFullDetails=false,$perPage=10,$page=0,$withCustomerDeets=false,$searchPhrase='',$inArray=array(),$sortByField='',$sortOrder='DESC',$quickFilters=array()){

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_invoice',
			'post_status'            => 'any',
			'posts_per_page'         => $perPage,
			'order'                  => 'DESC',
			'orderby'                => 'post_date'
		);


		$extraMetaQueries = array();

		#} Search
		#} need to rethink search for new DB - this should search all meta..
		if (!empty($searchPhrase)){
			$extraMetaQueries[] = array(
		           'key' => 'zbs_customer_invoice_meta',
		           //'value' => '%'.$q.'%',
		           'value' => $searchPhrase,
		           'compare' => 'LIKE'
		        );
		}

		#} Quick filters - here for 2.2, probably needs refactoring (or db change)
		// NOTE: This is hacky
		if (is_array($quickFilters) && count($quickFilters) > 0){

			foreach ($quickFilters as $qFilter){

				// normal/hardtyped

				switch ($qFilter){


					case 'status_draft':

						// hack 
						$q = 's:6:"status";s:5:"Draft"';
						$extraMetaQueries[] = array(
				           'key' => 'zbs_customer_invoice_meta',
				           //'value' => '%'.$q.'%',
				           'value' => $q,
				           'compare' => 'LIKE'
				        );

						break;


					case 'status_unpaid':

						// hack 
						$q = 's:6:"status";s:6:"Unpaid"';
						$extraMetaQueries[] = array(
				           'key' => 'zbs_customer_invoice_meta',
				           //'value' => '%'.$q.'%',
				           'value' => $q,
				           'compare' => 'LIKE'
				        );

						break;


					case 'status_paid':

						// hack 
						$q = 's:6:"status";s:4:"Paid"';
						$extraMetaQueries[] = array(
				           'key' => 'zbs_customer_invoice_meta',
				           //'value' => '%'.$q.'%',
				           'value' => $q,
				           'compare' => 'LIKE'
				        );

						break;


					case 'status_overdue':

						// hack 
						$q = 's:6:"status";s:7:"Overdue"';
						$extraMetaQueries[] = array(
				           'key' => 'zbs_customer_invoice_meta',
				           //'value' => '%'.$q.'%',
				           'value' => $q,
				           'compare' => 'LIKE'
				        );

						break;


				}  // / switch

			} // foreach


		} // quick filters

		if (count($extraMetaQueries) > 0){

			// gross.
			$args['meta_query'] = $extraMetaQueries;

		}

		if(!empty($inArr)){    //query by posts in an array
			$args['post__in'] = $inArr;
		}

		#} Sort by (works for ID, name at the moment)
		$acceptableSortFields = array('post_id');
		if (isset($sortByField) && !empty($sortByField) && in_array($sortByField,$acceptableSortFields) && !empty($sortOrder)){

			$args['order'] = $sortOrder; // we're trusting that this is right
			$args['orderby'] = $sortByField;

		}
		
		#} Add page if page... - dodgy meh
		$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
		if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

		$list = get_posts( $args );

		$ret = array();

		foreach ($list as $ele){

			$retObj = array(

				'id' => 	$ele->ID,
				// not true 'name' => 	$customerEle->post_title,
				'created' => $ele->post_date_gmt,

				#TRANSITIONTOMETANO
				'zbsid'=>get_post_meta($ele->ID, 'zbsid', true)

			);

			#} Full details?
			if ($withFullDetails) {
				
				$retObj['meta'] 			= get_post_meta($ele->ID, 'zbs_customer_invoice_meta', true);

				// DAL2->3 Compatability
				// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
				// ... once migrated to DAL3 fully, this will no longer happen.
				$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
				// / DAL2->3 Compatability


				$retObj['customerid']		= get_post_meta($ele->ID, 'zbs_customer_invoice_customer', true);
				$retObj['companyid']		= get_post_meta($ele->ID, 'zbs_company_invoice_company', true); // should be zbs_parent_co

				if ($withCustomerDeets && !empty($retObj['customerid'])){
					
					$retObj['customer']		= zeroBS_getCustomer($retObj['customerid']);
				
				}
				if ($withCustomerDeets && !empty($retObj['companyid'])){
					
					$retObj['company']		= zeroBS_getCompany($retObj['companyid']);
				
				}

			}

			$ret[] = $retObj;

		}

		return $ret;
}

function zeroBS_getInvoicesCountIncParams($withFullDetails=false,$perPage=10,$page=0,$withCustomerDeets=false,$searchPhrase='',$inArray=array(),$sortByField='',$sortOrder='DESC',$quickFilters=array()){

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_invoice',
			'post_status'            => 'any',
			'posts_per_page'         => 10000000,
			'offset'				 => 0,
			'order'                  => 'DESC',
			'orderby'                => 'post_date'
		);


		$extraMetaQueries = array();

		#} Search
		#} need to rethink search for new DB - this should search all meta..
		if (!empty($searchPhrase)){
			$extraMetaQueries[] = array(
		           'key' => 'zbs_customer_invoice_meta',
		           //'value' => '%'.$q.'%',
		           'value' => $searchPhrase,
		           'compare' => 'LIKE'
		        );
		}

		#} Quick filters - here for 2.2, probably needs refactoring (or db change)
		// NOTE: This is hacky
		if (is_array($quickFilters) && count($quickFilters) > 0){

			foreach ($quickFilters as $qFilter){

				// normal/hardtyped

				switch ($qFilter){


					case 'status_draft':

						// hack 
						$q = 's:6:"status";s:5:"Draft"';
						$extraMetaQueries[] = array(
				           'key' => 'zbs_customer_invoice_meta',
				           //'value' => '%'.$q.'%',
				           'value' => $q,
				           'compare' => 'LIKE'
				        );

						break;


					case 'status_unpaid':

						// hack 
						$q = 's:6:"status";s:6:"Unpaid"';
						$extraMetaQueries[] = array(
				           'key' => 'zbs_customer_invoice_meta',
				           //'value' => '%'.$q.'%',
				           'value' => $q,
				           'compare' => 'LIKE'
				        );

						break;


					case 'status_paid':

						// hack 
						$q = 's:6:"status";s:4:"Paid"';
						$extraMetaQueries[] = array(
				           'key' => 'zbs_customer_invoice_meta',
				           //'value' => '%'.$q.'%',
				           'value' => $q,
				           'compare' => 'LIKE'
				        );

						break;


					case 'status_overdue':

						// hack 
						$q = 's:6:"status";s:7:"Overdue"';
						$extraMetaQueries[] = array(
				           'key' => 'zbs_customer_invoice_meta',
				           //'value' => '%'.$q.'%',
				           'value' => $q,
				           'compare' => 'LIKE'
				        );

						break;


				}  // / switch

			} // foreach


		} // quick filters

		if (count($extraMetaQueries) > 0){

			// gross.
			$args['meta_query'] = $extraMetaQueries;

		}

		if(!empty($inArr)){    //query by posts in an array
			$args['post__in'] = $inArr;
		}

		#} Sort by (works for ID, name at the moment)
		$acceptableSortFields = array('post_id');
		if (isset($sortByField) && !empty($sortByField) && in_array($sortByField,$acceptableSortFields) && !empty($sortOrder)){

			$args['order'] = $sortOrder; // we're trusting that this is right
			$args['orderby'] = $sortByField;

		}
		

		return count(get_posts( $args ));
}

function zeroBS_getQuotes($withFullDetails=false,$perPage=10,$page=0,$withCustomerDeets=false,$searchPhrase='',$inArray=array(),$sortByField='',$sortOrder='DESC',$quickFilters=array()){

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_quote',
			'post_status'            => 'publish',
			'posts_per_page'         => $perPage,
			'order'                  => 'DESC',
			'orderby'                => 'post_date'
		);

		$extraMetaQueries = array();

		#} Search
		#} need to rethink search for new DB - this should search all meta..
		if (!empty($searchPhrase)){
			$extraMetaQueries[] = array(
		           'key' => 'zbs_customer_quote_meta',
		           //'value' => '%'.$q.'%',
		           'value' => $searchPhrase,
		           'compare' => 'LIKE'
		        );
		}

		#} Quick filters - here for 2.2, probably needs refactoring (or db change)
		// NOTE: This is hacky
		if (is_array($quickFilters) && count($quickFilters) > 0){

			foreach ($quickFilters as $qFilter){

				// normal/hardtyped

				switch ($qFilter){


					case 'status_accepted':

						// hack 
						$q = 's:8:"accepted";a:';
						$extraMetaQueries[] = array(
				           'key' => 'zbs_customer_quote_meta',
				           //'value' => '%'.$q.'%',
				           'value' => $q,
				           'compare' => 'LIKE'
				        );

						break;


					case 'status_notaccepted':

						// hack 
						$q = 's:8:"accepted";';
						$extraMetaQueries[] = array(
				           'key' => 'zbs_customer_quote_meta',
				           //'value' => '%'.$q.'%',
				           'value' => $q,
				           'compare' => 'NOT LIKE'
				        );

						break;


				}  // / switch

			} // foreach


		} // quick filters

		if (count($extraMetaQueries) > 0){

			// gross.
			$args['meta_query'] = $extraMetaQueries;

		}

		if(!empty($inArr)){    //query by posts in an array
			$args['post__in'] = $inArr;
		}

		#} Sort by (works for ID, name at the moment)
		$acceptableSortFields = array('post_id');
		if (isset($sortByField) && !empty($sortByField) && in_array($sortByField,$acceptableSortFields) && !empty($sortOrder)){

			$args['order'] = $sortOrder; // we're trusting that this is right
			$args['orderby'] = $sortByField;

		}

		
		#} Add page if page... - dodgy meh
		$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
		if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

		$list = get_posts( $args );

		$ret = array();

		foreach ($list as $ele){

			$retObj = array(

				'id' => 	$ele->ID,
				// not true 'name' => 	$customerEle->post_title,
				'created' => $ele->post_date_gmt,

				#TRANSITIONTOMETANO
				'zbsid'=>get_post_meta($ele->ID, 'zbsid', true)

			);

			#} Full details?
			if ($withFullDetails) {
				
				$retObj['meta'] 			= get_post_meta($ele->ID, 'zbs_customer_quote_meta', true);

				// DAL2->3 Compatability
				// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
				// ... once migrated to DAL3 fully, this will no longer happen.
				$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
				// / DAL2->3 Compatability

				$retObj['customerid']		= get_post_meta($ele->ID, 'zbs_customer_quote_customer', true);

				if ($withCustomerDeets && !empty($retObj['customerid'])){
					
					$retObj['customer']		= zeroBS_getCustomer($retObj['customerid']);
				
				}

			}

			$ret[] = $retObj;

		}

		return $ret;
}

function zeroBS_getQuotesCountIncParams($withFullDetails=false,$perPage=10,$page=0,$withCustomerDeets=false,$searchPhrase='',$inArray=array(),$sortByField='',$sortOrder='DESC',$quickFilters=array()){

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_quote',
			'post_status'            => 'publish',
			'posts_per_page'         => 1000000,
			'offset' 				 => 0,
			'order'                  => 'DESC',
			'orderby'                => 'post_date'
		);

		$extraMetaQueries = array();

		#} Search
		#} need to rethink search for new DB - this should search all meta..
		if (!empty($searchPhrase)){
			$extraMetaQueries[] = array(
		           'key' => 'zbs_customer_quote_meta',
		           //'value' => '%'.$q.'%',
		           'value' => $searchPhrase,
		           'compare' => 'LIKE'
		        );
		}

		if (count($extraMetaQueries) > 0){

			// gross.
			$args['meta_query'] = $extraMetaQueries;

		}

		if(!empty($inArr)){    //query by posts in an array
			$args['post__in'] = $inArr;
		}

		#} Sort by (works for ID, name at the moment)
		$acceptableSortFields = array('post_id');
		if (isset($sortByField) && !empty($sortByField) && in_array($sortByField,$acceptableSortFields) && !empty($sortOrder)){

			$args['order'] = $sortOrder; // we're trusting that this is right
			$args['orderby'] = $sortByField;

		}

		return count(get_posts( $args ));
}


function zeroBS_getQuoteBuilderContent($qID=-1){

	if ($qID !== -1){

            $content = get_post_meta($qID, 'zbs_quote_content' , true ) ;
            $content = htmlspecialchars_decode($content);
		
			return array(
				'content'=>$content,
				'template_id' => get_post_meta($qID, 'zbs_quote_template_id' , true ) 
				);

	} else return false;
}

function zeroBS_getQuote($qID=-1,$withQuoteBuilderData=false){

	if ($qID !== -1){
		

		if (!$withQuoteBuilderData){
			
			$retObj = array(
				'id'=>$qID,
				'meta'=>get_post_meta($qID, 'zbs_customer_quote_meta', true),
				'customerid'=>get_post_meta($qID, 'zbs_customer_quote_customer', true)
				);


			// DAL2->3 Compatability
			// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
			// ... once migrated to DAL3 fully, this will no longer happen.
			$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
			// / DAL2->3 Compatability

			return $retObj;

		} else {
			
			$retObj = array(
				'id'=>$qID,
				'meta'=>get_post_meta($qID, 'zbs_customer_quote_meta', true),
				'customerid'=>get_post_meta($qID, 'zbs_customer_quote_customer', true),
				'quotebuilder'=>zeroBS_getQuoteBuilderContent($qID)
				);

			// DAL2->3 Compatability
			// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
			// ... once migrated to DAL3 fully, this will no longer happen.
			$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
			// / DAL2->3 Compatability

			return $retObj;


		}

	}

	return false;
}
#TRANSITIONTOMETANO - this returns by ZBSID (quote no, not wp post no)
function zeroBS_getQuoteByZBSID($zbsQuoteID=-1){

	if ($zbsQuoteID !== -1){

		#} find post id :)
		$wpPostID = zeroBS_getQuotePostIDFromZBSID($zbsQuoteID);
		if (!empty($wpPostID)){
		
			$retObj = array(
				'id'=>$wpPostID,
				'meta'=>get_post_meta($wpPostID, 'zbs_customer_quote_meta', true),
				'customerid'=>get_post_meta($wpPostID, 'zbs_customer_quote_customer', true),
				'zbsid' => (int)$zbsQuoteID
				);

			// DAL2->3 Compatability
			// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
			// ... once migrated to DAL3 fully, this will no longer happen.
			$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
			// / DAL2->3 Compatability

			return $retObj;

		}

	}
	
	return false; 
}

#TRANSITIONTOMETANO - this returns wppostid from a ZBSID
function zeroBS_getQuotePostIDFromZBSID($zbsQuoteID=-1){

		$args = array (
			'post_type'              => 'zerobs_quote',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'order'                  => 'DESC',
			'orderby'                => 'post_date',

			// KEY
			   'meta_key'   => 'zbsid',
			   'meta_value' => $zbsQuoteID
		);

		$potentialQuote = get_posts( $args );

		if (isset($potentialQuote) && isset($potentialQuote[0]) && isset($potentialQuote->ID)) return $potentialQuote->ID;

		return false;

}
function zeroBS_getQuotesForCustomer($customerID=-1,$withFullDetails=false,$perPage=10,$page=0,$withCustomerDeets=false,$withQuoteBuilderData=true){

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_quote',
			'post_status'            => 'publish',
			'posts_per_page'         => $perPage,
			'order'                  => 'DESC',
			'orderby'                => 'post_date',

			// KEY
			   'meta_key'   => 'zbs_customer_quote_customer',
			   'meta_value' => $customerID
		);
		
		#} Add page if page... - dodgy meh
		$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
		if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

		$list = get_posts( $args );

		$ret = array();

		foreach ($list as $ele){

			$retObj = array(

				'id' => 	$ele->ID,
				// not true 'name' => 	$customerEle->post_title,
				'created' => $ele->post_date_gmt,

				#} #TRANSITIONTOMETANO
				'zbsid' => get_post_meta($ele->ID, 'zbsid', true)

			);


			#} Full details?
			if ($withFullDetails) {
				
				$retObj['meta'] 		= get_post_meta($ele->ID, 'zbs_customer_quote_meta', true);

				// DAL2->3 Compatability
				// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
				// ... once migrated to DAL3 fully, this will no longer happen.
				$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
				// / DAL2->3 Compatability

				$retObj['customerid']	= get_post_meta($ele->ID, 'zbs_customer_quote_customer', true);
				if ($withQuoteBuilderData) $retObj['quotebuilder'] = zeroBS_getQuoteBuilderContent($ele->ID);

				if ($withCustomerDeets && !empty($retObj['customerid'])){
					
					$retObj['customer']		= zeroBS_getCustomer($retObj['customerid']);
				
				}

			}

			$ret[] = $retObj;

		}

		return $ret;
}

#} Marks a quote as "accepted" and saves as much related data as poss on accepter
function zeroBS_markQuoteAccepted($qID=-1,$quoteSignedBy=''){

	if ($qID !== -1){
		
		#} Retrieve meta
		$quoteMeta = get_post_meta($qID, 'zbs_customer_quote_meta', true);

		#} Update (brutal) - would override if double accepted
		$quoteMeta['accepted'] = array(time(),$quoteSignedBy,zeroBSCRM_getRealIpAddr()); #} Could add other deets here... browser etc.?

		#} Save
		return update_post_meta($qID,'zbs_customer_quote_meta',$quoteMeta);

	} 

	return false;

}

#} UNMarks a quote as "accepted" and saves as much related data as poss on accepter
function zeroBS_markQuoteUnAccepted($qID=-1){

	if ($qID !== -1){
		
		#} Retrieve meta
		$quoteMeta = get_post_meta($qID, 'zbs_customer_quote_meta', true);

		#} Update (brutal) - will wipe all previous acceptence data
		unset($quoteMeta['accepted']);

		#} Save
		return update_post_meta($qID,'zbs_customer_quote_meta',$quoteMeta);

	} 

	return false;

}


function zeroBS_getInvoice($wpPostID=-1){

	if ($wpPostID !== -1){
		
		$retObj = array(
			'id'=>(int)$wpPostID,
			'meta'=>get_post_meta($wpPostID, 'zbs_customer_invoice_meta', true),
			'customerid'=>get_post_meta($wpPostID, 'zbs_customer_invoice_customer', true),
			'zbsid'=>get_post_meta($wpPostID, 'zbsid', true)
			);

		// DAL2->3 Compatability
		// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
		// ... once migrated to DAL3 fully, this will no longer happen.
		$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
		// / DAL2->3 Compatability

		return $retObj;

	}
	
	return false; 
}
#TRANSITIONTOMETANO - this returns by ZBSID (invoice no, not wp post no)
function zeroBS_getInvoiceByZBSID($zbsInvID=-1){

	if ($zbsInvID !== -1){

		#} find post id :)
		$wpPostID = zeroBS_getInvoicePostIDFromZBSID($zbsInvID);
		if (!empty($wpPostID)){
		
			$retObj = array(
				'id'=>$wpPostID,
				'meta'=>get_post_meta($wpPostID, 'zbs_customer_invoice_meta', true),
				'customerid'=>get_post_meta($wpPostID, 'zbs_customer_invoice_customer', true),
				'zbsid' => (int)$zbsInvID
				);

			// DAL2->3 Compatability
			// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
			// ... once migrated to DAL3 fully, this will no longer happen.
			$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
			// / DAL2->3 Compatability

			return $retObj;

		}

	}
	
	return false; 
}
#TRANSITIONTOMETANO - this returns wppostid from a ZBSID
function zeroBS_getInvoicePostIDFromZBSID($zbsInvID=-1){

		$args = array (
			'post_type'              => 'zerobs_invoice',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'order'                  => 'DESC',
			'orderby'                => 'post_date',

			// KEY
			   'meta_key'   => 'zbsid',
			   'meta_value' => $zbsInvID
		);

		$potentialInvoice = get_posts( $args );

		if (isset($potentialInvoice) && isset($potentialInvoice[0]) && isset($potentialInvoice->ID)) return $potentialInvoice->ID;

		return false;

}
// wh quick shim - checks if (Contact) has any invoices efficiently
function zeroBS_contactHasInvoice($contactID=-1){
	
	if ($contactID > 0){

		// check if has invs (low ball test, 1 per page, no deets)
		$invoices = zeroBS_getInvoicesForCustomer($contactID,false,1,0,false);

		if (is_array($invoices) && count($invoices) > 0) return true;

	}
	
	return false;

}
function zeroBS_getInvoicesForCustomer($customerID=-1,$withFullDetails=false,$perPage=10,$page=0,$withCustomerDeets=false,$orderBy='post_date',$order='DESC'){

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_invoice',
			'post_status'            => 'publish',
			'posts_per_page'         => $perPage,
			'order'                  => $order,
			'orderby'                => $orderBy,

			// KEY
			   'meta_key'   => 'zbs_customer_invoice_customer',
			   'meta_value' => $customerID
		);
		
		#} Add page if page... - dodgy meh
		$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
		if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

		$list = get_posts( $args );

		$ret = array();

		foreach ($list as $ele){

			$retObj = array(

				'id' => 	$ele->ID,
				// not true 'name' => 	$customerEle->post_title,
				'created' => $ele->post_date_gmt,

				#TRANSITIONTOMETANO
				'zbsid'=>get_post_meta($ele->ID, 'zbsid', true)

			);

			#} Full details?
			if ($withFullDetails) {
				
				$retObj['meta'] 		= get_post_meta($ele->ID, 'zbs_customer_invoice_meta', true);

				// DAL2->3 Compatability
				// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
				// ... once migrated to DAL3 fully, this will no longer happen.
				$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
				// / DAL2->3 Compatability

				$retObj['customerid']		= get_post_meta($ele->ID, 'zbs_customer_invoice_customer', true);
				$retObj['companyid']		= get_post_meta($ele->ID, 'zbs_company_invoice_company', true);

				if ($withCustomerDeets && !empty($retObj['customerid'])){
					
					$retObj['customer']		= zeroBS_getCustomer($retObj['customerid']);
				
				}

			}

			$ret[] = $retObj;

		}

		return $ret;
}

function zeroBS_getInvoicesForCompany($companyID=-1,$withFullDetails=false,$perPage=10,$page=0,$withCompanyDeets=false,$orderBy='post_date',$order='DESC'){

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_invoice',
			'post_status'            => 'publish',
			'posts_per_page'         => $perPage,
			'order'                  => $order,
			'orderby'                => $orderBy,

			// KEY
			   'meta_key'   => 'zbs_company_invoice_company',
			   'meta_value' => $companyID
		);
		
		#} Add page if page... - dodgy meh
		$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
		if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

		$list = get_posts( $args );

		$ret = array();

		foreach ($list as $ele){

			$retObj = array(

				'id' => 	$ele->ID,
				// not true 'name' => 	$customerEle->post_title,
				'created' => $ele->post_date_gmt,

				#TRANSITIONTOMETANO
				'zbsid'=>get_post_meta($ele->ID, 'zbsid', true)

			);

			#} Full details?
			if ($withFullDetails) {
				
				$retObj['meta'] 		= get_post_meta($ele->ID, 'zbs_customer_invoice_meta', true);

				// DAL2->3 Compatability
				// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
				// ... once migrated to DAL3 fully, this will no longer happen.
				$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
				// / DAL2->3 Compatability

				$retObj['customerid']		= get_post_meta($ele->ID, 'zbs_customer_invoice_customer', true);
				$retObj['companyid']		= $companyID;

				// if ($withCompanyDeets) - NOT USED HERE.

			}

			$ret[] = $retObj;

		}

		return $ret;
}



function zeroBS_getTransaction($tID=-1){

	if ($tID !== -1){
		
		$retObj = array(
			'id'=>$tID,
			'meta'=>get_post_meta($tID, 'zbs_transaction_meta', true),
			'customerid'=>get_post_meta($tID, 'zbs_parent_cust', true),
			'companyid'=>get_post_meta($tID, 'zbs_parent_co', true)
		);

		// DAL2->3 Compatability
		// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
		// ... once migrated to DAL3 fully, this will no longer happen.
		$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
		// / DAL2->3 Compatability

		return $retObj;

	} else return false;

} 




function zeroBS_getTransactions($withFullDetails=false,$perPage=10,$page=0,$withCustomerDeets=false, $searchPhrase='',$hasTagIDs=array(),$inArray=array(),$sortByField='',$sortOrder='DESC',$withTags=false,$quickFilters=array()){

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_transaction',
			'post_status'            => 'publish',
			'posts_per_page'         => $perPage,
			'order'                  => 'DESC',
			'orderby'                => 'post_date'
		);
		
		if(!empty($hasTagIDs)){
			$args['tax_query'] = array(
                    array(
                    'taxonomy' => 'zerobscrm_transactiontag',
						'field' => 'term_id',
    				'terms' => $hasTagIDs,
                    )
                );
		}
		
		$extraMetaQueries = array();

		#} Quick filters - here for 2.2, probably needs refactoring (or db change)
		if (is_array($quickFilters) && count($quickFilters) > 0){

			// SELECT * FROM `zbscrm_demo_postmeta` WHERE meta_key = 'zbs_customer_meta'

			foreach ($quickFilters as $qFilter){

				// catch these "status" ones separately, can probs do away with lead/customer after this :)
				if (substr($qFilter,0,7) == 'status_'){

					$qFilterStatus = substr($qFilter,7);
					$qFilterStatus = str_replace('_',' ',$qFilterStatus);


					$q = '"status";s:'.strlen($qFilterStatus).':"'. $qFilterStatus .'"';
					$extraMetaQueries[] = array(
			           'key' => 'zbs_transaction_meta',
			           //'value' => '%'.$q.'%',
			           'value' => $q,
			           'compare' => 'LIKE'
			        );

				}

			}


		}


		#} Search
		#} need to rethink search for new DB - this should search all meta..
		if (!empty($searchPhrase)){
			$extraMetaQueries[] = array(
		           'key' => 'zbs_transaction_meta',
		           //'value' => '%'.$q.'%',
		           'value' => $searchPhrase,
		           'compare' => 'LIKE'
		        );
		}

		if (count($extraMetaQueries) > 0){

			// gross.
			$args['meta_query'] = $extraMetaQueries;

		}

		if(!empty($inArr)){    //query by posts in an array
			$args['post__in'] = $inArr;
		}

		#} Sort by (works for ID, name at the moment)
		$acceptableSortFields = array('post_id');
		if (isset($sortByField) && !empty($sortByField) && in_array($sortByField,$acceptableSortFields) && !empty($sortOrder)){

			$args['order'] = $sortOrder; // we're trusting that this is right
			$args['orderby'] = $sortByField;

		}

		#} Add page if page... - dodgy meh
		$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
		if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

		$list = get_posts( $args );

		$ret = array();

		foreach ($list as $ele){

			$retObj = array(

				'id' => 	$ele->ID,
				// not true 'name' => 	$customerEle->post_title,
				'created' => $ele->post_date_gmt,
				// this is set for 'date' so pass...
				'date' => $ele->post_date

			);

			#} Full details?
			if ($withFullDetails) {
				
				$retObj['meta'] 			= get_post_meta($ele->ID, 'zbs_transaction_meta', true);

				// DAL2->3 Compatability
				// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
				// ... once migrated to DAL3 fully, this will no longer happen.
				$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
				// / DAL2->3 Compatability

				$retObj['customerid']		= get_post_meta($ele->ID, 'zbs_parent_cust', true);
				$retObj['companyid']		= get_post_meta($ele->ID, 'zbs_parent_co', true);

				if ($withCustomerDeets){ //
					
					$retObj['customer']		= zeroBS_getCustomer($retObj['customerid']);
					$retObj['company']		= zeroBS_getCompany($retObj['companyid']);
				
				}

				#} With tags?
				if ($withTags){
					
					// https://codex.wordpress.org/Function_Reference/wp_get_object_terms#Argument_Options
					$args = array(
						'order' => 'ASC',
						'orderby' => 'name'
					);
					$retObj['tags'] = zeroBSCRM_getTransactionTagsByID($ele->ID);//wp_get_object_terms($customerEle->ID,'zerobscrm_customertag',$args);

				}

			}

			$ret[] = $retObj;

		}

		return $ret;
}
function zeroBS_getTransactionsCountIncParams( $withFullDetails = false, $perPage = 10, $page = 0, $withCustomerDeets = false, $searchPhrase = '', $hasTagIDs = array(), $inArray = array(), $sortByField = '', $sortOrder = 'DESC', $withTags = false, $quickFilters = array() ) {

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_transaction',
			'post_status'            => 'publish',
			'posts_per_page'         => 1000000,
			'offset'				 => 0,
			'order'                  => 'DESC',
			'orderby'                => 'post_date'
		);
		
		if(!empty($hasTagIDs)){
			$args['tax_query'] = array(
                    array(
                    'taxonomy' => 'zerobscrm_transactiontag',
						'field' => 'term_id',
    				'terms' => $hasTagIDs,
                    )
                );
		}
		
		$extraMetaQueries = array();

		#} Quick filters - here for 2.2, probably needs refactoring (or db change)
		if (is_array($quickFilters) && count($quickFilters) > 0){

			// SELECT * FROM `zbscrm_demo_postmeta` WHERE meta_key = 'zbs_customer_meta'

			foreach ($quickFilters as $qFilter){

				// catch these "status" ones separately, can probs do away with lead/customer after this :)
				if (substr($qFilter,0,7) == 'status_'){

					$qFilterStatus = substr($qFilter,7);
					$qFilterStatus = str_replace('_',' ',$qFilterStatus);


					$q = '"status";s:'.strlen($qFilterStatus).':"'. $qFilterStatus .'"';
					$extraMetaQueries[] = array(
			           'key' => 'zbs_transaction_meta',
			           //'value' => '%'.$q.'%',
			           'value' => $q,
			           'compare' => 'LIKE'
			        );

				}

			}


		}


		#} Search
		#} need to rethink search for new DB - this should search all meta..
		if (!empty($searchPhrase)){
			$extraMetaQueries[] = array(
		           'key' => 'zbs_transaction_meta',
		           //'value' => '%'.$q.'%',
		           'value' => $searchPhrase,
		           'compare' => 'LIKE'
		        );
		}

		if (count($extraMetaQueries) > 0){

			// gross.
			$args['meta_query'] = $extraMetaQueries;

		}

		return count(get_posts( $args ));
}

#} Quick func to retrieve transactions for a customer
function zeroBS_getTransactionsForCustomer($customerID=-1,$withFullDetails=false,$perPage=10,$page=0,$withCustomerDeets=false){

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_transaction',
			'post_status'            => 'publish',
			'posts_per_page'         => $perPage,
			'order'                  => 'DESC',
			'orderby'                => 'post_date',

			// KEY
			   'meta_key'   => 'zbs_parent_cust', #zbs_order_meta zbs_transaction_meta
			   'meta_value' => $customerID
		);
		
		#} Add page if page... - dodgy meh
		$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
		if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

		$list = get_posts( $args );

		$ret = array();

		foreach ($list as $ele){

			$retObj = array(

				'id' => 	$ele->ID,
				// not true 'name' => 	$customerEle->post_title,
				
				// post_date_gmt was not updated anywhere when transaction is updated / saved.
				'created' => $ele->post_date

			);

			#} The way Mike's done this, you always need this meta...
			#} So ignore "$withFullDetails"
			#} And just include meta

			#} Full details?
			#if ($withFullDetails) {
				
				$retObj['meta'] 		= get_post_meta($ele->ID, 'zbs_transaction_meta', true);

				// DAL2->3 Compatability
				// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
				// ... once migrated to DAL3 fully, this will no longer happen.
				$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
				// / DAL2->3 Compatability

				$retObj['customerid']		= $customerID; #?WTF

				if ($withCustomerDeets && !empty($retObj['customerid'])){
					
					$retObj['customer']		= zeroBS_getCustomer($retObj['customerid']);
				
				}

				#} Also add type :)
				$retObj['type'] = get_post_meta($ele->ID,'zerobs_transaction_type',true);

			#}

			$ret[] = $retObj;

		}

		return $ret;
}


#} Quick func to retrieve transactions for a company
function zeroBS_getTransactionsForCompany($companyID=-1,$withFullDetails=false,$perPage=10,$page=0,$withCustomerDeets=false){

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_transaction',
			'post_status'            => 'publish',
			'posts_per_page'         => $perPage,
			'order'                  => 'DESC',
			'orderby'                => 'post_date',

			// KEY
			   'meta_key'   => 'zbs_parent_co', #zbs_order_meta zbs_transaction_meta
			   'meta_value' => $companyID
		);
		
		#} Add page if page... - dodgy meh
		$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
		if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

		$list = get_posts( $args );

		$ret = array();

		foreach ($list as $ele){

			$retObj = array(

				'id' => 	$ele->ID,
				// not true 'name' => 	$customerEle->post_title,
				
				// post_date_gmt was not updated anywhere when transaction is updated / saved.
				'created' => $ele->post_date

			);

			#} The way Mike's done this, you always need this meta...
			#} So ignore "$withFullDetails"
			#} And just include meta

			#} Full details?
			#if ($withFullDetails) {
				
				$retObj['meta'] 		= get_post_meta($ele->ID, 'zbs_transaction_meta', true);
				//$retObj['customerid']		= $customerID; #?WTF

				// DAL2->3 Compatability
				// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
				// ... once migrated to DAL3 fully, this will no longer happen.
				$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
				// / DAL2->3 Compatability


				if ($withCustomerDeets && !empty($retObj['customerid'])){
					
					$retObj['customer']		= zeroBS_getCustomer($retObj['customerid']);
				
				}

				#} Also add type :)
				$retObj['type'] = get_post_meta($ele->ID,'zerobs_transaction_type',true);

			#}

			$ret[] = $retObj;

		}

		return $ret;
}


/*
#} ExternalID is name in this case :)
function zeroBS_getTransactionIDWithExternalSource($transactionExternalSource='',$transactionExternalID=''){


	// retrieve external sources from $zbs now
	global $zbs;



	$ret = false;

	#} No empties, no random externalSources :)
	if (!empty($transactionExternalSource) && !empty($transactionExternalID) && array_key_exists($transactionExternalSource,$zbs->external_sources)){


		#} If here, is legit.
		$approvedExternalSource = $transactionExternalSource;

		#} Will find the post, if exists, no dealing with dupes here, yet?
		$args = array (
			'post_type'              => 'zerobs_transaction',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'order'                  => 'DESC',
			'orderby'                => 'post_date',


				// post v2.4 this is genericified:
				//'meta_key'   => 'zbs_trans_ext_'.$approvedExternalSource,
				'meta_key'   => 'zbs_obj_ext_'.$approvedExternalSource,	
			   
			   'meta_value' => $transactionExternalID
		);

		$potentialTransactionList = get_posts( $args );


		if (count($potentialTransactionList) > 0){

			if (isset($potentialTransactionList[0]) && isset($potentialTransactionList[0]->ID)){

				$ret = $potentialTransactionList[0]->ID;

			}

		}

	}


	return $ret;

} */

// DO NOT USE! Use zeroBS_integrations_addOrUpdateTransaction (from integration funcs!)
function zeroBS_addUpdateTransaction(

		$tID = -1,

		/* 

		example:
			$tFields = array(
				
				REQUIRED:
				'orderid' => 'UNIQUEID',
				'customer' => CustomerID,
				'status' => 'Completed', 'Refunded' similar.
				'total' => 123.99,

				RECOMMENDED:
				'date' => 12345TIME,
				'currency' => 'USD',
				'item' => 'TITLE',
				'net' => 0,
				'tax' => 0,
				'fee' => 0,
				'discount' => 0,
				'tax_rate' => 0,


			);

		*/

		$tFields = array(),

		$transactionExternalSource='',
		$transactionExternalID='',
		$transactionDate='',
		$transactionTags=array(), /* extra */

		$fallBackLog = false,
		$extraMeta = false,
		$automatorPassthrough = false

		){

	global $zbs;


	#} return
	$ret = false;

	#} Try and get trans customer from meta
	$zbsTransactionCustomer = ''; if (isset($tFields) && isset($tFields['customer']) && !empty($tFields['customer'])) $zbsTransactionCustomer = (int)$tFields['customer'];
	$zbsTransactionCompany = ''; if (isset($tFields) && isset($tFields['company']) && !empty($tFields['company'])) $zbsTransactionCompany = (int)$tFields['company'];

	#} Basics - /--needs unique ID, total MINIMUM
	if (isset($tFields) && count($tFields) > 0){ //  && isset($zbsTransactionCustomer) && !empty($zbsTransactionCustomer)#} && isset($cFields['zbsc_status'])

		#} New flag
		$newTrans = false;


			if ($tID > 0){

				#} Retrieve / check?
				#} Na... v1.1 .lol
				#} lol we're now 1.2.1! and still not validating here :o
				$postID = $tID;

				#} Build "existing meta" to pass, (so we only update fields pushed here)
				$existingMeta = zeroBS_getTransactionMeta($postID);

			} else {

				#} Set flag
				$newTrans = true;

				#} Build header 
				$headerPost = array(
										'post_type' => 'zerobs_transaction',
										'post_status' => 'publish',
										'comment_status' => 'closed'
									);

				#} This still could do with Validation, eventually.
				if (!empty($transactionDate)) $headerPost['post_date'] = $transactionDate;

				#} If any useful title, add it (for now it's trans id)
				if (isset($tFields) && isset($tFields['orderid']) && !empty($tFields['orderid'])) $headerPost['post_title'] = $tFields['orderid'];
					
				#} Insert
				$postID = wp_insert_post($headerPost);

				#} Set up empty meta arr
				$existingMeta = array();

			}

			#} Assign/update customer link - NO CHECKING :o
			update_post_meta($postID,'zbs_parent_cust', $zbsTransactionCustomer);
			update_post_meta($postID,'zbs_parent_co', $zbsTransactionCompany);

			#} Default status setting using existing meta as wrapper... will be overwritten with whatever is passed, if passed... #defaultstatus
			if (isset($existingMeta) && is_array($existingMeta) && !isset($existingMeta['status'])) $existingMeta['status'] = 'Unknown';

			#} Add external source/externalid
			#} No empties, no random externalSources :)
			$approvedExternalSource = ''; #} As this is passed to automator :)

			if (!empty($transactionExternalSource) && !empty($transactionExternalID) && array_key_exists($transactionExternalSource,$zbs->external_sources)){

				#} If here, is legit.
				$approvedExternalSource = $transactionExternalSource;

				#} Add/Update record flag
                // 2.4+ Migrated away from this method to new 
                // update_post_meta($postID, 'zbs_trans_ext_'.$approvedExternalSource, $transactionExternalID);
                // 2.52+ Moved to new DAL method :)
                
                $extSourceArr = array(
                    'source' => $approvedExternalSource,
                    'uid' => $transactionExternalID
                    );


                // type = 5 = transaction
                zeroBS_updateExternalSource(5,$postID,$extSourceArr);


			} #} Otherwise will just be a random customer no ext source



			#} Build meta
			if (!empty($postID)){

				#} Build using centralised func below, passing any existing meta (updates not overwrites)
				$zbsTransactionMeta = zeroBS_buildTransactionMeta($tFields,$existingMeta);

				#} For now a brutal pass through:
				if (isset($tFields['trans_time']) && !empty($tFields['trans_time'])) $zbsTransactionMeta['trans_time'] = (int)$tFields['trans_time'];

				#} Update record
                update_post_meta($postID, 'zbs_transaction_meta', $zbsTransactionMeta);

                #} Any extra meta keyval pairs?
                if (isset($extraMeta) && is_array($extraMeta)) foreach ($extraMeta as $k => $v){

                	#} This won't fix stupid keys, just catch basic fails... 
                	$cleanKey = strtolower(str_replace(' ','_',$k));

                	#} Brutal update
                	update_post_meta($postID, 'zbs_transaction_extra_'.$cleanKey, $v);

                }

                #} Add/Replace any tags
                if (isset($transactionTags) && is_array($transactionTags)) wp_set_object_terms( $postID, $transactionTags, 'zerobscrm_transactiontag', false );

			}



			#} INTERNAL AUTOMATOR 
			#} & 
			#} FALLBACKS
			if ($newTrans){

				#} Add to automator
				zeroBSCRM_FireInternalAutomator('transaction.new',array(
	                'id'=>$postID,
	                'transactionMeta'=>$zbsTransactionMeta,
                    'againstid' => $zbsTransactionCustomer,
	                'extsource'=>$approvedExternalSource,
	                'automatorpassthrough'=>$automatorPassthrough #} This passes through any custom log titles or whatever into the Internal automator recipe.
	            ));

			} else {

				#} Transaction Update here (automator)?
				#} TODO


				#} FALLBACK 
				#} (This fires for customers that weren't added because they already exist.)
				#} e.g. x@g.com exists, so add log "x@g.com filled out form"
				#} Requires a type and a shortdesc
				if (
					isset($fallBackLog) && is_array($fallBackLog) 
					&& isset($fallBackLog['type']) && !empty($fallBackLog['type'])
					&& isset($fallBackLog['shortdesc']) && !empty($fallBackLog['shortdesc'])
				){

					#} Brutal add, maybe validate more?!

					#} Long desc if present:
					$zbsNoteLongDesc = ''; if (isset($fallBackLog['longdesc']) && !empty($fallBackLog['longdesc'])) $zbsNoteLongDesc = $fallBackLog['longdesc'];

						#} Only raw checked... but proceed.
						$newOrUpdatedLogID = zeroBS_addUpdateLog($postID,-1,-1,array(
							#} Anything here will get wrapped into an array and added as the meta vals
							'type' => $fallBackLog['type'],
							'shortdesc' => $fallBackLog['shortdesc'],
							'longdesc' => $zbsNoteLongDesc
						),'zerobs_transaction');


				}


			}

			#} REQ?
			#} MAKE SURE if you change any post_name features you also look at: "NAMECHANGES" in this file (when a post updates it'll auto replace these...)
	        #$newCName = zeroBS_customerName('',$zbsMeta,true,false)


			#} Return customerID if success :)
			$ret = $postID;


	}



	return $ret;

}


#} Quick wrapper to future-proof.
#} Should later replace all get_post_meta's with this
function zeroBS_getTransactionMeta($cID=-1){

	if (!empty($cID)) return get_post_meta($cID, 'zbs_transaction_meta', true);

	return false;

}


#} Adapted from customer equiv 1.2.1
function zeroBS_buildTransactionMeta($arraySource=array(),$startingArray=array()){

	#} def
	$zbsTransMeta = array();

	#} if passed...
	if (isset($startingArray) && is_array($startingArray)) $zbsTransMeta = $startingArray;


	#} go
        global $zbsTransactionFields;

        foreach ($zbsTransactionFields as $fK => $fV){

            if (!isset($zbsTransMeta[$fK])) $zbsTransMeta[$fK] = '';

            #} Just using key here
            #if (isset($arraySource['zbsc_'.$fK])) {
            if (isset($arraySource[$fK])) {

                switch ($fV[0]){

                    case 'tel':

                        // validate tel?
                        $zbsTransMeta[$fK] = sanitize_text_field($arraySource[$fK]);
                        preg_replace("/[^0-9 ]/", '', $zbsTransMeta[$fK]);
                        break;

                    case 'price':
                    case 'numberfloat': // same as price, ultimately

                        // validate float
                        $zbsTransMeta[$fK] = sanitize_text_field($arraySource[$fK]);
                        $zbsTransMeta[$fK] = preg_replace('@[^0-9\.]+@i', '-', $zbsTransMeta[$fK]);
                        $zbsTransMeta[$fK] = floatval($zbsTransMeta[$fK]);
                        break;

                    case 'numberint':

                        // validate int
                        $zbsTransMeta[$fK] = sanitize_text_field($arraySource[$fK]);
                        $zbsTransMeta[$fK] = preg_replace('@[^0-9]+@i', '-', $zbsTransMeta[$fK]);
                        $zbsTransMeta[$fK] = floatval($zbsTransMeta[$fK]);
                        break;


                    case 'textarea':

                        $zbsTransMeta[$fK] = zeroBSCRM_textProcess($arraySource[$fK]);

                        break;


                    default:

                        $zbsTransMeta[$fK] = sanitize_text_field($arraySource[$fK]);

                        break;


                }


            }


        }

    return $zbsTransMeta;
}



// simple wrapper for Form 
function zeroBS_getForm($fID=-1){

	if ($fID !== -1){
		
		$retObj = array(
			'id'=>$fID,
			
			// mikes init fields
			'meta'=>get_post_meta($fID,'zbs_form_field_meta',true),
			'style'=>get_post_meta($fID, 'zbs_form_style', true),
			'views'=>get_post_meta($fID, 'zbs_form_views', true),
			'conversions'=>get_post_meta($fID, 'zbs_form_conversions', true)

		);				

		// DAL2->3 Compatability
		// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
		// ... once migrated to DAL3 fully, this will no longer happen.
		$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
		// / DAL2->3 Compatability

		return $retObj;

	} else return false;
}

/* WH: Removed this as I improved the original, rather than accumulate multiple
#} Taken from the Transactions getTransactions. I know there's a getForms below BUT spent a while with this
#} For transactions so will do other list views from this one (search, etc.)
function zeroBS_getFormsv2($withFullDetails=false,$perPage=10,$page=0,$withCustomerDeets=false, $searchPhrase,$hasTagIDs=array(),$inArray=array(),$quickFilters=array()){

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_form',
			'post_status'            => 'publish',
			'posts_per_page'         => $perPage,
			'order'                  => 'DESC',
			'orderby'                => 'post_date'
		);
		
		/* NO TAGGING OR QUICK FILTERS FOR FORMS 
		if(!empty($hasTagIDs)){
			$args['tax_query'] = array(
                    array(
                    'taxonomy' => 'zerobscrm_transactiontag',
						'field' => 'term_id',
    				'terms' => $hasTagIDs,
                    )
                );
		}

		

					#} Quick filters - here for 2.2, probably needs refactoring (or db change)
		if (is_array($quickFilters) && count($quickFilters) > 0){

			// SELECT * FROM `zbscrm_demo_postmeta` WHERE meta_key = 'zbs_customer_meta'
			$extraMetaQueries = array();

			foreach ($quickFilters as $qFilter){

				// catch these "status" ones separately, can probs do away with lead/customer after this :)
				if (substr($qFilter,0,7) == 'status_'){

					$qFilterStatus = substr($qFilter,7);
					$qFilterStatus = str_replace('_',' ',$qFilterStatus);

				

					$q = '"status";s:'.strlen($qFilterStatus).':"'. $qFilterStatus .'"';
					$extraMetaQueries[] = array(
			           'key' => 'zbs_transaction_meta',
			           //'value' => '%'.$q.'%',
			           'value' => $q,
			           'compare' => 'LIKE'
			        );

				}

			}

			if (count($extraMetaQueries) > 0){

				// gross.
				$args['meta_query'] = $extraMetaQueries;

			}


		}

		* /

		#} Search
		#} need to rethink search for new DB - this should search all meta..
		if (!empty($searchPhrase)){
			$extraMetaQueries[] = array(
		           'key' => 'zbs_form_meta',
		           //'value' => '%'.$q.'%',
		           'value' => $searchPhrase,
		           'compare' => 'LIKE'
		        );
		}

		if (count($extraMetaQueries) > 0){

			// gross.
			$args['meta_query'] = $extraMetaQueries;

		}

		#} Add page if page... - dodgy meh
		$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
		if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

		$list = get_posts( $args );

		$ret = array();

		foreach ($list as $ele){

			$retObj = array(

				'id' => 	$ele->ID,
				// not true 'name' => 	$customerEle->post_title,
				'created' => $ele->post_date_gmt,
				'title' => $ele->post_title

			);


			$ret[] = $retObj;

		}

		return $ret;
}

#} v2 of getting quotes (for Mike list view)
function zeroBS_getQuotesv2($withFullDetails=false,$perPage=10,$page=0,$withCustomerDeets=false, $searchPhrase,$hasTagIDs=array(),$inArray=array(),$quickFilters=array()){

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_quote',
			'post_status'            => 'publish',
			'posts_per_page'         => $perPage,
			'order'                  => 'DESC',
			'orderby'                => 'post_date'
		);
		
		/* NO TAGGING OR QUICK FILTERS FOR FORMS 
		if(!empty($hasTagIDs)){
			$args['tax_query'] = array(
                    array(
                    'taxonomy' => 'zerobscrm_transactiontag',
						'field' => 'term_id',
    				'terms' => $hasTagIDs,
                    )
                );
		}

		

					#} Quick filters - here for 2.2, probably needs refactoring (or db change)
		if (is_array($quickFilters) && count($quickFilters) > 0){

			// SELECT * FROM `zbscrm_demo_postmeta` WHERE meta_key = 'zbs_customer_meta'
			$extraMetaQueries = array();

			foreach ($quickFilters as $qFilter){

				// catch these "status" ones separately, can probs do away with lead/customer after this :)
				if (substr($qFilter,0,7) == 'status_'){

					$qFilterStatus = substr($qFilter,7);
					$qFilterStatus = str_replace('_',' ',$qFilterStatus);

		

					$q = '"status";s:'.strlen($qFilterStatus).':"'. $qFilterStatus .'"';
					$extraMetaQueries[] = array(
			           'key' => 'zbs_transaction_meta',
			           //'value' => '%'.$q.'%',
			           'value' => $q,
			           'compare' => 'LIKE'
			        );

				}

			}

			if (count($extraMetaQueries) > 0){

				// gross.
				$args['meta_query'] = $extraMetaQueries;

			}


		}

		* /

		#} Search
		#} need to rethink search for new DB - this should search all meta..
		if (!empty($searchPhrase)){
			$extraMetaQueries[] = array(
		           'key' => 'zbs_customer_quote_meta',
		           //'value' => '%'.$q.'%',
		           'value' => $searchPhrase,
		           'compare' => 'LIKE'
		        );
		}

		if (count($extraMetaQueries) > 0){

			// gross.
			$args['meta_query'] = $extraMetaQueries;

		}




		#} Add page if page... - dodgy meh
		$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
		if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

		$list = get_posts( $args );

		$ret = array();

		foreach ($list as $ele){

			$retObj = array(

				'id' => 	$ele->ID,
				// not true 'name' => 	$customerEle->post_title,
				'created' => $ele->post_date_gmt,
				'title' => $ele->post_title

			);


			$retObj['meta'] = get_post_meta($ele->ID,'zbs_customer_quote_meta',true);

			#} Full details?
			if ($withFullDetails) {
				
				$retObj['customerid'] 	= get_post_meta($ele->ID, 'zbs_customer_quote_customer', true);

				if ($withCustomerDeets && !empty($retObj['customerid'])){
					
					$retObj['customer']		= zeroBS_getCustomer($retObj['customerid']);
				
				}

			}

			$ret[] = $retObj;

		}




		return $ret;
}

#} v2 of getting quotes (for Mike list view)
function zeroBS_getInvoicesv2($withFullDetails=false,$perPage=10,$page=0,$withCustomerDeets=false, $searchPhrase,$hasTagIDs=array(),$inArray=array(),$quickFilters=array()){

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_invoice',
			'post_status'            => 'publish',
			'posts_per_page'         => $perPage,
			'order'                  => 'DESC',
			'orderby'                => 'post_date'
		);
		
		/* NO TAGGING OR QUICK FILTERS FOR FORMS 
		if(!empty($hasTagIDs)){
			$args['tax_query'] = array(
                    array(
                    'taxonomy' => 'zerobscrm_transactiontag',
						'field' => 'term_id',
    				'terms' => $hasTagIDs,
                    )
                );
		}

		

					#} Quick filters - here for 2.2, probably needs refactoring (or db change)
		if (is_array($quickFilters) && count($quickFilters) > 0){

			// SELECT * FROM `zbscrm_demo_postmeta` WHERE meta_key = 'zbs_customer_meta'
			$extraMetaQueries = array();

			foreach ($quickFilters as $qFilter){

				// catch these "status" ones separately, can probs do away with lead/customer after this :)
				if (substr($qFilter,0,7) == 'status_'){

					$qFilterStatus = substr($qFilter,7);
					$qFilterStatus = str_replace('_',' ',$qFilterStatus);


					$q = '"status";s:'.strlen($qFilterStatus).':"'. $qFilterStatus .'"';
					$extraMetaQueries[] = array(
			           'key' => 'zbs_transaction_meta',
			           //'value' => '%'.$q.'%',
			           'value' => $q,
			           'compare' => 'LIKE'
			        );

				}

			}

			if (count($extraMetaQueries) > 0){

				// gross.
				$args['meta_query'] = $extraMetaQueries;

			}


		}

		* /

		#} Search
		#} need to rethink search for new DB - this should search all meta..
		if (!empty($searchPhrase)){
			$extraMetaQueries[] = array(
		           'key' => 'zbs_invoice_meta',
		           //'value' => '%'.$q.'%',
		           'value' => $searchPhrase,
		           'compare' => 'LIKE'
		        );
		}

		if (count($extraMetaQueries) > 0){

			// gross.
			$args['meta_query'] = $extraMetaQueries;

		}




		#} Add page if page... - dodgy meh
		$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
		if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

		$list = get_posts( $args );

		$ret = array();

		foreach ($list as $ele){

			$retObj = array(

				'id' => 	$ele->ID,
				// not true 'name' => 	$customerEle->post_title,
				'created' => $ele->post_date_gmt,

			);


			$retObj['title'] = __("Invoice " . $ele->ID, 'zero-bs-crm');
			$retObj['meta'] = get_post_meta($ele->ID,'zbs_customer_invoice_meta',true);

			#} Full details?
			if ($withFullDetails) {
				
				$retObj['customerid'] 	= get_post_meta($ele->ID, 'zbs_customer_invoice_customer', true);

				if ($withCustomerDeets && !empty($retObj['customerid'])){
					
					$retObj['customer']		= zeroBS_getCustomer($retObj['customerid']);
				
				}

			}

			$ret[] = $retObj;

		}




		return $ret;
}
*/

function zeroBS_getForms($withFullDetails=false,$perPage=10,$page=0,$searchPhrase='',$inArray=array(),$sortByField='',$sortOrder='DESC',$quickFilters=array()){

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_form',
			'post_status'            => 'publish',
			'posts_per_page'         => $perPage,
			'order'                  => 'DESC',
			'orderby'                => 'post_date'
		);
		

		$extraMetaQueries = array();

		#} Search
		if (!empty($searchPhrase)) $args['s'] = $searchPhrase;

		#} Quick filters - here for 2.2, probably needs refactoring (or db change)
		/* none yet
		// NOTE: This is hacky
		if (is_array($quickFilters) && count($quickFilters) > 0){

			foreach ($quickFilters as $qFilter){

				// normal/hardtyped

				switch ($qFilter){


					case 'status_draft':

						// hack 
						$q = 's:6:"status";s:5:"Draft"';
						$extraMetaQueries[] = array(
				           'key' => 'zbs_customer_invoice_meta',
				           //'value' => '%'.$q.'%',
				           'value' => $q,
				           'compare' => 'LIKE'
				        );

						break;


				}  // / switch

			} // foreach


		} // quick filters
		*/

		if (count($extraMetaQueries) > 0){

			// gross.
			$args['meta_query'] = $extraMetaQueries;

		}

		if(!empty($inArr)){    //query by posts in an array
			$args['post__in'] = $inArr;
		}

		#} Sort by (works for ID, name at the moment)
		$acceptableSortFields = array('post_id');
		if (isset($sortByField) && !empty($sortByField) && in_array($sortByField,$acceptableSortFields) && !empty($sortOrder)){

			$args['order'] = $sortOrder; // we're trusting that this is right
			$args['orderby'] = $sortByField;

		}

		#} Add page if page... - dodgy meh
		$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
		if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

		$list = get_posts( $args );

		$ret = array();

		foreach ($list as $ele){

			$retObj = array(

				'id' => 	$ele->ID,
				// mikes init fields - just styles by default
				'meta'=>get_post_meta($ele->ID,'zbs_form_field_meta',true),
				'style'=>get_post_meta($ele->ID, 'zbs_form_style', true),
				'title'=>$ele->post_title,
				'created' => $ele->post_date_gmt

			);

			// DAL2->3 Compatability
			// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
			// ... once migrated to DAL3 fully, this will no longer happen.
			$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
			// / DAL2->3 Compatability

			#} Full details?
			if ($withFullDetails) {
				
				// full deets inc views + conversions
				$retObj['views'] 		= get_post_meta($ele->ID, 'zbs_form_views', true);
				$retObj['conversions']		= get_post_meta($ele->ID, 'zbs_form_conversions', true);

			}

			$ret[] = $retObj;

		}

		return $ret;
}

function zeroBS_getFormsCountIncParams($withFullDetails=false,$perPage=10,$page=0,$searchPhrase='',$inArray=array(),$sortByField='',$sortOrder='DESC',$quickFilters=array()){

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_form',
			'post_status'            => 'publish',
			'posts_per_page'         => 10000000,
			'offset'				 => 0,
			'order'                  => 'DESC',
			'orderby'                => 'post_date'
		);
		

		$extraMetaQueries = array();

		#} Search
		if (!empty($searchPhrase)) $args['s'] = $searchPhrase;

		#} Quick filters - here for 2.2, probably needs refactoring (or db change)
		/* none yet
		// NOTE: This is hacky
		if (is_array($quickFilters) && count($quickFilters) > 0){

			foreach ($quickFilters as $qFilter){

				// normal/hardtyped

				switch ($qFilter){


					case 'status_draft':

						// hack 
						$q = 's:6:"status";s:5:"Draft"';
						$extraMetaQueries[] = array(
				           'key' => 'zbs_customer_invoice_meta',
				           //'value' => '%'.$q.'%',
				           'value' => $q,
				           'compare' => 'LIKE'
				        );

						break;


				}  // / switch

			} // foreach


		} // quick filters
		*/

		if (count($extraMetaQueries) > 0){

			// gross.
			$args['meta_query'] = $extraMetaQueries;

		}

		if(!empty($inArr)){    //query by posts in an array
			$args['post__in'] = $inArr;
		}

		#} Sort by (works for ID, name at the moment)
		$acceptableSortFields = array('post_id');
		if (isset($sortByField) && !empty($sortByField) && in_array($sortByField,$acceptableSortFields) && !empty($sortOrder)){

			$args['order'] = $sortOrder; // we're trusting that this is right
			$args['orderby'] = $sortByField;

		}

		#} Add page if page... - dodgy meh
		$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
		if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

		return count(get_posts( $args ));
}



function zeroBS_getQuoteTemplate($tID=-1){

	if ($tID !== -1){
		
		$retObj = array(
			'id'=>$tID,
			'meta'=>get_post_meta($tID, 'zbs_quotemplate_meta', true),
			'zbsdefault'=>get_post_meta($tID, 'zbsdefault', true),
			'content'=> get_post_field('post_content', $tID) #http://wordpress.stackexchange.com/questions/9667/get-wordpress-post-content-by-post-id
			);

		// DAL2->3 Compatability
		// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
		// ... once migrated to DAL3 fully, this will no longer happen.
		$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
		// / DAL2->3 Compatability

		return $retObj;

	} else return false;

} 

function zeroBS_getQuoteTemplates($withFullDetails=false,$perPage=10,$page=0){

		#} Page legit? - lazy check
		if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

		$args = array (
			'post_type'              => 'zerobs_quo_template',
			'post_status'            => 'publish',
			'posts_per_page'         => $perPage,
			'order'                  => 'DESC',
			'orderby'                => 'post_date'
		);
		
		#} Add page if page... - dodgy meh
		$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
		if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

		$list = get_posts( $args );

		$ret = array();

		foreach ($list as $ele){

			$retObj = array(

				'id' => 	$ele->ID,
				'name' => 	$ele->post_title,
				'created' => $ele->post_date_gmt,
				'content'=> $ele->post_content

			);

			#} Full details?
			if ($withFullDetails) {
				
				$retObj['meta'] 			= get_post_meta($ele->ID, 'zbs_quotemplate_meta', true);
		
				// DAL2->3 Compatability
				// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
				// ... once migrated to DAL3 fully, this will no longer happen.
				$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
				// / DAL2->3 Compatability


				$retObj['zbsdefault'] 		= get_post_meta($ele->ID, 'zbsdefault', true);

			}

			$ret[] = $retObj;

		}

		return $ret;
}



// THIS STAYS THE SAME FOR DB2 until trans+invoices MOVED OVER #DB2ROUND2
#} Main function to return a customers "total value" 
#} At MVP that means Invoices + Transactions
function zeroBS_customerTotalValue($custID='',$customerInvoices=array(),$customerTransactions=array()){

	$tot = 0;
	
	#} Brutal tot up of invoices
	if (isset($customerInvoices) && is_array($customerInvoices)) foreach ($customerInvoices as $inv){

            if (isset($inv['meta']) && isset($inv['meta']['val'])) $tot += floatval($inv['meta']['val']);

    }
	
	#} Brutal tot up of transacts
	#} EXCLUDING ALLOCATED ONES - as it can be assumed allocated transactions are accounted for by inv tot
	if (isset($customerTransactions) && is_array($customerTransactions)) foreach ($customerTransactions as $transact){

		#} If invoice_id not set!
        if (
    			isset($transact['meta']) && 
        		(
        			!isset($transact['meta']['invoice_id']) ||
        			(isset($transact['meta']['invoice_id']) && empty($transact['meta']['invoice_id']))
        		)
        ) {

            if (isset($transact['meta']) && isset($transact['meta']['total'])) $tot += floatval($transact['meta']['total']);

        }

    }

	return $tot;
}

// THIS STAYS THE SAME FOR DB2 until quotes MOVED OVER #DB2ROUND2
#} Adds up value of quotes for a customer...
function zeroBS_customerQuotesValue($custID='',$customerQuotes=array()){

	$tot = 0;
	
	#} Brutal tot up of invoices
	if (isset($customerQuotes) && is_array($customerQuotes)) foreach ($customerQuotes as $quote){

            if (isset($quote['meta']) && isset($quote['meta']['val'])) $tot += floatval($quote['meta']['val']);

    }
	
	return $tot;
}

// THIS STAYS THE SAME FOR DB2 until invoices MOVED OVER #DB2ROUND2
#} Adds up value of invoices for a customer...
function zeroBS_customerInvoicesValue($custID='',$customerInvoices=array()){

	$tot = 0;
	
	#} Brutal tot up of invoices
	if (isset($customerInvoices) && is_array($customerInvoices)) foreach ($customerInvoices as $inv){

            if (isset($inv['meta']) && isset($inv['meta']['val'])) $tot += floatval($inv['meta']['val']);

    }

	return $tot;
}

// same as above, but only for PAID invoices
// THIS STAYS THE SAME FOR DB2 until invoices MOVED OVER #DB2ROUND2
#} Adds up value of invoices for a customer...
function zeroBS_customerInvoicesValuePaid($custID='',$customerInvoices=array()){

	$tot = 0;
	
	#} Brutal tot up of invoices
	if (isset($customerInvoices) && is_array($customerInvoices)) foreach ($customerInvoices as $inv){

            if (isset($inv['meta']) && isset($inv['meta']['val'])) $tot += floatval($inv['meta']['val']);

    }

	return $tot;
}

// same as above, but only for NOT PAID (i.e. amount due)
// THIS STAYS THE SAME FOR DB2 until invoices MOVED OVER #DB2ROUND2
#} Adds up value of invoices for a customer...
function zeroBS_customerInvoicesValueNotPaid($custID='',$customerInvoices=array()){

	$tot = 0;
	
	#} Brutal tot up of invoices
	if (isset($customerInvoices) && is_array($customerInvoices)) foreach ($customerInvoices as $inv){

            if (isset($inv['meta']) && isset($inv['meta']['val'])) $tot += floatval($inv['meta']['val']);

    }

	return $tot;
}

// THIS STAYS THE SAME FOR DB2 until trans MOVED OVER #DB2ROUND2
#} Adds up value of transactions for a customer...
function zeroBS_customerTransactionsValue($custID='',$customerTransactions=array()){

	$tot = 0; $includeAll = false;
	$status_to_include = zeroBSCRM_getSetting('transinclude_status');	


	// Debug echo 'id:'.$custID.',status'.(!is_array($status_to_include) && $status_to_include == 'all').':<pre>'; print_r($status_to_include); echo '</pre>trans:'; print_r($customerTransactions);

	#} only include the status we say
	// repeating above code? $status_to_include = zeroBSCRM_getSetting('transinclude_status');
	// WH tweak, if set to 'all' string, inc all if (!is_array($status_to_include)) $status_to_include = array();
	if (!is_array($status_to_include) && $status_to_include === 'all'){
		
		$includeAll = true;
		$search_array = array();

	} else {

		if (!is_array($status_to_include)) $status_to_include = array();
		$search_array 		= array_map('strtolower', $status_to_include);
	}

	#} Brutal tot up of transacts
	#} EXCLUDING ALLOCATED ONES - as it can be assumed allocated transactions are accounted for by inv tot
	if (isset($customerTransactions) && is_array($customerTransactions)) foreach ($customerTransactions as $transact){

        $this_trans_status = strtolower(str_replace(' ','_',str_replace(':','_',$transact['meta']['status'])));

		if ($includeAll || in_array($this_trans_status, $search_array)){
			$tot += floatval($transact['meta']['total']);
		}


    }
	return $tot;
} 



// This can, for now, ultimately be a wrapper for zeroBS_customerInvoicesValue
// used in company single view
function zeroBS_companyInvoicesValue($companyID='',$companyInvoices=array()){

	// func just counts up :)
	return zeroBS_customerInvoicesValue($companyID,$companyInvoices);

}


// This can, for now, ultimately be a wrapper for zeroBS_customerTransactionsValue
// used in company single view
function zeroBS_companyTransactionsValue($companyID='',$companyTransactions=array()){

	// func just counts up :)
	return zeroBS_customerTransactionsValue($companyID,$companyTransactions);

}


function zeroBS_companyName($companyID='',$companyMeta=array(),$incFirstLineAddr=true,$incID=true){
	
	$ret = '';

	if (isset($companyMeta['coname']) && !empty($companyMeta['coname'])) $ret .= $companyMeta['coname'];

	#} First line of addr?
	if ($incFirstLineAddr) if (isset($companyMeta['addr1']) && !empty($companyMeta['addr1'])) $ret .= ' ('.$companyMeta['addr1'].')';

	#} ID?
	if ($incID) $ret .= ' #'.$companyID;

	return trim($ret);
}





#} Returns a str of address, ($third param = 'short','full')
#} Pass an ID OR a customerMeta array (saves loading ;) - in fact doesn't even work with ID yet... lol)
function zeroBS_companyAddr($companyID='',$companyMeta=array(),$addrFormat = 'short',$delimiter= ', '){
	
	$ret = '';

	if ($addrFormat == 'short'){

		if (isset($companyMeta['addr1']) && !empty($companyMeta['addr1'])) $ret = $companyMeta['addr1'];
		if (isset($companyMeta['city']) && !empty($companyMeta['city'])) $ret .= zeroBS_delimiterIf($delimiter,$ret).$companyMeta['city'];

	} else if ($addrFormat == 'full'){

		if (isset($companyMeta['addr1']) && !empty($companyMeta['addr1'])) $ret = $companyMeta['addr1'];
		if (isset($companyMeta['addr2']) && !empty($companyMeta['addr2'])) $ret .= zeroBS_delimiterIf($delimiter,$ret).$companyMeta['addr2'];
		if (isset($companyMeta['city']) && !empty($companyMeta['city'])) $ret .= zeroBS_delimiterIf($delimiter,$ret).$companyMeta['city'];
		if (isset($companyMeta['county']) && !empty($companyMeta['county'])) $ret .= zeroBS_delimiterIf($delimiter,$ret).$companyMeta['county'];
		if (isset($companyMeta['postcode']) && !empty($companyMeta['postcode'])) $ret .= zeroBS_delimiterIf($delimiter,$ret).$companyMeta['postcode'];


	}

	return trim($ret);
}

#} Returns a str of address, ($third param = 'short','full')
#} Pass an ID OR a customerMeta array (saves loading ;) - in fact doesn't even work with ID yet... lol)
function zeroBS_companySecondAddr($companyID='',$companyMeta=array(),$addrFormat = 'short',$delimiter= ', '){
	
	$ret = '';

	if ($addrFormat == 'short'){

		if (isset($companyMeta['secaddr_addr1']) && !empty($companyMeta['secaddr_addr1'])) $ret = $companyMeta['secaddr_addr1'];
		if (isset($companyMeta['secaddr_city']) && !empty($companyMeta['secaddr_city'])) $ret .= zeroBS_delimiterIf($delimiter,$ret).$companyMeta['secaddr_city'];

	} else if ($addrFormat == 'full'){

		if (isset($companyMeta['secaddr_addr1']) && !empty($companyMeta['secaddr_addr1'])) $ret = $companyMeta['secaddr_addr1'];
		if (isset($companyMeta['secaddr_addr2']) && !empty($companyMeta['secaddr_addr2'])) $ret .= zeroBS_delimiterIf($delimiter,$ret).$companyMeta['secaddr_addr2'];
		if (isset($companyMeta['secaddr_city']) && !empty($companyMeta['secaddr_city'])) $ret .= zeroBS_delimiterIf($delimiter,$ret).$companyMeta['secaddr_city'];
		if (isset($companyMeta['secaddr_county']) && !empty($companyMeta['secaddr_county'])) $ret .= zeroBS_delimiterIf($delimiter,$ret).$companyMeta['secaddr_county'];
		if (isset($companyMeta['secaddr_postcode']) && !empty($companyMeta['secaddr_postcode'])) $ret .= zeroBS_delimiterIf($delimiter,$ret).$companyMeta['secaddr_postcode'];


	}

	return trim($ret);
}



function zeroBS_getQuoteCount(){

	$counts = wp_count_posts('zerobs_quote');

	if (isset($counts) && isset($counts->publish)) return (int)$counts->publish;

	return 0;
}

function zeroBS_getInvoiceCount(){

	$counts = wp_count_posts('zerobs_invoice');

	if (isset($counts) && isset($counts->publish)) return (int)$counts->publish;

	return 0;
}

function zeroBS_getCompanyCount(){

	$counts = wp_count_posts('zerobs_company');

	if (isset($counts) && isset($counts->publish)) return (int)$counts->publish;

	return 0;
}

function zeroBS_getTransactionCount(){

	$counts = wp_count_posts('zerobs_transaction');

	if (isset($counts) && isset($counts->publish)) return (int)$counts->publish;

	return 0;
}


function zeroBS_getQuoteTemplateCount(){

	$counts = wp_count_posts('zerobs_quo_template');

	if (isset($counts) && isset($counts->publish)) return (int)$counts->publish;

	return 0;
}


#} Exploring sql ver of retrieving inv,quote,trans
function zeroBS_getCustomerExtrasViaSQL($customerID=-1){

	global $wpdb;
	
	/*$query = "
	SELECT posts.post_type,posts.ID,posts.post_date_gmt,
	(SELECT meta_value FROM `zbscrm_demo_postmeta` WHERE post_id = posts.ID AND meta_key = CONCAT('zbs_customer_' , substr(posts.post_type,8) , '_meta')) meta
	FROM $wpdb->posts posts
	LEFT JOIN $wpdb->postmeta ON posts.ID = postmeta.post_id
	WHERE 
	posts.post_type IN ('zerobs_quote','zerobs_invoice')
	AND posts.post_status = 'publish'
	AND postmeta.meta_value = $customerID
	ORDER BY ID DESC limit 0,10000";*/

	#} This one does transactions too :)
	$query = "
	SELECT posts.post_type,posts.ID,posts.post_date_gmt,
	(SELECT meta_value FROM $wpdb->postmeta WHERE post_id = posts.ID AND meta_key = CONCAT('zbs_customer_' , substr(posts.post_type,8) , '_meta')) meta,
	(SELECT meta_value FROM $wpdb->postmeta WHERE post_id = posts.ID AND meta_key = 'zbs_transaction_meta') transmeta

	FROM $wpdb->posts posts
	LEFT JOIN $wpdb->postmeta postmeta ON posts.ID = postmeta.post_id
	WHERE 
	posts.post_type IN ('zerobs_quote','zerobs_invoice','zerobs_transaction')
	AND posts.post_status = 'publish'
	AND postmeta.meta_value = ".(int)$customerID." AND postmeta.meta_key IN ('zbs_customer_quote_customer','zbs_customer_invoice_customer','zbs_parent_cust')
	ORDER BY ID DESC limit 0,10000";

	$customersObjs = $wpdb->get_results($query);

	#} Clean them
	$ret = array('quotes'=>array(),'invoices'=>array(),'transactions'=>array());
	if (count($customersObjs) > 0) foreach ($customersObjs as $co){

		switch ($co->post_type){

			case 'zerobs_quote':

				#} add
				$retObj = array(

					'id' => $co->ID,
					'created' => $co->post_date_gmt,
					'meta' => unserialize($co->meta),
					'customerid' => $customerID,
					
					// wh added to bring in line with other getquotes funcs
					#} #TRANSITIONTOMETANO
					'zbsid' => get_post_meta($co->ID, 'zbsid', true)

				);

				// DAL2->3 Compatability
				// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
				// ... once migrated to DAL3 fully, this will no longer happen.
				$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
				// / DAL2->3 Compatability

				$ret['quotes'][] = $retObj;

				break;

			case 'zerobs_invoice':

				#} add
				$retObj = array(

					'id' => $co->ID,
					'created' => $co->post_date_gmt,
					'meta' => unserialize($co->meta),
					'customerid' => $customerID,
					
					// wh added to bring in line with other getquotes funcs
					#} #TRANSITIONTOMETANO
					'zbsid' => get_post_meta($co->ID, 'zbsid', true)

				);

				// DAL2->3 Compatability
				// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
				// ... once migrated to DAL3 fully, this will no longer happen.
				$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
				// / DAL2->3 Compatability

				$ret['invoices'][] = $retObj;

				break;

			case 'zerobs_transaction':

				#} add
				$retObj = array(

					'id' => $co->ID,
					'created' => $co->post_date_gmt,
					'meta' => unserialize($co->transmeta), #} KEY HERE is diff meta arr
					'customerid' => $customerID,
					
					// wh added to bring in line with other getquotes funcs
					#} #TRANSITIONTOMETANO
					'zbsid' => get_post_meta($co->ID, 'zbsid', true)

				);

				// DAL2->3 Compatability
				// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
				// ... once migrated to DAL3 fully, this will no longer happen.
				$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
				// / DAL2->3 Compatability

				$ret['transactions'][] = $retObj;

				break;


		}

	}


	return $ret;
}

/*function zeroBS_saveInvoiceToCustomer($invID=-1,$custID=-1){

	if ($invID > 0 && $custID > 0){

		#} Load customer
		$cust = zeroBS_getCustomer($custID);

		#} Check meta
		if (isset($cust['meta']) && is_array($cust['meta'])){

			#} modify meta
			$existingCustInvs = $cust['meta']['invoices'];

			if (!in_array($invID,$existingCustInvs)) {

				$existingCustInvs[] = $invID;

				$cust['meta']['invoices'] = $existingCustInvs;

	            #} UPDATE!
	            update_post_meta($custID, 'zbs_customer_meta', $cust['meta']);

	        }

	        return true;


		} else {

			wp_die('ERROR SAVING CUSTOMER INVOICE LINK!?');

		}


	}

	return false;
}*/


// for now, wrapper for past! - moved this to zeroBS_buildContactMeta
function zeroBS_buildCustomerMeta($arraySource=array(),$startingArray=array(),$fieldPrefix='zbsc_',$outputPrefix='',$removeEmpties=false){

	return zeroBS_buildContactMeta($arraySource,$startingArray,$fieldPrefix,$outputPrefix,$removeEmpties);

}


#} This takes an array source (can be $_POST) and builds out a meta field array for it..
#} This lets us use the same fields array for Metaboxes.php and any custom integrations
#} e.g. $zbsCustomerMeta = zeroBS_buildCustomerMeta($_POST);
#} e.g. $zbsCustomerMeta = zeroBS_buildCustomerMeta($importedMetaFields);
#} e.g. $zbsCustomerMeta = zeroBS_buildCustomerMeta(array('zbsc_fname'=>'Woody'));
#} 27/09/16: Can now also pass starting array, which lets you "override" fields present in $arraySource, without loosing originals not passed
#} 12/04/18: Added prefix so as to be able to pass normal array e.g. fname (by passing empty fieldPrefix)
#} 13/03/19: Added $autoGenAutonumbers - if TRUE, empty/non-passed autonumber custom fields will assume fresh + autogen (useful for PORTAL/SYNC generated)
function zeroBS_buildContactMeta($arraySource=array(),$startingArray=array(),$fieldPrefix='zbsc_',$outputPrefix='',$removeEmpties=false,$autoGenAutonumbers=false){

	// debug  print_r($arraySource); exit();

	#} def
	$zbsCustomerMeta = array();

	#} if passed...
	if (isset($startingArray) && is_array($startingArray)) $zbsCustomerMeta = $startingArray;

	// debug print_r($zbsCustomerMeta); exit();

	#} go
        global $zbsCustomerFields,$zbs;

        $i=0;
		
		// debug print_r($zbsCustomerFields); exit();

        foreach ($zbsCustomerFields as $fK => $fV){

        	$i++;

        	// if it's not an autonumber (which generates new on blank passes), set it to empty
        	// ... or if it has $autoGenAutonumbers = true, 
            if (
            	($fV[0] !== 'autonumber' && !isset($zbsCustomerMeta[$outputPrefix.$fK]))
            	||
            	$autoGenAutonumbers
            	)
            	$zbsCustomerMeta[$outputPrefix.$fK] = '';

            // two EXCEPTIONS:
            	// 1) custom field type checkbox, because it adds -0 -1 etc. to options, so this wont fire, 
            	// 2) Autonumbers which are blank to start with get caught beneath
            // ... see below for checkbox catch            
            if (isset($arraySource[$fieldPrefix.$fK])) {

                switch ($fV[0]){


                    case 'tel':

                        // validate tel?
                        $zbsCustomerMeta[$outputPrefix.$fK] = sanitize_text_field($arraySource[$fieldPrefix.$fK]);
                        preg_replace("/[^0-9 ]/", '', $zbsCustomerMeta[$outputPrefix.$fK]);
                        break;

                    case 'price':
                    case 'numberfloat':

                        $zbsCustomerMeta[$outputPrefix.$fK] = sanitize_text_field($arraySource[$fieldPrefix.$fK]);
                        $zbsCustomerMeta[$outputPrefix.$fK] = preg_replace('@[^0-9\.]+@i', '-', $zbsCustomerMeta[$outputPrefix.$fK]);
                        $zbsCustomerMeta[$outputPrefix.$fK] = floatval($zbsCustomerMeta[$outputPrefix.$fK]);
                        break;

                    case 'numberint':

                        $zbsCustomerMeta[$outputPrefix.$fK] = sanitize_text_field($arraySource[$fieldPrefix.$fK]);
                        $zbsCustomerMeta[$outputPrefix.$fK] = preg_replace('@[^0-9]+@i', '-', $zbsCustomerMeta[$outputPrefix.$fK]);
                        $zbsCustomerMeta[$outputPrefix.$fK] = floatval($zbsCustomerMeta[$outputPrefix.$fK]);
                        break;


                    case 'textarea':

                        $zbsCustomerMeta[$outputPrefix.$fK] = zeroBSCRM_textProcess($arraySource[$fieldPrefix.$fK]);

                        break;

                    case 'date':

                        $zbsCustomerMeta[$outputPrefix.$fK] = sanitize_text_field($arraySource[$fieldPrefix.$fK]);

                        break;

                    case 'radio':
                    case 'select':

                    	// just get value, easy.
                        $zbsCustomerMeta[$outputPrefix.$fK] = sanitize_text_field($arraySource[$fieldPrefix.$fK]);

                        break;

                    // autonumber dealt with below this if {}
                    case 'autonumber':

                    	// pass it along :)
                        $zbsCustomerMeta[$outputPrefix.$fK] = sanitize_text_field($arraySource[$fieldPrefix.$fK]);

                        break;

                    // checkbox dealt with below this if {}

                    default:

                        $zbsCustomerMeta[$outputPrefix.$fK] = sanitize_text_field($arraySource[$fieldPrefix.$fK]);

                        break;


                } // / switch type


            } // / if isset (simple) $arraySource[$fieldPrefix.$fK]

            // catch checkboxes
            if ($fV[0] == 'checkbox'){

	            // currently just assumes max 64 options (mental if more?)
	            // ... later use custom field to actually check amount
				$checkboxArr = array(); // if any                    	
	            for ($checkboxI = 0; $checkboxI < 64; $checkboxI++){

	            	if (isset($arraySource[$fieldPrefix.$fK.'-'.$checkboxI])) {

	            		// retrieve
	            		$checkboxArr[] = sanitize_text_field($arraySource[$fieldPrefix.$fK.'-'.$checkboxI]);

	            	}

	            } // / foreach checkbox item

            	if (count($checkboxArr)){

	            	// csv em
	                $zbsCustomerMeta[$outputPrefix.$fK] = implode(',',$checkboxArr);

	            } else {

	            	// none selected, set blank
                    $zbsCustomerMeta[$outputPrefix.$fK] = '';

	            }

	        } // / if checkbox

	        // if autonumber
            if ($fV[0] == 'autonumber'){

                // this is a generated field.
            	// if was previously set, sticks with that, if not set, will generate new, based on custom field rule
            	// NOTE!!!! if this is NOT SET in customerMeta, it WILL NOT be updated
            	// ... this is because when passing incomplete update records (e.g. not passing autonumber)
            	// ... it doesn't need a new AUTONUMBER
            	// ... so if you want a fresh autonumber, you need to pass with $startingArray[] EMPTY value set
            	
            	// Debug echo '<pre>'.print_r($zbsCustomerMeta,1).'</pre>'; exit();

            	// if not yet set
            	if (isset($zbsCustomerMeta[$outputPrefix.$fK]) && empty($zbsCustomerMeta[$outputPrefix.$fK])){

            		// retrieve based on custom field rule
            		$autono = '';

            			// retrieve rule
            			$formatExample = ''; if (isset($fV[2])) $formatExample = $fV[2];
            			if (!empty($formatExample) && strpos($formatExample, '#') > 0){

            				// has a rule at least
            				$formatParts = explode('#', $formatExample);

            				// build                    				

            					// prefix
            					if (!empty($formatParts[0])) $autono .= zeroBSCRM_customFields_parseAutoNumberStr($formatParts[0]);

            					// number
            					$no = zeroBSCRM_customFields_getAutoNumber(ZBS_TYPE_CONTACT,$fK);
            					if ($no > 0 && $no !== false) $autono .= $no;                    			
            				
            					// suffix
            					if (!empty($formatParts[2])) $autono .= zeroBSCRM_customFields_parseAutoNumberStr($formatParts[2]);

            				// if legit, add
                			if ($no > 0 && $no !== false) $zbsCustomerMeta[$outputPrefix.$fK] = $autono;


            			}
            	}

	        } // / if autonumber



        } // / foreach field

        // if DAL2, second addresses get passed differently? ¯\_(ツ)_/¯
        if ($zbs->isDAL2()){

        	$replaceMap = array(
					'secaddr1' => 'secaddr_addr1',
					'secaddr2' => 'secaddr_addr2',
					'seccity' => 'secaddr_city',
					'seccounty' => 'secaddr_county',
					'seccountry' => 'secaddr_country',
					'secpostcode' => 'secaddr_postcode'
					);

        	foreach ($replaceMap as $d2key => $d1key)
	        if (isset($zbsCustomerMeta[$outputPrefix.$d1key])){
	        	$zbsCustomerMeta[$outputPrefix.$d2key] = $zbsCustomerMeta[$outputPrefix.$d1key];
	        	unset($zbsCustomerMeta[$outputPrefix.$d1key]);
	        }

		}

        // can also pass some extras :) /social
        $extras = array('tw','fb','li');
        foreach ($extras as $fK){

            if (!isset($zbsCustomerMeta[$outputPrefix.$fK])) $zbsCustomerMeta[$outputPrefix.$fK] = '';

            if (isset($arraySource[$fieldPrefix.$fK])) {

                $zbsCustomerMeta[$outputPrefix.$fK] = sanitize_text_field($arraySource[$fieldPrefix.$fK]);

            }

        }

        // $removeEmpties
        if ($removeEmpties){

        	$ret = array();
        	foreach ($zbsCustomerMeta as $k => $v){
				
				$intV = (int)$v;

				if (!is_array($v) && !empty($v) && $v != '' && $v !== 0 && $v !== -1 && $intV !== -1){
					$ret[$k] = $v;
				}

        	}

        	$zbsCustomerMeta = $ret;

        }

     //echo '<pre>'.print_r($arraySource,1).'</pre>';
     //echo '<pre>'.print_r($zbsCustomerMeta,1).'</pre>';

    return $zbsCustomerMeta;
}


/* ======================================================
  / DAL Functions
   ====================================================== */


/* ======================================================
	Save [obj] overrides
   ====================================================== */




#} WH V1.1.10 - same as for customer name, but with companies
function zbsCustomer_saveCompanyPostdata($post_id) {

  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  #print($_POST['save_zbscustomer_nce']);
  if (!isset($_POST['save_zbscompany_nce'])) return;
  if (!wp_verify_nonce($_POST['save_zbscompany_nce'], 'save_zbscompany')) return;
  if ('page' == $_POST['post_type']) {
    if (!current_user_can('edit_page', $post_id)) return;
  } else {
    if (!current_user_can('edit_post', $post_id)) return;
  }

  #} Only for this post-type (assured by verify nonce :))

  		#} Got meta?
  		global $zbsCurrentCompanyPostMeta; if (isset($zbsCurrentCompanyPostMeta)) 
  			$zbsMeta = $zbsCurrentCompanyPostMeta;
  		else
  			#} Boo....
  			$zbsMeta = get_post_meta($post_id, 'zbs_company_meta', true);

    // Fire auto-update
    zbsCustomer_updateCompanyNameInPostTitle($post_id,$zbsMeta);


}
add_action('save_post', 'zbsCustomer_saveCompanyPostdata',200); // change priority here, change below x2

#} This is used to build a custom post title for customers, it stops any save_post and acts then re-applies 
global $zbsCRMCompanyUpdatingArr; $zbsCRMCompanyUpdatingArr = array();
function zbsCustomer_updateCompanyNameInPostTitle($post_id=-1,$zbsMetaPassThrough=false){

	#} WH fix for clashing
	global $zbsCRMCompanyUpdatingArr;

	if ($post_id !== -1 && !isset($zbsCRMCompanyUpdatingArr[$post_id])){

		#} Set blocker
		if (!is_array($zbsCRMCompanyUpdatingArr))
			$zbsCRMCompanyUpdatingArr = array($post_id);
		else
			$zbsCRMCompanyUpdatingArr[] = $post_id;

		#} Got meta?
		if (isset($zbsMetaPassThrough) && $zbsMetaPassThrough !== false) 
			$zbsMeta = $zbsMetaPassThrough;
		else
			#} Boo....
			$zbsMeta = get_post_meta($post_id, 'zbs_company_meta', true);

		#} Check if action is gonna be an issue
		$actionInPlace = has_action('save_post', 'zbsCustomer_saveCompanyPostdata');
		if ($actionInPlace > 0) $actionInPlace = true; # will now be bool 

	    //If calling wp_update_post, unhook this function so it doesn't loop infinitely
	    if ($actionInPlace) remove_action('save_post', 'zbsCustomer_saveCompanyPostdata',200);

	        #} Update the post name to be customer fname + lname
	    	#} NAMECHANGES
	        $newCName = zeroBS_companyName('',$zbsMeta,true,false);
	        wp_update_post(array(
	            'ID' => $post_id,
	            'post_title' => $newCName
	        ));

	        #} Add zbs_company_nameperm meta (req. for importer) zeroBS_getCompanyIDWithName etc.
	        #} This sets this meta to the exact name, e.g. "Dell"
	        $simpleCName = zeroBS_companyName('',$zbsMeta,false,false);
	        update_post_meta($post_id,'zbs_company_nameperm',$simpleCName);

            #} Is new customer? (passed from metabox html)
            #} Internal Automator
            if (isset($_POST['zbscrm_newcompany']) && $_POST['zbscrm_newcompany'] == 1){

                zeroBSCRM_FireInternalAutomator('company.new',array(
                    'id'=>$post_id,
                    'companyMeta'=>$zbsMeta
                    ));
                
            }


	    // re-hook this function
	    if ($actionInPlace) add_action('save_post', 'zbsCustomer_saveCompanyPostdata',200);

	    #} clear blocker
	    unset($zbsCRMCompanyUpdatingArr[$post_id]);

	    return $newCName;

	} return false;

}


#} WH V1.1 - compiles name into post_title when post saved :)
#} LOL: http://wordpress.stackexchange.com/questions/51363/how-to-avoid-infinite-loop-in-save-post-callback
function zbsCustomer_saveTransactionPostdata($post_id) {

  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  #print($_POST['save_zbscustomer_nce']);
  #SHOULD HAVE NONCES! MIKE! if (!isset($_POST['save_zbscustomer_nce'])) return;
  #if (!wp_verify_nonce($_POST['save_zbscustomer_nce'], 'save_zbscustomer')) return;
  if (isset($_POST['post_type']) && $_POST['post_type'] == 'page') {
    if (!current_user_can('edit_page', $post_id)) return;
  } else {
    if (!current_user_can('edit_post', $post_id)) return;
  }

  #} Only for this post-type (assured by verify nonce :))
  #} ... it's not by mikes! :/ quick hack....



		if (isset($_POST['zbs_hidden_flag'])) {


			$newDate = ''; if (isset($_POST['transactionDate']) && !empty($_POST['transactionDate'])) $newDate = sanitize_text_field($_POST['transactionDate']);
		
	
			//this won't work with new date formatting.. :/ woes.


			$unixtime = zeroBSCRM_locale_dateToUTS($newDate);
			//format it this way. Saves doing the block below

			$postDateStr = date('Y-m-d H:i:s', $unixtime);

			if (!empty($newDate) && $unixtime > 0){ //  && strlen($newDate) == 10 // e.g. 09.11.2016

				#} gross, but quick parse
				//$year = substr($newDate,6);
				//$month = substr($newDate,3,2);
				//$days = substr($newDate,0,2);
				#} make str // uses 'Y-m-d H:i:s' format
		//		$postDateStr = $year.'-'.$month.'-'.$days.' 00:09:00';

				#} ... though this isn't ideal, need to rethink inc time.

				#} This needs wrapping here to avoid infinite loops


				zbsTransaction_updatePostDate($post_id,$postDateStr);

			}

		

	}

}
add_action('save_post', 'zbsCustomer_saveTransactionPostdata', 1);
#} updates post_date to match trans date :)
function zbsTransaction_updatePostDate($post_id=-1,$postDateStr=false){

	if ($post_id !== -1){

		#} Check if action is gonna be an issue
		$actionInPlace = has_action('save_post', 'zbsCustomer_saveTransactionPostdata');
		if ($actionInPlace > 0) $actionInPlace = true; # will now be bool 

	    //If calling wp_update_post, unhook this function so it doesn't loop infinitely
	    if ($actionInPlace){
			remove_action('save_post', 'zbsCustomer_saveTransactionPostdata', 1);
			remove_action('save_post', 'wpt_save_zbs_transaction_meta', 25, 2 );
		}
			#} Simple update
			wp_update_post(array('ID'=>$post_id,'post_date'=>$postDateStr, 'post_date_gmt'=>$postDateStr));

	    // re-hook this function
	    if ($actionInPlace){
			add_action('save_post', 'zbsCustomer_saveTransactionPostdata', 1);
			add_action('save_post', 'wpt_save_zbs_transaction_meta', 25, 2 );

		}

	    return $post_id;

	} return false;

}

#} WH To fix links :/ 1.2.5
function zbsQuote_saveQuotePostdata($post_id) {

  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  #print($_POST['save_zbscustomer_nce']);
  #SHOULD HAVE NONCES! MIKE! if (!isset($_POST['save_zbscustomer_nce'])) return;
  #if (!wp_verify_nonce($_POST['save_zbscustomer_nce'], 'save_zbscustomer')) return;
  if (isset($_POST['post_type']) && $_POST['post_type'] == 'page') {
    if (!current_user_can('edit_page', $post_id)) return;
  } else {
    if (!current_user_can('edit_post', $post_id)) return;
  }

  #} Only for this post-type (assured by verify nonce :))
	if (isset($_POST['quo-ajax-nonce'])) {

	  	#} This needs wrapping here to avoid infinite loops
		zbsQuote_updatePostDate($post_id);		

	}

}
add_action('save_post', 'zbsQuote_saveQuotePostdata');
#} updates post_date to match trans date :)
function zbsQuote_updatePostDate($post_id=-1){

	if ($post_id !== -1){

		#} Check if action is gonna be an issue
		$actionInPlace = has_action('save_post', 'zbsQuote_saveQuotePostdata');
		if ($actionInPlace > 0) $actionInPlace = true; # will now be bool 

	    //If calling wp_update_post, unhook this function so it doesn't loop infinitely
	    if ($actionInPlace) remove_action('save_post', 'zbsQuote_saveQuotePostdata');

			#} Simple update - name to id, title override.
			wp_update_post(array('ID'=>$post_id,'post_name'=>$post_id,'post_title'=>'Proposal'));

			#} and generate a hash
			update_post_meta($post_id,'zbshash',zeroBSCRM_GenerateHashForPost($post_id,12));

	    // re-hook this function
	    if ($actionInPlace) add_action('save_post', 'zbsQuote_saveQuotePostdata');

	    return $post_id;

	} return false;

}

/* ======================================================
	Save [obj] overrides
   ====================================================== */




/* ======================================================
	Backend tools
   ====================================================== */

# brutal removal of db autodrafts for our post types.
# answer way down on page here http://stackoverflow.com/questions/10234271/wordpress-auto-draft-disabling
function zeroBSCRM_clearCPTAutoDrafts(){

	global $wpdb, $zbsCustomPostTypes;

	#} use our CPT's as list
	foreach ($zbsCustomPostTypes as $cpt){

		$del= $wpdb->query("DELETE FROM $wpdb->posts WHERE post_status = 'auto-draft' AND post_type = '".$cpt."'");
		$del= $wpdb->query("DELETE FROM $wpdb->posts WHERE post_type = 'revision'");				

	}

	#} set option (seen in system status page)
	update_option('zbscptautodraftclear',time(), false);

}



function zeroBS_getTransactionsForInvoice($invID=-1){
	global $wpdb;
	$ret = false;
	#} No empties, no validation, either.
	if (!empty($invID)){
		#} Will find the post, if exists, no dealing with dupes here, yet?
		$sql = $wpdb->prepare("select post_id from $wpdb->postmeta where meta_value = '%d' And meta_key='zbs_invoice_partials'", $invID);
		$potentialTransactionList = $wpdb->get_results($sql);
		if (count($potentialTransactionList) > 0){
			if (isset($potentialTransactionList[0]) && isset($potentialTransactionList[0]->post_id)){
				$ret = $potentialTransactionList[0]->post_id;
			}
		}		
	}
	return $ret;	
}

function zeroBSCRM_invoice_canView($cID=-1, $invID=-1){
	if (!empty($cID) && !empty($invID)){
		$accessID = get_post_meta($invID, 'zbs_customer_invoice_customer', true);
		return $accessID;
	}

}


function zeroBSCRM_quote_canView($cID=-1, $invID=-1){
	if (!empty($cID) && !empty($invID)){
		$accessID = get_post_meta($invID, 'zbs_customer_quote_customer', true);
		return $accessID;
	}

}

// wrapper: (3.0+ switched zeroBSCRM_invoice_canView to zeroBSCRM_invoice_getContactAssigned)
// this effectively does same
function zeroBSCRM_invoice_getContactAssigned($invID=-1){
	return get_post_meta($invID, 'zbs_customer_invoice_customer', true);	
}

// wrapper: (3.0+ switched zeroBSCRM_quote_canView to zeroBSCRM_quote_getContactAssigned)
// this effectively does same
function zeroBSCRM_quote_getContactAssigned($invID=-1){
	return get_post_meta($invID, 'zbs_customer_quote_customer', true);	
}
	


/* ======================================================
	/ Backend tools
   ====================================================== */


	


/* ======================================================
	API 
   ====================================================== */

#} generates, or regenerates a key :)
function zeroBSCRM_regenerateAPIKey(){
	$x = zeroBSCRM_API_generate_api_key();
	zeroBSCRM_storeAPIKey($x);
	return $x;
}

#} save key
function zeroBSCRM_storeAPIKey($api_key=''){
	update_option('zbs_crm_api_key', $api_key, false);
}


//allow updating the API to be read / write... allow READ ONLY for now..
function zeroBSCRM_updateAPIKey($api_key=''){
	update_option('zbs_crm_api_key', $api_key, false);
}

//each CRM is  only given one API (for now) 
function zeroBSCRM_getAPIKey(){
    $api_key = get_option('zbs_crm_api_key');
    return $api_key;
}

function zeroBSCRM_getAPIKeys(){
    $api_key = get_option('zbs_crm_api_key');
    return $api_key;
}

#} generates, or regenerates a secret :)
function zeroBSCRM_regenerateAPISecret(){

	#} uses same func as key for now..
	$x = zeroBSCRM_API_generate_api_key();
	zeroBSCRM_storeAPISecret($x);
	return $x;
}

#} save api_secret
function zeroBSCRM_storeAPISecret($api_secret=''){
	update_option('zbs_crm_api_secret', $api_secret, false);
}

//each CRM is  only given one API (for now) 
function zeroBSCRM_getAPISecret(){
    $api_secret = get_option('zbs_crm_api_secret');
    return $api_secret;
}



/* ======================================================
	/ API 
   ====================================================== */





/* ======================================================
	Ownership
   ====================================================== */

// Note: if $allowNoOwnerAccess = true, if this has NO owner, it'll return true 
function zeroBS_checkOwner($postID=-1,$potentialOwnerID=-1,$allowNoOwnerAccess=true){

	if ($postID !== -1){

		$potentialOwner = zeroBS_getOwner($postID);

		if (isset($potentialOwner['ID']) && $potentialOwner['ID'] == $potentialOwnerID) 
			return true;
		// no owner owns this!
		else if ($allowNoOwnerAccess && (!isset($potentialOwner['ID']) || $potentialOwner['ID'] == -1))
			return true;

	} 

	return false;
}



function zeroBS_getCompanyOwner($companyID=-1){

	if ($companyID !== -1){

		return zeroBS_getOwner($companyID);

	} 

	return false;
}




/* ======================================================
	/ Ownership 
   ====================================================== */


/* =====================================================
	/Status list stuff (for API and internal) - copied from customer variant above
   ====================================================== */


	function zeroBS_getTransactionsStatuses(){
	    
	    global $zbs;

	    $settings = $zbs->settings->getAll();

	    $zbsStatusStr = ''; 
	    #} stored here: $settings['customisedfields']
	    if (isset($settings['customisedfields']['transactions']['status']) && is_array($settings['customisedfields']['transactions']['status'])) $zbsStatusStr = $settings['customisedfields']['transactions']['status'][1];                                        
	    if (empty($zbsStatusStr)) {
	      #} Defaults:
	      global $zbsTransactionFields; if (is_array($zbsTransactionFields)) $zbsStatusStr = implode(',',$zbsTransactionFields['status'][3]);
	    }  

	    return $zbsStatusStr;
	}



/* ======================================================
	DB v2.0 Data Access
   ====================================================== */

   /* 
		zeroBSCRM_getSegments notes:

			Pass -1 for $perPage and $page and this'll return ALL


   */


	#} WHLOOK - when we get new DB our edit links will change from
	#} /wp-admin/post.php?post=4526&action=edit to something else
	/* replaced by zbsLink
	function zeroBSCRM_getEditLink($id = -1){
		if($id > 0){
			$editLink = admin_url('post.php?post='.$id.'&action=edit');
		}else{
			$editLink = "#";
		}
		return $editLink;
	} */


	// DELETES ALL rows from any table, based on ID
	// no limits! be careful.
	function zeroBSCRM_db2_deleteGeneric($id=-1,$tableKey=''){

		// globals
		global $ZBSCRM_t,$wpdb;

		if (!empty($id) && !empty($tableKey) && array_key_exists($tableKey, $ZBSCRM_t)){

	   		return $wpdb->delete( 
						$ZBSCRM_t[$tableKey], 
						array( // where
							'ID' => $id
							),
						array(
							'%d'
							)
						);

	   	}

	   	return false;
	}



   

/* ======================================================
	/ DB v2.0 Data Access
   ====================================================== */

/* ======================================================
	To be sorted helpers 
   ====================================================== */

   function zeroBSCRM_setCompanyTags($cID=-1,$tags=array()){

   		return wp_set_object_terms( $cID, $tags, 'zerobscrm_companytag', false );
   		
   }

   function zeroBSCRM_getCompanyTagsByID($cID=-1,$justIDs=false){

   			// https://codex.wordpress.org/Function_Reference/wp_get_object_terms#Argument_Options
			$args = array(
				'order' => 'ASC',
				'orderby' => 'name'
			);
			if ($justIDs) $args['fields'] = 'ids';
			return wp_get_object_terms($cID,'zerobscrm_companytag',$args);


   }


   function zeroBSCRM_getTransactionTagsByID($cID=-1,$justIDs=false){

   			// https://codex.wordpress.org/Function_Reference/wp_get_object_terms#Argument_Options
			$args = array(
				'order' => 'ASC',
				'orderby' => 'name'
			);
			if ($justIDs) $args['fields'] = 'ids';
			return wp_get_object_terms($cID,'zerobscrm_transactiontag',$args);


   }

   	// this has a js equivilent in global.js: zeroBSCRMJS_telURLFromNo
    function zeroBSCRM_clickToCallPrefix(){

        $click2CallType = zeroBSCRM_getSetting('clicktocalltype');

        if ($click2CallType == 1) return 'tel:';
        if ($click2CallType == 2) return 'callto:';

    }

	function zeroBSCRM_getCustomerStatuses($asArray=false){
	    
	    global $zbs;

	    $settings = $zbs->settings->getAll();

	    $zbsStatusStr = ''; 
	    #} stored here: $settings['customisedfields']
	    if (isset($settings['customisedfields']['customers']['status']) && is_array($settings['customisedfields']['customers']['status'])) $zbsStatusStr = $settings['customisedfields']['customers']['status'][1];                                        
	    if (empty($zbsStatusStr)) {
	      #} Defaults:
	      global $zbsCustomerFields; if (is_array($zbsCustomerFields)) $zbsStatusStr = implode(',',$zbsCustomerFields['status'][3]);
	    }  

	    if ($asArray){

	    	if (strpos('#'.$zbsStatusStr, ',') > -1){

	    		$arr = explode(',',$zbsStatusStr);
	    		$ret = array();
	    		foreach ($arr as $x) { $z = trim($x); if (!empty($z)) $ret[] = $z; }

	    		return $ret;

	    	}

	    }

	    return $zbsStatusStr;
	}

	function zeroBSCRM_getTransactionsStatuses($returnArray=false){
	    
	    global $zbs;

	    $settings = $zbs->settings->getAll();

	    $zbsStatusStr = ''; 
	    #} stored here: 
	    if (isset($settings['customisedfields']['transactions']['status']) && is_array($settings['customisedfields']['transactions']['status'])) $zbsStatusStr = $settings['customisedfields']['transactions']['status'][1];                                        
	    if (empty($zbsStatusStr)) {
	      #} Defaults:
	      global $zbsTransactionFields; if (is_array($zbsTransactionFields) && isset($zbsTransactionFields['status'])) $zbsStatusStr = implode(',',$zbsTransactionFields['status'][3]);
	    }

	    if ($returnArray){

	    	if (strpos($zbsStatusStr,',') > -1) 
	    		return explode(',', $zbsStatusStr);
	    	else
	    		return array();
	    }

	    return $zbsStatusStr;
	}


	function zeroBSCRM_getCompanyStatusesCSV(){
	    
	    global $zeroBSCRM_Settings;

	    $settings = $zeroBSCRM_Settings->getAll();

	    $zbsStatusStr = ''; 
	    #} stored here: $settings['customisedfields']
	    if (isset($settings['customisedfields']['companies']['status']) && is_array($settings['customisedfields']['companies']['status'])) $zbsStatusStr = $settings['customisedfields']['companies']['status'][1];                                        
	    if (empty($zbsStatusStr)) {
	      #} Defaults:
	      global $zbsCompanyFields; if (is_array($zbsCompanyFields)) $zbsStatusStr = implode(',',$zbsCompanyFields['status'][3]);
	    }  

	    return $zbsStatusStr;
	}

	function zeroBSCRM_getCompanyStatuses(){
	    
	    $statusesSTR = zeroBSCRM_getCompanyStatusesCSV();
	    $ret = array();

	    if (strpos($statusesSTR, ',') > -1) {
	    	$statuses = explode(',', $statusesSTR);
	    	foreach ($statuses as $s) if (trim($s) == false) $ret[] = $s;
	    }

	    return $ret;
	}


   // moves a quote from being assigned to one cust, to another
   function zeroBSCRM_changeQuoteCustomer($qID=-1,$newCustomerID=0){

   		if (!empty($qID) && !empty($newCustomerID)){

            return update_post_meta($qID, 'zbs_customer_quote_customer', $newCustomerID); 

        }

        return false;

    }
   // moves an invoice from being assigned to one cust, to another
   function zeroBSCRM_changeInvoiceCustomer($invWPID=-1,$newCustomerID=0){


   		if (!empty($invWPID) && !empty($newCustomerID)){

            return update_post_meta($invWPID, 'zbs_customer_invoice_customer', $newCustomerID); 

        }

        return false;

    }
   // grabs invoice customer
   function zeroBSCRM_getInvoiceCustomer($invWPID=-1){


   		if (!empty($invWPID)){

			return get_post_meta($invWPID,'zbs_customer_invoice_customer',true);

        }

        return false;

    }
   // moves a tranasction from being assigned to one cust, to another
   function zeroBSCRM_changeTransactionCustomer($transWPID=-1,$newCustomerID=0){

   		if (!empty($transWPID) && !empty($newCustomerID)){

            return update_post_meta($transWPID, 'zbs_parent_cust', $newCustomerID); 

        }

        return false;

    }
   // moves a tranasction from being assigned to one company, to another
   function zeroBSCRM_changeTransactionCompany($transWPID=-1,$newCompanyID=0){

   		if (!empty($transWPID) && !empty($newCompanyID)){

            return update_post_meta($transWPID, 'zbs_parent_co', $newCompanyID); 

        }

        return false;

    }
   // moves an Event from being assigned to one cust, to another
   function zeroBSCRM_changeEventCustomer($eventWPID=-1,$newCustomerID=0){

   		if (!empty($eventWPID) && !empty($newCustomerID)){

   			// get event meta
   			$eventMeta = get_post_meta($eventWPID, 'zbs_event_meta', true);
   			if (is_array($eventMeta)){

   				// switch this key only
   				$eventMeta['customer'] = $newCustomerID;

   				return update_post_meta($eventWPID, 'zbs_event_meta', $eventMeta); 

   			}

        }

        return false;

    }



    // this was missing pre v3.0, so this is added for migration assistance
	function zeroBS_getEvent($eventID=-1,$withFullDetails=false){

		if ($eventID !== -1){

			$eventPost = get_post($eventID,ARRAY_A);

			if (isset($eventPost) && is_array($eventPost)){
				
				$retObj = array(
					'id'=>$eventID,
					'created' => $eventPost['post_date_gmt'],
					'title' => $eventPost['post_title'],

					'meta'=>get_post_meta($eventID, 'zbs_event_meta', true),
					'actions'=>get_post_meta($eventID, 'zbs_event_actions', true)
				);

				// DAL2->3 Compatability
				// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
				// ... once migrated to DAL3 fully, this will no longer happen.
				$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
				// / DAL2->3 Compatability

				// lets also return the full customer Deets
				if (is_array($retObj['meta']) && isset($retObj['meta']['customer'])) $retObj['customer']    =  zeroBS_getCustomerMeta($retObj['meta']['customer']);
			
				return $retObj;

			}

		} 

		return false;

	}



	function zeroBS_getEvents($withFullDetails=false,$perPage=10,$page=0, $ownedByID=false){

			#} Page legit? - lazy check
			if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

			$args = array (
				'post_type'              => 'zerobs_event',
				'post_status'            => 'publish',
				'posts_per_page'         => $perPage,
				'order'                  => 'DESC',
				'orderby'                => 'post_date'
			);
			
			#} Add page if page... - dodgy meh
			$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
			if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;


			#} To filter by owner
			if (!empty($ownedByID)){

				if (!isset($args['meta_query'])) $args['meta_query'] = array();

				$args['meta_query'][] = array(
		           'key' => 'zbs_owner',
		           'value' => $ownedByID,
		           'compare' => '='
		        );

				
			}

			$list = get_posts( $args );

			$ret = array();

			foreach ($list as $ele){

				$retObj = array(

					'id' => 	$ele->ID,
					'created' => $ele->post_date_gmt,
					'title' => $ele->post_title

				);

				#} Full details?
				if ($withFullDetails) {

					// customer ID will be in meta as "customer"
					
					$retObj['meta'] 		= get_post_meta($ele->ID, 'zbs_event_meta', true);

					// DAL2->3 Compatability
					// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
					// ... once migrated to DAL3 fully, this will no longer happen.
					$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
					// / DAL2->3 Compatability

					$retObj['actions']		= get_post_meta($ele->ID, 'zbs_event_actions', true);

					// lets also return the full customer Deets
					if (is_array($retObj['meta']) && isset($retObj['meta']['customer'])) $retObj['customer']    =  zeroBS_getCustomerMeta($retObj['meta']['customer']);
				}

				$ret[] = $retObj;

			}

			return $ret;
	}
	
	function zeroBS_getEventsByCustomerID($cID=-1,$withFullDetails=false,$perPage=10,$page=0){

			#} Page legit? - lazy check
			if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

			$args = array (
				'post_type'              => 'zerobs_event',
				'post_status'            => 'publish',
				'posts_per_page'         => $perPage,
				'order'                  => 'DESC',
				'orderby'                => 'post_date',
					'meta_query' => array( #} Super hack... 
						array(
							'key' => 'zbs_event_meta',
							'value' => sprintf('"customer";i:%d;', $cID), 
							'compare' => 'LIKE'
						)
					)

			);
			
			#} Add page if page... - dodgy meh
			$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
			if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

			$list = get_posts( $args );

			$ret = array();

			foreach ($list as $ele){

				$retObj = array(

					'id' => 	$ele->ID,
					'created' => $ele->post_date_gmt

				);

				#} Full details?
				if ($withFullDetails) {

					// customer will be in meta as "customer"
					
					$retObj['meta'] 		= get_post_meta($ele->ID, 'zbs_event_meta', true);

					// DAL2->3 Compatability
					// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
					// ... once migrated to DAL3 fully, this will no longer happen.
					$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
					// / DAL2->3 Compatability

					$retObj['actions']		= get_post_meta($ele->ID, 'zbs_event_actions', true);
					// lets also return the full customer Deets
					if (is_array($retObj['meta']) && isset($retObj['meta']['customer'])) $retObj['customer']    =  zeroBS_getCustomerMeta($retObj['meta']['customer']);


				}

				$ret[] = $retObj;

			}

			return $ret;
	}

	// also: used in v2-3 migration, be careful changing here.
	function zeroBS_getEventsByCompanyID($coID=-1,$withFullDetails=false,$perPage=10,$page=0){

			#} Page legit? - lazy check
			if ($perPage < 0) $perPage = 10; else $perPage = (int)$perPage;

			$args = array (
				'post_type'              => 'zerobs_event',
				'post_status'            => 'publish',
				'posts_per_page'         => $perPage,
				'order'                  => 'DESC',
				'orderby'                => 'post_date',
					'meta_query' => array( #} Super hack... 
						array(
							'key' => 'zbs_event_meta',
							'value' => sprintf('"company";i:%d;', $coID), 
							'compare' => 'LIKE'
						)
					)

			);
			
			#} Add page if page... - dodgy meh
			$actualPage = $page-1; if ($actualPage < 0) $actualPage = 0;
			if ($actualPage > 0) $args['offset'] = $perPage*$actualPage;

			$list = get_posts( $args );

			$ret = array();

			foreach ($list as $ele){

				$retObj = array(

					'id' => 	$ele->ID,
					'created' => $ele->post_date_gmt

				);

				#} Full details?
				if ($withFullDetails) {

					// customer will be in meta as "customer"
					
					$retObj['meta'] 		= get_post_meta($ele->ID, 'zbs_event_meta', true);

					// DAL2->3 Compatability
					// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
					// ... once migrated to DAL3 fully, this will no longer happen.
					$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($retObj);
					// / DAL2->3 Compatability

					$retObj['actions']		= get_post_meta($ele->ID, 'zbs_event_actions', true);

				}

				$ret[] = $retObj;

			}

			return $ret;
	}




    // this'll let you find strings in serialised arrays
    // super dirty :)
    // wh wrote for log reporter miguel
    function zeroBSCRM_makeQueryMetaRegexReturnVal($fieldNameInSerial=''){

    	/* 

			https://regex101.com/

			e.g. from 
						a:3:{s:4:"type";s:4:"Note";s:9:"shortdesc";s:24:"Testing Notes on another";s:8:"longdesc";s:16:"Dude notes what ";}

			thes'll return:

    	 	works, tho returns full str:

    	 		/"'.$fieldNameInSerial.'";s:[0-9]*:"[a-zA-Z0-9_ ]+/


    	 	returns:
		
				`shortdesc";s:24:"Testing Notes on another`


    		this is clean(er):
    		
    			(?<=shortdesc";s:)[0-9]*:"[^"]*

    		returns: 

    			24:"Testing Notes on another

			

			.. could get even cleaner, for now settling here



			// WH WORKS:

				// 
				https://stackoverflow.com/questions/16926847/wildcard-for-single-digit-mysql
				a:3:{s:4:"type";s:4:"Note";s:9:"shortdesc";s:24:"Testing Notes on another";s:8:"longdesc";s:16:"Dude notes what ";}

				SELECT *
				FROM `wp_postmeta`
				WHERE post_id = 150 AND meta_value regexp binary '/shortdesc";s:[0-9]*:"/'
				LIMIT 50
				// https://regex101.com/

		*/

		$regexStr = '/(?<="'.$fieldNameInSerial.'";s:)[0-9]*:"[^"]*/';

    	if (!empty($fieldNameInSerial) && zeroBSCRM_isRegularExpression($regexStr)) return $regexStr;

    	return false;

    }


    // this'll let you CHECK FOR strings in serialised arrays
    // super dirty :)
    // wh wrote for log reporter miguel
    function zeroBSCRM_makeQueryMetaRegexCheck($fieldNameInSerial='',$posval=''){

    	$regexStr = '/(?<="'.$fieldNameInSerial.'";s:)[0-9]*:"[^"]*'.$posval.'[^"]*/';

    	if (!empty($fieldNameInSerial) && !empty($posval) && zeroBSCRM_isRegularExpression($regexStr)) return $regexStr;

    	return false;

    }

    // this'll let you CHECK FOR strings (multiple starting fieldnames) in serialised arrays
    // super dirty :)
    // wh wrote for log reporter miguel
    // e.g. is X in shortdesc or longdesc in serialised wp options obj
    function zeroBSCRM_makeQueryMetaRegexCheckMulti($fieldNameInSerialArr=array(),$posval=''){

    	// multi fieldnames :)
    	// e.g. (?:shortdesc";s:|longdesc";s:)[0-9]*:"[^"]*otes[^"]*
    	// e.g. str: a:3:{s:4:"type";s:4:"Note";s:9:"shortdedsc";s:24:"Testing Notes on another";s:8:"longdesc";s:16:"Dude notes what ";}

    	$fieldNameInSerialStr = ''; if (count($fieldNameInSerialArr) > 0){

	    	foreach ($fieldNameInSerialArr as $s){

	    		if (!empty($fieldNameInSerialStr)) $fieldNameInSerialStr .= '|';
	    		$fieldNameInSerialStr .= '"'.$s.'";s:';
	    	}

	   	}

    	// FOR THESE REASONS: https://stackoverflow.com/questions/18317183/1139-got-error-repetition-operator-operand-invalid-from-regexp
    	// .. cant use this:
    	//$regexStr = '/(?:'.$fieldNameInSerialStr.')[0-9]*:"[^"]*'.$posval.'[^"]*/';
    	// bt this works:
    	$regexStr = '/('.$fieldNameInSerialStr.')[0-9]*:"[^"]*'.$posval.'[^"]*/';

    	if (!empty($fieldNameInSerialStr) && !empty($posval) && zeroBSCRM_isRegularExpression($regexStr)) return $regexStr;

    	return false;

    }

    // test regex roughly 
    // https://stackoverflow.com/questions/8825025/test-if-a-regular-expression-is-a-valid-one-in-php
    /*function zeroBSCRM_checkRegexWorks($pattern,$subject=''){
		if (@preg_match($pattern, $subject) !== false) return true;

		return false;
	} */
	function zeroBSCRM_isRegularExpression($string) {
	  set_error_handler(function() {}, E_WARNING);
	  $isRegularExpression = preg_match($string, "") !== FALSE;
	  restore_error_handler();
	  return $isRegularExpression;
	}

	function zeroBS_getCurrentUserUsername(){

		// https://codex.wordpress.org/Function_Reference/wp_get_current_user

	    $current_user = wp_get_current_user();
	    if ( !($current_user instanceof WP_User) ) return;
	    return $current_user->user_login;
	}


	function zeroBS_updateInvoiceStatus($wpPostID=-1,$statusStr='Draft'){

		if ( in_array( $statusStr, zeroBSCRM_getInvoicesStatuses() ) ){

			$potentialInvoice = zeroBS_getInvoice($wpPostID);
			if (isset($potentialInvoice) && is_array($potentialInvoice)){
				if (isset($potentialInvoice['meta']) && is_array($potentialInvoice['meta'])){

					// has inv + meta
					$newMeta = $potentialInvoice['meta'];
					$newMeta['status'] = $statusStr;

					// update meta
					update_post_meta($wpPostID,'zbs_customer_invoice_meta', $newMeta);

				}

			}

		}

		return false;

	}


function zeroBS_getCompanyIDWithEmail($custEmail=''){

	$ret = false;

	#} No empties, no validation, either.
	if (!empty($custEmail)){

		#} Will find the post, if exists, no dealing with dupes here, yet?
		$args = array (
			'post_type'              => 'zerobs_company',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'order'                  => 'DESC',
			'orderby'                => 'post_date',
			'meta_query' => array( #} Super hack... if metavar CONTAINS this email str :/
				#} LOL @ Hack, from here: http://wordpress.stackexchange.com/a/184407/20183
				array(
					'key' => 'zbs_company_meta',
					'value' => sprintf(':"%s";', $custEmail), 
					'compare' => 'LIKE'
				)
			)

		);

		$potentialCompanyList = get_posts( $args );

		if (count($potentialCompanyList) > 0){

			if (isset($potentialCompanyList[0]) && isset($potentialCompanyList[0]->ID)){

				$ret = $potentialCompanyList[0]->ID;

			}

		}

	}


	return $ret;


}


function zeroBSCRM_getTaskList($cID=-1){
	$ret = array();
	if ($cID > 0){
                $args = array (
                    'post_type'              => 'zerobs_event',
                    'post_status'            => 'publish',
                    'posts_per_page'         => -1,
                    'order'                  => 'DESC',
                    'orderby'                => 'post_date'
                );

                $q = '"customer";i:'. (int)$cID  .';';

                $extraMetaQueries[] = array(
                   'key' => 'zbs_event_meta',
                   //'value' => '%'.$q.'%',
                   'value' => $q,
                   'compare' => 'LIKE'
                );



                 $args['meta_query'] = $extraMetaQueries;


				$tasks = get_posts( $args );
				$i = 0;
				foreach($tasks as $task){
					$ret[$i]['title'] = $task->post_title;
					$ret[$i]['ID'] = $task->ID;
					$ret[$i]['meta'] = get_post_meta($task->ID,'zbs_event_meta',true);

					// DAL2->3 Compatability
					// to provide forward compatability we make sure $retObj has data in $retObj, and $retObj['meta']
					// ... once migrated to DAL3 fully, this will no longer happen.
					$retObj = zeroBSCRM_DAL2to3CompatTranslateMeta($ret[$i]);
					// / DAL2->3 Compatability
					$ret[$i] = $retObj;

					$ret[$i]['actions'] = get_post_meta($task->ID,'zbs_event_actions',true);

					// titles moved into meta with MS new task ui, wh bringing them out here:
					if (empty($task->post_title) && is_array($ret[$i]['meta']) && isset($ret[$i]['meta']['title']) && !empty($ret[$i]['meta']['title'])){
						$ret[$i]['title'] = $ret[$i]['meta']['title'];
					}

					$i++;
				}

				return $ret;
    }

    return array();
}


	// ===============================================================================
	// ===========   TEMPHASH ========================================================

	
	 /**
	 * checks validity of a temporary hash object
	 *
	 * @return int success;
	 */
	 function zeroBSCRM_checkValidTempHash($objid=-1,$type='',$hash=''){

	 	// get a valid hash
	 	$hash = zeroBSCRM_getTempHash(-1,$type,$hash,1);
	 	
	 	// check id
	 	if (isset($hash) && is_array($hash) && isset($hash['objid'])) if ($objid == $hash['objid']) return true;

	 	return false;

	 }
	
	 /**
	 * retrieves a temporary hash object
	 *
	 * @return int success;
	 */
	 function zeroBSCRM_getTempHash($id=-1,$type='',$hash='',$status=-99){

		$id = (int)$id;
   		if (!empty($id) && $id > 0){
	
			global $ZBSCRM_t,$wpdb;

			$whereStr = ''; $additionalWHERE = ''; $queryVars = array();

			if (!empty($id)){

				$queryVars[] = $id;
				$whereStr = 'ID = %d';

			} else {

				if (!empty($hash)){

					$queryVars[] = $hash;
					$whereStr = 'zbstemphash_objhash = %s';

				}

			}

			if (!empty($type)){

				$queryVars[] = $type;
				$additionalWHERE = 'AND zbstemphash_objtype = %s ';

			} // else will be from ANY type


			if ($status != -99){

				$queryVars[] = $status;
				$additionalWHERE = 'AND zbstemphash_status = %d ';

			}

			/* -- prep started, see: #OWNERSHIP */
			
			if (!empty($whereStr)){

				$sql = "SELECT * FROM ".$ZBSCRM_t['temphash']." WHERE ".$whereStr." ".$additionalWHERE."ORDER BY ID ASC LIMIT 0,1";

   				$potentialReponse = $wpdb->get_row( $wpdb->prepare($sql,$queryVars), OBJECT );

			}

   			if (isset($potentialReponse) && isset($potentialReponse->ID)){

   				#} Retrieved :) fill + return
   				
					// tidy
					$res = zeroBS_tidy_temphash($potentialReponse);

   				return $res;
   			}

   		}

   		return false;


 	}

	 /**
	 * adds or updates a temporary hash object
	 *
	 * @return int success;
	 */
 	function zeroBSCRM_addUpdateTempHash($id=-1,$objstatus=-1,$objtype='',$objid=-1,$objhash='',$returnHashArr=false){

		// globals
		global $ZBSCRM_t,$wpdb;

		// got id?
		$id = (int)$id;
   		if (!empty($id) && $id > 0){

				// check exists?

				// for now just brutal update.
	   			if ($wpdb->update( 
						$ZBSCRM_t['temphash'], 
						array( 
							//'zbs_site' => zeroBSCRM_installSite(),
							//'zbs_team' => zeroBSCRM_installTeam(),
							//'zbs_owner' => zeroBSCRM_currentUserID(),

							'zbstemphash_status' => (int)$objstatus,
							'zbstemphash_objtype' => $objtype,
							'zbstemphash_objid' => (int)$objid,
							'zbstemphash_objhash' => $objhash,

							//'zbsmaillink_created' => time(),
							'zbstemphash_lastupdated' => time()
						), 
						array( // where
							'ID' => $id
							),
						array( 
							'%d', 
							'%s',
							'%d',
							'%s',
							'%d'
						),
						array(
							'%d'
							)
						) !== false){

							// if "return hash"
							if ($returnHashArr) return array('id'=>$id,'hash'=>$objhash);

	   						// return id
			   				return $id;

			   			}



		} else {
			
			// insert

			// create hash if not created :)
			if (empty($objhash)) $objhash = zeroBSCRM_GenerateTempHash();

			// go
			if ($wpdb->insert( 
						$ZBSCRM_t['temphash'], 
						array( 
							//'zbs_site' => zeroBSCRM_installSite(),
							//'zbs_team' => zeroBSCRM_installTeam(),
							//'zbs_owner' => zeroBSCRM_currentUserID(),

							'zbstemphash_status' => (int)$objstatus,
							'zbstemphash_objtype' => $objtype,
							'zbstemphash_objid' => (int)$objid,
							'zbstemphash_objhash' => $objhash,

							'zbstemphash_created' => time(),
							'zbstemphash_lastupdated' => time()
						), 
						array( 
							//'%d',  // site
							//'%d',  // team
							//'%d',  // owner

							'%d', 
							'%s',
							'%d',
							'%s',
							'%d',
							'%d'
						) 
					) > 0){

					// inserted, let's move on
					$newID = $wpdb->insert_id;

					// if "return hash"
					if ($returnHashArr) return array('id'=>$id,'hash'=>$objhash);

					return $newID;
				}

		}

		return false;

	}
	 /**
	 * deletes a temporary hash object
	 *
	 * @param array $args Associative array of arguments
	 * 				id
	 *
	 * @return int success;
	 */
	function zeroBSCRM_deleteTempHash($args=array()){

		// Load Args
		$defaultArgs = array(

			'id' 			=> -1

		); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) $$argK = $args[$argK]; }

		// globals
		global $ZBSCRM_t,$wpdb;

		$id = (int)$id;
   		if (!empty($id) && $id > 0) return zeroBSCRM_db2_deleteGeneric($id,'temphash');

	   	return false;

	}

	function zeroBS_tidy_temphash($obj=false){

   		$res = false;

   		if (isset($obj->ID)){
			$res = array();
			$res['id'] = $obj->ID;
			$res['created'] = $obj->zbstemphash_created;
			$res['lastupdated'] = $obj->zbstemphash_lastupdated;

			$res['status'] = $obj->zbstemphash_status;
			$res['objtype'] = $obj->zbstemphash_objtype;
			$res['objid'] = $obj->zbstemphash_objid;
			$res['objhash'] = $obj->zbstemphash_objhash;
		} 

		return $res;

	}
	// generates generic HASH (used for links etc.)
	function zeroBSCRM_GenerateTempHash($str=-1,$length=20){

		#} Brutal hash generator, for now
		if (!empty($str)){

			#} Semi-nonsense, not "secure"
			//$newMD5 = md5($postID.time().'fj30948hjfaosindf');

			$newMD5 = wp_generate_password(64, false);

			return substr($newMD5,0,$length-1);

		}

		return '';

	}

	// =========== / TEMPHASH   ======================================================
	// ===============================================================================


// FOLLOWING 2 funcs should be moved to DAL2 
function zeroBSCRM_task_getMeta($ID = -1){


  $ret = false;
  #} if we are on a new post, status will be auto-draft
  if(get_post_status($ID) == 'auto-draft'){
      $ret['placeholder'] = __('Task Name...', 'zero-bs-crm');
      $ret['title'] = '';
      return $ret;
  }


  if($ID > 0){
      $ret1           = get_post_meta($ID,'zbs_event_meta',true);
      $ret2           = get_post_meta($ID,'zbs_event_actions', true);
      $owner          = zeroBS_getOwner($ID,true,'zerobs_event');
      $ret3['owner']  = $owner['ID'];
      
      $ret            = array_merge($ret1,$ret2);

      $ret            = array_merge($ret,$ret3);
      if(!array_key_exists('title', $ret)){
          $ret['title'] = get_the_title($ID);
          if($ret['title'] == __('Auto Draft','zero-bs-crm')){
              $ret['title'] = '';
          }
      }
      $ret['placeholder'] = __('Task Name...', 'zero-bs-crm');
  }else{
      $ret['placeholder'] = __('Task Name...', 'zero-bs-crm');
      $ret['title'] = '';
  }
  return $ret;
}

function zeroBSCRM_task_updateMeta($ID = -1, $meta = array()){
  
  #} bit of a pig, but can pass any array of meta, and it update just that.
  $task_meta = array('from','to','notes','title','customer','showoncal');
  $action_meta = array('complete', 'notify');
  
  if($ID > 0){
      $task_meta_vals     =   get_post_meta($ID,'zbs_event_meta',true);
      $action_meta_vals   =   get_posT_meta($ID,'zbs_event_actions', true);

      #} combine into a single parent meta in DB2.0 for speed..
      foreach($meta as $k => $v){
          if(in_array($k, $task_meta)){
              $task_meta_vals[$k] = $v;
          }
          if(in_array($k,$action_meta)){
              $action_meta_vals[$k] = $v;
          }
      }

      #}updates the two arrays
      update_post_meta($ID, 'zbs_event_meta', $task_meta_vals);
      update_post_meta($ID,'zbs_event_actions', $action_meta_vals);        
  
  }
}


function zeroBSCRM_getAddressCustomFields(){

    global $zbs,$zbsAddressFields,$zbsFieldSorts;

    $customfields = $zbs->settings->get('customfields');

    if (isset($customfields) && is_array($customfields)){

        #} Addresses
        if (isset($customfields['addresses'])){

            if (is_array($customfields['addresses']) && count($customfields['addresses']) > 0) return $customfields['addresses'];

        }

    }

    return array();
}


#} ZBS users page - returns list of WP user IDs, which have a ZBS role and includes name / email, etc
function zeroBSCRM_crm_users_list(){  
      //from Permissions
      /*
      remove_role('zerobs_admin');
      remove_role('zerobs_customermgr');
      remove_role('zerobs_quotemgr');
      remove_role('zerobs_invoicemgr');
      remove_role('zerobs_transactionmgr');
      remove_role('zerobs_customer');
      remove_role('zerobs_mailmgr');

        */
        //NOT zerbs_customer - this is people who have purchased (i.e. WooCommerce folk)
        $role = array('zerobs_customermgr','zerobs_admin','administrator','zerobs_quotemgr', 'zerobs_invoicemgr', 'zerobs_transactionmgr',  'zerobs_mailmgr'); 
        $crm_users = get_users(array('role__in' => $role, 'orderby' => 'ID'));

        //this will return what WP holds (and can interpret on the outside.)
        return $crm_users;

}

/*
function zeroBSCRM_getObjCreationLog($objID=-1,$objType=ZBS_TYPE_CONTACT){
			

		if (!empty($objID) && $objID !== -1 && $objID !== false){

			global $zbs;
			return $zbs->DAL->getLogsForObj(array(

					'objtype' => $objType,
					'objid' => $objID,

					'notetype' 	=> 'Created',

					'incMeta' 	=> true,

					'sortByField' 	=> 'zbsl_created',
					'sortOrder' 	=> 'ASC',
					'page'			=> 0,
					'perPage'		=> 1,
					'ignoreowner'		=> zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)

				));

		}
}
function zeroBSCRM_logTypeStrToDB($str=''){

	$x = str_replace(' ','_',$str);
	$x = str_replace(':','_',$x);
	return strtolower($x);

} */
/* ======================================================
	/ To be sorted helpers  
   ====================================================== */



/* ======================================================
   / DAL2.5 Work: (Objs other than contacts)
   / FUNCS replaced by DAL2.5.LegacySupport.php
   / DAL 1 funcs
		- The above remains unchanged from DAL1
		- ... because in DAL2 we only migrated some of the objects (not all)   
		- ... leaving these in-tact
		- ... DAL2.Helpers.php supercedes this file and has all functions rewritten
   ====================================================== */

   
// ====================================================================================================================================
// ====================================================================================================================================
// ==================== / UNMIGRATED DAL1 DB FUNCS ====================================================================================
// ====================================================================================================================================
// ====================================================================================================================================
   
// BECAUSE in DA3 we moved all data into 1 object for sub objs (quotes, invs, trans etc.)
// we get meta here, but then we 'copy it' into main ret obj :)
function zeroBSCRM_DAL2to3CompatTranslateMeta($objArr=array()){

	global $zbs;

		$objMeta = array();

		// $objArr should be array with 'meta' => array, this rebuilds, basically, to have $obj + $obj['meta'], basically the same :/
		if (isset($objArr['meta']) && is_array($objArr['meta'])) {
			// pinch it from array
			$objMeta = $objArr['meta'];
			unset($objArr['meta']);
		}

		// return obj starts out as this
		$retObj = array_merge($objMeta,$objArr);

		// if compatability support (e.g. old extensions present), also dump this into a sub-array
		// (gross, inefficient etc.)
		if ($zbs->db2CompatabilitySupport) $retObj['meta'] = $objMeta;

		return $retObj;

}

/* orig ver, not used
function zeroBSCRM_DAL2to3CompatTranslateMetaPRE($objMetaArr=array()){

	global $zbs;

		// if compatability support (e.g. old extensions present), also dump this into a sub-array
		// (gross, inefficient etc.)
		if (!is_array($objMetaArr)) $objMetaArr = array();
		if ($zbs->db2CompatabilitySupport) $objMetaArr['meta'] = $objMetaArr;

		return $objMetaArr;

}*/




// ==============================================================================================================================
// ================================= DAL PREP FOR INVOICING v3.0 ================================================================
// ========================= This offers backward compat for what will be fully in v3.0 =========================================
// ==============================================================================================================================        

//middleman for getting the invoice partials (i.e. transactions), DAL3.0 should replace this (easier query?)
function zeroBSCRM_get_invoice_partials($objID = -1){
    global $wpdb;
    $res = false;
    if($objID > 0){                    
        $zbs_partials_query = $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE meta_key = 'zbs_invoice_partials' AND meta_value = '%d'", $objID);
        $zbs_partials = $wpdb->get_results($zbs_partials_query);
        $i = 0;
        $gone_ids = array();
        foreach($zbs_partials as $zbs_partial){ 
            $trans_meta = get_post_meta($zbs_partial->post_id,'zbs_transaction_meta',true);                
            if(!in_array($trans_meta['orderid'], $gone_ids)){ 
                $gone_ids[]         = $trans_meta['orderid'];
                $res[$i]['id']      = $zbs_partial->post_id;
                $res[$i]['orderid'] = $trans_meta['orderid'];
                $res[$i]['total']   = $trans_meta['total'];
                $i++;
            }
        }
    }
    return $res;
}

#} this function probably becomes defunct when DAL ready. Is cos right now, if invoice_meta = '' then it's a new invoice, so should return defaults.
function zeroBSCRM_get_invoice_defaults($objID = -1){
           
    #} Settings
    $settings = zeroBSCRM_get_invoice_settings();
    $logo       = $settings['logo'];

    /* WH: not sure this worked? Replaced with what made sense (and tested)
    $default_date = get_the_date();

    if ($default_date == ''){
        $default_date = date(get_option( 'date_format' ), time());
    } */
    $default_date = zeroBSCRM_date_i18n(-1,time(),false,true);


    $custom_id = zeroBSCRM_getNextInvoiceID();  // this should return invoice_id + offset (if that option is set) or -1.

    #} order this to match the above, eventually.
    $defaults = array(
            'status'                    => 'draft',                 // default invoice status. Best way to have these customisable?
            'invoice_id'                => $objID,                  // blank, created on save
            'new_invoice'               => true,
            'invoice_custom_id'         => $custom_id,
            'invoice_items'             => array(),
            'invoice_hours_or_quantity' => 'quantity',
            'invoice_contact'           => -1,
            'invoice_company'           => -1,
            'ref'                       => $settings['defaultref'],
            'date'                      => $default_date,                        //need to sort this out on the way out (in TS to outputtable date for Date Picker)
            'due'                       => 0,
            'hash'                      => zeroBSCRM_GetHashForPost($objID),
            'invoice_logo_url'          => $logo,
            'bill'                      => '',
            'settings'                  => $settings,
            'product_index'             => zeroBSCRM_getProductIndex(),
            'preview_link'              => '/invoices/hash',
            'pdf_installed'             => zeroBSCRM_isExtensionInstalled('pdfinv'),
            'portal_installed'          => zeroBSCRM_isExtensionInstalled('portal'),
            'totals'                    => array(
                        'invoice_discount_total'    => 0,
                        'invoice_discount_type'     => '%',
                        'invoice_postage_total'     => 0
            )
    );
    return $defaults;

 }


#} wrapper as right now it was loading the full settings into the page. Tidy up page to have the translations here. 
#} WH - is it possible that some languages here will mess with the output? character encoding wise?
function zeroBSCRM_get_invoice_settings(){

    global $zbs;
    
    $all_settings = $zbs->settings->getAll();
    
    $invoice_settings = array(
        'b2bmode'           => zbs_ifAV($all_settings,'companylevelcustomers',false),
        'invtax'            => zbs_ifAV($all_settings,'invtax',''),
        'invpandp'          => zbs_ifAV($all_settings,'invpandp',''),
        'invdis'            => zbs_ifAV($all_settings,'invdis',''),
        'logo'              => zbs_ifAV($all_settings,'invoicelogourl',''),
        'bizname'           => zbs_ifAV($all_settings,'businessname',''),
        'yourname'          => zbs_ifAV($all_settings,'businessyourname',''),
        'defaultref'        => zbs_ifAV($all_settings,'defaultref',''),
        'invhash'           => zbs_ifAV($all_settings,'easyaccesslinks',''),
        'hideid'            => zbs_ifAV($all_settings,'invid',false),
        'businessextra'     => nl2br(zeroBSCRM_textExpose(zbs_ifAV($all_settings,'businessextra',''))),
        'businessyouremail' => zbs_ifAV($all_settings,'businessyouremail',''),
        'businessyoururl'   => zbs_ifAV($all_settings,'businessyoururl',''),
        'settings_slug'     => admin_url("admin.php?page=" . $zbs->slugs['settings']) . "&tab=invbuilder",
        'biz_settings_slug'     => admin_url("admin.php?page=" . $zbs->slugs['settings']) . "&tab=bizinfo",
        'addnewcontacturl' => zbsLink('create',-1,'zerobs_customer'),
        'addnewcompanyurl' => zbsLink('create',-1,'zerobs_company'),
        'contacturlprefix' => zbsLink('edit',-1,'zerobs_customer',true),
        'companyurlprefix' => zbsLink('edit',-1,'zerobs_company',true),
        'lang'                  => array(
                'invoice_number'    => zeroBSCRM_slashOut(__('ID', 'zero-bs-crm'),true),
                'invoice_date'      => zeroBSCRM_slashOut(__('Invoice Date', 'zero-bs-crm'),true),
                'reference'         => zeroBSCRM_slashOut(__('Reference', 'zero-bs-crm'),true),
                'due_date'          => zeroBSCRM_slashOut(__('Due Date', 'zero-bs-crm'),true),
                'frequency'         => zeroBSCRM_slashOut(__('Frequency', 'zero-bs-crm'),true),
                'update'            => zeroBSCRM_slashOut(__('Update', 'zero-bs-crm'),true),
                'remove'            => zeroBSCRM_slashOut(__('Remove', 'zero-bs-crm'),true),
                'biz_info'          => zeroBSCRM_slashOut(__('Your business information', 'zero-bs-crm'),true),
                'add_edit'          => zeroBSCRM_slashOut(__('Edit '.jpcrm_label_company().' Details', 'zero-bs-crm'),true),
                'add_logo'          => zeroBSCRM_slashOut(__('Add your logo', 'zero-bs-crm'),true),
                'send_to'           => zeroBSCRM_slashOut(__('Assign Invoice to', 'zero-bs-crm'),true),
                'customise'         => zeroBSCRM_slashOut(__('Customise', 'zero-bs-crm'),true),
                'hours'             => zeroBSCRM_slashOut(__('Hours', 'zero-bs-crm'),true),
                'quantity'          => zeroBSCRM_slashOut(__('Quantity', 'zero-bs-crm'),true),
                'description'       => zeroBSCRM_slashOut(__('Description', 'zero-bs-crm'),true),
                'price'             => zeroBSCRM_slashOut(__('Price', 'zero-bs-crm'),true),
                'rate'              => zeroBSCRM_slashOut(__('Rate', 'zero-bs-crm'),true),
                'tax'               => zeroBSCRM_slashOut(__('Tax', 'zero-bs-crm'),true),
                'add_row'           => zeroBSCRM_slashOut(__('Add Row', 'zero-bs-crm'),true),
                'remove_row'        => zeroBSCRM_slashOut(__('Remove Row', 'zero-bs-crm'),true),
                'amount'            => zeroBSCRM_slashOut(__('Amount', 'zero-bs-crm'),true),
                'discount'          => zeroBSCRM_slashOut(__('Discount', 'zero-bs-crm'),true),
                'shipping'          => zeroBSCRM_slashOut(__('Shipping', 'zero-bs-crm'),true),
                'tax_on_shipping'   => zeroBSCRM_slashOut(__('Tax on shipping', 'zero-bs-crm'),true),
                'due'               => array(
                                        'none'      => zeroBSCRM_slashOut(__('No due date', 'zero-bs-crm'),true),
                                        'on'        => zeroBSCRM_slashOut(__('Due on receipt', 'zero-bs-crm'),true),
                                        'ten'       => zeroBSCRM_slashOut(__('Due in 10 days', 'zero-bs-crm'),true),
                                        'fifteen'   => zeroBSCRM_slashOut(__('Due in 15 days', 'zero-bs-crm'),true),
                                        'thirty'    => zeroBSCRM_slashOut(__('Due in 30 days', 'zero-bs-crm'),true),
                                        'fortyfive' => zeroBSCRM_slashOut(__('Due in 45 days', 'zero-bs-crm'),true),
                                        'sixty'     => zeroBSCRM_slashOut(__('Due in 60 days', 'zero-bs-crm'),true),
                                        'ninety'    => zeroBSCRM_slashOut(__('Due in 90 days', 'zero-bs-crm'),true)
                ),
                'preview'           => zeroBSCRM_slashOut(__('Preview', 'zero-bs-crm'),true),
                'dl_pdf'            => zeroBSCRM_slashOut(__('Download PDF', 'zero-bs-crm'),true),
                'bill_to'           => zeroBSCRM_slashOut(__('Enter email address or name', 'zero-bs-crm'),true),
                'edit_record'       => zeroBSCRM_slashOut(__('Edit record', 'zero-bs-crm'),true),
                'no_tax'            => zeroBSCRM_slashOut(__('No tax', 'zero-bs-crm'),true),
                'sub_total'         => zeroBSCRM_slashOut(__('Sub total', 'zero-bs-crm'),true),
                'total'             => zeroBSCRM_slashOut(__('Total', 'zero-bs-crm'),true),
                'amount_due'        => zeroBSCRM_slashOut(__('Amount Due', 'zero-bs-crm'),true),
                'partial_table'     => zeroBSCRM_slashOut(__('Payments', 'zero-bs-crm'),true),
                'rowtitleplaceholder' => zeroBSCRM_slashOut(__('Item Title', 'zero-bs-crm'),true),
                'rowdescplaceholder' => zeroBSCRM_slashOut(__('Item Description', 'zero-bs-crm'),true),
                'noname' => zeroBSCRM_slashOut(__('Unnamed', 'zero-bs-crm'),true), // no name on typeahead,
                'contact' => zeroBSCRM_slashOut(__('Contact', 'zero-bs-crm'),true), // contact view button (if assigned)
                'company' => zeroBSCRM_slashOut(jpcrm_label_company(),true), // contact view button (if assigned)
                'view' => zeroBSCRM_slashOut(__('View', 'zero-bs-crm'),true),
                'addnewcontact' => zeroBSCRM_slashOut(__('Add New Contact', 'zero-bs-crm'),true),
                'newcompany' => zeroBSCRM_slashOut(__('New '.jpcrm_label_company(), 'zero-bs-crm'),true),
                'or' => zeroBSCRM_slashOut(__('or', 'zero-bs-crm'),true),


        )
    );
    return $invoice_settings;

}

#} Invoicing Pro - needs product index 
function zeroBSCRM_getProductIndex(){
    $product_index = array();
    apply_filters('zbs_product_index_array', $product_index);
    return $product_index;
}


    // these were clunky to say least, tried to rename + bring sense to them, probs need rewrite for DAL3

	#} added, wrapper to store the hash for the post (currently using post_meta)
	#} DAL will need to migrate these three functions over.
	function zeroBSCRM_ensureHashForPost($postID = -1){
		
        if($postID > 0){

			$hash = zeroBSCRM_GetHashForPost($postID);
			//no hash so generate it
			if ($hash == ''){
                $hash = zeroBSCRM_GenerateHashForPost($postID, 20);
				zeroBSCRM_saveHashForPost($postID,$hash);
			}

            if (!empty($hash)) return $hash;
		}
        
        return false;
	}


    function zeroBSCRM_saveHashForPost($postID = -1,$hash = ''){
        return update_post_meta($postID, 'zbshash', $hash);
    }


	function zeroBSCRM_GetHashForPost($postID = -1){
		$hash = get_post_meta($postID, 'zbshash', true);
        // Return with PREFIX (makes it interpretable later on as this is shared between invID + invHash (for example) at endpoint /invoices/*hashorid)
        return 'zh-'.$hash;
	}


	//function is this a hash of an INVOICE. Could be refined when DB2.0
	function zeroBSCRM_invoicing_getFromHash($hash = '', $pay = -1){
		//function for checking if a hash is valid

		//SANITIZE
		$hash = sanitize_text_field($hash); //sanitize it here

        // if prefix still present, chunk off
        if (substr($hash,0,3) == 'zh-') $hash = substr($hash,3);

		global $wpdb;
		//currently in POST META but will be in own table soon (objType will be X or Y) for Quote or invoice
		$sql = $wpdb->prepare("SELECT post_id FROM " . $wpdb->postmeta . " WHERE meta_key = 'zbshash' AND meta_value = %s", $hash);

		$res = (int)$wpdb->get_var($sql);
		if($res > 0){
			//we have a ID found in the DB matching the hash now we need to get the $cID for the invoice (mainly for getting the Stripe Customer)
			if($pay > 0){
				//paying so need the customerID from settings otherwise just viewing so dont need to expose data
			}
			$ret['success'] = true;
			$ret['data'] = array(
				'ID'	=> $res,
				'cID'	=> -1
			);
		}else{
			$ret['success'] = false;
			$ret['data'] 	= array();
		}		
		//return the customer information that the invoice will need (i.e. Stripe customerID) same function will be used
		//in invoice checkout process (invoice pro) when being paid for using a HASH URL.

		return $ret;
	
	}



/**
 *  This file has the various functions used to control the invoice metaboxes
 *  Wrappers so can be used throughout and switched over when it comes to it
 *   
 *  The current metabox output, has also been changed to draw with JS now given
 *  the added complexity of the tax table and discount per line
 * 
 *  The calculation routine has also been reviewed to calculate the tax due
 *  AFTER the line items discount has been applied
 * 
 *  Drawing a new line was already available in JS, but the initial load (new) and edit
 *  were messily drawn in PHP
 * 
 *  Now it simply stores the invoice meta as one big data JSON structure outlined below
 *  data format described below
 * 
 *  JSON object for invoice
 * 
 *  invoiceObj = {
 *!                 invoice_id: 5,        // ID in the database - usually the invoice ID.
 *!                 invoice_custom_id: -1 // the ID if over-written by settings
 *                      
 *!                 status:  paid,        // not defined in settings - should be? (draft, unpaid, paid, overdue)
 * 
 *                  preview_link:         // generated from hash
 *                  pdf_dl_link:          // downloaded on fly
 * 
 *!                  hash:                 // the invoice hash (for front end accessible pages)
 * 
 *                  pdf_template:         // the template to use 
 *                  portal_template:      // allow the choice of portal template (0 = default)
 *                  email_template:       // allow the choice of email template (0 = default) 
 * 
 *                  invoice_frequency:    // invoicing pro only (0 = once only, 1 = week, 2 = month, 3 = year)
 * 
 *                  invoice_number:       // this is over-ridable in settings
 *                  invoice_date:         // date of the invoice    
 *                  invoice_due:          // when due -1 (no due date), 0 (on receipt), 10, 15, 30, 45, 60, 90 (days in advance of invoice date)
 *                  
 *!                  invoice_ref:          // internal reference number      
 * 
 *                  invoice_parent:       // invoice pro only (0 for parent), id of parent if child
 *                        
 *                  invoice_pay_via:      // 0 online, 1 bank transfer, 2 (both) - Zak addition to show online payment only for some
 * 
 * 
 *!                  invoice_logo_url:    // url of the invoice logo (default, or custom per invoice)
 *                  invoice_business_details:   // the details from settings to go on the invoice (also in settings obj)
 * 
 * 
 *                  invoice_send_to:       // email to send the invoice to
 *!                 invoice_contact:       // 0 or contact ID
 *!                 invoice_company:       // 0 or company ID
 *                  invoice_address_to:    // 0 contact or 1 company. So if assigned to Mike, can be address to a company (i.e. Mike Stott: Jetpack CRM, Mike Stott: Epic Plugins) etc
 * 
 * 
 *                  invoice_hours_or_quantity:    0 for hours, 1 for quantity
 *                  
 *                  invoice_items:   {
 *                                      item_id: (line_item ID)
 *                                      order:   (order in list, i.e. 0,1,2,3,4,5) 
 *                                      title:  
 *                                      description: 
 *                                      unit: 
 *                                      price: 
 *                                      tax_ids: {
 *                                              id: 1, rate: 20,
 *                                              id: 2, rate: 19
 *                                      },
 *                                      
 *                                    },{
 *                                    
 *                                    }
 * 
 *                  invoice_discount:   0,
 *                  invoice_shipping:   0,
 *                  invoice_shipping_tax: {
 *                                      tax_ids:{
 *                                             id: 1, rate: 20,
 *                                             id: 2, rate: 19
 *                                      }
 *                  },
 * 
 *                  invoice_tip:        0, 1 (allow tip) - not in UI yet
 *                  invoice_partial:    0, 1 (allow partial payment) - in UI already (i.e. can assign multiple transactions) need to handle it via checkout (i.e. pay full amount, or pay instalments)
 * 
 *                  transactions: {                             //the transactions against the invoice (array to allow for partial payments)
 *                                      transaction_id: 5,
 *                                      amount: 200,
 *                                      status: paid, 
 *                                  },
 *                  invoice_attachments: {
 *                              id: 1,
 *                              url:  uploaded_url
 *                              send: 0,1     
 *                  },
 *                  invoice_custom_fields: {
 *                          id: 1, 
 *                          label: "vesting period",
 *                          type:  "date",
 *                          value: "20/10/2019"
 *                  },
 *                  //what the invoice settings are (biz info, tax etc)
 *                  settings: {
 *                      
 *                  }
 * 
 *                }
 * 
 * 
 *   tax_linesObj = {
 *                      id:       
 *                      name:    (e.g. VAT, GST)
 *                      rate:    (%)
 *                  } 
 */


 // this gets the data (from the current DAL and outputs it to the UI) - can get via jQuery 
 // once happy it works and fills the current databse. This will need switching over come 
 // the new DAL database structure but allows me to work with the UI now ahead of time.



// wh Centralised, is ultimately output via ajax zeroBSCRM_AJAX_getInvoice function in Control.Invoices
 function zeroBSCRM_invoicing_getInvoiceData($invID=-1){

    global $zbs, $wpdb;

    $data = array();

    // if > 0 = existing
    if ($invID > 0){

        // build response
        $data['invoiceObj'] = array();

        #} Get the invoice_meta for the invID (old DB1.0 way but the below meta isn't everything)
        $invoice = get_post_meta($invID, 'zbs_customer_invoice_meta', true);

        if (!is_array($invoice)){

            // get blank defaults - this is for a de-headed serpent? (don't think should ever exist)
            $data['invoiceObj'] = zeroBSCRM_get_invoice_defaults($invID);

        } else {

            // process the loaded data

            if (!isset($invoice['date'])){
                $invoice['date'] = get_the_date('',$invID);
            }
            
            // in DAL2 world, can populate this directly from the DB query.
            if (is_array($invoice)){

                #} This is what has been stored initially, however other meta used throughout page and save
                #} Whcih is bad really, as there's a TON of infomration stored. In a pretty crap way

                #} Settings
                $settings = zeroBSCRM_get_invoice_settings();

                #} Hash
                //WH note: isn't this loaded from DB:?
                $hash = zeroBSCRM_GetHashForPost($invID);

                #} Pre-processing
                $logo = '';
                if (!isset($invoice['logo']))
                    $logo = $settings['logo'];
                else
                    $logo = $invoice['logo'];
                

                #} MAIN data array
                $data['invoiceObj']['invoice_id']                   = $invID;
                $data['invoiceObj']['invoice_custom_id']            = get_post_meta($invID,'zbsid', true);


                $data['invoiceObj']['status']                       = strtolower($invoice['status']);
                $data['invoiceObj']['ref']                          = $invoice['ref'];


                $data['invoiceObj']['invoice_contact']              = get_post_meta($invID, 'zbs_customer_invoice_customer', true);
                $data['invoiceObj']['invoice_company']              = get_post_meta($invID, 'zbs_company_invoice_company', true);
                $data['invoiceObj']['new_invoice']                  = false;

                // these two aren't actually in $invoice - can get the bill from the invoice_contact
                $billing_email = '';
                if($data['invoiceObj']['invoice_contact'] > 0){

                    // retrieve #WHDAL3UI (make this getContactEmail)
                    $customer = zeroBS_getCustomer($data['invoiceObj']['invoice_contact']);
                    if (is_array($customer) && isset($customer['email'])) $billing_email = $customer['email'];

                } elseif ($data['invoiceObj']['invoice_contact'] > 0){

                    $company = zeroBS_getCompany($data['invoiceObj']['invoice_contact']);
                    if (is_array($company) && isset($company['email'])) $billing_email = $company['email'];

                }
                
                //handle if due is not set
                $data['invoiceObj']['due'] = -1; // default
                if (isset($invoice['due'])) $data['invoiceObj']['due'] = $invoice['due'];
                
                $data['invoiceObj']['bill']                         = $billing_email;
                $data['invoiceObj']['invoice_items']                = get_post_meta($invID, 'zbs_invoice_lineitems', true);
                $data['invoiceObj']['invoice_logo_url']             = $logo;
                $data['invoiceObj']['hash']                         = $hash;
                $data['invoiceObj']['date']                         = $invoice['date'];     
                $data['invoiceObj']['invoice_hours_or_quantity']    = get_post_meta($invID, 'zbsInvoiceHorQ', true);


                //preview link uses portal and a hash now for individual invoice viewing
                $portalLink         = zeroBS_portal_link();
                $invoice_endpoint   = zeroBSCRM_portal_get_invoice_endpoint();

                //if invoice hashes this will be a hash URL, otherwise the invoice ID
                if($settings['invhash']){
                    $preview_link       = esc_url($portalLink .  $invoice_endpoint .  '/' . $hash);
                }else{
                    $preview_link       = esc_url($portalLink .  $invoice_endpoint .  '/' . $invID);
                }
                //if a hash is set (or admin) load the invoice for logged out users, if agreed
                //will still need to load the contactID and info in the Stripe call too even if logged out
                $data['invoiceObj']['preview_link']                 = $preview_link;

                //is PDF and Portal installed 
                $data['invoiceObj']['pdf_installed']                = zeroBSCRM_isExtensionInstalled('pdfinv');
                $data['invoiceObj']['portal_installed']             = zeroBSCRM_isExtensionInstalled('portal');

                #urgh. this was how we got the settings object.
                #refine this too.

                # SETTINGS array (process from main ZBS settings object)
                $data['invoiceObj']['settings']                     = $settings;

                $data['invoiceObj']['totals']                       = get_post_meta($invID,"zbs_invoice_totals",true);

                //shipping total needs to return 0 in some cases if not set it is empty. GRR @ mike DB1.0 data.
                if(!array_key_exists('invoice_postage_total', $data['invoiceObj']['totals'] )){
                    $data['invoiceObj']['totals']['invoice_postage_total'] = 0;
                }

                # Invoice PARTIALS
                $data['invoiceObj']['partials']                     = zeroBSCRM_get_invoice_partials($invID);


            }else{
                //we don't have any meta, this is a new invoice (WP will generate it an auto-draft postID)
                $data['invoiceObj'] = zeroBSCRM_get_invoice_defaults($invID);
            }

        }

        #} update to get from tax table UI. Below is dummy data for UI work (UI tax table TO DO)
        $data['tax_linesObj'] = zeroBSCRM_getTaxTableArr();
        
        return $data;

    }

    return false; 
}


// ===============================================================================
// =======================  File Upload Related Funcs ============================
   function zeroBS___________FileHelpers(){return;}

	// retrieve all files for a (customer)whatever
	function zeroBSCRM_files_getFiles($fileType = '',$objID=-1){

		global $zbs;

		$filesArrayKey = zeroBSCRM_files_key($fileType);
		
		if (!empty($filesArrayKey) && $objID > 0){

			// for DAL1 contacts + quotes/invs:
			if (!$zbs->isDAL2() || $filesArrayKey == 'zbs_customer_quotes' || $filesArrayKey == 'zbs_customer_invoices' || $filesArrayKey == 'zbs_company_files') // DAL1
				return get_post_meta($objID, $filesArrayKey, true);
			elseif ($zbs->isDAL2()){

				// DAL2+
				// bit gross hard-typed, could be genericified as all is using is >DAL()->getMeta
				switch ($fileType){

					case 'customer':
					case 'contact':
						return $zbs->DAL->contacts->getContactMeta($objID,'files');
						break;

					case 'quotes':
					case 'quote':
						return $zbs->DAL->quotes->getQuoteMeta($objID,'files');
						break;

					case 'invoices':
					case 'invoice':
						return $zbs->DAL->invoices->getInvoiceMeta($objID,'files');
						break;

					case 'companies':
					case 'company':
						return $zbs->DAL->companies->getCompanyMeta($objID,'files');
						break;

					// no default

				}

			} // / is DAL2+

		}

		return array();
	}

	// updates files array for a (whatever)
	function zeroBSCRM_files_updateFiles($fileType = '',$objID=-1,$filesArray=-1){

		global $zbs;

		$filesArrayKey = zeroBSCRM_files_key($fileType);
		
		if (!empty($filesArrayKey) && $objID > 0){

			// for DAL1 contacts + quotes/invs:
			if (!$zbs->isDAL2() || $filesArrayKey == 'zbs_customer_quotes' || $filesArrayKey == 'zbs_customer_invoices' || $filesArrayKey == 'zbs_company_files') // DAL1
				update_post_meta($objID,$filesArrayKey,$filesArray);
			elseif ($zbs->isDAL2()){

				// DAL2+
				// bit gross hard-typed, could be genericified as all is using is >DAL()->getMeta
				switch ($fileType){

					case 'customer':
					case 'contact':
						$zbs->DAL->updateMeta(ZBS_TYPE_CONTACT,$objID,'files',$filesArray);
						break;

					case 'quotes':
					case 'quote':
						$zbs->DAL->updateMeta(ZBS_TYPE_QUOTE,$objID,'files',$filesArray);
						break;

					case 'invoices':
					case 'invoice':
						$zbs->DAL->updateMeta(ZBS_TYPE_INVOICE,$objID,'files',$filesArray);
						break;

					case 'companies':
					case 'company':
						$zbs->DAL->updateMeta(ZBS_TYPE_COMPANY,$objID,'files',$filesArray);
						break;

					// no default

				}

			}

			return $filesArray;			

		}

		return false;
	}

	// moves all files from one objid to another objid
	function zeroBSCRM_files_moveFilesToNewObject($fileType='',$oldObjID=-1,$objID=-1){

		// v3.0+ only
		return false;
	}

	// gets meta key for file type arr
	function zeroBSCRM_files_key($fileType=''){


		switch ($fileType){

			case 'customer':
			case 'contact':

				return 'zbs_customer_files';

				break;
			case 'quotes':
			case 'quote':

				return 'zbs_customer_quotes';

				break;
			case 'invoices':
			case 'invoice':

				return 'zbs_customer_invoices';

				break;

			case 'companies':
			case 'company':

				return 'zbs_company_files';

				break;

		}

		return '';
	}

// ======================= / File Upload Related Funcs ===========================
// ===============================================================================


// === Security Log, quick + rough DAL

// this is fired on all req (expects a "fini" followup fire of next func to mark "success")
// (defaults to failed req.)
function zeroBSCRM_security_logRequest($reqType='unknown',$reqHash='',$reqID=-1){

    // don't log requests for admins, who by nature, can see all
    // needs to match zeroBSCRM_security_finiRequest precheck
    if (zeroBSCRM_isZBSAdminOrAdmin()) return false;

    global $wpdb,$ZBSCRM_t;

    // if user logged in, also log id
    $userID = (int)wp_get_current_user();
    $userIP = zeroBSCRM_getRealIpAddr(); 

    // validate these a bit
    $validTypes = array('quoteeasy','inveasy');
    if (!in_array($reqType, $validTypes)) $reqType = 'na';
    $reqHash = sanitize_text_field( $reqHash ); if (strlen($reqHash) > 128) $reqHash = '';
    $reqID = (int)sanitize_text_field( $reqID );

    if ($wpdb->insert( 
        $ZBSCRM_t['security_log'], 
        array( 

            //'zbs_site' => zeroBSCRM_installSite(),
            //'zbs_team' => zeroBSCRM_installTeam(),
            'zbs_owner' => -1, //zeroBSCRM_currentUserID(),

            'zbssl_reqtype' => $reqType, 
            'zbssl_ip' => $userIP, 
            'zbssl_reqhash' => $reqHash, 

            'zbssl_reqid' => $reqID, 
            'zbssl_loggedin_id' => $userID, 
            'zbssl_reqstatus' => -1, // guilty until proven...
            'zbssl_reqtime' => time()
        ), 
        array( 
            '%d', 

            '%s' , 
            '%s' , 
            '%s' , 

            '%d' , 
            '%d' , 
            '%d' , 
            '%d' 
        ) 
    )){

        // success
        return $wpdb->insert_id;

    } 

    return false;

}

// after security validated, 
function zeroBSCRM_security_finiRequest($requestID=-1){

    // don't log requests for admins, who by nature, can see all
    // needs to match zeroBSCRM_security_logRequest precheck
    if (zeroBSCRM_isZBSAdminOrAdmin()) return false;

    // basic check
    $requestID = (int)$requestID;

    if ($requestID > 0){

        global $wpdb,$ZBSCRM_t;

        // for now just brutal update, not even comparing IP
        if ($wpdb->update( 
                    $ZBSCRM_t['security_log'], 
                    array( 
                        'zbssl_reqstatus' => 1
                    ), 
                    array( // where
                        'ID' => $requestID
                        ),
                    array( 
                        '%d',
                    ),
                    array(
                        '%d'
                    )
                ) !== false){

                    // return id
                    return $requestID;

                }

    }

    return false;
}

// checks if blocked 
function zeroBSCRM_security_blockRequest($reqType='unknown'){ 

    // don't log requests for admins, who by nature, can see all
    // needs to match zeroBSCRM_security_logRequest etc. above
    if (zeroBSCRM_isZBSAdminOrAdmin()) return false;

    global $zbs,$wpdb,$ZBSCRM_t;

    // see if more than X (5?) failed request accessed by this ip within last Y (48h?)
    $userIP = zeroBSCRM_getRealIpAddr(); 
    $sinceTime = time()-172800; // 48h = 172800
    $maxFails = 5;
    $query = $wpdb->prepare( "SELECT COUNT(ID) FROM ".$ZBSCRM_t['security_log']." WHERE zbssl_ip = %s AND zbssl_reqstatus <> %d AND zbssl_reqtime > %d", array($userIP,1,$sinceTime));
    $countFailed = (int)$wpdb->get_var($query);

    // less than ..
    if ($countFailed < $maxFails) return false;

    return true;
}


// removes all security logs older than setting (72h at addition)
// this is run DAILY by a cron job in ZeroBSCRM.CRON.php
function zeroBSCRM_clearSecurityLogs(){

    global $zbs,$wpdb,$ZBSCRM_t;

    // older than
    $deleteOlderThanTime = time()-259200; // 72h = 259200

    // delete
	$wpdb->query($wpdb->prepare("DELETE FROM ".$ZBSCRM_t['security_log']." WHERE zbssl_reqtime < %d",$deleteOlderThanTime));

}



#} General function to check the amount due on an invoice, if <= mark as paid.
function zeroBSCRM_check_amount_due_mark_paid($invoice_id){

	$status = __('Unpaid','zero-bs-crm');
    $zbs_inv_meta = get_post_meta($invoice_id,'zbs_customer_invoice_meta', true); 

    global $wpdb;
    //get the partials from the transactions
    $zbs_partials_query = $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE meta_key = 'zbs_invoice_partials' AND meta_value = '%d'", $invoice_id);
    $zbs_partials = $wpdb->get_results($zbs_partials_query);
    
    $gone_ids = array();
    $zbsInvoiceTotalsArr = get_post_meta($invoice_id,"zbs_invoice_totals",true);
    $balance = 0; 
    if (isset($zbsInvoiceTotalsArr["invoice_grandt_value"])) $balance = $zbsInvoiceTotalsArr["invoice_grandt_value"];

    if($zbs_partials){
        $subtotalhide = '';
        foreach($zbs_partials as $zbs_partial){ 
            $trans_meta = get_post_meta($zbs_partial->post_id,'zbs_transaction_meta',true); 
            if (is_array($trans_meta)){
                if(array_key_exists('orderid', $trans_meta) && array_key_exists('total', $trans_meta)){ 
                    if(!in_array($trans_meta['orderid'], $gone_ids)){ 
                        $gone_ids[] = $trans_meta['orderid'];
                        $balance = $balance - $trans_meta['total'];
                    }
                }
            }

        } 
    }

    if($balance <= 0){
        $status = __('Paid','zero-bs-crm');;   //set to paid, if balance is 0
        update_post_meta($invoice_id,'zbs_customer_invoice_meta', $zbs_inv_meta);
    }

    return $status;
}


// ===============================================================================
// =======================  Tax Table Helpers ====================================
   function zeroBS___________TaxTableHelpers(){return;}

    // old alias
   	function zeroBSCRM_getTaxTableArr(){
   		return zeroBSCRM_taxRates_getTaxTableArr();
   	}

   	// retrieve tax table as array
	function zeroBSCRM_taxRates_getTaxTableArr(){

	    /* // demo/dummy data
	    return array(

	            //these will be populated based on the array
	            1  => array(
	                'id'    => 1,
	                'tax'   => 20,
	                'name'  => 'VAT'
	            ),

	            2 => array(
	                'id'    => 2,
	                'tax'   => 19,
	                'name'  => 'GST'
	            )

	        ); */

        global $ZBSCRM_t,$wpdb;

	    // for v3.0, brutal direct sql
	    $query = 'SELECT * FROM '.$ZBSCRM_t['tax'].' ORDER BY ID ASC';
        $potentialTaxRates = $wpdb->get_results($query, OBJECT);

        #} Interpret results (Result Set - multi-row)
        if (isset($potentialTaxRates) && is_array($potentialTaxRates) && count($potentialTaxRates) > 0) {

        	$res = array();

            #} Has results, tidy + return 
            foreach ($potentialTaxRates as $resDataLine) {
                        
                // tidy
                $res[] = zeroBSCRM_taxRates_tidy_taxRate($resDataLine);

            }

            return $res;
        }

        return array();

	}

     /**
     * adds or updates a taxrate object
     *
     * @param array $args Associative array of arguments
     *              id (not req.), owner (not req.) data -> key/val
     *
     * @return int line ID
     */
    function zeroBSCRM_taxRates_addUpdateTaxRate($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1,
            'owner'         => -1,

            // fields (directly)
            'data'          => array(

                'name'   => '',
                'rate'     => 0.0,
                'created'   => -1 // override date? :(
                
            )

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============


        #} ========== CHECK FIELDS ============

            $id = (int)$id;

            // if owner = -1, add current
            if (!isset($owner) || $owner === -1) $owner = zeroBSCRM_user();

        #} ========= / CHECK FIELDS ===========

        $dataArr = array( 

                            // ownership
                            // no need to update these (as of yet) - can't move teams etc.
                            //'zbs_site' => zeroBSCRM_installSite(),
                            //'zbs_team' => zeroBSCRM_installTeam(),
                            'zbs_owner' => $owner,

                            // fields
                            'zbsc_tax_name' => $data['name'],
                            'zbsc_rate' => $data['rate'],
                            'zbsc_lastupdated' => time()
                        );

        $dataTypes = array( // field data types
                            '%d',

                            '%s',
                            '%s',
                            '%d'
                        );

            if (isset($data['created']) && !empty($data['created']) && $data['created'] !== -1){
                $dataArr['zbsc_created'] = $data['created']; $dataTypes[] = '%d';
            }


        if (isset($id) && !empty($id) && $id > 0){

                #} Check if obj exists (here) - for now just brutal update (will error when doesn't exist)

                #} Attempt update
                if ($wpdb->update( 
                        $ZBSCRM_t['tax'], 
                        $dataArr, 
                        array( // where
                            'ID' => $id
                            ),
                        $dataTypes,
                        array( // where data types
                            '%d'
                            )) !== false){

                            // Successfully updated - Return id
                            return $id;

                        } else {

                            // FAILED update
                            return false;

                        }

        } else {

            // set created if not set
            if (!isset($dataArr['zbsc_created'])) {
                $dataArr['zbsc_created'] = time(); $dataTypes[] = '%d';
            }

            // add team etc
            $dataArr['zbs_site'] = zeroBSCRM_site(); $dataTypes[] = '%d';
            $dataArr['zbs_team'] = zeroBSCRM_team(); $dataTypes[] = '%d';
            
            #} No ID - must be an INSERT
            if ($wpdb->insert( 
                        $ZBSCRM_t['tax'], 
                        $dataArr, 
                        $dataTypes ) > 0){

                    #} Successfully inserted, lets return new ID
                    $newID = $wpdb->insert_id;

                    return $newID;

                } else {

                    #} Failed to Insert
                    return false;

                }

        }

        return false;

    }

     /**
     * deletes a Taxrate object
     *
     * @param array $args Associative array of arguments
     *              id
     *
     * @return int success;
     */
    function zeroBSCRM_taxRates_deleteTaxRate($args=array()){

        global $ZBSCRM_t,$wpdb;

        #} ============ LOAD ARGS =============
        $defaultArgs = array(

            'id'            => -1

        ); foreach ($defaultArgs as $argK => $argV){ $$argK = $argV; if (is_array($args) && isset($args[$argK])) {  if (is_array($args[$argK])){ $newData = $$argK; if (!is_array($newData)) $newData = array(); foreach ($args[$argK] as $subK => $subV){ $newData[$subK] = $subV; }$$argK = $newData;} else { $$argK = $args[$argK]; } } }
        #} =========== / LOAD ARGS ============

        #} Check ID & Delete :)
        $id = (int)$id;
        if (!empty($id) && $id > 0) return zeroBSCRM_db2_deleteGeneric($id,'tax');

        return false;

    }

    /**
     * tidy's the object from wp db into clean array
     *
     * @param array $obj (DB obj)
     *
     * @return array (clean obj)
     */
    function zeroBSCRM_taxRates_tidy_taxRate($obj=false){

            $res = false;

            if (isset($obj->ID)){
            $res = array();
            $res['id'] = $obj->ID;
            $res['owner'] = $obj->zbs_owner;
            
            $res['name'] = $obj->zbsc_tax_name;
            $res['rate'] = $obj->zbsc_rate;

            // to maintain old obj more easily, here we refine created into datestamp
            $res['created'] = zeroBSCRM_locale_utsToDatetime($obj->zbsc_created);
            $res['createduts'] = $obj->zbsc_created; // this is the UTS (int14)

            $res['lastupdated'] = $obj->zbsc_lastupdated;

        } 

        return $res;


    }


// ======================= / Tax Table Helpers ===================================
// ===============================================================================

// ==============================================================================================================================
// =============================== / DAL PREP FOR INVOICING v3.0 ================================================================
// ==============================================================================================================================  

// Wild west - *tumbleweed*

/* This was moved into the DAL objlayer proper
// Modified version from DAL3, only here for sake of migration routine 2->3
	// Differences:
		// Takes object model from temp migration global
		// removes $zbs->isDAL3() check
#} This takes an ID (or -1) and an obj built by zeroBS_buildObjArr 
#} .. then it verifies unqiue fields are indeed unique :)
// NOTE! Only used DAL3+, no backward compat. Is included in DAL2 Helpers, but only for sake of Migration routine.
function zeroBS_verifyObjArrUniques($objTypeID=-1,$objArr=array(),$objID=-1){

	// req.
	global $zbs;

	$checksFailed = array();

	if ($objTypeID > 0 && is_array($objArr)){

		// DEBUG echo 'Checking Unique<br>';

	    // DAL3+ we now have proper object models, so can check for 'force_unique' flags against field

	    	// get an obj model, if set
			// following written ad-hoc specifically for the v2-v3 migration (not to be used in run-of-the-mill way)
	    	$potentialModel = false;
	    	switch ($objTypeID){

	    		case ZBS_TYPE_COMPANY:
	    			global $tempCompaniesClass;
	    			$potentialModel = $tempCompaniesClass->objModel();
	    			break;

	    		case ZBS_TYPE_QUOTE:
	    			global $tempQuotesClass;
	    			$potentialModel = $tempQuotesClass->objModel();
	    			break;

	    		case ZBS_TYPE_INVOICE:
	    			global $tempInvoicesClass;
	    			$potentialModel = $tempInvoicesClass->objModel();
	    			break;

	    		case ZBS_TYPE_TRANSACTION:
	    			global $tempTransactionsClass;
	    			$potentialModel = $tempTransactionsClass->objModel();
	    			break;

	    		case ZBS_TYPE_LINEITEM:
	    			global $tempLineItemsClass;
	    			$potentialModel = $tempLineItemsClass->objModel();
	    			break;

	    		case ZBS_TYPE_ADDRESS:
	    			global $tempAddressClass;
	    			$potentialModel = $tempAddressClass->objModel();
	    			break;

	    	}

	    	// will be objlayer model if set
	    	if (is_array($potentialModel)){

				// DEBUG echo 'got model<br>';

	    		// cycle through each field verify where necessary
	    		foreach ($potentialModel as $fieldKey => $fieldDetail){

	    			// there's a few we ignore :)
	    			if (in_array($fieldKey, array('ID','zbs_site','zbs_team','zbs_owner'))) continue;


					// DEBUG echo 'Checking '.$fieldKey.'<br>';

					// verify unique fields are unique + unused
					if (isset($fieldDetail['force_unique']) && $fieldDetail['force_unique']){

							if (isset($fieldDetail['can_be_blank']) && $fieldDetail['can_be_blank'] && empty($objArr[$fieldKey])){

								// field is blank, and is allowed to be!

							} else {

								// needs to ensure field is unique.

									// get existing id, if set
							        $whereArr = array(); // colname zbsc_email
									$whereArr['uniquecheck'] = array($fieldDetail['fieldname'],'=','%s',$objArr[$fieldKey]);

							        $potentialID = $zbs->DAL->getFieldByWHERE(array(
										'objtype' => $objTypeID, // ZBS_TYPE_CONTACT
							            'colname' => 'ID',
							            'where' => $whereArr,
							            'ignoreowner' => true));

							        // DEBUG echo 'checked field: '.$fieldDetail['fieldname'].' for value "'.$objArr[$fieldKey].'"...res:'.$potentialID.'?<br>';
							        
							        // catch dupes (exists, but it's not this)
							        if ($potentialID > 0 && $potentialID != $objID){

							        	// pass back the failed field.
							        	$checksFailed[$fieldKey] = $fieldDetail;

							        }

							}

					}

	    		} // / foreach

	    	} // / if has model


	}

	// if here, success:
    if (count($checksFailed) == 0) return array(true,array());

    // else fail!
    return array(false,$checksFailed);
} */


// temporary future-proofing, this clones zeroBSCRM_getAddressCustomFields in DAL3, ahead of it's time, 
// for use with v2-v3 migration only
function zeroBSCRM_getv3AddressCustomFields($includeHidden=false){

	global $zbs;
	return $zbs->DAL->getActiveCustomFields(array('objtypeid'=>ZBS_TYPE_ADDRESS));
	
}


// updates files array
// v2-v3 migration helper, once transitioned use zeroBSCRM_files_updateFiles
function zeroBSCRM_files_updateFilesv3($fileType = '',$objID=-1,$filesArray=-1){

	global $zbs;

	$filesArrayKey = zeroBSCRM_files_key($fileType);
	
	if (!empty($filesArrayKey) && $objID > 0){


		// DAL2+
		// bit gross hard-typed, could be genericified as all is using is >DAL()->getMeta
		switch ($fileType){

			case 'customer':
			case 'contact':
				$zbs->DAL->updateMeta(ZBS_TYPE_CONTACT,$objID,'files',$filesArray);
				break;

			case 'quotes':
			case 'quote':
				$zbs->DAL->updateMeta(ZBS_TYPE_QUOTE,$objID,'files',$filesArray);
				break;

			case 'invoices':
			case 'invoice':
				$zbs->DAL->updateMeta(ZBS_TYPE_INVOICE,$objID,'files',$filesArray);
				break;

			case 'companies':
			case 'company':
				$zbs->DAL->updateMeta(ZBS_TYPE_COMPANY,$objID,'files',$filesArray);
				break;

			// no default

		}


		return $filesArray;			

	}

	return false;
}

// moves all files from one objid to another objid
// v2-v3 migration helper, once transitioned use zeroBSCRM_files_moveFilesToNewObject
function zeroBSCRM_files_moveFilesToNewObjectv3($fileType='',$oldObjID=-1,$objID=-1){

	global $zbs;

	$filesArrayKey = zeroBSCRM_files_key($fileType);
	$filesObjTypeInt = $zbs->DAL->objTypeID($fileType);

	if ($filesObjTypeInt > 0 && !empty($filesArrayKey) && $oldObjID > 0 && $objID > 0){

		// retrieve existing
		$existingFileArray = zeroBSCRM_files_getFiles($fileType,$oldObjID);

		// if has files
		if (is_array($existingFileArray)){

			// put the files into new obj:
			$x = zeroBSCRM_files_updateFilesv3($fileType,$objID,$existingFileArray);

			// delete old reference
			$zbs->DAL->deleteMeta(array(

	            'objtype'           => $filesObjTypeInt,
	            'objid'             => $oldObjID,
	            'key'               => $filesArrayKey

	        ));

	        return true;

		}

	}

	return false;
}


#} Backward compat, used by migration routine v2-v3
function zeroBSCRM_getMostRecentCPTLog($objID=false,$withFullDetails=false,$restrictToTypes=false){


			$args = array (
				'post_type'              => 'zerobs_log',
				'post_status'            => 'publish',
				'posts_per_page'         => 1,
				'order'                  => 'DESC',
				'orderby'                => 'post_date'
			);
			


			#} Of types
			if (is_array($restrictToTypes)){

				// init owner add
				$args['meta_query'] = array();
				$args['meta_query']['relation'] = 'AND';
				$args['meta_query'][] = array(
				           'key' => 'zbs_logowner',
				           'value' => (int)$objID,
				           'compare' => '='
				        );

				// each type in a NESTED group with OR relation
				$argsExtra = array();
				foreach ($restrictToTypes as $t){

					$q = 's:4:"type";s:'.strlen($t).':"'.$t.'"';
					$argsExtra[] =  array(
				           'key' => 'zbs_log_meta',
				           'value' => $q,
				           'compare' => 'LIKE'
				        );


				}

				if (count($argsExtra) > 0){

					// add relation
					$argsExtra['relation'] = 'OR';

					// add nested
					$args['meta_query'][] = $argsExtra;
				}



			} else {

				// normal way

					#} in cust
					if ($objID > 0){

					   $args['meta_key']   = 'zbs_logowner';
					   $args['meta_value'] = (int)$objID;

					} else return array(); // fail
			}
		

		$logList = get_posts( $args );

		$retObj = array();

		foreach ($logList as $logEle){

			global $zbs;

			// BECAUSE in DAL2 we moved all data into 1 object, we get meta here, but then we 'copy it' into main ret obj :)
			$retObjMeta = get_post_meta($logEle->ID, 'zbs_log_meta', true);
			$retObj = $retObjMeta;
			// if compatability support (e.g. old extensions present), also dump this into a sub-array
			// (gross, inefficient etc.)
			if (!is_array($retObj)) $retObj = array();
			if ($zbs->db1CompatabilitySupport) $retObj['meta'] = $retObjMeta;

			$retObj['id'] = $logEle->ID;
			$retObj['owner'] = get_post_meta($logEle->ID, 'zbs_logowner', true);
			$retObj['created'] = $logEle->post_date_gmt;
			$retObj['name'] = $logEle->post_title;
			$retObj['author'] = get_the_author_meta('display_name',$logEle->post_author);
			$retObj['authorid'] = $logEle->post_author;


		}



		return $retObj;
}
