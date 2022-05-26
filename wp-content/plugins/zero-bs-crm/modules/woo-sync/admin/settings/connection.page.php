<?php
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 *
 * WooSync: Admin: Settings page
 *
 */
namespace Automattic\JetpackCRM;

// block direct access
defined( 'ZEROBSCRM_PATH' ) || exit;

/**
 * Page: WooSync Settings
 */
function jpcrm_settings_page_html_woosync_connection() {

	global $zbs;

	$settings = $zbs->modules->woosync->settings->getAll();

	$zbs_customer_statuses = zeroBSCRM_getCustomerStatuses();

	// Act on any edits!
	if ( isset( $_POST['editwplf'] ) ) {

		// Retrieve
		$updatedSettings = array();

		//wcsetuptype
		$updatedSettings['wcsetuptype'] = ( !empty( $_POST['wpzbscrm_wcsetuptype'] ) ? JPCRM_WOO_SYNC_MODE_API : $JPCRM_WOO_SYNC_MODE_LOCAL );

		//API settings
		$updatedSettings['wcdomain'] = ( !empty($_POST['wcdomain'] ) ? sanitize_text_field( $_POST['wcdomain'] ) : 0 );
		$updatedSettings['wckey']    = ( !empty($_POST['wckey'] ) ? sanitize_text_field( $_POST['wckey'] ) : 0 );
		$updatedSettings['wcsecret'] = ( !empty($_POST['wcsecret'] ) ? sanitize_text_field( $_POST['wcsecret'] ) : 0 );
		$updatedSettings['wcprefix'] = ( !empty($_POST['wcprefix'] ) ? sanitize_text_field( $_POST['wcprefix'] ) : 0 );

		#} Brutal update
		foreach ( $updatedSettings as $k => $v ) {
			$zbs->modules->woosync->settings->update( $k, $v );
		}

		// $msg out!
		$sbupdated = true;

		// Reload
		$settings = $zbs->modules->woosync->settings->getAll();

	}

	// Show Title
	jpcrm_render_setting_title( __( 'WooSync Connection Settings', 'zero-bs-crm'), '' );

	?>

	<?php
	if ( isset( $sbupdated ) && $sbupdated ) {
		echo '<div class="ui message success">'. __( 'Settings Updated', 'zero-bs-crm' ) . '</div>';
	}
	?>

	<div id="sbA">
	<form method="post">
		<input type="hidden" name="editwplf" id="editwplf" value="1" />

		<?php
		if ( !isset( $settings['wcsetuptype'] ) || $settings['wcsetuptype'] == '0' ) {
		?>
			<style>
				.wc-ext{
					display:none;
				}
			</style>
		<?php
		}
		?>
		<table class="table table-bordered table-striped wtab">

			<thead>
				<tr>
					<th colspan="2" class="wmid"><?php _e('WooSync Connection Type','zero-bs-crm'); ?></th>
				</tr>
			</thead>

			<tbody>

				<tr>
					<td class="wfieldname">
						<label for="wpzbscrm_wcsetuptype"><?php _e( 'Setup Type', 'zero-bs-crm' ); ?></label><br />
						<?php _e( 'Where is WooCommerce installed?', 'zero-bs-crm' ); ?>
					</td>
					<td style="width:540px">
						<select class="winput" name="wpzbscrm_wcsetuptype" id="wpzbscrm_wcsetuptype">
							<option value="<?php echo JPCRM_WOO_SYNC_MODE_LOCAL; ?>" <?php if ( isset( $settings['wcsetuptype'] ) && $settings['wcsetuptype'] == JPCRM_WOO_SYNC_MODE_LOCAL ) echo ' selected="selected"'; ?>><?php _e( 'Same website', 'zero-bs-crm' );?></option>
							<option value="<?php echo JPCRM_WOO_SYNC_MODE_API; ?>" <?php if ( isset( $settings['wcsetuptype'] ) && $settings['wcsetuptype'] == JPCRM_WOO_SYNC_MODE_API ) echo ' selected="selected"'; ?>><?php _e( 'External website', 'zero-bs-crm' );?></option>
						</select>
					</td>
				</tr>
			</tbody>
		</table>

		<p class="wc-ext">
			<?php _e( 'For Jetpack CRM to connect to your external WooCommerce site, you will need to get API credentials from the WooCommerce install.', 'zero-bs-crm' ); ?> <a href="<?php echo $zbs->modules->woosync->urls['kb-woo-api-keys']; ?>" target="_blank"><?php _e( 'Learn more here.', 'zero-bs-crm' ); ?></a>
		</p>

		<table class="wc-ext table table-bordered table-striped wtab">

			<thead>
				<tr>
					<th colspan="2" class="wmid"><?php _e( 'WooCommerce API Settings', 'zero-bs-crm' ); ?></th>
				</tr>
			</thead>

			<tbody>
				<tr>
					<td class="wfieldname"><label for="wcdomain"><?php _e( 'Domain', 'zero-bs-crm' ); ?></label><br /><?php _e( 'Enter the domain of your WooCommerce site (including https:).', 'zero-bs-crm' ); ?></td>
					<td style='width:540px'><input type="text" class="winput form-control" name="wcdomain" id="wcdomain" value="<?php echo $settings['wcdomain']; ?>" placeholder="e.g. https://yourwoosite.com" /></td>
				</tr>

				<tr>
					<td class="wfieldname"><label for="wckey"><?php _e( 'API key', 'zero-bs-crm' ); ?></label><br /><?php _e( 'Enter your WooCommerce API key.', 'zero-bs-crm' ); ?></td>
					<td style='width:540px'><input type="text" class="winput form-control" name="wckey" id="wckey" value="<?php if (isset($settings['wckey']) && !empty($settings['wckey'])) echo $settings['wckey']; ?>" placeholder="e.g. ck_????????????????????????????????????????" /></td>
				</tr>

				<tr>
					<td class="wfieldname"><label for="wcsecret"><?php _e( 'API secret', 'zero-bs-crm' ); ?></label><br /><?php _e( 'Enter your WooCommerce API secret.', 'zero-bs-crm' ); ?></td>
					<td style='width:540px'><input type="text" class="winput form-control" name="wcsecret" id="wcsecret" value="<?php if (isset($settings['wcsecret']) && !empty($settings['wcsecret'])) echo $settings['wcsecret']; ?>" placeholder="e.g. cs_????????????????????????????????????????" /></td>
				</tr>

				<tr>
					<td class="wfieldname"><label for="wpzbs_wcprefix"><?php _e( 'Order prefix', 'zero-bs-crm' ); ?></label><br /><?php _e( 'Enter a unique prefix for your orders, matching the prefix in your WooCommerce store if possible. If changing to a different WooCommerce store change this prefix.', 'zero-bs-crm' ); ?></td>
					<td style='width:540px'><input type="text" class="winput form-control" name="wcprefix" id="wpzbs_wcprefix" value="<?php if (isset($settings['wcprefix']) && !empty($settings['wcprefix'])) echo $settings['wcprefix']; ?>" placeholder="e.g. my_woo_site" /></td>
				</tr>

			</tbody>
		</table>

		<table class="table table-bordered table-striped wtab">
			<tbody>

				<tr>
					<td colspan="2" class="wmid"><button type="submit" class="button button-primary button-large"><?php _e( 'Save Settings', 'zero-bs-crm' ); ?></button></td>
				</tr>

			</tbody>
		</table>

	</form>

	<script type="text/javascript">

		jQuery(document).ready(function(){

			jQuery( '#wpzbscrm_wcsetuptype' ).on('change',function(e){
				jQuery('.wc-ext').toggle();
			});

		});


	</script>

	</div>
	<?php

}
