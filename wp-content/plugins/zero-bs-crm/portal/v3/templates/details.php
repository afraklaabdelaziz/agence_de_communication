<?php
/**
 * Your Details Page
 *
 * This displays the users details for editing
 *
 * @author 		ZeroBSCRM
 * @package 	Templates/Portal/Details
 * @see			https://kb.jetpackcrm.com/
 * @version     3.0
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Don't allow direct access

do_action( 'zbs_enqueue_scripts_and_styles' );

global $zbs, $wpdb, $zbsCustomerFields;

    // handle the saving of the details.
    if(array_key_exists('save', $_POST)){
        jpcrm_portal_save_details();

    }

    $uid = get_current_user_id();
    $uinfo = get_userdata( $uid );
    $cID = zeroBS_getCustomerIDWithEmail($uinfo->user_email);

?>
<div class="alignwide zbs-site-main zbs-portal-grid">
    <nav class="zbs-portal-nav">
        <?php

        // define
        $details_endpoint = 'details';

        //moved into func
        if(function_exists('zeroBSCRM_clientPortalgetEndpoint')){
            $details_endpoint = zeroBSCRM_clientPortalgetEndpoint('details');
        }
        zeroBS_portalnav($details_endpoint);
        ?>
    </nav>
    <div class='zbs-portal-content'>
        <?php

        $page_title = __("Your Details","zero-bs-crm");
        $page_title = apply_filters('zbs_portal_details_title', $page_title);

        // if admin, explain
        if (current_user_can( 'admin_zerobs_manage_options' ) && empty($cID)){
            jpcrm_portal_details_RenderAdminNotice();
        }

        // admin msg (upsell cpp) (checks perms itself, safe to run)
        // leave off here, enough with long msg above zeroBSCRM_portal_adminMsg();
        ?>
        <h2><?php echo $page_title; ?></h2>
        <div class='zbs-entry-content' style="position:relative;">
            <form action="#" name="zbs-update-deets" method="POST" style="padding-bottom:50px;" class="form-horizontal form-inline">
                <?php

                $fields = $zbsCustomerFields;

                // Get field Hides...
                $fieldHideOverrides = $zbs->settings->get('fieldhides');
                $zbsCustomer = zeroBS_getCustomerMeta($cID);

                $zbsOpenGroup = false;
                $showAddr = true;

                // Fields to hide for front-end situations (Portal)
                $fields_to_hide_on_portal = $zbs->DAL->fields_to_hide_on_frontend( ZBS_TYPE_CONTACT );
                $potentialNotToShow = $zbs->settings->get('portal_hidefields');
                if (isset($potentialNotToShow)){
                    $potentialNotToShow = explode(',',$potentialNotToShow);
                }
                if (is_array($potentialNotToShow)) $fields_to_hide_on_portal = $potentialNotToShow;

                ?>
                <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $cID; ?>" />
                <div class="form-table wh-metatab wptbp" id="wptbpMetaBoxMainItem">
                    <?php

                    // Address settings
                    $showAddresses = zeroBSCRM_getSetting('showaddress');
                    $showSecondAddress = zeroBSCRM_getSetting('secondaddress');
                    $showCountryFields = zeroBSCRM_getSetting('countries');

                    // This global holds "enabled/disabled" for specific fields... ignore unless you're WH or ask
                    global $zbsFieldsEnabled;
                    if ($showSecondAddress == "1"){
                        $zbsFieldsEnabled['secondaddress'] = true;
                    }

                    $second_address_area = zeroBSCRM_getSetting('secondaddresslabel');

                    // used for new fields 2.98.5 +
                    $postPrefix = 'zbsc_';

                    $zbsWasOpenMultiGroupWrap = false;
                    foreach ($fields as $fieldK => $fieldV){
                        // WH hard-not-showing some fields
                        if (in_array($fieldK, $fields_to_hide_on_portal)) {
                            continue;
                        }

                        $showField = true;

                        // Check if not hard-hidden by opt override (on off for second address, mostly)
                        if (isset($fieldV['opt']) && (!isset($zbsFieldsEnabled[$fieldV['opt']]) || !$zbsFieldsEnabled[$fieldV['opt']])) $showField = false;

                        // or is hidden by checkbox?
                        if (isset($fieldHideOverrides['customer']) && is_array($fieldHideOverrides['customer'])){
                            if (in_array($fieldK, $fieldHideOverrides['customer'])){
                                $showField = false;
                            }
                        }

                        // ==================================================================================
                        // Following grouping code needed moving out of ifShown loop:

                        // Whatever prev field group was, if this is diff, close (post group)
                        if (
                            $zbsOpenGroup &&
                            // diff group
                            (
                                (isset($fieldV['area']) && $fieldV['area'] != $zbsFieldGroup) ||
                                // No group
                                !isset($fieldV['area']) && $zbsFieldGroup != ''
                            )
                        ){
                            echo '</div>';
                            $zbsOpenGroup = false;
                        }

                        // Any groupings?
                        if (isset($fieldV['area'])){
                            if (!$zbsWasOpenMultiGroupWrap) {
                                echo "<div class='zbs-multi-group-wrap'>";
                                $zbsWasOpenMultiGroupWrap = true;
                            }

                            // First in a grouping? (assumes in sequential grouped order)
                            if ($zbsFieldGroup != $fieldV['area']){
                                // set it
                                $zbsFieldGroup = $fieldV['area'];
                                $fieldGroupLabel = str_replace(' ','_',$zbsFieldGroup);
                                $fieldGroupLabel = strtolower($fieldGroupLabel);

                                if ($showSecondAddress != "1") {
                                    $fieldGroupLabel .= "_100w";
                                }
                                if ($showAddresses == "0") {
                                    $fieldGroupLabel .= " zbs-hide";
                                }

                                // Make class for hiding address (this form output is weird) <-- classic mike saying my code is weird when it works fully. Ask if you don't know!
                                // DR Still Weird?
                                $zbsShouldHideOrNotClass = '';
                                // if addresses turned off, hide the lot
                                if (($showAddresses != "1") ||
                                    ($zbsFieldGroup == 'Second Address' && $showSecondAddress != "1"))
                                {
                                    $zbsShouldHideOrNotClass = 'zbs-hide';
                                }

                                if ($second_address_area != '' && $fieldV['area'] == 'Second Address') {
                                    echo '<div class="zbs-multi-group-item '. $zbsShouldHideOrNotClass .'"><label class="zbs-field-group-label">'. $second_address_area .'</label>';
                                } else {
                                    echo '<div class="zbs-multi-group-item '. $zbsShouldHideOrNotClass .'"><label class="zbs-field-group-label">'. __($fieldV['area'],"zero-bs-crm").'</label>';
                                }
                                // Set this (need to close)
                                $zbsOpenGroup = true;
                            }
                        } else {
                            // No groupings!
                            $zbsFieldGroup = '';
                        }

                        // Grouping
                        // ==================================================================================

                        // close opened wrap of groups
                        if (!array_key_exists('area', $fieldV) && $zbsWasOpenMultiGroupWrap) {
                            echo "</div>";
                            $zbsWasOpenMultiGroupWrap = false;
                        }

                        // If show...
                        if ($showField) {
                            // This whole output is LEGACY
                            // v3.0 + this is resolved in core via zeroBSCRM_html_editFields() and zeroBSCRM_html_editField()
                            // ... in FormatHelpers.
                            // ... this could do with re-writing to match that.
                            $value = jpcrm_portal_details_GetValue($fieldK, $zbsCustomer);
                            if (isset($fieldV[0])) {
                                jpcrm_portal_details_RenderFieldByType($fieldV[0], $fieldK, $fieldV, $value, $postPrefix, $showCountryFields, $zbsCustomer);
                            }
                        }
                    } // foreach field
                    // closes any groups/tabs that are still open
                    if ($zbsWasOpenMultiGroupWrap) {
                        echo "</div>";
                    }
                    if ($zbsOpenGroup) {
                        echo "</div>";
                    }
                    ?>
                <p>
                    <label style="margin-top:2em;"><?php _e("Change your password (or leave blank to keep the same)", "zero-bs-crm"); ?></label>
                    <input class="form-control" type="password" id="password" name="password" value=""/>
                </p>
                <p>
                    <label><?php _e("Re-enter password", "zero-bs-crm"); ?></label>
                    <input class="form-control" type="password" id="password2" name="password2" value=""/>
                </p>
                <p>
                    <input type="hidden" id="save" name="save" value="1"/>
                    <input type="submit" id="submit" value="<?php _e('Submit',"zero-bs-crm");?>"/>
                </p>
                </div>
            </form>
        </div>
    </div>
    <div class="zbs-portal-grid-footer"><?php zeroBSCRM_portalFooter(); ?></div>
</div>


