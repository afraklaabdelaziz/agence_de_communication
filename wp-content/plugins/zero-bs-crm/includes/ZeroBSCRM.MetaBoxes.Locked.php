<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 * V2.52+
 *
 * Copyright 2020 Automattic
 *
 * Date: 05/03/18
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

   function zeroBSCRM_LockMetaboxSetup(){

        $zeroBS__Metabox_Lock = new zeroBS__Metabox_Lock( __FILE__ );

   }

   add_action( 'after_zerobscrm_settings_init','zeroBSCRM_LockMetaboxSetup');

/* ======================================================
   / Init Func
   ====================================================== */

/* ======================================================
  LOCKED (v1) Metabox
   ====================================================== */

    class zeroBS__Metabox_Lock {

        static $instance;
        private $postTypes;

        public function __construct( $plugin_file, $typesToLock=array('zerobs_customer') ) {
            self::$instance = $this;
            $this->postTypes = $typesToLock;
            add_action( 'add_meta_boxes', array( $this, 'initMetaBox' ) );
        }

        public function initMetaBox(){

            if (count($this->postTypes) > 0) foreach ($this->postTypes as $pt){

                #} pass an arr
                $callBackArr = array($this,$pt);

                add_meta_box(
                    'wpzbsc_lockdetails_'.$pt,
                    __('Locked Contact',"zero-bs-crm"),
                    array( $this, 'print_meta_box' ),
                    $pt,
                    'normal',
                    'high',
                    $callBackArr
                );

            }

        }

        public function print_meta_box( $post, $metabox ) {

            #} Display locked msg + hide EVERYTHING else :)

            global $zbs;

            ?><div id="zbs-lockout" class="ui modal">
                <div class="content">
                <?php 
                    echo zeroBSCRM_UI2_messageHTML('large info',__('Contact Database Update Needed',"zero-bs-crm"),__('Your contact information needs an update to work with new database improvements, you will not be able to edit contact information until your contact database has been migrated.',"zero-bs-crm"),'disabled warning sign','zbsNope'); 
                ?></div><div class="actions"><?php

                if (current_user_can( 'manage_options' )){

                        
                    ?><a href="<?php echo admin_url('admin.php?page='.$zbs->slugs['migratedb2contacts']); ?>" class="ui button large blue"><?php _e('Go to Update',"zero-bs-crm"); ?></a><?php

                }
                ?><a href="<?php echo admin_url('admin.php?page='.$zbs->slugs['managecontacts']); ?>" class="ui button large teal"><?php _e('Back to Contact List',"zero-bs-crm"); ?></a><?php
                ?></div>
            </div>
            <style>
                #postbox-container-1, #postbox-container-2 { display:none;}
            </style>
            <script type="text/javascript">
            jQuery(function(){
                jQuery('#zbs-lockout').modal({closable:false}).modal('show').modal('refresh');

            });
            </script><?php


        }

    }

/* ======================================================
  / Lock v1 Metabox
   ====================================================== */




    #} Mark as included :)
    define('ZBSCRM_INC_LOCKMB',true);