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

   function zeroBSCRM_ExternalSourcesMetaboxSetup(){

        $zeroBS__ExternalSourceMetabox = new zeroBS__ExternalSourceMetabox( __FILE__ );

   }

   add_action( 'after_zerobscrm_settings_init','zeroBSCRM_ExternalSourcesMetaboxSetup');

/* ======================================================
   / Init Func
   ====================================================== */



/* ======================================================
  External Source Metabox
   ====================================================== */

    class zeroBS__ExternalSourceMetabox {

        static $instance;
        #private $packPerm;
        #private $pack;
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
            $this->postTypes = array('zerobs_company');  // zerobs_customer (not db2)
            add_action( 'add_meta_boxes', array( $this, 'initMetaBox' ) );

            #add_filter( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
        }

        public function initMetaBox(){

            // WH added as this was being hidden?
            // unhides for current user :) - this probs only needs to fire once? remove in due course (as move src elsewhere)
            zeroBSCRM_unhideMetaBox('zerobs_customer','wpzbscext_itemdetails_zerobs_customer');

            if (count($this->postTypes) > 0) foreach ($this->postTypes as $pt){

                #} pass an arr
                $callBackArr = array($this,$pt);

                add_meta_box(
                    'wpzbscext_itemdetails_'.$pt,
                    'External Sources',
                    array( $this, 'print_meta_box' ),
                    $pt,
                    'side',
                    'low',
                    $callBackArr
                );

            }

        }
        /*
        public function create_meta_box() {


            #} Don't share for new customers :)
            #if (isset($this->ID)){

                    add_meta_box(
                        'wpzbscext_itemdetails',
                        'External Source(s)',
                        array( $this, 'print_meta_box' ),
                        $this->postType,
                        'side',
                        'low'
                    );            

            #}
        }
        */
        public function print_meta_box( $post, $metabox ) {

                #} Post type
                $postType = ''; if (isset($metabox['args']) && isset($metabox['args'][1]) && !empty($metabox['args'][1])) $postType = $metabox['args'][1];

                #} Only load if is legit.
                if (in_array($postType,array('zerobs_customer','zerobs_company'))){

                    // type => id
                    $objType = -1; 
                    if ($postType == 'zerobs_customer') $objType = ZBS_TYPE_CONTACT;
                    if ($postType == 'zerobs_company') $objType = ZBS_TYPE_COMPANY;

                    // here we use a legacy fill-in func to get an oldstyle list
                    $zbsThisObjExternals = zeroBS_getExternalSourceLegacyList($post->ID,$objType);
                    
                    // old output :)
                    if (isset($zbsThisObjExternals) && is_array($zbsThisObjExternals) && count($zbsThisObjExternals) > 0){
                
                ?>
                    <input type="hidden" name="meta_box_ids[]" value="<?php echo $metabox['id']; ?>" />

                    <table class="form-table wh-metatab wptbp" id="wptbpMetaBoxExternalSource">
                        <tr>
                        <td>
                            <?php if (count($zbsThisObjExternals) > 0) foreach ($zbsThisObjExternals as $extKey => $extDeet){

                                #} Display a "source"
                                #} extDeet will be array('PayPal','usermail@gmail.com') or array('Another Source Name','123ID')
                                echo '<div class="zbsExternalSource">';

                                    // moved into func
                                    echo zeroBS_getExternalSourceTitle($extKey,$extDeet);

                                echo '</div>';

                            } ?>
                        </td></tr>
                    </table>


                <style type="text/css">
                </style>
                <script type="text/javascript">

                    jQuery(function(){

                    });


                </script>
                 
                <input type="hidden" name="<?php echo $metabox['id']; ?>_fields[]" value="subtitle_text" />


                <?php


                } else {

                    #} Gross hide :/

                    ?><style type="text/css">#wpzbscext_itemdetails_<?php echo $postType; ?> {display:none;}</style><?php

                }

            } // / only load if post type

        }

    }


/* ======================================================
  / External Source Metabox
   ====================================================== */