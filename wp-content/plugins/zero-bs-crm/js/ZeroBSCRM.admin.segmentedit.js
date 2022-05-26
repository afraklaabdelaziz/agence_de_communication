/*!
 * Jetpack CRM
 * https://jetpackcrm.com
 * V2.5
 *
 * Copyright 2020 Automattic
 *
 * Date: 09/01/18
 */


jQuery(function(){

	// build out initial
	zeroBSCRMJS_segment_buildConditions();

	// bind post-render
	setTimeout(function(){

		zeroBSCRMJS_segment_bindPostRender();

	},0);

});


function zeroBSCRMJS_segment_bindPostRender(){

	// on change of type, rebuild line
	jQuery('.zbs-segment-edit-var-condition-type').off('change').on( 'change', function(){
		
		// which to update? (this stops this updating all the other lines)
		jQuery(this).closest('.zbs-segment-edit-condition').addClass('dirty');

		// set cascading options
		zeroBSCRMJS_segment_buildConditionCascades();

	});

	// on change of type, rebuild line
	jQuery('.zbs-segment-edit-var-condition-operator').off('change').on( 'change', function(){
		
		// which to update? (this stops this updating all the other lines)
		jQuery(this).closest('.zbs-segment-edit-condition').addClass('dirty');

		// set cascading options
		zeroBSCRMJS_segment_buildConditionCascades2();

	});

	jQuery('#zbs-segment-edit-act-add-condition').off('click').on( 'click', function(){

		// add an empty
		var html = zeroBSCRMJS_segment_buildConditionLine(false);

		// set it
		jQuery('#zbs-segment-edit-conditions').append(html);

		// build cascading options (post render)
		setTimeout(function(){

			// which to update? (this stops this updating all the other lines) - it'll be the last, as was just added
			jQuery('.zbs-segment-edit-condition').last().addClass('dirty');

			// .. which in turn builds its own cascading value...
			zeroBSCRMJS_segment_buildConditionCascades();

			// bind post-render
			setTimeout(function(){

				zeroBSCRMJS_segment_bindPostRender();

			},0);

		},0);

	});

	// remove conditions
	jQuery('.zbs-segment-edit-condition-remove').off('click').on( 'click', function(){

		// if more than 1 left...
		if (jQuery('.zbs-segment-edit-condition').length > 1){
			
			jQuery(this).closest('.zbs-segment-edit-condition').remove();

		} else {

			// show notice
			jQuery('#zbs-segment-edit-conditions-err').show();

			// hide in 2 s
			setTimeout(function(){

				jQuery('#zbs-segment-edit-conditions-err').hide();

			},1800);
		}

	});

	// hover over remove
	jQuery('.zbs-segment-edit-condition-remove').off('hover').on( 'mouseenter', function(){
		jQuery(this).addClass('orange')
	}).on( 'mouseleave', function(){
		jQuery(this).removeClass('orange')
	});
	

	// preview audience / continue
	jQuery('#zbs-segment-edit-act-p2preview').off('click').on( 'click', function(){

		zeroBSCRMJS_segment_previewAudience();

	});

	// save segment
	jQuery('#zbs-segment-edit-act-p2submit, #zbs-segment-edit-act-save').off('click').on( 'click', function(){

		zeroBSCRMJS_segment_saveSegmentAct();

	});

	// back to segment list
	jQuery('#zbs-segment-edit-act-back').off('click').on( 'click', function(){

		// what if not saved? :o
		window.location = window.zbsSegmentListURL;

	});


}

/*
* Builds the segment editor condition lines
* (based on window.zbsSegment obj)
*/
function zeroBSCRMJS_segment_buildConditions(){

	// build html
	var html = '';

	// build any existing (rules)
	if (typeof window.zbsSegment != "undefined" && typeof window.zbsSegment.conditions != "undefined"){

		jQuery.each(window.zbsSegment.conditions,function(ind,ele){

			html += zeroBSCRMJS_segment_buildConditionLine(ele);

		});

	} else {

		// add an empty
		html += zeroBSCRMJS_segment_buildConditionLine(false);

	}

	// set it
	jQuery('#zbs-segment-edit-conditions').append(html);

	// build cascading options (post render)
	setTimeout(function(){

		// this'll build ALL :) 
		jQuery('.zbs-segment-edit-condition').addClass('dirty');

		// .. which in turn builds its own cascading value...
		zeroBSCRMJS_segment_buildConditionCascades(jQuery('#zbs-segment-edit-conditions'));

	},0);

}

// builds out a condition/rule line
function zeroBSCRMJS_segment_buildConditionLine(rule){

	var html = '<div class="zbs-segment-edit-condition ui corner labeled segment"';
	// for existing
	if (typeof rule != "undefined" && rule !== false){
		if (typeof rule.id != "undefined") html += ' id="zbs-segment-edit-condition-' + rule.id + '"';
		if (typeof rule.operator != "undefined") html += ' data-orig-operator="' + rule.operator + '"';

		// these are 'parsed' (e.g. dates)
		//if (typeof rule.value != "undefined") html += ' data-orig-value="' + rule.value + '"';
		//if (typeof rule.value2 != "undefined") html += ' data-orig-value2="' + rule.value2 + '"';
		if (typeof rule.valueconv != "undefined") html += ' data-orig-value="' + rule.valueconv + '"';
		if (typeof rule.value2conv != "undefined") html += ' data-orig-value2="' + rule.value2conv + '"';

	}
	html += '>';

	// add remove label
	html += '<div class="ui corner label zbs-segment-edit-condition-remove"><i class="remove icon"></i></div>';

	// if rule == false it's an empty new one

		// condition selector
		if (typeof window.zbsAvailableConditions != "undefined"){

			// check type of condition is available
			// (e.g. catch adv segments condition type in existing segment, but no adv segments)
			if ( rule !== false && typeof window.zbsAvailableConditions != "undefined" && typeof window.zbsAvailableConditions[ rule.type ] == "undefined" ){

				// has condition not currently supported. 
				// add in as a 'disabled' option (so saving doesn't remove it, but user can remove it/not edit it)
				html += '<i class="red exclamation triangle icon"></i> <input type="text" disabled="disabled" class="zbs-segment-edit-var-condition-type" value="' + rule.type + '" />';

			} else {

				// rule is of acceptable type (or a new line)

				// type e.g. STATUS
				html += '<select class="zbs-segment-edit-var-condition-type ui dropdown">';

				// in end do this in 2 - generics/non generics (easier to seperate)
				var isGeneric = false;

					// non generic
					jQuery.each(window.zbsAvailableConditions,function(ind,ele){

						if (typeof ele.generic == "undefined"){
							html += '<option value="' + ind + '"';
							if (typeof rule != "undefined" && rule !== false && typeof rule.type != "undefined" && rule.type == ind) html += ' selected="selected"';
							html += '>' + ele.name + '</option>';
						} else isGeneric = true;

					});

					// generic
					if (isGeneric){

						html += '<optgroup label="' + window.zbsSegmentLang.contactfields + '">';

						jQuery.each(window.zbsAvailableConditions,function(ind,ele){

							if (typeof ele.generic != "undefined"){
								html += '<option value="' + ind + '"';
								if (typeof rule != "undefined" && rule !== false && typeof rule.type != "undefined" && rule.type == ind) html += ' selected="selected"';
								html += '>' + ele.name + '</option>';
							}

						});

						// close it out
						html += '</optgroup>';

					}

				html += '</select>';

				// Operator e.g. = (this'll be built based on first, post render :)

				// Value e.g. 123 (this'll be built based on first, post render :)

			}

		}


	html += '</div>';

	return html;

}

// cycle through each condition + build out their "casacde" options
function zeroBSCRMJS_segment_buildConditionCascades(){

	jQuery('.zbs-segment-edit-condition.dirty').each(function(ind,ele){

		zeroBSCRMJS_segment_buildConditionCascadesForEle(ele);

	});

	// build cascading options (post render)
	setTimeout(function(){

		// .. which in turn builds its own cascading value...
		zeroBSCRMJS_segment_buildConditionCascades2();

	},0);

}


// for each condition + build out their "casacde" options
function zeroBSCRMJS_segment_buildConditionCascadesForEle(ele){

	// what's selected?
	var selected = jQuery('.zbs-segment-edit-var-condition-type',jQuery(ele)).val();
	//console.log('selectd:',selected);

	if (typeof selected != "undefined"){

		// clear existing + start html
		jQuery('.zbs-segment-edit-var-condition-operator',jQuery(ele)).remove();
		var html = '';

		// appropriate operator
		if (typeof window.zbsAvailableConditions[selected] != "undefined"){

			if ( 
				typeof window.zbsAvailableConditions[selected].operators != "undefined" 
				&& window.zbsAvailableConditions[selected].operators.length > 0
				&& window.zbsAvailableConditions[selected].operators[0] != "tag"
				&& window.zbsAvailableConditions[selected].operators[0] != "tag_transaction"
				&& window.zbsAvailableConditions[selected].operators[0] != "extsource" ){

				// get orig if building from scratch
				var origVal = ''; if (typeof jQuery(ele).attr('data-orig-operator') !== "undefined") origVal = jQuery(ele).attr('data-orig-operator');

				// build select for each
				html += '<select class="zbs-segment-edit-var-condition-operator ui dropdown">';
				jQuery.each(window.zbsAvailableConditions[selected].operators,function(ind2,ele2){

					html += '<option value="' + ele2 + '"';
					// needs to check if setting 
					//if (typeof rule != "undefined" && rule !== false && typeof rule.type != "undefined" && rule.type == ind) html += ' selected="selected"';
					if (ele2 == origVal) html += ' selected="selected"';
					html += '>' + window.zbsAvailableConditionOperators[ele2].name + '</option>';

				});
				html += '</select>';


			} else {

				// is tagged, or ext source? pass hidden input
				if ( window.zbsAvailableConditions[selected].operators[0] == "tag" || window.zbsAvailableConditions[selected].operators[0] == "tag_transaction" || window.zbsAvailableConditions[selected].operators[0] == "extsource" ) {
				
					html += '<input type="hidden" class="zbs-segment-edit-var-condition-operator" value="' + window.zbsAvailableConditions[selected].operators[0] + '" />';

				}

			}

		} else {

				// get original value
				var original_value = ''; if (typeof jQuery(ele).attr('data-orig-operator') !== "undefined") original_value = jQuery(ele).attr('data-orig-operator');

				// add in as a 'disabled' option (so saving doesn't remove it, but user can remove it/not edit it)
				html += '<input type="text" disabled="disabled" class="zbs-segment-edit-var-condition-operator segment-condition-errored" value="' + original_value + '" />';

		}

		// appropriate value selector


		// append
		jQuery(ele).append(html);

		// mark clean
		// actually leave that for cascade 2 jQuery(ele).removeClass('dirty');

	}

}

// cycle through each operator + build out their "casacde" value
function zeroBSCRMJS_segment_buildConditionCascades2(){

	jQuery('.zbs-segment-edit-condition.dirty').each(function(ind,ele){

		// what's selected?
		var selected = jQuery('.zbs-segment-edit-var-condition-operator',jQuery(ele)).val();
		var typeselected = jQuery('.zbs-segment-edit-var-condition-type',jQuery(ele)).val();
		//console.log('selectd:',[typeselected,selected]);

		if (typeof selected != "undefined"){

			// clear existing + start html
			jQuery('.zbs-segment-edit-var-condition-value, .zbs-segment-edit-var-condition-value-2, span',jQuery(ele)).remove();
			var html = '';

			// allows injection on edit page (if injected, then remove after inserted/loaded)
			var v = '', v2 = '';
			if (typeof jQuery(ele).attr('data-orig-value') !== "undefined") v = jQuery(ele).attr('data-orig-value');
			if (typeof jQuery(ele).attr('data-orig-value2') !== "undefined") v2 = jQuery(ele).attr('data-orig-value2');

			//console.log("building " + selected);

			// appropriate value selector
			switch (selected){

				// these two, the operator is the value, in a way, is a backward way of doing this, but 
				// only used for hypo for now... value ignored, operator used to filter
				case 'istrue':
				case 'isfalse':

					html += '<input type="hidden" class="zbs-segment-edit-var-condition-value" value="1" />';

					break;

				case 'equal':
				case 'notequal':

					if (typeselected == 'status'){

						// status ddl
						html += '<select class="zbs-segment-edit-var-condition-value ui dropdown">';

							if (typeof window.zbsAvailableStatuses != "undefined" && window.zbsAvailableStatuses.length > 0){

								jQuery.each(window.zbsAvailableStatuses,function(ind2,ele2){

									html += '<option value="' + ele2 + '"';
										if (v == ele2) html += ' selected="selected"';
									html += '>' + ele2 + '</option>';

								});

							} else {

								html += '<option value="">' + zeroBSCRMJS_segmentLang('nostatuses') + '</option>';
							}

						html += '</select>';

					} else {

						// other input
						html += '<input type="text" class="zbs-segment-edit-var-condition-value" value="'+v+'" />';

					}

					break;

				case 'contains':
				case 'larger':
				case 'less':

					html += '<input type="text" class="zbs-segment-edit-var-condition-value" value="'+v+'" />';

					break;

				case 'before':
				case 'after':

					html += '<input type="text" class="zbs-date-time zbs-segment-edit-var-condition-value" value="'+v+'" />';

					break;

				case 'daterange':

					html += '<input type="text" class="zbs-date-range-condition zbs-segment-edit-var-condition-value" value="'+v+' - '+v2+'" />';

					break;


				case 'floatrange':

					html += '<input type="text" class="zbs-float zbs-segment-edit-var-condition-value zbs-segment-pair-input" value="'+v+'" placeholder="' + zeroBSCRMJS_segmentLang('eg') + ' 0.00" />';
					html += '<span>' + zeroBSCRMJS_segmentLang('to') + '</span><input type="text" class="zbs-float zbs-segment-edit-var-condition-value-2 zbs-segment-pair-input" value="'+v2+'" placeholder="' + zeroBSCRMJS_segmentLang('eg') + ' 100.00" />';

					break;


				case 'intrange':

					html += '<input type="text" class="zbs-int zbs-segment-edit-var-condition-value zbs-segment-pair-input" value="'+v+'" placeholder="' + zeroBSCRMJS_segmentLang('eg') + ' 0" />';
					html += '<span>' + zeroBSCRMJS_segmentLang('to') + '</span><input type="text" class="zbs-int zbs-segment-edit-var-condition-value-2 zbs-segment-pair-input" value="'+v2+'" placeholder="' + zeroBSCRMJS_segmentLang('eg') + ' 100" />';

					break;

				case 'tag':

					// select of avail tags
					html += '<select class="zbs-segment-edit-var-condition-value ui dropdown">';

						if (typeof window.jpcrm_available_contact_tags != "undefined" && window.jpcrm_available_contact_tags.length > 0){

							jQuery.each(window.jpcrm_available_contact_tags,function(ind2,ele2){

								html += '<option value="' + ele2.id + '"';
									if (v == ele2.id) html += ' selected="selected"';
								html += '>' + ele2.name + '</option>';

							});

						} else {

							html += '<option value="">' + zeroBSCRMJS_segmentLang('notags') + '</option>';
						}

					html += '</select>';

					break;

				// transaction tags
				case 'tag_transaction':

					// select of avail tags
					html += '<select class="zbs-segment-edit-var-condition-value ui dropdown">';

						if (typeof window.jpcrm_available_transaction_tags != "undefined" && window.jpcrm_available_transaction_tags.length > 0){

							jQuery.each(window.jpcrm_available_transaction_tags,function(ind2,ele2){

								html += '<option value="' + ele2.id + '"';
									if (v == ele2.id) html += ' selected="selected"';
								html += '>' + ele2.name + '</option>';

							});

						} else {

							html += '<option value="">' + zeroBSCRMJS_segmentLang('notags') + '</option>';
						}

					html += '</select>';

					break;

				case 'extsource':

					// select of avail external sources
					html += '<select class="zbs-segment-edit-var-condition-value ui dropdown">';

						if ( typeof window.jpcrm_external_source_list != "undefined" && window.jpcrm_external_source_list.length > 0 ){

							jQuery.each( window.jpcrm_external_source_list, function( ind2, ele2 ){

								html += '<option value="' + ele2.key + '"';
									if (v == ele2.key) html += ' selected="selected"';
								html += '>' + ele2.name + '</option>';

							});

						} else {

							html += '<option value="">' + zeroBSCRMJS_segmentLang('noextsources') + '</option>';
						}

					html += '</select>';

					break;



			}


			// potentially has condition not currently supported. 
			if ( typeof window.zbsAvailableConditions[typeselected] == "undefined" ){

				// add in as hidden values (so saving doesn't remove it, but user can remove it/not edit it)
				html = '<input type="hidden" class="zbs-segment-edit-var-condition-value" value="' + v + '" />';
				html += '<input type="hidden" class="zbs-segment-edit-var-condition-value-2" value="' + v2 + '" />';

				// display a namesake
				var original_value = v; 
				if ( v2 != '' ){
					original_value += '-' + v2;
				}
				html += '<input type="text" disabled="disabled" class="segment-condition-errored" value="' + original_value + '" />';

			}

			// append
			jQuery(ele).append(html);

			// remove the v's if orig passed
			if (typeof jQuery(ele).attr('data-orig-value') !== "undefined") jQuery(ele).removeAttr('data-orig-value');
			if (typeof jQuery(ele).attr('data-orig-value2') !== "undefined") jQuery(ele).removeAttr('data-orig-value2');
			if (typeof jQuery(ele).attr('data-orig-operator') !== "undefined") jQuery(ele).removeAttr('data-orig-operator');

			setTimeout(function(){

				// date pickers
				zbscrm_JS_bindDateRangePicker();

				// our specific daterange setup
				zeroBSCRMJS_segment_bindDateRangePicker();

				// this makes sure we're rebuilding post operator change
				zeroBSCRMJS_segment_bindPostRender();

			},0);

		}


		// mark clean
		jQuery(ele).removeClass('dirty');
		

	});

}


function zeroBSCRMJS_segment_bindDateRangePicker(){

		// default hard typed
     	var localeOpt = {
	            format: "DD.MM.YYYY",
	            cancelLabel: 'Clear'
	    };

     	// this lets you override - see zeroBSCRM_date_localeForDaterangePicker + core zbs_root
     	if (typeof window.zbs_root.localeOptions != "undefined") localeOpt = window.zbs_root.localeOptions;


		jQuery('.zbs-date-range-condition').daterangepicker({

					// this specific settings
					"alwaysShowCalendars": true,
					"opens": "left",
					showDropdowns:true,

					// standard settings from admin global js
					//... except this: autoUpdateInput: false,
					locale: localeOpt,//{format: 'DD.MM.YYYY',cancelLabel: 'Clear'},
					ranges: {'Today': [moment(), moment()],'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],'Last 7 Days': [moment().subtract(6, 'days'), moment()],'Last 30 Days': [moment().subtract(29, 'days'), moment()],'This Month': [moment().startOf('month'), moment().endOf('month')],'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]},
					callback: function (start,end,period){

				        //? jQuery('#zbs-crm-customerfilter-addedrange-reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
				        //? jQuery('#zbs-crm-customerfilter-addedrange').val(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

					}
				});
}

function zeroBSCRMJS_segment_previewAudience(){

	// id's
	var snameid 		= 'zbs-segment-edit-var-title';
	var smatchtypeid 	= 'zbs-segment-edit-var-matchtype';

	// retrieve
	var sname 		= jQuery('#' + snameid).val();
	var smatchtype 	= jQuery('#' + smatchtypeid).val();

	// check (these also show "required")
	var errors = 0;
	if (!zeroBSCRMJS_genericCheckNotEmptySemantic(snameid)) {
		errors++;
		// and focus it as on this ui might be far down page
		jQuery('#zbs-segment-edit-var-title').focus();
	}
	// trust matchtype for now

	// clear these
	jQuery('#zbs-segment-edit-emptypreview-err').hide();

	if (errors == 0){

		// continue

			// check conditions
			var sconditions = [];
			jQuery('.zbs-segment-edit-condition').each(function(ind,ele){

				// get vars
				var type = jQuery('.zbs-segment-edit-var-condition-type',jQuery(ele)).val();
				var operator = jQuery('.zbs-segment-edit-var-condition-operator',jQuery(ele)).val();
				var value1 = jQuery('.zbs-segment-edit-var-condition-value',jQuery(ele)).val();
				var value2 = jQuery('.zbs-segment-edit-var-condition-value-2',jQuery(ele)).val();

					// operator will be empty for those such as tagged
					if (typeof operator == "undefined" || operator == "undefined") operator = -1;

				var condition = {

						'type': type,
						'operator': operator,
						'value': value1,
						'value2': value2

				};

				//Nope.if (typeof value2 != "undefined") condition.value += '|'.value2;

				// push
				sconditions.push(condition);

			});

			// if good, get preview audience
			// for now that means 1 + :)
			if (sconditions.length > 0){

				// loading button
				jQuery('#zbs-segment-edit-act-p2preview').addClass('loading');
				jQuery('#zbs-segment-edit-act-save, #zbs-segment-edit-act-delete').addClass('loading');

				// make a segment obj
				var segment = {

					title: sname,
					matchtype: smatchtype,
					conditions: sconditions

				};

				// if ID present in local obj, inject (update not insert)
				if (typeof window.zbsSegment != "undefined" && typeof window.zbsSegment.id != "undefined") segment.id = window.zbsSegment.id;

				// fire ajax save
				zeroBSCRMJS_segment_previewSegment(segment,function(previewList){

					// successfully retrieved

						// localise
						var seg = segment;

						// build preview out
						var html = '';

							// catch errors
							if (typeof previewList == "undefined") var previewList = {list:[],count:0};

							if (previewList.count == 0){

								// no contacts returned
								html = '';
								jQuery('#zbs-segment-edit-emptypreview-err').show();

							} else {

								// some contacts returned, show in table 
								html += '<h2 class="ui header" style="margin-left:0">' + previewList.count + ' ' + zeroBSCRMJS_segmentLang('currentlyInSegment') + '</h2>';
								html += '<table class="ui celled striped table"><thead><tr><th colspan="2">' + zeroBSCRMJS_segmentLang('previewTitle') + ':</th></tr></thead><tbody>';
									
									// for each preview line
									jQuery.each(previewList.list,function(ind,ele){

										var fn = ele.fullname; if (fn == '') fn = zeroBSCRMJS_segmentLang('noName');
										var em = ele.email; if (em == '') em = zeroBSCRMJS_segmentLang('noEmail');

										html += '<tr><td>' + fn + '</td><td>' + em + '</td></tr>';

									});

								html += '</tbody></table>';

							}

						// inject
						jQuery('#zbs-segment-edit-preview-output').html(html);

						// loading button
						jQuery('#zbs-segment-edit-act-p2preview').removeClass('loading');
						jQuery('#zbs-segment-edit-act-save, #zbs-segment-edit-act-delete').removeClass('loading').prop( 'disabled', false );

						// show
						jQuery('#zbs-segment-edit-preview').show();

				},function(r){

					// error saving

						// loading button
						jQuery('#zbs-segment-edit-act-p2preview').removeClass('loading');
						jQuery('#zbs-segment-edit-act-save, #zbs-segment-edit-act-delete').removeClass('loading').prop( 'disabled', false );

						// err			
						swal(
						  zeroBSCRMJS_segmentLang('generalerrortitle') + ' #219',
						  zeroBSCRMJS_segmentLang('generalerror'),
						  'error'
						);


				});





			} else {

				// shouldn't be able to fire :)

					// show notice
					jQuery('#zbs-segment-edit-conditions-err').show();

					// hide in 2 s
					setTimeout(function(){

						jQuery('#zbs-segment-edit-conditions-err').hide();

					},1800);
			}


	}
}

// this matches preview substancially, but it's effecetively preview + 1 step
function zeroBSCRMJS_segment_saveSegmentAct(){

	// id's
	var snameid 		= 'zbs-segment-edit-var-title';
	var smatchtypeid 	= 'zbs-segment-edit-var-matchtype';

	// retrieve
	var sname 		= jQuery('#' + snameid).val();
	var smatchtype 	= jQuery('#' + smatchtypeid).val();

	// check (these also show "required")
	var errors = 0;
	if (!zeroBSCRMJS_genericCheckNotEmptySemantic(snameid)) {
		errors++;
		// and focus it as on this ui might be far down page
		jQuery('#zbs-segment-edit-var-title').focus();
	}
	// trust matchtype for now

	if (errors == 0){

		// show blocker
		// not using jQuery('#zbs-segment-editor-blocker').removeClass('hidden');
		jQuery('#zbs-segment-edit-act-p2submit').addClass('loading').attr('disabled','disabled');
		jQuery('#zbs-segment-edit-act-p2preview').addClass('loading').attr('disabled','disabled');

		// and any header buttons
		jQuery('#zbs-segment-edit-act-save, #zbs-segment-edit-act-delete').addClass('loading').attr('disabled','disabled');

		// continue

			// check conditions
			var sconditions = [];
			jQuery('.zbs-segment-edit-condition').each(function(ind,ele){

				// get vars
				var type = jQuery('.zbs-segment-edit-var-condition-type',jQuery(ele)).val();
				var operator = jQuery('.zbs-segment-edit-var-condition-operator',jQuery(ele)).val();
				var value1 = jQuery('.zbs-segment-edit-var-condition-value',jQuery(ele)).val();
				var value2 = jQuery('.zbs-segment-edit-var-condition-value-2',jQuery(ele)).val();

					// operator will be empty for those such as tagged
					if (typeof operator == "undefined" || operator == "undefined") operator = -1;

				var condition = {

						'type': type,
						'operator': operator,
						'value': value1,
						'value2': value2

				};

				// Nope if (typeof value2 != "undefined") condition.value += '|'.value2;

				// push
				sconditions.push(condition);

			});

			// if good, get preview audience
			// for now that means 1 + :)
			if (sconditions.length > 0){

				// make a segment obj
				var segment = {

					title: sname,
					matchtype: smatchtype,
					conditions: sconditions

				};

				// if ID present in local obj, inject (update not insert)
				if (typeof window.zbsSegment != "undefined" && typeof window.zbsSegment.id != "undefined") segment.id = window.zbsSegment.id;

				// fire ajax save
				zeroBSCRMJS_segment_saveSegment(segment,function(id){

					// successfully saved

						// needs page refresh? (if new -> edit, not edit->edit)
						var refreshReq = false; if (window.zbsSegment == false) refreshReq = true;

						// localise
						var seg = segment;

						// update local obj
						window.zbsSegment.id = id;
						window.zbsSegment.name = seg.title;
						window.zbsSegment.matchtype = seg.matchtype;
						window.zbsSegment.conditions = seg.conditions;

						// inject
						jQuery('#zbs-segment-edit-preview-output').html('');
						jQuery('#zbs-segment-edit-preview').show();
						
						// hide blocker
						// not using jQuery('#zbs-segment-editor-blocker').addClass('hidden');
						jQuery('#zbs-segment-edit-act-p2submit').removeClass('loading').prop( 'disabled', false );
						jQuery('#zbs-segment-edit-act-p2preview').removeClass('loading').prop( 'disabled', false );
						jQuery('#zbs-segment-edit-act-save, #zbs-segment-edit-act-delete').removeClass('loading').prop( 'disabled', false );

						jQuery('#zbs-segment-edit-act-save').html(window.zbsSegmentLang.savedSegment);

						setTimeout(function(){

							// reset
							jQuery('#zbs-segment-edit-act-save').html(window.zbsSegmentLang.saveSegment);

						},1500);

						// move to edit page
						if (refreshReq) window.location = window.zbsSegmentStemURL + id;

				},function(r){

					// error saving
						
						// hide blocker
						// not using jQuery('#zbs-segment-editor-blocker').addClass('hidden');
						jQuery('#zbs-segment-edit-act-p2submit').removeClass('loading').prop( 'disabled', false );
						jQuery('#zbs-segment-edit-act-p2preview').removeClass('loading').prop( 'disabled', false );
						jQuery('#zbs-segment-edit-act-save, #zbs-segment-edit-act-delete').removeClass('loading').prop( 'disabled', false );

						// err			
						swal(
						  zeroBSCRMJS_segmentLang('generalerrortitle') + ' #221',
						  zeroBSCRMJS_segmentLang('generalerror'),
						  'error'
						);


				});





			} else {

				// shouldn't be able to fire :)

					// show notice
					jQuery('#zbs-segment-edit-conditions-err').show();
						
					// hide blocker
					// not using jQuery('#zbs-segment-editor-blocker').addClass('hidden');
					jQuery('#zbs-segment-edit-act-p2submit').removeClass('loading').prop( 'disabled', false );
					jQuery('#zbs-segment-edit-act-p2preview').removeClass('loading').prop( 'disabled', false );
					jQuery('#zbs-segment-edit-act-save, #zbs-segment-edit-act-delete').removeClass('loading').prop( 'disabled', false );

					// hide in 2 s
					setTimeout(function(){

						jQuery('#zbs-segment-edit-conditions-err').hide();

					},1800);
			}


	}
}


// this should be in a semantic helper js file
// checks a semantic ui field and returns true/false if empty + shows 'required' txt if empty
function zeroBSCRMJS_genericCheckNotEmptySemantic(eleid){

	var val	= jQuery('#' + eleid).val();

	if (typeof val == "undefined" || val.length < 1){

		// show error + mark field
		jQuery('#' + eleid).closest('.field').addClass('error');
		jQuery('.ui.message',jQuery('#' + eleid).closest('.field')).show();


		return false;

	} else {

		// hide errors
		jQuery('#' + eleid).closest('.field').removeClass('error');
		jQuery('.ui.message',jQuery('#' + eleid).closest('.field')).hide();

		return true;
	}

	return false;
}


var zbsAJAXSending = false;
function zeroBSCRMJS_segment_saveSegment(segment,callback,cbfail){

	// if not blocked
	if (!window.zbsAJAXSending){ window.zbsAJAXSending = true;

		// pull through vars
		var sID = -1;

			// get id from passed js if avail
			// from obj if (typeof window.zbsSegment != "undefined" && typeof window.zbsSegment.id != "undefined") sID = window.zbsSegment.id;
			// from passed
			if (typeof segment != "undefined" && typeof segment.id != "undefined") sID = segment.id;

			// get deets - whatever's passed is updated, so don't pass nulls
			var data = {
				'action': 'zbs_segment_savesegment',
				'sID': sID,
				'sec': window.zbsSegmentSEC
			};

			// pass into data
			if (typeof segment.title != "undefined") 		data.sTitle = segment.title;
			if (typeof segment.matchtype != "undefined") 	data.sMatchType = segment.matchtype;
			if (typeof segment.conditions != "undefined") 	data.sConditions = segment.conditions;


			// Send it Pat :D
			jQuery.ajax({
				type: "POST",
				url: ajaxurl,
				"data": data,
				timeout: 20000,
				success: function(response) {

					//console.log("response",response);

					// unblock
					window.zbsAJAXSending = false;

					// any callback
					if (typeof callback == "function") callback(response.id);

						return true;

				},
				error: function(response){

					//console.log('err',response);

					// unblock
					window.zbsAJAXSending = false;

					// any callback
					if (typeof cbfail == "function") cbfail(response);

						return false;

			 	}

			});

	} // / ifnot blocked
}

// load a preview of segment
function zeroBSCRMJS_segment_previewSegment(segment,callback,cbfail){

	// if not blocked
	if (!window.zbsAJAXSending){ window.zbsAJAXSending = true;

		// pull through vars
		var sID = -1;

			// get id from passed js if avail
			// from obj if (typeof window.zbsSegment != "undefined" && typeof window.zbsSegment.id != "undefined") sID = window.zbsSegment.id;
			// from passed
			if (typeof segment != "undefined" && typeof segment.id != "undefined") sID = segment.id;

			// get deets - whatever's passed is updated, so don't pass nulls
			var data = {
				'action': 'zbs_segment_previewsegment',
				'sID': sID,
				'sec': window.zbsSegmentSEC
			};

			// pass into data
			if (typeof segment.title != "undefined") 		data.sTitle = segment.title;
			if (typeof segment.matchtype != "undefined") 	data.sMatchType = segment.matchtype;
			if (typeof segment.conditions != "undefined") 	data.sConditions = segment.conditions;


			// Send it Pat :D
			jQuery.ajax({
				type: "POST",
				url: ajaxurl,
				"data": data,
				timeout: 20000,
				success: function(response) {

					//console.log("response",response);

					// unblock
					window.zbsAJAXSending = false;

					// any callback
					if (typeof callback == "function") callback(response);

					return true;

				},
				error: function(response){

					//console.log('err',response);

					// unblock
					window.zbsAJAXSending = false;

					// any callback
					if (typeof cbfail == "function") cbfail(response);

					return false;

			 	}

			});

	} // / ifnot blocked
}

function zeroBSCRMJS_segmentLang(key){
	if (typeof window.zbsSegmentLang[key] != "undefined") return window.zbsSegmentLang[key];
	return '';
}