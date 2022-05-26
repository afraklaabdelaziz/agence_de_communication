<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 * V3.0
 *
 * Copyright 2020 Automattic
 *
 * Date: 20/02/2019
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

   function zeroBSCRM_QuotesMetaboxSetup(){

        // main detail
        $zeroBS__Metabox_Quote = new zeroBS__Metabox_Quote( __FILE__ );

        // quote content box
        $zeroBS__Metabox_QuoteContent = new zeroBS__Metabox_QuoteContent( __FILE__ );

        // quote next step box (publish etc.)
        $zeroBS__Metabox_QuoteNextStep = new zeroBS__Metabox_QuoteNextStep( __FILE__ );

        // quote actions box
        $zeroBS__Metabox_QuoteActions = new zeroBS__Metabox_QuoteActions( __FILE__ );

        // quote tags box
        $zeroBS__Metabox_QuoteTags = new zeroBS__Metabox_QuoteTags( __FILE__ );

        // quote accepted details
        $zeroBS__Metabox_QuoteAcceptedDetails = new zeroBS__Metabox_QuoteAcceptedDetails( __FILE__ );

        // files
        $zeroBS__Metabox_QuoteFiles = new zeroBS__Metabox_QuoteFiles( __FILE__ );
   }

   add_action( 'admin_init','zeroBSCRM_QuotesMetaboxSetup');

    /*
        $zeroBS__MetaboxQuote = new zeroBS__MetaboxQuote( __FILE__ );
        $zeroBS__QuoteContentMetabox = new zeroBS__QuoteContentMetabox( __FILE__ );
        $zeroBS__QuoteActionsMetabox = new zeroBS__QuoteActionsMetabox( __FILE__ );
        $zeroBS__QuoteStatusMetabox = new zeroBS__QuoteStatusMetabox( __FILE__ );
    */

/* ======================================================
   / Init Func
   ====================================================== */


/* ======================================================
  Quote Metabox
   ====================================================== */

    class zeroBS__Metabox_Quote extends zeroBS__Metabox{ 
        
        // this is for catching 'new' quotes
        private $newRecordNeedsRedir = false;

        public function __construct( $plugin_file ) {

            // set these
            $this->objType = 'quote';
            $this->metaboxID = 'zerobs-quote-edit';
            $this->metaboxTitle = __('Step 1: Quote Details','zero-bs-crm'); // will be headless anyhow
            $this->headless = true;
            $this->metaboxScreen = 'zbs-add-edit-quote-edit';
            $this->metaboxArea = 'normal';
            $this->metaboxLocation = 'high';
            $this->saveOrder = 1;
            $this->capabilities = array(

                'can_hide'          => false, // can be hidden
                'areas'             => array('normal'), // areas can be dragged to - normal side = only areas currently
                'can_accept_tabs'   => true,  // can/can't accept tabs onto it
                'can_become_tab'    => false, // can be added as tab
                'can_minimise'      => true, // can be minimised
                'can_move'          => true // can be moved

            );

            // call this 
            $this->initMetabox();

        }

        public function html( $quote, $metabox ) {

                // localise ID
                $quoteID = -1; if (is_array($quote) && isset($quote['id'])) $quoteID = (int)$quote['id'];

                // if new + $zbsObjDataPrefill passed, use that instead of loaded trans.
                if ($quoteID == -1){
                    global $zbsObjDataPrefill;
                    $quote = $zbsObjDataPrefill;
                }

                global $zbs;

                // Debug echo 'Quote:<pre>'.print_r($quote,1).'</pre>';
    
                ?>                
                <?php #} AJAX NONCE ?><script type="text/javascript">var zbscrmjs_secToken = '<?php echo wp_create_nonce( "zbscrmjs-ajax-nonce" ); ?>';</script><?php # END OF NONCE ?>
                <?php

                #} retrieve
                // some legacy bits from CPT days:
                $quoteContactID = -1;  if (is_array($quote) && isset($quote['contact']) && is_array($quote['contact']) && count($quote['contact']) > 0) $quoteContactID = $quote['contact'][0]['id']; //get_post_meta($post->ID, 'zbs_customer_quote_customer', true);
                $templateUsed = -1; if (is_array($quote) && isset($quote['template'])) $templateUsed = $quote['template']; //get_post_meta($post->ID, 'zbs_quote_template_id', true);
                
                #} this is a temp weird one, just passing onto meta for now (long day):
                // ? Not used DAL3?
                //$zbsTemplated = get_post_meta($post->ID, 'templated', true);
                //if (!empty($zbsTemplated)) $quote['templated'] = true;
                // quick WH predictive hack, not sure if viable - to test DAL3
                $quote['templated'] = false; if ($templateUsed !== -1 && !empty($templateUsed)) $quote['templated'] = true;

                #} if customer id is empty, but prefill isn't, use prefill
                if ($quoteContactID == -1 && isset($_GET['zbsprefillcust'])) $quoteContactID = (int)$_GET['zbsprefillcust'];

                #} pass to other metaboxes (cache?)
                global $zbsCurrentEditQuote; $zbsCurrentEditQuote = $quote;

                #} Retrieve fields from global
                global $zbsCustomerQuoteFields; $fields = $zbsCustomerQuoteFields;
                // Debug echo 'Fields:<pre>'.print_r($fields,1).'</pre>';
                
                #} Using "Quote Builder" or not?
                $useQuoteBuilder = zeroBSCRM_getSetting('usequotebuilder');

                // Inputs out:

                    #} New quote?
                    if (!isset($quote['id'])) echo '<input type="hidden" name="zbscrm_newquote" value="1" />';

                    #} pass this if already templated:
                    if ($useQuoteBuilder == 1 && isset($quote['template'])) echo '<input type="hidden" name="zbscrm_templated" id="zbscrm_templated" value="1" />';

                    #} Nonce used for loading quote template, left in for now, could be centralised to normal sec nonce
                    echo '<input type="hidden" name="quo-ajax-nonce" id="quo-ajax-nonce" value="' . wp_create_nonce( 'quo-ajax-nonce' ) . '" />';

                    // we pass the hash along the chain here too :)
                    echo '<input type="hidden" name="zbscq_hash" id="zbscq_hash" value="' . (isset($quote['hash']) ? $quote['hash'] : '') . '" />';
                ?>
                <style>
                    @media all and (max-width:699px){
                        table.wh-metatab{
                            min-width:100% !important;
                        }
                    }  
                </style>
                <table class="form-table wh-metatab wptbp" id="wptbpMetaBoxMainItem">

                    <?php 

                    // DAL3 only show after saved, easier
                    if (!empty($quoteID) && $quoteID > 0){

                        // QUOTE ID is seperate / unchangable
                        ?><tr class="wh-large"><th><label><?php _e('Quote (ID)',"zero-bs-crm");?>:</label></th>
                        <td>
                            <div class="zbs-prominent"><?php 

                            if (empty($quoteID)) $quoteID = zeroBSCRM_getNextQuoteID();

                            echo $quoteID;

                            ?><input type="hidden" name="zbsquoteid" value="<?php echo $quoteID; ?>" /></div>
                        </td></tr><?php

                    }


                    #} ALSO customer assigning is seperate:
                    ?><tr class="wh-large"><th><label><?php _e('Contact',"zero-bs-crm");?>:</label></th>
                    <td><?php

                        #} 27/09/16 - switched select for typeahead

                            #} Any customer?
                            $prefillStr = ''; if (isset($quoteContactID) && !empty($quoteContactID)){

                                $prefillStr = $zbs->DAL->contacts->getContactNameWithFallback( $quoteContactID );
                                
                            }

                            #} Output select box
                            echo zeroBSCRM_CustomerTypeList('zbscrmjs_quoteCustomerSelect',$prefillStr,true,'zbscrmjs_quote_unsetCustomer');

                            #} Output input which will pass the value via post
                            ?><input type="hidden" name="zbscq_customer" id="zbscq_customer" value="<?php echo $quoteContactID; ?>" /><?php

                            #} Output function which will copy over value - maybe later move to js
                            ?><script type="text/javascript">

                                jQuery(function(){

                                    // bind 
                                    setTimeout(function(){
                                        zeroBSCRMJS_showContactLinkIf(jQuery("#zbscq_customer").val());
                                    },0);

                                });

                                function zbscrmjs_quoteCustomerSelect(cust){

                                    // pass id to hidden input
                                    jQuery('#zbscq_customer').val(cust.id);

                                    // enable/disable button if present (here is def present)
                                    jQuery('#zbsQuoteBuilderStep2').prop( 'disabled', false );
                                    jQuery('#zbsQuoteBuilderStep2info').hide();


                                    setTimeout(function(){

                                        var lID = cust.id;

                                        // when inv select drop down changed, show/hide quick nav
                                        zeroBSCRMJS_showContactLinkIf(lID);

                                    },0);

                                }

                                function zbscrmjs_quote_unsetCustomer(o){

                                    if (typeof o == "undefined" || o == ''){

                                        jQuery("#zbscq_customer").val('');
                                        //jQuery("#bill").val('');
                                        //jQuery("#cusbill").val('');

                                        setTimeout(function(){

                                            // when inv select drop down changed, show/hide quick nav
                                            zeroBSCRMJS_showContactLinkIf('');

                                        },0);
                                        
                                    }
                                }

                                // if an contact is selected (against a trans) can 'quick nav' to contact
                                function zeroBSCRMJS_showContactLinkIf(contactID){

                                    // remove old
                                    //jQuery('#zbs-customer-title .zbs-view-contact').remove();
                                    jQuery('#zbs-quote-learn-nav .zbs-quote-quicknav-contact').remove();

                                    if (typeof contactID != "undefined" && contactID !== null && contactID !== ''){

                                        contactID = parseInt(contactID);
                                        if (contactID > 0){

                                            // seems like a legit inv, add

                                            /* this was from trans meta, here just add to top
                                                var html = '<div class="ui right floated mini animated button zbs-view-contact">';
                                                        html += '<div class="visible content"><?php  zeroBSCRM_slashOut(__('View','zero-bs-crm')); ?></div>';
                                                            html += '<div class="hidden content">';
                                                                html += '<i class="user icon"></i>';
                                                            html += '</div>';
                                                        html += '</div>';

                                                jQuery('#zbs-customer-title').prepend(html); */

                                                // ALSO show in header bar, if so
                                                var navButton = '<a target="_blank" style="margin-left:6px;" class="zbs-quote-quicknav-contact ui icon button blue mini labeled" href="<?php echo zbsLink('edit',-1,'zerobs_customer',true); ?>' + contactID + '"><i class="user icon"></i> <?php  zeroBSCRM_slashOut(__('Contact','zero-bs-crm')); ?></a>';
                                                jQuery('#zbs-quote-learn-nav').append(navButton);

                                                // bind
                                                //zeroBSCRMJS_bindContactLinkIf();
                                        }
                                    }

                                }

                            </script>
                    </td>
                    </tr><?php


                    // wh centralised 20/7/18 - 2.91+ skipFields
                    zeroBSCRM_html_editFields($quote,$fields,'zbscq_');

                    #} if enabled, and new quote, or one which hasn't had the 'templated' meta key added.
                    if ($useQuoteBuilder == 1 && !isset($quote['template'])){

                        ?><tr class="wh-large" id="zbs-quote-builder-step-1">

                            <th colspan="2">

                                <div class="zbs-move-on-wrap">

                                    <!-- infoz -->
                                    <h3><?php _e('Publish this Quote',"zero-bs-crm");?></h3>
                                    <p><?php _e('Do you want to use the Quote Builder to publish this quote? (This lets you email it to a client directly, for approval)',"zero-bs-crm");?></p>

                                    <input type="hidden" name="zbs_quote_template_id_used" id="zbs_quote_template_id_used" value="<?php if (isset($templateUsed) && !empty($templateUsed)) echo $templateUsed; ?>" />
                                    <select class="form-control" name="zbs_quote_template_id" id="zbs_quote_template_id">
                                        <option value="" disabled="disabled"><?php _e('Select a template',"zero-bs-crm");?>:</option>
                                        <?php

                                            $templates = zeroBS_getQuoteTemplates(true,100,0);

                                            #} If this quote has already selected a template it'll be stored in the meta under 'templateid'
                                            #} But if it's not the first, we never need to show this anyway...

                                            if (count($templates) > 0) foreach ($templates as $template){

                                                $templateName = __('Template','zero-bs-crm').' '.$template['id']; 
                                                if (isset($template['title']) && !empty($template['title'])) $templateName = $template['title'].' ('.$template['id'].')';

                                                echo '<option value="'.$template['id'].'"';
                                                #if (isset())
                                                echo '>'.$templateName.'</option>';

                                            }

                                        ?>
                                        <option value=""><?php _e("Blank Template","zero-bs-crm");?></option>
                                    </select>
                                    <br />
                                    <p><?php _e('Create additional quote templates',"zero-bs-crm"); ?> <a href="<?php echo zbsLink($zbs->slugs['quote-templates']);?>"><?php _e('here',"zero-bs-crm");?></a></p>
                                    <button type="button" id="zbsQuoteBuilderStep2" class="button button-primary button-large xl"<?php if (!isset($quoteContactID) || empty($quoteContactID)){ echo ' disabled="disabled"'; } ?>><?php _e('Use Quote Builder',"zero-bs-crm");?></button>
                                    <?php if (!isset($quoteContactID) || empty($quoteContactID)){ ?>
                                    <p id="zbsQuoteBuilderStep2info">(<?php _e("You'll need to assign this Quote to a customer to use this","zero-bs-crm");?>);</p>
                                    <?php } ?>

                                </div>

                            </th>

                        </tr><?php

                    } ?>

            </table><?php 
              
        }

        public function save_data( $quoteID, $quote ) {

            if (!defined('ZBS_OBJ_SAVED')){

                define('ZBS_OBJ_SAVED',1);

                // DAL3.0+
                global $zbs;

                // check this
                if (empty($quoteID) || $quoteID < 1)  $quoteID = -1;

                    // defaults, pulled from DAL obj 25/2/19
                    /* $quote = array(
                        'title' => '',
                        'currency' => '',
                        'value' => '',
                        'date' => '',
                        'template' => '',
                        'content' => '',
                        'notes' => '',
                        'send_attachments' => -1, (removed 4.0.9)
                        'hash' => '',
                        'lastviewed' => '',
                        'viewed_count' => '',
                        'accepted' => '',
                        //'created' => '',
                        //'lastupdated' => '',
                    );
                    */
                    $extraMeta = array(); // can pass any additional meta here

                    // retrieve _POST into arr
                    //global $zbsCustomerQuoteFields; 
                    //$zbsCustomerQuoteMeta = zeroBSCRM_save_fields($zbsCustomerQuoteFields,'zbscq_'); 
                    $autoGenAutonumbers = true; // generate if not set :)
                    $removeEmpties = false; // req for autoGenAutonumbers
                    $quote = zeroBS_buildObjArr($_POST,array(),'zbscq_','',$removeEmpties,ZBS_TYPE_QUOTE,$autoGenAutonumbers);
                                
                    // Use the tag-class function to retrieve any tags so we can add inline.
                    // Save tags against objid
                    $quote['tags'] = zeroBSCRM_tags_retrieveFromPostBag(true,ZBS_TYPE_QUOTE);  

                    /*// debug
                    echo 'POST:<pre>'.print_r($_POST,1).'</pre>';
                    echo 'Quote:<pre>'.print_r($quote,1).'</pre>';
                    exit();*/

                    // we always get this, because it's used below, but not part of buildObjArr (currently at 3.0)
                    if ($quoteID > 0) $quote['template'] = (int)$zbs->DAL->quotes->getQuoteTemplateID($quoteID);

                    // content (from other metabox actually)
                    if (isset($_POST['zbs_quote_content'])) {

                        #} Save content
                        //$data=htmlspecialchars($_POST['zbs_quote_content']);
                        $quote['content'] = zeroBSCRM_io_WPEditor_WPEditorToDB($_POST['zbs_quote_content']);                        

                        #} update templated vars
                        if (isset($_POST['zbs_quote_template_id'])) $quote['template'] = (int)sanitize_text_field($_POST['zbs_quote_template_id']);

                    }

                    #} First up, save quote id! #TRANSITIONTOMETANO
                    // DAL 3 will probs move away from this, for now leaving for refactoring round 2
                    // for now store as meta (though perhaps needs a new field zbsid)
                    $quoteOffset = zeroBSCRM_getQuoteOffset();
                    $quoteZBSID = (int)$quoteID+$quoteOffset; if (isset($_POST['zbsquoteid']) && !empty($_POST['zbsquoteid'])) $quoteZBSID = (int)$_POST['zbsquoteid'];
                    //update_post_meta($post_id,"zbsid",$quoteID);
                    $extraMeta['zbsid'] = $quoteZBSID;
                    #} and increment this 
                    if (!empty($quoteZBSID)) zeroBSCRM_setMaxQuoteID($quoteZBSID);

                    // assignments                        
                    $zbsQuoteContact = -1; if (isset($_POST['zbscq_customer'])) $zbsQuoteContact = (int)sanitize_text_field($_POST['zbscq_customer']);
                    $quote['contacts'] = ($zbsQuoteContact > 0) ? array($zbsQuoteContact) : array();
                    $zbsQuoteCompany = -1; if (isset($_POST['zbscq_company'])) $zbsQuoteCompany = (int)sanitize_text_field($_POST['zbscq_company']);
                    $quote['companies'] = ($zbsQuoteCompany > 0) ? array($zbsQuoteCompany) : array();

                    /* line item (temp here from Inv metabox, not yet implemented in ui)
                    //new way..  now not limited to 30 lines as now they are stored in [] type array in JS draw
                    $zbsInvoiceLines = array();
                    foreach($_POST['zbsli_itemname'] as $k => $v){

                        $ks = sanitize_text_field( $k ); // at least this
                        
                        $zbsInvoiceLines[$ks]['title']      = sanitize_text_field($_POST['zbsli_itemname'][$k]);
                        $zbsInvoiceLines[$ks]['desc']           = sanitize_text_field($_POST['zbsli_itemdes'][$k]);
                        $zbsInvoiceLines[$ks]['quantity']          = sanitize_text_field($_POST['zbsli_quan'][$k]);
                        $zbsInvoiceLines[$ks]['price']         = sanitize_text_field($_POST['zbsli_price'][$k]);
                        $zbsInvoiceLines[$ks]['tax']           = sanitize_text_field($_POST['zbsli_tax'][$k]);

                    }
                    if (count($zbsInvoiceLines) > 0) $invoice['lineitems'] = $zbsInvoiceLines;
                    */

                    // Status Overwrites (manual changes, only after initial save)
                    if ($quoteID > 0 && isset($_POST['quote_status'])){

                        switch ($_POST['quote_status']){

                            case 'draft':
                                // if changing to draft, remove any accepted date + template ID
                                $quote['accepted'] = 0;
                                $quote['template'] = -1;

                                break;
                            case 'published':

                                // if changing to published, just needs accepted unsetting, and if no template, populate
                                $quote['accepted'] = 0;
                                
                                // got template?

                                    // if not already set, set, otherwise leave existing set time in.
                                    if (!isset($quote['template']) || $quote['template'] <= 0) {

                                        // hacky setting of it to unlikely cieling 
                                        $quote['template'] = 99999;

                                    }

                                break;
                            case 'accepted':

                                // if not already accepted, mark accepted.

                                    // existing
                                    $accepted = (int)$zbs->DAL->quotes->getQuoteAcceptedTime($quoteID);

                                    // if not already set, set, otherwise leave existing set time in.
                                    if ($accepted <= 0) {

                                        // set it (first time, manual)
                                        $quote['accepted'] = time();
                                        $quote['acceptedsigned'] = 'manual';
                                        $quote['acceptedip'] = '';

                                    }

                                break;

                        }
                    }


                // add/update
                $addUpdateReturn = $zbs->DAL->quotes->addUpdateQuote(array(

                            'id'    => $quoteID,
                            'data'  => $quote,
                            'extraMeta' => $extraMeta,
                            'limitedFields' => -1

                    ));

                //echo 'adding:'.$quoteID.':<pre>'.print_r($quote,1).'</pre>'; exit();

                // Note: For NEW objs, we make sure a global is set here, that other update funcs can catch 
                // ... so it's essential this one runs first!
                // this is managed in the metabox Class :)
                if ($quoteID == -1 && !empty($addUpdateReturn) && $addUpdateReturn != -1) {
                    
                    $quoteID = $addUpdateReturn;
                    global $zbsJustInsertedMetaboxID; $zbsJustInsertedMetaboxID = $quoteID;

                    // set this so it redirs
                    $this->newRecordNeedsRedir = true;
                }

                // success?
                if ($addUpdateReturn != -1 && $addUpdateReturn > 0){

                    // Update Msg
                    // this adds an update message which'll go out ahead of any content
                    // This adds to metabox: $this->updateMessages['update'] = zeroBSCRM_UI2_messageHTML('info olive mini zbs-not-urgent',__('Contact Updated',"zero-bs-crm"),'','address book outline','contactUpdated');
                    // This adds to edit page
                    $this->updateMessage();

                    // catch any non-critical messages
                    $nonCriticalMessages = $zbs->DAL->getErrors(ZBS_TYPE_QUOTE);
                    if (is_array($nonCriticalMessages) && count($nonCriticalMessages) > 0) $this->dalNoticeMessage($nonCriticalMessages);


                } else {

                    // fail somehow
                    $failMessages = $zbs->DAL->getErrors(ZBS_TYPE_QUOTE);

                    // show msg (retrieved from DAL err stack)
                    if (is_array($failMessages) && count($failMessages) > 0)
                        $this->dalErrorMessage($failMessages);
                    else
                        $this->dalErrorMessage(array(__('Insert/Update Failed with general error','zero-bs-crm')));

                    // pass the pre-fill:
                    global $zbsObjDataPrefill; $zbsObjDataPrefill = $quote;

        
                }

            }

            return $quote;
        }

        // This catches 'new' contacts + redirs to right url
        public function post_save_data($objID,$obj){

            if ($this->newRecordNeedsRedir){

                global $zbsJustInsertedMetaboxID;
                if (!empty($zbsJustInsertedMetaboxID) && $zbsJustInsertedMetaboxID > 0){

                    // redir
                    wp_redirect( zbsLink('edit',$zbsJustInsertedMetaboxID,$this->objType) );
                    exit;

                }

            }

        }

        public function updateMessage(){

            global $zbs;

            // zbs-not-urgent means it'll auto hide after 1.5s
            // genericified from DAL3.0
            $msg = zeroBSCRM_UI2_messageHTML('info olive mini zbs-not-urgent',$zbs->DAL->typeStr($zbs->DAL->objTypeKey($this->objType)).' '.__('Updated',"zero-bs-crm"),'','address book outline','contactUpdated');

            $zbs->pageMessages[] = $msg;

        }

    }


/* ======================================================
  / Quote Metabox
   ====================================================== */


/* ======================================================
  Quote Content Metabox
   ====================================================== */

    class zeroBS__Metabox_QuoteContent extends zeroBS__Metabox{ 

        public function __construct( $plugin_file ) {

            // set these
            $this->objType = 'quote';
            $this->metaboxID = 'zerobs-quote-content-edit';
            $this->metaboxTitle = __('Step 2: Quote Content','zero-bs-crm'); // will be headless anyhow
            $this->headless = true;
            $this->metaboxScreen = 'zbs-add-edit-quote-edit';
            $this->metaboxArea = 'normal';
            $this->metaboxLocation = 'low';
            $this->saveOrder = 1;
            $this->capabilities = array(

                'can_hide'          => false, // can be hidden
                'areas'             => array('normal'), // areas can be dragged to - normal side = only areas currently
                'can_accept_tabs'   => true,  // can/can't accept tabs onto it
                'can_become_tab'    => false, // can be added as tab
                'can_minimise'      => true, // can be minimised
                'can_move'          => true // can be moved

            );

            // call this 
            $this->initMetabox();

        }

        public function html( $quote, $metabox ) {

            // localise ID & content
            $quoteID = -1; if (is_array($quote) && isset($quote['id'])) $quoteID = (int)$quote['id'];
            $quoteContent = ''; if (is_array($quote) && isset($quote['content'])) $quoteContent = $quote['content'];
            
            #http://stackoverflow.com/questions/3493313/how-to-add-wysiwyg-editor-in-wordpress-meta-box
            $content = zeroBSCRM_io_WPEditor_DBToWPEditor($quoteContent);

            // remove "Add contact form" button from Jetpack
            remove_action( 'media_buttons', 'grunion_media_button', 999 );
            wp_editor( $content, 'zbs_quote_content', array(
                'editor_height' => 580,
                'wpautop' => false,
            ));
        }

        // saved via main metabox

    }


/* ======================================================
  / Quote Content Metabox
   ====================================================== */



/* ======================================================
  Quote Next Step Metabox
   ====================================================== */

    class zeroBS__Metabox_QuoteNextStep extends zeroBS__Metabox{ 

        public function __construct( $plugin_file ) {

            // set these
            $this->objType = 'quote';
            $this->metaboxID = 'zerobs-quote-nextstep';
            $this->metaboxTitle = __('Step 3: Publish and Send','zero-bs-crm'); // will be headless anyhow
            $this->headless = true;
            $this->metaboxScreen = 'zbs-add-edit-quote-edit';
            $this->metaboxArea = 'normal';
            $this->metaboxLocation = 'low';
            $this->saveOrder = 1;
            $this->capabilities = array(

                'can_hide'          => false, // can be hidden
                'areas'             => array('normal'), // areas can be dragged to - normal side = only areas currently
                'can_accept_tabs'   => true,  // can/can't accept tabs onto it
                'can_become_tab'    => false, // can be added as tab
                'can_minimise'      => true, // can be minimised
                'can_move'          => true // can be moved

            );

            // call this 
            $this->initMetabox();

        }

        public function html( $quote, $metabox ) {

            global $zbs;

            // localise ID & content
            $quoteID = -1; if (is_array($quote) && isset($quote['id'])) $quoteID = (int)$quote['id'];

            #} retrieve
            // some legacy bits from CPT days:
            $quoteContactID = -1;  if (is_array($quote) && isset($quote['contact']) && is_array($quote['contact']) && count($quote['contact']) > 0) $quoteContactID = $quote['contact'][0]['id']; //get_post_meta($post->ID, 'zbs_customer_quote_customer', true);
            $templateUsed = -1; if (is_array($quote) && isset($quote['template'])) $templateUsed = $quote['template']; //get_post_meta($post->ID, 'zbs_quote_template_id', true);
                
            #} Using "Quote Builder" or not?
            $useQuoteBuilder = zeroBSCRM_getSetting('usequotebuilder');
            $useHash = zeroBSCRM_getSetting('easyaccesslinks');

            #} if enabled, and new quote, or one which hasn't had the 'templated' meta key added.
            if ($useQuoteBuilder == "1") { 

                // retrieve email $contactEmail = ''; 
                $contactEmail = $zbs->DAL->contacts->getContactEmail($quoteContactID);//zeroBS_contactEmail($quoteContactID);

                // quick WH predictive hack, not sure if viable - to test DAL3
                $quote['templated'] = false; if ($templateUsed !== -1 && !empty($templateUsed)) $quote['templated'] = true;

                #} first load?
                if (gettype($quote) != "array" || !isset($quote['templated'])){

                        ?>
                            <div class="zbs-move-on-wrap" style="padding-top:30px;">

                                <!-- infoz -->
                                <h3><?php _e("Publish this Quote","zero-bs-crm");?></h3>
                                <p><?php _e("When you've finished writing your Quote, save it here before sending on to your customer","zero-bs-crm");?>:</p>

                                <button type="button" id="zbsQuoteBuilderStep3" class="button button-primary button-large xl"><?php _e("Save Quote","zero-bs-crm");?></button>

                            </div>

                        <?php

                } else {

                    # already has a saved quote
                    #} If Portal is uninstalled it will break Quotes. So show a message warning them that this should be on
                    if (!zeroBSCRM_isExtensionInstalled('portal')){
                        ?>
                            <div class="ui message red" style="font-size:18px;">
                                <b><i class="ui icon warning"></i><?php _e("Client Portal Deactivated","zero-bs-crm");?></b>
                                <?php _e('<p>You have uninstalled the Client Portal. The only way you will be able to send your Quote to your contact is by downloading a PDF (needs PDF invoicing installed) and then emailing it to them manually.</p>','zero-bs-crm'); ?>
                                <a class="ui button blue" href="<?php echo admin_url('admin.php?page=zerobscrm-extensions');?>"><?php _e("Enable Client Portal","zero-bs-crm"); ?></a>
                            </div>
                        <?php
                    }else{

                        // v3.0+ we use hash urls, so check exists
                        $dal3HashCheck = true; 
                        if ($zbs->isDAL3() && (!isset($quote['hash']) || empty($quote['hash']))) $dal3HashCheck = false;

                        if (isset($contactEmail) && !empty($contactEmail) && zeroBSCRM_validateEmail($contactEmail) && (!$useHash || ($useHash && $dal3HashCheck))){

                            // has email, and portal, all good

                            ?>

                                <div class="zbs-move-on-wrap" style="padding-top:30px;">
                                    <?php 
                                        #} Add nonce
                                        echo '<script type="text/javascript">var zbscrmjs_secToken = \''.wp_create_nonce( "zbscrmjs-ajax-nonce" ).'\';</script>';
                                    ?>

                                    <!-- infoz -->
                                    <h3><?php _e("Email or Share","zero-bs-crm");?></h3>
                                    <p><?php _e("Great! Your Quote has been published. You can now email it to your customer, or share the link directly","zero-bs-crm");?>:</p>

                                    <?php do_action('zbs_quote_actions'); ?>

                                    <div class="zbsEmailOrShare">
                                        <h4><?php _e("Email to Customer","zero-bs-crm");?>:</h4>
                                        <!-- todo -->                                    
                                        <p><input type="text" class="form-control" id="zbsQuoteBuilderEmailTo" value="<?php echo $contactEmail; ?>" placeholder="<?php _e('e.g. customer@yahoo.com','zero-bs-crm'); ?>" data-quoteid="<?php echo $quoteID; ?>" /></p>
                                        <p><button type="button" id="zbsQuoteBuilderSendNotification" class="button button-primary button-large"><?php _e("Send Quote","zero-bs-crm");?></button></p>
                                        <p class="small" id="zbsQuoteBuilderEmailToErr" style="display:none"><?php _e("An Email Address to send to is required","zero-bs-crm");?>!</p>
                                    </div>
                                    <div class="zbsEmailOrShare">
                                    <?php
                                        // temp changes from https://bitbucket.org/mikeandwoody/zerobs-core/commits/5f60426740a36c17fdd4f0d2f0f42e6e79394f0b
                                        //$prevURL = home_url('/clients/quotes/'.$post->ID); // was echo get_the_permalink($post->ID);
                                        
                                        #} If Portal is OFF this will totally goose the whole Quote functionality.
                                        
                                            // pre v3.0
                                            $quoteIDorHash = $quoteID;
                                            if ($zbs->isDAL3() && $useHash) $quoteIDorHash = $quote['hash'];

                                            $prevURL = zeroBS_portal_link('quotes',$quoteIDorHash); // as of 2.89
                                            ?>
                                            <h4><?php _e("Share the Link or","zero-bs-crm"); ?> <a href="<?php echo esc_url($prevURL);  ?>" target="_blank"><?php _e("preview","zero-bs-crm");?></a>:</h4>
                                            <p><input type="text" class="form-control" id="zbsQuoteBuilderURL" value="<?php echo esc_url($prevURL);  ?>" /></p>

                                   
                                        <!--<p class="small">Note: Anyone who has this link can view this proposal.</p>-->
                                    </div>  

                                    <?php
                                    #} WH second change, only showed if dompdf extension installed
                                            if (zeroBSCRM_isExtensionInstalled('pdfinv')){
                                                
                                                #} PDF Invoicing is installed
                                                ?>
                                                <div class="zbsEmailOrShare">
                                                <h4><?php _e("Download PDF","zero-bs-crm");?></h4>
                                                <p><i class="file pdf outline icon red" style="font-size:30px;margin-top:10px;"></i></p>
                                                <input type="button" name="jpcrm_quote_download_pdf" id="jpcrm_quote_download_pdf" class="ui button green" value="<?php _e("Download PDF","zero-bs-crm");?>" />
                                               
                                                </div>
                                                <script type="text/javascript">
                                                jQuery(function(){

                                                    // add your form to the end of body (outside <form>)
                                                    var formHTML = '<form target="_blank" method="post" id="jpcrm_quote_download_pdf_form" action="">';
                                                        formHTML += '<input type="hidden" name="jpcrm_quote_download_pdf" value="1" />';
                                                        formHTML += '<input type="hidden" name="jpcrm_quote_id" value="<?php echo $quoteID; ?>" />';
                                                        formHTML += '<input type="hidden" name="jpcrm_quote_pdf_gen_nonce" value="<?php echo esc_attr( wp_create_nonce( 'jpcrm-quote-pdf-gen' ) ); ?>" />';
                                                        formHTML += '</form>';
                                                    jQuery('#wpbody').append(formHTML);

                                                    // on click
                                                    jQuery('#jpcrm_quote_download_pdf').on( 'click', function(){

                                                        // submit form
                                                        jQuery('#jpcrm_quote_download_pdf_form').submit();

                                                    });

                                                });                    
                                                </script>
                                                <?php 

                                            }
                                ?>                            

                                </div>

                            <?php


                        } else {

                            if (isset($quoteContactID) && $quoteContactID > 0){

                                // Contact, but they don't have an email addr on file: ?>

                                <div class="zbs-move-on-wrap" style="padding-top:30px;">

                                    <h3><?php _e("Email or Share","zero-bs-crm");?></h3>
                                    <div class="zbsEmailOrShare">
                                        <h4><?php _e("Add Contact's Email","zero-bs-crm");?>:</h4>
                                        <p><?php _e('To proceed, edit the contact and add their email address, that way we can then send them this quote online.','zero-bs-crm'); ?></p>
                                        <p><a href="<?php echo zbsLink('edit',$quoteContactID,'zerobs_customer',true); ?>" class="button button-primary button-large"><?php _e("Edit Contact","zero-bs-crm");?></a></p>
                                    </div>              

                                </div>

                            <?php } else {

                                // not yet assigned to anyone. ?>

                                <div class="zbs-move-on-wrap" style="padding-top:30px;">

                                    <h3><?php _e("Email or Share","zero-bs-crm");?></h3>
                                    <div class="zbsEmailOrShare">
                                        <h4><?php _e("Assign to Contact","zero-bs-crm");?>:</h4>
                                        <p><?php _e('To proceed, assign this quote to a contact and save it.','zero-bs-crm'); ?></p>
                                    </div>              

                                </div>

                            <?php 

                            }
                        }
                    }

                }

            } # if quotebuilder

        }

        // nothing to save.
    }


/* ======================================================
  / Quote Actions Metabox
   ====================================================== */







/* ======================================================
  Quote files Metabox
   ====================================================== */

    class zeroBS__Metabox_QuoteFiles extends zeroBS__Metabox{

        public function __construct( $plugin_file ) {

            // DAL3 switched for objType $this->postType = 'zerobs_customer';
            $this->objType = 'quote';
            $this->metaboxID = 'zerobs-quote-files';
            $this->metaboxTitle = __('Associated Files',"zero-bs-crm");
            $this->metaboxScreen = 'zbs-add-edit-quote-edit'; //'zerobs_edit_contact'; // we can use anything here as is now using our func
            $this->metaboxArea = 'normal';
            $this->metaboxLocation = 'low';
            $this->capabilities = array(

                'can_hide'          => true, // can be hidden
                'areas'             => array('normal'), // areas can be dragged to - normal side = only areas currently
                'can_accept_tabs'   => true,  // can/can't accept tabs onto it
                'can_become_tab'    => true, // can be added as tab
                'can_minimise'      => true // can be minimised

            );

            // call this 
            $this->initMetabox();

        }

        public function html( $quote, $metabox ) {

                global $zbs;

                $html = '';

                // localise ID
                $quoteID = -1; if (is_array($quote) && isset($quote['id'])) $quoteID = (int)$quote['id'];

                #} retrieve
                $zbsFiles = array(); if ($quoteID > 0) $zbsFiles = zeroBSCRM_files_getFiles('quote',$quoteID);

                ?><table class="form-table wh-metatab wptbp" id="wptbpMetaBoxMainItemFiles">

                    <?php 

                        // WH only slightly updated this for DAL3 - could do with a cleanup run (contact file edit has more functionality)

                        #} Any existing
                        if (is_array($zbsFiles) && count($zbsFiles) > 0){ 
                          ?><tr class="wh-large"><th><label><?php printf( _n( '%s associated file', '%s associated files', count($zbsFiles), 'text-domain' ), number_format_i18n( count($zbsFiles) ) ); ?></label></th>
                                    <td id="zbsFileWrapInvoices">
                                        <?php $fileLineIndx = 1; foreach($zbsFiles as $zbsFile){
                                            
                                            $file = zeroBSCRM_files_baseName($zbsFile['file'],isset($zbsFile['priv']));

                                            echo '<div class="zbsFileLine" id="zbsFileLineQuote'.$fileLineIndx.'"><a href="'.$zbsFile['url'].'" target="_blank">'.$file.'</a> (<span class="zbsDelFile" data-delurl="'.$zbsFile['url'].'"><i class="fa fa-trash"></i></span>)</div>';
                                            $fileLineIndx++;

                                        } ?>
                                    </td></tr><?php

                        } 
                    ?>

                    <?php #adapted from http://code.tutsplus.com/articles/attaching-files-to-your-posts-using-wordpress-custom-meta-boxes-part-1--wp-22291
                             
                            $html .= '<input type="file" id="zbsobj_file_attachment" name="zbsobj_file_attachment" size="25" class="zbs-dc">';
                            
                            ?><tr class="wh-large"><th><label><?php _e('Add File',"zero-bs-crm");?>:</label><br />(<?php _e('Optional',"zero-bs-crm");?>)<br /><?php _e('Accepted File Types',"zero-bs-crm");?>:<br /><?php echo zeroBS_acceptableFileTypeListStr(); ?></th>
                                <td><?php
                            wp_nonce_field(plugin_basename(__FILE__), 'zbsobj_file_attachment_nonce');
                            echo $html;
                    ?></td></tr>
        
            </table>
            <script type="text/javascript">

                var zbsQuotesCurrentlyDeleting = false;
                var zbsMetaboxFilesLang = {
                    'err': '<?php echo zeroBSCRM_slashOut(__('Error',"zero-bs-crm")); ?>',
                    'unabletodel' : '<?php echo zeroBSCRM_slashOut(__('Unable to delete this file',"zero-bs-crm")); ?>',

                }

                jQuery(function(){

                    jQuery('.zbsDelFile').on( 'click', function(){

                        if (!window.zbsQuotesCurrentlyDeleting){

                            // blocking
                            window.zbsQuotesCurrentlyDeleting = true;

                            var delUrl = jQuery(this).attr('data-delurl');
                            var lineIDtoRemove = jQuery(this).closest('.zbsFileLine').attr('id');

                            if (typeof delUrl != "undefined" && delUrl != ''){



                                  // postbag!
                                  var data = {
                                    'action': 'delFile',
                                    'zbsfType': 'quotes',
                                    'zbsDel':  delUrl, // could be csv, never used though
                                    'zbsCID': <?php echo $quoteID; ?>,
                                    'sec': window.zbscrmjs_secToken
                                  };

                                  // Send it Pat :D
                                  jQuery.ajax({
                                          type: "POST",
                                          url: ajaxurl, // admin side is just ajaxurl not wptbpAJAX.ajaxurl,
                                          "data": data,
                                          dataType: 'json',
                                          timeout: 20000,
                                          success: function(response) {

                                            // visually remove
                                            jQuery('#' + lineIDtoRemove).remove();

                                            // file deletion errors, show msg:
                                            if (typeof response.errors != "undefined" && response.errors.length > 0){

                                                jQuery.each(response.errors,function(ind,ele){

                                                    jQuery('#zerobs-quotes-files-box').append('<div class="ui warning message" style="margin-top:10px;">' + ele + '</div>');

                                                });
                                                     
                                            }


                                          },
                                          error: function(response){

                                            jQuery('#zerobs-quotes-files-box').append('<div class="ui warning message" style="margin-top:10px;"><strong>' + window.zbsMetaboxFilesLang.err + ':</strong> ' + window.zbsMetaboxFilesLang.unabletodel + '</div>');

                                          }

                                        });

                            }

                            window.zbsQuotesCurrentlyDeleting = false;

                        } // / blocking

                    });

                });


            </script><?php

        }

        public function save_data( $quoteID, $quote ) {

            global $zbsobj_justUploadedObjFile;
            $id = $quoteID;

            if(!empty($_FILES['zbsobj_file_attachment']['name']) && 
                (!isset($zbsobj_justUploadedObjFile) ||
                    (isset($zbsobj_justUploadedObjFile) && $zbsobj_justUploadedObjFile != $_FILES['zbsobj_file_attachment']['name'])
                )
                ) {


            /* --- security verification --- */
            if(!wp_verify_nonce($_POST['zbsobj_file_attachment_nonce'], plugin_basename(__FILE__))) {
              return $id;
            } // end if


            if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
              return $id;
            } // end if
               
            /* Switched out for WH Perms model 19/02/16 
            if('page' == $_POST['post_type']) { 
              if(!current_user_can('edit_page', $id)) {
                return $id;
              } // end if
            } else { 
                if(!current_user_can('edit_page', $id)) { 
                    return $id;
                } // end if
            } // end if */
            if (!zeroBSCRM_permsQuotes()){
                return $id;
            }
            /* - end security verification - */

            #} Blocking repeat-upload bug
            $zbsobj_justUploadedObjFile = $_FILES['zbsobj_file_attachment']['name'];

                // proceed
                $supported_types = zeroBS_acceptableFileTypeMIMEArr(); //$supported_types = array('application/pdf');
                $arr_file_type = wp_check_filetype(basename($_FILES['zbsobj_file_attachment']['name']));
                $uploaded_type = $arr_file_type['type'];

                if(in_array($uploaded_type, $supported_types) || (isset($supported_types['all']) && $supported_types['all'] == 1)) {
                    $upload = wp_upload_bits($_FILES['zbsobj_file_attachment']['name'], null, file_get_contents($_FILES['zbsobj_file_attachment']['tmp_name']));

                    if(isset($upload['error']) && $upload['error'] != 0) {
                        wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
                    } else {
                        //update_post_meta($id, 'zbsobj_file_attachment', $upload);

                            // v2.13 - also privatise the file (move to our asset store)
                            // $upload will have 'file' and 'url'
                            $fileName = basename($upload['file']);
                            $fileDir = dirname($upload['file']);
                            $privateThatFile = zeroBSCRM_privatiseUploadedFile($fileDir,$fileName);
                            if (is_array($privateThatFile) && isset($privateThatFile['file'])){ 

                                // successfully moved to our store

                                    // modify URL + file attributes
                                    $upload['file'] = $privateThatFile['file'];
                                    $upload['url'] = $privateThatFile['url'];

                                    // add this extra identifier if in privatised sys
                                    $upload['priv'] = true;

                            } else {

                                // couldn't move to store, leave in uploaded for now :)

                            }

                            // w mod - adds to array :)
                            $zbsFiles = zeroBSCRM_files_getFiles('quote',$quoteID);
               
                            if (is_array($zbsFiles)){

                                //add it
                                $zbsFiles[] = $upload;

                            } else {

                                // first
                                $zbsFiles = array($upload);

                            }
 
                            // update
                            zeroBSCRM_files_updateFiles('quote',$quoteID, $zbsFiles);

                            // Fire any 'post-upload-processing' (e.g. CPP makes thumbnails of pdf, jpg, etc.)
                            // not req invoicing: do_action('zbs_post_upload_contact',$upload);
                    }
                }
                else {
                    wp_die("The file type that you've uploaded is not an accepted file format.");
                }
            }

            return $quote;
        }
    }


/* ======================================================
  / Attach files to quote metabox
   ====================================================== */


/* ======================================================
  Quote Accepted Details Metabox
   ====================================================== */

    class zeroBS__Metabox_QuoteAcceptedDetails extends zeroBS__Metabox{ 
        
        // this is for catching 'new' contacts
        private $newRecordNeedsRedir = false;

        public function __construct( $plugin_file ) {

            // set these
            $this->objType = 'quote';
            $this->metaboxID = 'zerobs-quote-status-edit';
            $this->metaboxTitle = __('Quote Public Status','zero-bs-crm'); // will be headless anyhow
            $this->headless = true;
            $this->metaboxScreen = 'zbs-add-edit-quote-edit';
            $this->metaboxArea = 'side';
            $this->metaboxLocation = 'low';
            $this->saveOrder = 1;
            $this->capabilities = array(

                'can_hide'          => false, // can be hidden
                'areas'             => array('side'), // areas can be dragged to - normal side = only areas currently
                'can_accept_tabs'   => true,  // can/can't accept tabs onto it
                'can_become_tab'    => false, // can be added as tab
                'can_minimise'      => true, // can be minimised
                'can_move'          => true // can be moved

            );

            global $useQuoteBuilder;   


            // call this 
            if ($useQuoteBuilder == "1") 
                $this->initMetabox();

        }

        public function html( $quote, $metabox ) {

            // localise ID & template
            $quoteID = -1; if (is_array($quote) && isset($quote['id'])) $quoteID = (int)$quote['id'];
            $templateUsed = -1; if (is_array($quote) && isset($quote['template'])) $templateUsed = $quote['template'];

            // quick WH predictive hack, not sure if viable - to test DAL3
            $quote['templated'] = false; if ($templateUsed !== -1 && !empty($templateUsed)) $quote['templated'] = true;
         
            global $useQuoteBuilder;   

            #} if enabled, and new quote, or one which hasn't had the 'templated' meta key added.
            #} ... also hide unless it's been "published"
            if ($useQuoteBuilder == "1" && (is_array($quote) && isset($quote['templated']) && $quote['templated'])) { 

                    if (isset($quote) && is_array($quote) && isset($quote['accepted']) && $quote['accepted'] > 0){

                        #} Deets
                        $acceptedDate = date(zeroBSCRM_getTimeFormat().' '.zeroBSCRM_getDateFormat(),$quote['accepted']);
                        $acceptedBy = $quote['acceptedsigned'];
                        $acceptedIP = $quote['acceptedip'];
                
                ?>

                        <table class="wh-metatab-side wptbp" id="wptbpMetaBoxQuoteStatus">
                            <tr><td style="text-align:center;color:green"><strong><?php _e('Accepted',"zero-bs-crm"); ?> <?php echo $acceptedDate; ?></strong></td></tr>
                            <?php if (!empty($acceptedBy)) { ?><tr><td style="text-align:center"><?php _e('By: ',"zero-bs-crm"); ?> <a href="mailto:<?php echo $acceptedBy; ?>" target="_blank"<?php if (!empty($acceptedIP)) { echo ' title="IP address:'.$acceptedIP.'"'; } ?>><?php echo $acceptedBy; ?></a></td></tr><?php } ?>                            
                        </table>   

                <?php


                } else {

                    ?>

                        <table class="wh-metatab-side wptbp" id="wptbpMetaBoxQuoteStatus">
                            <tr>
                            <td style="text-align:center"><strong><?php _e('Not Yet Accepted',"zero-bs-crm"); ?></td></tr>
                        </table>  

                    <?php

                }

            } else { // / only load if post type

                #} Gross hide :/
                ?><style type="text/css">#wpzbscquote_status {display:none;}</style><?php 

            }

              
        }

        // nothing to save

    }


/* ======================================================
  / Quote Accepted Details Metabox
   ====================================================== */



/* ======================================================
  Create Tags Box
   ====================================================== */

class zeroBS__Metabox_QuoteTags extends zeroBS__Metabox_Tags{


    public function __construct( $plugin_file ) {
    
        $this->objTypeID = ZBS_TYPE_QUOTE;
        // DAL3 switched for objType $this->postType = 'zerobs_customer';
        $this->objType = 'quote';
        $this->metaboxID = 'zerobs-quote-tags';
        $this->metaboxTitle = __('Quote Tags',"zero-bs-crm");
        $this->metaboxScreen = 'zbs-add-edit-quote-edit'; //'zerobs_edit_contact'; // we can use anything here as is now using our func
        $this->metaboxArea = 'side';
        $this->metaboxLocation = 'high';
        $this->showSuggestions = true;
        $this->capabilities = array(

            'can_hide'          => true, // can be hidden
            'areas'             => array('side'), // areas can be dragged to - normal side = only areas currently
            'can_accept_tabs'   => false,  // can/can't accept tabs onto it
            'can_become_tab'    => false, // can be added as tab
            'can_minimise'      => true // can be minimised

        );

        // call this 
        $this->initMetabox();

    }

    // html + save dealt with by parent class :) 

}

/* ======================================================
  / Create Tags Box
   ====================================================== */


/* ======================================================
    Quote Actions Metabox
   ====================================================== */

    class zeroBS__Metabox_QuoteActions extends zeroBS__Metabox{ 

        public function __construct( $plugin_file ) {

            // set these
            $this->objType = 'quote';
            $this->metaboxID = 'zerobs-quote-actions';
            $this->metaboxTitle = __('Quote Actions','zero-bs-crm'); // will be headless anyhow
            $this->headless = true;
            $this->metaboxScreen = 'zbs-add-edit-quote-edit';
            $this->metaboxArea = 'side';
            $this->metaboxLocation = 'high';
            $this->saveOrder = 1;
            $this->capabilities = array(

                'can_hide'          => false, // can be hidden
                'areas'             => array('high'), // areas can be dragged to - normal side = only areas currently
                'can_accept_tabs'   => true,  // can/can't accept tabs onto it
                'can_become_tab'    => false, // can be added as tab
                'can_minimise'      => true, // can be minimised
                'can_move'          => true // can be moved

            );

            // call this 
            $this->initMetabox();

        }

        public function html( $quote, $metabox ) {

            ?><div class="zbs-generic-save-wrap">

                    <div class="ui medium dividing header"><i class="save icon"></i> <?php _e('Quote Actions','zero-bs-crm'); ?></div>

            <?php

            // localise ID & content
            $quoteID = -1; if (is_array($quote) && isset($quote['id'])) $quoteID = (int)$quote['id'];
            
                #} if a saved post...
                //if (isset($post->post_status) && $post->post_status != "auto-draft"){
                if ($quoteID > 0){ // existing

                    // hard typed for now.
                    $acceptableQuoteStatuses = array(
                        "draft" => __('Draft','zero-bs-crm'),
                        "published" => __('Published, Unaccepted','zero-bs-crm'),
                        "accepted" => __('Accepted','zero-bs-crm')
                    );

                    // status
                    $status = __('Draft','zero-bs-crm');
                    if (is_array($quote) && isset($quote['status'])){
                        if ($quote['status'] == -2) $status = __('Published, Unaccepted','zero-bs-crm');
                        if ($quote['status'] == 1) $status = __('Accepted','zero-bs-crm');
                    }

                    /* grid doesn't work great for long-named:

                    <div class="ui grid">
                        <div class="six wide column">
                        </div>
                        <div class="ten wide column">
                        </div>
                    </div>

                    */
                    ?>
                    <div>
                        <label for="quote_status"><?php _e('Status',"zero-bs-crm"); ?>: </label>
                        <select id="quote_status" name="quote_status">
                            <?php foreach($acceptableQuoteStatuses as $statusOpt => $statusStr){

                                $sel = '';
                                if ($statusStr == $status) $sel = ' selected="selected"';
                                echo '<option value="'.$statusOpt.'"'. $sel .'>'.__($statusStr,"zero-bs-crm").'</option>';

                            } ?>
                        </select>
                    </div>

                    <div class="clear"></div>


                    <div class="zbs-quote-actions-bottom zbs-objedit-actions-bottom">

                        <button class="ui button green" type="button" id="zbs-edit-save"><?php _e("Update","zero-bs-crm"); ?> <?php _e("Quote","zero-bs-crm"); ?></button>

                        <?php

                            // delete?

                         // for now just check if can modify, later better, granular perms.
                         if ( zeroBSCRM_permsQuotes() ) { 
                        ?><div id="zbs-quote-actions-delete" class="zbs-objedit-actions-delete">
                             <a class="submitdelete deletion" href="<?php echo zbsLink('delete',$quoteID,'quote'); ?>"><?php _e('Delete Permanently', "zero-bs-crm"); ?></a>
                        </div>
                        <?php } // can delete  ?>
                        
                        <div class='clear'></div>

                    </div>
                <?php


                } else {

                    // NEW quote ?>

                    <button class="ui button green" type="button" id="zbs-edit-save"><?php _e("Save","zero-bs-crm"); ?> <?php _e("Quote","zero-bs-crm"); ?></button>

                 <?php

                }

            ?></div><?php // / .zbs-generic-save-wrap
              
        } // html

        // saved via main metabox

    }


/* ======================================================
  / Quotes Actions Metabox
   ====================================================== */
