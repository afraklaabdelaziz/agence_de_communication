<?php
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 * V2.5
 *
 * Copyright 2020 Automattic
 *
 * Date: 09/01/18
 */

/* ======================================================
  Breaking Checks ( stops direct access )
   ====================================================== */
if (! defined('ZEROBSCRM_PATH')) {
    exit;
}
/* ======================================================
  / Breaking Checks
   ====================================================== */

function zeroBSCRM_pages_addEditSegment($potentialID = -1)
{

    zeroBSCRM_html_addEditSegment($potentialID);
}


function zeroBSCRM_html_addEditSegment($potentialID = -1)
{

    global $zbs;

    #} New or edit
    $newSegment = true;

    // potential
    $segmentID = (int)$potentialID;

    // attempt retrieve (including has rights)
    $segment = $zbs->DAL->segments->getSegment($segmentID, true);

    if (isset($segment) && isset($segment['id'])) {
        // checks out
        $newSegment = false;
    } else {
        // no perms/doesn't checkout
        $segment = false;
    }

    // retrieve conditions/helpers
    $availableConditions = zeroBSCRM_segments_availableConditions();
    $availableConditionOperators = zeroBSCRM_segments_availableConditionOperators();
    $available_tags_contacts = $zbs->DAL->getTagsForObjType( array( 'objtypeid'=>ZBS_TYPE_CONTACT ) );
    $available_tags_transactions = $zbs->DAL->getTagsForObjType( array( 'objtypeid' => ZBS_TYPE_TRANSACTION ) );    
    $availableStatuses = zeroBSCRM_getCustomerStatuses(true);

    #} Refresh 2
    ?><div class="zbs-semantic wrap" id="zbs-segment-editor">

            <!-- load blocker not used.
            <div class="ui segment hidden" id="zbs-segment-editor-blocker">
              <div class="ui active inverted dimmer">
                <div class="ui text loader"><?php _e('Saving', 'zero-bs-crm'); ?></div>
              </div>
              <p></p>
            </div> -->


            <!-- edit title -->
            <div class="ui huge form centralsimple">

                <div class="field required">
                  <label><?php _e('Name this Segment', "zero-bs-crm"); ?></label>
                  <p style="font-size:0.8em"><?php _e('Enter a descriptive title. This is shown on internal pages and reports.', "zero-bs-crm"); ?></p>
                  <input placeholder="<?php _e('e.g. VIP Customers', "zero-bs-crm"); ?>" type="text" id="zbs-segment-edit-var-title" name="zbs-segment-edit-var-title" class="max500" value="<?php if (isset($segment['name'])) {
                        echo $segment['name'];
                                      } ?>">
                  <?php echo zeroBSCRM_UI2_messageHTML('mini error hidden', '', __('This field is required', "zero-bs-crm"), '', 'zbs-segment-edit-var-title-err'); ?>
                </div>

            </div>

            <!-- error segment -->
            <?php if (is_array($segment) && isset($segment['error'])) { ?>
                <div style="max-width: 600px; margin-left: auto; margin-right: auto; margin-top: 2.5em;">
                <?php

                // generate a user message re: segment error
                $error_code = $zbs->getErrorCode($segment['error']);
                if ($error_code && isset($error_code['user_message'])) {
                    $error_message = $error_code['user_message'];
                } else {
                    // error code not present in global error-code array, show code
                    $error_message = $segment['error'];
                }

                echo zeroBSCRM_UI2_messageHTML(
                    'warning',
                    __('Segment Warning', 'zero-bs-crm'),
                    __('There is an issue with this segment:', 'zero-bs-crm') . '<br>' . $error_message,
                    'warning',
                    'segment-condition-error'
                ); ?>
                </div>
            <?php } ?>

            <!-- edit conditions segment -->
            <div class="ui large form centralsimple segment">

                <div class="field" style="padding-top:0;padding-bottom: 0">

                    <button class="ui icon small button primary right floated" type="button" id="zbs-segment-edit-act-add-condition">
                        <?php _e('Add Condition', "zero-bs-crm"); ?>  <i class="plus icon"></i>
                    </button>

                    <label><?php _e('Conditions', "zero-bs-crm"); ?></label>
                    <p><?php _e('Select conditions which will define this segment.', "zero-bs-crm"); ?></p>

                </div>

                <div id="zbs-segment-edit-conditions" class="ui segments">
                    <!-- built via js -->
                </div>
                <div class="field" style="padding-top:0">
                    <?php echo zeroBSCRM_UI2_messageHTML('mini hidden', '', __('Segments require at least one condition', "zero-bs-crm"), '', 'zbs-segment-edit-conditions-err'); ?>
                </div>

                <div class="field" style="padding-top:1em">
                  <label><?php _e('Match Type', "zero-bs-crm"); ?></label>
                  <p><?php _e('Should contacts in this segment should match any or all the above conditions?:', "zero-bs-crm"); ?></p>                  
                   <select class="ui dropdown" id="zbs-segment-edit-var-matchtype">
                        <option value="all"><?php _e('Match all Conditions', "zero-bs-crm"); ?></option>
                        <option value="one"><?php _e('Match any one Condition', "zero-bs-crm"); ?></option>
                    </select>
                </div>
                
                <h4 class="ui horizontal header divider"><?php _e('Continue', "zero-bs-crm"); ?></h4>

                <div class="jog-on">
                    <button class="ui submit teal large icon button" id="zbs-segment-edit-act-p2preview"><?php _e('Preview Segment', "zero-bs-crm"); ?> <i class="unhide icon"></i></button>
                </div>
            </div>

            <!-- preview segment -->
            <div class="ui large form centralsimple segment hidden" id="zbs-segment-edit-preview">

                <div id="zbs-segment-edit-preview-output">

                </div>
                <?php echo zeroBSCRM_UI2_messageHTML('hidden', '', __('Your conditions did not produce any matching Contacts. You can still save this segment, but currently there is no one in it!', "zero-bs-crm"), '', 'zbs-segment-edit-emptypreview-err'); ?>

                <div class="jog-on">
                    <button class="ui submit positive large icon button" id="zbs-segment-edit-act-p2submit"><?php _e('Save Segment', "zero-bs-crm"); ?> <i class="pie chart icon"></i></button>
                </div>
            </div>

            <?php // ajax + lang bits ?><script type="text/javascript">
            var zbsSegment = <?php echo json_encode($segment); ?>;
            var zbsAvailableConditions = <?php echo json_encode($availableConditions); ?>;
            var zbsAvailableConditionOperators = <?php echo json_encode($availableConditionOperators); ?>;
            var jpcrm_available_contact_tags = <?php echo json_encode( $available_tags_contacts ); ?>;
            var jpcrm_available_transaction_tags = <?php echo json_encode( $available_tags_transactions ); ?>;
            var zbsAvailableStatuses = <?php echo json_encode($availableStatuses); ?>;
            var zbsSegmentStemURL = '<?php echo zbsLink('edit', -1, 'segment', true); ?>';
            var zbsSegmentListURL = '<?php echo zbsLink($zbs->slugs['segments']); ?>';
            var zbsSegmentSEC = '<?php echo wp_create_nonce("zbs-ajax-nonce"); ?>';
            var zbsSegmentLang = {

                generalerrortitle: '<?php _e('General Error', "zero-bs-crm"); ?>',
                generalerror: '<?php _e('There was a general error.', "zero-bs-crm"); ?>',

                currentlyInSegment: '<?php _e('Contacts currently match these conditions.', "zero-bs-crm"); ?>',
                previewTitle: '<?php _e('Contacts Preview', "zero-bs-crm"); ?>',

                noName: '<?php _e('Unnamed Contact', "zero-bs-crm"); ?>',
                noEmail: '<?php _e('No Email', "zero-bs-crm"); ?>',

                notags: '<?php _e('No Tags Found', "zero-bs-crm"); ?>',
                nostatuses: '<?php _e('No Statuses Found', "zero-bs-crm"); ?>',
                noextsources: '<?php _e('No External Sources Found', "zero-bs-crm"); ?>',

                to: '<?php _e('to', "zero-bs-crm"); ?>',
                eg: '<?php _e('e.g.', "zero-bs-crm"); ?>',

                saveSegment: '<?php echo zeroBSCRM_slashOut('Save Segment', true).' <i class="save icon">'; ?>',
                savedSegment: '<?php echo zeroBSCRM_slashOut('Segment Saved', true).' <i class="check circle outline icon">'; ?>',

                contactfields: '=== <?php _e('Contact Fields', "zero-bs-crm"); ?> ===',

            };
            var jpcrm_external_source_list = <?php
            
            // simplify our external sources array
            $external_source_array = array();
            foreach ($zbs->external_sources as $external_source_key => $external_source_info) {
                $external_source_array[] = array(
                    'key' => $external_source_key,
                    'name' => $external_source_info[0]
                );
            }

            // sort by name
            usort($external_source_array, function ($a, $b) {
                return strcmp($a['key'], $b['key']);
            });

            echo json_encode($external_source_array); ?>;</script>

    </div><?php
}


function zeroBSCRM_segments_typeConversions($value = '', $type = '', $operator = '', $direction = 'in')
{

    if (!empty($value)) {
        $availableConditions = zeroBSCRM_segments_availableConditions();

        if (isset($availableConditions[$type]['conversion'])) {
            // INBOUND (e.g. post -> db)
            // For dates, convert to UTS here. (EXCEPT FOR daterange!, dealing with that in zeroBSCRM_segments_filterConditions for now)
            if ($direction == 'in' && $operator != 'daterange') {
                switch ($availableConditions[$type]['conversion']) {
                    case 'date-to-uts':
                        // convert date to uts
                        $value = zeroBSCRM_locale_dateToUTS($value, true);
                        
                        // for those dates used in 'AFTER' this needs to effectively be midnight on the day (start of next day)
                        if ($operator == 'after') {
                            $value += (60*60*24);
                        }

                        break;
                }
            } elseif ($direction == 'out') {
                // OUTBOUND (e.g. exposing dates in segment editor)

                switch ($availableConditions[$type]['conversion']) {
                    case 'date-to-uts':
                        // for those dates used in 'AFTER'
                        // this needs to effectively be midnight on the day (start of next day)
                        // (in this case, we remove the same)
                        if ($operator == 'after') {
                            $value -= (60*60*24);
                        }

                        // convert uts back to date
                        $value = zeroBSCRM_date_i18n(-1, $value);
                        

                        break;
                }
            }
        }
    }

    return $value;
}
