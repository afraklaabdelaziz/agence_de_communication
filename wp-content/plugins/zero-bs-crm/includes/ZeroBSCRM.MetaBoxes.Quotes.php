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

   function zeroBSCRM_QuotesMetaboxSetup(){

        $zeroBS__MetaboxQuote = new zeroBS__MetaboxQuote( __FILE__ );
        $zeroBS__QuoteContentMetabox = new zeroBS__QuoteContentMetabox( __FILE__ );
        $zeroBS__QuoteActionsMetabox = new zeroBS__QuoteActionsMetabox( __FILE__ );
        $zeroBS__QuoteStatusMetabox = new zeroBS__QuoteStatusMetabox( __FILE__ );

   }

   add_action( 'after_zerobscrm_settings_init','zeroBSCRM_QuotesMetaboxSetup');

/* ======================================================
   / Init Func
   ====================================================== */



/* ======================================================
  Declare Globals
   ====================================================== */

    #} Used throughout
    // Don't know who added this, but GLOBALS are out of scope here
    //global $zbsCustomerFields,$zbsCustomerQuoteFields,$zbsCustomerInvoiceFields;    


/* ======================================================
  / Declare Globals
   ====================================================== */

/*===

Quote Template helper box

===*/

##WLREMOVE

//Register Meta Box

##/WLREMOVE


/* ======================================================
  Quote Metabox
   ====================================================== */

    class zeroBS__MetaboxQuote {

        static $instance;
        #private $packPerm;
        #private $pack;
        private $postType;

        public function __construct( $plugin_file ) {
           # if ( $this->instance instanceof wProject_Metabox ) {
            #    wp_die( sprintf( __w( 'Cannot instantiate singleton class: %1$s. Use %1$s::$instance instead.', 'plugin-namespace' ), __CLASS__ ) );
            #} else {
                self::$instance = $this;
            #}

            $this->postType = 'zerobs_quote';
            #if (???) wp_die( sprintf( __w( 'Cannot instantiate class: %1$s without pack', 'wptbp' ), __CLASS__ ) );

            add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );
            add_filter( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
        }

        public function create_meta_box() {

            #'wptbp'.$this->postType

            add_meta_box(
                'wpzbscq_itemdetails',
                __('Step 1: Quote Details',"zero-bs-crm"),
                array( $this, 'print_meta_box' ),
                $this->postType,
                'normal',
                'high'
            );
        }

        public function print_meta_box( $post, $metabox ) {


                #} retrieve
                $zbsQuote = get_post_meta($post->ID, 'zbs_customer_quote_meta', true);
                $zbsCustomerID = get_post_meta($post->ID, 'zbs_customer_quote_customer', true);
                $zbsTemplateIDUsed = get_post_meta($post->ID, 'zbs_quote_template_id', true);
                #} this is a temp weird one, just passing onto meta for now (long day):
                $zbsTemplated = get_post_meta($post->ID, 'templated', true);
                if (!empty($zbsTemplated)) $zbsQuote['templated'] = true;


                #} if customer id is empty, but prefill isn't, use prefill
                if (empty($zbsCustomerID) && isset($_GET['zbsprefillcust'])) $zbsCustomerID = (int)sanitize_text_field($_GET['zbsprefillcust']);



                #} pass to other metaboxes
                global $zbsCurrentEditQuote; $zbsCurrentEditQuote = $zbsQuote;

                /* mikes first fix
                #} first time load?
                
                $zbsQuoteFirstTime = get_post_meta($post->ID,'zbs_first_time_quote', true);
                if($zbsQuoteFirstTime == ''){
                    ZeroBSCRM_load_quote_template_chooser();
                    update_post_meta($post->ID,'zbs_first_time_quote',1);
                }
                */


                global $zbsCustomerQuoteFields;
                $fields = $zbsCustomerQuoteFields;
                
                #} Using "Quote Builder" or not?
                $useQuoteBuilder = zeroBSCRM_getSetting('usequotebuilder');

                ?><input type="hidden" name="meta_box_ids[]" value="<?php echo $metabox['id']; ?>" /><?php wp_nonce_field( 'save_' . $metabox['id'], $metabox['id'] . '_nonce' );

                    #} New quote?
                    if (gettype($zbsQuote) != "array") echo '<input type="hidden" name="zbscrm_newquote" value="1" />';

                    #} pass this if already templated:
                    if ($useQuoteBuilder == "1" && isset($zbsQuote['templated'])) echo '<input type="hidden" name="zbscrm_templated" id="zbscrm_templated" value="1" />';

                ?>

            <?php echo '<input type="hidden" name="quo-ajax-nonce" id="quo-ajax-nonce" value="' . wp_create_nonce( 'quo-ajax-nonce' ) . '" />'; ?>
                <style>
                    #post-body-content{
                        display:none;
                    }
                        @media all and (max-width:699px){
                        table.wh-metatab{
                            min-width:100% !important;
                        }
                    }  
                </style>

                <table class="form-table wh-metatab wptbp" id="wptbpMetaBoxMainItem">

                    <?php 
                    // QUOTE ID is seperate / unchangable
                    ?><tr class="wh-large"><th><label><?php _e('Quote (ID)',"zero-bs-crm");?>:</label></th>
                    <td>
                        <div class="zbs-prominent"><?php 

                        # $pid = (int)$post->ID; echo $pid+$quoteOffset; 
                        #TRANSITIONTOMETANO
                        #$quoteOffset = zeroBSCRM_getQuoteOffset();
                        #$quoteID = (int)$post->ID+$quoteOffset; 

                        $zbsQuoteID = get_post_meta($post->ID,"zbsid",true);
                        if (!empty($zbsQuoteID)) 
                            $quoteID = (int)$zbsQuoteID;
                        else #} override
                            $quoteID = zeroBSCRM_getNextQuoteID();

                        echo $quoteID;

                        ?><input type="hidden" name="zbsquoteid" value="<?php echo $quoteID; ?>" /></div>
                    </td></tr><?php


                    #} ALSO customer assigning is seperate:
                    ?><tr class="wh-large"><th><label><?php _e('Customer',"zero-bs-crm");?>:</label></th>
                    <td><?php

                        #} 27/09/16 - switched select for typeahead

                            #} Any customer?
                            $prefillStr = ''; if (isset($zbsCustomerID) && !empty($zbsCustomerID)){

                                #$zbsCustomer = zeroBS_getCustomer($zbsCustomerID);
                                #this was, for some reason, bugging out. $prefillStr = zeroBS_getCustomerName($zbsCustomerID);
                                $prefillStr = zeroBS_getCustomerNameShort($zbsCustomerID);
                                
                            }

                            #} Output select box
                            echo zeroBSCRM_CustomerTypeList('zbscrmjs_quoteCustomerSelect',$prefillStr,true,'zbscrmjs_quote_unsetCustomer');

                            #} Output input which will pass the value via post
                            ?><input type="hidden" name="zbscq_customer" id="zbscq_customer" value="<?php echo $zbsCustomerID; ?>" /><?php

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

                            </script><?php
                    /*
                        <select name="zbscq_customer" class="form-control" style="font-size:16px;">
                       <?php
                        //catcher
                        echo '<option value="" disabled="disabled"';
                        if (!isset($zbsCustomerID) || (isset($zbsCustomerID)) && empty($zbsCustomerID)) echo ' selected="selected"';
                        echo '>Select</option>';

                        $customerList = zeroBS_getCustomers(true,10000);

                        if (count($customerList) > 0) foreach ($customerList as $customer){

                            echo '<option value="'.$customer['id'].'"';
                            if (isset($zbsCustomerID) && $customer['id'] == $zbsCustomerID) echo ' selected="selected"';
                            echo '>'.zeroBS_customerName($customer['id'],$customer).'</option>';
                        }

                       ?>
                        </select>
                    */ ?>
                    </td></tr><?php

                    // wh centralised 20/7/18 - 2.91+
                    zeroBSCRM_html_editFields($zbsQuote,$fields,'zbscq_');

                    #} if enabled, and new quote, or one which hasn't had the 'templated' meta key added.
                    if ($useQuoteBuilder == "1" && (gettype($zbsQuote) != "array" || !isset($zbsQuote['templated']))){

                        ?><tr class="wh-large" id="zbs-quote-builder-step-1">

                            <th colspan="2">

                                <div class="zbs-move-on-wrap">

                                    <!-- infoz -->
                                    <h3><?php _e('Publish this Quote',"zero-bs-crm");?></h3>
                                    <p><?php _e('Do you want to use the Quote Builder to publish this quote? (This lets you email it to a client directly, for approval)',"zero-bs-crm");?></p>

                                    <input type="hidden" name="zbs_quote_template_id_used" id="zbs_quote_template_id_used" value="<?php if (isset($zbsTemplateIDUsed) && !empty($zbsTemplateIDUsed)) echo $zbsTemplateIDUsed; ?>" />
                                    <select class="form-control" name="zbs_quote_template_id" id="zbs_quote_template_id">
                                        <option value="" disabled="disabled"><?php _e('Select a template',"zero-bs-crm");?>:</option>
                                        <?php

                                            $templates = zeroBS_getQuoteTemplates(true,100,0);

                                            #} If this quote has already selected a template it'll be stored in the meta under 'templateid'
                                            #} But if it's not the first, we never need to show this anyway...

                                            if (count($templates) > 0) foreach ($templates as $template){

                                                $templateName = 'Template '.$template['id']; 
                                                if (isset($template['name']) && !empty($template['name'])) $templateName = $template['name'].' ('.$template['id'].')';

                                                echo '<option value="'.$template['id'].'"';
                                                #if (isset())
                                                echo '>'.$templateName.'</option>';

                                            }

                                        ?>
                                        <option value=""><?php _e("Blank Template","zero-bs-crm");?></option>
                                    </select>
                                    <br />
                                    <p><?php 
                                    $quo_temp = esc_url(zbsLink($zbs->slugs['quote-templates']));
                                    _e('Create additional quote templates',"zero-bs-crm"); ?> <a href="<?php echo $quo_temp;?>">
                                    <?php _e('here',"zero-bs-crm");?></a>
                                    </p>
                                    <button type="button" id="zbsQuoteBuilderStep2" class="button button-primary button-large xl"<?php if (!isset($zbsCustomerID) || empty($zbsCustomerID)){ echo ' disabled="disabled"'; } ?>><?php _e('Use Quote Builder',"zero-bs-crm");?></button>
                                    <?php if (!isset($zbsCustomerID) || empty($zbsCustomerID)){ ?>
                                    <p id="zbsQuoteBuilderStep2info">(<?php _e("You'll need to assign this Quote to a customer to use this","zero-bs-crm");?>);</p>
                                    <?php } ?>

                                </div>

                            </th>

                        </tr><?php

                    } ?>

            </table>
        <?php }

        public function save_meta_box( $post_id, $post ) {
            if( !isset($_POST['meta_box_ids']) || empty( $_POST['meta_box_ids'] ) || !is_array($_POST['meta_box_ids']) ){ return; }
            foreach( $_POST['meta_box_ids'] as $metabox_id ){
                if( !isset($_POST[ $metabox_id . '_nonce' ]) || ! wp_verify_nonce( $_POST[ $metabox_id . '_nonce' ], 'save_' . $metabox_id ) ){ continue; }
                #if( count( $_POST[ $metabox_id . '_fields' ] ) == 0 ){ continue; }
                if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){ continue; }

                if( $metabox_id == 'wpzbscq_itemdetails'  && $post->post_type == $this->postType){

                    // send as attachments checkbox
                    $sendAsAttachments = -1; if (isset($_POST['zbsc_sendattachments']) && !empty($_POST['zbsc_sendattachments'])) $sendAsAttachments = 1;
                    update_post_meta($post_id, 'zbs_quote_sendattachments', $sendAsAttachments);  

                    #} First up, save quote id! #TRANSITIONTOMETANO
                    $quoteOffset = zeroBSCRM_getQuoteOffset();
                    $quoteID = (int)$post_id+$quoteOffset; if (isset($_POST['zbsquoteid']) && !empty($_POST['zbsquoteid'])) $quoteID = (int)sanitize_text_field($_POST['zbsquoteid']);
                    update_post_meta($post_id,"zbsid",$quoteID);

                    #} and increment this 
                    if (!empty($quoteID)) zeroBSCRM_setMaxQuoteID($quoteID);

                    global $zbsCustomerQuoteFields;

                    // retrieve _POST into arr
                    $zbsCustomerQuoteMeta = zeroBSCRM_save_fields($zbsCustomerQuoteFields,'zbscq_');

                    # and customer
                    #} This should probs be just a custom post meta of "customer" and then the query customer side dealt with...
                    #} Else you could change this to another customer but leave trail on prev customer
                    $zbsCustomerQuoteCustomer = -1; if (isset($_POST['zbscq_customer'])) $zbsCustomerQuoteCustomer = (int)sanitize_text_field($_POST['zbscq_customer']);
                    if ($zbsCustomerQuoteCustomer !== -1) update_post_meta($post_id, 'zbs_customer_quote_customer', $zbsCustomerQuoteCustomer);



                    #} UPDATE!
                    update_post_meta($post_id, 'zbs_customer_quote_meta', $zbsCustomerQuoteMeta);


                    #} Log quote builder stuff..
                    #zbs_quote_template_id



                    #} Is new quote? (passed from metabox html)
                    #} Internal Automator
                    if (isset($_POST['zbscrm_newquote']) && $_POST['zbscrm_newquote'] == 1){

                        zeroBSCRM_FireInternalAutomator('quote.new',array(
                            'id'=>$post_id,
                            'againstid' => $zbsCustomerQuoteCustomer,
                            'quoteMeta'=> $zbsCustomerQuoteMeta,
                            'zbsid'=>$quoteID
                            ));
                        
                    }

                }


                # === OTHER METABOX: CONTENT

                if( $metabox_id == 'wpzbscsub_quotecontent' && $post->post_type == $this->postType){

                    #} UPDATE!

                        #http://stackoverflow.com/questions/3493313/how-to-add-wysiwyg-editor-in-wordpress-meta-box

                      if (isset($_POST['zbs_quote_content'])) {

                        
                        #} Save content
                        //$data=htmlspecialchars($_POST['zbs_quote_content']);
                        $data = zeroBSCRM_io_WPEditor_WPEditorToDB($_POST['zbs_quote_content']);
                        update_post_meta($post_id, 'zbs_quote_content', $data );

                        #} update templated vars
                        $templateID = ''; if (isset($_POST['zbs_quote_template_id_used'])) $templateID = (int)sanitize_text_field($_POST['zbs_quote_template_id_used']);
                        update_post_meta($post_id, 'zbs_quote_template_id',$templateID);
                        update_post_meta($post_id, 'templated',1);


                      }

                }

                # === / OTHER BOX


            }

            return $post;
        }
    }

/* ======================================================
  / Quote Metabox
   ====================================================== */




/* ======================================================
  Quote Content Metabox
   ====================================================== */

    class zeroBS__QuoteContentMetabox {

        static $instance;
        private $postType;
        private $postTypesLabels;

        public function __construct( $plugin_file ) {

            self::$instance = $this;

            $this->postType = 'zerobs_quote';        
            #} Temp
            $this->postTypesLabels = array(
                'zerobs_quote' => 'Quote'
            );
            add_action( 'add_meta_boxes', array( $this, 'initMetaBox' ) );

            #add_filter( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
        }

        public function initMetaBox(){

                add_meta_box(
                    'wpzbscsub_quotecontent',
                    __('Step 2: Quote Content',"zero-bs-crm"), #quick title
                    array( $this, 'print_meta_box' ),
                    $this->postType,
                    'normal',
                    'low'
                );


        }
        public function print_meta_box( $post, $metabox ) {

            #} nonce etc.
            ?><input type="hidden" name="meta_box_ids[]" value="<?php echo $metabox['id']; ?>" /><?php wp_nonce_field( 'save_' . $metabox['id'], $metabox['id'] . '_nonce' );

            #http://stackoverflow.com/questions/3493313/how-to-add-wysiwyg-editor-in-wordpress-meta-box

            #echo "<h3>Quote Content:</h3>";
            $content = zeroBSCRM_io_WPEditor_DBToWPEditor(get_post_meta($post->ID, 'zbs_quote_content' , true ));

            // remove "Add contact form" button from Jetpack
            remove_action( 'media_buttons', 'grunion_media_button', 999 );
            wp_editor( $content, 'zbs_quote_content', array(
                'editor_height' => 580,
                'wpautop' => false,
            )); #array("media_buttons" => false) as a last param removes media
        }
    }


/* ======================================================
  / Quote Content Metabox
   ====================================================== */









/* ======================================================
  Quote Next Step (Actions) Metabox
   ====================================================== */

    class zeroBS__QuoteActionsMetabox {

        static $instance;
        private $postType;
        private $postTypesLabels;

        public function __construct( $plugin_file ) {

            self::$instance = $this;

            $this->postType = 'zerobs_quote';        
            #} Temp
            $this->postTypesLabels = array(
                'zerobs_quote' => 'Quote'
            );
            add_action( 'add_meta_boxes', array( $this, 'initMetaBox' ) );

            #add_filter( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
        }

        public function initMetaBox(){

                add_meta_box(
                    'wpzbscsub_quoteactions',
                    __('Step 3: Publish and Send',"zero-bs-crm"), #quick title
                    array( $this, 'print_meta_box' ),
                    $this->postType,
                    'normal',
                    'low'
                );


        }
        public function print_meta_box( $post, $metabox ) {

            #} retrieve
            global $zbsCurrentEditQuote; $zbsQuote = $zbsCurrentEditQuote;
                
            #} Using "Quote Builder" or not?
            $useQuoteBuilder = zeroBSCRM_getSetting('usequotebuilder');




            #} if enabled, and new quote, or one which hasn't had the 'templated' meta key added.
            if ($useQuoteBuilder == "1") { 

                #} find customer email if one:
                $zbsCustomerID = get_post_meta($post->ID, 'zbs_customer_quote_customer', true);

                // retrieve email $customerEmail = ''; 
                $customerEmail = zeroBS_customerEmail($zbsCustomerID);

                ?><input type="hidden" name="meta_box_ids[]" value="<?php echo $metabox['id']; ?>" /><?php

                #} first load?
                if (gettype($zbsQuote) != "array" || !isset($zbsQuote['templated'])){

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

                        if (isset($customerEmail) && !empty($customerEmail) && zeroBSCRM_validateEmail($customerEmail)){

                            // has email, and portal, all good

                            ?>

                                <div class="zbs-move-on-wrap" style="padding-top:30px;">
                                    <?php 
                                        #} Add nonce
                                        echo '<script type="text/javascript">var zbscrmjs_secToken = \''.wp_create_nonce( "zbscrmjs-ajax-nonce" ).'\';</script>';
                                    ?>

                                    <!-- infoz -->
                                    <h3><?php _e("Quote Actions","zero-bs-crm");?></h3>
                                    <p><?php _e("Great! Your Quote has been published. You can now  issue it to your customer","zero-bs-crm");?>:</p>

                                    <?php do_action('zbs_quote_actions'); ?>

                                    <div class="zbsEmailOrShare">
                                        <h4><?php _e("Email to Contact:","zero-bs-crm");?>:</h4>
                                        <input type="hidden" class="form-control" id="zbsQuoteBuilderEmailTo" value="<?php echo $contactEmail; ?>" placeholder="<?php _e('e.g. customer@example.com','zero-bs-crm'); ?>" data-quoteid="<?php echo $quoteID; ?>" />
                                        <p><i class="email outline icon blue" style="font-size:30px;margin-top:10px;"></i></p>
                                        <p><button type="button" id="zbsQuoteBuilderSendNotification" class="button button-primary button-large"><?php _e("Email Quote","zero-bs-crm");?></button></p>
                                        <p class="small" id="zbsQuoteBuilderEmailToErr" style="display:none"><?php _e("An Email Address to send to is required","zero-bs-crm");?>!</p>
                                    </div>
                                    <div class="zbsEmailOrShare">
                                    <?php
                                        // temp changes from https://bitbucket.org/mikeandwoody/zerobs-core/commits/5f60426740a36c17fdd4f0d2f0f42e6e79394f0b
                                        //$prevURL = home_url('/clients/quotes/'.$post->ID); // was echo get_the_permalink($post->ID);
                                        
                                        #} If Portal is OFF this will totally goose the whole Quote functionality.
                                        

                                            $prevURL = zeroBS_portal_link('quotes',$post->ID); // as of 2.89
                                            ?>
                                            <h4><?php _e("Share the Link or","zero-bs-crm");?> <a href="<?php echo esc_url($prevURL);  ?>" target="_blank"><?php _e("preview","zero-bs-crm");?></a>:</h4>
                                            <p><input type="text" class="form-control" id="zbsQuoteBuilderURL" value="<?php echo esc_url($prevURL);  ?>" /></p>

                                   
                                        <!--<p class="small">Note: Anyone who has this link can view this proposal.</p>-->
                                    </div>     

                                    <?php
                                    #} WH second change, only showed if dompdf extension installed
                                    /*
                                        4.0.8 removed backward compatibility for pdf quotes not on DAL3.
                                                                
                                            if (zeroBSCRM_isExtensionInstalled('pdfinv')){
                                                
                                                #} PDF Invoicing is installed
                                                ?>
                                                <div class="zbsEmailOrShare">
                                                <h4><?php _e("Download PDF","zero-bs-crm");?></h4>
                                                <p><i class="file pdf outline icon red" style="font-size:30px;margin-top:10px;"></i></p>
                                                <input type="button" name="zbs_quote_download_pdf" id="zbs_quote_download_pdf" class="ui button green" value="<?php _e("Download PDF","zero-bs-crm");?>" />
                                               
                                                </div>
                                                <script type="text/javascript">
                                                jQuery(function(){

                                                    // add your form to the end of body (outside <form>)
                                                    var formHTML = '<form target="_blank" method="post" id="zbs_quote_download_pdf_form" action="">';
                                                        formHTML += '<input type="hidden" name="zbs_quote_download_pdf" value="1" />';
                                                        formHTML += '<input type="hidden" name="zbs_quote_id" value="<?php echo $post->ID; ?>" />';
                                                        formHTML += '</form>';
                                                    jQuery('#wpbody').append(formHTML);

                                                    // on click
                                                    jQuery('#zbs_quote_download_pdf').on( 'click', function(){
                                                        // submit form
                                                        jQuery('#zbs_quote_download_pdf_form').submit();
                                                    });

                                                });                    
                                                </script>
                                                <?php 

                                            }
                                    */
                                ?>

                   

                                </div>

                            <?php


                        } else {

                            // NO contact EMAIL ! 
                            ?>

                                <div class="zbs-move-on-wrap" style="padding-top:30px;">

                                    <!-- infoz -->
                                    <h3><?php _e("Email or Share","zero-bs-crm");?></h3>
                                    <div class="zbsEmailOrShare">
                                        <h4><?php _e("Add Customer's Email","zero-bs-crm");?>:</h4>
                                        <p><?php _e('To proceed, edit the contact and add their email address, that way we can then send them this quote online.','zero-bs-crm'); ?></p>
                                        <p><a href="<?php echo zbsLink('edit',$zbsCustomerID,'zerobs_customer',true); ?>" class="button button-primary button-large"><?php _e("Edit Contact","zero-bs-crm");?></a></p>
                                    </div>              

                                </div>

                            <?php
                        }
                    }

                }

            } # if quotebuilder

        }
        public function save_meta_box( $post_id, $post ) {
            /*
            if( empty( $_POST['meta_box_ids'] ) ){ return; }
            foreach( $_POST['meta_box_ids'] as $metabox_id ){
                if( !isset($_POST[ $metabox_id . '_nonce' ]) || ! wp_verify_nonce( $_POST[ $metabox_id . '_nonce' ], 'save_' . $metabox_id ) ){ continue; }
                #if( count( $_POST[ $metabox_id . '_fields' ] ) == 0 ){ continue; }
                if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){ continue; }

                if( $metabox_id == 'wpzbscsub_quoteactions'  && $post->post_type == $this->postType){

                    #} UPDATE!

                }
            } */

            return $post;
        }
    }


/* ======================================================
  / Quote Content Metabox
   ====================================================== */




/* ======================================================
  Initiate Quote Metabox
   ====================================================== */

    function zeroBS__addQuoteMetaBoxes() {  

        // quotes
        add_meta_box('zerobs-customer-quotes', __('Quote Files (optional)',"zero-bs-crm"), 'zeroBS__MetaboxFilesQuotes', 'zerobs_quote', 'normal', 'low');  

    }
    add_action('add_meta_boxes', 'zeroBS__addQuoteMetaBoxes');  

/* ======================================================
  / Initiate Quote Metabox
   ====================================================== */






/* ======================================================
  Quote Files Metabox
   ====================================================== */

function zeroBS__MetaboxFilesQuotes($post) {  
    //wp_nonce_field(plugin_basename(__FILE__), 'zbsc_quote_attachment_nonce');
    /*$html = '<p class="description">';
    $html .= 'Upload your PDF here.';
    $html .= '</p>';
    $html .= '<input type="file" id="zbsc_quote_attachment" name="zbsc_quote_attachment" size="25">';
    echo $html; */

    $html = '';

    // wmod

            #} retrieve
            $zbsCustomerQuoteFiles = get_post_meta($post->ID, 'zbs_customer_quotes', true);
            $zbsSendAttachments = get_post_meta($post->ID, 'zbs_quote_sendattachments', true);

    ?>
            <table class="form-table wh-metatab wptbp" id="wptbpMetaBoxMainItemQuotes">

                <?php 

                #} Any existing
                if (is_array($zbsCustomerQuoteFiles) && count($zbsCustomerQuoteFiles) > 0){ 
                  ?><tr class="wh-large"><th><label><?php echo count($zbsCustomerQuoteFiles).' Quote(s):'; ?></label></th>
                            <td id="zbsFileWrapQuotes">
                                <?php $fileLineIndx = 1; foreach($zbsCustomerQuoteFiles as $quoteFile){

                                    $file = basename($quoteFile['file']);
                                    // if in privatised system, ignore first hash in name
                                    if (isset($quoteFile['priv'])){

                                        $file = substr($file,strpos($file, '-')+1);
                                    }

                                    echo '<div class="zbsFileLine" id="zbsFileLineQuote'.$fileLineIndx.'"><a href="'.$quoteFile['url'].'" target="_blank">'.$file.'</a> (<span class="zbsDelFile" data-delurl="'.$quoteFile['url'].'"><i class="fa fa-trash"></i></span>)</div>';
                                    $fileLineIndx++;

                                } ?>
                            </td></tr><?php

                } ?>

                <?php #adapted from http://code.tutsplus.com/articles/attaching-files-to-your-posts-using-wordpress-custom-meta-boxes-part-1--wp-22291


                        wp_nonce_field(plugin_basename(__FILE__), 'zbsc_quote_attachment_nonce');
                         
                        $html .= '<input type="file" id="zbsc_quote_attachment" name="zbsc_quote_attachment" size="25">';
                        
                        ?><tr class="wh-large"><th><label><?php _e('Add File',"zero-bs-crm");?>:</label><br />(<?php _e('Optional',"zero-bs-crm");?>)<br /><?php _e('Accepted File Types',"zero-bs-crm");?>:<br /><?php echo zeroBS_acceptableFileTypeListStr(); ?></th>
                            <td><?php
                        echo $html;

                ?></td></tr>


                <?php 

                    // optionally send with email as attachment?

                ?><tr class="">
                        <td></td>
                        <td>
                            <label for="zbsc_sendattachments"><?php _e('Send as Attachments',"zero-bs-crm");?>:</label> <input type="checkbox" id="zbsc_sendattachments" name="zbsc_sendattachments" class="form-control" value="1"<?php if ($zbsSendAttachments == "1") echo ' checked="checked"'; ?> style="line-height: 1em;vertical-align: middle;display: inline-block;margin: 0;margin-top: -0.5em;" />
                            <br /><?php _e('Optionally send a copy of the attached files along with any quote emails sent.',"zero-bs-crm");?>                            
                        </td>
                    </tr>
            
            </table>
            <script type="text/javascript">

                var zbsQuotesCurrentlyDeleting = false;

                jQuery(function(){

                    // turn off auto-complete on records via form attr... should be global for all ZBS record pages
                    jQuery('#post').attr('autocomplete','off');

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
                                    'zbsCID': <?php echo $post->ID; ?>,
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
                                            //jQuery(this).closest('.zbsFileLine').remove();
                                            jQuery('#' + lineIDtoRemove).remove();


                                            // Callback
                                            //if (typeof cb == "function") cb(response);
                                            //callback(response);

                                          },
                                          error: function(response){

                                            jQuery('#zbsFileWrapQuotes').append('<div class="alert alert-error" style="margin-top:10px;"><strong>Error:</strong> Unable to delete this file.</div>');

                                            // Callback
                                            //if (typeof errorcb == "function") errorcb(response);
                                            //callback(response);


                                          }

                                        });

                            }

                            window.zbsQuotesCurrentlyDeleting = false;

                        } // / blocking

                    });

                });


            </script><?php
}

add_action('save_post', 'zbsc_save_quote_data',100);
function zbsc_save_quote_data($id) {

    global $zbsc_justUploadedQuote;


    if(isset($_FILES['zbsc_quote_attachment']) && isset($_FILES['zbsc_quote_attachment']['name']) && !empty($_FILES['zbsc_quote_attachment']['name']) && 
                (!isset($zbsc_justUploadedQuote) ||
                    (isset($zbsc_justUploadedQuote) && $zbsc_justUploadedQuote != $_FILES['zbsc_quote_attachment']['name'])
                )
                )  {

    /* --- security verification --- */
    if(!wp_verify_nonce($_POST['zbsc_quote_attachment_nonce'], plugin_basename(__FILE__))) {
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
        $zbsc_justUploadedQuote = $_FILES['zbsc_quote_attachment']['name'];

        $supported_types = zeroBS_acceptableFileTypeMIMEArr(); //$supported_types = array('application/pdf');
        $arr_file_type = wp_check_filetype(basename($_FILES['zbsc_quote_attachment']['name']));
        $uploaded_type = $arr_file_type['type'];

        if(in_array($uploaded_type, $supported_types) || (isset($supported_types['all']) && $supported_types['all'] == 1)) {
            $upload = wp_upload_bits($_FILES['zbsc_quote_attachment']['name'], null, file_get_contents($_FILES['zbsc_quote_attachment']['tmp_name']));
            if(isset($upload['error']) && $upload['error'] != 0) {
                wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
            } else {
                //update_post_meta($id, 'zbsc_quote_attachment', $upload);

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
                                // NAMING IS AWFUL HERE!
                                $zbsCustomerQuotes = get_post_meta($id,'zbs_customer_quotes', true);

                                if (is_array($zbsCustomerQuotes)){

                                    //add it
                                    $zbsCustomerQuotes[] = $upload;

                                } else {

                                    // first
                                    $zbsCustomerQuotes = array($upload);

                                }

                                update_post_meta($id, 'zbs_customer_quotes', $zbsCustomerQuotes);  
            }
        }
        else {
            wp_die("The file type that you've uploaded is not an accepted file format.");
        }
    }
}

/* ======================================================
  / Quote Files Metabox
   ====================================================== */




/* ======================================================
  Quote Accepted Details Metabox
   ====================================================== */

    class zeroBS__QuoteStatusMetabox {

        static $instance;
        private $postTypes;

        public function __construct( $plugin_file ) {
        
            self::$instance = $this;

            #} Moved to multiples 1.1.19 WH
            $this->postTypes = array('zerobs_quote');        
            add_action( 'add_meta_boxes', array( $this, 'initMetaBox' ) );

        }

        public function initMetaBox(){

            if (count($this->postTypes) > 0) foreach ($this->postTypes as $pt){

                #} pass an arr
                $callBackArr = array($this,$pt);

                add_meta_box(
                    'wpzbscquote_status',
                    __('Quote Public Status',"zero-bs-crm"),
                    array( $this, 'print_meta_box' ),
                    $pt,
                    'side',
                    'low',
                    $callBackArr
                );

            }

        }

        public function print_meta_box( $post, $metabox ) {

            global $useQuoteBuilder;

            #} retrieve / localise - NOTE: we're getting this 5 ways on same page... re-write for efficiency
            $zbsQuote = get_post_meta($post->ID, 'zbs_customer_quote_meta', true);
                #} this is a temp weird one, just passing onto meta for now (long day):
                $zbsTemplated = get_post_meta($post->ID, 'templated', true);
                if (!empty($zbsTemplated)) $zbsQuote['templated'] = true;

            #} if enabled, and new quote, or one which hasn't had the 'templated' meta key added.
            #} ... also hide unless it's been "published"
            if ($useQuoteBuilder == "1" && (is_array($zbsQuote) && isset($zbsQuote['templated']))) { 

                    if (isset($zbsQuote) && is_array($zbsQuote) && isset($zbsQuote['accepted'])){

                        #} Deets
                        $acceptedDate = date(zeroBSCRM_getTimeFormat().' '.zeroBSCRM_getDateFormat(),$zbsQuote['accepted'][0]);
                        $acceptedBy = $zbsQuote['accepted'][1];
                        $acceptedIP = $zbsQuote['accepted'][2];
                
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


    }


/* ======================================================
  / Quote Accepted Details Metabox
   ====================================================== */