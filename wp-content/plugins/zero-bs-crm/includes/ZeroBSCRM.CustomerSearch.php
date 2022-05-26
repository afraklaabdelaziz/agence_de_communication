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

   #} Your style func
	function zbscrm_customer_search_custom_css(){
		global $zbs;
		wp_enqueue_style( 'zbs-customer-search', ZEROBSCRM_URL.'css/ZeroBSCRM.admin.customersearch'.wp_scripts_get_suffix().'.css', array('customer-search-bs'), $zbs->version );
	}


   function zeroBSCRM_customersearch() {

   	global $zbs;

   	//brutal get company information (using DAL function)
   	//function zeroBS_getCustomers($withFullDetails=false,$perPage=10,$page=0,$withInvoices=false,$withQuotes=false,$searchPhrase='',$withTransactions=false,$argsOverride=false,$companyID=false){
   	$page = 0;
   	$perPage = 25;
   	$withInvoices = false;
   	$withQuotes = false;
   	$searchPhrase='';
   	$withTransactions = false;
   	$argsOverride = false;
   	$companyID = false;

   	//customer counts
   	
   	$zbs_customer_total = zeroBS_getCustomerCount();
   	$zbs_pages = ceil($zbs_customer_total / $perPage);

   	$zbs_customer_total_global = $zbs_customer_total;


   	$tagID ='';
   	
   	if(isset($_GET['zbs_tag']) && !empty($_GET['zbs_tag'])) $tagID = (int)sanitize_text_field($_GET['zbs_tag']);
   	if(isset($_GET['zbs_page']) && !empty($_GET['zbs_page'])) $page = (int)sanitize_text_field($_GET['zbs_page']);

   	if(isset($_POST['cust_search']) && !empty($_POST['cust_search'])) $searchPhrase = zeroBSCRM_textProcess($_POST['cust_search']);

   	$search_customers = zeroBS_getCustomers(true,$perPage,$page,$withInvoices,$withQuotes,$searchPhrase,$withTransactions,$argsOverride,$companyID,$tagID);
   	if(count($search_customers) > 0){
   		if(!empty($search_customers[0]['filterTot'])) $zbs_customer_total = $search_customers[0]['filterTot'];
		if (isset($search_customers) && isset($search_customers[0]) && isset($search_customers[0]['filterPages']))
			if($search_customers[0]['filterPages'] >= 0) $zbs_pages = $search_customers[0]['filterPages'];
	}else{
		$zbs_customer_total = 0;
	}
   	?>
   	<script type="text/javascript">
   	jQuery(function(){

	   	jQuery('#check_all').on( 'click', function(e){
	    	var table= jQuery('#customer_results');
	    	jQuery('td input:checkbox',table).prop('checked',this.checked);
		});

		jQuery('#export-idsxx').on( 'click', function(e){
			e.preventDefault();
			zbscrm_JS_check_checkboxes();
		});
	});

	 function zbscrm_JS_check_checkboxes() {         
	     var allVals = [];
	     jQuery('#checks :checked').each(function() {
	       allVals.push(jQuery(this).val());
	     });
	     if(allVals != ''){
	     	console.log('all OK');
	     	jQuery('#export-customers-form').submit();
	     }else{
	     	alert('Please select some customers');
	     	return false;
	     }

	}

   	</script>
   	<?php if(wp_is_mobile()){ ?>
   		<style>
   			.col-md-8{
   				width: 100%;
   			}
   			.zbs-s-sidebar{
			    top: initial !important;
			    bottom: initial !important;
			    background-color: #f9f9f9;
			    border: 1px solid #c6c6c6;
			    border-right: 1px solid #c6c6c6;
			    margin-top: 50px;
			    margin-bottom: 50px;
			    border-radius: 5px;
			    box-shadow: 0px 1px 4px rgba(0,0,0,0.3);
			    width:100%;
   			}
   		</style>
   	<?php } ?>


	    <style>
	    	.wp-heading-inline, .page-title-action, #screen-meta, #screen-options-link-wrap{
	    		display:none !important;
	    	}
		</style>





   		<div class='container'>
   				<div class='row customer-list' style="margin-right:70px;">
   					<div class='col-md-8 header-pad'>
   						<h4 class='customer-header'><?php _e('Export Contacts','zero-bs-crm'); ?></h4>
   						<div class='actions'>
   							<?php $val = __(" Export","zero-bs-crm"); ?>
   							<form method="post" id="export-customers-form" action="">
   							<?php wp_nonce_field( 'zbs_export', 'zbs_export_field' ); ?>
							<input type="submit" id="export-ids" name="download_cust_csv_filter" class="button-primary" value="<?php echo $val;?>" />
   						</div>
   						<table id="customer_results" class='table'>
   							<thead>
                  <tr>
  	   							<th class='cb'><input type="checkbox" id="check_all" name="check_all" value="all"/></th>
  	   							<th><?php _e('Contacts','zero-bs-crm'); ?></th>
  	   							<th><?php _e('Date Added',"zero-bs-crm"); ?></th>
  	   							<th><?php _e('Status',"zero-bs-crm"); ?></th>
                  </tr>
   							</thead>
   							<tbody id='checks'>
	   							<?php
	   								//zbs_prettyprint($search_customers);
	   								foreach($search_customers as $cust){
	   									$d = new DateTime($cust['created']);
	   									$formatted_date = $d->format(zeroBSCRM_getDateFormat());
	   									echo '<tr>';
	   									echo '<td class="cb"><input type="checkbox" name="cid[]" value="'.$cust['id'].'"/></td>';
	   									echo '<td><a href="'.zbsLink('view',$cust['id'],'zerobs_customer').'">' . $cust['name'] . '</a></td>';
	   									echo '<td>' . $formatted_date . '</td>';
	   									$status = ''; if (isset($cust) && isset($cust['status'])) $status = $cust['status'];
	   									echo '<td>' . $status . '</td>';
	   									echo '</tr>';
	   								}
	   							?>
   							</tbody>
   							</form>
   						</table>

   						<?php
   						/*
   						$pagLink = "<ul class='pagination'>";  
						for ($i=1; $i<=$zbs_pages; $i++) {  
									$zbsurl = get_admin_url('','edit.php?post_type=zerobs_customer&page=customer-searching') ."&zbs_page=".$i;
									if($page == $i-1){
										$pc = " class='active'";
									}else{
										$pc = "";
									}
						             $pagLink .= "<li".$pc."><a href='".$zbsurl."'>".$i."</a></li>";  
						};  
						echo $pagLink . "</ul>";  
						*/

						if($zbs_customer_total > 0){
					    $zbs_pagination = array(
					        'range'           => 4,
					        'count' 		  => $zbs_pages,
					        'page'			  => $page
					    );

						zeroBSCRM_pagination($zbs_pagination);
						}

						global $zbs;

						?>
		

						<div class='zbs_total pull-right'><h4><?php _e('Total: ',"zero-bs-crm");?><?php echo number_format($zbs_customer_total,0); ?></h4></div>


   					</div>
   					<div class='col-md-4 zbs-s-sidebar'>
   						<div class='section'>
	   						<div class='search-by'>
	   							<form action="#" method="POST">
	   								<?php
	   								if(!empty($searchPhrase)){
	   								$zbs_placeholder = $searchPhrase;
	   								}else{
	   									$zbs_placeholder = __('Search Contacts','zero-bs-crm');
	   								}	
	   								?>
	   								<input class='form-control' id='cust_search' name='cust_search' type='text' placeholder='<?php echo $zbs_placeholder;?>'/>
	   								<input type="submit" class='button-primary form-control zbs-submit' value="<?php _e('Search Contacts','zero-bs-crm'); ?>" />
	   							</form>
	   						</div>
   						</div>
   						<div class='section'>

   								<div class='import-buttons'>

		        <?php if(!zeroBSCRM_isExtensionInstalled('csvpro')){ ?>
							<a href="<?php echo admin_url('admin.php?page='.$zbs->slugs['csvlite']);?>"><div class='button import-customers'><?php _e("Import Contacts", "zero-bs-crm"); ?></div></a>
		        <?php } ;?>

   				
   									
   									<a href="<?php echo zbsLink('create',-1,'zerobs_customer');?>"><div class='button add-new-customer'>+</div></a>
   								</div>

   						</div>
   						<div class='mid-banner'>
   							<?php
							$zbstermc = sprintf( _n( '%s Contact', '%s Contacts', $zbs_customer_total_global, 'zero-bs-crm' ), $zbs_customer_total_global);

   							?>
   							<a href='<?php echo zbsLink($zbs->slugs['customer-search']);?>'><span class='lead'><?php _e('All Contacts', 'zero-bs-crm');?></span><span class='sub-count'><?php echo $zbstermc; ?></span></a>
   						</div>
   						<div class='section'>
   							<h5><i class='fa fa-tags'></i> <?php _e("Tags","zero-bs-crm");?></h5>
   							<ul id='customer-search-tags'>
   							<?php

   								#} Tags - WH modernised db2

					      		$tags = zeroBSCRM_getContactTagsArr(false);

								foreach($tags as $tag){
									$zbsurl = get_admin_url('','admin.php?&page=customer-searching') ."&zbs_tag=".$tag['id'];
									$zbstermc = sprintf( _n( '%s Contact', '%s Contacts', $tag['count'], 'zero-bs-crm' ), $tag['count']);

									echo "<li><a href='".$zbsurl."'>". $tag['name'] . "<span class='sub-count'>" .$zbstermc. "</span></a></li>";
								}
   							?>
   							<li><a class='under' href='<?php echo get_admin_url('','admin.php?page='.$zbs->slugs['tagmanager'].'&tagtype=contact');?>'><?php _e("+ Create a Tag","zero-bs-crm"); ?></a></li>
   							</ul>
   						</div>
   					</div>
   					<div class='clear'></div>
   				</div>
   		</div>
   		<?php
   }