(function($) {

	$(document).ready(function(){
	/**
     * Saving indicator
     * @param method
     */
      
        function themehunk_megamenu_saving_indicator( method ) {
            if (method == 'show'){
                $('#mmth-saving-indicator').show();
            }else if(method =='hide'){
                $('#mmth-saving-indicator').fadeOut();
            }
        }

        function add_themehunk_megamenu_events_to_widget(widget) {
            var update = widget.find(".widget-action");
            var close = widget.find(".widget-controls .close");
            var id = widget.attr("id");
            update.on('click', function(){
                if (! widget.hasClass("open")) {
                    //Supporting Black Studio TinyMCE
                    if ( widget.is( '[id*=black-studio-tinymce]' ) ) {
                        bstw( widget ).deactivate().activate();
                    }
                    $( document ).trigger('widget-added', [widget]);
                    widget.toggleClass("open");
                }else{
                    widget.toggleClass('open');
                }
            });
            close.on('click', function (e) {
                e.preventDefault();
                widget.removeClass('open');
            });
            $(".widget").not(widget).removeClass("open");
        }

		function themehunk_megamenu_ajax_request_load_menu_item_settings ( menu_item_id, depth = 0, menu_id, menu_item_title = '' ) {
	        $.ajax({
	            type: 'post',
	            url:themehunk_megamenu_obj.ajax_url,
	            data: {
	                action:'themehunk_megamenu_item_settings_load',
	                menu_item_id: menu_item_id,
	                menu_item_depth: depth,
	                menu_id: menu_id,
	                menu_item_title: menu_item_title,
	                themehunk_megamenu_nonce: themehunk_megamenu_obj.themehunk_megamenu_nonce
	            },
	            cache: false,
	            beforeSend: function () {
	                // $('.themehunk-megamenu-item-settings-content').html('<div class="mmth-item-loading"></div>');
	            },
	            success: function (response) {
	            	// initiate_sortable();
	                $('.themehunk-megamenu-item-settings-content').html( response );
                    $('.themehunk-megamenu-item-settings-heading').text( menu_item_title );
                    begin_sorting();


                    let myColorPicker = (index,value_)=>{
                    const inputElement = jQuery(value_);
                    const defaultColor = inputElement.css('background-color');
                    const pickr = new Pickr({
                      el:value_,
                      useAsButton: true,
                      default: defaultColor,
                      theme: 'nano', // or 'monolith', or 'nano'
                      swatches: [
                        'rgba(244, 67, 54, 1)',
                        'rgba(233, 30, 99, 0.95)',
                        'rgba(156, 39, 176, 0.9)',
                        'rgba(103, 58, 183, 0.85)',
                        'rgba(63, 81, 181, 0.8)',
                        'rgba(33, 150, 243, 0.75)',
                        'rgba(255, 193, 7, 1)'
                      ],
                      components: {
                        preview: true,
                        opacity: true,
                        hue: true,
                        interaction: {
                        input: true,
                        }
                      }
                    }).on('change',(color,instance)=>{
                      let color_ = color.toRGBA().toString(0);
                      // preview css on input editor item
                      inputElement.css('background-color',color_);
                      // apply color on selected item
                      inputElement.val(color_);
                      inputElement.change();
                    });
                }
                // your selector input
                let selectedElem_ = jQuery('input.color_picker_megamenu');
                jQuery.each(selectedElem_,myColorPicker);
                //getting empty widgetId for for WordPress 4.8 widgets when popup settings is opened, closed and
                // reopened
                if (wp.textWidgets !== undefined) {
                    wp.textWidgets.widgetControls = {}; // WordPress 4.8 Text Widget
                }
                if (wp.mediaWidgets !== undefined) {
                    wp.mediaWidgets.widgetControls = {}; // WordPress 4.8 Media Widgets
                }

                $('.widget').each(function() {
                    add_themehunk_megamenu_events_to_widget($(this));
                });

	            }
	        });
   		}

        function themehunk_megamenu_grid_setup( layout_format, layout_name ) {
        
                var layout_selector = $('#themehunk_megamenu_item_layout_wrap');
               var menu_item_id = $('.themehunk-megamenu-status-hidden').val();
                console.log(menu_item_id);  
                var menu_id = $('input#menu').val();
                
                var current_rows = $('#themehunk_megamenu_item_layout_wrap .themehunk-megamenu-row').length;

                $.ajax({
                    url : themehunk_megamenu_obj.ajax_url,
                    type : 'post',
                    data : {
                        action : 'themehunk_megamenu_save_layout',
                        layout_format: layout_format,
                        layout_name: layout_name,
                        current_rows:current_rows, 
                        menu_item_id : menu_item_id,
                        menu_id : menu_id,
                        themehunk_megamenu_nonce: themehunk_megamenu_obj.themehunk_megamenu_nonce
                    },
                    success : function( response ) {
                        console.log(response.data);
                        themehunk_megamenu_ajax_request_load_menu_item_settings (menu_item_id, 0, menu_id);
                    }
                }); 

                layout_selector.closest('.menu-layout-wrapper').hide();
        } 

        function themehunk_megamenu_delete_row( delete_button ){
            var button_clicked = delete_button ;
            var menu_item_id = $('.themehunk-megamenu-status-hidden').val(); 
            var row_id = parseInt( button_clicked.closest('.themehunk-megamenu-row').data('row-id'));
            var data = {
                action: 'themehunk_megamenu_delete_row',
                menu_item_id: menu_item_id,
                row_id: row_id,
                themehunk_megamenu_nonce: themehunk_megamenu_obj.themehunk_megamenu_nonce
            };
            $.post(ajaxurl, data, function (response) {
                if (response.success){
                    button_clicked.closest('.themehunk-megamenu-row').remove();
                }
            });
        }

        function begin_sorting(){
            $('.themehunk-megamenu-item-wrap').sortable({
                connectWith: ".themehunk-megamenu-item-wrap, .themehunk-megamenu-draggable-widgets-list",
                items: " .widget",
                placeholder: "drop-highlight",
                // containment: ".themehunk-megamenuDraggableWidgetArea",
                start: function(event, ui) {
                    // themehunk_megamenu_saving_indicator('show');
                    var from_item_index = ui.item.attr('data-item-key-id'); // Item inside themehunk-megamenu-item-wrap class
                    var item_order = $(this).sortable('toArray', {attribute: 'data-item-key-id'}).toString();
                    var menu_item_id = parseInt($(this).closest('.themehunk-megamenu-item-settins-wrap').attr('data-item-id'));

                    var row_id = parseInt($(this).closest('.themehunk-megamenu-row').attr('data-row-id'));
                    var col_id = parseInt($(this).closest('.themehunk-megamenu-col').attr('data-col-id'));  
                },
            receive: function(event, ui) {
                themehunk_megamenu_saving_indicator('show');
                var from_item_index = ui.item.attr('data-item-key-id');
                var item_order = $(this).sortable('toArray', {attribute: 'data-item-key-id'}).toString();
                var last_index = item_order.split(',').pop();

                var menu_item_id = parseInt($(this).closest('.themehunk-megamenu-item-settins-wrap').attr('data-item-id'));

                var row_id = parseInt($(this).closest('.themehunk-megamenu-row').attr('data-row-id'));
                var col_id = parseInt($(this).closest('.themehunk-megamenu-col').attr('data-col-id'));    

                var from_row_id = parseInt(ui.sender.closest('.themehunk-megamenu-row').attr('data-row-id'));
                var from_col_id = parseInt(ui.sender.closest('.themehunk-megamenu-col').attr('data-col-id'));  
                // console.log(from_item_index);       

                //outside-widget drag to inside
                if (ui.sender.attr('data-type') === 'outside-widget'){
                    var reorder_item_type = ui.sender.attr('data-type');
                    var widget_base_id = ui.sender.attr('data-widget-id-base');

                    var data = {
                        action: 'themehunk_megamenu_drag_to_add_widget_item',
                        menu_item_id: menu_item_id,
                        row_id: row_id,
                        col_id: col_id,
                        // type : 'connect',
                        widget_base_id : widget_base_id,
                        // reorder_item_type : reorder_item_type,
                        // themehunk_megamenu_nonce: themehunk_megamenu_obj.themehunk_megamenu_nonce
                    };
                    console.log(data);
                    //Saving via post method in db
                    $.post(themehunk_megamenu_obj.ajax_url, data, function (response) {
                        console.log(response);
                        if (response.success){
                            var menu_id = $('input#menu').val();
                            console.log(response.data);
                            themehunk_megamenu_ajax_request_load_menu_item_settings(menu_item_id, 0, menu_id);
                        }
                        themehunk_megamenu_saving_indicator('hide');
                    });
                    }else {
                        //rearrange inner widget or menu item
                        var from_item_order = ui.sender.sortable('toArray', {attribute: 'data-item-key-id'}).toString();

                        var data = {
                            action: 'themehunk_megamenu_reorder_items',
                            menu_item_id: menu_item_id,
                            item_order: item_order,
                            row_id: row_id,
                            col_id: col_id,

                            type : 'connect',
                            from_item_order : from_item_order,
                            from_item_index : from_item_index,
                            from_row_id : from_row_id,
                            from_col_id : from_col_id
                        };


                        $.post(themehunk_megamenu_obj.ajax_url, data, function (response) {
                            themehunk_megamenu_saving_indicator('hide');
                        });
                    }   
                },
                        update: function(event, ui) {
                        if (!ui.sender && ui.item.attr('data-type') !== 'outside-widget') {

                            themehunk_megamenu_saving_indicator('show');

                            var item_order = $(this).sortable('toArray', {attribute: 'data-item-key-id'}).toString();
                            var menu_item_id = parseInt($(this).closest('.themehunk-megamenu-item-settins-wrap').data('item-id'));                

                            var row_id = parseInt($(this).closest('.themehunk-megamenu-row').data('row-id'));
                            var col_id = parseInt($(this).closest('.themehunk-megamenu-col').data('col-id'));

                            var data = {
                                action: 'themehunk_megamenu_reorder_items',
                                menu_item_id: menu_item_id,
                                item_order: item_order,
                                row_id: row_id,
                                col_id: col_id
                            };

                            $.post(themehunk_megamenu_obj.ajax_url, data, function (response) {
                                themehunk_megamenu_saving_indicator('hide');
                            });
                        }
                    }
            }).disableSelection();

            $(".draggable-widget").draggable({
                connectToSortable: ".themehunk-megamenu-item-wrap",
                opacity: 0.8,
                helper: "clone",
                revert: "invalid",
                revertDuration: 0,
                zIndex:999,
                containment: ".themehunk-megamenuDraggableWidgetArea",

            }).disableSelection();

            $('#themehunk_megamenu_item_layout_wrap').sortable({
                items: '.themehunk-megamenu-row',
                handle: '.mmthRowSortingIcon',
                placeholder: "drop-highlight",
                containment: ".themehunk-megamenuDraggableWidgetArea",
                update: function(event, ui) {
                    themehunk_megamenu_saving_indicator('show');
                    var rows_order = $(this).sortable('toArray', {attribute: 'data-row-id'}).toString();
                    var menu_item_id = parseInt($(this).closest('.themehunk-megamenu-item-settins-wrap').data('item-id'));                
                    // var menu_item_id = parseInt($(this).closest('.mmth-item-settings-panel').data('id'));
                    console.log(rows_order);
                    var data = {
                        action: 'themehunk_megamenu_reorder_row',
                        menu_item_id: menu_item_id,
                        rows_order: rows_order
                    };
                    $.post(themehunk_megamenu_obj.ajax_url, data, function (response) {
                        console.log(response);
                        themehunk_megamenu_saving_indicator('hide');
                    });
                }
            });

            $('.themehunk-megamenu-row').sortable({
                items: '.themehunk-megamenu-col',
                handle: '.themehunk-megamenuColSortingIcon',
                placeholder: "drop-col-highlight",
                containment: ".themehunk-megamenuDraggableWidgetArea",
                update: function(event, ui) {
                    themehunk_megamenu_saving_indicator('show');
                    var col_order = $(this).sortable('toArray', {attribute: 'data-col-id'}).toString();
                    // var menu_item_id = parseInt($(this).closest('.mmth-item-settings-panel').data('id'));
                    var menu_item_id = parseInt($(this).closest('.themehunk-megamenu-item-settins-wrap').data('item-id'));                
                    var row_id = parseInt($(this).closest('.themehunk-megamenu-row').data('row-id'));
                    var data = {
                        action: 'themehunk_megamenu_reorder_col',
                        menu_item_id: menu_item_id,
                        col_order: col_order,
                        row_id:row_id
                    };
                    $.post(themehunk_megamenu_obj.ajax_url, data, function (response) {
                        console.log( col_order );
                        console.log( response );
                        themehunk_megamenu_saving_indicator('hide');
                    });
                }
            });

        }

        //Add a button above every menu item in the admin area
		$('#menu-to-edit li.menu-item').each(function() {
	        var menu_item = $(this);
	        var button = $("<span>").addClass("megamenu_themehunk_megamenu_begin").html( themehunk_megamenu_obj.mmth_begin_text );
	        $('.item-title', menu_item).append(button);
	    });

   		//Button to close menu builder popup
	    $(document).on( 'click', '.themehunk-megamenu-builder-close-btn', function( e) {
	    	e.preventDefault();
	    	$('.themehunk-megamenu-item-settins-wrap').fadeOut();
	        $('#themehunk-megamenuSettingOverlay').fadeOut();
	    });

   		//Adds a grid row
	    $(document).on('click', '.themehunk-megamenu-add-row-btn', function() {
            console.log('Add row button clicked');
            var layout_format = 12;
            var layout_name = 'layout12';
            themehunk_megamenu_grid_setup( layout_format, layout_name );    
        });

        //Adds a grid column
        $(document).on('click', '.themehunk-megamenu-add-col-btn', function() { 
            var layout_format = 12;
            var layout_name = 'layout12';
            // console.log('Add col button clicked');
            var total_columns = $(this).closest('.themehunk-megamenu-row').find('.themehunk-megamenu-col').length+1;
            var grid_parts =  (12/total_columns).toString();
            // console.log('total_columns',total_columns,'grid_parts', grid_parts);
            if ( total_columns > 6 ) {
                // $('#themehunk-megamenuDraggableWidgetArea--notices').text(themehunk_megamenu_obj.column_space_error).show();
                // $("#themehunk_megamenu_item_layout_wrap--notices").text(themehunk_megamenu_obj.no_column_space_error)
                // .slideDown().delay(2000).slideUp();
                alert(themehunk_megamenu_obj.no_column_space_error);
                return;
            }
            if ( total_columns == 5 ) {
                layout_format = "5,5,5,5,5";
                layout_name = "layout55555";
            } else{
                layout_format = grid_parts.repeat( total_columns ).split("").toString();
                layout_name = 'layout' + grid_parts.repeat( total_columns );              
            }
            // console.log('layout_format', layout_format, 'layout_name', layout_name)
            // themehunk_megamenu_grid_setup( layout_format, layout_name ); 
            var menu_id = $('input#menu').val();
            var menu_item_id = $('.themehunk-megamenu-status-hidden').val();     
            var row_id = parseInt($(this).closest('.themehunk-megamenu-row').attr('data-row-id'));

            $.ajax({
                url: themehunk_megamenu_obj.ajax_url,
                type: 'post',
                data:{
                    action: 'themehunk_megamenu_add_grid_row_column',
                    themehunk_megamenu_nonce: themehunk_megamenu_obj.themehunk_megamenu_nonce,
                    menu_item_id : menu_item_id,
                    row_id: row_id,
                    layout_format: layout_format,
                    layout_name: layout_name,
                },
                success : function( response ) {
                    // console.log(response.data);
                    themehunk_megamenu_ajax_request_load_menu_item_settings (menu_item_id, 0, menu_id);
                }
            });
         }); 

        //Deletes a grid row
        $(document).on('click', '.mmthRowDeleteIcon', function () {
            var button_clicked = $(this);
            themehunk_megamenu_delete_row( button_clicked );
        });       


        //Deletes a grid column
        $(document).on('click', '.mmthColDeleteIcon', function () {
            var row_delele_button = $(this).closest('.themehunk-megamenu-row').find('.mmthRowDeleteIcon');
            var total_columns = $(this).closest('.themehunk-megamenu-row').find('.themehunk-megamenu-col').length - 1;
            var button_clicked = $(this);
            var menu_item_id = $('.themehunk-megamenu-status-hidden').val(); 
            var row_id = parseInt($(this).closest('.themehunk-megamenu-row').data('row-id'));
            var col_id = parseInt($(this).closest('.themehunk-megamenu-col').data('col-id'));
            var data = {
                action: 'themehunk_megamenu_delete_column',
                menu_item_id: menu_item_id,
                row_id: row_id,
                col_id: col_id,
                themehunk_megamenu_nonce: themehunk_megamenu_obj.themehunk_megamenu_nonce
            };
            $.post(ajaxurl, data, function (response) {
                // console.log(response);
                if (response.success){
                    button_clicked.closest('.themehunk-megamenu-col').remove();
                } 

            });

            if ( ! total_columns ) {
                $(row_delele_button).trigger('click');
            }
        });

        //Show/Hide widgets list using slideToggle.
        $(document).on('click', '.themehunk-megamenu-draggable-widgets-list-title', function () {
            $(this).closest('.themehunk-megamenu-draggable-widgets-wrapper').find('.themehunk-megamenu-draggable-widgets-list').slideToggle();
            $('.themehunk-megamenuDraggableWidgetArea').show();
            $('.themehunk-megamenu-draggable-widgets-list-title').addClass('active');
            $('.themehunk-megamenu-builder-config-options-content').hide();
            $('.themehunk-megamenu-icons-container').hide();
            $('.themehunk-megamenu-builder-config-options').removeClass('active');
            $('.themehunk-megamenu-builder-megamenu-icons').removeClass('active');
        });

        //Show/Hide builder options using slideToggle.
        $(document).on('click', '.themehunk-megamenu-builder-config-options', function () {
            $(this).siblings('.themehunk-megamenu-draggable-widgets-wrapper').find('.themehunk-megamenu-draggable-widgets-list').slideUp();
            $('.themehunk-megamenu-builder-config-options-content').show();
            $('.themehunk-megamenu-builder-config-options').addClass('active');
            $('.themehunk-megamenuDraggableWidgetArea').hide();
            $('.themehunk-megamenu-icons-container').hide();
            $('.themehunk-megamenu-draggable-widgets-list-title').removeClass('active');
            $('.themehunk-megamenu-builder-megamenu-icons').removeClass('active');
        });        

        //Show/Hide builder options using slideToggle.
        $(document).on('click', '.themehunk-megamenu-builder-megamenu-icons', function () {
            $(this).siblings('.themehunk-megamenu-draggable-widgets-wrapper').find('.themehunk-megamenu-draggable-widgets-list').slideUp();
            $('.themehunk-megamenu-icons-container').show();
            $('.themehunk-megamenu-builder-megamenu-icons').addClass('active');
            $('.themehunk-megamenu-builder-config-options-content').hide();
            $('.themehunk-megamenuDraggableWidgetArea').hide();
            $('.themehunk-megamenu-draggable-widgets-list-title').removeClass('active');
            $('.themehunk-megamenu-builder-config-options').removeClass('active');
        });
        

        $(document).on('click', '.themehunk-megamenu-icons', function (e) {
            e.preventDefault();
            var icon = $(this).data('icon');
            var menu_item_id = $('.themehunk-megamenu-status-hidden').val();
            var item = $(this);

            themehunk_megamenu_saving_indicator('show');
            $.ajax({
                url : themehunk_megamenu_obj.ajax_url,
                type : 'post',
                data : {
                    action : 'themehunk_megamenu_update_megamenu_icon',
                    menu_item_id : menu_item_id,
                    icon: icon,
                    themehunk_megamenu_nonce: themehunk_megamenu_obj.themehunk_megamenu_nonce
                },
                success : function( response ) {
                    console.log(response.data);

                    $('.themehunk-megamenu-icons').removeClass('themehunk-megamenu-icon-selected');
                    item.addClass('themehunk-megamenu-icon-selected');
                    themehunk_megamenu_saving_indicator('hide');
                }
            });

        });

        //Show/Hide builder icons tabs.
        $(document).on('click', '.icon-tabs-nav', function (e) {
            e.preventDefault();
            $(this).addClass('active').parent().siblings().children().removeClass('active');
            var icon_panel_id =  '#' + $(this).data('icon-tabs');
            $(icon_panel_id).show().siblings().hide();
        });

        /**
         * Search iCon
         */
        $(document).on('keyup change paste', '#themehunk_megamenu_icons_search', function (e) {
            var search_term = $(this).val().toUpperCase();
            $('.themehunk-megamenu-icons').each(function(){
                search_term = search_term.toUpperCase();
                var icon_title = $(this).data('icon').toUpperCase();

                if (icon_title.indexOf(search_term) > -1 ){
                    $(this).show();
                }else{
                    $(this).hide();
                }
            });
        });
        /**
         * Save builder options
         */
        $(document).on('submit', 'form.themehunk-megamenu-builder-config-options-data', function (e) {
            e.preventDefault();
            themehunk_megamenu_saving_indicator('show');
            // var menu_item_id = $('.themehunk-megamenu-status-hidden').val();
            var form_input = $(this).serialize()+'&action=themehunk_megamenu_save_builder_options';
            
            // console.log(form_input);
            $.post(themehunk_megamenu_obj.ajax_url, form_input, function (response) {
                console.log( response );
                themehunk_megamenu_saving_indicator('hide');
            });
        });
        //Set Megamenu background image
        $(document).on('click','#set-item-megamenu-bgimage', function(e){
            e.preventDefault();

            var bgImageUploder;

            if( bgImageUploder ){
                bgImageUploder.open();
                return;
            }

            bgImageUploder = wp.media.frames.file_frame = wp.media({
                title: 'Choose a background image',
                button: {
                    text: 'Choose image',
                },
                multiple: false
            });
            bgImageUploder.open();

            bgImageUploder.on('select', function(){
                attachment = bgImageUploder.state().get('selection').first().toJSON();
                $('#item-megamenu-bgimage-url').val(attachment.url);
                $('#item-megamenu-bgimage-container').attr('src', attachment.url);
                $('#item-megamenu-bgimage-container').parent().removeClass('hidden');
            });

        });

        //Megamenu background image remove
        $(document).on('click', '.radio-cc', function(e){
           
            if ( $(this).prop("checked") == true ){
            $('.radio-cc').addClass('active');
            }else{
            $('.radio-cc').removeClass('active');
           }
        });
        $(document).on('click', '#remove_themehunk_megamenu_bg_image', function(e){
            e.preventDefault();
            
            $('#item-megamenu-bgimage-url').val('');
            $('#remove_themehunk_megamenu_bg_image').parent().addClass('hidden');
        });
	    //Checkbox to activate megamenu on menu item.
	    $(document).on('click', '#themehunk-megamenu-status-chkbox', function(){
			themehunk_megamenu_saving_indicator('show');
            menu_item_id = $('.themehunk-megamenu-item-settins-wrap').find('.themehunk-megamenu-status-hidden').val();
	    	var themehunk_megamenu_item_megamenu_status = '';
			
			if ( $(this).prop("checked") == true ) {
				themehunk_megamenu_item_megamenu_status = 'active';
				$('.themehunk-megamenu-builder-content').show();
	    		$('.activate-megamenu-msg').hide();
			}else if( $(this).prop("checked") == false ){
				themehunk_megamenu_item_megamenu_status = 'inactive';
				$('.themehunk-megamenu-builder-content').hide();
	    		$('.activate-megamenu-msg').show();
			}
    	    $.ajax({
			url : themehunk_megamenu_obj.ajax_url,
			type : 'post',
			data : {
				action : 'themehunk_megamenu_item_enable_megamenu',
				menu_item_id : menu_item_id,
				themehunk_megamenu_item_megamenu_status: themehunk_megamenu_item_megamenu_status,
				themehunk_megamenu_nonce: themehunk_megamenu_obj.themehunk_megamenu_nonce
			},
			success : function( response ) {
				console.log(menu_item_id, response.data);

				if( response.data.themehunk_megamenu_item_megamenu_status == 'active' ){
					$('#themehunk-megamenu-status-chkbox').prop("checked", true );
                    $('.themehunk-megamenu-status-text').addClass("active");
					
				}else if( response.data.themehunk_megamenu_item_megamenu_status == 'inactive' ) {
					$('#themehunk-megamenu-status-chkbox').prop("checked", false );
                    $('.themehunk-megamenu-status-text').removeClass("active");
				}
				themehunk_megamenu_saving_indicator('hide');
			}
			});
	    });

        // AJAX Save MMTH Settings
            $(".themehunk-megamenu-mega-menu-save").on("click", function(e) {
                e.preventDefault();
                $(".themehunk-megamenu-metabox .spinner").css("visibility", "visible");

                var mmth_settings = JSON.stringify($("[name^='themehunk_megamenu_nav_settings']").serializeArray());
                //console.log(mmth_settings);
                // retrieve the widget settings form
                $.post(themehunk_megamenu_obj.ajax_url, {
                    action: "themehunk_megamenu_nav_menu_save",
                    menu_id: $("#menu").val(),
                    mmth_settings : mmth_settings, 
                    themehunk_megamenu_nonce: themehunk_megamenu_obj.themehunk_megamenu_nonce
                }, function(response) {
                    // console.log(response);
                    $(".themehunk-megamenu-metabox .spinner").css("visibility", "hidden");
                });
            });
	   /**
	     * Launches Megamenu builder popup
	     */
	    $( '.megamenu_themehunk_megamenu_begin' ).click(function( e ) {
            e.preventDefault();

	        var menu_item = $(this).closest('li.menu-item');
	        var menu_id = $('input#menu').val();
	        var menu_item_title = menu_item.find('.menu-item-title').text();
	        var menu_item_id = parseInt(menu_item.attr('id').match(/[0-9]+/)[0], 10);
	        var depth = menu_item.attr('class').match(/\menu-item-depth-(\d+)\b/)[1];

	        var mmth_item_settings_wrap = $('.themehunk-megamenu-item-settins-wrap');
	        var mmth_status_hidden = $('.themehunk-megamenu-status-hidden');
	        //Show overlay
	        $('#themehunk-megamenuSettingOverlay').show();
	        themehunk_megamenu_ajax_request_load_menu_item_settings (menu_item_id, depth, menu_id, menu_item_title);
	        //Set this item id to settings wrap
	        mmth_item_settings_wrap.removeAttr('data-item-id');
	        mmth_item_settings_wrap.attr('data-item-id', menu_item_id);	 
			mmth_status_hidden.val( menu_item_id );	
	              
	        mmth_item_settings_wrap.show();

	        //Press escape key to close menu builder popup

	        document.addEventListener('keydown', function(event) {
	            if (event.keyCode === 27){
	                $('.themehunk-megamenu-item-settins-wrap').hide();
	                $('#themehunk-megamenuSettingOverlay').hide();
	            }
	        });
	    });


        /**
         * Open widget form.
         */
        $(document).on('click', '.widget-form-open', function (e) {
            e.preventDefault();
            $(this).closest('.widget').find('.widget-inside').slideToggle();
         });

        /**
         * Close widget form.
         */
        $(document).on('click', '.widget-controls a.close', function(e){
            e.preventDefault();
            $(this).closest('.widget').find('.widget-inside').slideUp();
        });

        /**
         * Delete Widget from column, sidebar and wp_options table
         */
        $(document).on('click', '.widget-controls a.delete', function(e){
            e.preventDefault();

            var menu_item_id = $('.themehunk-megamenu-status-hidden').val();
            var widget_key_id = $(this).closest('.widget').data('item-key-id');
            var widget_wrap = $(this).closest('.widget');

            var row_id = parseInt($(this).closest('.themehunk-megamenu-row').data('row-id'));
            var col_id = parseInt($(this).closest('.themehunk-megamenu-col').data('col-id'));
            themehunk_megamenu_saving_indicator('show');
            var form_data = $(this).closest('form').serialize()+'&action=themehunk_megamenu_delete_widget&menu_item_id='+menu_item_id+'&widget_key_id='+widget_key_id+'&row_id='+row_id+'&col_id='+col_id+'&themehunk_megamenu_nonce='+themehunk_megamenu_obj.themehunk_megamenu_nonce;
            $.post(themehunk_megamenu_obj.ajax_url, form_data, function (response) {
                widget_wrap.find('.widget-inside').slideUp();
                widget_wrap.hide();
                themehunk_megamenu_saving_indicator('hide');
            });
    });

        /**
         * Save widget input
         */
        $(document).on('submit', 'form.themehunk_megamenu_widget_save_form', function (e) {
            e.preventDefault();
            themehunk_megamenu_saving_indicator('show');

            var menu_item_id = $('.themehunk-megamenu-status-hidden').val();
            var widget_key_id = $(this).closest('.widget').data('item-key-id');
            var form_input = $(this).closest('form').serialize()+'&action=themehunk_megamenu_save_widget';
            $.post(themehunk_megamenu_obj.ajax_url, form_input, function (response) {
                console.log( response );
                themehunk_megamenu_saving_indicator('hide');
            });
        });
       	
	});
})(jQuery);
