<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 * V1.1.19
 *
 * Copyright 2020 Automattic
 *
 * Date: 25/10/16
 */

/* ======================================================
  Breaking Checks ( stops direct access )
   ====================================================== */
    if ( ! defined( 'ZEROBSCRM_PATH' ) ) exit;
/* ======================================================
  / Breaking Checks
   ====================================================== */




/* ======================================================
   Init Func
   ====================================================== */

   function zeroBSCRM_LogsMetaboxSetup(){

        // req. for custom log types
        zeroBSCRM_setupLogTypes();

        $zeroBS__Metabox_Logs = new zeroBS__Metabox_Logs( __FILE__ );

   }

   add_action( 'after_zerobscrm_settings_init','zeroBSCRM_LogsMetaboxSetup');

/* ======================================================
   / Init Func
   ====================================================== */


/* ======================================================
  Declare Globals
   ====================================================== */

    global $zeroBSCRM_logTypes; 
    $zeroBSCRM_logTypes = array(

        'zerobs_customer' => array(
        
                    'note'=> array("label" => __("Note","zero-bs-crm"), "ico" => "fa-sticky-note-o"),
                    'call'=> array("label" => __("Call","zero-bs-crm"), "ico" => "fa-phone-square"),
                    'email'=> array("label" => __("Email","zero-bs-crm"), "ico" => "fa-envelope-o"),
                    'mail'=> array("label" => __("Mail","zero-bs-crm"), "ico" => "fa-envelope-o"),
                    'meeting'=> array("label" => __("Meeting","zero-bs-crm"), "ico" => "fa-users"),
                    'quote__sent'=> array("label" => __("Quote: Sent","zero-bs-crm"), "ico" => "fa-share-square-o"),
                    'quote__accepted'=> array("label" => __("Quote: Accepted","zero-bs-crm"), "ico" => "fa-thumbs-o-up"),
                    'quote__refused'=> array("label" => __("Quote: Refused","zero-bs-crm"), "ico" => "fa-ban"),
                    'invoice__sent'=> array("label" => __("Invoice: Sent","zero-bs-crm"), "ico" => "fa-share-square-o"),
                    'invoice__part_paid'=> array("label" => __("Invoice: Part Paid","zero-bs-crm"), "ico" => "fa-money"),
                    'invoice__paid'=> array("label" => __("Invoice: Paid","zero-bs-crm"), "ico" => "fa-money"),
                    'invoice__refunded'=> array("label" => __("Invoice: Refunded","zero-bs-crm"), "ico" => "fa-money"),
                    'transaction'=> array("label" => __("Transaction","zero-bs-crm"), "ico" => "fa-credit-card"),
                    'feedback'=> array("label" => __("Feedback","zero-bs-crm"), "ico" => "fa-commenting"),
                    'tweet'=> array("label" => __("Tweet","zero-bs-crm"), "ico" => "fa-twitter"),
                    'facebook_post'=> array("label" => __("Facebook Post","zero-bs-crm"), "ico" => "fa-facebook-official"),
                    'created'=> array("locked" => true, "label" => __("Created","zero-bs-crm"), "ico" => "fa-plus-circle"),
                    'updated'=> array("locked" => true,"label" => __("Updated","zero-bs-crm"), "ico" => "fa-pencil-square-o"),
                    'quote_created'=> array("locked" => true,"label" => __("Quote Created","zero-bs-crm"), "ico" => "fa-plus-circle"),                    
                    'invoice_created'=> array("locked" => true,"label" => __("Invoice Created","zero-bs-crm"), "ico" => "fa-plus-circle"),
                    'event_created'=> array("locked" => true,"label" => __("Event Created","zero-bs-crm"), "ico" => "fa-calendar"),
                    'task_created'=> array("locked" => true,"label" => __("Task Created","zero-bs-crm"), "ico" => "fa-calendar"),
                    'transaction_created'=> array("locked" => true,"label" => __("Transaction Created","zero-bs-crm"), "ico" => "fa-credit-card"),
                    'transaction_updated'=> array("locked" => true,"label" => __("Transaction Updated","zero-bs-crm"), "ico" => "fa-credit-card"),
                    'form_filled'=> array("locked" => true,"label" => __("Form Filled","zero-bs-crm"), "ico" => "fa-wpforms"),
                    'api_action'=> array("locked" => true,"label" => __("API Action","zero-bs-crm"), "ico" => "fa-random"),
                    'bulk_action__merge'=> array("locked" => true,"label" => __("Bulk Action: Merge","zero-bs-crm"), "ico" => "fa-compress"),
                    'client_portal_user_created'=> array("locked" => true,"label" => __("Client Portal User Created","zero-bs-crm"), "ico" => "fa-id-card"),
                    'client_portal_access_changed'=> array("locked" => true,"label" => __("Client Portal Access Changed","zero-bs-crm"), "ico" => "fa-id-card"),
                    'status_change'=> array("locked" => true,"label" => __("Status Change","zero-bs-crm"), "ico" => "fa-random"),
                    'contact_changed_details_via_portal'=> array("locked" => true,"label" => __("Contact Changed Details via Portal","zero-bs-crm"), "ico" => "fa-id-card")

        ),

        'zerobs_company' => array(
        
                    'note'=> array("label" => __("Note","zero-bs-crm"), "ico" => "fa-sticky-note-o"),
                    'call'=> array("label" => __("Call","zero-bs-crm"), "ico" => "fa-phone-square"),
                    'email'=> array("label" => __("Email","zero-bs-crm"), "ico" => "fa-envelope-o"),
                    'created'=> array("locked" => true,"label" => __("Created","zero-bs-crm"), "ico" => "fa-plus-circle"),
                    'updated'=> array("locked" => true,"label" => __("Updated","zero-bs-crm"), "ico" => "fa-pencil-square-o")

        ),

    );
    

    function zeroBSCRM_permifyLogType($logTypeStr=''){

      return strtolower(str_replace(' ','_',str_replace(':','_',$logTypeStr)));
      
    }

    function zeroBSCRM_setupLogTypes(){

        global $zeroBSCRM_logTypes;

        // apply filters
        $zeroBSCRM_logTypes = apply_filters('zbs_logtype_array', $zeroBSCRM_logTypes);

    }

/* ======================================================
  / Declare Globals
   ====================================================== */




/* ======================================================
  Logs (v2 DB2) Metabox
   ====================================================== */

class zeroBS__Metabox_LogsV2 extends zeroBS__Metabox {

    public $objtypeid = false; // child fills out e.g. ZBS_TYPE_CONTACT

    public function __construct( $plugin_file ) {

        // call this 
        $this->initMetabox();

    }

    public function html( $obj, $metabox ) {

            global $zbs; 

      
            $objid = -1; if (is_array($obj) && isset($obj['id'])) $objid = (int)$obj['id'];

            #} Only load if is legit.
            if (in_array($this->postType,array('zerobs_customer'))){

                    #} Proceed

                    #} Retrieve
                    if ($objid > 0) $zbsLogs = $zbs->DAL->getLogsForObj(array(

                            'objtype'       => $this->objtypeid,
                            'objid'         => $objid,

                            'searchPhrase'  => '',

                            'incMeta'       => false,

                            'sortByField'   => 'zbsl_created',
                            'sortOrder'     => 'DESC',
                            'page'          => 0,
                            'perPage'       => 100,

                            'ignoreowner'   => true

                        ));


                    if (!is_array($zbsLogs)) $zbsLogs = array();

                    // FORCE *creation* to be last log
                    // this is because things like "stripe sync" create + add transaction in same second
                    // ... therefore breaking the logic
                    // this is a quick hackaround to make this display right, for #ZBS-732
                    // ... am aware that this defies date... if a situ comes up which makes more sense for this, let me know.
                    $templogs = array(); $creationLog = false; foreach ($zbsLogs as $l){
                        if ($l['type'] == 'created'){

                            // add to this var
                            $creationLog = $l;

                        } else {

                            // normal
                            $templogs[] = $l;

                        }

                    }
                    if (is_array($creationLog)) $templogs[] = $creationLog;
                    $zbsLogs = $templogs; unset($templogs);

            
            ?>
            <?php #} AJAX NONCE ?><script type="text/javascript">var zbscrmjs_logsSecToken = '<?php echo wp_create_nonce( "zbscrmjs-ajax-nonce-logs" ); ?>';</script><?php # END OF NONCE ?>

                <table class="form-table wh-metatab wptbp" id="wptbpMetaBoxLogs">
                    
                    <tr>
                        <td><h2><span id="zbsActiveLogCount"><?php echo zeroBSCRM_prettifyLongInts(count($zbsLogs)); ?></span> <?php _e("Logs","zero-bs-crm");?></h2></td>
                        <td><button type="button" class="ui primary button button-primary button-large" id="zbscrmAddLog"><?php _e("Add Log","zero-bs-crm");?></button></td>
                    </tr>

                    <!-- this line will pop/close with "add log" button -->
                    <tr id="zbsAddLogFormTR" style="display:none"><td colspan="2">


                        <div id="zbsAddLogForm">

                            <div id="zbsAddLogIco">
                                <!-- this will change with select changing... -->
                                <i class="fa fa-sticky-note-o" aria-hidden="true"></i>
                            </div>

                            <label for="zbsAddLogType"><?php _e("Activity Type","zero-bs-crm");?>:</label>
                            <select id="zbsAddLogType" class="form-control zbsUpdateTypeAdd">
                                <?php global $zeroBSCRM_logTypes; 
                                if (isset($zeroBSCRM_logTypes[$this->postType]) && count($zeroBSCRM_logTypes[$this->postType]) > 0) foreach ($zeroBSCRM_logTypes[$this->postType] as $logKey => $logType){

                                    // not for locked logs
                                    if (isset($logType['locked']) && $logType['locked']){
                                        // nope
                                    } else {
                                        ?><option><?php _e($logType['label'],"zero-bs-crm"); ?></option><?php 
                                    }
                                } 

                                /*

                                <!-- hard coded for first fix -->
                                <option disabled="disabled" value="">== General ==</option>
                                <option selected="selected">Note</option>
                                <option disabled="disabled" value="">== Contact ==</option>
                                <option>Call</option>
                                <option>Email</option>
                                <option>Meeting</option>
                                <option disabled="disabled" value="">== Quotes ==</option>
                                <option>Quote: Sent</option>
                                <option>Quote: Accepted</option>
                                <option>Quote: Refused</option>
                                <option disabled="disabled" value="">== Invoices ==</option>
                                <option>Invoice: Sent</option>
                                <option>Invoice: Part Paid</option>
                                <option>Invoice: Paid</option>
                                <option>Invoice: Refunded</option>
                                <option disabled="disabled" value="">== Transaction ==</option>
                                <option>Transaction</option>
                                <option disabled="disabled" value="">== Social Media ==</option>
                                <option>Tweet</option>
                                <option>Facebook Post</option>
                                <option disabled="disabled" value="">== CRM Actions ==</option>
                                <option>Created</option>
                                <option>Updated</option>
                                <option>Quote Created</option>
                                <option>Invoice Created</option>
                                <option>Form Filled</option>

                                */

                                ?>
                            </select>

                            <br />

                            <label for="zbsAddLogMainDesc"><?php _e("Activity Description","zero-bs-crm")?>:</label>
                            <input type="text" class="form-control" id="zbsAddLogMainDesc" placeholder="e.g. <?php _e('Called and talked to Todd about service x, seemed keen',"zero-bs-crm");?>" autocomplete="zbslog-<?php echo time(); ?>" />

                            <label for="zbsAddLogDetailedDesc"><?php _e("Activity Detailed Notes","zero-bs-crm");?>:</label>
                            <textarea class="form-control" id="zbsAddLogDetailedDesc" autocomplete="zbslog-<?php echo time(); ?>"></textarea>

                            <div id="zbsAddLogActions">
                                <div id="zbsAddLogUpdateMsg"></div>
                                <button type="button" class="ui red button button-info button-large" id="zbscrmAddLogCancel"><?php _e("Cancel","zero-bs-crm");?></button>
                                <button type="button" class="ui green button button-primary button-large" id="zbscrmAddLogSave"><?php _e("Save Log","zero-bs-crm");?></button>
                            </div>

                        </div>



                        <!-- edit log form is to be moved about by edit routines :) -->
                        <div id="zbsEditLogForm">

                            <div id="zbsEditLogIco">
                                <!-- this will change with select changing... -->
                                <i class="fa fa-sticky-note-o" aria-hidden="true"></i>
                            </div>

                            <label for="zbsEditLogType"><?php _e("Activity Type","zero-bs-crm");?>:</label>
                            <select id="zbsEditLogType" class="form-control zbsUpdateTypeEdit" autocomplete="zbslog-<?php echo time(); ?>">
                                <?php global $zeroBSCRM_logTypes; 
                                if (isset($zeroBSCRM_logTypes[$this->postType]) && count($zeroBSCRM_logTypes[$this->postType]) > 0) foreach ($zeroBSCRM_logTypes[$this->postType] as $logKey => $logType){

                                    // not for locked logs
                                    if (isset($logType['locked']) && $logType['locked']){
                                        // nope
                                    } else {
                                        ?><option><?php echo $logType['label']; ?></option><?php 
                                    }
                                } 

                                /*
                                <!-- hard coded for first fix -->
                                <option disabled="disabled" value="">== General ==</option>
                                <option>Note</option>
                                <option disabled="disabled" value="">== Contact ==</option>
                                <option>Call</option>
                                <option>Email</option>
                                <option>Meeting</option>
                                <option disabled="disabled" value="">== Quotes ==</option>
                                <option>Quote: Sent</option>
                                <option>Quote: Accepted</option>
                                <option>Quote: Refused</option>
                                <option disabled="disabled" value="">== Invoices ==</option>
                                <option>Invoice: Sent</option>
                                <option>Invoice: Part Paid</option>
                                <option>Invoice: Paid</option>
                                <option>Invoice: Refunded</option>
                                <option disabled="disabled" value="">== Transaction ==</option>
                                <option>Transaction</option>
                                <option disabled="disabled" value="">== Social Media ==</option>
                                <option>Tweet</option>
                                <option>Facebook Post</option>
                                <option disabled="disabled" value="">== CRM Actions ==</option>
                                <option>Created</option>
                                <option>Updated</option>
                                <option>Quote Created</option>
                                <option>Invoice Created</option>
                                <option>Form Filled</option>
                                */

                                ?>
                            </select>

                            <br />

                            <label for="zbsEditLogMainDesc"><?php _e("Activity Description","zero-bs-crm");?>:</label>
                            <input type="text" class="form-control" id="zbsEditLogMainDesc" placeholder="e.g. 'Called and talked to Todd about service x, seemed keen'" autocomplete="zbslog-<?php echo time(); ?>" />

                            <label for="zbsEditLogDetailedDesc"><?php _e("Activity Detailed Notes","zero-bs-crm");?>:</label>
                            <textarea class="form-control" id="zbsEditLogDetailedDesc" autocomplete="zbslog-<?php echo time(); ?>"></textarea>

                            <div id="zbsEditLogActions">
                                <div id="zbsEditLogUpdateMsg"></div>
                                <button type="button" class="button button-info button-large" id="zbscrmEditLogCancel"><?php _e("Cancel","zero-bs-crm");?></button>
                                <button type="button" class="button button-primary button-large" id="zbscrmEditLogSave"><?php _e("Save Log","zero-bs-crm");?></button>
                            </div>

                        </div>




                    </td></tr>

                    <?php if (isset($wDebug)) { ?><tr><td colspan="2"><pre><?php print_r($zbsLogs) ?></pre></td></tr><?php } ?>

                    <tr><td colspan="2">

                        <?php # Output logs (let JS do this!)

                            #if (count($zbsLogs) > 0){ }

                        ?>
                        <div id="zbsAddLogOutputWrap"></div>


                    </td></tr>

                </table>


            <style type="text/css">
                #submitdiv {
                    display:none;
                }
            </style>
            <script type="text/javascript">

                var zbsLogPerms = <?php echo json_encode(array('addedit'=>zeroBSCRM_permsLogsAddEdit(),'delete'=>zeroBSCRM_permsLogsDelete())); ?>;

                var zbsLogAgainstID = <?php echo $objid; ?>; var zbsLogProcessingBlocker = false;

                <?php if (isset($_GET['addlog']) && $_GET['addlog'] == "1"){

                    // this just opens new log for those who've clicked through from another page
                    echo 'var initialiseAddLog = true;';

                }

                
                #} Centralised log types :)
                global $zeroBSCRM_logTypes; 

                #} Build array of locked logs
                $lockedLogs = array();
                if (isset($zeroBSCRM_logTypes[$this->postType]) && count($zeroBSCRM_logTypes[$this->postType]) > 0) foreach ($zeroBSCRM_logTypes[$this->postType] as $logTypeKey => $logTypeDeet){
                    if (isset($logTypeDeet['locked']) && $logTypeDeet['locked']) $lockedLogs[$logTypeKey] = true;
                }
                echo 'var zbsLogsLocked = '.json_encode($lockedLogs).';';

                /*
                var zbsLogsLocked = {
                    'created': true,
                    'updated': true,
                    'quote_created': true,
                    'invoice_created': true,
                    'form_filled': true

                }; */ 

                if (isset($zeroBSCRM_logTypes[$this->postType]) && count($zeroBSCRM_logTypes[$this->postType]) > 0) {

                    echo 'var zbsLogTypes = '.json_encode($zeroBSCRM_logTypes[$this->postType]).';';

                } 

                /*
                var zbsLogTypes = {

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
                    'form_filled': { label: 'Form Filled', ico: 'fa-wpforms' }

                }; */ ?>

                var zbsLogIndex = <?php

                    #} Array or empty
                    if (count($zbsLogs) > 0 && is_array($zbsLogs)) {
                      
                        $zbsLogsExpose = array();
                        foreach ($zbsLogs as $zbsLog){

                            $retLine = $zbsLog;
                            if (isset($retLine) && isset($retLine['longdesc'])) $retLine['longdesc'] = nl2br(zeroBSCRM_textExpose($retLine['longdesc']));

                            // we had an issue where users (Alvaro) had historically copied + pasted <code> blocks with <script>'s into these long desc's
                            // (should strip these on the input end to, will make sure)
                            // ... so here we strip any accidental inclusions of those:
                            $retLine['longdesc'] = wp_strip_all_tags($retLine['longdesc'],true);


                            $zbsLogsExpose[] = $retLine;

                        }

                        echo json_encode($zbsLogsExpose);
                    } else
                        echo json_encode(array());

                ?>;

                var zbsLogEditing = -1;

                // def ico
                var zbsLogDefIco = 'fa-sticky-note-o'; 

                jQuery(function(){

                    // build log ui
                    zbscrmjs_buildLogs();

                    // if url has addlogs=1, init open addlogs :)
                    // passed down from php via var higher up in this file
                    setTimeout(function(){
                        if (typeof window.initialiseAddLog != "undefined"){
                            jQuery('#zbscrmAddLog').trigger( 'click' );
                        }
                    },500);

                    // add log button
                    jQuery('#zbscrmAddLog').on( 'click', function(){


                        if (jQuery(this).css('display') == 'block'){

                            jQuery('#zbsAddLogFormTR').slideDown('400', function() {
                                
                            });

                            jQuery(this).hide();

                        } else {

                            jQuery('#zbsAddLogFormTR').hide();

                            jQuery(this).show();

                        }


                    });

                    // cancel
                    jQuery('#zbscrmAddLogCancel').on( 'click', function(){

                            jQuery('#zbsAddLogFormTR').hide();

                            jQuery('#zbscrmAddLog').show();

                    });

                    // save
                    jQuery('#zbscrmAddLogSave').on( 'click', function(){

                            //jQuery('#zbsAddLogFormTR').hide();

                            //jQuery('#zbscrmAddLog').show();

                            /* 
                            zbsnagainstid
                            zbsntype
                            zbsnshortdesc            
                            zbsnlongdesc
                            zbsnoverwriteid
                            */

                            // get / check data
                            var data = {sec:window.zbscrmjs_logsSecToken,zbsnobjtype:'<?php echo $this->postType; ?>'}; var errs = 0;
                            if ((jQuery('#zbsAddLogType').val()).length > 0) data.zbsntype = jQuery('#zbsAddLogType').val();
                            if ((jQuery('#zbsAddLogMainDesc').val()).length > 0) data.zbsnshortdesc = jQuery('#zbsAddLogMainDesc').val();
                            if ((jQuery('#zbsAddLogDetailedDesc').val()).length > 0) 
                                data.zbsnlongdesc = jQuery('#zbsAddLogDetailedDesc').val();
                            else
                                data.zbsnlongdesc = '';

                            // post id & no need for overwrite id as is new
                            data.zbsnagainstid = parseInt(window.zbsLogAgainstID);

                            // debug console.log('posting new note: ',data);

                            // validate
                            var msgOut = '';
                            if (typeof data.zbsntype == "undefined" || data.zbsntype == '') {
                                errs++;
                                msgOut = 'Note Type is required!'; 
                                jQuery('#zbsAddLogType').css('border','2px solid orange');
                                setTimeout(function(){

                                    jQuery('#zbsAddLogUpdateMsg').html('');
                                    jQuery('#zbsAddLogType').css('border','1px solid #ddd');

                                },1500);
                            }
                            if (typeof data.zbsnshortdesc == "undefined" || data.zbsnshortdesc == '') {
                                errs++;
                                if (msgOut == 'Note Type is required!') 
                                    msgOut = 'Note Type and Description are required!'; 
                                else
                                    msgOut += 'Note Description is required!'; 
                                jQuery('#zbsAddLogMainDesc').css('border','2px solid orange');
                                setTimeout(function(){

                                    jQuery('#zbsAddLogUpdateMsg').html('');
                                    jQuery('#zbsAddLogMainDesc').css('border','1px solid #ddd');

                                },1500);
                            }

                            if (errs === 0){

                                // add action
                                data.action = 'zbsaddlog';

                                zbscrmjs_addNewNote(data,function(newLog){

                                    // success

                                        // msg
                                        jQuery('#zbsAddLogUpdateMsg').html('Saved!');

                                        // then hide form, build new log gui, clear form

                                            // hide + clear form
                                            jQuery('#zbsAddLogFormTR').hide();
                                            jQuery('#zbscrmAddLog').show();
                                            jQuery('#zbsAddLogType').val('Note');
                                            jQuery('#zbsAddLogMainDesc').val('');
                                            jQuery('#zbsAddLogDetailedDesc').val('');
                                            jQuery('#zbsAddLogUpdateMsg').html('');

                                        // add it (build example obj)
                                        var newLogObj = {
                                            id: newLog.logID,
                                            created: '', //moment(),
                                            type: newLog.zbsntype,
                                            shortdesc: newLog.zbsnshortdesc,
                                            longdesc: zbscrmjs_nl2br(newLog.zbsnlongdesc)
                                        }
                                        zbscrmjs_addNewNoteLine(newLogObj,true);

                                        // also add to window obj
                                        window.zbsLogIndex.push(newLogObj);


                                        // bind ui
                                        setTimeout(function(){
                                            zbscrmjs_bindNoteUIJS();
                                            zbscrmjs_updateLogCount();
                                        },0);


                                },function(){

                                    // failure

                                        // msg + do nothing
                                        jQuery('#zbsAddLogUpdateMsg').html('There was an error when saving this note!');

                                });

                            } else {
                                if (typeof msgOut !== "undefined" && msgOut != '') jQuery('#zbsAddLogUpdateMsg').html(msgOut); 
                            }

                    });


                    // note ico - works for both edit + add
                    jQuery('#zbsAddLogType, #zbsEditLogType').on( 'change', function(){

                        // get perm
                        var logPerm = zbscrmjs_permify(jQuery(this).val()); // jQuery('#zbsAddLogType').val()

                        var thisIco = window.zbsLogDefIco;
                        // find ico
                        if (typeof window.zbsLogTypes[logPerm] != "undefined") thisIco = window.zbsLogTypes[logPerm].ico;

                        // override all existing classes with ones we want:
                        if (jQuery(this).hasClass('zbsUpdateTypeAdd')) jQuery('#zbsAddLogIco i').attr('class','fa ' + thisIco);
                        if (jQuery(this).hasClass('zbsUpdateTypeEdit')) jQuery('#zbsEditLogIco i').attr('class','fa ' + thisIco);

                    });


                });

                function zbscrmjs_updateLogCount(){

                    var count = 0; 
                    if (window.zbsLogIndex.length > 0) count = parseInt(window.zbsLogIndex.length);
                    jQuery('#zbsActiveLogCount').html(zbscrmjs_prettifyLongInts(count));

                }

                // build log ui
                function zbscrmjs_buildLogs(){

                    // get from obj
                    var theseLogs = window.zbsLogIndex;


                    jQuery.each(theseLogs,function(ind,ele){

                        zbscrmjs_addNewNoteLine(ele);

                    });

                    // bind ui
                    setTimeout(function(){
                        zbscrmjs_bindNoteUIJS();
                        zbscrmjs_updateLogCount();
                    },0);

                }

                function zbscrmjs_addNewNoteLine(ele,prepflag,replaceExisting){

                        // localise
                        var logMeta = ele; if (typeof ele.meta != "undefined") logMeta = ele.meta;

                        // get perm
                        var logPerm = zbscrmjs_permify(logMeta.type);

                        // build it
                        var thisLogHTML = '<div class="zbsLogOut" data-logid="' + ele.id + '" id="zbsLogOutLine' + ele.id + '" data-logtype="' + logPerm + '">';


                            // type ico
                                
                                var thisIco = window.zbsLogDefIco;
                                // find ico
                                if (typeof window.zbsLogTypes[logPerm] != "undefined") thisIco = window.zbsLogTypes[logPerm].ico;

                                // output
                                thisLogHTML += '<div class="zbsLogOutIco"><i class="fa ' + thisIco + '" aria-hidden="true"></i></div>';


                            // created date
                            if (typeof ele.created !== "undefined" && ele.created !== '' && typeof ele.createduts !== "undefined" && ele.createduts !== '') {
                                
                                // timezone offset?
                                // Here we get the UTS offset in minutes from zbs_root
                                var offsetMins = 0; if (typeof window.zbs_root.timezone_offset_mins != "undefined") offsetMins = parseInt(window.zbs_root.timezone_offset_mins);
                                // Here we create a moment instance in correct timezone(offset) using original created unix timestamp in UTC
                                var createdMoment = moment.unix(parseInt(ele.createduts)).utcOffset(offsetMins);
                                

                                thisLogHTML += '<div class="zbsLogOutCreated" data-zbscreated="' + ele.created + '" title="' + createdMoment.format('lll') + '">' + createdMoment.format('lll') + '</div>';

                            } else {

                                // empty created means just created obj
                                var createdMoment = moment();
                                thisLogHTML += '<div class="zbsLogOutCreated" data-zbscreated="' + createdMoment + '" title="' + createdMoment.format('llll') + '">' + createdMoment.fromNow() + '</div>';

                            }

                            // title

                                var thisTitle = '';

                                // find type
                                var thisType = ucwords(logMeta.type);
                                if (typeof window.zbsLogTypes[logPerm] != "undefined") thisType = window.zbsLogTypes[logPerm].label;

                                // type
                                if (typeof thisType !== "undefined") thisTitle += '<span>' + thisType + '</span>';

                                // desc
                                if (typeof logMeta.shortdesc !== "undefined") {

                                    if (thisTitle != '') thisTitle += ': ';
                                    thisTitle += logMeta.shortdesc;

                                }

                                var logEditElements = '<div class="zbsLogOutEdits"><i class="fa fa-pencil-square-o zbsLogActionEdit" title="<?php _e('Edit Log',"zero-bs-crm");?>"></i><i class="fa fa-trash-o zbsLogActionRemove last" title="<?php _e('Delete Log',"zero-bs-crm");?>"></i></div>';
                                thisLogHTML += '<div class="zbsLogOutTitle">' + thisTitle + logEditElements + '</div>';

                            // desc
                           if (typeof logMeta.longdesc !== "undefined" && logMeta.longdesc !== '' && logMeta.longdesc !== null) thisLogHTML += '<div class="zbsLogOutDesc">' + logMeta.longdesc + '</div>';

                            thisLogHTML += '</div>';


                        if (typeof replaceExisting == "undefined"){

                            // normal

                            // add it
                            if (typeof prepflag !== "undefined")
                                jQuery('#zbsAddLogOutputWrap').prepend(thisLogHTML);
                            else
                                jQuery('#zbsAddLogOutputWrap').append(thisLogHTML);


                        } else {

                            // replace existing
                            jQuery('#zbsLogOutLine' + ele.id).replaceWith(thisLogHTML);

                        }

                }

                function zbscrmjs_bindNoteUIJS(){

                    // show hide edit controls
                    jQuery('.zbsLogOut').on( 'mouseenter', function(){

                        var logType = jQuery(this).attr('data-logtype');

                        // only if not editing another :) + Log Type is not one we don't have in our set
                        if (window.zbsLogEditing == -1 && typeof window.zbsLogTypes[logType] != "undefined"){

                            // check if locked log or not! 
                            if (typeof logType == "undefined") logType = '';

                            // if log type empty, or has a key in window.zbsLogsLocked, don't allow edits
                            // ... and finally check perms too 
                            if (
                                logType != '' && !window.zbsLogsLocked.hasOwnProperty(logType) && 
                                window.zbsLogPerms.addedit // can add edit
                                ){

                                    // check if can delete
                                    if (window.zbsLogPerms.delete){
                                        // can
                                        jQuery('.zbsLogActionRemove',jQuery(this)).css('display','inline-block');

                                    } else {
                                        // can't
                                        jQuery('.zbsLogActionRemove',jQuery(this)).css('display','none');
                                    }
                                    
                                // yep (overall)
                                jQuery('.zbsLogOutEdits',jQuery(this)).css('display','inline-block');

                            }

                        }

                    }).on( 'mouseleave', function(){

                        jQuery('.zbsLogOutEdits',jQuery(this)).not('.stayhovered').css('display','none');

                    });

                    // bind del
                    jQuery('.zbsLogOutEdits .zbsLogActionRemove').off('click').on( 'click', function(){

                        if (window.zbsLogPerms.delete){

                            // append "deleting"
                            jQuery(this).closest('.zbsLogOutEdits').addClass('stayhovered').append('<span>Deleting...</span>');

                            var noteID = parseInt(jQuery(this).closest('.zbsLogOut').attr('data-logid'));

                            if (noteID > 0){

                                var thisEle = this;

                                zbscrmjs_deleteNote(noteID,function(){

                                    // success

                                        // localise
                                        var nID = noteID;

                                        // append "deleted" and then vanish
                                        jQuery('span',jQuery(thisEle).closest('.zbsLogOutEdits')).html('Deleted!...');

                                        var that = thisEle;
                                        setTimeout(function(){

                                            // localise
                                            var thisNoteID = nID;

                                            // also del from window obj
                                            zbscrmjs_removeItemFromLogIndx(thisNoteID);

                                            // update count span
                                            zbscrmjs_updateLogCount();

                                            // slide up
                                            jQuery(that).closest('.zbsLogOut').slideUp(400,function(){

                                                // and remove itself?

                                            });
                                        },500);

                                },function(){

                                    //TODO: proper error msg
                                    console.error('There was an issue retrieving this note for editing/deleting'); 

                                });

                            } else console.error('There was an issue retrieving this note for editing/deleting'); //TODO: proper error msg

                        } // if perms

                    });

                    // bind edit
                    jQuery('.zbsLogOutEdits .zbsLogActionEdit').off('click').on( 'click', function(){

                        if (window.zbsLogPerms.addedit){

                            // one at a time please sir...
                            if (window.zbsLogEditing == -1){

                                // get edit id
                                var noteID = parseInt(jQuery(this).closest('.zbsLogOut').attr('data-logid'));

                                // get edit obj
                                var editObj = zbscrmjs_retrieveItemFromIndex(noteID);

                                // move edit box to before here
                                jQuery('#zbsEditLogForm').insertBefore('#zbsLogOutLine' + noteID);

                                setTimeout(function(){

                                    var lObj = editObj;
                                    if (typeof lObj.meta != "undefined") lObj = lObj.meta; // pre dal2

                                    // update edit box texts etc.
                                    jQuery('#zbsEditLogMainDesc').val(lObj.shortdesc);
                                    jQuery('#zbsEditLogDetailedDesc').val(zbscrmjs_reversenl2br(lObj.longdesc));
                                    jQuery('#zbsEditLogType option').each(function(){
                                        if (jQuery(this).text() == lObj.type) {
                                            jQuery(this).attr('selected', 'selected');
                                            return false;
                                        }
                                        return true;
                                    });
                                
                                    // type ico

                                        // get perm
                                        var logPerm = zbscrmjs_permify(lObj.type);
                                    
                                        var thisIco = window.zbsLogDefIco;
                                        // find ico
                                        if (typeof window.zbsLogTypes[logPerm] != "undefined") thisIco = window.zbsLogTypes[logPerm].ico;

                                        // update
                                        jQuery('#zbsEditLogIco i').attr('class','fa ' + thisIco);


                                },10);

                                // set edit vars
                                window.zbsLogEditing = noteID;

                                // hide line / show edit
                                jQuery('#zbsLogOutLine' + noteID).slideUp();
                                jQuery('#zbsEditLogForm').slideDown();

                                // bind
                                zbscrmjs_bindEditNote();

                            }
                       
                       } // if perms

                    });

                }

                function zbscrmjs_bindEditNote(){


                        // cancel
                        jQuery('#zbscrmEditLogCancel').on( 'click', function(){

                                // get note id
                                var noteID = window.zbsLogEditing;

                                // hide edit from
                                jQuery('#zbsEditLogForm').hide();

                                // show back log
                                jQuery('#zbsLogOutLine' + noteID).show();

                                // unset noteID
                                window.zbsLogEditing = -1;

                        });

                        // save
                        jQuery('#zbscrmEditLogSave').on( 'click', function(){

                                if (window.zbsLogEditing > -1){

                                        // get note id
                                        var noteID = window.zbsLogEditing;

                                        //jQuery('#zbsEditLogFormTR').hide();

                                        //jQuery('#zbscrmEditLog').show();

                                        /* 
                                        zbsnagainstid
                                        zbsntype
                                        zbsnshortdesc            
                                        zbsnlongdesc
                                        zbsnoverwriteid
                                        */

                                        // get / check data
                                        var data = {sec:window.zbscrmjs_logsSecToken,zbsnobjtype:'<?php echo $this->postType; ?>'}; var errs = 0;

                                        // same as add code, but with note id:
                                        data.zbsnprevid = noteID;

                                        if ((jQuery('#zbsEditLogType').val()).length > 0) data.zbsntype = jQuery('#zbsEditLogType').val();
                                        if ((jQuery('#zbsEditLogMainDesc').val()).length > 0) data.zbsnshortdesc = jQuery('#zbsEditLogMainDesc').val();
                                        if ((jQuery('#zbsEditLogDetailedDesc').val()).length > 0) 
                                            data.zbsnlongdesc = jQuery('#zbsEditLogDetailedDesc').val();
                                        else
                                            data.zbsnlongdesc = '';

                                        // post id & no need for overwrite id as is new
                                        data.zbsnagainstid = parseInt(window.zbsLogAgainstID);

                                        // validate
                                        var msgOut = '';
                                        if (typeof data.zbsntype == "undefined" || data.zbsntype == '') {
                                            errs++;
                                            msgOut = 'Note Type is required!'; 
                                            jQuery('#zbsEditLogType').css('border','2px solid orange');
                                            setTimeout(function(){

                                                jQuery('#zbsEditLogUpdateMsg').html('');
                                                jQuery('#zbsEditLogType').css('border','1px solid #ddd');

                                            },1500);
                                        }
                                        if (typeof data.zbsnshortdesc == "undefined" || data.zbsnshortdesc == '') {
                                            errs++;
                                            if (msgOut == 'Note Type is required!') 
                                                msgOut = 'Note Type and Description are required!'; 
                                            else
                                                msgOut += 'Note Description is required!'; 
                                            jQuery('#zbsEditLogMainDesc').css('border','2px solid orange');
                                            setTimeout(function(){

                                                jQuery('#zbsEditLogUpdateMsg').html('');
                                                jQuery('#zbsEditLogMainDesc').css('border','1px solid #ddd');

                                            },1500);
                                        }


                                        if (errs === 0){

                                            // add action
                                            data.action = 'zbsupdatelog';

                                            zbscrmjs_updateNote(data,function(newLog){

                                                // success

                                                    // msg
                                                    jQuery('#zbsEditLogUpdateMsg').html('Changes Saved!');

                                                    // then hide form, build new log gui, clear form

                                                        // hide + clear form
                                                        jQuery('#zbsEditLogForm').hide();
                                                        jQuery('#zbsEditLogType').val('Note');
                                                        jQuery('#zbsEditLogMainDesc').val('');
                                                        jQuery('#zbsEditLogDetailedDesc').val('');
                                                        jQuery('#zbsEditLogUpdateMsg').html('');

                                                    // update it (build example obj)
                                                    var newLogObj = {
                                                        id: newLog.logID,
                                                        created: '', //  moment().subtract(window.zbsCRMTimeZoneOffset, 'h')
                                                        meta: {

                                                            type: newLog.zbsntype,
                                                            shortdesc: newLog.zbsnshortdesc,
                                                            // have to replace the nl2br for long desc:
                                                            longdesc: zbscrmjs_nl2br(newLog.zbsnlongdesc)

                                                        }
                                                    }
                                                    zbscrmjs_addNewNoteLine(newLogObj,true,true); // third param here is "replace existing"

                                                    // also add to window obj in prev place
                                                    //window.zbsLogIndex.push(newLogObj);
                                                    zbscrmjs_replaceItemInLogIndx(newLog.logID,newLogObj);

                                                    // unset noteID
                                                    window.zbsLogEditing = -1;

                                                    // bind ui
                                                    setTimeout(function(){
                                                        zbscrmjs_bindNoteUIJS();
                                                        zbscrmjs_updateLogCount();
                                                    },0);


                                            },function(){

                                                // failure

                                                    // msg + do nothing
                                                    jQuery('#zbsEditLogUpdateMsg').html('There was an error when saving this note!');

                                            });

                                        } else {
                                            if (typeof msgOut !== "undefined" && msgOut != '') jQuery('#zbsEditLogUpdateMsg').html(msgOut); 
                                        }


                                } // if note id

                        });


                }

                function zbscrmjs_removeItemFromLogIndx(noteID){

                    var logIndex = window.zbsLogIndex;
                    var newLogIndex = [];

                    jQuery.each(logIndex,function(ind,ele){

                        if (typeof ele.id != "undefined" && ele.id != noteID) newLogIndex.push(ele);

                    });

                    window.zbsLogIndex = newLogIndex;

                    // fini
                    return window.zbsLogIndex;

                }

                function zbscrmjs_replaceItemInLogIndx(noteIDToReplace,newObj){

                    var logIndex = window.zbsLogIndex;
                    var newLogIndex = [];

                    jQuery.each(logIndex,function(ind,ele){

                        if (typeof ele.id != "undefined")
                            if (ele.id != noteIDToReplace) 
                                newLogIndex.push(ele);
                            else
                                // is to replace
                                newLogIndex.push(newObj);

                    });

                    window.zbsLogIndex = newLogIndex;

                    // fini
                    return window.zbsLogIndex;

                }

                function zbscrmjs_retrieveItemFromIndex(noteID){

                    var logIndex = window.zbsLogIndex;
                    var logObj = -1;

                    jQuery.each(logIndex,function(ind,ele){

                        if (typeof ele.id != "undefined" && ele.id == noteID) logObj = ele;

                    });

                    return logObj;
                }

                

                // function assumes a legit dataArr :) (validate above)
                function zbscrmjs_addNewNote(dataArr,cb,errcb){
                    
                    // needs nonce. <!--#NONCENEEDED -->

                    if (!window.zbsLogProcessingBlocker){
                        
                        // blocker
                        window.zbsLogProcessingBlocker = true;

                        // msg
                        jQuery('#zbsAddLogUpdateMsg').html('<?php _e('Saving...',"zero-bs-crm");?>');

                         // Send 
                            jQuery.ajax({
                                  type: "POST",
                                  url: ajaxurl, // admin side is just ajaxurl not wptbpAJAX.ajaxurl,
                                  "data": dataArr,
                                  dataType: 'json',
                                  timeout: 20000,
                                  success: function(response) {

                                    // Debug  console.log("RESPONSE",response);

                                    // blocker
                                    window.zbsLogProcessingBlocker = false;

                                    // this also has true/false on update... 
                                    if (typeof response.processed != "undefined" && response.processed){

                                        // callback
                                        // make a merged item... 
                                        var retArr = dataArr; dataArr.logID = response.processed;
                                        if (typeof cb == "function") cb(retArr);

                                    } else {

                                        // .. was an error :)

                                        // callback
                                        if (typeof errcb == "function") errcb(response);

                                    }


                                  },
                                  error: function(response){ 

                                    // Debug  console.error("RESPONSE",response);

                                    // blocker
                                    window.zbsLogProcessingBlocker = false;

                                    // callback
                                    if (typeof errcb == "function") errcb(response);



                                  }

                            });


                    } else {
                        
                        // end of blocker
                        jQuery('#zbsAddLogUpdateMsg').html('... already processing!');
                        setTimeout(function(){

                            jQuery('#zbsAddLogUpdateMsg').html('');

                        },2000);

                    }

                }

                // function assumes a legit dataArr :) (validate above)
                // is almost a clone of _addNote (homogenise later)
                function zbscrmjs_updateNote(dataArr,cb,errcb){
                    
                    // needs nonce. <!--#NONCENEEDED -->

                    if (!window.zbsLogProcessingBlocker){
                        
                        // blocker
                        window.zbsLogProcessingBlocker = true;

                        // msg
                        jQuery('#zbsEditLogUpdateMsg').html('<?php _e('Saving...',"zero-bs-crm");?>');

                         // Send 
                            jQuery.ajax({
                                  type: "POST",
                                  url: ajaxurl, // admin side is just ajaxurl not wptbpAJAX.ajaxurl,
                                  "data": dataArr,
                                  dataType: 'json',
                                  timeout: 20000,
                                  success: function(response) {

                                    // Debug  console.log("RESPONSE",response);

                                    // blocker
                                    window.zbsLogProcessingBlocker = false;

                                    // this also has true/false on update... 
                                    if (typeof response.processed != "undefined" && response.processed){

                                        // callback
                                        // make a merged item... 
                                        var retArr = dataArr; dataArr.logID = response.processed;
                                        if (typeof cb == "function") cb(retArr);

                                    } else {

                                        // .. was an error :)

                                        // callback
                                        if (typeof errcb == "function") errcb(response);

                                    }


                                  },
                                  error: function(response){ 

                                    // Debug  console.error("RESPONSE",response);

                                    // blocker
                                    window.zbsLogProcessingBlocker = false;

                                    // callback
                                    if (typeof errcb == "function") errcb(response);



                                  }

                            });


                    } else {
                        
                        // end of blocker
                        jQuery('#zbsEditLogUpdateMsg').html('... already processing!');
                        setTimeout(function(){

                            jQuery('#zbsEditLogUpdateMsg').html('');

                        },2000);

                    }

                }


                // function assumes a legit noteID + perms :) (validate above)
                function zbscrmjs_deleteNote(noteID,cb,errcb){
                    
                    // needs nonce. <!--#NONCENEEDED -->

                    if (!window.zbsLogProcessingBlocker){
                        
                        // blocker
                        window.zbsLogProcessingBlocker = true;

                        // -package
                        var dataArr = {
                            action : 'zbsdellog',
                            zbsnid : noteID,
                            sec:window.zbscrmjs_logsSecToken
                        };

                         // Send 
                            jQuery.ajax({
                                  type: "POST",
                                  url: ajaxurl, // admin side is just ajaxurl not wptbpAJAX.ajaxurl,
                                  "data": dataArr,
                                  dataType: 'json',
                                  timeout: 20000,
                                  success: function(response) {

                                    // Debug  console.log("RESPONSE",response);

                                    // blocker
                                    window.zbsLogProcessingBlocker = false;

                                    // this also has true/false on update... 
                                    if (typeof response.processed != "undefined" && response.processed){

                                        // Debug console.log("SUCCESS");

                                        // callback
                                        if (typeof cb == "function") cb(response);

                                    } else {

                                        // .. was an error :)
                                        // Debug console.log("ERRZ");                                    

                                        // callback
                                        if (typeof errcb == "function") errcb(response);

                                    }


                                  },
                                  error: function(response){ 

                                    // Debug  console.error("RESPONSE",response);

                                    // blocker
                                    window.zbsLogProcessingBlocker = false;

                                    // callback
                                    if (typeof errcb == "function") errcb(response);



                                  }

                            });


                    } else {
                        
                        // end of blocker

                    }

                }

                
                </script><?php

        } // / if post type


    }

    public function save_data( $objID, $obj ) {

        // not req. ajax

        return $obj;
    }
}


/* ======================================================
  / Logs V2 - DB2 Metabox
   ====================================================== */


/* ======================================================
  Logs (v1) Metabox
   ====================================================== */

    class zeroBS__Metabox_Logs {

        static $instance;
        #private $postType;
        private $postTypes;

        public function __construct( $plugin_file ) {
           # if ( $this->instance instanceof wProject_Metabox ) {
            #    wp_die( sprintf( __( 'Cannot instantiate singleton class: %1$s. Use %1$s::$instance instead.', 'zero-bs-crm' ), __CLASS__ ) );
            #} else {
                self::$instance = $this;
            #}

            #$this->postType = 'zerobs_customer';
            #add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );
            #} Moved to multiples 1.1.19 WH
            $this->postTypes = array('zerobs_company');  // Customer Logs -> DB2
            add_action( 'add_meta_boxes', array( $this, 'initMetaBox' ) );

            
            add_filter( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
        }

        public function initMetaBox(){

            if (count($this->postTypes) > 0) foreach ($this->postTypes as $pt){

                #} pass an arr
                $callBackArr = array($this,$pt);

                add_meta_box(
                    'wpzbsc_logdetails_'.$pt,
                    __('Activity Log',"zero-bs-crm"),
                    array( $this, 'print_meta_box' ),
                    $pt,
                    'normal',
                    'high',
                    $callBackArr
                );

            }

        }
        /*
        public function create_meta_box() {

            #'wptbp'.$this->postType

            add_meta_box(
                'wpzbsc_logdetails',
                'Activity Log',
                array( $this, 'print_meta_box' ),
                $this->postType,
                'normal',
                'high'
            );
        }
        */

        public function print_meta_box( $post, $metabox ) {

                
            /*

    $metabox is array as follows :

    #echo '<pre>'; print_r($metabox); echo '</pre>';


            Array
    (
        [id] => wpzbsc_logdetails_zerobs_company
        [title] => Activity Log
        [callback] => Array
            (
                [0] => zeroBS__Metabox_Logs Object
                    (
                        [postTypes:zeroBS__Metabox_Logs:private] => Array
                            (
                                [0] => zerobs_customer
                                [1] => zerobs_company
                            )

                    )

                [1] => print_meta_box
            )

        [args] => Array
            (
                [0] => zeroBS__Metabox_Logs Object
                    (
                        [postTypes:zeroBS__Metabox_Logs:private] => Array
                            (
                                [0] => zerobs_customer
                                [1] => zerobs_company
                            )

                    )

                [1] => zerobs_company
            )

    )
    */

                #} Post type
                $postType = ''; if (isset($metabox['args']) && isset($metabox['args'][1]) && !empty($metabox['args'][1])) $postType = $metabox['args'][1];

                #} Only load if is legit.
                if (in_array($postType,array('zerobs_customer','zerobs_company'))){

                    #} Proceed

                    #} retrieve
                    if ($postType == 'zerobs_customer') $zbsLogs = zeroBSCRM_getContactLogs($post->ID,true);
                    if ($postType == 'zerobs_company') $zbsLogs = zeroBSCRM_getCompanyLogs($post->ID,true);

                   //echo '<pre>'; print_r($zbsLogs); echo '</pre>';
            
            ?>
                <input type="hidden" name="meta_box_ids[]" value="<?php echo $metabox['id']; ?>" />
                <?php wp_nonce_field( 'save_' . $metabox['id'], $metabox['id'] . '_nonce' ); ?>
                <?php wp_nonce_field( 'save_zbslog', 'save_zbslog_nce' ); #thisone is for the custom save_post func in main file ?>
                <?php #} AJAX NONCE ?><script type="text/javascript">var zbscrmjs_logsSecToken = '<?php echo wp_create_nonce( "zbscrmjs-ajax-nonce-logs" ); ?>';</script><?php # END OF NONCE ?>

                <table class="form-table wh-metatab wptbp" id="wptbpMetaBoxLogs">
                    
                    <tr>
                        <td><h2><span id="zbsActiveLogCount"><?php echo zeroBSCRM_prettifyLongInts(count($zbsLogs)); ?></span> <?php _e("Logs","zero-bs-crm");?></h2></td>
                        <td><button type="button" class="ui primary button button-primary button-large" id="zbscrmAddLog"><?php _e("Add Log","zero-bs-crm");?></button></td>
                    </tr>

                    <!-- this line will pop/close with "add log" button -->
                    <tr id="zbsAddLogFormTR" style="display:none"><td colspan="2">


                        <div id="zbsAddLogForm">

                            <div id="zbsAddLogIco">
                                <!-- this will change with select changing... -->
                                <i class="fa fa-sticky-note-o" aria-hidden="true"></i>
                            </div>

                            <label for="zbsAddLogType"><?php _e("Activity Type","zero-bs-crm");?>:</label>
                            <select id="zbsAddLogType" class="form-control zbsUpdateTypeAdd">
                                <?php global $zeroBSCRM_logTypes; 
                                if (isset($zeroBSCRM_logTypes[$postType]) && count($zeroBSCRM_logTypes[$postType]) > 0) foreach ($zeroBSCRM_logTypes[$postType] as $logKey => $logType){

                                    // not for locked logs
                                    if (isset($logType['locked']) && $logType['locked']){
                                        // nope
                                    } else {
                                        ?><option><?php _e($logType['label'],"zero-bs-crm"); ?></option><?php 
                                    }
                                } 

                                /*

                                <!-- hard coded for first fix -->
                                <option disabled="disabled" value="">== General ==</option>
                                <option selected="selected">Note</option>
                                <option disabled="disabled" value="">== Contact ==</option>
                                <option>Call</option>
                                <option>Email</option>
                                <option>Meeting</option>
                                <option disabled="disabled" value="">== Quotes ==</option>
                                <option>Quote: Sent</option>
                                <option>Quote: Accepted</option>
                                <option>Quote: Refused</option>
                                <option disabled="disabled" value="">== Invoices ==</option>
                                <option>Invoice: Sent</option>
                                <option>Invoice: Part Paid</option>
                                <option>Invoice: Paid</option>
                                <option>Invoice: Refunded</option>
                                <option disabled="disabled" value="">== Transaction ==</option>
                                <option>Transaction</option>
                                <option disabled="disabled" value="">== Social Media ==</option>
                                <option>Tweet</option>
                                <option>Facebook Post</option>
                                <option disabled="disabled" value="">== CRM Actions ==</option>
                                <option>Created</option>
                                <option>Updated</option>
                                <option>Quote Created</option>
                                <option>Invoice Created</option>
                                <option>Form Filled</option>

                                */

                                ?>
                            </select>

                            <br />

                            <label for="zbsAddLogMainDesc"><?php _e("Activity Description","zero-bs-crm")?>:</label>
                            <input type="text" class="form-control" id="zbsAddLogMainDesc" placeholder="e.g. <?php _e('Called and talked to Todd about service x, seemed keen',"zero-bs-crm");?>" autocomplete="zbslog-<?php echo time(); ?>" />

                            <label for="zbsAddLogDetailedDesc"><?php _e("Activity Detailed Notes","zero-bs-crm");?>:</label>
                            <textarea class="form-control" id="zbsAddLogDetailedDesc" autocomplete="zbslog-<?php echo time(); ?>"></textarea>

                            <div id="zbsAddLogActions">
                                <div id="zbsAddLogUpdateMsg"></div>
                                <button type="button" class="ui red button button-info button-large" id="zbscrmAddLogCancel"><?php _e("Cancel","zero-bs-crm");?></button>
                                <button type="button" class="ui green button button-primary button-large" id="zbscrmAddLogSave"><?php _e("Save Log","zero-bs-crm");?></button>
                            </div>

                        </div>



                        <!-- edit log form is to be moved about by edit routines :) -->
                        <div id="zbsEditLogForm">

                            <div id="zbsEditLogIco">
                                <!-- this will change with select changing... -->
                                <i class="fa fa-sticky-note-o" aria-hidden="true"></i>
                            </div>

                            <label for="zbsEditLogType"><?php _e("Activity Type","zero-bs-crm");?>:</label>
                            <select id="zbsEditLogType" class="form-control zbsUpdateTypeEdit" autocomplete="zbslog-<?php echo time(); ?>">
                                <?php global $zeroBSCRM_logTypes; 
                                if (isset($zeroBSCRM_logTypes[$postType]) && count($zeroBSCRM_logTypes[$postType]) > 0) foreach ($zeroBSCRM_logTypes[$postType] as $logKey => $logType){

                                    // not for locked logs
                                    if (isset($logType['locked']) && $logType['locked']){
                                        // nope
                                    } else {
                                        ?><option><?php echo $logType['label']; ?></option><?php 
                                    }
                                } 

                                /*
                                <!-- hard coded for first fix -->
                                <option disabled="disabled" value="">== General ==</option>
                                <option>Note</option>
                                <option disabled="disabled" value="">== Contact ==</option>
                                <option>Call</option>
                                <option>Email</option>
                                <option>Meeting</option>
                                <option disabled="disabled" value="">== Quotes ==</option>
                                <option>Quote: Sent</option>
                                <option>Quote: Accepted</option>
                                <option>Quote: Refused</option>
                                <option disabled="disabled" value="">== Invoices ==</option>
                                <option>Invoice: Sent</option>
                                <option>Invoice: Part Paid</option>
                                <option>Invoice: Paid</option>
                                <option>Invoice: Refunded</option>
                                <option disabled="disabled" value="">== Transaction ==</option>
                                <option>Transaction</option>
                                <option disabled="disabled" value="">== Social Media ==</option>
                                <option>Tweet</option>
                                <option>Facebook Post</option>
                                <option disabled="disabled" value="">== CRM Actions ==</option>
                                <option>Created</option>
                                <option>Updated</option>
                                <option>Quote Created</option>
                                <option>Invoice Created</option>
                                <option>Form Filled</option>
                                */

                                ?>
                            </select>

                            <br />

                            <label for="zbsEditLogMainDesc"><?php _e("Activity Description","zero-bs-crm");?>:</label>
                            <input type="text" class="form-control" id="zbsEditLogMainDesc" placeholder="e.g. 'Called and talked to Todd about service x, seemed keen'" autocomplete="zbslog-<?php echo time(); ?>" />

                            <label for="zbsEditLogDetailedDesc"><?php _e("Activity Detailed Notes","zero-bs-crm");?>:</label>
                            <textarea class="form-control" id="zbsEditLogDetailedDesc" autocomplete="zbslog-<?php echo time(); ?>"></textarea>

                            <div id="zbsEditLogActions">
                                <div id="zbsEditLogUpdateMsg"></div>
                                <button type="button" class="button button-info button-large" id="zbscrmEditLogCancel"><?php _e("Cancel","zero-bs-crm");?></button>
                                <button type="button" class="button button-primary button-large" id="zbscrmEditLogSave"><?php _e("Save Log","zero-bs-crm");?></button>
                            </div>

                        </div>




                    </td></tr>

                    <?php if (isset($wDebug)) { ?><tr><td colspan="2"><pre><?php print_r($zbsLogs) ?></pre></td></tr><?php } ?>

                    <tr><td colspan="2">

                        <?php # Output logs (let JS do this!)

                            #if (count($zbsLogs) > 0){ }

                        ?>
                        <div id="zbsAddLogOutputWrap"></div>


                    </td></tr>

                </table>


            <style type="text/css">
                #submitdiv {
                    display:none;
                }
            </style>
            <script type="text/javascript">

                var zbsLogAgainstID = <?php echo $post->ID; ?>; var zbsLogProcessingBlocker = false;

                <?php 
                #} Centralised log types :)
                global $zeroBSCRM_logTypes; 

                #} Build array of locked logs
                $lockedLogs = array();
                if (isset($zeroBSCRM_logTypes[$postType]) && count($zeroBSCRM_logTypes[$postType]) > 0) foreach ($zeroBSCRM_logTypes[$postType] as $logTypeKey => $logTypeDeet){
                    if (isset($logTypeDeet['locked']) && $logTypeDeet['locked']) $lockedLogs[$logTypeKey] = true;
                }
                echo 'var zbsLogsLocked = '.json_encode($lockedLogs).';';

                /*
                var zbsLogsLocked = {
                    'created': true,
                    'updated': true,
                    'quote_created': true,
                    'invoice_created': true,
                    'form_filled': true

                }; */ 

                if (isset($zeroBSCRM_logTypes[$postType]) && count($zeroBSCRM_logTypes[$postType]) > 0) {

                    echo 'var zbsLogTypes = '.json_encode($zeroBSCRM_logTypes[$postType]).';';

                } 

                /*
                var zbsLogTypes = {

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
                    'form_filled': { label: 'Form Filled', ico: 'fa-wpforms' }

                }; */ ?>

                var zbsLogIndex = <?php

                    #} Array or empty
                    if (count($zbsLogs) > 0 && is_array($zbsLogs)) {
                      
                        $zbsLogsExpose = array();
                        foreach ($zbsLogs as $zbsLog){

                            $retLine = $zbsLog;
                            if (isset($retLine) && isset($retLine['longdesc'])) $retLine['longdesc'] = nl2br(zeroBSCRM_textExpose($retLine['longdesc']));

                            $zbsLogsExpose[] = $retLine;

                        }

                        echo json_encode($zbsLogsExpose);
                    } else
                        echo json_encode(array());

                ?>;

                var zbsLogEditing = -1;

                // def ico
                var zbsLogDefIco = 'fa-sticky-note-o'; 

                jQuery(function(){

                    // build log ui
                    zbscrmjs_buildLogs();

                    // add log button
                    jQuery('#zbscrmAddLog').on( 'click', function(){


                        if (jQuery(this).css('display') == 'block'){

                            jQuery('#zbsAddLogFormTR').slideDown('400', function() {
                                
                            });

                            jQuery(this).hide();

                        } else {

                            jQuery('#zbsAddLogFormTR').hide();

                            jQuery(this).show();

                        }


                    });

                    // cancel
                    jQuery('#zbscrmAddLogCancel').on( 'click', function(){

                            jQuery('#zbsAddLogFormTR').hide();

                            jQuery('#zbscrmAddLog').show();

                    });

                    // save
                    jQuery('#zbscrmAddLogSave').on( 'click', function(){

                            //jQuery('#zbsAddLogFormTR').hide();

                            //jQuery('#zbscrmAddLog').show();

                            /* 
                            zbsnagainstid
                            zbsntype
                            zbsnshortdesc            
                            zbsnlongdesc
                            zbsnoverwriteid
                            */

                            // get / check data
                            var data = {sec:window.zbscrmjs_logsSecToken}; var errs = 0;
                            if ((jQuery('#zbsAddLogType').val()).length > 0) data.zbsntype = jQuery('#zbsAddLogType').val();
                            if ((jQuery('#zbsAddLogMainDesc').val()).length > 0) data.zbsnshortdesc = jQuery('#zbsAddLogMainDesc').val();
                            if ((jQuery('#zbsAddLogDetailedDesc').val()).length > 0) 
                                data.zbsnlongdesc = jQuery('#zbsAddLogDetailedDesc').val();
                            else
                                data.zbsnlongdesc = '';

                            // post id & no need for overwrite id as is new
                            data.zbsnagainstid = parseInt(window.zbsLogAgainstID);

                            // debug console.log('posting new note: ',data);

                            // validate
                            var msgOut = '';
                            if (typeof data.zbsntype == "undefined" || data.zbsntype == '') {
                                errs++;
                                msgOut = 'Note Type is required!'; 
                                jQuery('#zbsAddLogType').css('border','2px solid orange');
                                setTimeout(function(){

                                    jQuery('#zbsAddLogUpdateMsg').html('');
                                    jQuery('#zbsAddLogType').css('border','1px solid #ddd');

                                },1500);
                            }
                            if (typeof data.zbsnshortdesc == "undefined" || data.zbsnshortdesc == '') {
                                errs++;
                                if (msgOut == 'Note Type is required!') 
                                    msgOut = 'Note Type and Description are required!'; 
                                else
                                    msgOut += 'Note Description is required!'; 
                                jQuery('#zbsAddLogMainDesc').css('border','2px solid orange');
                                setTimeout(function(){

                                    jQuery('#zbsAddLogUpdateMsg').html('');
                                    jQuery('#zbsAddLogMainDesc').css('border','1px solid #ddd');

                                },1500);
                            }

                            if (errs === 0){

                                // add action
                                data.action = 'zbsaddlog';

                                zbscrmjs_addNewNote(data,function(newLog){

                                    // success

                                        // msg
                                        jQuery('#zbsAddLogUpdateMsg').html('Saved!');

                                        // then hide form, build new log gui, clear form

                                            // hide + clear form
                                            jQuery('#zbsAddLogFormTR').hide();
                                            jQuery('#zbscrmAddLog').show();
                                            jQuery('#zbsAddLogType').val('Note');
                                            jQuery('#zbsAddLogMainDesc').val('');
                                            jQuery('#zbsAddLogDetailedDesc').val('');
                                            jQuery('#zbsAddLogUpdateMsg').html('');

                                        // add it (build example obj)
                                        var newLogObj = {
                                            id: newLog.logID,
                                            created: '', //moment(),
                                            meta: {

                                                type: newLog.zbsntype,
                                                shortdesc: newLog.zbsnshortdesc,
                                                longdesc: zbscrmjs_nl2br(newLog.zbsnlongdesc)

                                            }
                                        }
                                        zbscrmjs_addNewNoteLine(newLogObj,true);

                                        // also add to window obj
                                        window.zbsLogIndex.push(newLogObj);


                                        // bind ui
                                        setTimeout(function(){
                                            zbscrmjs_bindNoteUIJS();
                                            zbscrmjs_updateLogCount();
                                        },0);


                                },function(){

                                    // failure

                                        // msg + do nothing
                                        jQuery('#zbsAddLogUpdateMsg').html('There was an error when saving this note!');

                                });

                            } else {
                                if (typeof msgOut !== "undefined" && msgOut != '') jQuery('#zbsAddLogUpdateMsg').html(msgOut); 
                            }

                    });


                    // note ico - works for both edit + add
                    jQuery('#zbsAddLogType, #zbsEditLogType').on( 'change', function(){

                        // get perm
                        var logPerm = zbscrmjs_permify(jQuery(this).val()); // jQuery('#zbsAddLogType').val()

                        var thisIco = window.zbsLogDefIco;
                        // find ico
                        if (typeof window.zbsLogTypes[logPerm] != "undefined") thisIco = window.zbsLogTypes[logPerm].ico;

                        // override all existing classes with ones we want:
                        if (jQuery(this).hasClass('zbsUpdateTypeAdd')) jQuery('#zbsAddLogIco i').attr('class','fa ' + thisIco);
                        if (jQuery(this).hasClass('zbsUpdateTypeEdit')) jQuery('#zbsEditLogIco i').attr('class','fa ' + thisIco);

                    });


                });

                function zbscrmjs_updateLogCount(){

                    var count = 0; 
                    if (window.zbsLogIndex.length > 0) count = parseInt(window.zbsLogIndex.length);
                    jQuery('#zbsActiveLogCount').html(zbscrmjs_prettifyLongInts(count));

                }

                // build log ui
                function zbscrmjs_buildLogs(){

                    // get from obj
                    var theseLogs = window.zbsLogIndex;


                    jQuery.each(theseLogs,function(ind,ele){

                        zbscrmjs_addNewNoteLine(ele);

                    });

                    // bind ui
                    setTimeout(function(){
                        zbscrmjs_bindNoteUIJS();
                        zbscrmjs_updateLogCount();
                    },0);

                }

                function zbscrmjs_addNewNoteLine(ele,prepflag,replaceExisting){

                        // localise
                        var logMeta = ele; if (typeof ele.meta != "undefined") logMeta = ele.meta;

                        // get perm
                        var logPerm = zbscrmjs_permify(logMeta.type);

                        // build it
                        var thisLogHTML = '<div class="zbsLogOut" data-logid="' + ele.id + '" id="zbsLogOutLine' + ele.id + '" data-logtype="' + logPerm + '">';


                            // type ico
                                
                                var thisIco = window.zbsLogDefIco;
                                // find ico
                                if (typeof window.zbsLogTypes[logPerm] != "undefined") thisIco = window.zbsLogTypes[logPerm].ico;

                                // output
                                thisLogHTML += '<div class="zbsLogOutIco"><i class="fa ' + thisIco + '" aria-hidden="true"></i></div>';


                            // created date
                            if (typeof ele.created !== "undefined" && ele.created !== '' && typeof ele.createduts !== "undefined" && ele.createduts !== '') {
                                
                                // timezone offset?
                                // Here we get the UTS offset in minutes from zbs_root
                                var offsetMins = 0; if (typeof window.zbs_root.timezone_offset_mins != "undefined") offsetMins = parseInt(window.zbs_root.timezone_offset_mins);
                                // Here we create a moment instance in correct timezone(offset) using original created unix timestamp in UTC
                                var createdMoment = moment.unix(parseInt(ele.createduts)).utcOffset(offsetMins);                                

                                thisLogHTML += '<div class="zbsLogOutCreated" data-zbscreated="' + ele.created + '" title="' + createdMoment.format('lll') + '">' + createdMoment.format('lll') + '</div>';

                            } else {

                                // empty created means just created obj
                                var createdMoment = moment();
                                thisLogHTML += '<div class="zbsLogOutCreated" data-zbscreated="' + createdMoment + '" title="' + createdMoment.format('lll') + '">' + createdMoment.format('lll') + '</div>';

                            }

                            // title

                                var thisTitle = '';
                                // type
                                if (typeof logMeta.type !== "undefined") thisTitle += '<span>' + ucwords(logMeta.type) + '</span>';
                                // desc
                                if (typeof logMeta.shortdesc !== "undefined") {

                                    if (thisTitle != '') thisTitle += ': ';
                                    thisTitle += logMeta.shortdesc;

                                }

                                var logEditElements = '<div class="zbsLogOutEdits"><i class="fa fa-pencil-square-o zbsLogActionEdit" title="<?php _e('Edit Log',"zero-bs-crm");?>"></i><i class="fa fa-trash-o zbsLogActionRemove last" title="<?php _e('Delete Log',"zero-bs-crm");?>"></i></div>';
                                thisLogHTML += '<div class="zbsLogOutTitle">' + thisTitle + logEditElements + '</div>';

                            // desc
                           if (typeof logMeta.longdesc !== "undefined" && logMeta.longdesc !== '' && logMeta.longdesc !== null) thisLogHTML += '<div class="zbsLogOutDesc">' + logMeta.longdesc + '</div>';

                            thisLogHTML += '</div>';


                        if (typeof replaceExisting == "undefined"){

                            // normal

                            // add it
                            if (typeof prepflag !== "undefined")
                                jQuery('#zbsAddLogOutputWrap').prepend(thisLogHTML);
                            else
                                jQuery('#zbsAddLogOutputWrap').append(thisLogHTML);


                        } else {

                            // replace existing
                            jQuery('#zbsLogOutLine' + ele.id).replaceWith(thisLogHTML);

                        }

                }

                function zbscrmjs_bindNoteUIJS(){

                    // show hide edit controls
                    jQuery('.zbsLogOut').on( 'mouseenter', function(){

                        var logType = jQuery(this).attr('data-logtype');

                        // only if not editing another :) + Log Type is not one we don't have in our set
                        if (window.zbsLogEditing == -1 && typeof window.zbsLogTypes[logType] != "undefined"){

                            // check if locked log or not! 
                            if (typeof logType == "undefined") logType = '';

                            // if log type empty, or has a key in window.zbsLogsLocked, don't allow edits
                            if (logType == '' || window.zbsLogsLocked.hasOwnProperty(logType)){

                                // nope

                            } else {
                                    
                                // yep
                                jQuery('.zbsLogOutEdits',jQuery(this)).css('display','inline-block');

                            }

                        }

                    }).on( 'mouseleave', function(){

                        jQuery('.zbsLogOutEdits',jQuery(this)).not('.stayhovered').css('display','none');

                    });

                    // bind del
                    jQuery('.zbsLogOutEdits .zbsLogActionRemove').off('click').on( 'click', function(){

                        // append "deleting"
                        jQuery(this).closest('.zbsLogOutEdits').addClass('stayhovered').append('<span>Deleting...</span>');

                        var noteID = parseInt(jQuery(this).closest('.zbsLogOut').attr('data-logid'));

                        if (noteID > 0){

                            var thisEle = this;

                            zbscrmjs_deleteNote(noteID,function(){

                                // success

                                    // localise
                                    var nID = noteID;

                                    // append "deleted" and then vanish
                                    jQuery('span',jQuery(thisEle).closest('.zbsLogOutEdits')).html('Deleted!...');

                                    var that = thisEle;
                                    setTimeout(function(){

                                        // localise
                                        var thisNoteID = nID;

                                        // also del from window obj
                                        zbscrmjs_removeItemFromLogIndx(thisNoteID);

                                        // update count span
                                        zbscrmjs_updateLogCount();

                                        // slide up
                                        jQuery(that).closest('.zbsLogOut').slideUp(400,function(){

                                            // and remove itself?

                                        });
                                    },500);

                            },function(){

                                //TODO: proper error msg
                                console.error('There was an issue retrieving this note for editing/deleting'); 

                            });

                        } else console.error('There was an issue retrieving this note for editing/deleting'); //TODO: proper error msg

                    });

                    // bind edit
                    jQuery('.zbsLogOutEdits .zbsLogActionEdit').off('click').on( 'click', function(){

                        // one at a time please sir...
                        if (window.zbsLogEditing == -1){

                            // get edit id
                            var noteID = parseInt(jQuery(this).closest('.zbsLogOut').attr('data-logid'));

                            // get edit obj
                            var editObj = zbscrmjs_retrieveItemFromIndex(noteID);

                            // move edit box to before here
                            jQuery('#zbsEditLogForm').insertBefore('#zbsLogOutLine' + noteID);

                            setTimeout(function(){

                                var lObj = editObj;
                                if (typeof lObj.meta != "undefined") lObj = lObj.meta; // pre dal2

                                // update edit box texts etc.
                                jQuery('#zbsEditLogMainDesc').val(lObj.shortdesc);
                                jQuery('#zbsEditLogDetailedDesc').val(zbscrmjs_reversenl2br(lObj.longdesc));
                                jQuery('#zbsEditLogType option').each(function(){
                                    if (jQuery(this).text() == lObj.type) {
                                        jQuery(this).attr('selected', 'selected');
                                        return false;
                                    }
                                    return true;
                                });
                            
                                // type ico

                                    // get perm
                                    var logPerm = zbscrmjs_permify(lObj.type);
                                
                                    var thisIco = window.zbsLogDefIco;
                                    // find ico
                                    if (typeof window.zbsLogTypes[logPerm] != "undefined") thisIco = window.zbsLogTypes[logPerm].ico;

                                    // update
                                    jQuery('#zbsEditLogIco i').attr('class','fa ' + thisIco);


                            },10);

                            // set edit vars
                            window.zbsLogEditing = noteID;

                            // hide line / show edit
                            jQuery('#zbsLogOutLine' + noteID).slideUp();
                            jQuery('#zbsEditLogForm').slideDown();

                            // bind
                            zbscrmjs_bindEditNote();

                        }
                            

                    });

                }

                function zbscrmjs_bindEditNote(){


                        // cancel
                        jQuery('#zbscrmEditLogCancel').on( 'click', function(){

                                // get note id
                                var noteID = window.zbsLogEditing;

                                // hide edit from
                                jQuery('#zbsEditLogForm').hide();

                                // show back log
                                jQuery('#zbsLogOutLine' + noteID).show();

                                // unset noteID
                                window.zbsLogEditing = -1;

                        });

                        // save
                        jQuery('#zbscrmEditLogSave').on( 'click', function(){

                                if (window.zbsLogEditing > -1){

                                        // get note id
                                        var noteID = window.zbsLogEditing;

                                        //jQuery('#zbsEditLogFormTR').hide();

                                        //jQuery('#zbscrmEditLog').show();

                                        /* 
                                        zbsnagainstid
                                        zbsntype
                                        zbsnshortdesc            
                                        zbsnlongdesc
                                        zbsnoverwriteid
                                        */

                                        // get / check data
                                        var data = {sec:window.zbscrmjs_logsSecToken}; var errs = 0;

                                        // same as add code, but with note id:
                                        data.zbsnprevid = noteID;

                                        if ((jQuery('#zbsEditLogType').val()).length > 0) data.zbsntype = jQuery('#zbsEditLogType').val();
                                        if ((jQuery('#zbsEditLogMainDesc').val()).length > 0) data.zbsnshortdesc = jQuery('#zbsEditLogMainDesc').val();
                                        if ((jQuery('#zbsEditLogDetailedDesc').val()).length > 0) 
                                            data.zbsnlongdesc = jQuery('#zbsEditLogDetailedDesc').val();
                                        else
                                            data.zbsnlongdesc = '';

                                        // post id & no need for overwrite id as is new
                                        data.zbsnagainstid = parseInt(window.zbsLogAgainstID);

                                        // validate
                                        var msgOut = '';
                                        if (typeof data.zbsntype == "undefined" || data.zbsntype == '') {
                                            errs++;
                                            msgOut = 'Note Type is required!'; 
                                            jQuery('#zbsEditLogType').css('border','2px solid orange');
                                            setTimeout(function(){

                                                jQuery('#zbsEditLogUpdateMsg').html('');
                                                jQuery('#zbsEditLogType').css('border','1px solid #ddd');

                                            },1500);
                                        }
                                        if (typeof data.zbsnshortdesc == "undefined" || data.zbsnshortdesc == '') {
                                            errs++;
                                            if (msgOut == 'Note Type is required!') 
                                                msgOut = 'Note Type and Description are required!'; 
                                            else
                                                msgOut += 'Note Description is required!'; 
                                            jQuery('#zbsEditLogMainDesc').css('border','2px solid orange');
                                            setTimeout(function(){

                                                jQuery('#zbsEditLogUpdateMsg').html('');
                                                jQuery('#zbsEditLogMainDesc').css('border','1px solid #ddd');

                                            },1500);
                                        }


                                        if (errs === 0){

                                            // add action
                                            data.action = 'zbsupdatelog';

                                            zbscrmjs_updateNote(data,function(newLog){

                                                // success

                                                    // msg
                                                    jQuery('#zbsEditLogUpdateMsg').html('Changes Saved!');

                                                    // then hide form, build new log gui, clear form

                                                        // hide + clear form
                                                        jQuery('#zbsEditLogForm').hide();
                                                        jQuery('#zbsEditLogType').val('Note');
                                                        jQuery('#zbsEditLogMainDesc').val('');
                                                        jQuery('#zbsEditLogDetailedDesc').val('');
                                                        jQuery('#zbsEditLogUpdateMsg').html('');

                                                    // update it (build example obj)
                                                    var newLogObj = {
                                                        id: newLog.logID,
                                                        created: '', //  moment().subtract(window.zbsCRMTimeZoneOffset, 'h')
                                                        meta: {

                                                            type: newLog.zbsntype,
                                                            shortdesc: newLog.zbsnshortdesc,
                                                            // have to replace the nl2br for long desc:
                                                            longdesc: zbscrmjs_nl2br(newLog.zbsnlongdesc)

                                                        }
                                                    }
                                                    zbscrmjs_addNewNoteLine(newLogObj,true,true); // third param here is "replace existing"

                                                    // also add to window obj in prev place
                                                    //window.zbsLogIndex.push(newLogObj);
                                                    zbscrmjs_replaceItemInLogIndx(newLog.logID,newLogObj);

                                                    // unset noteID
                                                    window.zbsLogEditing = -1;

                                                    // bind ui
                                                    setTimeout(function(){
                                                        zbscrmjs_bindNoteUIJS();
                                                        zbscrmjs_updateLogCount();
                                                    },0);


                                            },function(){

                                                // failure

                                                    // msg + do nothing
                                                    jQuery('#zbsEditLogUpdateMsg').html('There was an error when saving this note!');

                                            });

                                        } else {
                                            if (typeof msgOut !== "undefined" && msgOut != '') jQuery('#zbsEditLogUpdateMsg').html(msgOut); 
                                        }


                                } // if note id

                        });


                }

                function zbscrmjs_removeItemFromLogIndx(noteID){

                    var logIndex = window.zbsLogIndex;
                    var newLogIndex = [];

                    jQuery.each(logIndex,function(ind,ele){

                        if (typeof ele.id != "undefined" && ele.id != noteID) newLogIndex.push(ele);

                    });

                    window.zbsLogIndex = newLogIndex;

                    // fini
                    return window.zbsLogIndex;

                }

                function zbscrmjs_replaceItemInLogIndx(noteIDToReplace,newObj){

                    var logIndex = window.zbsLogIndex;
                    var newLogIndex = [];

                    jQuery.each(logIndex,function(ind,ele){

                        if (typeof ele.id != "undefined")
                            if (ele.id != noteIDToReplace) 
                                newLogIndex.push(ele);
                            else
                                // is to replace
                                newLogIndex.push(newObj);

                    });

                    window.zbsLogIndex = newLogIndex;

                    // fini
                    return window.zbsLogIndex;

                }

                function zbscrmjs_retrieveItemFromIndex(noteID){

                    var logIndex = window.zbsLogIndex;
                    var logObj = -1;

                    jQuery.each(logIndex,function(ind,ele){

                        if (typeof ele.id != "undefined" && ele.id == noteID) logObj = ele;

                    });

                    return logObj;
                }

                

                // function assumes a legit dataArr :) (validate above)
                function zbscrmjs_addNewNote(dataArr,cb,errcb){
                    
                    // needs nonce. <!--#NONCENEEDED -->

                    if (!window.zbsLogProcessingBlocker){
                        
                        // blocker
                        window.zbsLogProcessingBlocker = true;

                        // msg
                        jQuery('#zbsAddLogUpdateMsg').html('<?php _e('Saving...',"zero-bs-crm");?>');

                         // Send 
                            jQuery.ajax({
                                  type: "POST",
                                  url: ajaxurl, // admin side is just ajaxurl not wptbpAJAX.ajaxurl,
                                  "data": dataArr,
                                  dataType: 'json',
                                  timeout: 20000,
                                  success: function(response) {

                                    // Debug  console.log("RESPONSE",response);

                                    // blocker
                                    window.zbsLogProcessingBlocker = false;

                                    // this also has true/false on update... 
                                    if (typeof response.processed != "undefined" && response.processed){

                                        // callback
                                        // make a merged item... 
                                        var retArr = dataArr; dataArr.logID = response.processed;
                                        if (typeof cb == "function") cb(retArr);

                                    } else {

                                        // .. was an error :)

                                        // callback
                                        if (typeof errcb == "function") errcb(response);

                                    }


                                  },
                                  error: function(response){ 

                                    // Debug  console.error("RESPONSE",response);

                                    // blocker
                                    window.zbsLogProcessingBlocker = false;

                                    // callback
                                    if (typeof errcb == "function") errcb(response);



                                  }

                            });


                    } else {
                        
                        // end of blocker
                        jQuery('#zbsAddLogUpdateMsg').html('... already processing!');
                        setTimeout(function(){

                            jQuery('#zbsAddLogUpdateMsg').html('');

                        },2000);

                    }

                }

                // function assumes a legit dataArr :) (validate above)
                // is almost a clone of _addNote (homogenise later)
                function zbscrmjs_updateNote(dataArr,cb,errcb){
                    
                    // needs nonce. <!--#NONCENEEDED -->

                    if (!window.zbsLogProcessingBlocker){
                        
                        // blocker
                        window.zbsLogProcessingBlocker = true;

                        // msg
                        jQuery('#zbsEditLogUpdateMsg').html('<?php _e('Saving...',"zero-bs-crm");?>');

                         // Send 
                            jQuery.ajax({
                                  type: "POST",
                                  url: ajaxurl, // admin side is just ajaxurl not wptbpAJAX.ajaxurl,
                                  "data": dataArr,
                                  dataType: 'json',
                                  timeout: 20000,
                                  success: function(response) {

                                    // Debug  console.log("RESPONSE",response);

                                    // blocker
                                    window.zbsLogProcessingBlocker = false;

                                    // this also has true/false on update... 
                                    if (typeof response.processed != "undefined" && response.processed){

                                        // callback
                                        // make a merged item... 
                                        var retArr = dataArr; dataArr.logID = response.processed;
                                        if (typeof cb == "function") cb(retArr);

                                    } else {

                                        // .. was an error :)

                                        // callback
                                        if (typeof errcb == "function") errcb(response);

                                    }


                                  },
                                  error: function(response){ 

                                    // Debug  console.error("RESPONSE",response);

                                    // blocker
                                    window.zbsLogProcessingBlocker = false;

                                    // callback
                                    if (typeof errcb == "function") errcb(response);



                                  }

                            });


                    } else {
                        
                        // end of blocker
                        jQuery('#zbsEditLogUpdateMsg').html('... already processing!');
                        setTimeout(function(){

                            jQuery('#zbsEditLogUpdateMsg').html('');

                        },2000);

                    }

                }


                // function assumes a legit noteID + perms :) (validate above)
                function zbscrmjs_deleteNote(noteID,cb,errcb){
                    
                    // needs nonce. <!--#NONCENEEDED -->

                    if (!window.zbsLogProcessingBlocker){
                        
                        // blocker
                        window.zbsLogProcessingBlocker = true;

                        // -package
                        var dataArr = {
                            action : 'zbsdellog',
                            zbsnid : noteID,
                            sec:window.zbscrmjs_logsSecToken
                        };

                         // Send 
                            jQuery.ajax({
                                  type: "POST",
                                  url: ajaxurl, // admin side is just ajaxurl not wptbpAJAX.ajaxurl,
                                  "data": dataArr,
                                  dataType: 'json',
                                  timeout: 20000,
                                  success: function(response) {

                                    // Debug  console.log("RESPONSE",response);

                                    // blocker
                                    window.zbsLogProcessingBlocker = false;

                                    // this also has true/false on update... 
                                    if (typeof response.processed != "undefined" && response.processed){

                                        // Debug console.log("SUCCESS");

                                        // callback
                                        if (typeof cb == "function") cb(response);

                                    } else {

                                        // .. was an error :)
                                        // Debug console.log("ERRZ");                                    

                                        // callback
                                        if (typeof errcb == "function") errcb(response);

                                    }


                                  },
                                  error: function(response){ 

                                    // Debug  console.error("RESPONSE",response);

                                    // blocker
                                    window.zbsLogProcessingBlocker = false;

                                    // callback
                                    if (typeof errcb == "function") errcb(response);



                                  }

                            });


                    } else {
                        
                        // end of blocker

                    }

                }







            </script>
             
            <input type="hidden" name="<?php echo $metabox['id']; ?>_fields[]" value="subtitle_text" />


            <?php


            } // / if has posttype

        }

        public function save_meta_box( $post_id, $post ) {
            if( empty( $_POST['meta_box_ids'] ) ){ return; }
            foreach( $_POST['meta_box_ids'] as $metabox_id ){
                if(!isset($_POST[ $metabox_id . '_nonce' ]) ||  ! wp_verify_nonce( $_POST[ $metabox_id . '_nonce' ], 'save_' . $metabox_id ) ){ continue; }
                #if( count( $_POST[ $metabox_id . '_fields' ] ) == 0 ){ continue; }
                if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){ continue; }

                if( $metabox_id == 'wpzbsc_logdetails'  && $post->post_type == $this->postType){

                    #} Save

                }
            }

            return $post;
        }
    }

/* ======================================================
  / Logs v1 Metabox
   ====================================================== */




    #} Mark as included :)
    define('ZBSCRM_INC_LOGSMB',true);
