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
function jpcrm_settings_page_html_woosync_main(){

	global $zbs;

	$settings = $zbs->modules->woosync->settings->getAll();

	$zbs_customer_statuses = zeroBSCRM_getCustomerStatuses();

	$auto_deletion_options = array(
		'do_nothing' => __( 'Do nothing', 'zero-bs-crm' ),
		'change_status' => sprintf( __( 'Change transaction/invoice status to `%s`', 'zero-bs-crm' ), __( 'Deleted', 'zero-bs-crm' ) ),
		'hard_delete_and_log' => __( 'Delete transaction/invoice, and add log to contact', 'zero-bs-crm' )
	);

	// Act on any edits!
	if (isset($_POST['editwplf'])){

		// Retrieve
		$updatedSettings = array();

		//order mapping - if not set, these all go to default..
		$updatedSettings['wcpending'] 	= ''; if (isset($_POST['wcpending']) 	&& !empty($_POST['wcpending'])) 	$updatedSettings['wcpending'] 	= sanitize_text_field($_POST['wcpending']);
		$updatedSettings['wcprocessing'] 	= ''; if (isset($_POST['wcprocessing']) 	&& !empty($_POST['wcprocessing'])) 	$updatedSettings['wcprocessing'] 	= sanitize_text_field($_POST['wcprocessing']);
		$updatedSettings['wconhold'] 	= ''; if (isset($_POST['wconhold']) 	&& !empty($_POST['wconhold'])) 	$updatedSettings['wconhold'] 	= sanitize_text_field($_POST['wconhold']);
		$updatedSettings['wccompleted'] 	= ''; if (isset($_POST['wccompleted']) 	&& !empty($_POST['wccompleted'])) 	$updatedSettings['wccompleted'] 	= sanitize_text_field($_POST['wccompleted']);
		$updatedSettings['wccancelled'] 	= ''; if (isset($_POST['wccancelled']) 	&& !empty($_POST['wccancelled'])) 	$updatedSettings['wccancelled'] 	= sanitize_text_field($_POST['wccancelled']);
		$updatedSettings['wcrefunded'] 	= ''; if (isset($_POST['wcrefunded']) 	&& !empty($_POST['wcrefunded'])) 	$updatedSettings['wcrefunded'] 	= sanitize_text_field($_POST['wcrefunded']);
		$updatedSettings['wcfailed'] 	= ''; if (isset($_POST['wcfailed']) 	&& !empty($_POST['wcfailed'])) 	$updatedSettings['wcfailed'] 	= sanitize_text_field($_POST['wcfailed']);

		//copy shipping address into second address
		$updatedSettings['wccopyship'] = 0; if (isset($_POST['wpzbscrm_wccopyship']) && !empty($_POST['wpzbscrm_wccopyship'])) $updatedSettings['wccopyship'] = 1;

		// tag objects with item name|coupon
		$updatedSettings['wctagcust'] = 0; if (isset($_POST['wpzbscrm_wctagcust']) && !empty($_POST['wpzbscrm_wctagcust'])) $updatedSettings['wctagcust'] = 1;
		$updatedSettings['wctagtransaction'] = 0; if (isset($_POST['wpzbscrm_wctagtransaction']) && !empty($_POST['wpzbscrm_wctagtransaction'])) $updatedSettings['wctagtransaction'] = 1;
		$updatedSettings['wctaginvoice'] = 0; if (isset($_POST['wpzbscrm_wctaginvoice']) && !empty($_POST['wpzbscrm_wctaginvoice'])) $updatedSettings['wctaginvoice'] = 1;
		$updatedSettings['wctagcoupon'] = 0; if (isset($_POST['wpzbscrm_wctagcoupon']) && !empty($_POST['wpzbscrm_wctagcoupon'])) $updatedSettings['wctagcoupon'] = 1;
		$updatedSettings['wctagcouponprefix'] 	= ''; if (isset($_POST['wctagcouponprefix']) 	&& !empty($_POST['wctagcouponprefix'])) 	$updatedSettings['wctagcouponprefix'] 	= zeroBSCRM_textProcess($_POST['wctagcouponprefix']);
		$updatedSettings['wctagproductprefix'] 	= ''; if (isset($_POST['wctagproductprefix']) 	&& !empty($_POST['wctagproductprefix'])) 	$updatedSettings['wctagproductprefix'] 	= zeroBSCRM_textProcess($_POST['wctagproductprefix']);

		// switches
		$updatedSettings['wcinv'] = 0; if (isset($_POST['wpzbscrm_wcinv']) && !empty($_POST['wpzbscrm_wcinv'])) $updatedSettings['wcinv'] = 1;
		$updatedSettings['wcprod'] = 0; if (isset($_POST['wpzbscrm_wcprod']) && !empty($_POST['wpzbscrm_wcprod'])) $updatedSettings['wcprod'] = 1;
		$updatedSettings['wcport'] = isset($_POST['wpzbscrm_wcport']) ? preg_replace( '/\s*,\s*/', ',', sanitize_text_field( $_POST['wpzbscrm_wcport'] ) ) : '';
		$updatedSettings['wcacc'] = 0; if (isset($_POST['wpzbscrm_wcacc']) && !empty($_POST['wpzbscrm_wcacc'])) $updatedSettings['wcacc'] = 1;

		// trash/delete action
		$updatedSettings['auto_trash'] = 'change_status';
		if ( isset( $_POST['jpcrm_woosync_auto_trash'] ) && in_array( $_POST['jpcrm_woosync_auto_trash'], array_keys( $auto_deletion_options ) ) ){
			$updatedSettings['auto_trash'] = sanitize_text_field( $_POST['jpcrm_woosync_auto_trash'] );
		}
		$updatedSettings['auto_delete'] = 'change_status';
		if ( isset( $_POST['jpcrm_woosync_auto_delete'] ) && in_array( $_POST['jpcrm_woosync_auto_delete'], array_keys( $auto_deletion_options ) ) ){
			$updatedSettings['auto_delete'] = sanitize_text_field( $_POST['jpcrm_woosync_auto_delete'] );
		}

		#} Brutal update
		foreach ($updatedSettings as $k => $v){
			$zbs->modules->woosync->settings->update($k,$v);
		}

		// $msg out!
		$sbupdated = true;

		// Reload
		$settings = $zbs->modules->woosync->settings->getAll();

	}

	// Show Title
	jpcrm_render_setting_title( 'WooSync Settings', '' );

	?>
    <p id="sbDesc"><?php _e( 'Here you can configure the global settings for WooSync.', 'zero-bs-crm' ); ?></p>
    <p style="padding-top: 18px; text-align:center;margin:1em">
		<?php
		echo sprintf(
			'<a href="%s&tab=%s&subtab=%s" class="ui button green">%s</a>',
			zbsLink($zbs->slugs['settings']),
			$zbs->modules->woosync->slugs['settings'],
			$zbs->modules->woosync->slugs['settings_connection'],
			__( 'Click here to Manage WooSync Connection', 'zero-bs-crm' )
		);
		?>
    </p>


	<?php if (isset($sbupdated)) if ($sbupdated) { echo '<div class="ui message success">'. __( 'Settings Updated', 'zero-bs-crm' ) . '</div>'; } ?>

    <div id="sbA">
    <form method="post">
        <input type="hidden" name="editwplf" id="editwplf" value="1" />
        <table class="table table-bordered table-striped wtab">

            <thead>

            <tr>
                <th colspan="2" class="wmid"><?php _e('WooSync Settings','zero-bs-crm'); ?>:</th>
            </tr>

            </thead>

            <tbody>
            <tr>
                <td class="wfieldname"><label for="wpzbscrm_wccopyship"><?php _e('Add Shipping Address','zero-bs-crm'); ?>:</label><br /><?php _e('Tick to store shipping address as contacts second address','zero-bs-crm'); ?></td>
                <td style="width:540px"><input type="checkbox" class="winput form-control" name="wpzbscrm_wccopyship" id="wpzbscrm_wccopyship" value="1"<?php if (isset($settings['wccopyship']) && $settings['wccopyship'] == "1") echo ' checked="checked"'; ?> /></td>
            </tr>


            <tr>
                <td class="wfieldname"><label for="wpzbscrm_wctagcust"><?php _e('Tag Contact','zero-bs-crm'); ?>:</label><br /><?php _e('Tick to tag your contact with their item name','zero-bs-crm'); ?></td>
                <td style="width:540px"><input type="checkbox" class="winput form-control" name="wpzbscrm_wctagcust" id="wpzbscrm_wctagcust" value="1"<?php if (isset($settings['wctagcust']) && $settings['wctagcust'] == "1") echo ' checked="checked"'; ?> /></td>
            </tr>


            <tr>
                <td class="wfieldname"><label for="wpzbscrm_wctagtransaction"><?php _e('Tag Transaction','zero-bs-crm'); ?>:</label><br /><?php _e('Tick to tag your transaction with the item name','zero-bs-crm'); ?></td>
                <td style="width:540px"><input type="checkbox" class="winput form-control" name="wpzbscrm_wctagtransaction" id="wpzbscrm_wctagtransaction" value="1"<?php if (isset($settings['wctagtransaction']) && $settings['wctagtransaction'] == "1") echo ' checked="checked"'; ?> /></td>
            </tr>


            <tr>
                <td class="wfieldname"><label for="wpzbscrm_wctaginvoice"><?php _e('Tag Invoice','zero-bs-crm'); ?>:</label><br /><?php _e('Tick to tag your invoice with the item name','zero-bs-crm'); ?></td>
                <td style="width:540px"><input type="checkbox" class="winput form-control" name="wpzbscrm_wctaginvoice" id="wpzbscrm_wctaginvoice" value="1"<?php if (isset($settings['wctaginvoice']) && $settings['wctaginvoice'] == "1") echo ' checked="checked"'; ?> /></td>
            </tr>


            <tr>
                <td class="wfieldname"><label for="jpcrm_woosync_auto_trash"><?php _e('Order Trash action','zero-bs-crm'); ?>:</label><br /><?php _e('Choose what should happen when an order is trashed in WooCommerce','zero-bs-crm'); ?></td>
                <td style="width:540px">
                	<select id="jpcrm_woosync_auto_trash" name="jpcrm_woosync_auto_trash" class="winput form-control">
                		<?php 

                			$current_auto_trash_setting = 'change_status';
                			if ( isset( $settings['auto_trash'] ) && !empty( $settings['auto_trash'] ) ){
                				$current_auto_trash_setting = $settings['auto_trash'];
                			}

                			foreach ( $auto_deletion_options as $option_key => $option_label ){

                				?><option value="<?php echo $option_key; ?>"<?php

                					if ( $option_key == $current_auto_trash_setting ){
                						echo ' selected="selected"';
                					}

                				?>><?php echo $option_label; ?></option><?php

                			}

                		?>
                	</select>                	
                </td>
            </tr>

            <tr>
                <td class="wfieldname"><label for="jpcrm_woosync_auto_delete"><?php _e('Order Delete action','zero-bs-crm'); ?>:</label><br /><?php _e('Choose what should happen when an order is deleted in WooCommerce','zero-bs-crm'); ?></td>
                <td style="width:540px">
                	<select id="jpcrm_woosync_auto_delete" name="jpcrm_woosync_auto_delete" class="winput form-control">
                		<?php 

                			$current_auto_delete_setting = 'change_status';
                			if ( isset( $settings['auto_delete'] ) && !empty( $settings['auto_delete'] ) ){
                				$current_auto_delete_setting = $settings['auto_delete'];
                			}

                			foreach ( $auto_deletion_options as $option_key => $option_label ){

                				?><option value="<?php echo $option_key; ?>"<?php

                					if ( $option_key == $current_auto_delete_setting ){
                						echo ' selected="selected"';
                					}

                				?>><?php echo $option_label; ?></option><?php

                			}

                		?>
                	</select>                	
                </td>
            </tr>


            <tr>
                <td class="wfieldname"><label for="wpzbscrm_wctagcoupon"><?php _e( 'Include Coupon as tag', 'zero-bs-crm' ); ?>:</label><br /><?php _e('Tick to include any used WooCommerce coupon codes as tags (depends on above settings)', 'zero-bs-crm' ); ?></td>
                <td style="width:540px"><input type="checkbox" class="winput form-control" name="wpzbscrm_wctagcoupon" id="wpzbscrm_wctagcoupon" value="1"<?php if (isset($settings['wctagcoupon']) && $settings['wctagcoupon'] == "1") echo ' checked="checked"'; ?> /></td>
            </tr>

            <tr>
                <td class="wfieldname"><label for="wpzbscrm_wcinv"><?php _e('Create Invoices from WooCommerce Orders','zero-bs-crm'); ?>:</label><br /><?php _e('Tick to create invoices from your WooCommerce orders','zero-bs-crm'); ?></td>
                <td style="width:540px"><input type="checkbox" class="winput form-control" name="wpzbscrm_wcinv" id="wpzbscrm_wcinv" value="1"<?php if (isset($settings['wcinv']) && $settings['wcinv'] == "1") echo ' checked="checked"'; ?> /></td>
            </tr>

            <tr>
                <td class="wfieldname">
                    <label for="wpzbscrm_wcacc"><?php _e('Show Invoices on My Account','zero-bs-crm'); ?>:</label><br />
					<?php _e( 'Tick to show a Jetpack CRM Invoices menu item under WooCommerce My Account', 'zero-bs-crm' ); ?>
                </td>
                <td style="width:540px">
                    <input type="checkbox" class="winput form-control" name="wpzbscrm_wcacc" id="wpzbscrm_wcacc" value="1"<?php if (isset($settings['wcacc']) && $settings['wcacc'] == "1") echo ' checked="checked"'; ?> />
					<?php
					$invoices_enabled = zeroBSCRM_getSetting( 'feat_invs' ) > 0;
					if ( !$invoices_enabled ){ ?>
                        <br />
                        <small><?php _e('Warning: Invoicing module is currently disabled.','zero-bs-crm'); ?></small>
					<?php } ?>
                </td>
            </tr>

            <tr>
                <td class="wfieldname"><label for="wctagproductprefix"><?php _e( 'Product tag prefix','zero-bs-crm'); ?>:</label><br /><?php _e('Enter a tag prefix for product tags (e.g. Product: )', 'zero-bs-crm' ); ?></td>
                <td style='width:540px'><input type="text" class="winput form-control" name="wctagproductprefix" id="wctagproductprefix" value="<?php if (isset($settings['wctagproductprefix']) && !empty($settings['wctagproductprefix'])) echo $settings['wctagproductprefix']; ?>" placeholder="<?php _e( "e.g. 'Product: '", 'zero-bs-crm' ); ?>" /></td>
            </tr>

            <tr>
                <td class="wfieldname"><label for="wctagcouponprefix"><?php _e( 'Coupon tag prefix','zero-bs-crm'); ?>:</label><br /><?php _e('Enter a tag prefix for coupon tags (e.g. Coupon: )', 'zero-bs-crm' ); ?></td>
                <td style='width:540px'><input type="text" class="winput form-control" name="wctagcouponprefix" id="wctagcouponprefix" value="<?php if (isset($settings['wctagcouponprefix']) && !empty($settings['wctagcouponprefix'])) echo $settings['wctagcouponprefix']; ?>" placeholder="<?php _e( "e.g. 'Coupon: '", 'zero-bs-crm' ); ?>" /></td>
            </tr>

            <!-- #follow-on-refinements commented out for now as we need to review how product index works now we have accessible line items in v3.0
	                    	<tr>
	                    		<td class="wfieldname"><label for="wpzbscrm_wcprod"><?php // _e('Use Product Index','zero-bs-crm'); ?>:</label><br /><?php // _e('Tick to allow Product Index on Invoices. Makes creating invoices easier','zero-bs-crm'); ?></td>
	                    		<td style="width:540px"><input type="checkbox" class="winput form-control" name="wpzbscrm_wcprod" id="wpzbscrm_wcprod" value="1"<?php // if (isset($settings['wcprod']) && $settings['wcprod'] == "1") echo ' checked="checked"'; ?> /></td>
	                    	</tr>
							-->

            <tr>
                <td class="wfieldname"><label for="wpzbscrm_port"><?php _e('WooCommerce My Account','zero-bs-crm'); ?>:</label><br /><?php _e('Enter a comma-separated list of Jetpack CRM custom fields to let customers edit these via WooCommerce My Account (e.g. custom-field-1, other-custom-field)','zero-bs-crm'); ?></td>
                <td style="width:540px"><input type="text" class="winput form-control" name="wpzbscrm_wcport" id="wpzbscrm_port" value="<?php if (isset($settings['wcport'])) echo $settings['wcport']; ?>" /></td>
            </tr>

            <tr>
                <td class="wfieldname"><label><?php _e('Order Mapping','zero-bs-crm'); ?>:</label><br /><?php _e('Here you can choose how you want to map WooCommerce order statuses to CRM contact statuses','zero-bs-crm'); ?></td>
                <td style="width:540px">
					<?php

					$zbs_woo_status = array(
						'wcpending'    => __('Pending', 'zero-bs-crm'),
						'wcprocessing' => __('Processing', 'zero-bs-crm'),
						'wconhold'     => __('On hold', 'zero-bs-crm'),
						'wccompleted'  => __('Completed', 'zero-bs-crm'),
						'wccancelled'  => __('Cancelled', 'zero-bs-crm'),
						'wcrefunded'   => __('Refunded', 'zero-bs-crm'),
						'wcfailed'     => __('Failed', 'zero-bs-crm'),
					);


					$zbs_woo_status          = apply_filters( 'zbs-woo-additional-status', $zbs_woo_status );
					$zbs_customer_stat_array = explode( ",", $zbs_customer_statuses );

					foreach ( $zbs_woo_status as $k => $v ) {

						$selected = '';
						if (is_array($settings) && isset($settings[$k])) {
							$selected = $settings[$k];
						}

						echo "<label class='mapto'>";
						_e("Map order status ", "zero-bs-crm");
						echo "<strong> " . $v . "</strong> to";
						echo "</label>";
						echo '<br/><select class="winput" name="' . $k . '" id="' . $k . '">';
						echo "<option value='-1'>";
						_e("Default", "zero-bs-crm");
						echo "</option>";

						//Jetpack CRM statuses chosen by user...
						foreach ($zbs_customer_stat_array as $cust_stat) {
							if ($selected == $cust_stat) {
								echo "<option value='" . $cust_stat . "' selected>" . $cust_stat . "</option>";
							} else {
								echo "<option value='" . $cust_stat . "'>" . $cust_stat . "</option>";
							}
						}

						echo '</select><br/>';
					}

					?>

                </td>
            </tr>

            </tbody>
        </table>

        <table class="table table-bordered table-striped wtab">
            <tbody>

            <tr>
                <td colspan="2" class="wmid"><button type="submit" class="button button-primary button-large"><?php _e('Save Settings','zero-bs-crm'); ?></button></td>
            </tr>

            </tbody>
        </table>

    </form>

    </div><?php

}
