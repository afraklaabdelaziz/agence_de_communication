// *
//  *Editor settings
//  */
/**************/
//MMTHUNKsettingLib
/**************/
(function ($) {
    var MMTHUNKsettingLib = {
        init: function (){
            this.bindEvents();
        },
        bindEvents: function (){
        	var $this = this;
            $this.setting_tab();
            $this.color_picker();
            $this.pickr();
            $this.SelectArrow();
            $this.CodeMirror();
            $this.resetconfirm();
            $this.setting_save();
            $this.setting_validation();

        },
        setting_validation: function (){
        $('form.theme_editor label[data-validation]').each(function() {
        var label = $(this);
        var validation = label.attr('data-validation');
        var error_message = label.siblings( '.mega-validation-message-' + label.attr('class') );
        var input = $('input', label);

        input.on('blur', function() {

            var value = $(this).val();

            if (label.hasClass('mega-flyout_width') && value == 'auto') {
                label.removeClass('mega-error');
                label.siblings( '.mega-validation-message-' + label.attr('class') ).hide();
                return;
            }

            if ( ( validation == 'int' && Math.floor(value) != value )
              || ( validation == 'px' && ! ( value.substr(value.length - 2) == 'px' || value.substr(value.length - 2) == 'em' || value.substr(value.length - 2) == 'vh' || value.substr(value.length - 2) == 'vw' || value.substr(value.length - 2) == 'pt' || value.substr(value.length - 3) == 'rem' || value.substr(value.length - 1) == '%' ) && value != 0 && value != 'normal' && value != 'inherit' )
              || ( validation == 'float' && ! $.isNumeric(value) ) ) {
                label.addClass('mega-error');
                error_message.show();
            } else {
                label.removeClass('mega-error');
                label.siblings( '.mega-validation-message-' + label.attr('class') ).hide();
            }

        });

    });},
        setting_tab: function () {
           $(document).ready(function(){ 
            $('.mega-tab-content').each(function() {

		        if (!$(this).hasClass('mega-tab-content-menu_bar')) {
		            $(this).hide();
		        }
		    });   
		    $('.mega-tab').on("click", function() {
  
		        var selected_tab = $(this);
		        selected_tab.siblings().removeClass('nav-tab-active');
		        selected_tab.addClass('nav-tab-active');
		        var content_to_show = $(this).attr('data-tab');
		        $('.mega-tab-content').hide();
		        $('.' + content_to_show).show();
		    });
		  });
        },
         color_picker: function () {
		        $(".mm_colorpicker").spectrum({
		        preferredFormat: "rgb",
		        showInput: true,
		        showAlpha: true,
		        clickoutFiresChange: true,
		        showSelectionPalette: true,
		        showPalette: true,
		        palette: $.isArray(megamenu_spectrum_settings.palette) ? megamenu_spectrum_settings.palette : [],
		        localStorageKey: "maxmegamenu.themeeditor",
		        change: function(color) {
		            if (color.getAlpha() === 0) {
		                $(this).siblings('div.chosen-color').html('transparent');
		            } else {
		                $(this).siblings('div.chosen-color').html(color.toRgbString());
		            }
		        }
		    });
         },
         pickr: function () {
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
				let selectedElem_ = jQuery('input.color_picker');
				jQuery.each(selectedElem_,myColorPicker);
				
         },


         CodeMirror: function () {
			                   if (typeof wp.codeEditor !== 'undefined' && typeof cm_settings !== 'undefined') {
			       		        if ($('#codemirror').length) {
			       		            wp.codeEditor.initialize($('#codemirror'), cm_settings);
			       		        }
			       
			       		        $('[data-tab="mega-tab-content-custom_styling"]').on('click', function() {
			       		            setTimeout( function() {
			       		                $('.mega-tab-content-custom_styling').find('.CodeMirror').each(function(key, value) {
			       		                    value.CodeMirror.refresh();
			       		                });
			       		            }, 160);
			       		        });
			       		    }
          },
          SelectArrow:function() {
				      $('.icon_dropdown').select2({
				      containerCssClass: 'tpx-select2-container select2-container-sm',
				      dropdownCssClass: 'tpx-select2-drop',
				      minimumResultsForSearch: -1,
				      formatResult: function(icon) {
				        return '<i class="' + $(icon.element).attr('data-class') + '"></i>';
				      },
				      formatSelection: function (icon) {
				        return '<i class="' + $(icon.element).attr('data-class') + '"></i>';
				        }
				    });
          },
          resetconfirm: function () {
			           $(".confirm").on("click", function() {
			             return confirm(themehunk_megamenu_options.confirm);
			    });
			},
         setting_save: function () {

					$(".theme_editor").on("submit", function(e) {
			        e.preventDefault();
			        $(".theme_result_message").remove();
			        $(".spinner").css('visibility', 'visible').css('display', 'block');
			        $("input#submit").attr('disabled', 'disabled');
			        var memory_limit_link = $("<a>").attr('href', themehunk_megamenu_options.increase_memory_limit_url).html(themehunk_megamenu_options.increase_memory_limit_anchor_text);
			        $.ajax({
			            url:ajaxurl,
			            async: true,
			            data: $(this).serialize(),
			            type: 'POST',
			            success: function(message) {
			                if (message.success == true) { //Theme saved successfully
			                    var success = $("<p>").addClass('saved theme_result_message');
			                    var icon = $("<span>").addClass('dashicons dashicons-yes');
			                    $('.megamenu_submit .mega_left').append(success.html(icon).append(message.data));
			                } else if (message.success == false) { // Errors in scss
			                    var error = $("<p>").addClass('fail theme_result_message').html(themehunk_megamenu_options.theme_save_error + " ").append(themehunk_megamenu_options.theme_save_error_refresh).append("<br /><br />").append(message.data);
			                    $('.megamenu_submit').after(error);
			                } else {
			                    if (message.indexOf("exhausted") >= 0) {
			                        var error = $("<p>").addClass('fail theme_result_message').html(themehunk_megamenu_options.theme_save_error + " ").append(themehunk_megamenu_options.theme_save_error_exhausted + " ").append(themehunk_megamenu_options.theme_save_error_memory_limit + " ").append(memory_limit_link).append("<br />").append(message);
			                    } else {
			                        var error = $("<p>").addClass('fail theme_result_message').html(themehunk_megamenu_options.theme_save_error + "<br />").append(message);
			                    }
			                    $('.megamenu_submit').after(error);
			                }
			            },
			            error: function(message) {
			            	
			                if(message.status == 500) { // 500 error with no response from server
			                    var error = $("<p>").addClass('fail theme_result_message').html(themehunk_megamenu_options.theme_save_error_500 + " ").append(themehunk_megamenu_options.theme_save_error_memory_limit + " ").append(memory_limit_link);
			                } else {
			                    if (message.responseText == "-1") { // nonce check failed
			                        var error = $("<p>").addClass('fail theme_result_message').html(themehunk_megamenu_options.theme_save_error + " " + themehunk_megamenu_options.theme_save_error_nonce_failed );
			                    }
			                }
			                $('.megamenu_submit').after(error);

			            },
			            complete: function() {
			                $(".spinner").hide();
			                $("input#submit").removeAttr('disabled');
			            }
			        });

			    });
         },



  }
MMTHUNKsettingLib.init();
})(jQuery);