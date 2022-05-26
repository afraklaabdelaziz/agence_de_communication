<?php 
/*!
 * Admin Page: Settings: Tax settings
 */

// stop direct access
if ( ! defined( 'ZEROBSCRM_PATH' ) ) exit;

global $wpdb, $zbs;  #} Req

$confirmAct = false;
$taxTables = zeroBSCRM_getTaxTableArr();

#} Act on any edits!
if (isset($_POST['editzbstax'])){

    // check nonce
    check_admin_referer( 'jpcrm-update-settings-tax' );

    // cycle through realistic potentials:
    $taxTableIDs = array(); // this stores a quick index, every ID not present will get culled after this.
    for ($i = 1; $i <= 64; $i++){

        // got deets?
        $thisLine = array(
            'id' => -1,
            'name' => '',
            'rate' => 0.0
        );

        // ID
        if (isset($_POST['zbs-taxtable-line-' .$i. '-id'])){
            $potentialID = (int)sanitize_text_field( $_POST['zbs-taxtable-line-' .$i. '-id'] );
            if ($potentialID > 0) {
                $thisLine['id'] = $potentialID;
                $taxTableIDs[] = $potentialID;
            }
        }

        // name + rate
        if (isset($_POST['zbs-taxtable-line-' .$i. '-name'])) $thisLine['name'] = sanitize_text_field( $_POST['zbs-taxtable-line-' .$i. '-name'] );
        if (isset($_POST['zbs-taxtable-line-' .$i. '-rate'])) $thisLine['rate'] = (float)sanitize_text_field( $_POST['zbs-taxtable-line-' .$i. '-rate'] );

        if ($thisLine['rate'] > 0){

            // Debug echo 'adding: <pre>'.print_r($thisLine,1).'</pre>';

            // add/update
            $addedID = zeroBSCRM_taxRates_addUpdateTaxRate(array(

                'id' => $thisLine['id'],
                'data'          => array(

                    'name'   => $thisLine['name'],
                    'rate'     => $thisLine['rate']

                )
            ));
            // add any newly added to id index (though actually, because taxTables got above, shouldn't occur)
            if ($thisLine['id'] == -1 && $addedID > 0) $taxTableIDs[] = $addedID;

        }

    }
    // cull all those ID's not found in post
    foreach ($taxTables as $rate){
        if (!in_array($rate['id'], $taxTableIDs)) zeroBSCRM_taxRates_deleteTaxRate(array('id'=>$rate['id']));
    }

    #} Reload
    $taxTables = zeroBSCRM_getTaxTableArr();

    $sbupdated = true;

}

// Debug echo '<pre>'.print_r($taxTables,1).'</pre>';

?><p id="sbDesc"><?php _e('On this page you can set up different tax rates to use throughout your CRM (e.g. in Transactions).',"zero-bs-crm"); ?></p>

<?php if (isset($sbupdated)) if ($sbupdated) { echo '<div style="width:500px; margin-left:20px;" class="wmsgfullwidth">'; zeroBSCRM_html_msg(0,__('Settings Updated',"zero-bs-crm")); echo '</div><br>'; } ?>

<div id="sbA">

    <form method="post">
        <input type="hidden" name="editzbstax" id="editzbstax" value="1" />
        <?php
        // add nonce
        wp_nonce_field( 'jpcrm-update-settings-tax');
        ?>

        <table class="table table-bordered table-striped wtab" id="zbs-taxtable-table">

            <thead>

            <tr>
                <th colspan="3" class="wmid"><button type="button" class="ui icon button zbs-taxtable-add-rate right floated" title="<?php _e('Add Rate',"zero-bs-crm"); ?>"><i class="plus icon"></i></button><?php _e('Tax Rates',"zero-bs-crm"); ?>:</th>
            </tr>

            <tr>
                <th><?php _e('Name',"zero-bs-crm"); ?>:</th>
                <th><?php _e('Rate',"zero-bs-crm"); ?>:</th>
                <th></th>
            </tr>

            </thead>

            <tbody>

            <tr id="zbs-taxtable-loader">
                <td colspan="3" class="wmid"><div class="ui padded segment loading borderless" id="zbs-taxtables-loader">&nbsp;</div></td>
            </tr>



            </tbody>
        </table>
        <table class="table" id="zbsNoTaxRateResults"<?php if (!is_array($taxTables) || count($taxTables) == 0) echo '';  else echo ' style="display:none"'; ?>>
            <tbody>
            <tr>
                <td class="wmid">
                    <div class="ui info icon message">
                        <div class="content">
                            <div class="header"><?php _e('No Tax Rates',"zero-bs-crm"); ?></div>
                            <p><?php echo sprintf( __( 'There are no tax rates defined yet. Do you want to <a href="%s" id="zbs-new-add-tax-rate">create one</a>?', 'zero-bs-crm' ), '#' ); ?></p>
                        </div>
                    </div>
                </td>
            </tr>

            </tbody>
        </table>
        
        <table class="table table-bordered table-striped wtab">
            <tbody>

            <tr>
                <td class="wmid"><button type="submit" class="ui button primary"><?php _e('Save Settings',"zero-bs-crm"); ?></button></td>
            </tr>

            </tbody>
        </table>

    </form>


    <script type="text/javascript">

        var zeroBSCRMJS_taxTable = <?php echo json_encode($taxTables); ?>;
        var zeroBSCRMJS_taxTableLang = {

            defaulTaxName: '<?php echo zeroBSCRM_slashOut(__('Tax Rate Name','zero-bs-crm')); ?>',
            defaulTaxPerc: '<?php echo zeroBSCRM_slashOut(__('Tax Rate %','zero-bs-crm')); ?>',
            percSymbol: '<?php echo zeroBSCRM_slashOut(__('%','zero-bs-crm')); ?>',

        };

        jQuery(function(){

            // anything to build?
            if (window.zeroBSCRMJS_taxTable.length > 0)
                jQuery.each(window.zeroBSCRMJS_taxTable,function(ind,ele){

                    zeroBSCRMJS_taxTables_addLine(ele);

                });

            // remove loader
            jQuery('#zbs-taxtable-loader').remove();

            // bind what's here
            zeroBSCRMJS_bind_taxTables();

        });

        function zeroBSCRMJS_bind_taxTables(){

            jQuery('#zbs-new-add-tax-rate').off('click').on( 'click', function(){

                // add a line
                zeroBSCRMJS_taxTables_addLine();

                // hide msg
                jQuery('#zbsNoTaxRateResults').hide();

            });


            jQuery('.zbs-taxtable-add-rate').off('click').on( 'click', function(){

                // add a new line
                zeroBSCRMJS_taxTables_addLine();

            });

            jQuery('.zbs-taxtable-remove-rate').off('click').on( 'click', function(){

                var that = this;

                swal({
                    title: '<?php _e('Are you sure?','zero-bs-crm'); ?>',
                    text: '<?php _e('Are you sure you want to delete this tax rate? This will remove it from your database and existing transactions with this tax rate will not show properly. You cannot undo this','zero-bs-crm'); ?>',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '<?php _e('Yes remove the tax rate','zero-bs-crm'); ?>',
                })//.then((result) => {
                    .then(function (result) {
                        if (typeof result.value != "undefined" && result.value) {

                            var thisThat = that;

                            // brutal.
                            jQuery(thisThat).closest('.zbs-taxtable-line').remove();

                        }
                    });

            });

            // numbersOnly etc.
            zbscrm_JS_bindFieldValidators();
        }

        function zeroBSCRMJS_taxTables_addLine(line){

            // gen the html
            var html = zeroBSCRMJS_taxTables_genLine(line);

            // append to table
            jQuery('#zbs-taxtable-table tbody').append(html);

            // rebind
            zeroBSCRMJS_bind_taxTables();

        }

        function zeroBSCRMJS_taxTables_genLine(line){

            var i = jQuery('.zbs-taxtable-line').length + 1;
            var namestr = '', rateval = '', thisID = -1;
            if (typeof line != "undefined" && typeof line.id != "undefined") thisID = line.id;
            if (typeof line != "undefined" && typeof line.name != "undefined") namestr = line.name;
            if (typeof line != "undefined" && typeof line.rate != "undefined") rateval = line.rate;

            var html = '';

            html += '<tr class="zbs-taxtable-line">';
            html += '<td>';
            html += '<input type="hidden" name="zbs-taxtable-line-' + i + '-id" value="' + thisID + '" />';
            html += '<div class="ui fluid input"><input type="text" class="winput form-control" name="zbs-taxtable-line-' + i + '-name" id="zbs-taxtable-line-' + i + '-name" value="' + namestr + '" placeholder="' + window.zeroBSCRMJS_taxTableLang.defaulTaxName + '" /></div>';
            html += '</td>';
            html += '<td>';
            html += '<div class="ui right labeled input">';
            html += '<input type="text" class="winput form-control numbersOnly zbs-dc" name="zbs-taxtable-line-' + i + '-rate" id="zbs-taxtable-line-' + i + '-rate" value="' + rateval + '" placeholder="' + window.zeroBSCRMJS_taxTableLang.defaulTaxPerc + '"  />';
            html += '<div class="ui basic label">' + window.zeroBSCRMJS_taxTableLang.percSymbol + '</div></div>';
            html += '</td>';
            html += '<td class="wmid">';
            html += '<button type="button" class="ui icon button zbs-taxtable-remove-rate"><i class="close icon"></i></button>';
            html += '</td>';
            html += '</tr>';

            return html;
        }

    </script>

</div>
