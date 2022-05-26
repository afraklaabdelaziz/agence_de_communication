<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 * V1.0
 *
 * Copyright 2020 Automattic
 *
 * Date: 26/05/2016
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

   function zeroBSCRM_TransactionsMetaboxSetup(){

		add_meta_box("zeroBSCRM-transaction-meta-box", __("Transaction Info","zero-bs-crm"), "zbs_transactions_meta_box_markup", "zerobs_transaction", "normal", "high", null);
	
   }


   // this one needs to fire later :)
   //add_action( 'after_zerobscrm_settings_init','zeroBSCRM_TransactionsMetaboxSetup');
   add_action( 'add_meta_boxes','zeroBSCRM_TransactionsMetaboxSetup');

/* ======================================================
   / Init Func
   ====================================================== */




/* ======================================================
  Transactions Metabox
   ====================================================== */

	#} Add meta box to woo commerce orders..
	function zbs_transactions_meta_box_markup(){
		global $post;
		$meta = get_post_meta($post->ID,'zbs_transaction_meta', true);
	    ?>
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
	    <?php


    	// WH added 20/7/18 - trans to co link
    	$companyID = -1; if (isset($post->ID) && $post->ID > 0) { $companyID = (int)get_post_meta($post->ID,'zbs_parent_co', true); }

    	//print_r($meta);

	    if($meta == ''){ ?>


	    	<input type="hidden" name="zbs_hidden_flag" value="true" />
	    	<input type="hidden" name="zbscrm_newtransaction" value="1" />
	    	<table class="form-table wh-metatab wptbp" id="wptbpMetaBoxMainItem">

				<tr class="wh-large">
					<th style="min-width:240px"><label for="status"><?php _e("Transaction Status","zero-bs-crm");?>:</label></th>
		            <td>
		            	<select id="status" name="status">
		            		<?php

		            			$chosenStatus = ''; $noSet = false;
		            			if(isset($meta['status']) && !empty($meta['status'])) $chosenStatus = $meta['status'];

		            			$transStatuses = zeroBS_getTransactionsStatuses();
		            			$transStatusArr = explode(",", $transStatuses);
		            			foreach($transStatusArr as $transStat){
		            				if($transStat == $chosenStatus){
		            					echo "<option value='".$transStat."' selected>".$transStat."<status>";
		            				}else{
		            					echo "<option value='".$transStat."'>".$transStat."<status>";
		            				}
		            			}
		            			if($noSet && $chosenStatus != ''){
		            				echo "<option value='".$chosenStatus."' selected>".$chosenStatus."<status>";
		            			}

		            		?>

		            	</select>
		            </td>
		        </tr>

				<tr class="wh-large">
					<th><label for="orderid"><?php _e("Transaction unique ID","zero-bs-crm");?>:</label></th>
		            <td><input type = "text" id="orderid" name="orderid" class="form-control" value="<?php echo zeroBSCRM_uniqueID(); ?>" autocomplete="zbstra-<?php echo time(); ?>-orderid"></td>
		        </tr>

				<tr class="wh-large">
					<th><label><?php _e("Transaction Date","zero-bs-crm");?>:</label></th>
	                <td>
	                	<input type="text" name="transactionDate" id="transactionDate" class="form-control zbs-date" placeholder="" value="" autocomplete="zbstra-<?php echo time(); ?>-date" />
	                	<input type="hidden" name="trans_time" id="trans_time" value="" /><!-- for now -->
	                </td>
	            </tr>

				<tr class="wh-large">
					<th><label for="total"><?php _e("Transaction Value ","zero-bs-crm"); ?><?php echo "(". zeroBSCRM_getCurrencyChr() . "):"; ?></label></th>
		            <td><input id="total" name="total" value="" class="form-control numbersOnly" style="width: 130px;display: inline-block;" autocomplete="zbstra-<?php echo time(); ?>-val" /></td>
		        </tr>

				<tr class="wh-large">
					<th><label for="item"><?php _e("Transaction Name:","zero-bs-crm"); ?></label>
					<span class="zbs-infobox" style="margin-top:3px"><?php _e("If possible, keep these the same for the same item (they are used in the transaction index)","zero-bs-crm");?></span> 
					</th>
		            <td><input id="item" name="item" value="" class="form-control widetext" autocomplete="zbstra-<?php echo time(); ?>-name" /></td>
		        </tr>

		        <?php #} Custom Fields

	                global $zbsTransactionFields; 

                    // wh centralised 20/7/18 - 2.91+


                    	// had to add this, because other fields are put out separately here
                    	$skipList = array('orderid','customer','status','total','customer_name','date','currency','item','net','tax','fee','discount','tax_rate');
                    
                    // output fields
                    zeroBSCRM_html_editFields($meta,$zbsTransactionFields,'zbsct_',$skipList);


		        ?>

		        <tr><td colspan="2"><hr /></td></tr>
		    </table>
		    <table class="form-table wh-metatab wptbp">
		        <tr><td colspan="2" style="margin-top:0;padding-top:0;"><h2 style="margin-top:0;padding-top:0;"><?php _e("Assign to a Contact or ".jpcrm_label_company()." (Optional)","zero-bs-crm");?></td></tr>

		        <tr class="wh-large">
		        	<td colspan="2">		        		
                    <?php if (zeroBSCRM_getSetting('companylevelcustomers') != "1"){ 


							$cname = ''; $cid = -1; $transCust = -1;

				            if (isset($_GET['zbsprefillcust']) && !empty($_GET['zbsprefillcust'])){

				                $transCust = zeroBS_getCustomer((int)sanitize_text_field($_GET['zbsprefillcust'])); 
				                
				            }
							            
							if (is_array($transCust) && isset($transCust['id'])){
			                    
								 $cid = $transCust['id'];
								 $cname = zeroBS_getCustomerNameShort($cid);
								 if ($cname == -1) $cname = '';
			                     
			                 }

                    		// Just contact
                    		?><label><?php _e("Customer","zero-bs-crm"); ?></label><?php
                    		echo zeroBSCRM_CustomerTypeList('zbscrmjs_transaction_setCustomer', $cname,false,'zbscrmjs_transaction_unsetCustomer');

                    		// mikes inv selector
                    		?><div class="assignInvToCust" style="display:none"><label for="invoiceFieldWrap"><?php _e("Customer invoice:","zero-bs-crm"); ?></label><span class="zbs-infobox" style="margin-top:3px"><?php _e("Is this transaction a payment for an invoice? If so enter the Invoice ID. Otherwise leave blank","zero-bs-crm");?></span></div>
                    		<div id="invoiceFieldWrap" style="position:relative;display:none" class="assignInvToCust">
                    			<input style="max-width:200px" id="invoice_id" name="invoice_id" value="<?php if(isset($meta['invoice_id'])){ echo $meta['invoice_id']; } ?>" class="form-control" autocomplete="zbstra-<?php echo time(); ?>-<?php echo rand(0,100); ?>" />
                    		</div><?php


                    	} else {

                    		// contact or co
                    		?><div class="ui grid"><div class="seven wide column"><label><?php _e("Customer","zero-bs-crm"); ?></label><br /><?php

                    		// check
                    		if (!isset($customerName) || $customerName == -1) $customerName = '';

                    		// contact
                    		 echo zeroBSCRM_CustomerTypeList('zbscrmjs_transaction_setCustomer', $customerName,false,'zbscrmjs_transaction_unsetCustomer');

                    		// mikes inv selector
                    		?><div class="assignInvToCust" style="display:none"><label for="invoiceFieldWrap"><?php _e("Customer invoice:","zero-bs-crm"); ?></label><span class="zbs-infobox" style="margin-top:3px"><?php _e("Is this transaction a payment for an invoice? If so enter the Invoice ID. Otherwise leave blank","zero-bs-crm");?></span></div>
                    		<div id="invoiceFieldWrap" style="position:relative;display:none" class="assignInvToCust">
                    			<input style="max-width:200px" id="invoice_id" name="invoice_id" value="<?php if(isset($meta['invoice_id'])){ echo $meta['invoice_id']; } ?>" class="form-control" autocomplete="zbstra-<?php echo time(); ?>-<?php echo rand(0,100); ?>" />
                    		</div><?php


                    		 ?></div><div class="two wide column centered"><?php _e('Or','zero-bs-crm'); ?></div><div class="seven wide column"><label><?php echo jpcrm_label_company(); ?></label><br /><?php

                    		// company
	                            
	                            #} Co Name Default
	                            $coName = ''; $companyID = -1;

	                            // from prefill, potentially
					            if(isset($_GET['zbsprefillco']) && !empty($_GET['zbsprefillco'])){
					                $companyID = (int)sanitize_text_field($_GET['zbsprefillco']);
					            }

	                            if (!empty($companyID) && $companyID > 0){
                                    $co = zeroBS_getCompany($companyID);
                                    if (isset($co) && isset($co['meta']) && isset($co['meta']['coname'])) $coName = $co['meta']['coname'];
                                    # Shouldn't need this? WH attempted fix for caching, not here tho..
                                    if (empty($coName) && isset($co['coname'])) $coName = $co['coname'];
                                    if (empty($coName)) $coName = jpcrm_label_company().' #'.$co['id'];
                                
	                            }
	                        
	                            #} Output
	                            echo zeroBSCRM_CompanyTypeList('zbscrmjs_transaction_setCompany',$coName,true,'zbscrmjs_transaction_unsetCompany'); 
	                            
	                            #} Hidden input (real input) & Callback func
	                            ?><input type="hidden" name="zbsct_company" id="zbsct_company" value="<?php echo $companyID; ?>" />


	                        </div></div><?php

                    	} ?>

		        	</td>
		        </tr>

				<tr class="wh-large hide">
					<th><label for="customer"><?php _e("Customer ID","zero-bs-crm");?></label></th>
		            <td><input id="customer" name="customer" value="<?php echo $cid; ?>" class="form-control widetext" type="hidden"></td>
		        </tr>

				<tr class="wh-large hide">
					<th><label for="customer"><?php _e("Customer Name","zero-bs-crm");?></label></th>
		            <td><input id="customer_name" name="customer_name" value="" class="form-control widetext" type="hidden"></td>
		        </tr>

		    </table>
	    	<?php
	    } else { 
	    	echo '<input type="hidden" name="zbs_hidden_flag" value="1" />';
	    	echo '<table class="form-table wh-metatab wptbp" id="wptbpMetaBoxMainItem">';
	    	

		    // this was confusing internal meta + parent cust, so broke DAL2
		    $customerID = -1;
		    $customerName = -1;
		    $cname = ''; // not sure why we need this
		    /* prev way *using wrong meta 
    		if (isset($meta['customer_name']) && !empty($meta['customer_name'])) 
    			$cname = $meta['customer_name'];
    		else {
				if (!empty($meta['customer'])){
					$cname = zeroBS_getCustomerNameShort($meta['customer']);
				}
    		}

    			and this from recent mike fix: 
    			$cname = ''; 
		        		if (isset($meta['customer_name']) && !empty($meta['customer_name'])) 
		        			$cname = $meta['customer_name'];
		        		else {
							if (!empty($meta['customer'])){
								$cname = zeroBS_getCustomerNameShort($meta['customer']);
							}
		        		}



    		*/
	    	$customerID = (int)get_post_meta($post->ID,'zbs_parent_cust', true);
	    	if ($customerID > 0) $customerName = zeroBS_customerName($customerID,false,true,false); 
            if (!isset($customerName) || $customerName == -1) $customerName = '';

	    	?>



				<tr class="wh-large">
					<th style="min-width:240px"><label for="status"><?php _e("Transaction Status","zero-bs-crm");?>:</label></th>
		            <td>
		            	<select id="status" name="status">
		            		<?php

		            			$chosenStatus = ''; $noSet = false;
		            			if(isset($meta['status']) && !empty($meta['status'])) $chosenStatus = $meta['status'];

		            			$transStatuses = zeroBS_getTransactionsStatuses();
		            			$transStatusArr = explode(",", $transStatuses);
		            			foreach($transStatusArr as $transStat){
		            				if($transStat == $chosenStatus){
		            					echo "<option value='".$transStat."' selected>".$transStat."<status>";
		            				}else{
		            					echo "<option value='".$transStat."'>".$transStat."<status>";
		            				}
		            			}
		            			if($noSet && $chosenStatus != ''){
		            				echo "<option value='".$chosenStatus."' selected>".$chosenStatus."<status>";
		            			}

		            		?>

		            	</select>
		            </td>
		        </tr>
		    </table>

		    <div class="ui divider"></div>
		    <h5><?php _e("Transaction Data","zero-bs-crm");?></h5>

		    <table>

				<tr class="wh-large">
					<th><label for="orderid"><?php _e("Transaction unique ID","zero-bs-crm");?>:</label></th>
		            <td><input type="text" id="orderid" name="orderid" class="form-control" value="<?php if(isset($meta['orderid'])){ echo $meta['orderid']; }else{ echo zeroBSCRM_uniqueID(); } ?>" autocomplete="zbstra-<?php echo time(); ?>-<?php echo rand(0,100); ?>" /></td>
		        </tr>


				<tr class="wh-large">
					<th><label><?php _e("Transaction Date","zero-bs-crm");?>:</label></th>
	                <td>
	                	<input type="text" name="transactionDate" id="transactionDate" class="form-control zbs-date" placeholder="" value="<?php if(isset($post->post_date)){

							#} Put in format
							// NOTE there is NO TIME used here, so we use post_date_gmt + 'true' for isGMT in  zeroBSCRM_date_i18n
		                	$dt = strtotime($post->post_date_gmt);
							echo zeroBSCRM_date_i18n(-1,$dt,false,true);

	                	}?>" autocomplete="zbstra-<?php echo time(); ?>-<?php echo rand(0,100); ?>" />
	                	<input type="hidden" name="trans_time" id="trans_time" value="<?php if(isset($meta['trans_time'])){ echo $meta['trans_time']; }?>" /><!-- passes any saved times for now -->
	                </td>
	            </tr>

				<tr class="wh-large">
					<th><label for="total"><?php _e("Transaction Value ","zero-bs-crm"); ?><?php echo "(". zeroBSCRM_getCurrencyChr() . "):"; ?></label></th>
		            <td><input id="total" name="total" value="<?php if(isset($meta['total'])){ echo $meta['total']; } ?>" class="form-control numbersOnly" style="width: 130px;display: inline-block;" autocomplete="zbstra-<?php echo time(); ?>-<?php echo rand(0,100); ?>" /></td>
		        </tr>

				<tr class="wh-large">
					<th><label for="item"><?php _e("Transaction Name:","zero-bs-crm"); ?></label>
					<span class="zbs-infobox" style="margin-top:3px"><?php _e("If possible, keep these the same for the same item (they are used in the transaction index)","zero-bs-crm");?></span> 
					</th>
		            <td><input id="item" name="item" value="<?php if(isset($meta['item'])){ echo $meta['item']; }?>" class="form-control widetext" autocomplete="zbstra-<?php echo time(); ?>-<?php echo rand(0,100); ?>" /></td>
		        </tr>

		        <?php #} Custom Fields

	                global $zbsTransactionFields; 

                    // wh centralised 20/7/18 - 2.91+

                    	// had to add this, because other fields are put out separately here
                    	$skipList = array('orderid','customer','status','total','customer_name','date','currency','item','net','tax','fee','discount','tax_rate');
                    
                    // output fields
                    zeroBSCRM_html_editFields($meta,$zbsTransactionFields,'zbsct_',$skipList);


		        ?>


		    </table>

		    <?php
		    $line_items = get_post_meta($post->ID, 'zerobscrm_lineitems',true);		    
		    if($line_items != '' && is_array($line_items)){ ?>

		    <div class="ui divider"></div>
		    <h5><?php _e("Line Items","zero-bs-crm");?></h5>
		    <table class="ui table green">
		    	<thead>
            <tr>
  		    		<th><?php _e("Name","zero-bs-crm");?></th>
  		    		<th><?php _e("Quantity","zero-bs-crm");?></th>
  		    		<th><?php _e("Tax","zero-bs-crm");?></th>
  		    		<th><?php _e("Shipping","zero-bs-crm");?></th>
  		    		<th><?php _e("Handling","zero-bs-crm");?></th>
  		    		<th><?php _e("Amount","zero-bs-crm");?></th>
            </tr>
		    	</thead>
		    	<tbody><?php

			    	if (count($line_items) > 0){

			    		// res
			    		foreach ($line_items as $item){
			    			echo "<tr>";
			    				if (isset($item["name"])) echo "<td>" . $item["name"] . "</td>";
			    				if (isset($item["quantity"])) echo "<td>" . $item["quantity"] . "</td>";
			    				if (isset($item["tax"])) echo "<td>" . $item["tax"] . "</td>";
			    				if (isset($item["ship"])) echo "<td>" . $item["ship"] . "</td>";
			    				if (isset($item["handle"])) echo "<td>" . $item["handle"] . "</td>";
			    				if (isset($item["amount"])) echo "<td>" . $item["amount"] . "</td>";
			    			echo "</tr>";
			    		}

			    	} else {

			    		// no res
			    		?><tr><td colspan="6"><?php _e('No Line Items Found',"zero-bs-crm"); ?></td></tr><?php
			    		
			    	} ?>
			    </tbody>

		    </table>


		   	<?php } ?>

		    <table class="form-table wh-metatab wptbp">


		        <tr><td><hr /></td></tr>

		        <tr><td><h2><?php _e("Assign to a Contact or ".jpcrm_label_company()." (Optional)","zero-bs-crm");?></td></tr>

		        <tr class="wh-large" id="zbs-transaction-assignment-wrap">
		        	<td>		        		
                    <?php if (zeroBSCRM_getSetting('companylevelcustomers') != "1"){ 

                    		// Just contact
                    		?><div id="zbs-customer-title"><label><?php _e("Customer","zero-bs-crm"); ?></label></div><?php
                    		echo zeroBSCRM_CustomerTypeList('zbscrmjs_transaction_setCustomer', $customerName,false,'zbscrmjs_transaction_unsetCustomer');

                    		// mikes inv selector
                    		?><div class="assignInvToCust" style="display:none;max-width:658px" id="invoiceSelectionTitle"><label for="invoiceFieldWrap"><?php _e("Customer invoice:","zero-bs-crm"); ?></label><span class="zbs-infobox" style="margin-top:3px"><?php _e("Is this transaction a payment for an invoice? If so enter the Invoice ID. Otherwise leave blank","zero-bs-crm");?></span></div>
                    		<div id="invoiceFieldWrap" style="position:relative;display:none;max-width:658px" class="assignInvToCust"><input style="max-width:200px" id="invoice_id" name="invoice_id" value="<?php if(isset($meta['invoice_id'])){ echo $meta['invoice_id']; } ?>" class="form-control" autocomplete="zbstra-<?php echo time(); ?>-<?php echo rand(0,100); ?>" /></div><?php

                    	} else {

                    		// contact or co
                    		?><div class="ui grid"><div class="seven wide column">
                    		<div id="zbs-customer-title"><label><?php _e("Customer","zero-bs-crm"); ?></label></div><?php

                    		// contact
                    		 echo zeroBSCRM_CustomerTypeList('zbscrmjs_transaction_setCustomer', $customerName,false,'zbscrmjs_transaction_unsetCustomer');
                    		// mikes inv selector
                    		?><div class="assignInvToCust" style="display:none;max-width:658px" id="invoiceSelectionTitle"><label for="invoiceFieldWrap"><?php _e("Customer invoice:","zero-bs-crm"); ?></label><span class="zbs-infobox" style="margin-top:3px"><?php _e("Is this transaction a payment for an invoice? If so enter the Invoice ID. Otherwise leave blank","zero-bs-crm");?></span></div>
                    		<div id="invoiceFieldWrap" style="position:relative;display:none;max-width:658px" class="assignInvToCust"><input style="max-width:200px" id="invoice_id" name="invoice_id" value="<?php if(isset($meta['invoice_id'])){ echo $meta['invoice_id']; } ?>" class="form-control" autocomplete="zbstra-<?php echo time(); ?>-<?php echo rand(0,100); ?>" /></div><?php

                    		 ?></div><div class="two wide column centered"><?php _e('Or','zero-bs-crm'); ?></div><div class="seven wide column"><div id="zbs-company-title"><label><?php _e("Company","zero-bs-crm"); ?></label></div><?php

                    		// company

	                            #} Co Name Default
	                            $coName = '';

	                            if (!empty($companyID) && $companyID > 0){
                                    $co = zeroBS_getCompany($companyID);
                                    if (isset($co) && isset($co['meta']) && isset($co['meta']['coname'])) $coName = $co['meta']['coname'];
                                    # Shouldn't need this? WH attempted fix for caching, not here tho..
                                    if (empty($coName) && isset($co['coname'])) $coName = $co['coname'];
                                    if (empty($coName)) $coName = jpcrm_label_company().' #'.$co['id'];
                                
	                            }
	                        
	                            #} Output
	                            echo zeroBSCRM_CompanyTypeList('zbscrmjs_transaction_setCompany',$coName,true,'zbscrmjs_transaction_unsetCompany'); 
	                            
	                            #} Hidden input (real input) & Callback func
	                            ?><input type="hidden" name="zbsct_company" id="zbsct_company" value="<?php echo $companyID; ?>" />


	                        </div></div>

                    	<?php } ?>

		        	</td>
		        </tr>


		        <?php // ms way of hiding :o ?>
				<tr class="wh-large hide">
					<th><label for="customer"><?php _e("Customer ID","zero-bs-crm");?></label></th>
		            <td><input id="customer" name="customer" value="<?php 

		            	// this was confusing internal meta + parent cust, so broke DAL2
		            	// if(isset($meta['customer'])){ echo $meta['customer']; }
		            	echo $customerID;

		             ?>" class="form-control widetext" type="hidden"></td>
		        </tr>

				<tr class="wh-large hide">
					<th><label for="customer_name"><?php _e("Customer Name","zero-bs-crm");?></label></th>
		            <td><input id="customer_name" name="customer_name" value="<?php echo $cname; ?>" class="form-control widetext" type="hidden"></td>
		        </tr>

		    </table>
	    	<?php } ?>

	  
		<script type="text/javascript">

		
		jQuery(function(){

            // turn off auto-complete on records via form attr... should be global for all ZBS record pages
            jQuery('#post').attr('autocomplete','off');


			// on init, if customer has been selected, prefil inv list
			if (jQuery('#customer').val()){
			
				// any inv selected?
				var existingInvID = false;
				if (jQuery('#invoice_id').val()) existingInvID = jQuery('#invoice_id').val();

				zbscrmjs_build_custInv_dropdown(jQuery('#customer').val(),existingInvID);

			}

			// bind 
			setTimeout(function(){
				zeroBSCRMJS_showInvLinkIf();
				zeroBSCRMJS_showContactLinkIf(jQuery("#customer").val());
				zeroBSCRMJS_showCompanyLinkIf(jQuery('#zbsct_company').val());
			},0);



		});


		function zbscrmjs_transaction_unsetCustomer(o){

			if (typeof o == "undefined" || o == ''){

				jQuery("#customer").val('');
				jQuery("#customer_name").val('');

				// should also hide these!
				jQuery('.assignInvToCust, #invoiceFieldWrap').hide();

				setTimeout(function(){

					// when inv select drop down changed, show/hide quick nav
					zeroBSCRMJS_showContactLinkIf('');

				},0);
				
			}
		}

		function zbscrmjs_transaction_unsetCompany(o){

			if (typeof o == "undefined" || o == ''){

				jQuery("#zbsct_company").val('');

				setTimeout(function(){

					// when inv select drop down changed, show/hide quick nav
					zeroBSCRMJS_showCompanyLinkIf('');

				},0);
				
			}
		}

		// custom fuction to copy customer details from typeahead customer deets
		function zbscrmjs_transaction_setCustomer(obj){

			// 
		//	console.log("Customer Chosen!",obj);

			if (typeof obj.id != "undefined"){

				// set vals
				jQuery("#customer").val(obj.id);
				jQuery("#customer_name").val(obj.name);

				// build inv dropdown
				zbscrmjs_build_custInv_dropdown(obj.id);

			} else {

				jQuery("#customer").val('');
				jQuery("#customer_name").val('');

			}

			setTimeout(function(){

				var lID = obj.id;

				// when inv select drop down changed, show/hide quick nav
				zeroBSCRMJS_showContactLinkIf(lID);

			},0);
		}

        // custom fuction to copy company details from typeahead company deets
        function zbscrmjs_transaction_setCompany(obj){

            // console.log("Company Chosen!",obj);

            if (typeof obj.id != "undefined"){

                // set vals
                jQuery("#zbsct_company").val(obj.id);

            } else {

                // set vals
                jQuery("#zbsct_company").val('');

            }

			setTimeout(function(){

				var lID = obj.id;

				// when inv select drop down changed, show/hide quick nav
				zeroBSCRMJS_showCompanyLinkIf(lID);

			},0);
        }

		// this builds a dropdown of invoices against a customer
		function zbscrmjs_build_custInv_dropdown(custID,preSelectedInvID){

			var previousInvVal = jQuery('#invoice_id').val();

			// if cust id, retrieve inv list from ajax/cache
			if (custID != ""){

				// show loading
				jQuery('#invoiceFieldWrap').append(zbscrm_js_uiSpinnerBlocker());
				
		

				zbscrm_js_getCustInvs(custID, function(r){

					// successfully got list!
					// console.log("got list",[r,r.length]);

					// wrap
					var retHTML = '<select id="invoice_id" name="invoice_id" class="form-control">'; //form-control

						// if has invoices:
						if (r.length > 0){

							// def
							retHTML += '<option value="" disabled="disabled"';
								
								// if an inv id is passed, don't select this
								if (typeof preSelectedInvID == "undefined" || preSelectedInvID <= 0) retHTML += ' selected="selected"';
							
							retHTML += '>Select Invoice</option>';
							retHTML += '<option value="">None</option>';

							// cycle through + create
							jQuery.each(r,function(ind,ele){

								var invID = ele.id; //  POST id


								// build a user-friendly str
								var invStr = ""; 

								// #TRANSITIONTOMETANO 
								if (typeof ele.zbsid != "undefined") {
								
									invStr += '#' + ele.zbsid;	
								
								} else {
									
									// forced to show post id as some kind of identifier..
									invStr += '#PID:' + ele.id;

								}
								if (typeof ele.meta != "undefined"){
									// val
									if (typeof ele.meta.val != "undefined") invStr += ' (' + window.zbs_root.currencyOptions.currencyStr + ele.meta.val + ')';
									// date
									if (typeof ele.meta.date != "undefined") invStr += ' - ' + ele.meta.date;

								}

								retHTML += '<option value="' + invID + '"';

									// if prefilled... select
									if (typeof preSelectedInvID != "undefined" && invID == preSelectedInvID) retHTML += ' selected="selected"';

								retHTML += '>' + invStr + '</option>';

							});

						} else {
							var zbs_no_found = '<?php _e('No Invoices Found!',"zero-bs-crm") ;?>';
							// no invs
							retHTML += '<option value="" disabled="disabled" selected="selected">'+ zbs_no_found +'</option>';

						}

					// / wrap
					retHTML += '</select>';
					
					// output
					jQuery('#invoiceFieldWrap').html(retHTML);

					// wh addition 20/7/18 - show when useful
					jQuery('.assignInvToCust').show();

					// bind 
					setTimeout(function(){
						zeroBSCRMJS_bindInvSelect();
					},0);


				},function(r){

					// wh addition 20/7/18 - hide until useful
					jQuery('.assignInvToCust').hide();

					// failed to get... leave as manual

					// localise
					var previousInvValL = previousInvVal;
					if (previousInvValL == 0) var previousInvValL = '';

					// NOTE THIS IS DUPE BELOW... REFACTOR
					jQuery('#invoiceFieldWrap').html('<input style="max-width:200px;" id="invoice_id" name="invoice_id" value="' + previousInvValL + '" class="form-control">');

				});

			} else {

				// wh addition 20/7/18 - hide until useful
				jQuery('.assignInvToCust').show();


				// leave as manual entry (but maybe later do not allow?)
				if (previousInvVal == 0) var previousInvVal = '';
				// NOTE THIS IS DUPE ABOVE... REFACTOR
				jQuery('#invoiceFieldWrap').html('<input style="max-width:200px;" id="invoice_id" name="invoice_id" value="' + previousInvVal + '" class="form-control">');

					// bind 
					setTimeout(function(){
						zeroBSCRMJS_bindInvSelect();
					},0);

			}


		}

		// when inv select drop down changed, show/hide quick nav
		function zeroBSCRMJS_bindInvSelect(){

			jQuery('#invoice_id').on( 'change', function(){

				zeroBSCRMJS_showInvLinkIf();

			});

			zeroBSCRMJS_showInvLinkIf();
		}


		// if an inv is selected (against a trans) can 'quick nav' to inv
		function zeroBSCRMJS_showInvLinkIf(){

			// remove old
			//jQuery('#invoiceFieldWrap .zbs-view-invoice').remove();
			jQuery('#invoiceSelectionTitle .zbs-view-invoice').remove();

			// see if selected
			var inv = jQuery('#invoiceFieldWrap select').val();

			if (typeof inv != "undefined" && inv !== null && inv !== ''){

				inv = parseInt(inv);
				if (inv > 0){

					// seems like a legit inv, add


						var html = '<div class="ui right floated mini animated button zbs-view-invoice" style="margin-left:0.5em">';
								html += '<div class="visible content"><?php  zeroBSCRM_slashOut(__('View','zero-bs-crm')); ?></div>';
									html += '<div class="hidden content">';
								    	html += '<i class="icon file text"></i>';
								  	html += '</div>';
								html += '</div>';

						jQuery('#invoiceSelectionTitle').prepend(html);

						// bind
						zeroBSCRMJS_bindInvLinkIf();
				}
			}


		}

		// click for quicknav :)
		function zeroBSCRMJS_bindInvLinkIf(){

			jQuery('#invoiceSelectionTitle .zbs-view-invoice').off('click').on( 'click', function(){

				var invID = parseInt(jQuery('#invoiceFieldWrap select').val());//jQuery(this).attr('data-invid');

				var url = '<?php echo zbsLink('edit',-1,'zerobs_invoice',true); ?>' + invID;

				// bla bla https://stackoverflow.com/questions/1574008/how-to-simulate-target-blank-in-javascript
				window.open(url,'_parent');

			});
		}







		// if an contact is selected (against a trans) can 'quick nav' to contact
		function zeroBSCRMJS_showContactLinkIf(contactID){

			// remove old
			jQuery('#zbs-customer-title .zbs-view-contact').remove();
			jQuery('#zbs-transaction-learn-nav .zbs-trans-quicknav-contact').remove();

			if (typeof contactID != "undefined" && contactID !== null && contactID !== ''){

				contactID = parseInt(contactID);
				if (contactID > 0){

					// seems like a legit inv, add


						var html = '<div class="ui right floated mini animated button zbs-view-contact">';
								html += '<div class="visible content"><?php  zeroBSCRM_slashOut(__('View','zero-bs-crm')); ?></div>';
									html += '<div class="hidden content">';
								    	html += '<i class="user icon"></i>';
								  	html += '</div>';
								html += '</div>';

						jQuery('#zbs-customer-title').prepend(html);

						// ALSO show in header bar, if so
						var navButton = '<a target="_blank" style="margin-left:6px;" class="zbs-trans-quicknav-contact ui icon button blue mini labeled" href="<?php echo zbsLink('edit',-1,'zerobs_customer',true); ?>' + contactID + '"><i class="user icon"></i> <?php  zeroBSCRM_slashOut(__('Contact','zero-bs-crm')); ?></a>';
						jQuery('#zbs-transaction-learn-nav').append(navButton);

						// bind
						zeroBSCRMJS_bindContactLinkIf();
				}
			}


		}

		// click for quicknav :)
		function zeroBSCRMJS_bindContactLinkIf(){

			jQuery('#zbs-customer-title .zbs-view-contact').off('click').on( 'click', function(){

				// get from hidden input
				var contactID = parseInt(jQuery("#customer").val());//jQuery(this).attr('data-invid');

				if (typeof contactID != "undefined" && contactID !== null && contactID !== ''){
					contactID = parseInt(contactID);
					if (contactID > 0){

						var url = '<?php echo zbsLink('edit',-1,'zerobs_customer',true); ?>' + contactID;

						// bla bla https://stackoverflow.com/questions/1574008/how-to-simulate-target-blank-in-javascript
						window.open(url,'_parent');
					}
				}

			});
		}



		// if an Company is selected (against a trans) can 'quick nav' to Company
		function zeroBSCRMJS_showCompanyLinkIf(companyID){

			// remove old
			jQuery('#zbs-company-title .zbs-view-company').remove();
			jQuery('#zbs-transaction-learn-nav .zbs-trans-quicknav-company').remove();

			if (typeof companyID != "undefined" && companyID !== null && companyID !== ''){

				companyID = parseInt(companyID);
				if (companyID > 0){

					// seems like a legit inv, add


						var html = '<div class="ui right floated mini animated button zbs-view-company">';
								html += '<div class="visible content"><?php  zeroBSCRM_slashOut(__('View','zero-bs-crm')); ?></div>';
									html += '<div class="hidden content">';
								    	html += '<i class="building icon"></i>';
								  	html += '</div>';
								html += '</div>';

						jQuery('#zbs-company-title').prepend(html);

						// ALSO show in header bar, if so
						var navButton = '<a target="_blank" style="margin-left:6px;" class="zbs-trans-quicknav-company ui icon button blue mini labeled" href="<?php echo zbsLink('edit',-1,'zerobs_company',true); ?>' + companyID + '"><i class="building icon"></i> <?php  zeroBSCRM_slashOut(jpcrm_label_company()); ?></a>';
						jQuery('#zbs-transaction-learn-nav').append(navButton);

						// bind
						zeroBSCRMJS_bindCompanyLinkIf();
				}
			}


		}

		// click for quicknav :)
		function zeroBSCRMJS_bindCompanyLinkIf(){

			jQuery('#zbs-company-title .zbs-view-company').off('click').on( 'click', function(){

				// get from hidden input
				var companyID = parseInt(jQuery("#zbsct_company").val());//jQuery(this).attr('data-invid');

				if (typeof companyID != "undefined" && companyID !== null && companyID !== ''){
					companyID = parseInt(companyID);
					if (companyID > 0){

						var url = '<?php echo zbsLink('edit',-1,'zerobs_company',true); ?>' + companyID;

						// bla bla https://stackoverflow.com/questions/1574008/how-to-simulate-target-blank-in-javascript
						window.open(url,'_parent');
					}
				}

			});
		}

		</script>
		<?php
	}


	function wpt_save_zbs_transaction_meta($post_id, $post) {

		 global $wp, $wpdb;  

		//Check it's not an auto save routine
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
		return;

		 
		 if( ! ( wp_is_post_revision( $post_id) || wp_is_post_autosave( $post_id ) ) ) {

	     if($post->post_type == 'zerobs_transaction'){

			remove_action('save_post', 'wpt_save_zbs_transaction_meta', 25 );

		    // Is the user allowed to edit the post or page?
		    if ( !current_user_can( 'edit_post', $post_id ))
		        return $post_id;
		    // OK, we're authenticated: we need to find and save the data
		    // We'll put it into an array to make it easier to loop though.
		    if (isset($_POST['zbs_hidden_flag'])) {

		    	#} #TransactionFields

                    // wh centralised 20/7/18 - 2.91+
				  	// save custom fields
	                global $zbsTransactionFields; 

                    	// had to add this, because other fields are saved separately here
                    	$skipList = array('orderid','customer','status','total','customer_name','date','currency','item','net','tax','fee','discount','tax_rate');                   

                    // retrieve _POST into arr (here we're just grabbing any custom fields... whole lot could be put through this if use $fields properly)
                    $zbo = zeroBSCRM_save_fields($zbsTransactionFields,'zbsct_',$skipList);

					$zbo['orderid'] = ''; 	if (isset($_POST['orderid'])) 	$zbo['orderid'] = sanitize_text_field($_POST["orderid"]);
					$zbo['currency'] = ''; 	if (isset($_POST['currency'])) 	$zbo['currency'] = sanitize_text_field($_POST["currency"]);
					$zbo['item'] = ''; 		if (isset($_POST["item"])) 		$zbo['item'] = sanitize_text_field($_POST["item"]);
					// Nope, should be saved against zbs_parent_cust
					//... storing in 2 places was causing issues 
					// just use zbs_parent_cust
					// $zbo['customer'] = ''; 	if(isset($_POST['customer'])) 	$zbo['customer'] = (int)sanitize_text_field($_POST["customer"]);
					$transactionCustomerID = ''; 	if(isset($_POST['customer'])) 	$transactionCustomerID = (int)sanitize_text_field($_POST["customer"]);

					// trans -> co wh added 20/7/18 2.91+
					$transactionCompanyID = ''; 	if(isset($_POST['zbsct_company'])) 	$transactionCompanyID = (int)sanitize_text_field($_POST["zbsct_company"]);

					if(isset($_POST['status'])){
				    	$zbo['status'] 		= 		sanitize_text_field($_POST["status"]);
					}
					$zbo['total'] 		= 		sanitize_text_field($_POST["total"]);

					if(isset($_POST['net'])){			
						$zbo['net'] 		= 		sanitize_text_field($_POST["net"]);
					}

					if(isset($_POST['tax'])){
						$zbo['tax'] 		= 		sanitize_text_field($_POST["tax"]);
					}

					if(isset($_POST['fee'])){
						$zbo['fee'] 		= 		sanitize_text_field($_POST["fee"]);
					}

					if(isset($_POST['discount'])){
						$zbo['discount'] 	= 		sanitize_text_field($_POST['discount']);
					}

					$zbo['trans_id'] = $post_id;

					#} any new one? 
					// dangerous to store this... as can update parent meta via meta key, but won't auto change this...
					// leaving in for now..
					$zbo['customer_name'] = sanitize_text_field( $_POST['customer_name'] );
			
					$zbo['trans_time'] = strtotime($post->post_date);					

				  	#} Invoice allocation:
					$zbo['invoice_id'] = ''; 	if(isset($_POST['invoice_id'])) 	$zbo['invoice_id'] = (int)sanitize_text_field($_POST["invoice_id"]);

				  	#} Update Meta Obj
			  		update_post_meta($post_id,'zbs_transaction_meta',$zbo);

			  		//we also need to add the transaction to the customer
			  		update_post_meta($post_id,'zbs_parent_cust', $transactionCustomerID);  //for querying orders by customer...
			  		update_post_meta($post_id,'zbs_parent_co', $transactionCompanyID);  //for querying orders by co...	  		

			  		$zbsCustomerInvoiceCustomer = $transactionCustomerID;

		            /*
		            	Needed for automations..  comments below are valid

						Didn't roll this out yet, it's complex because what if:
						1) inv created + no cust, then later cust added?   - THESE WILL SKIP
						2) inv created, assigned to cust, then later changed to new cust? (what about orphaned log?)  - LET CRM USER DELETE IT
						... to deal with next push

		            #} Is new transaction? (passed from metabox html) - ONLY adds if new transaction + has customer
		            #} Internal Automator
		            */

		        
		            	/*
		                zeroBSCRM_FireInternalAutomator('transaction.new',array(
		                    'id'=>$post_id,
		                    'againstid' => $zbsCustomerInvoiceCustomer,
		                    'transactionMeta'=> $zbo
		                    ));


							FOR NOW, REMOVED LOGGING RECIPE FROM IA AND FIRING BELOW, WH TO REWRITE LOGGING FUNC


		                   */

		    
		            // removed this, now below  do_action('zbs_new_transaction', $post_id); //fires new transaction hook. above was timing out my server. REALLY do think we use do_actions #noego.
					
				   //this was running too early (i.e. before the meta was stored)
					  	#} Retrieve Invoice ID to match to...
				  		#} Store it even if empty:

						#} if has an invoice, 
					  	if($zbo['invoice_id'] != ''){

					  		//store in it's own meta...
							update_post_meta($post_id, 'zbs_invoice_partials', $zbo['invoice_id']); 
							
							//function to check ammount due and mark invoice as paid if amount due <= 0.
							//the return of the invoice status here actually overwrote the transaction status
							zeroBSCRM_check_amount_due_mark_paid($zbo['invoice_id']);

					  		#} Presume you mean this way it's possible to search for partials against an inv...
					  		#} Later we'll need to rethink this for optimisation/clarity

				  		}





	                #} Is new transact? (passed from metabox html)
	                #} Internal Automator
	                if (isset($_POST['zbscrm_newtransaction']) && $_POST['zbscrm_newtransaction'] == 1){

	                	// stop it trying to assign to no customer :)
	                	if ($zbsCustomerInvoiceCustomer <= 0) $zbsCustomerInvoiceCustomer = false;

	                    zeroBSCRM_FireInternalAutomator('transaction.new',array(
	                        'id'=>$post_id,
	                        'againstid' => $zbsCustomerInvoiceCustomer,
	                        'transactionMeta'=> $zbo,
	                        'zbsid'=>$post_id
	                        ));
	                    
	                }

			  }
			  

			add_action('save_post', 'wpt_save_zbs_transaction_meta', 25, 2);

	   }

	} //end autosave and autorevision block.

	}
	//change priority
	add_action('save_post', 'wpt_save_zbs_transaction_meta', 25, 2); // save the custom fields

/* ======================================================
  / Transactions Metabox
   ====================================================== */



    #} Mark as included :)
    define('ZBSCRM_INC_TRANSMB',true);