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

   function zeroBSCRM_InvoicesMetaboxSetup(){

        // invoices
        add_meta_box('zerobs-customer-invoices', __('Attachments',"zero-bs-crm"), 'zeroBS__MetaboxFilesInvoice', 'zerobs_invoice', 'normal', 'low'); 
     
     
     //   add_meta_box('zerobs-customer-invoices-test', __('Send Test',"zero-bs-crm"), 'zeroBS__SendTest', 'zerobs_invoice', 'side', 'low'); 



   }

   // this one needs to fire later :)
   //add_action( 'after_zerobscrm_settings_init','zeroBSCRM_InvoicesMetaboxSetup');
   add_action( 'add_meta_boxes','zeroBSCRM_InvoicesMetaboxSetup');

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




function zeroBS__InvoicePro(){

        $upTitle = __('Want more from invoicing?',"zero-bs-crm");
        $upDesc = __('Accept Payments Online with Invoicing Pro.',"zero-bs-crm");
        $upButton = __('Buy Now',"zero-bs-crm");
        $upTarget = "https://jetpackcrm.com/product/invoicing-pro/";

        echo zeroBSCRM_UI2_squareFeedbackUpsell($upTitle,$upDesc,$upButton,$upTarget); 
            
}


/* ======================================================
  Invoice Metabox
   ====================================================== */

    class zeroBS__MetaboxInvoice {

        //this is the class that outputs the current invoice metabox. 

        static $instance;
        #private $packPerm;
        #private $pack;
        private $postType;

        public function __construct( $plugin_file ) {
           # if ( $this->instance instanceof wProject_Metabox ) {
            #    wp_die( sprintf( __( 'Cannot instantiate singleton class: %1$s. Use %1$s::$instance instead.', 'zero-bs-crm' ), __CLASS__ ) );
            #} else {
                self::$instance = $this;
            #}

            $this->postType = 'zerobs_invoice';
            #if (???) wp_die( sprintf( __( 'Cannot instantiate class: %1$s without pack', 'zero-bs-crm' ), __CLASS__ ) );

            add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );
            add_filter( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
        }

        public function create_meta_box() {

            #'wptbp'.$this->postType

            add_meta_box(
                'wpzbsci_itemdetails',
                ' ',
                array( $this, 'print_meta_box' ),
                $this->postType,
                'normal',
                'high'
            );
        }

        public function print_meta_box( $post, $metabox ) {
                global $zbs;

                #} Prefill ID and OBJ are added to the #zbs_invoice to aid in prefilling the data (when drawn with JS)
                $prefill_id = -1; $prefill_obj = -1;
                if(isset($_GET['zbsprefillcust']) && !empty($_GET['zbsprefillcust'])){
                    $prefill_id = (int)$_GET['zbsprefillcust'];
                    $prefill_obj = 1; //1 for contact, 2 for company (for filling and settings of invoice meta) v2.9x doesn't have prefill Company.
                }                
                ?>
                <?php #} AJAX NONCE ?><script type="text/javascript">var zbscrmjs_secToken = '<?php echo wp_create_nonce( "zbscrmjs-ajax-nonce" ); ?>';</script><?php # END OF NONCE ?>

                <!-- WordPress nonce stuff -->
                <input type="hidden" name="meta_box_ids[]" value="<?php echo $metabox['id']; ?>" />
                <input type="hidden" name="<?php echo $metabox['id']; ?>_fields[]" value="subtitle_text" />
                <?php wp_nonce_field( 'save_' . $metabox['id'], $metabox['id'] . '_nonce' );
                echo '<input type="hidden" name="inv-ajax-nonce" id="inv-ajax-nonce" value="' . wp_create_nonce( 'inv-ajax-nonce' ) . '" />';

                //invoice UI divs (loader and canvas)
                echo '<div id="zbs_loader"><div class="ui active dimmer inverted"><div class="ui text loader">'. __('Loading Invoice','zero-bs-crm')  .'</div></div><p></p></div>';
                echo "<div id='zbs_invoice' class='zbs_invoice_html_canvas' data-invid='". $post->ID. "' data-prefillid='". $prefill_id. "' data-prefillobj='". $prefill_obj. "'></div>";

                // invoicing UI errors (get shown if issue)

                    ?><div id="zbs-invbuilder-warnings-wrap">
                    <?php #} Pre-loaded msgs, because I wrote the helpers in php first... should move helpers to js and fly these 

                    echo zeroBSCRM_UI2_messageHTML('warning hidden','Error Retrieving Invoice','There has been a problem retrieving your invoice, if this issue persists, please ask your administrator to reach out to Jetpack CRM.','disabled warning sign','zbsCantLoadData');
                    //echo zeroBSCRM_UI2_messageHTML('warning hidden','Error Updating Columns '.$this->plural,'There has been a problem saving your column configuration, if this issue persists, please ask your administrator to reach out to Jetpack CRM.','disabled warning sign','zbsCantSaveCols');
                    
                    // any additional messages?
                    /*if (isset($this->messages) && is_array($this->messages) && count($this->messages) > 0){

                        //echo '<div id="zbs-list-view-messages">';

                            foreach ($this->messages as $message){
                                // $message needs to match this func :)
                                echo zeroBSCRM_UI2_messageHTML($message[0],$message[1],$message[2],$message[3],$message[4]);
                            }

                        //echo '</div>';

                    } */

                    ?></div><?php

                // / prefilled errs
        }

        public function save_meta_box( $post_id, $post ) {
            global $wpdb;
            //save as pubslished
            if ( 'publish' != $post->post_status && 'auto-draft' != $post->post_status && $post->post_type == 'zerobs_invoice'){
                $wpdb->update( $wpdb->posts, array( 'post_status' => 'publish' ), array( 'ID' => $post->ID ) );
            }

            if( empty( $_POST['meta_box_ids'] ) ){ return; }
                      
            foreach( $_POST['meta_box_ids'] as $metabox_id ){
                if( !isset($_POST[ $metabox_id . '_nonce' ]) || ! wp_verify_nonce( $_POST[ $metabox_id . '_nonce' ], 'save_' . $metabox_id ) ){ continue; }
                #if( count( $_POST[ $metabox_id . '_fields' ] ) == 0 ){ continue; }
                if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){ continue; }
                if( $metabox_id == 'wpzbsci_itemdetails'  && $post->post_type == $this->postType){

                    //this ons is the actual processing and saving of data (shoe-horning it into DB1.0 table codes)
                    //this function is in /includes/controllers/ZeroBSCRM.Control.Invoices.php
                    // this also now creates hash as it goes
                    zeroBSCRM_save_invoice_data($post_id, $_POST);
                }
            }

            return $post;
        }
    
    
    }

    $zeroBS__MetaboxInvoice = new zeroBS__MetaboxInvoice( __FILE__ );


/* ======================================================
  Attach a file to Invoice Metabox
   ====================================================== */

function zeroBS__MetaboxFilesInvoice($post) {  
    //wp_nonce_field(plugin_basename(__FILE__), 'zbsc_invoice_attachment_nonce');
    /*$html = '<p class="description">';
    $html .= 'Upload your PDF here.';
    $html .= '</p>';
    $html .= '<input type="file" id="zbsc_file_attachment" name="zbsc_file_attachment" size="25">';
    echo $html; */

    $html = '';
    global $zbs;

    // wmod

            #} retrieve
            $zbsCustomerInvoiceFiles = get_post_meta($post->ID, 'zbs_customer_invoices', true);
            $zbsSendAttachments = get_post_meta($post->ID, 'zbs_inv_sendattachments', true);

    ?>
            <table class="form-table wh-metatab wptbp" id="wptbpMetaBoxMainItemInvs">

                <?php 

                #} Any existing
                if (is_array($zbsCustomerInvoiceFiles) && count($zbsCustomerInvoiceFiles) > 0){ 
                  ?><tr class="wh-large"><th><label><?php echo count($zbsCustomerInvoiceFiles).' Attachment:'; ?></label></th>
                            <td id="zbsFileWrapInvoices">
                                <?php $fileLineIndx = 1; foreach($zbsCustomerInvoiceFiles as $invoiceFile){
                                    
                                    $file = basename($invoiceFile['file']);
                                    // if in privatised system, ignore first hash in name
                                    if (isset($invoiceFileFile['priv'])){

                                        $file = substr($file,strpos($file, '-')+1);
                                    }

                                    echo '<div class="zbsFileLine" id="zbsFileLineInvoice'.$fileLineIndx.'"><a href="'.$invoiceFile['url'].'" target="_blank">'.$file.'</a> (<span class="zbsDelFile" data-delurl="'.$invoiceFile['url'].'"><i class="fa fa-trash"></i></span>)</div>';
                                    $fileLineIndx++;

                                } ?>
                            </td></tr><?php

                } ?>

                <?php #adapted from http://code.tutsplus.com/articles/attaching-files-to-your-posts-using-wordpress-custom-meta-boxes-part-1--wp-22291


                        wp_nonce_field(plugin_basename(__FILE__), 'zbsc_invoice_attachment_nonce');
                         
                        $html .= '<input type="file" id="zbsc_invoice_attachment" name="zbsc_invoice_attachment" size="25">';
                        $zbs_settings_slug = admin_url("admin.php?page=" . $zbs->slugs['settings']);
                        ?><tr class="wh-large"><th><label><?php _e("Attach Files","zero-bs-crm");?></label><div class="zbs-infobox"><?php _e("You can attach as many file types as you like. Supported file formats are","zero-bs-crm");?> <span class='zbs-file-types'><?php echo zeroBS_acceptableFileTypeListStr(); ?></span>. <?php _e("You can manage these in","zero-bs-crm");?> <a href="<?php echo $zbs_settings_slug; ?>" target="_blank"><?php _e("Settings","zero-bs-crm"); ?></a>. <?php _e("Use this for attaching things like a PDF version of the invoice or your Terms and Conditions","zero-bs-crm");?>.</div></th>
                            <td><?php
                        echo $html;

                ?></td></tr>

                <tr><td colspan="2" class="zbs-normal zbs-add-memo-trigger hide">
                    <span class="zbs-plus">+</span> <?php _e("Add memo to self", "zero-bs-crm"); ?>
               </td></tr>

                <tr><td colspan="2" class="zbs-normal zbs-memo-box hide">
                    <div class="zbs-memo"><?php _e("Memo", "zero-bs-crm"); ?></div>
                    <textarea class="zbs-memo-ta form-control" id="notes" name="notes" placeholder="<?php _e("Add memo to self (your recipient won't see this)","zero-bs-crm"); ?>"></textarea>
                    <div class='zbs-memo-hide'><?php _e("Hide","zero-bs-crm"); ?></div>
               </td></tr>


                <?php 

                    // optionally send with email as attachment?

                ?><tr class="">
                    <td colspan="2">
                        <table style="width:100%;border:0">
                            <tr>
                                <td style="width:50%">
                                    <label for="zbsc_sendattachments"><?php _e('Send as Attachments',"zero-bs-crm");?>:</label> <input type="checkbox" id="zbsc_sendattachments" name="zbsc_sendattachments" class="form-control" value="1"<?php if ($zbsSendAttachments == "1") echo ' checked="checked"'; ?> style="line-height: 1em;vertical-align: middle;display: inline-block;margin: 0;margin-top: -0.5em;" />
                                    <br /><?php _e('Optionally send a copy of the attached files along with any invoice emails sent.',"zero-bs-crm");?>                            
                                </td>
                                <td>
                                    <label><?php _e('Note:','zero-bs-crm'); ?></label><br />
                                    <div><em><?php _e("It is the user's responsibility to create an invoice that is compliant with local laws and regulations, including, but not limited to, the application of the correct tax rate(s)","zero-bs-crm");?></em></div>
                                </td>
                            </tr>
                        </table></td>
                    </tr>


            
            </table>
            <script type="text/javascript">

                var zbsInvoicesCurrentlyDeleting = false;
                <?php echo 'var zbscrmjs_secToken = \''.wp_create_nonce( "zbscrmjs-ajax-nonce" ).'\';'; ?>

                jQuery(function(){

                    jQuery('.zbsDelFile').on( 'click', function(){

                        if (!window.zbsInvoicesCurrentlyDeleting){

                            // blocking
                            window.zbsInvoicesCurrentlyDeleting = true;

                            var delUrl = jQuery(this).attr('data-delurl');
                            var lineIDtoRemove = jQuery(this).closest('.zbsFileLine').attr('id');

                            if (typeof delUrl != "undefined" && delUrl != ''){



                                  // postbag!
                                  var data = {
                                    'action': 'delFile',
                                    'zbsfType': 'invoices',
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

                                            jQuery('#zbsFileWrapInvoices').append('<div class="alert alert-error" style="margin-top:10px;"><strong>Error:</strong> Unable to delete this file.</div>');

                                            // Callback
                                            //if (typeof errorcb == "function") errorcb(response);
                                            //callback(response);


                                          }

                                        });

                            }

                            window.zbsInvoicesCurrentlyDeleting = false;

                        } // / blocking

                    });

                });


            </script><?php
}

add_action('save_post', 'zeroBSCRM_save_invoice_file_data');
function zeroBSCRM_save_invoice_file_data($id) {



    if(!empty($_FILES['zbsc_invoice_attachment']['name'])) {


    /* --- security verification --- */
    if(!wp_verify_nonce($_POST['zbsc_invoice_attachment_nonce'], plugin_basename(__FILE__))) {
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
    if (!zeroBSCRM_permsInvoices()){
        return $id;
    }
    /* - end security verification - */
        $supported_types = zeroBS_acceptableFileTypeMIMEArr(); //$supported_types = array('application/pdf');
        $arr_file_type = wp_check_filetype(basename($_FILES['zbsc_invoice_attachment']['name']));
        $uploaded_type = $arr_file_type['type'];

        if(in_array($uploaded_type, $supported_types) || (isset($supported_types['all']) && $supported_types['all'] == 1)) {
            $upload = wp_upload_bits($_FILES['zbsc_invoice_attachment']['name'], null, file_get_contents($_FILES['zbsc_invoice_attachment']['tmp_name']));
            if(isset($upload['error']) && $upload['error'] != 0) {
                wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
            } else {
                //update_post_meta($id, 'zbsc_invoice_attachment', $upload);

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
                                $zbsCustomerInvoiceFiles = get_post_meta($id,'zbs_customer_invoices', true);

                                if (is_array($zbsCustomerInvoiceFiles)){

                                    //add it
                                    $zbsCustomerInvoiceFiles[] = $upload;

                                } else {

                                    // first
                                    $zbsCustomerInvoiceFiles = array($upload);

                                }

                                update_post_meta($id, 'zbs_customer_invoices', $zbsCustomerInvoiceFiles);  
            }
        }
        else {
            wp_die("The file type that you've uploaded is not in an accepted file format.");
        }
    }
}

/* ======================================================
  / Attach a file to Invoice Metabox
   ====================================================== */










// FUNCS FROM MS INV 2.0, to be worked into metabox 3.0 :) 
function zeroBSCRM_save_invoice_data($post_id = -1, $data = array()){

    //pile of cr**p above again, but the below is what is storing, first one stores a hash for this postID (old meta)
    zeroBSCRM_ensureHashForPost($post_id);

    if($post_id > 0){

        //MS suggests we REMOVE this. This was in before the "Reference" on invoices. ID will now be the DAL3.0 table ID, and they can change reference.
        //Output wise, will let them control it via invoice templates (i.e. whether they want to show the invoice ID or just the Ref.)
        //Can use Alvaro "Auto Increment" type field in its place. 
        $invOffset          = zeroBSCRM_getInvoiceOffset();
        $allowInvNoChange   = zeroBSCRM_getSetting('invallowoverride');
        $invID = (int)$post_id+$invOffset; if ($allowInvNoChange && isset($_POST['zbsinvid']) && !empty($_POST['zbsinvid'])) $invID = (int)$_POST['zbsinvid'];
        update_post_meta($post_id,"zbsid",$invID);

        //send attachments setting (from separate meta box) - should be in DB3.0 
        $sendAsAttachments = -1; if (isset($_POST['zbsc_sendattachments']) && !empty($_POST['zbsc_sendattachments'])) $sendAsAttachments = 1;
        update_post_meta($post_id, 'zbs_inv_sendattachments', $sendAsAttachments); 

        //new way..  now not limited to 30 lines as now they are stored in [] type array in JS draw
        $zbsInvoiceLines = array();
        foreach($_POST['zbsli_itemname'] as $k => $v){
            $zbsInvoiceLines[$k]["zbsli_itemname"]      = sanitize_text_field($_POST['zbsli_itemname'][$k]);
            $zbsInvoiceLines[$k]["zbsli_des"]           = sanitize_text_field($_POST['zbsli_itemdes'][$k]);
            $zbsInvoiceLines[$k]["zbsli_quan"]          = sanitize_text_field($_POST['zbsli_quan'][$k]);
            $zbsInvoiceLines[$k]["zbsli_price"]         = sanitize_text_field($_POST['zbsli_price'][$k]);
            $zbsInvoiceLines[$k]["zbsli_tax"]           = sanitize_text_field($_POST['zbsli_tax'][$k]);
        }
        update_post_meta($post_id,"zbs_invoice_lineitems",$zbsInvoiceLines);

        //other items to update
        $zbsInvoiceHorQ = $_POST['invoice-customiser-type'];
        update_post_meta($post_id, 'zbsInvoiceHorQ', $zbsInvoiceHorQ);


        $zbsInvoiceTotals = array();
        $zbsInvoiceTotals["invoice_discount_total"] = isset($_POST["invoice_discount_total"]) ? $_POST["invoice_discount_total"] : 0;
        $zbsInvoiceTotals["invoice_discount_type"]  = isset($_POST["invoice_discount_type"])  ? $_POST["invoice_discount_type"] : "%";
        $zbsInvoiceTotals["invoice_postage_total"]  = isset($_POST["invoice_postage_total"])  ? $_POST["invoice_postage_total"] : 0;
        //the taxID for shipping (0 is no tax)
        $zbsInvoiceTotals["tax"] = isset($_POST["zbsli_tax_ship"]) ? $_POST["zbsli_tax_ship"] : 0;  

        update_post_meta($post_id,"zbs_invoice_totals",$zbsInvoiceTotals);

        $zbsInvoiceContact = (int)sanitize_text_field($_POST['zbs_invoice_contact']);
        $zbsInvoiceCompany = (int)sanitize_text_field($_POST['zbs_invoice_company']);

        //the company or contact id for the type-ahead (invoice to) field
        update_post_meta($post_id, 'zbs_customer_invoice_customer',$zbsInvoiceContact);
        update_post_meta($post_id, 'zbs_company_invoice_company', $zbsInvoiceCompany);

        //the invoice meta

        /*
            [zbscq_ref] => in_1DwMVLBy0i6Hd9ALwiYAhi4k
            [zbscq_due] => -1
        */
        $meta['status'] = sanitize_text_field($_POST['invoice_status']);
        $meta['logo']   = sanitize_text_field($_POST['zbsi_logo']);
        $meta['date']   = sanitize_text_field($_POST['zbsi_date']);
        $meta['due']    = sanitize_text_field($_POST['zbsi_due']);
        $meta['ref']    = sanitize_text_field($_POST['zbsi_ref']);

        /*

            legacy support for Pre Dal3, add 'val'

        */
        // ... js pass through :o
        // #TEMPNEEDSPHPFUNC (search for this hash for other refs to remove)
        // TEMP: WH added for MS on DAL3 work, 
        // ... this retrieves the total out of an inp for saving
        // SHOULD BE REPLACED with php variant of js total calc code here, later
        // ... smt like zeroBSCRM_invoicing_calcTotal($inv);
        $meta['val'] = 0; if (isset($_POST['zbs-inv-grand-total-store'])) $meta['val'] = (float)sanitize_text_field( $_POST['zbs-inv-grand-total-store'] );

        //the main "meta" from the data.
        update_post_meta($post_id, 'zbs_customer_invoice_meta', $meta);

        //print out the array of these (for WH data mapping) matches the above $invoiceObj data structure
        $invoice_data = array(
            'invoice_id'                   => $post_id,
            'invoice_custom_id'            => $invID,
            'status'                       => $meta['status'],
            'date'                         => $meta['date'],   
            'ref'                          => $meta['ref'],
            'due'                          => $meta['due'],
            'invoice_logo_url'             => $meta['logo'],
            'invoice_contact'              => $zbsInvoiceContact,
            'invoice_company'              => $zbsInvoiceCompany,
            'invoice_hours_or_quantity'    => $zbsInvoiceHorQ,
            'invoice_items'                => $zbsInvoiceLines,
            'totals'                       => $zbsInvoiceTotals,

            //the additional meta box to send attachments
            'send_attachments'             => $sendAsAttachments

            // 'new_invoice'                false runs from whether a new or a loaded invoice
            // bill'                        populated from invoice_contact or invoice_company now
            // 'hash'                       stored by function => zeroBSCRM_GetHashForPost($objID);
            // 'preview_link'               the hash of the invoice (for viewing without portal)

            // WH Q.. what about custom fields?

        );

        //for testing shizzle only :) 
        //zbs_write_log("new data in a nice array above is");
        //zbs_write_log($invoice_data);

    }
    

}


/*#} Currently not used. Started to get confusing. To chat through as part of v3.1+ (and Recurring Invoices work?)
 function zerBSCRM_invoice_admin_submenu(){
     ?>
    <div class="ui menu" id="invoice_menu_ui">
    <div class="ui simple dropdown link item">
        <span class="text"><?php _e("Manage Invoices","zero-bs-crm");?></span>
        <i class="dropdown icon"></i>
        <div class="menu">
            <div class="item"><?php _e("Manage Invoices","zero-bs-crm");?></div>
            <div class="item"><?php _e("Manage Recurring Invoices","zero-bs-crm");?></div>
        </div>
    </div>
    <a class="item">
        <?php _e("Create Invoice", "zero-bs-crm"); ?>
    </a>
    <a class="item">
        <?php _e("Invoice Items", "zero-bs-crm"); ?>
    </a>

    <div class="ui simple dropdown item">
        <span class="text"><?php _e("Settings","zero-bs-crm");?></span>
        <i class="dropdown icon"></i>
        <div class="menu">
            <div class="item"><?php _e("Invoice Settings","zero-bs-crm");?></div>
            <div class="item"><?php _e("Business Information","zero-bs-crm");?></div>
            <div class="item"><?php _e("Tax Information","zero-bs-crm");?></div>
            <div class="item"><?php _e("Templates","zero-bs-crm");?></div>
        </div>
    </div>

    <a class="item right">
        <i class='ui icon info circle'></i><?php _e("Help", "zero-bs-crm"); ?>
    </a>

    </div>
     <?php
 }  */
