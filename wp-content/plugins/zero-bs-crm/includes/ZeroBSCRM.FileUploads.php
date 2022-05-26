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
  Acceptible Mime Types
   ====================================================== */

	#} A list of applicable Mimetypes for file uploads
	function zeroBSCRM_returnMimeTypes(){ 
		return array(
										'pdf' => array('application/pdf'),
										'doc' => array('application/msword'),
										'docx' => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
										'ppt' => array('application/vnd.ms-powerpointtd>'),
										'pptx' => array('application/vnd.openxmlformats-officedocument.presentationml.presentation'),
										'xls' => array('application/vnd.ms-excel'),
										'xlsx' => array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
										'csv' => array('text/csv'),
										'png' => array('image/png'),
										'jpg' => array('image/jpeg'),
										'jpeg' => array('image/jpeg'),
										'gif' => array('image/gif'),
										'mp3' => array('audio/mpeg'),
										'txt' => array('text/plain'),
										'zip' => array('application/zip, application/x-compressed-zip'),
										'mp4' => array('video/mp4')
												# plus 'any'
			);
	}

/* ======================================================
  / Acceptible Mime Types
   ====================================================== */





/* ======================================================
  File Upload Related Funcs
   ====================================================== */

	// str e.g. .pdf, .xls
	function zeroBS_acceptableFileTypeListStr(){

		$ret = '';
	  
		global $zbs;

		#} Retrieve settings
		$settings = $zbs->settings->getAll();
		
		if (isset($settings['filetypesupload'])) {

			if (isset($settings['filetypesupload']['all']) && $settings['filetypesupload']['all'] == 1){

				$ret = 'All File Types';

			} else {

				foreach ($settings['filetypesupload'] as $filetype => $enabled){

					if (isset($settings['filetypesupload'][$filetype]) && $enabled == 1) {

						if (!empty($ret)) $ret .= ', ';

						$ret .= '.'.$filetype;

					}

				} 

			}

		}

		if (empty($ret)) $ret = 'No Uploads Allowed';

		return $ret;
	}

	function zeroBS_acceptableFileTypeListArr(){

		$ret = array();
	  
		global $zbs;

		#} Retrieve settings
		$settings = $zbs->settings->getAll();
		
		if (isset($settings['filetypesupload'])) 
			foreach ($settings['filetypesupload'] as $filetype => $enabled){

				if (isset($settings['filetypesupload'][$filetype]) && $enabled == 1) $ret[] = '.'.$filetype;

			} 

		return $ret;
	}

	function zeroBS_acceptableFileTypeMIMEArr(){

		$ret = array();
	  
		global $zbs;

		#} Retrieve settings
		$settings = $zbs->settings->getAll();
		
		// if all, pass that
		if (isset($settings['filetypesupload']['all']) && $settings['filetypesupload']['all'] == 1){

			return array('all'=>1);

		}
		if (isset($settings['filetypesupload'])) 

			if (isset($settings['filetypesupload']['all']) && $settings['filetypesupload']['all'] == 1){

				// add all
				foreach ($settings['filetypesupload'] as $filetype => $enabled){

					$ret[] = $zbs->acceptable_mime_types[$filetype][0];

				}

			} else {

				// individual

				foreach ($settings['filetypesupload'] as $filetype => $enabled){

					if (isset($settings['filetypesupload'][$filetype]) && $enabled == 1) $ret[] = $zbs->acceptable_mime_types[$filetype][0];

				}

			}

		return $ret;
	}



	#} removes a link to file (quote, invoice, other)
	// not always customer id... sometimes inv/co etc.
	function zeroBS_removeFile($objectID=-1,$fileType='',$fileURL=''){

	  	if ( current_user_can( 'admin_zerobs_customers' ) ) {   //only admin can do this too (extra security layer)

	  		global $zbs;

			if ($objectID !== -1 && !empty($fileURL)){
				
				/* centralised into zeroBSCRM_files_getFiles
				switch ($fileType){

					case 'customer':

						$filesArrayKey = 'zbs_customer_files';

						break;
					case 'quotes':

						$filesArrayKey = 'zbs_customer_quotes';

						break;
					case 'invoices':

						$filesArrayKey = 'zbs_customer_invoices';

						break;
				} */

				#} good?
				// zeroBSCRM_files_getFiles if (isset($filesArrayKey)){
				if (in_array($fileType, array('customer','quotes','invoices','company'))){

					#} First remove list reference:

						#} any change?
						$changeFlag = false; $fileObjToDelete = false;

						#} Load files arr

						/* centralised into zeroBSCRM_files_getFiles
						// for DAL1 contacts + quotes/invs:
						if (!$zbs->isDAL2() || $filesArrayKey == 'zbs_customer_quotes' || $filesArrayKey == 'zbs_customer_invoices') // DAL1
							$filesList = get_post_meta($objectID, $filesArrayKey, true);
						else // DAL2
							$filesList = $zbs->DAL->contacts->getContactMeta($objectID,'files');
						*/
						$filesList = zeroBSCRM_files_getFiles($fileType,$objectID);


						if (is_array($filesList) && count($filesList) > 0){

							#} defs
							$ret = array();
							
							#} Cycle through and remove any with this url - lame, but works for now
							foreach ($filesList as $fileObj){

								if ($fileObj['url'] != $fileURL) 
									$ret[] = $fileObj;
								else {
									$fileObjToDelete = $fileObj;
									$changeFlag = true;

									// also, if the removed file(s) are logged in any slots, clear the slot :)
    								$slot = zeroBSCRM_fileslots_fileSlot($fileObj['file'],$objectID,ZBS_TYPE_CONTACT);
    								if ($slot !== false && !empty($slot)){
										zeroBSCRM_fileslots_clearFileSlot($slot,$objectID,ZBS_TYPE_CONTACT);
									}
								}

							}

							if ($changeFlag) {

								/* zeroBSCRM_files_updateFiles 
								// for DAL1 contacts + quotes/invs:
								if (!$zbs->isDAL2() || $filesArrayKey == 'zbs_customer_quotes' || $filesArrayKey == 'zbs_customer_invoices') // DAL1
									update_post_meta($objectID,$filesArrayKey,$ret);
								else // DAL2
									$zbs->DAL->updateMeta(ZBS_TYPE_CONTACT,$objectID,'files',$ret);
								*/
								zeroBSCRM_files_updateFiles($fileType,$objectID,$ret);

							}

						} #} else w/e

					#} Then delete actual file ... 
					if ($changeFlag && isset($fileObjToDelete) && isset($fileObjToDelete['file'])){

						#} Brutal 
						#} #recyclingbin
						if (file_exists($fileObjToDelete['file'])) {

							#} Delete
							unlink($fileObjToDelete['file']);

							#} Check if deleted:
							if (file_exists($fileObjToDelete['file'])){

								// try and be more forceful:
								chmod($fileObjToDelete['file'], 0777);
								unlink(realpath($fileObjToDelete['file']));

								if (file_exists($fileObjToDelete['file'])){
									
									// tone down perms, at least
									chmod($fileObjToDelete['file'], 0644);

									// add message
									return __('Could not delete file from server:','zero-bs-crm').' '.$fileObjToDelete['file'];

								}

							}

						}

					}

					return true;

				}


			}

		} #} / can manage options


		return false;
	}

  

/* ======================================================
  	File Upload related funcs
   ====================================================== */

   function zeroBSCRM_privatiseUploadedFile($fromPath='',$filename=''){

   		#} Check dir created
   		$currentUploadDirObj = zeroBSCRM_privatisedDirCheck();
		if (is_array($currentUploadDirObj) && isset($currentUploadDirObj['path'])){ 
			$currentUploadDir = $currentUploadDirObj['path'];
			$currentUploadURL = $currentUploadDirObj['url'];
		} else {
			$currentUploadDir = false;
			$currentUploadURL = false;
		}

   		if (!empty($currentUploadDir)){

   			// generate a safe name + check no file existing
   			// this is TEMP code to be rewritten on formally secure file sys WH
   			$filePreHash = md5($filename.time());
   			// actually limit to first 16 chars is plenty
   			$filePreHash = substr($filePreHash,0,16);
   			$finalFileName = $filePreHash.'-'.$filename;
   			$finalFilePath = $currentUploadDir.'/'.$finalFileName;

   			// check exists, deal with (unlikely) dupe names
   			$c = 1;
   		 	while (file_exists($finalFilePath) && $c < 50){

   		 		// remake
   				$finalFileName = $filePreHash.'-'.$c.'-'.$filename;
   		 		$finalFilePath = $currentUploadDir.'/'.$finalFileName;

   		 		// let it roll + retest
   		 		$c++;
   		 	}

   			if (rename($fromPath.'/'.$filename,$finalFilePath)){

   				// moved :)

   				// check perms?
   				/* https://developer.wordpress.org/reference/functions/wp_upload_bits/
			    // Set correct file permissions
			    $stat = @ stat( dirname( $new_file ) );
			    $perms = $stat['mode'] & 0007777;
			    $perms = $perms & 0000666;
			    @ chmod( $new_file, $perms );
			    */

			    $endPath = $finalFilePath;
			    // this url is temp, it should be fed via php later.
			    $endURL = $currentUploadURL.'/'.$finalFileName;


   				// the caller-func needs to remove/change data/meta :)
   				return array('file'=>$endPath,'url'=>$endURL);

   			} else {

   				// failed to move
   				return false;
   			}


   		}

   		return false; // couldn't - no dir to move to :)


   }

function zeroBSCRM_privatisedDirCheck( $echo = false ) {

	$wp_uploads_dir = wp_upload_dir();

	// JetpackRebrandRelook = this is public facing on the System page. Will editing this break anything?
	$private_dir_name = 'zbscrm-store';

	if ( !empty( $wp_uploads_dir['basedir'] ) ) {

		$final_dir_path = $wp_uploads_dir['basedir'] . '/' . $private_dir_name;
		$final_url = $wp_uploads_dir['baseurl'] . '/' . $private_dir_name;

		// check existence
		if ( !is_dir( $final_dir_path ) ) {

			// doesn't exist, attempt to create
			mkdir( $final_dir_path, 0755, true );
			// force perms?
			chmod( $final_dir_path, 0755 );

		}

		if ( is_dir( $final_dir_path ) ) {
			jpcrm_secure_directory_from_external_access( $final_dir_path, false );
			return array( 'path' => $final_dir_path, 'url' => $final_url );
		}

	}

	return false;

}

// 2.95.5+ we also add a subdir for 'work' (this is used by CPP when making thumbs, for example)
function zeroBSCRM_privatisedDirCheckWorks( $echo = false ) {

	$wp_uploads_dir = wp_upload_dir();
	$private_dir_name = 'zbscrm-store/_wip';

	if ( !empty( $wp_uploads_dir['basedir'] ) ) {

		$final_dir_path = $wp_uploads_dir['basedir'] . '/' . $private_dir_name;
		$final_url = $wp_uploads_dir['baseurl'] . '/' . $private_dir_name;

		// check existence
		if ( !is_dir( $final_dir_path ) ) {

			// doesn't exist, attempt to create
			mkdir( $final_dir_path, 0755, true );
			// force perms?
			chmod( $final_dir_path, 0755 );

		}

		if ( is_dir( $final_dir_path ) ) {
			jpcrm_secure_directory_from_external_access( $final_dir_path, false );
			return array( 'path' => $final_dir_path, 'url' => $final_url );
		}

	}

	return false;

}



/* ======================================================
  / File Upload related funcs
   ====================================================== */
   
/* ======================================================
  File Slots helpers
   ====================================================== */

   function zeroBSCRM_fileSlots_getFileSlots($objType=1){

   		global $zbs;

   		$fileSlots = array();

        $settings = zeroBSCRM_getSetting('customfields'); $cfbInd = 1;

        switch ($objType){

        	case 1:

		        if (isset($settings['customersfiles']) && is_array($settings['customersfiles']) && count($settings['customersfiles']) > 0){

			         foreach ($settings['customersfiles'] as $cfb){

			            $cfbName = ''; if (isset($cfb[0])) $cfbName = $cfb[0];
			         	$key = $zbs->DAL->makeSlug($cfbName); // $cfbInd
			            if (!empty($key)){
			            	$fileSlots[] = array('key'=>$key,'name'=>$cfbName);
			            	$cfbInd++;
			            }

			        }

		    	}

		    break;

		}

    	return $fileSlots;
   }

   // returns the slot (if assigned) of a given file
   function zeroBSCRM_fileslots_fileSlot($file='',$objID=-1,$objType=1){

   		// get all slotted files for contact/obj
   	
   		if ($objID > 0 && !empty($file)){

   			global $zbs;
   			$fileSlots = zeroBSCRM_fileslots_allSlots($objID,$objType);
   			// cycle through
   			if (count($fileSlots) > 0){

   				foreach ($fileSlots as $fsKey => $fsFile){

   					if ($fsFile == $file) return $fsKey;

   				}

   			}


   		}
   		return false;
   }


   // returns all slots (if assigned) of a given obj(contact)
   function zeroBSCRM_fileslots_allSlots($objID=-1,$objType=1){

   		if ($objID > 0){

   			global $zbs;
   			$fileSlots = zeroBSCRM_fileSlots_getFileSlots(ZBS_TYPE_CONTACT);
   			$ret = array();
   			if (count($fileSlots) > 0){

   				foreach ($fileSlots as $fs){

   					$ret[$fs['key']] = zeroBSCRM_fileslots_fileInSlot($fs['key'],$objID,$objType);

   				}

   			}
   			return $ret;
   	
   		}
   		return false;
   }

   // returns a file for a slot
   function zeroBSCRM_fileslots_fileInSlot($fileSlot='',$objID=-1,$objType=1){

   		if ($objID > 0){

   			global $zbs;

   			return $zbs->DAL->meta($objType,$objID,'cfile_'.$fileSlot);

   		}
   		return false;
 
   }

   // adds a file to a slot
   function zeroBSCRM_fileslots_addToSlot($fileSlot='',$file='',$objID=-1,$objType=1,$overrite=false){

   		if ($objID > 0){

   			//echo '<br>zeroBSCRM_fileslots_addToSlot '.$fileSlot.' '.$file.' '.$objID.' ext:'.zeroBSCRM_fileslots_fileInSlot($fileSlot,$objID).'!';

   			global $zbs;

	   		// check existing?
	   		if (!$overrite){
	   			$existingFile = zeroBSCRM_fileslots_fileInSlot($fileSlot,$objID);
	   			if (!empty($existingFile)) return false;
	   		} else {

	   			// overrite... so remove any if present before..
	   			zeroBSCRM_fileslots_clearFileSlot($fileSlot,$objID,$objType);
	   		}

	        // DAL2 add via meta (for now)
	        $zbs->DAL->updateMeta($objType,$objID,'cfile_'.$fileSlot,$file);
	        return true;

	    }

	    return false;

   	
   }

   function zeroBSCRM_fileslots_clearFileSlot($fileSlot='',$objID=-1,$objType=1){

   		if ($objID > 0){

   			global $zbs;
			return $zbs->DAL->deleteMeta(array(
						'objtype' 			=> $objType,
						'objid' 			=> $objID,
						'key'	 			=> 'cfile_'.$fileSlot
			   		));

		}

		return false;
   }


   function zeroBSCRM_files_baseName($filePath='',$privateRepo=false){

   		$file = '';
   		if (!empty($filePath)){


		    $file = basename($filePath);
		    if ($privateRepo) $file = substr($file,strpos($file, '-')+1);

   		}

   		return $file;

   }

/* ======================================================
  / File Slots helpers
   ====================================================== */