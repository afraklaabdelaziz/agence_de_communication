<?php 
/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 * V1.20
 *
 * Copyright 2020 Automattic
 *
 * Date: 01/11/16
 */

/* ======================================================
  Breaking Checks ( stops direct access )
   ====================================================== */
    if ( ! defined( 'ZEROBSCRM_PATH' ) ) exit;
/* ======================================================
  / Breaking Checks
   ====================================================== */


/* ======================================================
	
	v3.0 Notice

		Note: from v3.0 this becomes legacy
		... to be replaced with ZeroBSCRM.DAL3.Export.php


====================================================== */


/* ======================================================
  Export Tools
   ====================================================== */

function zeroBSCRM_export_tools_header(){

	global $zbs;

	$b2bMode = zeroBSCRM_getSetting('companylevelcustomers');
	if ( $b2bMode == 1 ){
		$zbs_cust = 'Export Contacts';
		$export_items = array(jpcrm_label_company(true), 'Contacts', 'Invoices','Quotes','Transactions');
	}else{
		$zbs_cust = 'Export Contacts';
		$export_items = array('Contacts', 'Invoices','Quotes', 'Transactions');
	}

	$url = admin_url('admin.php?page='.$zbs->slugs['legacy-zbs-export-tools']); 

	
	$ex = __('Export',"zero-bs-crm"); 

	echo "<ul class='zbs-export-links'>";
		$i = 0;
		foreach($export_items as $export){
			echo "<li><a href='".$url."&zbswhat=".strtolower($export)."'>" . $ex . " ". $export . "</a></li>";
			if($i < count($export_items)-1){
				echo " | ";
			}
			$i++;
		}
	echo "</ul>";
}


function zeroBSCRM_pages_export_tools(){ ?>


	<div class="ui segment" style="margin-right:25px;">

	<style>
		.zbs-export-links{

		}
		.zbs-export-links li{
			display:inline-block;
		}
		input[type="checkbox"]{
			padding:5px;
		}
	</style>
	<?php

	zeroBSCRM_export_tools_header();

	if(isset($_GET['zbswhat']) && $_GET['zbswhat'] == 'companies'){

		$b2bMode = zeroBSCRM_getSetting('companylevelcustomers');
		if ( $b2bMode == 1 ){
			$zbs_cust = 'Export Contacts';
		}else{
			$zbs_cust = 'Export Customers';
		}

		if ( $b2bMode == 1 ){

			$co = zeroBS_companyCount();
			echo "<p>";
			_e("Choose which fields you want to export. Total ".jpcrm_label_company(true)." in database: ","zero-bs-crm"); 
			echo "<b>" . $co . "</b>";
			echo "</p>";

			echo "<div class='postbox'><div class='inside'>";
			echo '<form method="post" id="download_form" action="">';
			?>
	        <script type="text/javascript">
	        jQuery(function(){


			jQuery('#select-all').on( 'click', function(event) {   
				 if(this.checked) {
				      // Iterate each checkbox
				      jQuery(':checkbox').each(function() {
				          this.checked = true;
				      });
				  }
				  else {
				    jQuery(':checkbox').each(function() {
				          this.checked = false;
				      });
				  }
			});

			});
			</script>

			<input type="checkbox" id="select-all" /> Toggle All<br/>

			<hr/>

			<?php

	   		global $zbsCompanyFields;
	        $fields = $zbsCompanyFields;
	    //    zbs_prettyprint($fields);
	        $useSecondAddr = zeroBSCRM_getSetting('secondaddress');
			global $zbsFieldsEnabled; if ($useSecondAddr == '1') $zbsFieldsEnabled['secondaddress'] = true;

			// ID
			echo '<input type="checkbox" name="zbs_export_group[]" value="id" /> ID<br />';


	                  foreach ($fields as $fieldK => $fieldV){

	                        $showField = true;

	                        #} Check if not hard-hidden by opt override (on off for second address, mostly)
	                        if (isset($fieldV['opt']) && (!isset($zbsFieldsEnabled[$fieldV['opt']]) || !$zbsFieldsEnabled[$fieldV['opt']])) $showField = false;


	                        // or is hidden by checkbox? 
	                        if (isset($fieldHideOverrides['company']) && is_array($fieldHideOverrides['company'])){
	                            if (in_array($fieldK, $fieldHideOverrides['company'])){
	                              $showField = false;
	                            }
	                        }


	                        #} If show...
	                        if ($showField) {


	                        	echo '<input type="checkbox" name="zbs_export_group[]" value="'.$fieldK.'" /> '.$fieldV[1].'<br />';


	                        } #} / if show


	                 
	                    }

		 		?>
				<br/>
		       <input type="submit" name="download_co_csv" class="button-primary" value="<?php echo __('Export','zero-bs-crm').' '.jpcrm_label_company(true); ?>" />


		       <?php wp_nonce_field( 'zbs_export', 'zbs_export_field' ); ?>


		    </form>
		    </div>
		    </div>
    	<?php }
	}

	if(isset($_GET['zbswhat']) && $_GET['zbswhat'] == 'contacts'){

			$zbs_cust = 'Contacts';

			$co = zeroBS_customerCount();
			echo "<p>";
			_e("Choose which fields you want to export. Total ". $zbs_cust ." in database: ","zero-bs-crm"); 
			echo "<b>" . $co . "</b>";
			echo "</p>";

			echo "<div class='postbox'><div class='inside'>";
			echo '<form method="post" id="download_form" action="">';
			?>
	        <script type="text/javascript">
	        jQuery(function(){


			jQuery('#select-all').on( 'click', function(event) {   
				 if(this.checked) {
				      // Iterate each checkbox
				      jQuery(':checkbox').each(function() {
				          this.checked = true;
				      });
				  }
				  else {
				    jQuery(':checkbox').each(function() {
				          this.checked = false;
				      });
				  }
			});

			});
			</script>

			<input type="checkbox" id="select-all" /> Toggle All<br/>

			<hr/>

			<?php
            
            global $zbsCustomerFields;
            $fields = $zbsCustomerFields;
	    //    zbs_prettyprint($fields);
	        $useSecondAddr = zeroBSCRM_getSetting('secondaddress');
			global $zbsFieldsEnabled; if ($useSecondAddr == '1') $zbsFieldsEnabled['secondaddress'] = true;

			// ID
			echo '<input type="checkbox" name="zbs_export_group[]" value="id" /> ID<br />';

	                  foreach ($fields as $fieldK => $fieldV){

	                        $showField = true;

	                        #} Check if not hard-hidden by opt override (on off for second address, mostly)
	                        if (isset($fieldV['opt']) && (!isset($zbsFieldsEnabled[$fieldV['opt']]) || !$zbsFieldsEnabled[$fieldV['opt']])) $showField = false;


	                        // or is hidden by checkbox? 
	                        if (isset($fieldHideOverrides['company']) && is_array($fieldHideOverrides['company'])){
	                            if (in_array($fieldK, $fieldHideOverrides['company'])){
	                              $showField = false;
	                            }
	                        }


	                        #} If show...
	                        if ($showField) {


	                        	echo '<input type="checkbox" name="zbs_export_group[]" value="'.$fieldK.'" /> '.$fieldV[1].'<br />';


	                        } #} / if show


	                 
	                    }

		 		?>
				<br/>
				<?php wp_nonce_field( 'zbs_export', 'zbs_export_field' ); ?>
		       <input type="submit" name="download_cust_csv" class="button-primary" value="<?php _e('Export Contacts', 'zero-bs-crm'); ?>" />

		    </form>
		    </div>
		    </div>
    	<?php 
	}

	if(isset($_GET['zbswhat']) && $_GET['zbswhat'] == 'invoices'){

			$co = zeroBS_invCount();
			echo "<p>";
			_e("Total Invoices in database: ","zero-bs-crm"); 
			echo "<b>" . $co . "</b>";
			echo "</p>";
			?>

		<form method="post" id="download_form" action="">
			<?php wp_nonce_field( 'zbs_export', 'zbs_export_field' ); ?>
	         <input type="submit" name="download_inv_csv" class="button-primary" value="<?php _e('Export Invoices', 'zero-bs-crm'); ?>" />
	    </form>
    	<?php 
	}

	if(isset($_GET['zbswhat']) && $_GET['zbswhat'] == 'quotes'){

			$co = zeroBS_quoCount();
			echo "<p>";
			_e("Total Quotes in database: ","zero-bs-crm"); 
			echo "<b>" . $co . "</b>";
			echo "</p>";
			?>

		<form method="post" id="download_form" action="">
			<?php wp_nonce_field( 'zbs_export', 'zbs_export_field' ); ?>
	            <input type="submit" name="download_quo_csv" class="button-primary" value="<?php _e('Export Quotes', 'zero-bs-crm'); ?>" />
	    </form>
    	<?php 
	}


	if(isset($_GET['zbswhat']) && $_GET['zbswhat'] == 'transactions'){

			$co = zeroBS_tranCount();
			echo "<p>";
			_e("Total Transactions in database: ","zero-bs-crm"); 
			echo "<b>" . $co . "</b>";
			echo "</p>";
			?>

		<form method="post" id="download_form" action="">
			<?php wp_nonce_field( 'zbs_export', 'zbs_export_field' ); ?>
	            <input type="submit" name="download_trans_csv" class="button-primary" value="<?php _e('Export Transactions', 'zero-bs-crm'); ?>" />
	    </form>
    	<?php 
	}
?>

</div>

<?php

}



//export tools
function zeroBSCRM_pages_export(){
	$zbscontent = null;
    ob_start();
	
	$zbs_cust = 'Export Contacts';

	?>

	<br/>
	<a class='ui button' href='<?php echo admin_url('admin.php?page=zbs-export-tools&zbswhat=customers'); ?>'><?php _e('Export Tools',"zero-bs-crm"); ?></a>


	<?php
    $zbscontent = ob_get_contents();
    ob_end_clean();
    return $zbscontent;

}

//export CSV function

function zbs_export_companies(){
	global $plugin_page;


		if ( isset($_POST['download_co_csv']) && $plugin_page == 'zbs-export-tools' ) {

			//kill it here for now
			//die();

			if ( 
			    ! isset( $_POST['zbs_export_field'] ) 
			    || ! wp_verify_nonce( $_POST['zbs_export_field'], 'zbs_export' ) 
			) {

			   print __('Sorry, your nonce did not verify.',"zero-bs-crm");
			   exit;

			} else {

			

			if(zeroBSCRM_permsCustomers()){
				header('Content-Type: text/csv; charset=utf-8');		
				$zbs_cust = 'Export '.jpcrm_label_company(true);
				$filename = 'CRM-Companies.csv';		
				header('Content-Disposition: attachment; filename= ' . $filename);
				$output = fopen('php://output', 'w');


				$zbs_export_fields = array(); $zbs_export_fieldTitles = array();
				if(isset($_POST['zbs_export_group']) && !empty($_POST['zbs_export_group'])){
					$zbs_export_fields = sanitize_text_field($_POST['zbs_export_group']);

					// because of SYLK bug in excel, we have to wrap these in "" - but fputcsv doesnt do it :/
					// https://www.alunr.com/excel-csv-import-returns-an-sylk-file-format-error/
					// https://stackoverflow.com/questions/2489553/forcing-fputcsv-to-use-enclosure-for-all-fields
					// ... so
					// THIS doesn't even work 
					// ... if (is_array($zbs_export_fields)) $zbs_export_fields = zeroBSCRM_encloseArrItems($zbs_export_fields,'"');
					// quick hack then..
					if (is_array($zbs_export_fields)){

						$zbs_export_fieldTitles = array();
						foreach ($zbs_export_fields as $k => $v){

							// add space to it :(
							$nV = $v; if ($v == 'ID' || $v == 'id') $nV = 'ID ';

							$zbs_export_fieldTitles[$k] = $nV;
						}

					}
					// TO FIX PROPERLY (stop using fputcsv)

					fputcsv($output, $zbs_export_fieldTitles);

					$companies = zeroBS_getCompanies(false,10000,0,'',false,false,false,false);

					foreach($companies as $company){
						$zbsMeta = get_post_meta($company['id'],'zbs_company_meta',true);

						//build the output array
						$line_array = array();
						if (isset($zbs_export_fields) && is_array($zbs_export_fields)) foreach($zbs_export_fields as $field){

							$line_array[$field] = '';
							
							// meta
							if (isset($zbsMeta[$field])) $line_array[$field] = $zbsMeta[$field];

							// catch id :)
							if ($field == 'id') $line_array[$field] = $company['id'];
						}

					/*	$out = array($company['name'],$zbsMeta['addr1'],$zbsMeta['addr2'],$zbsMeta['city'],$zbsMeta['county'],$zbsMeta['postcode'],$zbsMeta['maintel'],$zbsMeta['sectel'], $zbsMeta['email'],$zbsMeta['notes'], count($customer['contacts']), count($customer['quotes']), count($customer['invoices']),count($customer['transactions']),$totalVal );
					*/

						$out = $line_array;

						fputcsv($output, $out);
						}
				}
			}
			die();
		}

	}

}
add_action('admin_init','zbs_export_companies');


function zbs_export_customers(){
	global $plugin_page;
	if ( isset($_POST['download_cust_csv']) && $plugin_page == 'zbs-export-tools' ) {


		if ( 
			    ! isset( $_POST['zbs_export_field'] ) 
			    || ! wp_verify_nonce( $_POST['zbs_export_field'], 'zbs_export' ) 
			) {

			   print __('Sorry, your nonce did not verify.',"zero-bs-crm");
			   exit;

			} else {

		if(zeroBSCRM_permsCustomers()){

			global $zbs;

		header('Content-Type: text/csv; charset=utf-8');	
		$zbs_cust = 'Export Contacts';
		$filename = 'CRM-Customers.csv'; 	
		header('Content-Disposition: attachment; filename= ' . $filename);
		$output = fopen('php://output', 'w');

		if(isset($_POST['zbs_export_group']) && !empty($_POST['zbs_export_group'])){

			$zbs_export_fields = sanitize_text_field($_POST['zbs_export_group']);
			$zbs_export_fields_nor = sanitize_text_field($_POST['zbs_export_group']);

			$zbs_added = __('added',"zero-bs-crm");
			array_push($zbs_export_fields, $zbs_added);

			$zbs_export_fields['total_val'] = 0;

			// because of SYLK bug in excel, we have to wrap these in "" - but fputcsv doesnt do it :/
			// https://www.alunr.com/excel-csv-import-returns-an-sylk-file-format-error/
			// https://stackoverflow.com/questions/2489553/forcing-fputcsv-to-use-enclosure-for-all-fields
			// ... so
			// THIS doesn't even work 
			// ... if (is_array($zbs_export_fields)) $zbs_export_fields = zeroBSCRM_encloseArrItems($zbs_export_fields,'"');
			// quick hack then..
			$zbs_export_fieldTitles = array();
			if (is_array($zbs_export_fields)){

				$zbs_export_fieldTitles = array();
				foreach ($zbs_export_fields as $k => $v){

					// add space to it :(
					$nV = $v; if ($v == 'ID' || $v == 'id') $nV = 'ID ';

					$zbs_export_fieldTitles[$k] = $nV;
				}

			}
			// TO FIX PROPERLY (stop using fputcsv)

			// headers
			fputcsv($output, $zbs_export_fieldTitles);

			$customers = zeroBS_getCustomers(true,100000,0,true,true,'',true,false,false);

			foreach($customers as $customer){
				
				// DAL 2 now :)
				if (!$zbs->isDAL2())
					$zbsContact = get_post_meta($customer['id'],'zbs_customer_meta',true);
				else
					$zbsContact = zeroBS_getCustomer($customer['id']);
				/*
				$totalVal = zeroBS_customerTotalValue($customer['id'],$customer['invoices'],$customer['transactions']);	
				$out = array($zbsMeta['status'], $zbsMeta['prefix'] ,$zbsMeta['fname'], $zbsMeta['lname'],$zbsMeta['addr1'],$zbsMeta['addr2'],$zbsMeta['city'],$zbsMeta['county'],$zbsMeta['postcode'],$zbsMeta['hometel'],$zbsMeta['worktel'],$zbsMeta['mobtel'], $zbsMeta['email'],$zbsMeta['notes'], count($customer['quotes']), count($customer['invoices']),count($customer['transactions']),$totalVal );
				fputcsv($output, $out);
				*/

				//build the output array
				$line_array = array();
				foreach($zbs_export_fields_nor as $field){

					$line_array[$field] = '';

					// meta
					if (isset($zbsContact[$field])) $line_array[$field] = $zbsContact[$field];

					// catch id :)
					if ($field == 'id') $line_array[$field] = $customer['id'];

					
				}
				$line_array[$zbs_added] = $customer['created'];

				// EXPORTTOMODERNISETODAL3
				$totalVal = zeroBS_customerTotalValue($customer['id'],$customer['invoices'],$customer['transactions']);
				$line_array['total_val'] = $totalVal;

				$out = $line_array;
				fputcsv($output, $out);


				}
		}

		}
		die();


		}
	}
}
add_action('admin_init','zbs_export_customers');

function zbs_export_customers_filter(){
		global $plugin_page;

		if ( isset($_POST['download_cust_csv_filter']) && $plugin_page == 'customer-searching' ) {


			if ( 
			    ! isset( $_POST['zbs_export_field'] ) 
			    || ! wp_verify_nonce( $_POST['zbs_export_field'], 'zbs_export' ) 
			) {

			   print __('Sorry, your nonce did not verify.',"zero-bs-crm");
			   exit;

			} else {


		if(zeroBSCRM_permsCustomers()){

			global $zbs;

			header('Content-Type: text/csv; charset=utf-8');		
			$b2bMode = zeroBSCRM_getSetting('companylevelcustomers');
			$zbs_cust = 'Export Contacts';


		if(!isset($_POST['cid']) && empty($_POST['cid'])){
			$inArr = '';
		}else{
			$inArr = (int)sanitize_text_field($_POST['cid']);
		}


		$filename = 'CRM.csv';		
		header('Content-Disposition: attachment; filename= ' . $filename);
		$output = fopen('php://output', 'w');

		fputcsv($output, array('ID ','Status','Prefix', 'First Name', 'Last Name', 'Address Line 1', 'Address Line 2','City','County','Postcode','Home Tel','Work Tel','Mobile', 'Email','Notes','Quotes', 'Invoices','Transactions','Total Value'));
		$customers = zeroBS_getCustomers(true,100000,0,true,true,'',true,false,false,'', $inArr);
		
		// wh quick add for isset check
		$load = array('status','prefix','fname','lname','addr1','addr2','city','county','postcode','hometel','worktel','mobtel','email','notes');

		foreach($customers as $customer){


				// DAL 2 now :)
				if (!$zbs->isDAL2())
					$zbsContact = get_post_meta($customer['id'],'zbs_customer_meta',true);
				else
					$zbsContact = zeroBS_getCustomer($customer['id']);

				//$zbsMeta = get_post_meta($customer['id'],'zbs_customer_meta',true);


			$totalVal = zeroBS_customerTotalValue($customer['id'],$customer['invoices'],$customer['transactions']);

				// in case empty				
				foreach ($load as $fieldKey) {
					$$fieldKey = ''; if (isset($zbsContact[$fieldKey])) $$fieldKey = $zbsContact[$fieldKey];
				}
			$out = array($customer['id'],$status,$prefix,$fname,$lname,$addr1,$addr2,$city,$county,$postcode,$hometel,$worktel,$mobtel,$email,$notes, count($customer['quotes']), count($customer['invoices']),count($customer['transactions']),$totalVal );
			//$out = array($zbsMeta['status'], $zbsMeta['prefix'] ,$zbsMeta['fname'], $zbsMeta['lname'],$zbsMeta['addr1'],$zbsMeta['addr2'],$zbsMeta['city'],$zbsMeta['county'],$zbsMeta['postcode'],$zbsMeta['hometel'],$zbsMeta['worktel'],$zbsMeta['mobtel'], $zbsMeta['email'],$zbsMeta['notes'], count($customer['quotes']), count($customer['invoices']),count($customer['transactions']),$totalVal );
			fputcsv($output, $out);
			}

		}
		die();
		}
	}
}
add_action('admin_init','zbs_export_customers_filter');



// zeroBS_getTransactions

function zbs_export_transactions(){
	global $plugin_page;
	if ( isset($_POST['download_trans_csv']) && $plugin_page == 'zbs-export-tools' ) {

			if ( 
			    ! isset( $_POST['zbs_export_field'] ) 
			    || ! wp_verify_nonce( $_POST['zbs_export_field'], 'zbs_export' ) 
			) {

			   print __('Sorry, your nonce did not verify.',"zero-bs-crm");
			   exit;

			} else {

		if(zeroBSCRM_permsTransactions()){

		header('Content-Type: text/csv; charset=utf-8');		

		$filename = 'CRM-Transactions.csv';		
		header('Content-Disposition: attachment; filename= ' . $filename);
		$output = fopen('php://output', 'w');

		$zbs_export_fields = array(
			"id",
			"created",
			"item",
			"total",
			"customer",
			"fname",
			"lname",
			"email",
			"custcreate"
		);


		fputcsv($output, $zbs_export_fields);

		$transactions = zeroBS_getTransactions(true,100000,0,true);
		foreach($transactions as $transaction){
			$line_array = array();
			$line_array['id'] = $transaction['id'];
			$line_array['created']  = $transaction['created'];
			$line_array['item'] = $transaction['meta']['item'];
			$line_array['total'] = $transaction['meta']['total'];
			$line_array['customer'] = $transaction['customerid'];
			$line_array['fname'] = $transaction['customer']['fname'];
			$line_array['lname'] = $transaction['customer']['lname'];
			$line_array['email'] = $transaction['customer']['email'];
			$line_array['custcreate'] = $transaction['customer']['created'];

			//build the output array
			

			$out = $line_array;
			fputcsv($output, $out);


			}
	}

	}
	die();
	}
}
add_action('admin_init','zbs_export_transactions');


function zbs_export_invoices(){
	global $plugin_page;
	if ( isset($_POST['download_inv_csv']) && $plugin_page == 'zbs-export-tools' ) {


	if ( 
			    ! isset( $_POST['zbs_export_field'] ) 
			    || ! wp_verify_nonce( $_POST['zbs_export_field'], 'zbs_export' ) 
			) {

			   print __('Sorry, your nonce did not verify.',"zero-bs-crm");
			   exit;

			} else {


		if(zeroBSCRM_permsInvoices()){
			header('Content-Type: text/csv; charset=utf-8');		
			$filename = 'CRM-Invoices-Export-'. date('Ymd') .'.csv';		
			header('Content-Disposition: attachment; filename= ' . $filename);
			$output = fopen('php://output', 'w');
			fputcsv($output, array('Number','Status', 'Customer Name',jpcrm_label_company().' Name', 'Value', 'Created'));		
			$invoices = zeroBS_getInvoices(true,1000000,0,true);
			foreach($invoices as $invoice){

				if(empty($invoice['meta']['status'])){
					$stat = 'Draft';
				}else{
					$stat = $invoice['meta']['status'];
				}
				$who = $invoice['customer']['fname'] . ' ' . $invoice['customer']['lname'];

				$whocID = get_post_meta($invoice['id'],'zbs_company_invoice_company',true);

				$coMeta = get_post_meta($whocID,'zbs_company_meta',true);
				$whoc = ''; if (is_array($coMeta) && isset($coMeta['coname'])) $whoc = $coMeta['coname'];

				if(empty($invoice['meta']['val'])){
					$val = 0;
				}else{
					$val = $invoice['meta']['val'];
				}

					#TRANSITIONTOMETANO proper id's
					$invoiceID = $invoice['id']; #} WP POST ID, really!
					if (isset($invoice['zbsid']) && !empty($invoice['zbsid'])) $invoiceID = $invoice['zbsid']; #} PROPER ID!

				$out = array($invoiceID, $stat , $who, $whoc, $val, $invoice['created']);
				fputcsv($output, $out);
			}
		}
		die();
	}
	}
}
add_action('admin_init','zbs_export_invoices');

function zbs_export_quotes(){
	global $plugin_page;
	if ( isset($_POST['download_quo_csv']) && $plugin_page == 'zbs-export-tools') {


	if ( 
			    ! isset( $_POST['zbs_export_field'] ) 
			    || ! wp_verify_nonce( $_POST['zbs_export_field'], 'zbs_export' ) 
			) {

			   print __('Sorry, your nonce did not verify.',"zero-bs-crm");
			   exit;

			} else {



		if(zeroBSCRM_permsQuotes()){
			header('Content-Type: text/csv; charset=utf-8');	
			$filename = 'CRM-Quotes-Export-'. date('Ymd') .'.csv';		
			header('Content-Disposition: attachment; filename= ' . $filename);
			$output = fopen('php://output', 'w');

			fputcsv($output, array('Created','Number', 'Customer Name', 'Value','Notes'));
			
			$quotes = zeroBS_getQuotes(true,1000000,0,true);

			foreach($quotes as $quote){
				if(empty($quote['meta']['notes'])){
					$notes = '';
				}else{
					$notes = $quote['meta']['notes'];
				}
				$who = $quote['customer']['fname'] . ' ' . $quote['customer']['lname'];
				if(empty($quote['meta']['val'])){
					$val = 0;
				}else{
					$val = $quote['meta']['val'];
				}

					#TRANSITIONTOMETANO proper id's
					$quoteID = $quote['id']; #} WP POST ID, really!
					if (isset($quote['zbsid']) && !empty($quote['zbsid'])) $quoteID = $quote['zbsid']; #} PROPER ID!

				$out = array($quote['created'], $quoteID , $who, $val, $notes);
				fputcsv($output, $out);
			}
		}
		die();
	}
	}
}
add_action('admin_init','zbs_export_quotes');

/* ======================================================
  / Export Tools
   ====================================================== */
