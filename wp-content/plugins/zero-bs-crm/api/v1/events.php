<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 * V2.0
 *
 * Copyright 2020 Automattic
 *
 * Date: 06/04/17
 */

/* ======================================================
  Breaking Checks ( stops direct access )
   ====================================================== */
    if ( ! defined( 'ZEROBSCRM_PATH' ) ) exit;
/* ======================================================
  / Breaking Checks
   ====================================================== */

	if (!zeroBSCRM_API_is_zbs_api_authorised()){

		   #} NOPE
		   zeroBSCRM_API_AccessDenied(); 
		   exit();

	} else {

		$json_params 		= file_get_contents("php://input");
		$event_params 		= json_decode($json_params,true);

		$perPage = 10; 		if (isset($event_params['perpage'])) 	$perPage 	= (int)sanitize_text_field($event_params['perpage']);
		$page = 0; 			if (isset($event_params['page'])) 		$page 		= (int)sanitize_text_field($event_params['page']);
		$page = -1; 		if (isset($event_params['owned'])) 		$isOwned 	= (int)$event_params['owned'];
	
		$events = zeroBS_getEvents(true,$perPage,$page,$isOwned);


		#} MIKE TODO - add paging/params for get count (max 50 at a time I think) - DONE ABOVE
		#  WOODY TODO - above needs moving to the $args version you mentioned (as added isAssigned) to DAL
		echo json_encode($events);
		exit();

	}

	exit();

?>