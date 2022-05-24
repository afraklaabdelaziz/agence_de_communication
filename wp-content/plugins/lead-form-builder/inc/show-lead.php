<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
Class LFB_Show_Leads {


function expanded_alowed_tags() {
    $allowed = wp_kses_allowed_html( 'post' );

    // form fields - input
    $allowed['a'] = array(
        'href' => array(),
        'class'    => array(),
        'onclick'  => array(),
    );
// form fields - input
    $allowed['input'] = array(
        'class' => array(),
        'id'    => array(),
        'name'  => array(),
        'value' => array(),
        'type'  => array(),
        'onclick' => array(),
    );


        $allowed['option'] = array(
            'value'    => array(),
            'selected'   => array(),
        );

    return $allowed;
}



    function lfb_show_form_leads() {
        global $wpdb;
        $option_form = '';
        $first_form=0;
        $th_save_db = new LFB_SAVE_DB($wpdb);
        $table_name = LFB_FORM_FIELD_TBL;
        $prepare_16 = $wpdb->prepare("SELECT * FROM $table_name WHERE form_status = %s ORDER BY id DESC ",'ACTIVE');
        $posts = $th_save_db->lfb_get_form_content($prepare_16);
        if (!empty($posts)) {
            foreach ($posts as $results) {
                $first_form++;
                $form_title = $results->form_title;
                $form_id = $results->id;
                if($first_form==1){
                $first_form_id = $results->id;
                if (get_option('lf-remember-me-show-lead') !== false ) {
                $first_form_id = get_option('lf-remember-me-show-lead');
                }
                }
                $option_form .= '<option ' . ($first_form_id == $form_id ? 'selected="selected"' : "" ) . ' value=' . $form_id . '>' . $form_title . '</option>';
            }
        }
        $rem_nonce = wp_create_nonce( 'rem-nonce' );

        include_once( plugin_dir_path(__FILE__) . 'header.php' );

        echo '<div class="wrap"><div class="inside"><div class="card"><table class="form-table"><tbody><tr><th scope="row">
<label for="select_form_lead">'.esc_html__('Select From','lead-form-builder').'</label></th>
<td><select name="select_form_lead" id="select_form_lead">' . wp_kses($option_form,$this->expanded_alowed_tags()) . '</select>
<td><input rem_nonce = "'.$rem_nonce.'" type="button" value="'.esc_html__('Remember this form','lead-form-builder').'" onclick="remember_this_form_id();" id="remember_this_form_id"></td>
</tr><tr><td><div id="remember_this_message" ></div></td></tr></tbody></table></div></div></div><div class="wrap" id="form-leads-show">';
$this->lfb_show_leads_first_form($first_form_id);
echo '</div>';
    }

function lfb_show_leads_first_form($form_id){

        $start = 0;

        $th_save_db = new LFB_SAVE_DB();
        $getArray =  $th_save_db->lfb_get_all_view_leads_db($form_id,$start);
        $nonce = wp_create_nonce( 'lfb-nonce-rm' );

        $posts          = $getArray['posts'];
        $rows           = $getArray['rows'];
        $limit          = $getArray['limit'];
        $fieldData       = $getArray['fieldId'];
        $sn_counter     = 0;
        $detail_view    = '';
        $id             = $headcount = 1;
        $fieldIdNew     = array();
        $tableHead  = '';
        
             foreach ($fieldData as $fieldkey => $fieldvalue) {
                // Html Field removed
                $pos = strpos($fieldkey, 'htmlfield_');
                if ($pos !== false) {
                    continue;
                }
                
           if($headcount < 6){
            $tableHead  .='<th>' . $fieldvalue . '</th>';
            }
            $fieldIdNew[] = $fieldkey;
           // } else{ break; }
            $headcount++;
            }

        if (!empty($posts)) {
            $entry_counter = 0;
            $table_head = '';
            $table_body = '';
            $popupTab   = '';

             if($headcount >= 6){
                     $table_head .='<th> . . . </th><th><input type="button" onclick="show_all_leads(' . $id . ',' . $form_id . ')" value="Show all Columns"></th>';
                }

            foreach ($posts as $results) {
                $table_row = '';
                $sn_counter++;
                $row_size_limit = 0;
                $form_data = $results->form_data;
                $lead_id = $results->id;
                $form_data = maybe_unserialize($form_data);
                $lead_date = date("jS F Y", strtotime($results->date));
                unset($form_data['hidden_field']);
                unset($form_data['action']);
                unset($form_data['g-recaptcha-response']);
                $entry_counter++;
                $complete_data = '';
                $popup_data_val= '';
                $date_td = '<td><b>'.$lead_date.'</b></td>';

                $returnData = $th_save_db->lfb_lead_form_value($form_data,$fieldIdNew,$fieldData,5);
                $table_row .= $returnData['table_row'];
                $table_row .= $date_td;

                                   

                foreach ($form_data as $form_data_key => $form_data_value) {
                        $row_size_limit++;

                    if (($detail_view != 1) && ($row_size_limit == 6)) {
                        $table_row .= '<td> . . . </td><td><a href="#lf-openModal-' . $lead_id . '" value="view">view</a></td>';
                    }
                }

                $complete_data .='<table><tr><th>Field</th><th>Value</th></tr>'.$returnData['table_popup'].'<tr><td>Date</td>'.$date_td.'</tr></table>';

                $popupTab .= '<div id="lf-openModal-'.$lead_id.'" class="lf-modalDialog">
                          <div class="lfb-popup-leads"><a href="#lf-close" title="Close" class="lf-close">X</a>'.$complete_data.'
                          </div>
                          </div>';

                $table_body .= '<tbody id="lead-id-' . $lead_id . '">';
                $table_body .= '<tr><td><span class="lead-count">' . $sn_counter . '</span><a class="lead-remove" onclick="delete_this_lead(' . $lead_id . ',\''.$nonce.'\')" ><i class="fa fa-trash" aria-hidden="true"></i></a></td>'. $table_row .'</tr>';
            }

              $thHead = '<div class="wrap" id="form-leads-show"><table class="show-leads-table wp-list-table widefat fixed" id="show-leads-table" >
                <thead><tr><th>Action</th>'.$tableHead.'<th>Date</th>'.$table_head.'</tr></thead>';

            echo wp_kses($thHead. $table_body.'</tbody></table>'.$popupTab,$this->expanded_alowed_tags());
    
            $total = ceil($rows / $limit);
            if ($id > 1) {
                echo "<a href=''  onclick='lead_pagi_view(" . intval($id - 1) . "," . intval($form_id) . ")' class='button'><i class='fa fa-chevron-left'></i></a>";
            }
            if ($id != $total) {
                "<a href='' onclick='lead_pagi_view(" . intval($id + 1) . "," . intval($form_id) . ")' class='button'><i class='fa fa-chevron-right'></i></a>";
            }
            ?> <ul class='page'>
                <?php
            for ($i = 1; $i <= $total; $i++) {
                if ($i == $id) {
                  ?> <li class='lf-current'><a href='#'><?php echo intval($i); ?></a></li> <?php
                } else {
                    echo "<li><a href='' onclick='lead_pagi_view(" . intval($i) . "," . intval($form_id) . ")'>" . intval($i) . "</a></li>";
                }
            }
             ?> </ul>
             </div>
             <?php
        } else {
              ?>
              <div class="wrap" id="form-leads-show">
                <?php
             esc_html_e("No leads..!","lead-form-builder")
              ?>
             </div>
             <?php
        }
    }

// show all leads

    function lfb_show_form_leads_datewise($form_id,$leadtype){
        $th_save_db = new LFB_SAVE_DB();

        $getArray =  $th_save_db->lfb_get_all_view_date_leads_db($form_id,$leadtype);
        $nonce = wp_create_nonce( 'lfb-nonce-rm' );

        $posts          = $getArray['posts'];
        $rows           = $getArray['rows'];
        $limit          = $getArray['limit'];
        $fieldData       = $getArray['fieldId'];
        $sn_counter     = 0;
        $detail_view    = '';
        $id             = $headcount = 1;
        $fieldIdNew     = array();

            $tableHead  = '';
            foreach ($fieldData as $fieldkey => $fieldvalue) {
                // Html Field removed
                $pos = strpos($fieldkey, 'htmlfield_');
                if ($pos !== false) {
                    continue;
                }

           if($headcount < 6){
            $tableHead  .='<th>' . $fieldvalue . '</th>';
            }
            $fieldIdNew[] = $fieldkey;
           // } else{ break; }
            $headcount++;
            }


        if (!empty($posts)) {
            $entry_counter = 0;
            $value1 = 0;
            $table_head = '';
            $table_body = '';
            $popupTab   = '';
           
            if($headcount >= 6){
                     $table_head .='<th> . . . </th><th><input type="button" onclick="show_all_leads(' . $id . ',' . $form_id . ')" value="'.esc_html__('Show all Columns','lead-form-builder').'"></th>';
                }
            foreach ($posts as $results) {
                $table_row = '';
                $row_size_limit = 0;
                $form_data = $results->form_data;
                $lead_date = date("jS F Y", strtotime($results->date));
                $lead_id = $results->id;
                $form_data = maybe_unserialize($form_data);
                unset($form_data['hidden_field']);
                unset($form_data['action']);
                unset($form_data['g-recaptcha-response']);
                $entry_counter++;
                 $sn_counter++;
                $complete_data = '';
                $popup_data_val= '';
                    $date_td = '<td><b>'.$lead_date.'</b></td>';

            $returnData = $th_save_db->lfb_lead_form_value($form_data,$fieldIdNew,$fieldData,5);
            $table_row .= $returnData['table_row'];
                                   
            $table_row .= $date_td;
                foreach ($form_data as $form_data_key => $form_data_value) {
                    $row_size_limit++;

                    if (($detail_view != 1) && ($row_size_limit == 6)) {
                        $table_row .= '<td>. . .</td><td><a href="#lf-openModal-' . $lead_id . '" value="view">view</a></td>';
                    }
                }

                $complete_data .='<table><tr><th>Field</th><th>Value</th></tr>'.$returnData['table_popup'].'<tr><td>Date</td>'.$date_td.'</tr></table>';


                $popupTab .= '<div id="lf-openModal-'.$lead_id.'" class="lf-modalDialog">
                          <div class="lfb-popup-leads" ><a href="#lf-close" title="Close" class="lf-close">X</a>'.$complete_data.'
                          </div>
                          </div>';
                          /** Today Leads Show**/
                $table_body .= '<tbody id="lead-id-' . $lead_id . '">';
                $table_body .= '<tr><td><span class="lead-count">' . $sn_counter . '</span><a lfid = "ss" class="lead-remove" onclick="delete_this_lead(' . $lead_id . ',\''.$nonce.'\')" ><i class="fa fa-trash" aria-hidden="true"></i></a></td>'. $table_row .'</tr>';
            }


                $thHead = '<div class="wrap" id="form-leads-show"><table class="show-leads-table wp-list-table widefat fixed" id="show-leads-table" >
                <thead><tr><th>Action</th>'.$tableHead.'<th>Date</th>'.$table_head.'</tr></thead>';

                echo wp_kses($thHead. $table_body.'</tbody></table>'.$popupTab,$this->expanded_alowed_tags());

            $rows = count($rows);
            $total = ceil($rows / $limit);
            if ($id > 1) {
                echo "<a href=''  onclick='lead_pagination_datewise(" . intval($id - 1) . "," . intval($form_id) . ",\"".esc_attr($leadtype)."\");' class='button'><i class='fa fa-chevron-left'></i></a>";
            }
            if ($id != $total) {
                echo "<a href='' onclick='lead_pagination_datewise(" . intval($id + 1) . "," . intval($form_id) . ",\"".esc_attr($leadtype)."\");' class='button'><i class='fa fa-chevron-right'></i></a>";
            }
            echo "<ul class='page'>";
            for ($i = 1; $i <= $total; $i++) {
                if ($i == $id) {
                    ?> <li class='lf-current'><a href='#'><?php echo intval($i); ?></a></li> <?php
                } else {
                    echo "<li><a href='' onclick='lead_pagination_datewise(".intval($i).",".intval($form_id).",\"".esc_attr($leadtype)."\");'>" . intval($i) . "</a></li>";
                }
            }
             ?></ul></div>
             <?php
        } else {
            ?> <div class="wrap" id="form-leads-show"><?php
             esc_html_e("No leads..!","lead-form-builder");
             ?> </div> <?php
        }
    }
}
