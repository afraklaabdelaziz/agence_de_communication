/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 */

 /*
		Javascript for admin systems pages
 */
jQuery(document).ready(function() {

	// init
	jpcrm_init_systempage();


});


// Initialise the page
function jpcrm_init_systempage(){

        //jQuery('.tabular.menu .item').tab();
        jQuery('#jpcrm-system-manager .tabular.menu .item').tab({
            context: '#jpcrm-system-manager'
        });

        // accordian
        jQuery('.ui.accordion').accordion();

}