<?php

// block direct access
if ( ! defined( 'ZEROBSCRM_PATH' ) ) exit;

// Check the access
$authorized = zeroBSCRM_API_is_zbs_api_authorised();

if ( $authorized ) {
	$reply = array(
		'is_error'    => false,
		'message'     => 'Success',
		'status_code' => 200,
	);
} else {
	$reply = array(
		'is_error'    => true,
		'message'     => 'Failed',
		'status_code' => 403,
	);
}

wp_send_json( $reply, $reply['status_code'] );
