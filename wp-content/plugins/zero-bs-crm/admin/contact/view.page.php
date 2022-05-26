<?php 
/*!
 * Single contact view page
 */
defined( 'ZEROBSCRM_PATH' ) || exit;

global $zbs;

/**
 * Render the page
 */
function jpcrm_render_contact_view_page( $id = -1 ){

  // generic php-js for view pages (lang labels)
  zeroBSCRM_pages_admin_contact_view_page_js();

  if (!empty($id) && $id > 0){

      global $zbs;

      $useQuotes    = zeroBSCRM_getSetting('feat_quotes');
      $useInvoices  = zeroBSCRM_getSetting('feat_invs');
      $useTrans     = zeroBSCRM_getSetting('feat_transactions');
      $useTasks     = zeroBSCRM_getSetting('feat_calendar');

      // Retrieve contact
      $contact = $zbs->DAL->contacts->getContact( $id, array(        
        'withCustomFields'   => true,
        'withQuotes'         => true,
        'withInvoices'       => true,
        'withTransactions'   => true,
        'withTasks'         => true,
        'withLogs'          => true,
        'withLastLog'       => false,
        'withTags'          => true,
        'withCompanies'     => true,
        'withOwner'         => true,
        'withValues'        => true,
        'withExternalSourcesGrouped' => true,

        // but we limit to the top 20 (quotes, invs, trans etc.)
        // note that this means we have to add calls to contactHasCountObjType, but it protects against contacts with 1000 objs etc.
        // Note this is defunct until we add contact filters to our object list views.
        //'withObjLimit' => 20,

        'ignoreowner'        => zeroBSCRM_DAL2_ignoreOwnership(ZBS_TYPE_CONTACT)

      ));

      $contact_actions = zeroBS_contact_actions($id,$contact);

      // if customer id provided, but no obj, don't load any further.
      // this matches the ghost-record treatment in the single edit.php class
      if ( !is_array( $contact ) ){

            // brutal hide, then msg #ghostrecord
            ?><style type="text/css">#zbs-edit-save, #zbs-nav-view, #zbs-nav-prev, #zbs-nav-next { display:none; }</style>
            <div id="zbs-edit-warnings-wrap"><?php
            echo zeroBSCRM_UI2_messageHTML('warning','Error Retrieving Contact','There does not appear to be a Contact with this ID.','disabled warning sign','zbsCantLoadData');  
            ?></div><?php  
            return false;

      }

      // contact obj counts
      $contact_quote_count = $zbs->DAL->contacts->contactHasCountObjType( $id, ZBS_TYPE_QUOTE );
      $contact_invoice_count = $zbs->DAL->contacts->contactHasCountObjType( $id, ZBS_TYPE_INVOICE );
      $contact_transaction_count = $zbs->DAL->contacts->contactHasCountObjType( $id, ZBS_TYPE_TRANSACTION );
      $contact_task_count = $zbs->DAL->contacts->contactHasCountObjType( $id, ZBS_TYPE_EVENT );
      
      // socials
      global $zbsSocialAccountTypes;
      $contact_socials = zeroBS_getCustomerSocialAccounts( $id );
      // empty empties.. hmmm
      $contact_socials_array = array(); 
      if ( is_array( $contact_socials ) && count( $contact_socials ) > 0 ){
        foreach ( $contact_socials as $zbsSocialKey => $zbsSocialAcc ){
          if ( !empty( $zbsSocialAcc ) ){
            $contact_socials_array[$zbsSocialKey] = $zbsSocialAcc;
          }
        }
      }
      $contact_socials = $contact_socials_array; 
      unset( $contact_socials_array );

      // email
      $contact_email = '';
      if ( isset( $contact['email'] ) ) {
          $contact_email = $contact['email'];
      }

      // external sources
      $contact_external_source_count = 0;
      $contact_has_external_sources = ( is_array( $contact['external_sources'] ) && count( $contact['external_sources'] ) > 0 );      
      if ( $contact_has_external_sources ) {        
        foreach ( $contact['external_sources'] as $external_source_key => $external_source_group_sources ) {
          $contact_external_source_count += count( $external_source_group_sources );
        }
      }

      // avatar mode
      $avatar_mode = zeroBSCRM_getSetting( 'avatarmode' );
      $avatar = '';
      if ( $avatar_mode !== "3" ) {
          $avatar = zeroBS_customerAvatarHTML( $id, $contact, 100, 'ui small image centered' );
      }

      // check flags (atm this is just 'do-not-email' 2.90+)
      $contact_flags = array();
      if ( $zbs->DAL->contacts->getContactDoNotMail( $id ) ) {
          $contact_flags[] = 'do-not-email';
      }

          ?>
          <div class="ui divided grid" style="margin-top:-1em;">

            <div class="ten wide column" id="zbs-customer-panel">

              <div class="ui segment grid">

              <?php # based on avatar/no avatar, subtle diff design here:
              if ($avatar_mode == "3" || empty($avatar)){
                
                  // 1 column, no avatar card
                  ?><div class="sixteen wide column zbs-view-card"><?php

              } else {

                  // normal, 2 column 'contact card'
                  ?><div class="three wide column" style="text-align:center">
                      <?php echo $avatar; ?>
                        <a class="ui button green" style="margin-top:0.8em" href="<?php echo zbsLink('edit',$id,'zerobs_customer',false);?>">
                            <?php _e("Edit Contact", "zero-bs-crm"); ?>
                        </a>

                    </div>
                    <div class="thirteen wide column zbs-view-card"><?php


              } ?>

                      <h3>
                        <?php echo zeroBS_customerName('',$contact,false,false); ?>
                        <?php #} When no avatar, show edit button top right
                          if ($avatar_mode == "3" || empty($avatar)){
                            ?><a class="ui button green right floated" style="margin-top:0.8em" href="<?php echo zbsLink('edit',$id,'zerobs_customer',false);?>">
                                  <?php _e("Edit Contact", "zero-bs-crm"); ?>
                              </a><?php
                          }
                        ?>
                      </h3>
                      <p class="zbs-email">
                        <?php zeroBSCRM_html_sendemailto($id,$contact_email,false); ?>
                        <input type="hidden" id="email" value="<?php echo $contact_email; ?>" />
                      </p>

                      <div class="zbs-social-buttons">
                          <?php 
                            if (count($zbsSocialAccountTypes) > 0 && count($contact_socials) > 0){ 
                              foreach ($zbsSocialAccountTypes as $socialKey => $socialAccType){
                                 if (is_array($contact_socials) && isset($contact_socials[$socialKey]) && !empty($contact_socials[$socialKey])){ 
                                        // got acc? link to it
                                        $socialLink = zeroBSCRM_getSocialLink($socialKey,$contact_socials);
                                        //added so it outputs tw => twitter, fb => facebook
                                        $semanticSocial = zeroBSCRM_getSocialIcon($socialKey);
                                        ?>
                                        <a class="ui mini <?php echo $semanticSocial; ?> button" href="<?php echo $socialLink; ?>" target="_blank" title="<?php echo __('View',"zero-bs-crm").' '.$contact_socials[$socialKey]; ?>"><i class="large middle aligned ui <?php echo zeroBSCRM_faSocialToSemantic($socialAccType['fa']); ?>" aria-hidden="true" style="padding-top:0"></i></a>
                                    <?php } 
                              }
                            }
                          ?>
                      </div>
                      <div class='clear'></div>


                      <p class="zbs-sentence">
                        <?php echo zeroBSCRM_html_contactIntroSentence($contact); ?>
                      </p>


                      <?php # https://codepen.io/kyleshockey/pen/bdeLrE 
                      if (count($contact_actions) > 0) { ?>
                      <div class="action-wrap">
                        <div class="ui green basic dropdown action-button"><?php _e('Contact Actions',"zero-bs-crm"); ?><i class="dropdown icon"></i>
                           <div class="menu">
                            <?php foreach ($contact_actions as $actKey => $action){ ?>
                               <div class="item zbs-contact-action" id="zbs-contact-action-<?php echo $actKey; ?>"<?php
                                // if url isset, pass that data-action, otherwise leave for js to attach to
                                if (isset($action['url']) && !empty($action['url'])){ 
                                  ?> data-action="<?php if (isset($action['url'])) echo 'url'; ?>" data-url="<?php if (isset($action['url'])) echo $action['url']; ?>"<?php
                                }

                                // got extra attributes?
                                if (isset($action['extraattr']) && is_array($action['extraattr'])){

                                      // dump extra attr into item
                                      foreach ($action['extraattr'] as $k => $v){
                                          echo ' data-'.$k.'="'.$v.'"';
                                      }

                                } ?>>
                                 <?php 

                                    // got ico?
                                    if (isset($action['ico'])) echo '<i class="'.$action['ico'].'"></i>';

                                    // got text?
                                    if (isset($action['label'])) echo $action['label'];

                                ?>
                               </div>
                            <?php } ?>
                            </div>
                       </div>
                     </div>
                     <?php } ?>


                  </div>
              </div>

            <?php #DEBUG echo '<pre>'; print_r($contact); echo '</pre><hr>'; ?>

              

              <!-- customer vitals -->
              <?php

                // prep
                $statusStr = ''; if (isset($contact) && isset($contact['status']) && !empty($contact['status'])) $statusStr = $contact['status'];
              
                // compiled addr str
                $addrStr = ''; if (isset($contact)) $addrStr = zeroBS_customerAddr($contact['id'],$contact,'full','<br />');
                $addr2Str = ''; if (isset($contact)) $addr2Str = zeroBS_customerSecondAddr($contact['id'],$contact,'full','<br />');

                // tels?
                $tels = array(); 
                if (isset($contact) && isset($contact['hometel']) && !empty($contact['hometel'])) $tels['hometel'] = $contact['hometel'];
                if (isset($contact) && isset($contact['worktel']) && !empty($contact['worktel'])) $tels['worktel'] = $contact['worktel'];
                if (isset($contact) && isset($contact['mobtel']) && !empty($contact['mobtel'])) $tels['mobtel'] = $contact['mobtel'];

                // values - DAL3 we get them passed all nicely :)
                $contactTotalValue = 0; if (isset($contact['total_value'])) $contactTotalValue = $contact['total_value'];
                $contactQuotesValue = 0; if (isset($contact['quotes_total'])) $contactQuotesValue = $contact['quotes_total'];
                $contactInvoicesValue = 0; if (isset($contact['invoices_total'])) $contactInvoicesValue = $contact['invoices_total'];
                $contactTransactionsValue = 0; if (isset($contact['transactions_total'])) $contactTransactionsValue = $contact['transactions_total'];

                // retrieve any additional tabs peeps have prepared
                $zbsContactVitalTabs = apply_filters( 'jetpack-crm-contact-vital-tabs', array(), $id );

              ?>

              <div id="zbs-vitals-box">
                <div class="ui top attached tabular menu">
                  <div data-tab="vitals" class="<?php if (!isset($activeVitalsTab)) { echo 'active '; $activeVitalsTab = 'vitals'; } ?>item"><?php 

                      // custom title e.g. Lead Vitals                                
                      if (!empty($statusStr)) 
                        echo $statusStr; 
                      else
                        _e('Contact',"zero-bs-crm");
                       
                       echo ' '.__("Vitals","zero-bs-crm"); 

                      ?></div>
                  <?php if (count($zbsSocialAccountTypes) > 0 && count($contact_socials) > 0){ ?>
                    <div class="zbs-hide" data-tab="social" id="contact-tab-social" class="<?php if (!isset($activeVitalsTab)) { echo 'active '; $activeVitalsTab = 'social'; } ?>item"><?php _e('Social',"zero-bs-crm"); ?></div>                      
                  <?php } ?>
                  <?php #} Any integrated tabs - via filter jetpack-crm-contact-vital-tabs
                  if (is_array($zbsContactVitalTabs) && count($zbsContactVitalTabs) > 0){
                    $tabIndx = 1;
                    foreach ($zbsContactVitalTabs as $tab){

                      $tabName = __('Untitled Tab',"zero-bs-crm");
                      $tabID = 'zbs-contact-tab-'.$tabIndx;

                      if (is_array($tab) && isset($tab['name'])) $tabName = $tab['name'];
                      if (is_array($tab) && isset($tab['id'])) $tabID = $tab['id'];
                      
                      ?><div data-tab="<?php echo $tabID; ?>" class="item" id="contact-tab-<?php echo $tabID ?>"><?php echo $tabName; ?></div><?php

                      $tabIndx++;

                    }

                  } ?>
                  <?php if (!empty($statusStr)) { ?>
                  <div class="right menu item">
                    <?php _e("Status","zero-bs-crm");?>: 
                    <span class="ui green label"><?php echo $statusStr; ?></span>
                  </div>
                  <?php } ?>
                </div>

                <div class="ui bottom attached active tab segment" data-tab="vitals" id="zbs-contact-view-vitals">
                    <table class="ui fixed single line celled table">
                      <tbody>
                        <?php if ( $useInvoices == "1" || $useTrans == "1" ): ?>
                        <tr class="zbs-view-vital-totalvalue">
                          <td class="zbs-view-vital-label"><strong><?php _e("Total Value","zero-bs-crm");?><i class="circle info icon link" data-content="<?php _e("Total Value is all transaction types and any unpaid invoices","zero-bs-crm");?>" data-position="bottom center"></i></strong></td>
                          <td><strong><?php echo zeroBSCRM_formatCurrency($contactTotalValue); ?></strong></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ( $useQuotes == "1"){ ?>
                        <tr>
                          <td class="zbs-view-vital-label"><?php _e("Quotes","zero-bs-crm"); ?> <i class="circle info icon link" data-content="<?php _e("Quotes: This shows the total sum of your quotes & count.","zero-bs-crm");?>" data-position="bottom center"></i></td>
                          <td>
                            <?php if ( $contact_quote_count > 0 ){
                                    echo zeroBSCRM_formatCurrency( $contactQuotesValue ) .' (' . zeroBSCRM_prettifyLongInts( $contact_quote_count ) . ')';
                                  } else {
                                    _e('None',"zero-bs-crm");
                                  } ?>
                          </td>
                        </tr>
                        <?php } ?>
                        <?php if ($useInvoices == "1"){ ?>
                        <tr class="zbs-view-vital-invoices">
                          <td class="zbs-view-vital-label"><?php _e("Invoices","zero-bs-crm"); ?> <i class="circle info icon link" data-content="<?php _e("Invoices: This shows the total sum of your invoices & count.","zero-bs-crm");?>" data-position="bottom center"></i></td>
                          <td>
                            <?php if ( $contact_invoice_count > 0 ){
                                    echo zeroBSCRM_formatCurrency( $contactInvoicesValue ) . ' (' . zeroBSCRM_prettifyLongInts( $contact_invoice_count ) . ')';
                                  } else {
                                    _e('None',"zero-bs-crm");
                                  } ?>
                          </td>
                        </tr>
                        <?php } ?>
                        <?php if ($useTrans == "1"){ ?>
                        <tr class="zbs-view-vital-transactions">
                          <td class="zbs-view-vital-label"><?php _e("Transactions","zero-bs-crm"); ?> <i class="circle info icon link" data-content="<?php _e("Transactions Total & count: This shows the sum of your succeeded transactions (set in settings)","zero-bs-crm");?>" data-position="bottom center"></i></td>
                          <td>
                            <?php if ( $contact_transaction_count > 0 ){
                                    echo zeroBSCRM_formatCurrency( $contactTransactionsValue ) . ' (' . zeroBSCRM_prettifyLongInts( $contact_transaction_count ) . ')';
                                  } else {
                                    _e('None',"zero-bs-crm");
                                  } ?>
                          </td>
                        </tr>
                        <?php } ?>
                        <tr class="zbs-view-vital-source">
                          <td class="zbs-view-vital-label"><?php ( $contact_external_source_count > 1 ?  _e( "Sources", "zero-bs-crm" ) : _e( "Source", "zero-bs-crm" ) ); ?></td>
                          <td>
                            <?php                            

                            if ( $contact_has_external_sources ){
  
                              // Previously: zeroBS_getExternalSourceTitle
                              jpcrm_render_external_sources_info( $contact['external_sources'], $contact['id'], ZBS_TYPE_CONTACT );

                            } else {

                              _e( 'Manually Added', 'zero-bs-crm' );

                            }

                             ?></td>
                        </tr>
                        <?php /* IF IN B2B MODE show co here too */

                          $b2bMode = zeroBSCRM_getSetting('companylevelcustomers');

                          if($b2bMode){ ?><tr class="zbs-view-vital-b2b">
                          <td class="zbs-view-vital-label"><?php _e(jpcrm_label_company(),"zero-bs-crm");?></td>
                          <td>
                            <?php 

                              // companies where this contact is linked to
                              $contactStr = zeroBSCRM_html_linkedContactCompanies($id,(isset($contact['companies']) ? $contact['companies'] : false));

                              if (!empty($contactStr))
                                echo $contactStr;
                              else
                                __('No '.jpcrm_label_company().' on File',"zero-bs-crm");

                            ?>
                          </td>
                        </tr>
                        <?php } ?>
                        <tr class="zbs-view-vital-address wraplines">
                          <td class="zbs-view-vital-label"><?php _e("Address Details","zero-bs-crm");?></td>
                          <td>
                            <?php
                              if (!empty($addrStr) && empty($addr2Str)) {
                                echo $addrStr;
                              } elseif (empty($addrStr) && !empty($addr2Str)) {
                                echo $addr2Str;
                              } elseif (!empty($addrStr) && !empty($addr2Str)) {
                                ?>
                                <div class="ui grid">
                                  <div class="eight wide column">
                                    <h4 class="ui dividing header" style="margin-bottom: 0.6em;"><?php _e('Main address',"zero-bs-crm"); ?></h4>
                                    <?php echo $addrStr; ?>
                                  </div>
                                  <div class="eight wide column">
                                    <h4 class="ui dividing header" style="margin-bottom: 0.6em;"><?php _e('Secondary address',"zero-bs-crm"); ?></h4>
                                    <?php echo $addr2Str; ?>
                                  </div>
                                </div>
                                <?php
                              } else {
                                _e('No Address on File',"zero-bs-crm");
                              }
                            ?>
                          </td>
                        </tr>
                        <tr class="zbs-view-vital-telephone">
                          <td class="zbs-view-vital-label"><?php _e("Telephone Contacts","zero-bs-crm");?></td>
                          <td>
                            <?php 


                                  if (count($tels) > 0){

                                    // Click 2 call?
                                    $click2call = $zbs->settings->get('clicktocall');

                                    ?><div class="ui horizontal list"><?php

                                      foreach ($tels as $telKey => $telNo){ ?>
                                      <div class="item">
                                        <?php switch ($telKey){

                                          case 'hometel': 
                                              echo '<i class="large phone icon"></i>';
                                              break;
                                          case 'worktel':
                                              echo '<i class="large phone square icon"></i>'; 
                                              break;
                                          case 'mobtel':
                                              echo '<i class="large mobile icon"></i>'; 
                                              break;

                                        } ?>
                                        <div class="content">
                                          <?php if ($click2call == "1") { ?>
                                          <a class="ui small button" href="<?php echo zeroBSCRM_clickToCallPrefix().$telNo; ?>" title="<?php _e('Call',"zero-bs-crm").' '.$telNo; ?>"><?php echo $telNo; ?></a>
                                          <?php } else { ?>
                                          <div class="header"><?php echo $telNo; ?></div>
                                          <?php } ?>
                                        </div>
                                      </div>
                                      <?php } ?>

                                    </div><?php


                                  } else _e('No phone number on File',"zero-bs-crm"); ?></td>
                        </tr>
                        <?php

                          if (is_array($contact_flags) && count($contact_flags) > 0){
                            ?>
                        <tr class="zbs-view-vital-flags">
                          <td class="zbs-view-vital-label"><?php _e("Flags","zero-bs-crm");?></td>
                          <td>
                            <?php
                            
                            foreach ($contact_flags as $flag){

                              switch ($flag){

                                case 'do-not-email':
                                  echo zeroBSCRM_UI2_label('red','<i class="bell slash outline icon"></i>',__('Email Unsubscribed','zero-bs-crm'),__('(Do Not Email Flag)','zero-bs-crm'),'do-not-email');
                                  break;

                              }

                            }

                            ?>
                          </td>
                        </tr><?php

                          }

                        ?>
                      </tbody>
                    </table>

                </div>

                  <?php #} Any integrated tabs - via filter jetpack-crm-contact-vital-tabs
                  if (is_array($zbsContactVitalTabs) && count($zbsContactVitalTabs) > 0){
                    $tabIndx = 1;
                    foreach ($zbsContactVitalTabs as $tab){

                      $tabID = 'zbs-contact-tab-'.$tabIndx;
                      if (is_array($tab) && isset($tab['id'])) $tabID = $tab['id'];
                      
                      ?><div class="ui bottom attached tab segment" data-tab="<?php echo $tabID; ?>" id="zbs-contact-view-vitals-<?php echo $tabID; ?>">
                      <?php #} Content
                      if (is_array($tab) && isset($tab['contentaction'])){

                          // calls the users function name, if they opted for that instead of content
                          call_user_func($tab['contentaction'],$id);

                      } else if (is_array($tab) && isset($tab['content'])) echo $tab['content']; ?>
                      </div><?php

                      $tabIndx++;

                    }

                  } ?>                      
                <!-- / customer vitals -->


                <?php if (count($zbsSocialAccountTypes) > 0 && count($contact_socials) > 0){ ?>
                <div class="ui bottom attached tab segment" data-tab="social" id="zbs-contact-view-social">
                  <?php


                      if (count($zbsSocialAccountTypes) > 0){
                        
                        ?><div class="ui relaxed divided large list"><?php


                        foreach ($zbsSocialAccountTypes as $socialKey => $socialAccType){

                          ?><div class="item zbs-social-acc <?php echo $socialAccType['slug']; ?>" title="<?php echo $socialAccType['name']; ?>">
                              <?php if (is_array($contact_socials) && isset($contact_socials[$socialKey]) && !empty($contact_socials[$socialKey])){ 

                                  // got acc? link to it
                                  $socialLink = zeroBSCRM_getSocialLink($socialKey,$contact_socials);

                                  ?>
                                  <i class="large middle aligned <?php echo zeroBSCRM_faSocialToSemantic($socialAccType['fa']); ?>" aria-hidden="true" style="padding-top:0"></i>
                                  <div class="content middle aligned">
                                    <a href="<?php echo $socialLink; ?>" target="_blank" title="<?php echo __('View',"zero-bs-crm").' '.$contact_socials[$socialKey]; ?>" class="header"><?php echo $contact_socials[$socialKey]; ?></a>
                                  </div>
                              <?php } ?>
                          </div><?php

                        }

                        ?></div><?php

                      }

                  ?>
                </div><!-- / customer socials -->
                <?php } ?>
              </div>
        

              <h4 class="ui horizontal header divider">
                <i class="archive icon"></i>
                <?php _e('Documents',"zero-bs-crm"); ?>
              </h4>

              <div id="zbs-doc-menu">
                <div class="ui top attached tabular menu">
                  <?php if ($useQuotes == "1"){ ?><div data-tab="quotes" class="<?php if (!isset($activeTab)) { echo 'active '; $activeTab = 'quotes'; } ?>item"><?php _e('Quotes',"zero-bs-crm"); ?></div><?php } ?>
                  <?php if ($useInvoices == "1"){ ?><div data-tab="invoices" class="<?php if (!isset($activeTab)) { echo 'active '; $activeTab = 'invoices'; } ?>item"><?php _e('Invoices',"zero-bs-crm"); ?></div><?php } ?>
                    <?php if ($useTrans == "1"){ ?><div data-tab="transactions" class="<?php if (!isset($activeTab)) { echo 'active '; $activeTab = 'transactions'; } ?>item"><?php _e('Transactions',"zero-bs-crm"); ?></div><?php } ?>
                  <div data-tab="files" class="<?php if (!isset($activeTab)) { echo 'active '; $activeTab = 'files'; } ?>item"><?php _e('Files','zero-bs-crm'); ?></div>
                  <?php if ($useTasks == "1"){ ?><div data-tab="tasks" class="<?php if (!isset($activeTab)) { echo 'active '; $activeTab = 'tasks'; } ?>item"><?php _e('Tasks',"zero-bs-crm"); ?></div><?php } ?>
                </div>

                <?php

                //start quotes tab content
                if ($useQuotes == "1"): ?>
                  <div class="ui bottom attached <?php if ($activeTab == 'quotes') echo 'active '; ?>tab segment" data-tab="quotes">
                    <table class="ui celled table unstackable">
                      <thead>
                        <tr>
                          <th><?php _e("ID & Title","zero-bs-crm"); ?></th>
                          <th><?php _e("Date","zero-bs-crm"); ?></th>
                          <th><?php _e("Value","zero-bs-crm"); ?></th>
                          <th><?php _e("Status","zero-bs-crm"); ?></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        // prep link to create a new quote
                        $new_quote_url = zbsLink('create',-1,ZBS_TYPE_QUOTE).'&zbsprefillcust='.$contact['id'];
                        
                        if ( count( $contact['quotes'] ) > 0 ){

                          foreach( $contact['quotes'] as $quote ){
                            
                            $quoteValue = '-'; 

                            $idRefStr = '';
                            // DAL3 change of field name
                            if ($zbs->isDAL3()){

                              // 3.0
                              if (isset($quote['id'])) $idRefStr = '#'.$quote['id'];
                              if (isset($quote['id_override']) && !empty($quote['id_override'])) {
                                if (!empty($idRefStr)) $idRefStr .= ' -';
                                $idRefStr .= ' '.$quote['id_override'];
                              }
                              if (isset($quote['title']) && !empty($quote['title'])) {
                                if (!empty($idRefStr)) $idRefStr .= ' -';
                                $idRefStr .= ' '.$quote['title'];
                              }

                              $quoteURL = zbsLink('edit',$quote['id'],ZBS_TYPE_QUOTE);
                              $quoteValue = $quote['value'];
                              $quoteStatus = $quote['status'];

                            } else {

                              if (isset($quote['zbsid'])) $idRefStr = '#'.$quote['zbsid'];
                              if (isset($quote['meta']) && isset($quote['meta']['ref'])) {
                                if (!empty($idRefStr)) $idRefStr .= ' -';
                                $idRefStr .= ' '.$quote['meta']['ref'];
                              }

                              $quoteURL = zbsLink('edit',$quote['id'],ZBS_TYPE_QUOTE);//admin_url('post.php?action=edit&post='.$quote['id']);

                              if (isset($quote['meta']['val'])) $quoteValue = $quote['meta']['val'];

                            }

                            if ($quoteValue != '-' && !empty($quoteValue)) $quoteValue = zeroBSCRM_formatCurrency($quoteValue);

                            echo "<tr>";
                              echo '<td><a href="'.$quoteURL.'">' . $idRefStr . "</a></td>";
                              echo "<td>" . zeroBSCRM_html_QuoteDate($quote) . "</td>";
                              echo "<td>" . $quoteValue . "</td>";
                              echo "<td><span class='".zeroBSCRM_html_quoteStatusLabel($quote)."'>" . zeroBS_getQuoteStatus($quote,false) . "</span></td>";
                            echo "</tr>"; 
                          }

                          // if we have more than we're showing, communicate that
                          // Note this is defunct until we add contact filters to our object list views.
                          if ( count( $contact['quotes'] ) > $contact_quote_count ){
                            ?>
                            <tr>
                              <td colspan="4">
                                  <div class="ui info message">
                                    <div class="content">
                                      <p style="text-align:center"><a href="<?php echo zbsLink( $zbs->slugs['managequotes'] ); ?>" class="ui button blue"><?php _e( 'View All Quotes', 'zero-bs-crm' ); ?></a></p>
                                    </div>
                                  </div>
                              </td>
                            </tr>
                            <?php
                          }

                        } else {
                          ?>
                          <tr>
                            <td colspan="4">
                                <div class="ui info icon message" id="zbsNoQuoteResults">
                                  <div class="content">
                                    <div class="header"><?php _e('No Quotes',"zero-bs-crm"); ?></div>
                                    <p><?php echo sprintf(__('This contact does not have any quotes yet. Do you want to <a href="%s">create one</a>?',"zero-bs-crm"),$new_quote_url); ?></p>
                                  </div>
                                </div>
                            </td>
                          </tr>
                          <?php
                        }

                        ?>

                      </tbody>
                    </table>
                    <?php if (count($contact['quotes']) > 0) : ?>
                      <div style="text-align: right;">
                        <a href="<?php echo $new_quote_url ?>" class="ui basic green button">
                          <i class="plus square outline icon"></i>
                          <?php _e('Add Quote','zero-bs-crm') ?>
                        </a>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php
                //end quotes tab content
                endif;
                
                //start invoices tab content
                if ($useInvoices == "1"): ?>
                  <div class="ui bottom attached <?php if ($activeTab == 'invoices') echo 'active '; ?>tab segment" data-tab="invoices">
                    <table class="ui celled table unstackable">
                      <thead>
                        <tr>
                          <th><?= $zbs->settings->get('reflabel') ?></th>
                          <th><?php _e("Date","zero-bs-crm"); ?></th>
                          <th><?php _e("Amount","zero-bs-crm"); ?></th>
                          <th><?php _e("Status","zero-bs-crm"); ?></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        
                        // prep link to create a new invoice
                        $new_invoice_url = zbsLink('create',-1,ZBS_TYPE_INVOICE).'&zbsprefillcust='.$contact['id'];
                        if (count($contact['invoices']) > 0){

                          foreach($contact['invoices'] as $invoice){

                            $idRefStr = '';
                            // DAL3 change of field name
                            if ($zbs->isDAL3()){

                              // 3.0
                              if (isset($invoice['id'])) $idRefStr = '#'.$invoice['id'];
                              if (isset($invoice['id_override']) && !empty($invoice['id_override'])) {
                                if (!empty($idRefStr)) $idRefStr .= ' -';
                                $idRefStr .= ' '.$invoice['id_override'];
                              }

                              $invoiceURL = zbsLink('edit',$invoice['id'],ZBS_TYPE_INVOICE);

                              $invoiceVal = $invoice['total'];

                              $invoiceStatus = $invoice['status'];

                            } else {

                              // <3.0
                              if (isset($invoice['zbsid'])) $idRefStr = '#'.$invoice['zbsid'];
                              if (isset($invoice['meta']) && isset($invoice['meta']['ref'])) {
                                if (!empty($idRefStr)) $idRefStr .= ' -';
                                $idRefStr .= ' '.$invoice['meta']['ref'];
                              }

                              $invoiceURL = zbsLink('edit',$invoice['id'],ZBS_TYPE_INVOICE);

                              $invoiceVal = $invoice['meta']['val'];

                              $invoiceStatus = $invoice['meta']['status'];

                            }
                            

                            echo "<tr>";
                              echo '<td><a href="'.$invoiceURL.'">' . $idRefStr . "</a></td>";
                              echo "<td>" . zeroBSCRM_html_InvoiceDate($invoice) . "</td>";
                              echo "<td>" . zeroBSCRM_formatCurrency($invoiceVal) . "</td>";
                              echo "<td><span class='".zeroBSCRM_html_invoiceStatusLabel($invoice)."'>" . ucfirst($invoiceStatus) . "</span></td>";
                            echo "</tr>"; 
                          }


                          // if we have more than we're showing, communicate that
                          // Note this is defunct until we add contact filters to our object list views.
                          if ( count( $contact['invoices'] ) > $contact_invoice_count ){
                            ?>
                            <tr>
                              <td colspan="4">
                                  <div class="ui info message">
                                    <div class="content">
                                      <p style="text-align:center"><a href="<?php echo zbsLink( $zbs->slugs['manageinvoices'] ); ?>" class="ui button blue"><?php _e( 'View All Invoices', 'zero-bs-crm' ); ?></a></p>
                                    </div>
                                  </div>
                              </td>
                            </tr>
                            <?php
                          }

                        } else {
                          ?>
                          <tr>
                            <td colspan="4">
                                <div class="ui info icon message" id="zbsNoInvoiceResults">
                                  <div class="content">
                                    <div class="header"><?php _e('No Invoices',"zero-bs-crm"); ?></div>
                                    <p><?php echo sprintf(__('This contact does not have any invoices yet. Do you want to <a href="%s">create one</a>?',"zero-bs-crm"),$new_invoice_url); ?></p>
                                  </div>
                                </div>
                            </td>
                          </tr>
                          <?php

                        }

                        ?>

                      </tbody>
                    </table>
                    <?php if (count($contact['invoices']) > 0) : ?>
                      <div style="text-align: right;">
                        <a href="<?php echo $new_invoice_url ?>" class="ui basic green button">
                          <i class="plus square outline icon"></i>
                          <?php _e('Add Invoice','zero-bs-crm') ?>
                        </a>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php
                //end invoices tab content
                endif;
                
                //start transactions tab content
                if ($useTrans == "1"): ?>
                  <div class="ui bottom attached <?php if ($activeTab == 'transactions') echo 'active '; ?>tab segment" data-tab="transactions">
                    <?php

                    // get columns from screen options
                    $activeTransactionColumns = array('date','id','total','status'); // default
                    if (
                        isset($screenOpts) && is_array($screenOpts) 
                        && isset($screenOpts['tablecolumns']) && is_array($screenOpts['tablecolumns']) 
                        && isset($screenOpts['tablecolumns']['transactions']) 
                        && is_array($screenOpts['tablecolumns']['transactions']) 
                        && count($screenOpts['tablecolumns']['transactions']) > 0
                      ) $activeTransactionColumns = $screenOpts['tablecolumns']['transactions'];
                    ?>
                    <table class="ui celled table unstackable">
                      <thead>
                        <tr>
                        <?php 

                        // for now, pick out id so always on left
                        if (in_array('id', $activeTransactionColumns)) echo '<th>'.zeroBS_objDraw_transactionColumnHeader('id').'</th>';

                        foreach ($activeTransactionColumns as $col){ 

                          // id pulled out above
                          if ($col != 'id') echo '<th>'.zeroBS_objDraw_transactionColumnHeader($col).'</th>'; 

                        } ?>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        
                        // prep link to create a new transaction
                        $new_transaction_url = zbsLink('create',-1,ZBS_TYPE_TRANSACTION).'&zbsprefillcust='.$contact['id'];

                        if (count($contact['transactions']) > 0){

                          foreach($contact['transactions'] as $zbsTransaction){

                            echo "<tr>";
                        
                              // ultimately these should be drawn by JS so they can use the same 
                              // 'generate obj html' funcs as list view
                              // for now quickly generated in php for this freelance.

                              // for now, pick out id so always on left
                              if (in_array('id', $activeTransactionColumns)) echo '<td>'.zeroBS_objDraw_transactionColumnTD('id',$zbsTransaction).'</td>';

                              foreach ($activeTransactionColumns as $col){ 

                                // id pulled out above
                                if ($col != 'id') echo '<td>'.zeroBS_objDraw_transactionColumnTD($col,$zbsTransaction).'</td>';

                              }

                            echo "</tr>";
                          }

                          // if we have more than we're showing, communicate that
                          // Note this is defunct until we add contact filters to our object list views.
                          if ( count( $contact['transactions'] ) > $contact_transaction_count ){
                            ?>
                            <tr>
                              <td colspan="4">
                                  <div class="ui info message">
                                    <div class="content">
                                      <p style="text-align:center"><a href="<?php echo zbsLink( $zbs->slugs['managetransactions'] ); ?>" class="ui button blue"><?php _e( 'View All Transactions', 'zero-bs-crm' ); ?></a></p>
                                    </div>
                                  </div>
                              </td>
                            </tr>
                            <?php
                          }

                        } else {

                          ?>
                          <tr>
                            <td colspan="<?php echo count($activeTransactionColumns); ?>">
                                <div class="ui info icon message" id="zbsNoTransactionResults">
                                  <div class="content">
                                    <div class="header"><?php _e('No Transactions',"zero-bs-crm"); ?></div>
                                    <p><?php echo sprintf(__('This contact does not have any transactions yet. Do you want to <a href="%s">create one</a>?',"zero-bs-crm"),$new_transaction_url); ?></p>
                                  </div>
                                </div>
                            </td>
                          </tr>
                          <?php

                        }

                        ?>

                      </tbody>
                    </table>
                    <?php if (count($contact['transactions']) > 0) : ?>
                      <div style="text-align: right;">
                        <a href="<?php echo $new_transaction_url ?>" class="ui basic green button">
                          <i class="plus square outline icon"></i>
                          <?php _e('Add Transaction','zero-bs-crm') ?>
                        </a>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php
                //end transactions tab content
                endif;
                
                //start files tab content
                ?>
                <div class="ui bottom attached tab segment" data-tab="files">
                  <?php /* 
                   <a class="ui button tiny right" href="#">Add File</a>
                    <div class="clear"></div>
                    <div class="ui divider"></div>

                    <style>
                    .thetitle{
                      font-size: 16px;
                      font-weight: 900;
                    }
                    </style> */ ?>

                  <table class="ui celled table unstackable" id="zbsFilesTable" style="margin-bottom:0;">
                    <thead>
                      <tr>
                        <th><?php _e("Info", 'zero-bs-crm');?></th>
                        <th class="center aligned"><?php _e("View File", 'zero-bs-crm');?></th>
                        <th class="center aligned"><?php _e("Shown on Portal", 'zero-bs-crm');?></th>
                        <th class="center aligned" style="min-width:230px"><?php _e("Actions", 'zero-bs-crm');?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $zbsFiles = zeroBSCRM_getCustomerFiles($id); $hasFiles = false;

                      // prep link to upload a new file
                      $new_file_url = zbsLink('edit',$id,'zerobs_customer',false).'#zerobs-customer-files-head';
                      
                      #} Any files
                      if (is_array($zbsFiles) && count($zbsFiles) > 0){ 

                        $hasFiles = true;
                        $fileLineIndx = 0;

                        foreach($zbsFiles as $zbsFile){

                          //$fileFullname = basename($zbsFile['file']);
                          //$file = substr($fileFullname,strpos($fileFullname, '-')+1);
                          $file = zeroBSCRM_files_baseName($zbsFile['file'],isset($zbsFile['priv']));
                          ?>
                          <tr>
                            <td>
                              <h4><?php if (isset($zbsFile['title'])) echo $zbsFile['title']; else echo __('Untitled','zero-bs-crm'); ?></h4>
                              <p>
                                <?php if (isset($zbsFile['desc'])) echo $zbsFile['desc']; ?>
                              </p>
                              <em>(<?php echo $file; ?>)</em>
                            </td>

                            <td class="center aligned">
                              <?php 
                                echo '<a class="" href="'.$zbsFile['url'].'" target="_blank" class="ui button basic">'.__("View","zero-bs-crm") .'</a>';
                              ?>
                            </td>


                            <td class="center aligned">
                              <?php 
                              if(isset($zbsFile['portal']) && $zbsFile['portal']){
                                echo "<i class='icon check cirlc green inverted'></i>";
                              } else {
                                echo "<i class='icon ban inverted red'></i>";
                              }
                              ?>
                            </td>

                            <td class="center aligned">
                              <?php
                              $zbs_edit = esc_url(admin_url('admin.php?page='.$zbs->slugs['editfile']) . "&customer=".$id."&fileid=" . $fileLineIndx  );   
                              ?>
                              <a href="<?php echo $zbs_edit;?>" target="_blank" class="ui button basic"><i class="edit icon"></i><?php _e("Edit","zero-bs-crm"); ?></a>&nbsp;&nbsp;
                              <button class="zbsDelFile ui button basic" data-delurl="<?php echo $zbsFile['url']; ?>"><i class="trash alternate icon"></i><?php _e("Delete","zero-bs-crm"); ?></button>
                            </td>
                          </tr>
                          <?php
       
                          $fileLineIndx++;
                        } //end of the files loop.. 

                      } 

                    // put this out either way, so that if a user deletes all it can be reshown in ui
                        // empty, create?
                      ?>
                      <tr id="zbs-no-files-msg" style="display:<?php echo $hasFiles ? 'none' : 'table-row'; ?>">
                        <td colspan="4">
                            <div class="ui info icon message" id="zbsNoFileResults">
                              <div class="content">
                                <div class="header"><?php _e('No Files',"zero-bs-crm"); ?></div>
                                <p><?php echo sprintf(__('This contact does not have any files yet. Do you want to <a href="%s">upload one</a>?',"zero-bs-crm"),$new_file_url); ?></p>
                              </div>
                            </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <div id="zbsFileActionOutput" style="display:none"></div>
                  <?php  
                  ##WLREMOVE 

                  // and upsell here if admin + not using client portal pro
                  if ( current_user_can('admin_zerobs_manage_options') && !defined('ZBS_CLIENTPRO_TEMPLATES') ){

                    
                    
                    if ( !$zbs->hasEntrepreneurBundleMin() ){

                      // no client portal pro, no bundle, so UPSELL :) 
                      ?><div style="margin-bottom:0;line-height: 1.8em;text-align:center" class="ui inverted segment"><?php

                      echo sprintf( __( 'Want to show clients files on their Client Portal? <a href="%s"> target="_blank">Upgrade to a Bundle</a> (and get Client Portal Pro) to enable this.', 'zero-bs-crm' ), $zbs->urls['upgrade'] . '?utm_content=inplugin-contactview' );
                    
                    } else {

                      // no CPP, but has bundle, suggest install
                      ?><div style="margin-bottom:0;line-height: 1.8em;text-align:center" class="ui info segment"><?php

                      _e( '<strong>Bundle holder:</strong> Please install the Client Portal Pro extension if you\'d like to show clients their files on the client portal.', 'zero-bs-crm' );

                    }

                    ?></div><?php

                  }

                  ##/WLREMOVE 
                  ?>
                </div>
                <?php
                //end files tab content
                
                //start tasks tab content
                if ($useTasks == "1"): ?>
                  <div class="ui bottom attached <?php if ($activeTab == 'tasks') echo 'active '; ?>tab segment" data-tab="tasks">
                    <table class="ui celled table unstackable">
                      <thead>
                        <tr>
                          <th><?php _e("Date","zero-bs-crm"); ?></th>
                          <th><?php _e("Task","zero-bs-crm"); ?></th>
                          <th><?php _e("Status","zero-bs-crm"); ?></th>
                          <th><?php _e("View","zero-bs-crm"); ?></th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php

                        // prep link to create a new task
                        $new_task_url = zbsLink('create',-1,ZBS_TYPE_EVENT).'&zbsprefillcust='.$contact['id'];

                        if (isset($contact['tasks']) && is_array($contact['tasks']) && count($contact['tasks']) > 0){

                          $lastTaskStart = -1;
                          $upcomingOutput = false;

                          foreach ($contact['tasks'] as $task){

                            // if the first task is upcoming, add a header
                            if (!$upcomingOutput && $task['start'] > time()){

                              // tried to use horizontal divider here, but there's a semantic bug
                              // ... when using these in tables. https://semantic-ui.com/elements/divider.html
                              // ... adding display:block to the td fixes, but then colspan doesn't work. Skipping for now
                              echo '<tr><td colspan="4"><div class="ui horizontal divider">'.__('Upcoming Tasks','zero-bs-crm').'</div></td></tr>';

                              // shown
                              $upcomingOutput = true;

                            }

                            // if there are tasks in future, and past, draw a line between
                            if ($lastTaskStart > 0 && $lastTaskStart > time() && $task['end'] < time()){

                              // tried to use horizontal divider here, but there's a semantic bug
                              // ... when using these in tables. https://semantic-ui.com/elements/divider.html
                              // ... adding display:block to the td fixes, but then colspan doesn't work. Skipping for now
                              echo '<tr><td colspan="4"><div class="ui horizontal divider">'.__('Past Tasks','zero-bs-crm').'</div></td></tr>';

                            }

                            $taskURL = zbsLink('edit',$task['id'],ZBS_TYPE_EVENT);
                            $statusStr = __('Incomplete','zero-bs-crm'); if (isset($task['complete']) && $task['complete'] === 1) $statusStr = __('Completed','zero-bs-crm');
                            $status = "<span class='".zeroBSCRM_html_taskStatusLabel($task)."'>" . $statusStr . "</span>";

                            echo "<tr>";
                              echo "<td>" . zeroBSCRM_html_taskDate($task) . "</td>";
                              echo "<td>" . $task['title'] . "</td>";
                              echo "<td>".$status."</td>";
                              echo '<td style="text-align:center"><a href="'.$taskURL.'">' . __('View','zero-bs-crm') . "</a></td>";
                            echo "</tr>"; 

                            $lastTaskStart = $task['start'];

                          }
                        } else {

                          ?>
                          <tr>
                            <td colspan="4">
                              <div class="ui info icon message" id="zbsNoTaskResults">
                                <div class="content">
                                  <div class="header"><?php _e('No Tasks',"zero-bs-crm"); ?></div>
                                  <p><?php echo sprintf(__('This contact does not have any tasks yet. Do you want to <a href="%s">create one</a>?',"zero-bs-crm"),$new_task_url); ?></p>
                                </div>
                              </div>
                            </td>
                          </tr><?php

                        }
                        ?>
                      </tbody>
                    </table>
                    <?php if (count($contact['tasks']) > 0) : ?>
                      <div style="text-align: right;">
                        <a href="<?php echo $new_task_url ?>" class="ui basic green button">
                          <i class="plus square outline icon"></i>
                          <?php _e('Add Task','zero-bs-crm') ?>
                        </a>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php
                //end tasks tab content
                endif; ?>
                
                </div><!-- / tabs -->


              <?php 

                  $customerTags = zeroBSCRM_getCustomerTagsByID($contact['id']);

                  // debug echo '<pre>'; print_r($customerTags); echo '</pre><hr>';

                  if (count($customerTags) > 0){

                      ?><!-- TAGGED --><div class="zbs-view-tags">
                      <h4 class="ui horizontal header divider">
                          <i class="tag icon"></i>
                          <?php _e('Tagged',"zero-bs-crm"); ?>
                      </h4>
                      <?php

                        // output as links
                        zeroBSCRM_html_linkedContactTags($contact['id'],$customerTags,'ui medium olive button');

                      ?>
                      </div><!-- / TAGGED --><?php
                  } ?>

            </div>

            <div class="six wide column" id="zbs-custom-quicklinks" style="padding-right: 30px;">

              <?php 
                  #} Metaboxes
                  zeroBSCRM_do_meta_boxes( 'zbs-view-contact', 'side', $contact ); // should be an obj! 'zerobs_customer'
              ?>

            </div>


          </div>
          <script type="text/javascript">
            
            // Nonce
            var zbscrmjs_secToken = '<?php echo wp_create_nonce( "zbscrmjs-ajax-nonce" ); ?>';

            // moved to singleview.js
            var zbsViewSettings = {

                objid: <?php echo $id; ?>,
                objdbname: 'contact' <?php //echo $this->objType; ?>

            };

            </script><?php

            // PRIVATE hook (do not share with dev/docs PLEASE leave off.)
            do_action('zerobscrm_contactview_postscripts');

    } // if ID

}


/*
  Exposes generic php-js for view pages (lang labels)
*/
function zeroBSCRM_pages_admin_contact_view_page_js(){

  ?><script>
        var zbsViewLang = {

            'error': '<?php echo zeroBSCRM_slashOut(__('Error','zero-bs-crm')); ?>',
            'unabletodelete': '<?php echo zeroBSCRM_slashOut(__('Unable to delete this file.','zero-bs-crm')); ?>'
        };
    </script><?php
}


/*

  Custom Fields View Contact Tab

*/
function zeroBSCRM_pages_admin_view_page_contact_custom_fields($arr=array(), $id=-1) {

  global $zbs;

  // Here we hide it if:
  // - Non admin
  // - No custom fields
  if ($zbs->DAL->contacts->hasCustomFields($id,false) || zeroBSCRM_isZBSAdminOrAdmin()){

      // this is just a check :)
      if (!is_array($arr)) $arr = array();

      // Here we add the new tab
      $arr[] = array(
        'id' => 'contact-custom-fields-tab',
        'name' => __('Custom Fields', 'zero-bs-crm'),
        'content' => zeroBSCRM_pages_admin_display_custom_fields_table($id,ZBS_TYPE_CONTACT)
      );

  }

  return $arr;

}
add_filter( 'jetpack-crm-contact-vital-tabs', 'zeroBSCRM_pages_admin_view_page_contact_custom_fields', 10, 2 );
