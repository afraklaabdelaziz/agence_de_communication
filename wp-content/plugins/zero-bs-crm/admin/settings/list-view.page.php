<?php 
/*!
 * Admin Page: Settings: List view settings
 */

// stop direct access
if ( ! defined( 'ZEROBSCRM_PATH' ) ) exit; 

global $wpdb, $zbs;  #} Req


#} Act on any edits!
if (zeroBSCRM_isZBSAdminOrAdmin() && isset($_POST['editwplflistview'])){

    // check nonce
    check_admin_referer( 'zbs-update-settings-listview' );

    #debug echo 'UPDATING: <PRE>'; print_r($_POST); echo '</PRE>';
    $existingSettings = $zbs->settings->get('quickfiltersettings');

    if (isset($_POST['wpzbscrm_notcontactedinx'])){

        $potentialNotContactedInX = (int)sanitize_text_field( $_POST['wpzbscrm_notcontactedinx'] );
        if ($potentialNotContactedInX > 0) $existingSettings['notcontactedinx'] = $potentialNotContactedInX;
    }

    if (isset($_POST['wpzbscrm_olderthanx'])){

        $potentialOlderThanX = (int)sanitize_text_field( $_POST['wpzbscrm_olderthanx'] );
        if ($potentialOlderThanX > 0) $existingSettings['olderthanx'] = $potentialOlderThanX;
    }

    #} This brutally overrides existing!
    $zbs->settings->update('quickfiltersettings',$existingSettings);

    // and this
    $allowinlineedits = -1; if (isset($_POST['wpzbscrm_allowinlineedits'])) $allowinlineedits = 1;
    $zbs->settings->update('allowinlineedits',$allowinlineedits);

    $sbupdated = true;

}

// reget
$settings = $zbs->settings->get('quickfiltersettings');
$allowinlineedits = $zbs->settings->get('allowinlineedits');

?>

<p id="sbDesc"><?php echo sprintf( __( 'This page lets you set some generic List View settings. These affect pages like the <a href="%s">Contact List</a> view.', 'zero-bs-crm' ), zbsLink( 'create', -1, ZBS_TYPE_CONTACT ) ); ?></p>

<?php if (isset($sbupdated)) if ($sbupdated) { echo '<div style="width:500px; margin-left:20px;" class="wmsgfullwidth">'; zeroBSCRM_html_msg(0,__('Settings Updated',"zero-bs-crm")); echo '</div>'; } ?>

<div id="sbA">
    <form method="post" action="?page=<?php echo $zbs->slugs['settings']; ?>&tab=listview" id="zbslistviewform">
        <input type="hidden" name="editwplflistview" id="editwplflistview" value="1" />
        <?php
        // add nonce
        wp_nonce_field( 'zbs-update-settings-listview');
        ?>

        <table class="table table-bordered table-striped wtab">

            <thead>

            <tr>
                <th colspan="2" class="wmid"><?php _e('Quick Filters',"zero-bs-crm"); ?>:</th>
            </tr>

            </thead>

            <tbody>

            <tr>
                <td class="wfieldname"><label for="wpzbscrm_notcontactedinx"><?php _e('"Not Contacted in X Days"',"zero-bs-crm"); ?>:</label><br /><?php _e('Enter the number of days to use in this filter.',"zero-bs-crm"); ?><br /><?php _e('E.g. Not contacted in 10 days',"zero-bs-crm"); ?></td>
                <td style="width:540px">
                    <input style="width:100px;padding:10px;" name="wpzbscrm_notcontactedinx" id="wpzbscrm_notcontactedinx" class="form-control" type="text" value="<?php if (isset($settings['notcontactedinx']) && !empty($settings['notcontactedinx'])) echo $settings['notcontactedinx']; ?>" />
                </td>
            </tr>
            <tr>
                <td class="wfieldname"><label for="wpzbscrm_allowinlineedits"><?php _e('"Allow Inline Edits"',"zero-bs-crm"); ?>:</label><br /><?php _e('Allow Inline editing of list view fields',"zero-bs-crm"); ?></td>
                <td style="width:540px">
                    <input type="checkbox" name="wpzbscrm_allowinlineedits" id="wpzbscrm_allowinlineedits" class="form-control" value="1"<?php if (isset($allowinlineedits) && $allowinlineedits == "1") echo ' checked="checked"'; ?> />
                </td>
            </tr>
            <?php if ($zbs->DBVER >= 2){ ?>
                <tr>
                    <td class="wfieldname"><label for="wpzbscrm_olderthanx"><?php _e('"Added more than X days ago"',"zero-bs-crm"); ?>:</label><br /><?php _e('Enter the number of days to use in this filter.',"zero-bs-crm"); ?><br /><?php _e('E.g. Contact older than 30 days',"zero-bs-crm"); ?></td>
                    <td style="width:540px">
                        <input style="width:100px;padding:10px;" name="wpzbscrm_olderthanx" id="wpzbscrm_olderthanx" class="form-control" type="text" value="<?php if (isset($settings['olderthanx']) && !empty($settings['olderthanx'])) echo $settings['olderthanx']; ?>" />
                    </td>
                </tr>
            <?php } ?>



            </tbody>

        </table>

        <table class="table table-bordered table-striped wtab">
            <tbody>

            <tr>
                <td class="wmid">
                    <button type="submit" class="ui button primary"><?php _e('Save Settings',"zero-bs-crm"); ?></button>
                </td>
            </tr>

            </tbody>
        </table>

    </form>

    <script type="text/javascript">

    </script>

</div>
