<?php 
/*!
 * Admin Page: Settings: Mail delivery setup
 */

// stop direct access
if ( ! defined( 'ZEROBSCRM_PATH' ) ) exit; 

global $zbs;  #} Req

// check if running locally (Then smtp may not work, e.g. firewalls + pain)
$runningLocally = zeroBSCRM_isLocal(true);

?><div id="zbs-mail-delivery-wrap"><?php

    #} SMTP Configured?
    $zbsSMTPAccs = zeroBSCRM_getMailDeliveryAccs();
    $defaultMailOptionIndex = zeroBSCRM_getMailDeliveryDefault();


    // TEMP DELETES ACCS: global $zbs->settings; $zbs->settings->update('smtpaccs',array());


    #} Defaults
    $defaultFromDeets = zeroBSCRM_wp_retrieveSendFrom();

    // Temp print_r($defaultFromDeets);

    if (count($zbsSMTPAccs) <= 0){

        #} ====================================
        #} No settings yet :)
        ?><h1 class="ui header blue zbs-non-wizard" style="margin-top: 0;"><?php _e('Mail Delivery',"zero-bs-crm"); ?></h1>

        <div class="ui icon big message zbs-non-wizard">
            <i class="wordpress icon"></i>
            <div class="content">
                <div class="header">
                    <?php _e('Jetpack CRM is using the default WordPress email delivery',"zero-bs-crm"); ?>
                </div>
                <hr />
                <p><?php _e('By default Jetpack CRM is configured to use wp_mail to send out all emails. This means your emails will go out from the basic wordpress@yourdomain.com style sender. This isn\'t great for deliverability, or your branding.',"zero-bs-crm"); ?></p>
                <div><?php _e('Currently mail is sent from',"zero-bs-crm"); echo ' <div class="ui large teal horizontal label">'.$defaultFromDeets['name'].' ('.$defaultFromDeets['email'].')</div><br />'.__('Do you want to set up a different Mail Delivery option?',"zero-bs-crm"); ?></div>
                <div style="padding:2em 0 1em 2em">
                    <button type="button" id="zbs-mail-delivery-start-wizard" class="ui huge primary button"><?php _e('Start Wizard','zero-bs-crm'); ?></button>
                </div>
            </div>
        </div>


        <?php
        #} ====================================



    } else {

        #} ====================================
        #} Has settings, dump them out :)
        // debug
        //echo '<pre>'; print_r($zbsSMTPAccs); echo '</pre>';
        //$key = zeroBSCRM_mailDelivery_makeKey($zbsSMTPAccs[0]); echo 'key:'.$key.'!';

        ?><div id="zbs-mail-delivery-account-list-wrap">
        <h1 class="ui header blue zbs-non-wizard" style="margin-top: 0;"><?php _e('Mail Delivery',"zero-bs-crm"); ?></h1>
        <table class="ui celled table zbs-non-wizard">
            <thead>
            <tr>
                <th><?php _e('Outbound Account',"zero-bs-crm"); ?></th>
                <th style="text-align:center">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php $detailMode = false; $accIndx = 0;
            if (count($zbsSMTPAccs) > 1) $detailMode = true;
            foreach ($zbsSMTPAccs as $accKey => $acc){

                // reset
                $isDefault = false;

                ?>
                <tr id="zbs-mail-delivery-<?php echo $accKey; ?>">
                    <td class="zbs-mail-delivery-item-details"><?php

                        if (count($zbsSMTPAccs) == 1 && $accIndx == 0) {
                            $isDefault = true;
                        } else {

                            if ($accKey == $defaultMailOptionIndex) $isDefault = true;

                        }

                        if ($isDefault) { ?><div class="ui ribbon label zbs-default"><?php _e('Default',"zero-bs-crm"); ?></div><?php }

                        #} account name etc.
                        $accStr = '';
                        if (isset($acc['fromname'])) $accStr = $acc['fromname'];
                        if (isset($acc['fromemail'])) {
                            if (!empty($accStr))
                                $accStr .= ' &lt;'.$acc['fromemail'].'&gt;';
                            else
                                $accStr .= $acc['fromemail'];
                        }
                        echo $accStr;

                        #} Mode label
                        ?>&nbsp;&nbsp;<div class="ui purple horizontal label"><?php
                            $modeStr = 'wp_mail';
                            if (isset($acc['mode']) && $acc['mode'] == 'smtp') $modeStr = 'SMTP';
                            echo $modeStr;
                            ?></div><?php

                        #} Detail
                        $detailStr = '';
                        if (isset($acc['host']) && !empty($acc['host'])) $detailStr = $acc['host'];
                        if ($detailMode) echo '<div class="zbs-mail-delivery-detail">'.$detailStr.'</div>';

                        ?></td>
                    <td style="text-align:center">
                        <button type="button" class="ui tiny green button zbs-test-mail-delivery" data-from="<?php echo $acc['fromemail']; ?>" data-indx="<?php echo $accKey; ?>"><i class="icon mail"></i> Send Test</button>&nbsp;
                        <button type="button" class="ui tiny orange button zbs-remove-mail-delivery" data-indx="<?php echo $accKey; ?>"><i class="remove circle icon"></i> Remove</button>&nbsp;
                        <button type="button" class="ui tiny teal button zbs-default-mail-delivery<?php if ($isDefault) echo ' disabled'; ?>" data-indx="<?php echo $accKey; ?>"><i class="check circle outline icon"></i> Set as Default</button>
                    </td>
                </tr>
                <?php $accIndx++; } ?>
            </tbody>
            <tfoot>
            <tr><th colspan="2">
                    <div class="ui right floated">
                        <button type="button" id="zbs-mail-delivery-start-wizard" class="ui primary button right floated"><i class="add circle icon"></i> <?php _e('Add Another','zero-bs-crm'); ?></button>
                    </div>
                </th>
            </tr></tfoot>
        </table>
        </div><?php

        #} ====================================

    }



    /* Wizard? */

    #} Default
    $smtpAcc = array(
        'sendfromname' => '',
        'sendfromemail' => ''
    );

    #} Never run, so autofill with wp defaults?
    if (count($zbsSMTPAccs) == 0){

        // should these be the active user, really?
        $smtpAcc['sendfromname'] = $defaultFromDeets['name'];
        $smtpAcc['sendfromemail'] = $defaultFromDeets['email'];

    }


    ?><div id="zbs-mail-delivery-wizard-wrap" class="hidden">


        <!--<h1 class="ui header blue" style="margin-top: 0;"><?php _e('Mail Delivery Setup',"zero-bs-crm"); ?></h1>-->


        <div class="ui three top attached steps">
            <div class="active step zbs-top-step-1">
                <i class="address card outline icon"></i>
                <div class="content">
                    <div class="title"><?php _e('Sender Details',"zero-bs-crm"); ?></div>
                    <div class="description"><?php _e('Who are you?',"zero-bs-crm"); ?></div>
                </div>
            </div>
            <div class="disabled step zbs-top-step-2">
                <i class="server icon"></i>
                <div class="content">
                    <div class="title"><?php _e('Mail Server',"zero-bs-crm"); ?></div>
                    <div class="description"><?php _e('Your SMTP Settings',"zero-bs-crm"); ?></div>
                </div>
            </div>
            <div class="disabled step zbs-top-step-3">
                <i class="open envelope outline icon"></i>
                <div class="content">
                    <div class="title"><?php _e('Confirmation',"zero-bs-crm"); ?></div>
                    <div class="description"><?php _e('Test &amp; Verify',"zero-bs-crm"); ?></div>
                </div>
            </div>
        </div>
        <div class="ui attached segment borderless" id="zbs-mail-delivery-wizard-steps-wrap">


            <!-- Step 1: -->
            <div id="zbs-mail-delivery-wizard-step-1-wrap" class="zbs-step">

                <h1 class="ui header"><?php _e('Sender Details',"zero-bs-crm"); ?> <i class="address card outline icon"></i></h1>

                <div class="ui very padded segment borderless">
                    <p>
                        <?php _e( 'Enter your "Send From" details below. For best brand impact we recommend using a same-domain email address, although any email you have SMTP details for will work.', 'zero-bs-crm' ); ?>
                        <?php ##WLREMOVE ?>
                        <a href="<?php echo $zbs->urls['kbsmtpsetup']; ?>"><?php _e( 'See the Guide', "zero-bs-crm" ); ?></a>
                        <?php ##/WLREMOVE ?>
                    </p>
                </div>

                <div class="ui inverted zbsdarkgradient segment">
                    <div class="ui inverted form">
                        <div class="field">
                            <label><?php _e('Send From Name',"zero-bs-crm"); ?>:</label>
                            <input id="zbs-mail-delivery-wizard-sendfromname" placeholder="<?php _e('e.g. Mike Mikeson',"zero-bs-crm"); ?>" type="text" value="<?php echo $smtpAcc['sendfromname']; ?>">
                            <div class="ui pointing label hidden" id="zbs-mail-delivery-wizard-sendfromname-error"></div>
                        </div>
                        <div class="field">
                            <label><?php _e('Send From Email',"zero-bs-crm"); ?>:</label>
                            <input id="zbs-mail-delivery-wizard-sendfromemail" placeholder="<?php _e('e.g. your@domain.com',"zero-bs-crm"); ?>" type="text" value="<?php echo $smtpAcc['sendfromemail']; ?>">
                            <div class="ui pointing label hidden" id="zbs-mail-delivery-wizard-sendfromemail-error"></div>
                        </div>
                        <div class="ui zbsclear">
                            <button type="button" class="ui button positive right floated" id="zbs-mail-delivery-wizard-step-1-submit"><?php _e('Next',"zero-bs-crm"); ?></button>
                        </div>

                    </div>
                </div>

            </div>
            <!-- / Step 1 -->

            <!-- Step 2: -->
            <div id="zbs-mail-delivery-wizard-step-2-wrap" class="hidden zbs-step">

                <h1 class="ui header"><?php _e('Mail Server',"zero-bs-crm"); ?> <i class="server icon"></i></h1>

                <div class="ui very padded segment borderless">
                    <p>
                        <?php _e( 'The CRM can send out emails via your default server settings, or via an SMTP server (mail server). If you would like to reliably send emails from a custom domain, we recommend entering SMTP details here.', 'zero-bs-crm' ); ?>
                        <?php ##WLREMOVE ?>
                        <a href="<?php echo $zbs->urls['kbsmtpsetup']; ?>"><?php _e('See the Guide',"zero-bs-crm"); ?></a>
                        <?php ##/WLREMOVE ?>
                    </p>
                </div>

                <div class="ui inverted zbsdarkgradient segment">
                    <div class="ui inverted form">

                        <div class="field">
                            <div class="ui radio checkbox" id="zbs-mail-delivery-wizard-step-2-servertype-wpmail">
                                <input type="radio" name="servertype" checked="checked" tabindex="0" class="hidden">
                                <label><?php _e('Default WordPress Mail (wp_mail)',"zero-bs-crm"); ?></label>
                            </div>
                        </div>



                        <div class="ui grid">
                            <div class="eight wide column">

                                <div class="field">
                                    <div class="ui radio checkbox" id="zbs-mail-delivery-wizard-step-2-servertype-smtp">
                                        <input type="radio" name="servertype" tabindex="0" class="hidden">
                                        <label><?php _e('Custom Mail Server (SMTP)',"zero-bs-crm"); ?></label>
                                    </div>
                                </div>

                            </div>
                            <div class="eight wide column hidden">

                                <div class="hidden" id="zbs-mail-delivery-wizard-step-2-prefill-smtp">
                                    <div class="field">
                                        <label for="smtpCommonProviders"><?php _e('Quick-fill SMTP Details',"zero-bs-crm"); ?>:</label>
                                        <select id="smtpCommonProviders">
                                            <option value="-1" selected="selected" disabled="disabled"><?php _e('Select a Common Provider',"zero-bs-crm"); ?>:</option>
                                            <option value="-1" disabled="disabled">=================</option>
                                            <?php # Hard typed: <option value="ses1" data-host="email-smtp.us-east-1.amazonaws.com" data-auth="tls" data-port="587" data-example="AKGAIR8K9UBGAZY5UMLA">AWS SES US East (N. Virginia)</option><option value="ses3" data-host="email-smtp.us-west-2.amazonaws.com" data-auth="tls" data-port="587" data-example="AKGAIR8K9UBGAZY5UMLA">AWS SES US West (Oregon)</option><option value="ses2" data-host="email-smtp.eu-west-1.amazonaws.com" data-auth="tls" data-port="587" data-example="AKGAIR8K9UBGAZY5UMLA">AWS SES EU (Ireland)</option><option value="sendgrid" data-host="smtp.sendgrid.net" data-auth="tls" data-port="587" data-example="you@yourdomain.com">SendGrid</option><option value="gmail" data-host="smtp.gmail.com" data-auth="ssl" data-port="465" data-example="you@gmail.com">GMail</option><option value="outlook" data-host="smtp.live.com" data-auth="tls" data-port="587" data-example="you@outlook.com">Outlook.com</option><option value="office365" data-host="smtp.office365.com" data-auth="tls" data-port="587" data-example="you@office365.com">Office365.com</option><option value="yahoo" data-host="smtp.mail.yahoo.com" data-auth="ssl" data-port="465" data-example="you@yahoo.com">Yahoo Mail</option><option value="yahooplus" data-host="plus.smtp.mail.yahoo.com" data-auth="ssl" data-port="465" data-example="you@yahoo.com">Yahoo Mail Plus</option><option value="yahoouk" data-host="smtp.mail.yahoo.co.uk" data-auth="ssl" data-port="465" data-example="you@yahoo.co.uk">Yahoo Mail UK</option><option value="aol" data-host="smtp.aol.com" data-auth="tls" data-port="587" data-example="you@aol.com">AOL.com</option><option value="att" data-host="smtp.att.yahoo.com" data-auth="ssl" data-port="465" data-example="you@att.com">AT&amp;T</option><option value="hotmail" data-host="smtp.live.com" data-auth="tls" data-port="587" data-example="you@hotmail.com">Hotmail</option><option value="oneandone" data-host="smtp.1and1.com" data-auth="tls" data-port="587" data-example="you@yourdomain.com">1 and 1</option><option value="zoho" data-host="smtp.zoho.com" data-auth="ssl" data-port="465" data-example="you@zoho.com">Zoho</option><option value="mailgun" data-host="smtp.mailgun.org" data-auth="ssl" data-port="465" data-example="postmaster@YOUR_DOMAIN_NAME">MailGun</option><option value="oneandonecom" data-host="smtp.1and1.com" data-auth="tls" data-port="587" data-example="you@yourdomain.com">OneAndOne.com</option><option value="oneandonecouk" data-host="auth.smtp.1and1.co.uk" data-auth="tls" data-port="587" data-example="you@yourdomain.co.uk">OneAndOne.co.uk</option>

                                            #} This allows easy update though :)
                                            $commonSMTPSettings = zeroBSCRM_mailDelivery_commonSMTPSettings();
                                            foreach ($commonSMTPSettings as $settingPerm => $settingArr){

                                                echo '<option value="'.$settingPerm.'" data-host="'.$settingArr['host'].'" data-auth="'.$settingArr['auth'].'" data-port="'.$settingArr['port'].'" data-example="'.$settingArr['userexample'].'">'.$settingArr['name'].'</option>';

                                            }

                                            ?>
                                        </select>
                                    </div> <!-- .field -->
                                </div>

                            </div>
                        </div>

                        <div class="ui inverted zbstrans segment hidden" id="zbs-mail-delivery-wizard-step-2-smtp-wrap">

                            <!-- SMTP DEETS -->
                            <div class="ui grid">
                                <div class="eight wide column">

                                    <div class="required field">
                                        <label for="zbs-mail-delivery-wizard-step-2-smtp-host"><?php _e('SMTP Address',"zero-bs-crm"); ?></label>
                                        <input type="text" placeholder="e.g. pro.turbo-smtp.com" id="zbs-mail-delivery-wizard-step-2-smtp-host" class="mailInp" value="" />
                                        <div class="ui pointing label hidden" id="zbs-mail-delivery-wizard-smtphost-error"></div>
                                    </div> <!-- .field -->
                                    <div class="required field">
                                        <label for="zbs-mail-delivery-wizard-step-2-smtp-port"><?php _e('SMTP Port',"zero-bs-crm"); ?></label>
                                        <div class="seven wide field">
                                            <input type="text" placeholder="e.g. 587 or 465" id="zbs-mail-delivery-wizard-step-2-smtp-port" class="mailInp" value="587" />
                                        </div>
                                        <div class="ui pointing label hidden" id="zbs-mail-delivery-wizard-smtpport-error"></div>
                                    </div> <!-- .field -->

                                    <!--
                                    <div class="ui toggle checkbox">
                                      <input type="checkbox" name="public">
                                      <label>Use SSL Authentication</label>
                                    </div>
                                    -->
                                </div>
                                <div class="eight wide column">


                                    <div class="required field">
                                        <label for="zbs-mail-delivery-wizard-step-2-smtp-user"><?php _e('Username',"zero-bs-crm"); ?></label>
                                        <input type="text" placeholder="e.g. mike or mike@yourdomain.com" id="zbs-mail-delivery-wizard-step-2-smtp-user" class="mailInp" value="" autocomplete="new-smtpuser-<?php echo time(); ?>" />
                                        <div class="ui pointing label hidden" id="zbs-mail-delivery-wizard-smtpuser-error"></div>
                                    </div> <!-- .field -->

                                    <div class="required field">
                                        <label for="zbs-mail-delivery-wizard-step-2-smtp-pass"><?php _e('Password',"zero-bs-crm"); ?></label>
                                        <input type="text" placeholder="" id="zbs-mail-delivery-wizard-step-2-smtp-pass" class="mailInp" value="" autocomplete="new-password-<?php echo time(); ?>" />
                                        <div class="ui pointing label hidden" id="zbs-mail-delivery-wizard-smtppass-error"></div>
                                    </div> <!-- .field -->

                                </div>
                            </div>
                            <!-- / SMTP DEETS -->


                        </div>
                        <div class="ui zbsclear">
                            <button type="button" class="ui button" id="zbs-mail-delivery-wizard-step-2-back"><?php _e('Back',"zero-bs-crm"); ?></button>
                            <button type="button" class="ui button positive right floated" id="zbs-mail-delivery-wizard-step-2-submit"><?php _e('Validate Settings',"zero-bs-crm"); ?></button>
                        </div>
                    </div> <!-- / inverted segment -->

                    <div class="ui very padded segment borderless">
                        <p><?php _e('After this step Jetpack CRM will probe your server and attempt to send a test email in order to validate your settings.',"zero-bs-crm"); ?></p>
                    </div>

                </div>

            </div>
            <!-- / Step 2 -->



            <!-- Step 3: -->
            <div id="zbs-mail-delivery-wizard-step-3-wrap" class="hidden zbs-step">

                <h1 class="ui header"><?php _e('Confirmation',"zero-bs-crm"); ?> <i class="open envelope outline icon"></i></h1>


                <div class="ui inverted zbsdarkgradient segment" id="zbs-mail-delivery-wizard-validate-console-wrap">

                    <div class="ui padded zbsbigico segment loading borderless" id="zbs-mail-delivery-wizard-validate-console-ico">&nbsp;</div>

                    <div class="ui padded segment borderless" id="zbs-mail-delivery-wizard-validate-console"><?php _e('Attempting to connect to mail server...',"zero-bs-crm"); ?></div>

                    <div class="ui padded segment borderless hidden" id="zbs-mail-delivery-wizard-admdebug"></div>

                    <div class="ui zbsclear">
                        <button type="button" class="ui hidden button" id="zbs-mail-delivery-wizard-step-3-back"><?php _e('Back',"zero-bs-crm"); ?></button>
                        <button type="button" class="ui button positive right floated disabled" id="zbs-mail-delivery-wizard-step-3-submit"><?php _e('Finish',"zero-bs-crm"); ?></button>
                    </div>

                </div>
            </div>

        <!-- / Step 3 -->
    </div>



</div>

<?php if ($runningLocally){

    ?><div class="ui message"><div class="header"><div class="ui yellow label"><?php _e('Local Machine?','zero-bs-crm'); ?></div></div><p><?php _e('It appears you are running Jetpack CRM locally, this may cause SMTP delivery methods to behave unexpectedly.<br />(e.g. your computer may block outgoing SMTP traffic via firewall or anti-virus software).<br />Jetpack CRM may require external web hosting to properly send via SMTP.','zero-bs-crm'); ?></p></div><?php


} ?>


    <style type="text/css">

        /*
            see scss
        */

    </style>

    <script type="text/javascript">

        var zeroBSCRM_sToken = '<?php echo wp_create_nonce( "wpzbs-ajax-nonce" ); ?>';
        var zeroBSCRM_currentURL = '<?php echo admin_url( "admin.php?page=zerobscrm-plugin-settings&tab=maildelivery" ); ?>';

        var zeroBSCRMJS_lang = {

            // generic
            pleaseEnter : '<?php zeroBSCRM_slashOut(__('Please enter a value',"zero-bs-crm")); ?>',
            pleaseEnterEmail : '<?php zeroBSCRM_slashOut(__('Please enter a valid email address',"zero-bs-crm")); ?>',
            thanks : '<?php zeroBSCRM_slashOut(__('Thank you',"zero-bs-crm")); ?>',
            defaultText: '<?php zeroBSCRM_slashOut(__('Default',"zero-bs-crm")); ?>',

            // email delivery setup
            settingsValidatedWPMail: '<?php zeroBSCRM_slashOut(__('Your Email Delivery option has been validated. A test email has been sent via wp_mail, the default WordPress mail provider, to: ',"zero-bs-crm")); ?>',
            settingsValidatedWPMailError: '<?php zeroBSCRM_slashOut( sprintf( __( 'There was an error sending a mail via wp_mail. Please go back and check your email address. If this persists please <a href="%s" target="_blank">contact support</a>.', 'zero-bs-crm' ), $zbs->urls['support'] ) ) ?>',
            settingsValidateSMTPProbing : '<?php zeroBSCRM_slashOut(__('Probing your mail server (this may take a few seconds)...',"zero-bs-crm")); ?>',
            settingsValidateSMTPPortCheck : '<?php zeroBSCRM_slashOut(__('Checking Ports are Open (this may take a few seconds)...',"zero-bs-crm")); ?>',
            settingsValidateSMTPAttempt : '<?php zeroBSCRM_slashOut(__('Attempting to send test email...',"zero-bs-crm")); ?>',
            settingsValidateSMTPSuccess : '<?php zeroBSCRM_slashOut(__('Test email sent...',"zero-bs-crm")); ?>',

            settingsValidatedSMTP: '<?php zeroBSCRM_slashOut(__('Your Email Delivery option has been validated. A test email has been sent via SMTP to the address below. Please check you received this email to ensure a complete test.',"zero-bs-crm")); ?> <a href="#debug" id="zbs-mail-delivery-showdebug"><?php zeroBSCRM_slashOut(__('debug output','zero-bs-crm')); ?></a> (<?php zeroBSCRM_slashOut(__('click to view','zero-bs-crm')); ?>).',
            settingsValidatedSMTPProbeError: '<?php zeroBSCRM_slashOut( sprintf( __( 'Jetpack CRM has tested your settings, and also tried probing your mail server, but unfortunately it was not possible to confirm a test email was sent. Please go back and check your settings, and if this persists please <a href="%s" target="_blank">contact support</a>, optionally sending us the <a href="#debug" id="zbs-mail-delivery-showdebug">debug output</a> (click to view).', 'zero-bs-crm' ), $zbs->urls['support'] ) ) ?>',
            settingsValidatedSMTPGeneralError: '<?php zeroBSCRM_slashOut( sprintf( __( 'There was an error sending a mail via SMTP. Please go back and check your settings, and if this persists please <a href="%s" target="_blank">contact support</a>, optionally sending us the <a href="#debug" id="zbs-mail-delivery-showdebug">debug output</a> (click to view).', 'zero-bs-crm' ), $zbs->urls['support'] ) ) ?>',

            // send test from list view
            sendTestMail: '<?php zeroBSCRM_slashOut(__('Send a test email from',"zero-bs-crm"));?>',
            sendTestButton: '<?php zeroBSCRM_slashOut(__('Send test',"zero-bs-crm"));?>',
            sendTestWhere: '<?php zeroBSCRM_slashOut(__('Which email address should we send the test email to?',"zero-bs-crm"));?>',
            sendTestFail: '<?php zeroBSCRM_slashOut(__('There was an error sending this test',"zero-bs-crm"));?>',
            sendTestSent: '<?php zeroBSCRM_slashOut(__('Test Sent Successfully',"zero-bs-crm"));?>',
            sendTestSentSuccess: '<?php zeroBSCRM_slashOut(__('Test email was successfully sent to',"zero-bs-crm"));?>',
            sendTestSentFailed: '<?php zeroBSCRM_slashOut(__('Test email could not be sent (problem with this mail delivery method?)',"zero-bs-crm"));?>',

            // delete mail delivery method via list view
            deleteMailDeliverySureTitle: '<?php zeroBSCRM_slashOut(__('Are you sure?',"zero-bs-crm"));?>',
            deleteMailDeliverySureText: '<?php zeroBSCRM_slashOut(__('This will totally remove this mail delivery method from your Jetpack CRM.',"zero-bs-crm"));?>',
            deleteMailDeliverySureConfirm: '<?php zeroBSCRM_slashOut(__('Yes, remove it!',"zero-bs-crm"));?>',
            deleteMailDeliverySureDeletedTitle: '<?php zeroBSCRM_slashOut(__('Delivery Method Removed',"zero-bs-crm"));?>',
            deleteMailDeliverySureDeletedText: '<?php zeroBSCRM_slashOut(__('Your mail delivery method has been successfully removed.',"zero-bs-crm"));?>',
            deleteMailDeliverySureDeleteErrTitle: '<?php zeroBSCRM_slashOut(__('Delivery Method Not Removed',"zero-bs-crm"));?>',
            deleteMailDeliverySureDeleteErrText: '<?php zeroBSCRM_slashOut(__('There was a general error removing this mail delivery method.',"zero-bs-crm"));?>',

            // set mail delivery method  as default via list view
            defaultMailDeliverySureTitle: '<?php zeroBSCRM_slashOut(__('Are you sure?',"zero-bs-crm"));?>',
            defaultMailDeliverySureText: '<?php zeroBSCRM_slashOut(__('Do you want to default to this mail delivery method?',"zero-bs-crm"));?>',
            defaultMailDeliverySureConfirm: '<?php zeroBSCRM_slashOut(__('Set as Default',"zero-bs-crm"));?>',
            defaultMailDeliverySureDeletedTitle: '<?php zeroBSCRM_slashOut(__('Default Saved',"zero-bs-crm"));?>',
            defaultMailDeliverySureDeletedText: '<?php zeroBSCRM_slashOut(__('Your mail delivery method default has been successfully saved.',"zero-bs-crm"));?>',
            defaultMailDeliverySureDeleteErrTitle: '<?php zeroBSCRM_slashOut(__('Default Not Updated',"zero-bs-crm"));?>',
            defaultMailDeliverySureDeleteErrText: '<?php zeroBSCRM_slashOut(__('There was a general error when setting this mail delivery method default.',"zero-bs-crm"));?>',

            likelytimeout: '<?php zeroBSCRM_slashOut(__('The Wizard timed out when trying to connect to your Mail Server. This probably means your server is blocking the SMTP port you have specified, please check with them that they have these ports open. If they will not open the ports, you may have to use wp_mail mode.',"zero-bs-crm"));?>',

        };

        var zeroBSCRMJS_SMTPWiz = {

            sendFromName: '',
            sendFromEmail: '',
            serverType: 'wp_mail',
            smtpHost: '',
            smtpPort: '',
            smtpUser: '',
            smtpPass: ''

        };

        // generic func - can we standardise this (wh)?
        function zeroBSCRMJS_refreshPage(){

            window.location = window.zeroBSCRM_currentURL;

        }

        jQuery(function(){

            // bind
            zeroBSCRMJS_mail_delivery_bindWizard();
            zeroBSCRMJS_mail_delivery_bindList();


        });

        // defaults for test delivery pass through for SWAL
        var zbsTestDelivery = false, zbsTestDeliveryMsg = '';

        // bind list view stuff
        function zeroBSCRMJS_mail_delivery_bindList(){

            jQuery('.zbs-test-mail-delivery').off('click').on( 'click', function(){

                // get deets
                var emailFrom = '', emailIndx = -1;

                emailIndx = jQuery(this).attr('data-indx');
                emailFrom = jQuery(this).attr('data-from');

                swal({
                    title: window.zeroBSCRMJS_lang.sendTestMail + ' "' + emailFrom + '"',
                    //text: window.zeroBSCRMJS_lang.sendTestWhere,
                    input: 'email',
                    inputValue: emailFrom, // prefill with itself
                    showCancelButton: true,
                    confirmButtonText: window.zeroBSCRMJS_lang.sendTestButton,
                    showLoaderOnConfirm: true,
                    preConfirm: function (email) {
                        return new Promise(function (resolve, reject) {

                            // localise indx
                            var lIndx = emailIndx;

                            // timeout for loading
                            setTimeout(function() {
                                if (!zbscrm_JS_validateEmail(email)) {
                                    reject(window.zeroBSCRMJS_lang.pleaseEnterEmail)
                                } else {

                                    var data = {
                                        'action': 'zbs_maildelivery_test',
                                        'indx': lIndx,
                                        'em': email,
                                        'sec': window.zeroBSCRM_sToken
                                    };

                                    // Send it Pat :D
                                    jQuery.ajax({
                                        type: "POST",
                                        url: ajaxurl,
                                        "data": data,
                                        dataType: 'json',
                                        timeout: 20000,
                                        success: function(response) {

                                            // localise
                                            var lEmail = email;

                                            window.zbsTestDelivery = 'success';
                                            window.zbsTestDeliveryMsg = window.zeroBSCRMJS_lang.sendTestSentSuccess + ' ' + lEmail;

                                            resolve();

                                        },
                                        error: function(response){

                                            window.zbsTestDelivery = 'fail';
                                            window.zbsTestDeliveryMsg = window.zeroBSCRMJS_lang.sendTestSentFailed;

                                            resolve();

                                        }

                                    });


                                }
                            }, 2000)
                        })
                    },
                    allowOutsideClick: false
                }).then(function (email) {

                    if (window.zbsTestDelivery == 'success'){

                        swal({
                            type: 'success',
                            title: window.zeroBSCRMJS_lang.sendTestSent,
                            html: window.zbsTestDeliveryMsg
                        });

                    } else {

                        swal({
                            type: 'warning',
                            title: window.zeroBSCRMJS_lang.sendTestFail,
                            html: window.zbsTestDeliveryMsg
                        });

                    }
                }).catch(swal.noop);

            });

            // REMOVE one :)
            jQuery('.zbs-remove-mail-delivery').off('click').on( 'click', function(){

                // get deets
                var emailIndx = -1;

                emailIndx = jQuery(this).attr('data-indx');


                swal({
                    title: window.zeroBSCRMJS_lang.deleteMailDeliverySureTitle,
                    text: window.zeroBSCRMJS_lang.deleteMailDeliverySureText,
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: window.zeroBSCRMJS_lang.deleteMailDeliverySureConfirm
                }).then(function (result) {
                    if (result.value) {

                        // localise indx
                        var lIndx = emailIndx;

                        var data = {
                            'action': 'zbs_maildelivery_remove',
                            'indx': lIndx,
                            'sec': window.zeroBSCRM_sToken
                        };

                        // Send it Pat :D
                        jQuery.ajax({
                            type: "POST",
                            url: ajaxurl,
                            "data": data,
                            dataType: 'json',
                            timeout: 20000,
                            success: function(response) {

                                console.log('del',response);

                                swal({
                                    title: window.zeroBSCRMJS_lang.deleteMailDeliverySureDeletedTitle,
                                    text: window.zeroBSCRMJS_lang.deleteMailDeliverySureDeletedText,
                                    type: 'success',
                                    // refresh onClose: zeroBSCRMJS_refreshPage
                                    onClose: function(){

                                        // remove line
                                        llIndx = lIndx;
                                        jQuery('#zbs-mail-delivery-' + llIndx).hide();

                                    }
                                });

                            },
                            error: function(response){

                                console.error('del',response);

                                swal(
                                    window.zeroBSCRMJS_lang.deleteMailDeliverySureDeleteErrTitle,
                                    window.zeroBSCRMJS_lang.deleteMailDeliverySureDeleteErrText,
                                    'warning'
                                );

                            }

                        });

                    }


                });

            });



            // Set as default
            jQuery('.zbs-default-mail-delivery').off('click').on( 'click', function(){

                // get deets
                var emailIndx = -1;

                emailIndx = jQuery(this).attr('data-indx');


                swal({
                    title: window.zeroBSCRMJS_lang.defaultMailDeliverySureTitle,
                    text: window.zeroBSCRMJS_lang.defaultMailDeliverySureText,
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: window.zeroBSCRMJS_lang.defaultMailDeliverySureConfirm
                }).then(function () {

                    // localise indx
                    var lIndx = emailIndx;

                    var data = {
                        'action': 'zbs_maildelivery_setdefault',
                        'indx': lIndx,
                        'sec': window.zeroBSCRM_sToken
                    };

                    // Send it Pat :D
                    jQuery.ajax({
                        type: "POST",
                        url: ajaxurl,
                        "data": data,
                        dataType: 'json',
                        timeout: 20000,
                        success: function(response) {

                            console.log('def',response);

                            swal({
                                title: window.zeroBSCRMJS_lang.defaultMailDeliverySureDeletedTitle,
                                text: window.zeroBSCRMJS_lang.defaultMailDeliverySureDeletedText,
                                type: 'success',
                                // refresh onClose: zeroBSCRMJS_refreshPage
                                onClose: function(){

                                    // remove other default labels + inject
                                    jQuery('#zbs-mail-delivery-account-list-wrap td.zbs-mail-delivery-item-details .zbs-default').remove();
                                    // undisable as well
                                    jQuery('#zbs-mail-delivery-account-list-wrap .zbs-default-mail-delivery.disabled').removeClass('disabled');

                                    llIndx = lIndx;
                                    jQuery('#zbs-mail-delivery-' + llIndx + ' td.zbs-mail-delivery-item-details').prepend('<div class="ui ribbon label zbs-default">' + window.zeroBSCRMJS_lang.defaultText + '</div>');
                                    jQuery('#zbs-mail-delivery-' + llIndx + ' .ui.button.zbs-default-mail-delivery').addClass('disabled');

                                }
                            });

                        },
                        error: function(response){

                            console.error('def',response);

                            swal(
                                window.zeroBSCRMJS_lang.defaultMailDeliverySureDeleteErrTitle,
                                window.zeroBSCRMJS_lang.defaultMailDeliverySureDeleteErrText,
                                'warning'
                            );

                        }

                    });




                });

            });

        }


        // bind wizard funcs
        function zeroBSCRMJS_mail_delivery_bindWizard(){

            // any of these?
            jQuery('.ui.radio.checkbox').checkbox();

            // start wiz
            jQuery('#zbs-mail-delivery-start-wizard').off('click').on( 'click', function(){

                // hide bits classed .zbs-non-wizard
                jQuery('.zbs-non-wizard',jQuery('#zbs-mail-delivery-wrap')).hide();

                // show wiz
                jQuery('#zbs-mail-delivery-wizard-wrap').show();


            });

            // step 1

            // submit
            jQuery('#zbs-mail-delivery-wizard-step-1-submit').off('click').on( 'click', function(){

                // test inputs & move on to step 2
                var okayToProceed = true;
                var sendFromName = jQuery('#zbs-mail-delivery-wizard-sendfromname').val();
                var sendFromEmail = jQuery('#zbs-mail-delivery-wizard-sendfromemail').val();

                // send from name
                if (sendFromName.length > 0){

                    // set it
                    window.zeroBSCRMJS_SMTPWiz.sendFromName = sendFromName;

                    // hide any msg
                    jQuery('#zbs-mail-delivery-wizard-sendfromname-error').html(window.zeroBSCRMJS_lang.thanks).addClass('hidden');

                } else {

                    // not okay
                    okayToProceed = false;

                    // msg
                    jQuery('#zbs-mail-delivery-wizard-sendfromname-error').html(window.zeroBSCRMJS_lang.pleaseEnter).removeClass('hidden');

                }

                // send from email
                if (sendFromEmail.length > 0 && zbscrm_JS_validateEmail(sendFromEmail)){

                    // set it
                    window.zeroBSCRMJS_SMTPWiz.sendFromEmail = sendFromEmail;

                    // hide any msg
                    jQuery('#zbs-mail-delivery-wizard-sendfromemail-error').html(window.zeroBSCRMJS_lang.thanks).addClass('hidden');

                } else {

                    // not okay
                    okayToProceed = false;

                    // msg
                    jQuery('#zbs-mail-delivery-wizard-sendfromemail-error').html(window.zeroBSCRMJS_lang.pleaseEnterEmail).removeClass('hidden');

                }

                // okay?
                if (okayToProceed){

                    jQuery('#zbs-mail-delivery-wizard-step-1-wrap').hide();
                    jQuery('#zbs-mail-delivery-wizard-step-2-wrap').show();

                    jQuery('.zbs-top-step-1').removeClass('active');
                    jQuery('.zbs-top-step-2').removeClass('disabled').addClass('active');

                    // Pre-fill user on next step SMTP ...
                    if (jQuery('#zbs-mail-delivery-wizard-step-2-smtp-user').val() == '') jQuery('#zbs-mail-delivery-wizard-step-2-smtp-user').val(sendFromEmail);

                }

            });



            // Step 2
            jQuery('#zbs-mail-delivery-wizard-step-2-wrap .ui.radio.checkbox').on( 'click', function(){

                // check mode
                var serverType = 'wp_mail';
                if (jQuery('#zbs-mail-delivery-wizard-step-2-servertype-smtp').checkbox('is checked')) serverType = 'smtp';

                // show hide
                if (serverType == 'smtp'){
                    jQuery('#zbs-mail-delivery-wizard-step-2-smtp-wrap').show();
                    jQuery('#zbs-mail-delivery-wizard-step-2-prefill-smtp').show();
                } else {
                    jQuery('#zbs-mail-delivery-wizard-step-2-smtp-wrap').hide();
                    jQuery('#zbs-mail-delivery-wizard-step-2-prefill-smtp').hide();
                }


            });

            // back button
            jQuery('#zbs-mail-delivery-wizard-step-2-back').off('click').on( 'click', function(){

                jQuery('#zbs-mail-delivery-wizard-step-1-wrap').show();
                jQuery('#zbs-mail-delivery-wizard-step-2-wrap').hide();

                jQuery('.zbs-top-step-1').removeClass('disabled').addClass('active');
                jQuery('.zbs-top-step-2').removeClass('active').addClass('disabled');

            });

            // quickfill smtp
            jQuery('#zbs-mail-delivery-wizard-step-2-prefill-smtp select').off('change').on( 'change', function(){

                // debug console.log(jQuery('#zbs-mail-delivery-wizard-step-2-prefill-smtp select').val());
                var v = jQuery('#zbs-mail-delivery-wizard-step-2-prefill-smtp select').val();

                // find deets
                jQuery('#zbs-mail-delivery-wizard-step-2-prefill-smtp select option').each(function(ind,ele){

                    if (jQuery(ele).val() == v){

                        // fill out + break
                        jQuery('#zbs-mail-delivery-wizard-step-2-smtp-host').val(jQuery(ele).attr('data-host'));
                        jQuery('#zbs-mail-delivery-wizard-step-2-smtp-port').val(jQuery(ele).attr('data-port'));
                        jQuery('#zbs-mail-delivery-wizard-step-2-smtp-user').attr('placeholder',jQuery(ele).attr('data-example'));
                        //data-host="email-smtp.us-east-1.amazonaws.com" data-auth="tls" data-port="587" data-example="AKGAIR8K9UBGAZY5UMLA"

                        return true;

                    }


                });


            });

            // check over deets
            jQuery('#zbs-mail-delivery-wizard-step-2-submit').off('click').on( 'click', function(){

                // test inputs & move on to step 2
                var okayToProceed = true;

                // wpmail or smtp?
                var serverType = 'wp_mail';
                if (jQuery('#zbs-mail-delivery-wizard-step-2-servertype-smtp').checkbox('is checked')) serverType = 'smtp';

                // smtp?
                if (serverType == "smtp"){

                    var smtpHost = jQuery('#zbs-mail-delivery-wizard-step-2-smtp-host').val();
                    var smtpPort = jQuery('#zbs-mail-delivery-wizard-step-2-smtp-port').val();
                    var smtpUser = jQuery('#zbs-mail-delivery-wizard-step-2-smtp-user').val();
                    var smtpPass = jQuery('#zbs-mail-delivery-wizard-step-2-smtp-pass').val();

                    // first check lengths of them all

                    if (smtpHost.length > 0){
                        // set it
                        window.zeroBSCRMJS_SMTPWiz.smtpHost = smtpHost;
                        // hide any msg
                        jQuery('#zbs-mail-delivery-wizard-smtphost-error').html(window.zeroBSCRMJS_lang.thanks).addClass('hidden');
                    } else {
                        // not okay
                        okayToProceed = false;
                        // msg
                        jQuery('#zbs-mail-delivery-wizard-smtphost-error').html(window.zeroBSCRMJS_lang.pleaseEnter).removeClass('hidden');
                    }

                    if (smtpPort.length > 0){
                        // set it
                        window.zeroBSCRMJS_SMTPWiz.smtpPort = smtpPort;
                        // hide any msg
                        jQuery('#zbs-mail-delivery-wizard-smtpport-error').html(window.zeroBSCRMJS_lang.thanks).addClass('hidden');
                    } else {
                        // not okay
                        okayToProceed = false;
                        // msg
                        jQuery('#zbs-mail-delivery-wizard-smtpport-error').html(window.zeroBSCRMJS_lang.pleaseEnter).removeClass('hidden');
                    }

                    if (smtpUser.length > 0){
                        // set it
                        window.zeroBSCRMJS_SMTPWiz.smtpUser = smtpUser;
                        // hide any msg
                        jQuery('#zbs-mail-delivery-wizard-smtpuser-error').html(window.zeroBSCRMJS_lang.thanks).addClass('hidden');
                    } else {
                        // not okay
                        okayToProceed = false;
                        // msg
                        jQuery('#zbs-mail-delivery-wizard-smtpuser-error').html(window.zeroBSCRMJS_lang.pleaseEnter).removeClass('hidden');
                    }

                    if (smtpPass.length > 0){
                        // set it
                        window.zeroBSCRMJS_SMTPWiz.smtpPass = smtpPass;
                        // hide any msg
                        jQuery('#zbs-mail-delivery-wizard-smtppass-error').html(window.zeroBSCRMJS_lang.thanks).addClass('hidden');
                    } else {
                        // not okay
                        okayToProceed = false;
                        // msg
                        jQuery('#zbs-mail-delivery-wizard-smtppass-error').html(window.zeroBSCRMJS_lang.pleaseEnter).removeClass('hidden');
                    }





                } // end if smtp

                // wpmail
                if (serverType == 'wp_mail'){

                    // no validation req.

                } // end if wpmail



                // okay?
                if (okayToProceed){

                    jQuery('#zbs-mail-delivery-wizard-step-2-wrap').hide();
                    jQuery('#zbs-mail-delivery-wizard-step-3-wrap').show();

                    jQuery('.zbs-top-step-2').removeClass('active');
                    jQuery('.zbs-top-step-3').removeClass('disabled').addClass('active');

                    // start validator
                    zeroBSCRMJS_validateSettings();

                }

            });

            // back button
            jQuery('#zbs-mail-delivery-wizard-step-3-back').off('click').on( 'click', function(){

                jQuery('#zbs-mail-delivery-wizard-step-2-wrap').show();
                jQuery('#zbs-mail-delivery-wizard-step-3-wrap').hide();

                jQuery('.zbs-top-step-2').removeClass('disabled').addClass('active');
                jQuery('.zbs-top-step-3').removeClass('active').addClass('disabled');

            });

            // fini button
            jQuery('#zbs-mail-delivery-wizard-step-3-submit').off('click').on( 'click', function(){

                window.location = window.zeroBSCRM_currentURL;

            });



        }

        // takes settings in window.zeroBSCRMJS_SMTPWiz and attempts to validate
        // (assumes present values)
        function zeroBSCRMJS_validateSettings(){

            /* window.zeroBSCRMJS_SMTPWiz
              sendFromName: '',
              sendFromEmail: '',
              serverType: 'wp_mail',
              smtpHost: '',
              smtpPort: '',
              smtpUser: '',
              smtpPass: ''
            */


            var serverType = 'wp_mail';
            if (jQuery('#zbs-mail-delivery-wizard-step-2-servertype-smtp').checkbox('is checked')) serverType = 'smtp';

            // step through:
            //<i class="terminal icon"></i>
            //<i class="handshake icon"></i>
            //<i class="mail outline icon"></i>
            //<i class="open envelope outline icon"></i>

            // clear prev debug
            jQuery('#zbs-mail-delivery-wizard-admdebug').html('').hide();

            switch (serverType){

                case 'wp_mail':

                    // easy - fire of a test via ajax, but will "work" in as far as validation

                    // loading
                    jQuery('#zbs-mail-delivery-wizard-validate-console-ico').addClass('loading');

                    // postbag! - NOTE: This also adds a new Mail Delivery line to the options (or updates an old one with same email)
                    var data = {
                        'action': 'zbs_maildelivery_validation_wp_mail',
                        'sendFromName': window.zeroBSCRMJS_SMTPWiz.sendFromName,
                        'sendFromEmail': window.zeroBSCRMJS_SMTPWiz.sendFromEmail,
                        'sec': window.zeroBSCRM_sToken
                    };

                    // Send it Pat :D
                    jQuery.ajax({
                        type: "POST",
                        url: ajaxurl,
                        "data": data,
                        dataType: 'json',
                        timeout: 20000,
                        success: function(response) {

                            // remove loading
                            jQuery('#zbs-mail-delivery-wizard-validate-console-ico').removeClass('loading').html('<i class="open envelope outline icon"></i>');
                            jQuery('#zbs-mail-delivery-wizard-validate-console').html('');

                            // success?
                            if (typeof response.success != "undefined"){

                                // show result
                                var resHTML = window.zeroBSCRMJS_lang.settingsValidatedWPMail + '<div class="zbs-validated">' + window.zeroBSCRMJS_SMTPWiz.sendFromEmail + '</div>';
                                jQuery('#zbs-mail-delivery-wizard-validate-console').html(resHTML);

                                // enable finish button, remove back button
                                jQuery('#zbs-mail-delivery-wizard-step-3-back').hide();
                                jQuery('#zbs-mail-delivery-wizard-step-3-submit').show().removeClass('disabled');

                            } else {

                                // some kind of error, suggest retry
                                var resHTML = window.zeroBSCRMJS_lang.settingsValidatedWPMailError;
                                jQuery('#zbs-mail-delivery-wizard-validate-console').html(resHTML);
                                jQuery('#zbs-mail-delivery-wizard-validate-console-ico').html('<i class="warning sign icon"></i>');

                                // enable back button, disable finish button
                                jQuery('#zbs-mail-delivery-wizard-step-3-back').show();
                                jQuery('#zbs-mail-delivery-wizard-step-3-submit').addClass('disabled');

                            }

                        },
                        error: function(response){

                            // remove loading
                            jQuery('#zbs-mail-delivery-wizard-validate-console-ico').removeClass('loading');
                            jQuery('#zbs-mail-delivery-wizard-validate-console-ico').html('<i class="warning sign icon"></i>');

                            // some kind of error, suggest retry
                            var resHTML = window.zeroBSCRMJS_lang.settingsValidatedWPMailError;
                            jQuery('#zbs-mail-delivery-wizard-validate-console').html(resHTML);

                            // enable back button, disable finish button
                            jQuery('#zbs-mail-delivery-wizard-step-3-back').show();
                            jQuery('#zbs-mail-delivery-wizard-step-3-submit').addClass('disabled');

                        }

                    });


                    break;

                case 'smtp':

                    // less easy - fire of a test via ajax, return varied responses :)

                    // loading
                    jQuery('#zbs-mail-delivery-wizard-validate-console-ico').addClass('loading');
                    jQuery('#zbs-mail-delivery-wizard-validate-console').html(window.zeroBSCRMJS_lang.settingsValidateSMTPPortCheck);


                    // FIRST check ports open (step 1)
                    var data = {
                        'action': 'zbs_maildelivery_validation_smtp_ports',
                        'smtpHost': window.zeroBSCRMJS_SMTPWiz.smtpHost,
                        'smtpPort': window.zeroBSCRMJS_SMTPWiz.smtpPort,
                        'sec': window.zeroBSCRM_sToken
                    };

                    // Send it Pat :D
                    jQuery.ajax({
                        type: "POST",
                        url: ajaxurl,
                        "data": data,
                        dataType: 'json',
                        timeout: 60000,
                        success: function(response) {

                            if (typeof response.open != "undefined" && response.open){

                                // NORMAL - validate smtp via send:
                                jQuery('#zbs-mail-delivery-wizard-validate-console').html(window.zeroBSCRMJS_lang.settingsValidateSMTPProbing);


                                // postbag! - NOTE: This also adds a new Mail Delivery line to the options (or updates an old one with same email)
                                var data = {
                                    'action': 'zbs_maildelivery_validation_smtp',
                                    'sendFromName': window.zeroBSCRMJS_SMTPWiz.sendFromName,
                                    'sendFromEmail': window.zeroBSCRMJS_SMTPWiz.sendFromEmail,
                                    'smtpHost': window.zeroBSCRMJS_SMTPWiz.smtpHost,
                                    'smtpPort': window.zeroBSCRMJS_SMTPWiz.smtpPort,
                                    'smtpUser': window.zeroBSCRMJS_SMTPWiz.smtpUser,
                                    'smtpPass': window.zeroBSCRMJS_SMTPWiz.smtpPass,
                                    'sec': window.zeroBSCRM_sToken
                                };

                                // Send it Pat :D
                                jQuery.ajax({
                                    type: "POST",
                                    url: ajaxurl,
                                    "data": data,
                                    dataType: 'json',
                                    timeout: 60000,
                                    success: function(response) {

                                        // console.log('SMTP',response);

                                        // 2.94.2 we also added hidden output of all debugs (click to show)
                                        if (typeof response.debugs != "undefined"){

                                            var debugStr = '';
                                            if (response.debugs.length > 0) jQuery.each(response.debugs,function(ind,ele){

                                                debugStr += '<hr />' + ele;

                                            });
                                            jQuery('#zbs-mail-delivery-wizard-admdebug').html('<strong>Debug Log</strong>:<br />' + debugStr);
                                        }

                                        // remove loading + play routine for now (no seperate ajax tests here)
                                        jQuery('#zbs-mail-delivery-wizard-validate-console').html(window.zeroBSCRMJS_lang.settingsValidateSMTPProbing);
                                        jQuery('#zbs-mail-delivery-wizard-validate-console-ico').removeClass('loading').html('<i class="terminal icon"></i>');

                                        setTimeout(function(){

                                            // attempting to send msg
                                            jQuery('#zbs-mail-delivery-wizard-validate-console').html(window.zeroBSCRMJS_lang.settingsValidateSMTPAttempt);
                                            jQuery('#zbs-mail-delivery-wizard-validate-console-ico').html('<i class="terminal icon"></i>');


                                            // fly or die:


                                            // success?
                                            if (typeof response.success != "undefined" && response.success){

                                                // sent
                                                var resHTML = window.zeroBSCRMJS_lang.settingsValidateSMTPSuccess;
                                                jQuery('#zbs-mail-delivery-wizard-validate-console').html(resHTML);
                                                jQuery('#zbs-mail-delivery-wizard-validate-console-ico').html('<i class="mail outline icon"></i>');

                                                setTimeout(function(){

                                                    //console.log('x',window.zeroBSCRMJS_SMTPWiz.smtpHost);
                                                    // show result
                                                    var resHTML = window.zeroBSCRMJS_lang.settingsValidatedSMTP + '<div class="zbs-validated">' + window.zeroBSCRMJS_SMTPWiz.sendFromEmail + '</div>';
                                                    jQuery('#zbs-mail-delivery-wizard-validate-console').html(resHTML);
                                                    jQuery('#zbs-mail-delivery-wizard-validate-console-ico').html('<i class="open envelope outline icon"></i>');

                                                    // enable finish button, remove back button
                                                    jQuery('#zbs-mail-delivery-wizard-step-3-back').hide();
                                                    jQuery('#zbs-mail-delivery-wizard-step-3-submit').show().removeClass('disabled');

                                                    setTimeout(function(){
                                                        // bind show debug
                                                        jQuery('#zbs-mail-delivery-showdebug').off('click').on( 'click', function(e){
                                                            jQuery('#zbs-mail-delivery-wizard-admdebug').toggle();
                                                            e.preventDefault();
                                                        });
                                                    },0);

                                                },1000);


                                            } else {

                                                // some kind of error, suggest retry
                                                var resHTML = window.zeroBSCRMJS_lang.settingsValidatedSMTPProbeError;
                                                jQuery('#zbs-mail-delivery-wizard-validate-console').html(resHTML);
                                                jQuery('#zbs-mail-delivery-wizard-validate-console-ico').html('<i class="warning sign icon"></i>');

                                                // enable back button, disable finish button
                                                jQuery('#zbs-mail-delivery-wizard-step-3-back').show();
                                                jQuery('#zbs-mail-delivery-wizard-step-3-submit').addClass('disabled');

                                                // bind show debug
                                                jQuery('#zbs-mail-delivery-showdebug').off('click').on( 'click', function(){
                                                    jQuery('#zbs-mail-delivery-wizard-admdebug').toggle();
                                                });


                                                setTimeout(function(){
                                                    // bind show debug
                                                    jQuery('#zbs-mail-delivery-showdebug').off('click').on( 'click', function(e){
                                                        jQuery('#zbs-mail-delivery-wizard-admdebug').toggle();
                                                        e.preventDefault();
                                                    });
                                                },0);

                                            }


                                        },1000);


                                        setTimeout(function(){
                                            // bind show debug
                                            jQuery('#zbs-mail-delivery-showdebug').off('click').on( 'click', function(e){
                                                jQuery('#zbs-mail-delivery-wizard-admdebug').toggle();
                                                e.preventDefault();
                                            });
                                        },0);




                                    },
                                    error: function(response){

                                        // debug (likely timed out)
                                        jQuery('#zbs-mail-delivery-wizard-admdebug').html('<strong>Debug Log</strong>:<br />' + window.zeroBSCRMJS_lang.likelytimeout);

                                        // remove loading
                                        jQuery('#zbs-mail-delivery-wizard-validate-console-ico').removeClass('loading');
                                        jQuery('#zbs-mail-delivery-wizard-validate-console-ico').html('<i class="warning sign icon"></i>');

                                        // some kind of error, suggest retry
                                        var resHTML = window.zeroBSCRMJS_lang.settingsValidatedSMTPGeneralError;
                                        jQuery('#zbs-mail-delivery-wizard-validate-console').html(resHTML);

                                        // enable back button, disable finish button
                                        jQuery('#zbs-mail-delivery-wizard-step-3-back').show();
                                        jQuery('#zbs-mail-delivery-wizard-step-3-submit').addClass('disabled');

                                        setTimeout(function(){
                                            // bind show debug
                                            jQuery('#zbs-mail-delivery-showdebug').off('click').on( 'click', function(e){
                                                jQuery('#zbs-mail-delivery-wizard-admdebug').toggle();
                                                e.preventDefault();
                                            });
                                        },0);


                                    }

                                });


                            } // had open ports
                            else {

                                // ports blocked

                                // 2.94.2 we also added hidden output of all debugs (click to show)
                                if (typeof response.debugs != "undefined"){

                                    var debugStr = '';
                                    if (response.debugs.length > 0) jQuery.each(response.debugs,function(ind,ele){

                                        debugStr += '<hr />' + ele;

                                    });
                                    jQuery('#zbs-mail-delivery-wizard-admdebug').html('<strong>Debug Log (Ports Blocked)</strong>:<br />' + debugStr);


                                    // remove loading
                                    jQuery('#zbs-mail-delivery-wizard-validate-console-ico').removeClass('loading');
                                    jQuery('#zbs-mail-delivery-wizard-validate-console-ico').html('<i class="warning sign icon"></i>');

                                    // some kind of error, suggest retry
                                    var resHTML = window.zeroBSCRMJS_lang.likelytimeout;
                                    jQuery('#zbs-mail-delivery-wizard-validate-console').html(resHTML);

                                    // enable back button, disable finish button
                                    jQuery('#zbs-mail-delivery-wizard-step-3-back').show();
                                    jQuery('#zbs-mail-delivery-wizard-step-3-submit').addClass('disabled');

                                    setTimeout(function(){
                                        // bind show debug
                                        jQuery('#zbs-mail-delivery-showdebug').off('click').on( 'click', function(e){
                                            jQuery('#zbs-mail-delivery-wizard-admdebug').toggle();
                                            e.preventDefault();
                                        });
                                    },0);
                                }


                            }



                        },
                        error: function(response){

                            // debug (likely timed out)
                            jQuery('#zbs-mail-delivery-wizard-admdebug').html('<strong>Debug Log (Ports Blocked)</strong>:<br />' + window.zeroBSCRMJS_lang.likelytimeout);

                            // remove loading
                            jQuery('#zbs-mail-delivery-wizard-validate-console-ico').removeClass('loading');
                            jQuery('#zbs-mail-delivery-wizard-validate-console-ico').html('<i class="warning sign icon"></i>');

                            // some kind of error, suggest retry
                            var resHTML = window.zeroBSCRMJS_lang.likelytimeout;
                            jQuery('#zbs-mail-delivery-wizard-validate-console').html(resHTML);

                            // enable back button, disable finish button
                            jQuery('#zbs-mail-delivery-wizard-step-3-back').show();
                            jQuery('#zbs-mail-delivery-wizard-step-3-submit').addClass('disabled');

                            setTimeout(function(){
                                // bind show debug
                                jQuery('#zbs-mail-delivery-showdebug').off('click').on( 'click', function(e){
                                    jQuery('#zbs-mail-delivery-wizard-admdebug').toggle();
                                    e.preventDefault();
                                });
                            },0);



                        }

                    });




                    break;

            } // / switch


        }

    </script>
</div>
