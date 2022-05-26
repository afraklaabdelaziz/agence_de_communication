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

   function zeroBSCRM_EventsMetaboxSetup(){

        $zeroBS__EventMetabox = new zeroBS__EventMetabox( __FILE__ );
   }

   add_action( 'after_zerobscrm_settings_init','zeroBSCRM_EventsMetaboxSetup');

/* ======================================================
   / Init Func
   ====================================================== */


/* ======================================================
  Event Metabox
   ====================================================== */

    class zeroBS__EventMetabox {

        static $instance;
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
            $this->postTypes = array('zerobs_event'); 
            $this->postType = 'zerobs_event';        
            add_action( 'add_meta_boxes', array( $this, 'initMetaBox' ) );
            add_filter( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
        }

        public function initMetaBox(){

            if (count($this->postTypes) > 0) foreach ($this->postTypes as $pt){

                #} pass an arr
                $callBackArr = array($this,$pt);

                add_meta_box(
                    'wpzbs_eventdetails_'.$pt,
                    __('Task Information',"zero-bs-crm"),
                    array( $this, 'print_meta_box' ),
                    $pt,
                    'normal',
                    'high',
                    $callBackArr
                );

            }

            ##WLREMOVE 

             /*    add_meta_box(
                    'event_manager_pro',
                    __('Task Scheduler Feedback',"zero-bs-crm"),
                    array( $this, 'print_feedback_box' ),
                    'zerobs_event',
                    'side',
                    'high'
                );

            add_meta_box(
                    'event_manager_notifications',
                    __('Task Actions',"zero-bs-crm"),
                    array( $this, 'print_actions_box' ),
                    'zerobs_event',
                    'side',
                    'high'
                );

            */

            ##/WLREMOVE 

        }

       public function print_actions_box($post, $metabox){

            // load this way JIRA-ZBS-466
            $zbsEventActions = array('notify'=>-1,'complete'=>-1);
            $zbsEventActionsLoaded = get_post_meta($post->ID, 'zbs_event_actions', true);
            if (is_array($zbsEventActionsLoaded) && isset($zbsEventActionsLoaded['notify'])) $zbsEventActions = $zbsEventActionsLoaded; 

            ?>
            <style>
                .in-pro{
                    color: #ddd;
                }
                .field-group{
                    margin-bottom:20px;
                }
                .field-group .completed{
                    margin-left: 8px;
                    margin-top: 3px;
                    position: absolute;
                }
                .help{
                    font-size:11px;
                }
            </style>
              <label><b><?php _e("Notify CRM User:","zero-bs-crm");?></b>
                <?php ##WLREMOVE 
                ?><a class='help' href="<?php echo $zbs->urls['kbcat_cal']; ?>" target="_blank"><?php _e("Need help?","zero-bs-crm");?></a><?php 
                ##/WLREMOVE ?>
              </label><br>
              <div class='field-group'>
                  <span><input type="radio" class="form-control" name="notify_crm" value="0"<?php if($zbsEventActions['notify'] == 0){ echo ' checked="checked"'; } ?> id="zbs-task-opt-never"> <label for="zbs-task-opt-never"><?php _e("Never","zero-bs-crm");?></label></span><br>
                  <span><input type="radio" class="form-control" name="notify_crm" value="24"<?php if($zbsEventActions['notify'] == 24){ echo ' checked="checked"'; } ?> id="zbs-task-opt-24"> <label for="zbs-task-opt-24"><?php _e("24 Hours before","zero-bs-crm");?></label></span><br>
                  <span class='in-pro'><input type="radio" class="form-control disabled" disabled="disabled" name="notify_crm" value="0" id="zbs-task-opt-12"> <label for="zbs-task-opt-12"><?php _e("12 Hours before","zero-bs-crm");?></label></span><br>
                  <span class='in-pro'><input type="radio" class="form-control disabled" disabled="disabled" name="notify_crm" value="0" id="zbs-task-opt-1"> <label for="zbs-task-opt-1"><?php _e("1 Hour before","zero-bs-crm");?></label></span><br>
              </div>
              <div class='field-group'>
                <label><b><?php _e("Mark Complete:","zero-bs-crm"); ?></b>
                  <?php ##WLREMOVE 
                  ?><a class='help' href="<?php echo $zbs->urls['kbcat_cal']; ?>" target="_blank"><?php _e("Need help?","zero-bs-crm");?></a><?php 
                  ##/WLREMOVE ?>
                </label><br>
                <input type="checkbox" name="complete_crm" value="1" id="zbs-task-opt-completed"<?php if($zbsEventActions['complete'] == 1){ echo ' checked="checked"'; } ?>><span class='completed'><label for="zbs-task-opt-completed"><?php _e('Completed',"zero-bs-crm"); ?></label></span><br>
              </div>

              <input type="hidden" name="<?php echo $metabox['id']; ?>_fields[]" value="subtitle_text" />

            <?php
       }



        public function print_feedback_box($post,$metabox){

                $upTitle = __('Do you like this?',"zero-bs-crm");
                $upDesc = __('Do you like this feature? Let us know.',"zero-bs-crm");
                $upButton = __('Send Feedback',"zero-bs-crm");
                $upTarget = "mailto:hello@jetpackcrm.com?subject='Events%20Manager%20Feedback'";

                echo zeroBSCRM_UI2_squareFeedbackUpsell($upTitle,$upDesc,$upButton,$upTarget); 
            
        }



public function print_meta_box( $post, $metabox ) {

    #} Post type
    $postType = ''; if (isset($metabox['args']) && isset($metabox['args'][1]) && !empty($metabox['args'][1])) $postType = $metabox['args'][1];

    #} Only load if is legit.
    if (in_array($postType,array('zerobs_event'))){

        $zbsEventMeta['notes'] = '';

        $zbsEventMeta = get_post_meta($post->ID, 'zbs_event_meta', true);   
        if (isset($zbsEventMeta) && is_array($zbsEventMeta)) 
            $zbsCustomerID = $zbsEventMeta['customer']; 
        else
            $zbsCustomerID = -1;

    ?>

    <style type="text/css">
            #screen-options-link-wrap, #minor-publishing-actions, #misc-publishing-actions {
            display:none !important;
        }
    </style>

    <?php  
        echo zeroBSCRM_task_addEdit($post->ID);  
        if (gettype($zbsEventMeta) != "array") echo '<input type="hidden" name="zbscrm_newevent" value="1" />';
    ?>

    <input type="hidden" name="meta_box_ids[]" value="<?php echo $metabox['id']; ?>" />
    <?php wp_nonce_field( 'save_' . $metabox['id'], $metabox['id'] . '_nonce' ); ?>     
    <input type="hidden" name="<?php echo $metabox['id']; ?>_fields[]" value="subtitle_text" />

<?php }

} 



        public function save_meta_box( $post_id, $post ) {
            if( empty( $_POST['meta_box_ids'] ) ){ return; }
            foreach( $_POST['meta_box_ids'] as $metabox_id ){
                if (!isset($_POST[ $metabox_id . '_nonce' ]) || ! wp_verify_nonce( $_POST[ $metabox_id . '_nonce' ], 'save_' . $metabox_id ) ){ continue; }
                #if( count( $_POST[ $metabox_id . '_fields' ] ) == 0 ){ continue; }
                if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){ continue; }

                if( $metabox_id == 'wpzbs_eventdetails_zerobs_event'  && $post->post_type == $this->postType){

                    // WH note: all this belongs in DAL eventually
                    $zbsEventMeta = array();
            
                    $start_d = date('m/d/Y H') . ":00:00";
                    $end_d =  date('m/d/Y H') . ":00:00";
                    $zbsEventMeta['from'] = $start_d; if (isset($_POST['zbs_event_from'])) $zbsEventMeta['from']  =  sanitize_text_field($_POST['zbs_event_from']);
                    $zbsEventMeta['to'] =$end_d; if (isset($_POST['zbs_event_to'])) $zbsEventMeta['to']     = sanitize_text_field($_POST['zbs_event_to']);

                    $zbsEventMeta['notes'] = ''; if (isset($_POST['zbs_event_notes'])) $zbsEventMeta['notes']  = zeroBSCRM_textProcess($_POST['zbs_event_notes']);
                    $zbsEventMeta['title'] = ''; if (isset($_POST['event_post_title'])) $zbsEventMeta['title']  = zeroBSCRM_textProcess($_POST['event_post_title']);
                    $zbsEventMeta['customer'] = ''; if (isset($_POST['zbsci_customer'])) $zbsEventMeta['customer']   = (int)sanitize_text_field($_POST['zbsci_customer']);

                    $zbsEventMeta['company'] = ''; if (isset($_POST['zbsci_company'])) $zbsEventMeta['company']   = (int)sanitize_text_field($_POST['zbsci_company']);


                    $zbsEventMeta['showoncal'] = false; if (isset($_POST['zbs_show_on_calendar'])) $zbsEventMeta['showoncal']   = sanitize_text_field($_POST['zbs_show_on_calendar']);
                    $zbsEventMeta['notify_crm'] = false; if (isset($_POST['zbs_remind_task_24'])) $zbsEventMeta['notify_crm']   = sanitize_text_field($_POST['zbs_remind_task_24']);
                    $zbsEventMeta['showonportal'] = false; if (isset($_POST['zbs_show_on_portal'])) $zbsEventMeta['showonportal']   = sanitize_text_field($_POST['zbs_show_on_portal']);
                

                    update_post_meta($post_id, 'zbs_event_meta', $zbsEventMeta);
                    zeroBS_setOwner($post_id, (int)sanitize_text_field($_POST['zerobscrm-owner']));

                   
                   
                    $zbsEventActions['complete'] = -1; if (isset($_POST['complete_crm'])) $zbsEventActions['complete']     =  (int)sanitize_text_field($_POST['complete_crm']);
               
                    update_post_meta($post_id, 'zbs_event_actions', $zbsEventActions);

                    // WH removed. use 'zbs_update_event' and 'zbs_new_event' do_action('zbs_task_after_update', $post_id);

                    // IA
                    if (isset($_POST['zbscrm_newevent'])){

                        // first save - new event

                            #} Add to automator
                            zeroBSCRM_FireInternalAutomator('event.new',array(
                                'id'=>$post_id,
                                'eventMeta'=>$zbsEventMeta,
                                'againstid' => $zbsEventMeta['customer'],
                                'automatorpassthrough'=>false #} This passes through any custom log titles or whatever into the Internal automator recipe.
                            ));

                            //need them here too (since this is called by the UI)
                            do_action('zbs-event-added', $zbsEventMeta, $post_id);


                    } else {

                        // update 

                            #} Add to automator
                            zeroBSCRM_FireInternalAutomator('event.update',array(
                                'id'=>$post_id,
                                'eventMeta'=>$zbsEventMeta,
                                'againstid' => $zbsEventMeta['customer'],
                                'automatorpassthrough'=>false #} This passes through any custom log titles or whatever into the Internal automator recipe.
                            ));

                            //need them here too (since this is called by the UI)
                            do_action('zbs-event-updated', $zbsEventMeta, $post_id);

                    }

                }

                if( $metabox_id == 'event_manager_notifications'  && $post->post_type == $this->postType){


                }


            }

            return $post;
        }


    }


/* ======================================================
  / External Source Metabox
   ====================================================== */