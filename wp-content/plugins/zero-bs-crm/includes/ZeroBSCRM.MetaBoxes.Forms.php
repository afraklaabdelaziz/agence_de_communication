<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 * V1.1.17
 *
 * Copyright 2020 Automattic
 *
 * Date: 13/09/16
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

   function zeroBSCRM_FormsMetaboxSetup(){

        $zeroBS__MetaboxFormFields = new zeroBS__MetaboxFormFields( __FILE__ );
        $zeroBS__MetaboxForm = new zeroBS__MetaboxForm( __FILE__ );

   }

   add_action( 'after_zerobscrm_settings_init','zeroBSCRM_FormsMetaboxSetup');

/* ======================================================
   / Init Func
   ====================================================== */




/* ======================================================
  Forms Metabox
   ====================================================== */

    class zeroBS__MetaboxFormFields {

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

            $this->postType = 'zerobs_form';
            #if (???) wp_die( sprintf( __( 'Cannot instantiate class: %1$s without pack', 'zero-bs-crm' ), __CLASS__ ) );

            add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );
            add_filter( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
        }

        public function create_meta_box() {

            #'wptbp'.$this->postType

            add_meta_box(
                'wpzbs_formfields',
                __('Form Language Labels',"zero-bs-crm"),
                array( $this, 'print_meta_box' ),
                $this->postType,
                'normal',
                'high'
            );
        }

        public function print_meta_box( $post, $metabox ) { ?>
            <?php 
                

                $zbsForm = get_post_meta($post->ID, 'zbs_form_field_meta', true);

                global $zbsFormFields;
                $fields = $zbsFormFields;
                ?>

                    <table class="form-table wh-metatab wptbp">
                    <?php
                    foreach ($fields as $fieldK => $fieldV){

                        #} Ignore no and date, dealt with above
                       switch ($fieldV[0]){

                            case 'text':

                                ?><tr class="wh-large"><th><label for="<?php echo $fieldK; ?>"><?php _e($fieldV[1],"zero-bs-crm"); ?>:</label></th>
                                <td>
                                    <input type="text" name="zbscf_<?php echo $fieldK; ?>" id="<?php echo $fieldK; ?>" class="form-control widetext" placeholder="<?php if (isset($fieldV[2])) _e($fieldV[2],"zero-bs-crm"); ?>" value="<?php if (isset($zbsForm[$fieldK])) echo $zbsForm[$fieldK]; ?>" />
                                </td></tr><?php

                                break;


                            case 'textarea':

                                ?><tr class="wh-large"><th><label for="<?php echo $fieldK; ?>"><?php _e($fieldV[1],"zero-bs-crm"); ?>:</label></th>
                                <td>
                                    <textarea name="zbscf_<?php echo $fieldK; ?>" id="<?php echo $fieldK; ?>" class="form-control" placeholder="<?php if (isset($fieldV[2])) _e($fieldV[2],"zero-bs-crm"); ?>"><?php if (isset($zbsForm[$fieldK])) echo zeroBSCRM_textExpose($zbsForm[$fieldK]); ?></textarea>
                                </td></tr><?php

                                break;


                        }



                    }


                    ?>

                    <!--
                    <tr class="wh-large"><th><label for="zbsci_fromquote">From Quote ID:</label></th>
                        <td>
                            <input type="text" name="zbsci_fromquote" id="zbsci_fromquote" class="form-control widetext" placeholder="e.g. 123" value="<?php if (isset($fromQuoteID)) echo $fromQuoteID; ?>" />
                        </td>
                    </tr>
                    -->
                
            </table>


            <div class="clear"></div>

                <input type="hidden" name="meta_box_ids[]" value="<?php echo $metabox['id']; ?>" />
                <?php wp_nonce_field( 'save_' . $metabox['id'], $metabox['id'] . '_nonce' ); ?>
                <input type="hidden" name="meta_box_ids[]" value="<?php echo $metabox['id']; ?>" />


            <?php
        }

        public function save_meta_box( $post_id, $post ) {
            if( empty( $_POST['meta_box_ids'] ) ){ return; }
            foreach( $_POST['meta_box_ids'] as $metabox_id ){
                if( !isset($_POST[ $metabox_id . '_nonce' ]) || ! wp_verify_nonce( $_POST[ $metabox_id . '_nonce' ], 'save_' . $metabox_id ) ){ continue; }
                #if( count( $_POST[ $metabox_id . '_fields' ] ) == 0 ){ continue; }
                if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){ continue; }

                if( $metabox_id == 'wpzbs_formfields'  && $post->post_type == $this->postType){

                    global $zbsFormFields;
                    foreach ($zbsFormFields as $fK => $fV){
                        $zbsFormFieldMeta[$fK] = '';
                        if (isset($_POST['zbscf_'.$fK])) {
                            switch ($fV[0]){
                                case 'text':
                                    $zbsFormFieldMeta[$fK] = zeroBSCRM_textProcess($_POST['zbscf_'.$fK]);
                                    break;
                                case 'textarea':
                                    $zbsFormFieldMeta[$fK] = zeroBSCRM_textProcess($_POST['zbscf_'.$fK]);
                                    break;
                                default:
                                    $zbsFormFieldMeta[$fK] = sanitize_text_field($_POST['zbscf_'.$fK]);
                                    break;
                            }
                        }
                    }
                    update_post_meta($post_id, 'zbs_form_field_meta', $zbsFormFieldMeta);
                }
            }

            return $post;
        }
    }



    class zeroBS__MetaboxForm {

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

            $this->postType = 'zerobs_form';
            #if (???) wp_die( sprintf( __( 'Cannot instantiate class: %1$s without pack', 'zero-bs-crm' ), __CLASS__ ) );

            add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );
            add_filter( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
        }

        public function create_meta_box() {

            #'wptbp'.$this->postType

            add_meta_box(
                'wpzbs_formsettings',
                __('Form Settings',"zero-bs-crm"),
                array( $this, 'print_meta_box' ),
                $this->postType,
                'normal',
                'high'
            );
        }

        public function print_meta_box( $post, $metabox ) { ?>
            <?php 
            //pre-processing
             $formcss = ZEROBSCRM_URL . 'css/ZeroBSCRM.admin.frontform.css';
             $formjs = ZEROBSCRM_URL . 'js/ZeroBSCRM.leadform.js?ver=1.17';
             // 29/10/19 rewritten into template system + proper endpoint.
             //$formtemp = ZEROBSCRM_WILDURL . 'form-templates/';
             $formRoot = get_site_url().'/crmforms';

             $zbsfs = get_post_meta($post->ID,'zbs_form_style',true);
            ?>
            <script>
                jQuery(function(){

                    var showGetMore = false;
                    <?php ##WLREMOVE
                    if (!zeroBSCRM_hasPaidExtensionActivated()) echo 'var showGetMore = true;';
                    ##/WLREMOVE ?>
                    var get_more = '<?php _e('Need More Fields?',"zero-bs-crm");?>';
                    var get_more_desc = '<?php _e('Jetpack CRM forms cover simple use contact and subscription forms, but if you need more we suggest using a form plugin like Contact Form 7 or Gravity Forms:',"zero-bs-crm");?> <a href="https://jetpackcrm.com/feature/forms/#benefit" target="_blank"><?php _e('See Options','zero-bs-crm'); ?></a>';
                    var get_more_wrap = '<div id="form-upsell"><h1 class="welcomeh1">'+get_more+'</h1><p style="text-align:center;padding: 2em;font-size: 1.2em;padding-top: 0;">' + get_more_desc + '</p></div>';
                    if (!showGetMore) get_more_wrap = '';

                    var embed_title = '<?php _e('Embed Code',"zero-bs-crm");?>';
                    var embed_msg = '<?php _e('Use the code below to embed this form on another site',"zero-bs-crm");?>';

                    jQuery('#wpzbs_formsettings').after('<div id="form-embed"><h1 class="welcomeh1">'+embed_title+'</h1><h3 class="welcomeh3">'+embed_msg+'</h3><pre id="zbs-form-pre"></pre></div>'+get_more_wrap);
                    
                    zbsembed = jQuery('.embed-selected').html();
                    jQuery('#zbs-form-pre').html(zbsembed);

                    //can move to a seperate script at some point....
                    jQuery('.choice').off("click").on("click",function(e){
                        jQuery('#zbs-form-pre').html(''); //clear out the HTML
                        var zbsf_pid = jQuery(this).data('pid');
                        var zbsf_style = jQuery(this).data('style');

                        jQuery('#zbs_form_style_post').val(zbsf_style);  
                        var zbsf_html = jQuery('#'+zbsf_style+'_html_form .zbs_form_content_wrap').html();  //replace with proper HTML form elements
                        var zbsf_html_encoded =  zbsf_html;

                        jQuery('.choice').removeClass('selected');
                        jQuery(this).addClass('selected');
                        jQuery('.zbs_shortcode_message').show();
                        jQuery('.shorty').html('[jetpackcrm_form id="'+zbsf_pid+'" style="'+zbsf_style+'"]').show();

                        jQuery('#zbs-form-pre').html(zbsf_html_encoded);
                    });

                });


            </script>

            <?php
            $zbsForm = get_post_meta($post->ID, 'zbs_form_field_meta', true);
            ?>


            <div class='ZBSencodedJS hide'>&lt;script type='text/javascript' src='<?php echo $formjs; ?>'&gt;&lt;/script&gt;
            </div>
            <?php 

                #} Get form style (default = simple)
                $zbsfs = get_post_meta($post->ID,'zbs_form_style',true);
                if (empty($zbsfs)) $zbsfs = 'simple';

            ?>
            <input type="hidden" name="zbs_form_style_post" id="zbs_form_style_post" value="<?php echo $zbsfs; ?>" />

            <h1 class="welcomeh1"><?php _e('Welcome to Jetpack CRM Form Creator',"zero-bs-crm");?></h1>
            <h3 class="welcomeh3"><?php _e("Choose your style for the form you wish to embed (click to choose)","zero-bs-crm");?></h3>
            <p class="zbs_msg"><?php _e('Make sure to save the form before using the shortcode',"zero-bs-crm");?>.</p>
            <div class="zbs_shortcode_message">
            <p><?php _e('You can embed this form on <b>this website</b> using the shortcode below (choose your style first). To embed the form on a seperate website use the embed code in the "Embed Code" box below.',"zero-bs-crm");?></p>
            <p class="shorty">[jetpackcrm_form id="<?php echo $post->ID; ?>" style="<?php  echo $zbsfs; ?>"]</p>
            </div>

            <div id="form-chooser">
                <!-- 3 styles for now - naked, simple and content grab -->
                <div class="third" id="naked-form">
                    <div class="naked choice <?php if($zbsfs == 'naked'){ echo 'selected';} ?>" data-pid="<?php echo $post->ID; ?>" data-style="naked">
                        <div class="blobby" style="margin-bottom:13px;">
                            <p>Lorem Ipsum Text here</p>
                            <p>Lorem Ipsum <span class="br">s</span> Text <span class="br">s</span> here</p>
                            <p>Lorem Ipsum Text here</p>
                            <p>Lorem Ipsum Text here</p>
                            <p>Lorem Ipsum <span class="br">s</span> Text <span class="br">s</span> here</p>
                            <p>Lorem Ipsum Text here</p>
                        </div>
                        <div class="content">
                             <div class="form-wrapper">
                                <div class="input"><?php if(!empty($zbsForm['fname'])){ echo $zbsForm['fname']; }else{ echo "First Name"; } ?></div><div class="input"><?php if(!empty($zbsForm['email'])){ echo $zbsForm['email']; }else{ _e("Email","zero-bs-crm"); } ?></div><div class="send"><?php if(!empty($zbsForm['submit'])){ echo $zbsForm['submit']; }else{ echo "Submit"; } ?></div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <div class="blobby">
                            <p>Lorem Ipsum Text here</p>
                            <p>Lorem Ipsum <span class="br">s</span> Text Lorem Ipsum m Ipsum <span class="br">s</span> here</p>
                            <p>Lorem Ipsum Text here</p>
                            <p>Lorem Ipsum Text here</p>
                            <p>Lorem Ipsum <span class="br">s</span> Text <span class="br">s</span> here</p>
                            <p>Lorem Ipsum Text here</p>
                        </div>
                    </div>
                    <div class="caption"><?php _e("Naked Style","zero-bs-crm");?></div>
                                    <div id="naked_html_form" class="hide">
                        <div id="zbs_form_css" data-css="<?php echo $formcss; ?>"></div>
                        <div id="zbs_form_js" data-js="<?php echo $formjs; ?>"></div>
                        <div id="zbs_form_action" data-zbsformaction="<?php echo esc_url( admin_url('admin-post.php') ); ?>"></div>
                        
    <div class='zbs_form_content_wrap <?php if($zbsfs == 'naked'){ echo 'embed-selected';} ?>'>&lt;iframe src='<?php echo $formRoot; ?>/naked/?fid=<?php echo $post->ID; ?>' height='200px' width='700px' style='border:0px!important'&gt;&lt;/iframe&gt;
    </div> <!-- end form content grab -->
                    </div>
                </div>

                <div class="third" id="cgrab-form">
                    <div class="cgrab choice <?php if($zbsfs == 'cgrab'){ echo 'selected';} ?>" data-pid="<?php echo $post->ID; ?>" data-style="cgrab">
                        <div class="blobby">
                            <p>Lorem Ipsum Text here</p>
                        </div>
                        <div class="content">
                            <h1><?php if(!empty($zbsForm['header'])){ echo $zbsForm['header']; }else{ echo "Want to find out more?"; } ?></h1>
                            <h3><?php if(!empty($zbsForm['subheader'])){ echo $zbsForm['subheader']; }else{ echo "Drop us a line. We follow up on all contacts"; } ?></h3>
                            <div class="form-wrapper">
                                <div class="input"><?php if(!empty($zbsForm['fname'])){ echo $zbsForm['fname']; }else{ echo "First Name"; } ?></div>
                                <div class="input"><?php if(!empty($zbsForm['lname'])){ echo $zbsForm['lname']; }else{ echo "Last Name"; } ?></div>
                                <div class="input"><?php if(!empty($zbsForm['email'])){ echo $zbsForm['email']; }else{ echo "Email"; } ?></div>
                                <div class="textarea"><?php if(!empty($zbsForm['notes'])){ echo $zbsForm['notes']; }else{ echo "Your Message"; } ?></div>
                                <div class="send"><?php if(!empty($zbsForm['submit'])){ echo $zbsForm['submit']; }else{ echo "Submit"; } ?></div>
                            </div>
                            <div class="clear"></div>
                            <div class="trailer"><?php if(!empty($zbsForm['spam'])){ echo $zbsForm['spam']; }else{ echo "We will not send you spam. Our team will be in touch within 24 to 48 hours Mon-Fri (but often much quicker)"; } ?></div>
                        </div>
                        <div class="clear"></div>
                        <div class="blobby">
                            <p>Lorem Ipsum <span class="br">s</span> Text Lorem Ipsum m Ipsum <span class="br">s</span> here</p>
                        </div>
                    </div>
                    <div class="caption"><?php _e("Content Grab","zero-bs-crm");?></div>
                    <div id="cgrab_html_form" class="hide">
                        <div id="zbs_form_css" data-css="<?php echo $formcss; ?>"></div>
                        <div id="zbs_form_js" data-js="<?php echo $formjs; ?>"></div>
                        <div id="zbs_form_action" data-zbsformaction=""></div>
                        
    <div class='zbs_form_content_wrap <?php if($zbsfs == 'cgrab'){ echo 'embed-selected';} ?>'>&lt;iframe src='<?php echo $formRoot; ?>/content/?fid=<?php echo $post->ID; ?>' height='700px' width='700px' style='border:0px!important'&gt;&lt;/iframe&gt;
    </div> <!-- end form content grab -->
                    </div>


                </div>


                <div class="third" id="simple-form">
                    <div class="simple choice <?php if($zbsfs == 'simple'){ echo 'selected';} ?>" data-pid="<?php echo $post->ID; ?>" data-style="simple">
                        <div class="blobby">
                            <p>Lorem Ipsum Text here</p>
                            <p>Lorem Ipsum <span class="br">s</span> Text <span class="br">s</span> here</p>
                            <p>Lorem Ipsum Text here</p>
                        </div>
                        <div class="content">
                            <h1><?php if(!empty($zbsForm['header'])){ echo $zbsForm['header']; }else{ echo "Want to find out more?"; } ?></h1>
                            <h3><?php if(!empty($zbsForm['subheader'])){ echo $zbsForm['subheader']; }else{ echo "Drop us a line. We follow up on all contacts"; } ?></h3>
                            <div class="form-wrapper">
                                <div class="input"><?php if(!empty($zbsForm['email'])){ echo $zbsForm['email']; }else{ echo "Email"; } ?></div><div class="send"><?php if(!empty($zbsForm['submit'])){ echo $zbsForm['submit']; }else{ echo "Submit"; } ?></div>
                            </div>
                            <div class="clear"></div>
                            <div class="trailer"><?php if(!empty($zbsForm['spam'])){ echo $zbsForm['spam']; }else{ echo "We will not send you spam. Our team will be in touch within 24 to 48 hours Mon-Fri (but often much quicker)"; } ?></div>
                        </div>
                        <div class="clear"></div>
                        <div class="blobby">
                            <p>Lorem Ipsum Text here</p>
                            <p>Lorem Ipsum <span class="br">s</span> Text Lorem Ipsum m Ipsum <span class="br">s</span> here</p>
                            <p>Lorem Ipsum Text here</p>
                        </div>
                    </div>
                    <div class="caption"><?php _e("Simple Style","zero-bs-crm");?></div>
                    <div id="simple_html_form" class="hide">
                        <div id="zbs_form_css" data-css="<?php echo $formcss; ?>"></div>
                        <div id="zbs_form_js" data-js="<?php echo $formjs; ?>"></div>
                        <div id="zbs_form_action" data-zbsformaction="<?php echo esc_url( admin_url('admin-post.php') ); ?>"></div>
                        
    <div class='zbs_form_content_wrap <?php if($zbsfs == 'simple'){ echo 'embed-selected';} ?>'>&lt;iframe src='<?php echo $formRoot; ?>/simple/?fid=<?php echo $post->ID; ?>' height='300px' width='700px' style='border:0px!important'&gt;&lt;/iframe&gt;
    </div> <!-- end form content grab -->
                    </div>
                </div>
      



            </div>


            <div class="clear"></div>

                <input type="hidden" name="meta_box_ids[]" value="<?php echo $metabox['id']; ?>" />
                <?php wp_nonce_field( 'save_' . $metabox['id'], $metabox['id'] . '_nonce' ); ?>


                        <input type="hidden" name="meta_box_ids[]" value="<?php echo $metabox['id']; ?>" />


            <?php
        }

        public function save_meta_box( $post_id, $post ) {
            if( empty( $_POST['meta_box_ids'] ) ){ return; }
            foreach( $_POST['meta_box_ids'] as $metabox_id ){
                if( !isset($_POST[ $metabox_id . '_nonce' ]) || ! wp_verify_nonce( $_POST[ $metabox_id . '_nonce' ], 'save_' . $metabox_id ) ){ continue; }
                #if( count( $_POST[ $metabox_id . '_fields' ] ) == 0 ){ continue; }
                if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){ continue; }

                if( $metabox_id == 'wpzbs_formsettings'  && $post->post_type == $this->postType){

                    //save down the settings...
                    $zbfs = sanitize_text_field($_POST['zbs_form_style_post']);
                    update_post_meta($post->ID,'zbs_form_style', $zbfs);
                    $zbs_form_conv = get_post_meta($post->ID, 'zbs_form_conversions', true);
                    $zbs_form_views = get_post_meta($post->ID, 'zbs_form_views', true);
                    if($zbs_form_conv == ''){
                        update_post_meta($post->ID,'zbs_form_conversions',0);
                    }
                    if($zbs_form_views == ''){
                        update_post_meta($post->ID,'zbs_form_views',0);
                    }
                }
            }

            return $post;
        }
    }


/* ======================================================
   / Forms Metabox
   ====================================================== */



    #} Mark as included :)
    define('ZBSCRM_INC_FORMSMB',true);