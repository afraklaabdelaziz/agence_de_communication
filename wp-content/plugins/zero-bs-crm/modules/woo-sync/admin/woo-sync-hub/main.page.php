<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 *
 * WooSync: Admin: Hub page
 *
 */

// block direct access
defined( 'ZEROBSCRM_PATH' ) || exit;

/**
 * Page: WooSync Hub
 */
function jpcrm_woosync_render_hub_page(){

	global $zbs;

	// intercept for attempting restart of initial sync
	if ( isset( $_GET['restart_sync'] ) ){


		// Show message, are you sure?
		$html = '<p>' . __( "This will restart syncing your WooCommerce orders from scratch, using your current settings.", 'zero-bs-crm' ) . '</p>';
		$html .= '<p>' . __( "This will not remove any existing orders or data, but it will update objects if they're reimported and have since changed.", 'zero-bs-crm' ) . '</p>';
		$html .= '<p><a href="' . zbsLink( $zbs->modules->woosync->slugs['hub'] . '&definitely_restart_sync=1' ) . '" class="ui button teal">' . __( 'Yes, Restart Sync.', 'zero-bs-crm' ) . '</a>&nbsp;&nbsp;<a href="' . zbsLink( $zbs->modules->woosync->slugs['hub'] ) . '" class="ui button red">' . __( 'Cancel and go back to Hub', 'zero-bs-crm' ) . '</a></p>';



		echo zeroBSCRM_UI2_messageHTML( 'warning', __( 'Want to restart your sync?', 'zero-bs-crm' ), $html, 'info' );
		exit();

	}

	// intercept for actual restart of initial sync
	if ( isset( $_GET['definitely_restart_sync'] ) ){

		// mark that we've 'not completed first import'
		$zbs->modules->woosync->background_sync->set_import_first_import_status( false );
		$zbs->modules->woosync->background_sync->set_resume_from_page( 0 );

		// output
		$sync_restarted = true;

	}

	// intercept for debug, if we have $_GET['debug_sync'], call that
	if ( isset( $_GET['debug_sync'] ) ){
		
		// render debug mode sync page
		jpcrm_woosync_render_hub_page_debug_mode();
		exit();

	}

	$settings = $zbs->modules->woosync->settings->getAll();
	$settings_page_url    = zbsLink( $zbs->slugs['settings'] . '&tab=' . $zbs->modules->woosync->slugs['settings'] );

	// retrieve status details
	$status = 'local_ready';
	$fire_ajax_run = false; // if true this page will spawn a child process to import via ajax (works alongside cron by leaving this page open)
	$total_order_count = -1;
	$contacts_synced_count = $zbs->modules->woosync->get_crm_woo_contact_count();
	$transactions_synced_count = $zbs->modules->woosync->get_crm_woo_transaction_count();
	$total_synced_value = $zbs->modules->woosync->get_crm_woo_transaction_total();
	$latest_synced_order = $zbs->modules->woosync->get_crm_woo_latest_woo_transaction();

	// mode prep
	$woo_connect_local_store = true;

	// external
	if ( $settings['wcsetuptype'] == 1 ){
	
		$woo_connect_local_store = false;

		// vars
		$domain          = $settings['wcdomain'];
		$key             = $settings['wckey'];
		$secret          = $settings['wcsecret'];
		$prefix          = $settings['wcprefix'];

		// confirm settings
		if ( $domain == '' || $key == '' || $secret == '' ){
			
			$status = 'external_no_settings';
			$has_external_settings = false;

			// if no prefix, alert
			if ( empty( $prefix ) ){

				$external_but_no_prefix = true;

			}

		} else {

			$status = 'external_ready';
			$has_external_settings = true;

		}

	} else {

		// local install

		// verify woo installed
		if ( !$zbs->woocommerce_is_active() ){

			$status = 'local_no_woocommerce';

		} else {

			// get counts
			$total_order_count = $zbs->modules->woosync->get_order_count_via_local_store();

			if ( $total_order_count == 0 ){

				$status = 'local_no_orders';

			}

		}


	}

	// retrieve sync status
	$completed_first_import = $zbs->modules->woosync->background_sync->first_import_completed();
	$resume_from_page = $zbs->modules->woosync->background_sync->resume_from_page();

	// last mods to $status, where in 'ready' state
	if ( in_array( $status, array( 'external_ready', 'local_ready' ) ) ) {

		// has completed import
		if ( $completed_first_import ){


			if ( $woo_connect_local_store ){

				$status = 'local_up_to_date';

			} else {

				$status = 'external_up_to_date';

			}

		} else {

			// has not yet completed import

			if ( $woo_connect_local_store ){

				$status = 'local_ready_incomplete';

			} else {

				$status = 'external_ready_incomplete';

			}

		}

	}

	// shorthand
	$settings_cog_html = '<a href="' . $settings_page_url . '" title="' . __( "Change Settings", 'zero-bs-crm' ) . '" target="_blank"><i class="cog icon"></i></a>';
	$settings_cog_button_html = '<a href="' . $settings_page_url . '" title="' . __( "Change Settings", 'zero-bs-crm' ) . '" target="_blank" class="ui right floated jpcrm-woosync-settings-button"><i class="cog icon"></i>' . __( "WooSync Settings", 'zero-bs-crm' ) . '</a>';

?>
<div id="jpcrm-woosync-hub-page">
	<div id="jpcrm-woo-logo">
		<img id="jpcrm-woosync-jpcrm-logo" src="<?php echo ZEROBSCRM_URL; ?>i/jpcrm-logo-horizontal-black.png" alt="" />
		<i class="plus icon"></i>
		<img id="jpcrm-woosync-woo-logo" src="<?php echo ZEROBSCRM_URL; ?>i/woocommerce-logo-color-black@2x.png" alt="" />
	</div>
	<?php 

	// any debug/messages?
	if ( isset( $sync_restarted ) ){

		?><div id="jpcrm-woosync-messages"><?php

		echo zeroBSCRM_UI2_messageHTML( 'info', __( "Sync restarted", 'zero-bs-crm' ), __( "The WooSync import has been restarted. This will start running in the background from the beginning.", 'zero-bs-crm' ) );

		?></div><?php

	}

	?>
	<div class="ui segment" id="jpcrm-woosync-page-body">
		<div>
			<div class="jpcrm-woosync-stats-header">
			</div>
			<?php 

				// catch various stumbling blocks and present state, where constructive:
				switch ( $status ){

					// external site mode but no settings?
					case 'external_no_settings':				
						echo zeroBSCRM_UI2_messageHTML( 'warning', '', sprintf( __( "You have not setup your WooCommerce API details. Please fill in your API details on the <a href=\"%s\" target=\"_blank\">settings page</a>. (WooCommerce Legacy API required)", "zero-bs-crm" ), $settings_page_url ) );
						break;

					// local site but no woocommerce:
					case 'local_no_woocommerce':
						echo zeroBSCRM_UI2_messageHTML( 'warning', '', __( "You do not have WooCommerce installed. Please install WooCommerce to use this service.", 'zero-bs-crm' ) );
						break;

					// local site but no orders to import
					case 'local_no_orders':
						echo zeroBSCRM_UI2_messageHTML( 'info', '', __( "You have no WooCommerce orders to import. As orders get added, they'll automatically get imported! (Nothing to do here.)", 'zero-bs-crm' ) );
						break;

				}

				// When we're in external mode but we have no prefix, alert
				if ( isset( $external_but_no_prefix ) ){

					echo zeroBSCRM_UI2_messageHTML( 'info', '', sprintf( __( "You are set up to import from an external site, but you have not set an 'order prefix'. This is recommended so that orders from external sites do not clash with local/other site orders. Please add a prefix on the <a href=\"%s\" target=\"_blank\">settings page</a>.", 'zero-bs-crm' ), $settings_page_url ) );

				}

			?>
		</div>
		<h2 class="ui header">
			<i class="icon<?php

				// status -> icon & colour
				switch ( $status ) {

					// local site, completed first import
					// external site, completed first import
				// local site but no orders to import
					case 'local_up_to_date':
					case 'external_up_to_date':
					case 'local_no_orders':
						echo ' thumbs up green';
						break;

					// local site, still working on first import
					// external site, still working on first import
					case 'local_ready_incomplete':
					case 'external_ready_incomplete':
						echo ' hourglass half green';
						break;

					// external site mode but no settings?
					// local site but no woocommerce:
					case 'external_no_settings':
					case 'local_no_woocommerce':
						echo ' settings orange';
						break;

				}

			?>"></i>
			<div class="content">
				<?php
				_e( 'Status: ', 'zero-bs-crm' );

				$sub_header_recap = '';
				$sub_header_text  = __( 'Nothing to do here, WooSync will continue to add orders as they come in.', 'zero-bs-crm' );

				$sub_header_recap .= __( 'Setup Type:', 'zero-bs-crm' ) . ' ';
				// output mode
				if ( $woo_connect_local_store ) {
					$sub_header_recap .= __( 'Same site (Local WooCommerce store)', 'zero-bs-crm' );
				} else {
					$sub_header_recap .= __( 'External site', 'zero-bs-crm' );
					// if domain setting, show site
					if ( ! empty( $domain ) ) {
						$sub_header_recap .= ' - ' . __( 'Domain: ', 'zero-bs-crm' ) . $domain;
					} else {
						$sub_header_recap .= ' ' . __( '(No site specified)', 'zero-bs-crm' );
					}
				}

				if ( is_array( $latest_synced_order ) ) {
					$sub_header_recap .= '<br />';
					$origin_str        = '';
					$external_sources  = $zbs->DAL->getExternalSources(
						-1,
						array(
							'objectID'          => $latest_synced_order['id'],
							'objectType'        => ZBS_TYPE_TRANSACTION,
							'grouped_by_source' => true,
							'ignoreowner'       => zeroBSCRM_DAL2_ignoreOwnership( ZBS_TYPE_CONTACT ),
						)
					);
					if ( isset( $external_sources['woo'] ) ) {
							$origin_detail = $zbs->DAL->hydrate_origin( $external_sources['woo'][0]['origin'] );
							$clean_domain  = $zbs->DAL->clean_external_source_domain_string( $origin_detail['origin'] );
							$origin_str    = __( ' from ', 'zero-bs-crm' ) . $clean_domain;
					}
					// build a 'latest order' string
					$sub_header_recap .= __( 'Last imported order: ', 'zero-bs-crm' ) . '<a href="' . zbsLink( 'edit', $latest_synced_order['id'], ZBS_TYPE_TRANSACTION ) . '" target="_blank">#' . $latest_synced_order['id'] . '</a> ';
					if ( !empty( $latest_synced_order['title'] ) ) {

						$sub_header_recap .= '- ' . $latest_synced_order['title'] . ' ';

					}
					if ( !empty( $latest_synced_order['date_date'] ) ) {

						$sub_header_recap .= '(' . $latest_synced_order['date_date'] . ') ';

					}
					$sub_header_recap .= $origin_str;
				}

				// display status str
				switch ( $status ) {

					// local site, completed first import
					// external site, completed first import
					case 'local_up_to_date':
					case 'external_up_to_date':
						echo '<span class="status green">' . __( 'Up to date.', 'zero-bs-crm' ) . '</span>';
						break;

					// local site but no orders to import
					case 'local_no_orders':
						echo '<span class="status green">' . __( 'Ready.', 'zero-bs-crm' ) . '</span>';
						$sub_header_text = __( 'WooSync will add orders as they come in.', 'zero-bs-crm' );
						break;

					// local site, still working on first import
					// external site, still working on first import
					case 'local_ready_incomplete':
					case 'external_ready_incomplete':
						echo '<span class="status green">' . __( 'Working on first import.', 'zero-bs-crm' ) . '</span>';
						// and we can give it a kick from this page
						$fire_ajax_run = true;
						// and set the subheader to reflect state
						$sub_header_text = __( 'WooSync is still importing orders...', 'zero-bs-crm' );
						break;

					// external site mode but no settings?
					case 'external_no_settings':
						echo '<span class="status orange">' . __( 'Setup required.', 'zero-bs-crm' ) . '</span> ' . $settings_cog_html;
						$sub_header_text = __( 'WooSync will start importing data when you have updated your settings.', 'zero-bs-crm' );
						break;

					// local site but no woocommerce:
					case 'local_no_woocommerce':
						echo '<span class="status orange">' . __( 'Missing WooCommerce.', 'zero-bs-crm' ) . '</span>';
						$sub_header_text = __( 'WooSync will start importing data when you have installed WooCommerce.', 'zero-bs-crm' );
						break;

				}

				// if still stuff to do, spawn a child process via ajax call to keep things moving
				if ( $fire_ajax_run ) {

					?>
					<script>
						var jpcrm_woo_connect_initiate_ajax_sync = true;
						var jpcrm_woosync_nonce = '<?php echo wp_create_nonce( 'jpcrm_woosync_hubsync' ); ?>';
						var jpcrm_woosync_post_completion_url = '<?php echo zbsLink( $zbs->modules->woosync->slugs['hub'] ); ?>';
					</script>
					<div class="ui inline loader" id="jpcrm_firing_ajax" title="<?php _e( 'Keeping this page open will improve the background sync speed.', 'zero-bs-crm' ); ?>"></div>
					<i class="green clipboard check icon" id="jpcrm_page_complete_ico" style="display:none"></i>
					<div id="jpcrm_failed_ajax" style="display:none"><i class="grey exclamation circle icon"></i> <span></span></div>
					<?php

				}

				?>
				<div class="sub header">
					<?php

					// e.g. "Last imported order: #1080 - A Website (2022-04-04 09:54:11)"
					if ( !empty( $sub_header_recap ) ) {

						echo '<p class="jpcrm-woosync-recap">' . $sub_header_recap . '</p>';

					}

					// e.g. "Nothing to do here, WooSync will continue to add orders as they come in."
					echo '<p class="wp-die-message">' . $sub_header_text . '</p>';

					?>
				</div>
			</div>
		</h2>

		<div id="jpcrm-woo-stats" class="ui">
			<?php if ( $contacts_synced_count < 1 && ( $status === 'external_no_settings' || $status === 'local_no_woocommerce' ) ) { ?>
			<div id="jpcrm-woo-stats-nothing-yet" class="ui active dimmer">
				<div>
					<p><?php _e( "You don't have any data synced from WooCommerce yet.", 'zero-bs-crm' ); ?></p>
					<p>
						<a href="<?php echo $settings_page_url; ?>" target="_blank" class="ui small button">
							<i class="cog icon"></i> 
							<?php _e("Change Settings","zero-bs-crm"); ?>
						</a>
						<?php ##WLREMOVE ?> 
						<a href="<?php echo $zbs->urls['kb-woosync-home']; ?>" target="_blank" class="ui small blue button">
							<i class="file text outline icon"></i> 
							<?php _e("Visit Setup Guide","zero-bs-crm"); ?>
						</a>
						<!-- <a href="<?php echo $zbs->urls['youtube_intro_to_woosync']; ?>" target="_blank" class="ui small red button">
							<i class="play circle outline icon"></i>
							<?php _e("Watch YouTube Guide","zero-bs-crm"); ?>
						</a> -->
						<?php ##/WLREMOVE ?> 
					</p>
				</div>
			</div>
			<?php } ?>
			<div class="ui grid" id="jpcrm-woosync-stats-container">
			  <div class="five wide column">
			  	<div class="jpcrm-woosync-stat ui inverted segment blue">
			  		<div class="jpcrm-woosync-stat-container jpcrm-clickable" data-href="<?php echo zeroBSCRM_getAdminURL( $zbs->slugs['managecontacts'] . '&quickfilters=woo_customer' ); ?>"<?php

			  		// basic style scaling for large numbers. 
			  		// On refining this hub page we should rethink
			  		if ( strlen( $contacts_synced_count ) > 8){

			  			// millions
			  			echo ' style="font-size:2.6em;"';

			  		} elseif ( strlen( $contacts_synced_count ) > 12){

			  			// billions
			  			echo ' style="font-size:1.5em;"';
			  			
			  		}

			  		?>>
			  			<i class="user circle icon"></i><br />
					  	<?php echo zeroBSCRM_prettifyLongInts( $contacts_synced_count ); ?>
					  	<div class="jpcrm-woosync-stat-label"><?php _e( "Contacts", 'zero-bs-crm' ); ?></div>
					</div>
				</div>
			  </div>
			  <div class="five wide column">
			  	<div class="jpcrm-woosync-stat ui inverted segment blue">
			  		<div class="jpcrm-woosync-stat-container jpcrm-clickable" data-href="<?php echo zeroBSCRM_getAdminURL( $zbs->slugs['managetransactions'] . '&quickfilters=woo_transaction' ); ?>"<?php

			  		// basic style scaling for large numbers. 
			  		// On refining this hub page we should rethink
			  		if ( strlen( $transactions_synced_count ) > 8){

			  			// millions
			  			echo ' style="font-size:2.6em;"';

			  		} elseif ( strlen( $transactions_synced_count ) > 12){

			  			// billions
			  			echo ' style="font-size:1.5em;"';
			  			
			  		}

			  		?>>
			  			<i class="exchange icon"></i><br />
					  	<?php echo zeroBSCRM_prettifyLongInts( $transactions_synced_count ); ?>
					  	<div class="jpcrm-woosync-stat-label"><?php _e( "Transactions", 'zero-bs-crm' ); ?></div>
					</div>
				</div>
			  </div>
			<div class="five wide column">
			  	<div class="jpcrm-woosync-stat ui inverted segment blue">
			  		<div class="jpcrm-woosync-stat-container"<?php

			  		// basic style scaling for large numbers. 
			  		// On refining this hub page we should rethink
			  		if ( strlen( $total_synced_value ) > 8){

			  			// millions
			  			echo ' style="font-size:2.6em;"';

			  		} elseif ( strlen( $total_synced_value ) > 12){

			  			// billions
			  			echo ' style="font-size:1.5em;"';

			  		}

			  		?>>
					<i class="money bill alternate icon"></i><br />
					  	<?php echo zeroBSCRM_formatCurrency( $total_synced_value ); ?>
					  	<div class="jpcrm-woosync-stat-label"><?php _e( "WooCommerce Transaction Total", 'zero-bs-crm' ); ?></div>
					</div>
				</div>
			  </div>
			</div>
		</div>
	</div>

	<div id="jpcrm-woosync-quiet-restart-link">
		<?php _e( "Admin Tools:", 'zero-bs-crm' ); 

		// settings link
		if ( zeroBSCRM_isZBSAdminOrAdmin() ) {
			?> <a href="<?php echo $settings_page_url; ?>"><?php _e( 'WooSync Settings', 'zero-bs-crm' ); ?></a> <?php
		}

		// show restart only when not already run
		if ( $completed_first_import ){ 

			?>| <a href="<?php echo zbsLink( $zbs->modules->woosync->slugs['hub'] . '&restart_sync=1' ); ?>"><?php _e( 'Restart Sync', 'zero-bs-crm' ); ?></a> <?php
		
		} ?>| <a href="<?php echo zbsLink( $zbs->modules->woosync->slugs['hub'] . '&debug_sync=1' ); ?>"><?php _e( 'Run Sync debug', 'zero-bs-crm' ); ?></a>
	</div>
</div>
<?php jpcrm_woosync_output_language_labels(); ?>


<?php
}

/*
* Output <script> JS to pass language labels to JS
*
* @param $additional_labels - array; any key/value pairs here will be expressed in the JS label var
*/
function jpcrm_woosync_output_language_labels( $additional_labels = array() ){

	// specify default (generic) labels
	$language_labels = array_merge( array(

		'ajax_fail'             => __( "Failed retrieving data.", 'zero-bs-crm' ),
		'complete'              => __( "Completed Sync.", 'zero-bs-crm' ),
		'remaining_pages'       => __( "{0} remaining pages.", 'zero-bs-crm' ),
		'caught_mid_job'        => __( "Import job is running in the back end. If this message is still shown after some time, please contact support.", 'zero-bs-crm' ),
		'server_error'          => __( "There was a general server error.", 'zero-bs-crm' ),

		'incomplete_nextpage'   => __( "Completed page. Next: page {0} of {1} pages. ({2})", 'zero-bs-crm' ),
		'complete_lastpage'     => __( "Completed last page, (page {0} of {1} pages)", 'zero-bs-crm' ),
		'debug_return'          => __( "Return: {0}", 'zero-bs-crm' ),
		'retrieving_page'       => __( "Retrieving page {0}", 'zero-bs-crm' ),

	), $additional_labels );


	?><script>var jpcrm_woosync_language_labels = <?php echo json_encode( $language_labels ); ?></script><?php

}


/**
 * Styles and scripts for hub page
 */
function jpcrm_woosync_hub_page_styles_scripts(){

	global $zbs;	
	wp_enqueue_script( 'jpcrm-woo-sync', plugins_url( '/js/jpcrm-woo-sync-hub-page'.wp_scripts_get_suffix().'.js', JPCRM_WOO_SYNC_ROOT_FILE ), array( 'jquery' ), $zbs->modules->woosync->version );
	wp_enqueue_style( 'jpcrm-woo-sync-hub-page', plugins_url( '/css/jpcrm-woo-sync-hub-page'.wp_scripts_get_suffix().'.css', JPCRM_WOO_SYNC_ROOT_FILE ) );

}


/**
 * Run a sync in debug mode:
 */
function jpcrm_woosync_render_hub_page_debug_mode(){

	global $zbs;

	?><div id="jpcrm-woosync-hub-page">
		<div id="jpcrm-woo-logo">
			<img id="jpcrm-woosync-jpcrm-logo" src="<?php echo ZEROBSCRM_URL; ?>i/jpcrm-logo-horizontal-black.png" alt="" />
			<i class="plus icon"></i>
			<img id="jpcrm-woosync-woo-logo" src="<?php echo ZEROBSCRM_URL; ?>i/woocommerce-logo-color-black@2x.png" alt="" />
		</div>
		<div class="ui segment" id="jpcrm-woosync-page-body">
			<h2>Debug Mode:</h2>

			<div id="jpcrm-woosync-debug-output">
			<?php

				// set debug
				$zbs->modules->woosync->background_sync->debug = true;

				// call job function
				$zbs->modules->woosync->background_sync->sync_orders();

			?></div>
		</div>
		<p style="text-align: center;margin-top:2em"><a href="<?php echo zbsLink( $zbs->modules->woosync->slugs['hub'] ) ?>" class="ui button green"><?php _e( 'Go back to WooSync Hub', 'zero-bs-crm' ); ?></a>
	</div><?php

}