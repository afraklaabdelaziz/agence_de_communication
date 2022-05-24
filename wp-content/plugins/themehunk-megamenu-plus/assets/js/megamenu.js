/*! Thunk Mega Menu jQuery Plugin */
(function ( $ ) {
    "use strict";
              $.themehunk_megamen = function(menu, options) {
                    var plugin = this;
			        var $menu = $(menu);
			        var $toggle_bar = $menu.siblings(".mega-menu-themehunk-megamenu-toggle");
			        var html_body_class_timeout;
			        var items_with_submenus = $("li.themehunk-megamenu-menu-item-has-children," + "li li.themehunk-megamenu-menu-item-has-children", menu);
                    
                    var defaults = {
					            event: $menu.attr("data-event"),
                                unbind_events:"true",
                                effect: 'fade',
					            breakpoint: $menu.attr("data-breakpoint"),
                                effect_mobile: $menu.attr("data-effect-mobile"),
                                document_click: $menu.attr("data-document-click"),
                                vertical_behaviour: 'accordion',
                                speed:'200',
                                effect_speed_mobile:'200',
                            };


 plugin.settings = {};

 plugin.EndToendPanelDesktop =function(){
           if ($('ul.mega-sub-menu-themehunk-megamenu').hasClass('end-to-end')){
                 $('ul.themehunk-megamenu').css('position','inherit');
           }
        };
 plugin.EndToendPanelMobile =function(){
           if ($('.mega-sub-menu-themehunk-megamenu.depth-0').hasClass('end-to-end')) {
                 $('ul.themehunk-megamenu').css('position','fixed');
           }
        };

 plugin.hideAllPanels = function() {
       $(".mega-toggle-on > a.themehunk-megamenu-menu-link", $menu).each(function() {
                plugin.hidePanel($(this), false);
            });
        };
 plugin.hideSiblingPanels = function(anchor, immediate) {
            anchor.parent().parent().find(".mega-toggle-on").children("a.themehunk-megamenu-menu-link").each(function() { // all open children of open siblings
                plugin.hidePanel($(this), immediate);
            });
        };

plugin.isDesktopView = function() {
            return Math.max(window.outerWidth, $(window).width()) > plugin.settings.breakpoint; // account for scrollbars
        };

plugin.isMobileView = function() {
            return !plugin.isDesktopView();
        };

plugin.showPanel = function(anchor) {
            anchor.parent().triggerHandler("before_open_panel");

            anchor.attr("aria-expanded", "true");

            $(".mega-animating").removeClass("mega-animating");

            if (plugin.isMobileView() && anchor.parent().hasClass("mega-hide-sub-menu-on-mobile")) {
                return;
            }

            if (plugin.isDesktopView() && ( $menu.hasClass("megamenu-horizontal") || $menu.hasClass("mega-menu-vertical") ) && !anchor.parent().hasClass("mega-collapse-children")) {
                plugin.hideSiblingPanels(anchor, true);

            }

            if ((plugin.isMobileView() && $menu.hasClass("mega-keyboard-navigation")) || plugin.settings.vertical_behaviour === "accordion") {
                plugin.hideSiblingPanels(anchor, false);
               
            }

            plugin.calculateDynamicSubmenuWidths(anchor);

            // apply jQuery transition (only if the effect is set to "slide", other transitions are CSS based)
            if ( anchor.parent().hasClass("mega-collapse-children") || plugin.settings.effect === "slide" || 
                ( plugin.isMobileView() && ( plugin.settings.effect_mobile === "slide" || plugin.settings.effect_mobile === "slide_left" || plugin.settings.effect_mobile === "slide_right" ) )
               ) {
                var speed = plugin.isMobileView() ? plugin.settings.effect_speed_mobile : plugin.settings.effect_speed;

                anchor.siblings(".mega-sub-menu-themehunk-megamenu").css("display", "none").animate({"height":"show", "paddingTop":"show", "paddingBottom":"show", "minHeight":"show"}, speed, function() {
                    $(this).css("display", "");
                });
            }

            anchor.parent().addClass("mega-toggle-on").triggerHandler("open_panel");
        };

 plugin.hidePanel = function(anchor, immediate) {
            anchor.parent().triggerHandler("before_close_panel");

            anchor.attr("aria-expanded", "false");

            if ( anchor.parent().hasClass("mega-collapse-children") || ( ! immediate && plugin.settings.effect === "slide" ) || 
                ( plugin.isMobileView() && ( plugin.settings.effect_mobile === "slide" || plugin.settings.effect_mobile === "slide_left" || plugin.settings.effect_mobile === "slide_right" ) )
               ){
                var speed = plugin.isMobileView() ? plugin.settings.effect_speed_mobile : plugin.settings.effect_speed;

                    anchor.siblings(".mega-sub-menu-themehunk-megamenu").animate({"height":"hide", "paddingTop":"hide", "paddingBottom":"hide", "minHeight":"hide"}, speed, function() {
                    anchor.siblings(".mega-sub-menu-themehunk-megamenu").css("display", "");
                    anchor.parent().removeClass("mega-toggle-on").triggerHandler("close_panel");
                    
                });

                return;
            }

            if (immediate) {
                anchor.siblings(".mega-sub-menu-themehunk-megamenu").css("display", "none").delay(plugin.settings.effect_speed).queue(function(){
                    $(this).css("display", "").dequeue();
                });
            }

            // pause video widget videos
            anchor.siblings(".mega-sub-menu-themehunk-megamenu").find(".widget_media_video video").each(function() {
                this.player.pause();
            });

            anchor.parent().removeClass("mega-toggle-on").triggerHandler("close_panel");
            // plugin.addAnimatingClass(anchor.parent());
        };


plugin.calculateDynamicSubmenuWidths = function(anchor) {
            // apply dynamic width and sub menu position (only to top level mega menus)
            if (anchor.parent().hasClass("themehunk-megamenu-is-megamenu") && plugin.settings.panel_width && $(plugin.settings.panel_width).length > 0) {
                if (plugin.isDesktopView()) {
                    var submenu_offset = $menu.offset();
                    var target_offset = $(plugin.settings.panel_width).offset();

                    anchor.siblings(".mega-sub-menu-themehunk-megamenu").css({
                        width: $(plugin.settings.panel_width).outerWidth(),
                        left: (target_offset.left - submenu_offset.left) + "px"
                    });
                } else {
                    anchor.siblings(".mega-sub-menu-themehunk-megamenu").css({
                        width: "",
                        left: ""
                    });
                }
            }

            // apply inner width to sub menu by adding padding to the left and right of the mega menu
            if (anchor.parent().hasClass("themehunk-megamenu-is-megamenu") && anchor.parent().parent().hasClass("max-mega-menu") && plugin.settings.panel_inner_width && $(plugin.settings.panel_inner_width).length > 0) {
                var target_width = 0;

                if ($(plugin.settings.panel_inner_width).length) {
                    // jQuery selector
                    target_width = parseInt($(plugin.settings.panel_inner_width).width(), 10);
                } else {
                    // we"re using a pixel width
                    target_width = parseInt(plugin.settings.panel_inner_width, 10);
                }

                var submenu_width = parseInt(anchor.siblings(".mega-sub-menu-themehunk-megamenu").innerWidth(), 10);

                if (plugin.isDesktopView() && target_width > 0 && target_width < submenu_width) {
                    anchor.siblings(".mega-sub-menu-themehunk-megamenu").css({
                        "paddingLeft": (submenu_width - target_width) / 2 + "px",
                        "paddingRight": (submenu_width - target_width) / 2 + "px"
                    });
                } else {
                    anchor.siblings(".mega-sub-menu-themehunk-megamenu").css({
                        "paddingLeft": "",
                        "paddingRight": ""
                    });
                }
            }
        };
var bindHoverEvents = function() {
            items_with_submenus.on({
                "mouseenter.megamenu" : function() {
                    plugin.unbindClickEvents();
                    if (! $(this).hasClass("mega-toggle-on")) {
                        plugin.showPanel($(this).children("a.themehunk-megamenu-menu-link"));
                    }
                   
                },
                "mouseleave.megamenu" : function() {
                    if ($(this).hasClass("mega-toggle-on") && ! $(this).hasClass("mega-disable-collapse") && ! $(this).parent().parent().hasClass("mega-menu-tabbed")) {
                        plugin.hidePanel($(this).children("a.themehunk-megamenu-menu-link"), false);
                    }
                }
            });
        };

var bindHoverIntentEvents = function() {
            items_with_submenus.hoverIntent({
                over: function () {
                    plugin.unbindClickEvents();
                    if (! $(this).hasClass("mega-toggle-on")) {
                        plugin.showPanel($(this).children("a.themehunk-megamenu-menu-link"));
                    }
                },
                out: function () {
                    if ($(this).hasClass("mega-toggle-on") && ! $(this).hasClass("mega-disable-collapse") && ! $(this).parent().parent().hasClass("mega-menu-tabbed")) {
                        plugin.hidePanel($(this).children("a.themehunk-megamenu-menu-link"), false);
                    }
                },
                timeout: megamenu.timeout,
                interval: megamenu.interval
            });
        };

        
 plugin.unbindAllEvents = function() {
            $("ul.mega-sub-menu-themehunk-megamenu, li.themehunk-megamenu-menu-item,a.themehunk-megamenu-menu-link, span.mega-indicator", menu).off().unbind();
        };

        plugin.unbindClickEvents = function() {
            $("> a.themehunk-megamenu-menu-link", items_with_submenus).off("click.megamenu touchend.megamenu");
        };

        plugin.unbindHoverEvents = function() {
            items_with_submenus.unbind("mouseenter.megamenu mouseleave.megamenu");
        };

        plugin.unbindHoverIntentEvents = function() {
            items_with_submenus.unbind("mouseenter mouseleave").removeProp("hoverIntent_t").removeProp("hoverIntent_s"); // hoverintent does not allow namespaced events
        };

        plugin.unbindKeyboardEvents = function() {
            $menu.parent().off("keyup.megamenu keydown.megamenu focusout.megamenu");
        };

        plugin.unbindMegaMenuEvents = function() {
            if (plugin.settings.event === "hover_intent") {
                plugin.unbindHoverIntentEvents();
            }

            if (plugin.settings.event === "hover") {
                plugin.unbindHoverEvents();
            }

            plugin.unbindClickEvents();
            plugin.unbindKeyboardEvents();
        };

        plugin.switchToMobile = function() {
            plugin.unbindMegaMenuEvents();
            plugin.bindMegaMenuEvents();
            plugin.hideAllPanels();
            plugin.EndToendPanelMobile();
        };
        plugin.switchToDesktop = function() {
            plugin.unbindMegaMenuEvents();
            plugin.bindMegaMenuEvents();
            plugin.hideAllPanels();
            plugin.EndToendPanelDesktop();
            $menu.css({
                width: "",
                left: "",
                display: ""
            });

            $toggle_bar.removeClass("mega-menu-open");
        };

/***********************/        
// mobile toggle menu
/***********************/  
 plugin.initToggleBar = function() {
            // mobile menu
            $toggle_bar.on("click", function(e) {
                if ( $(e.target).is(".mega-menu-themehunk-megamenu-toggle, .mega-menu-toggle-block, .mega-menu-toggle-animated-block, .mega-menu-toggle-animated-block *, .mega-toggle-blocks-left, .mega-toggle-blocks-center, .mega-toggle-blocks-right, .mega-toggle-label, .mega-toggle-label span") ) {  
                 if ($(this).hasClass("mega-menu-open")) {
                        plugin.hideMobileMenu();
                    } else {
                        plugin.showMobileMenu();
                    }
                }
            });
        };

plugin.hideMobileMenu = function() {
            if ( ! $toggle_bar.is(":visible")) {
                return;
            }
            html_body_class_timeout = setTimeout(function() {
                $("body").removeClass($menu.attr("id") + "-mobile-open");
                $("html").removeClass($menu.attr("id") + "-off-canvas-open");

            }, plugin.settings.effect_speed_mobile);

            $(".mega-toggle-label, .mega-toggle-animated", $toggle_bar).attr("aria-expanded", "false");

            if (plugin.settings.effect_mobile === "slide") {
                $menu.animate({"height":"hide"}, plugin.settings.effect_speed_mobile, function() {
                    $menu.css({
                        width: "",
                        left: "",
                        display: ""
                    });
                });
            }

            $toggle_bar.removeClass("mega-menu-open");
        };


plugin.showMobileMenu = function(e) {
            if ( ! $toggle_bar.is(":visible")) {
                return;
            }

            clearTimeout(html_body_class_timeout);

            $("body").addClass($menu.attr("id") + "-mobile-open");

            if ( plugin.settings.effect_mobile === "slide_left" || plugin.settings.effect_mobile === "slide_right" ||  plugin.settings.effect_mobile === "slide_center") {
                $("html").addClass($menu.attr("id") + "-off-canvas-open"); 
            }
            if ($(".mega-menu-themehunk-megamenu-toggle.mega-menu-open").lenth!==''){

            $("ul[data-effect-mobile=slide_center]").prepend('<span class="mega-toggle-label-themehunk-megamenu-closed"></span>');   
            
            }

            $(".mega-toggle-label, .mega-toggle-animated", $toggle_bar).attr("aria-expanded", "true");

            if (plugin.settings.effect_mobile === "slide") {
                $menu.animate({"height":"show"}, plugin.settings.effect_speed_mobile);
            }

            $toggle_bar.addClass("mega-menu-open");
        };



   var bindClickEvents = function() {
         var dragging = false;
            $(document).on({
                "touchmove": function(e) { dragging = true; },
                "touchstart": function(e) { dragging = false; }
            });

            $(document).on("click touchend", function(e) { // hide menu when clicked away from
               if (!dragging && plugin.settings.document_click === "collapse" &&  ! $(e.target).closest(".mega-menu-themehunk-megamenu-toggle").length || $(e.target).closest(".mega-toggle-label-themehunk-megamenu-closed").length ) {

                    plugin.hideAllPanels();
                    plugin.hideMobileMenu();
                }
                dragging = false;
            });
   }
 plugin.monitorView = function() {
            if (plugin.isDesktopView()) {
                $menu.data("view", "desktop");
                 plugin.EndToendPanelDesktop();

            } else {
                $menu.data("view", "mobile");
                plugin.switchToMobile();
                 plugin.EndToendPanelMobile();
            }

            plugin.checkWidth();
            $(window).resize(function() {
                plugin.checkWidth();
            });
        };
plugin.checkWidth = function() {
            if ( plugin.isMobileView() && $menu.data("view") === "desktop" ) {
                $menu.data("view", "mobile");
                plugin.switchToMobile();
            }

            if ( plugin.isDesktopView() && $menu.data("view") === "mobile" ) {
                $menu.data("view", "desktop");
                plugin.switchToDesktop();
            }

            plugin.calculateDynamicSubmenuWidths($("> li.themehunk-megamenu-is-megamenu > a.themehunk-megamenu-menu-link", $menu));
        };


plugin.bindMegaMenuEvents = function() {
            
               if (plugin.isDesktopView() && plugin.settings.event === "hover_intent") {
                bindHoverIntentEvents();
            }
                
                if (plugin.isDesktopView() && plugin.settings.event === "hover") {
                bindHoverEvents();
            }
                 bindClickEvents();
                plugin.initToggleBar();
           
 
};

plugin.init = function() {
            $menu.triggerHandler("before_mega_menu_init");
            plugin.settings = $.extend({}, defaults, options);
            $menu.removeClass("mega-no-js");

            plugin.initToggleBar();
            
            if (plugin.settings.unbind_events === "true") {
                plugin.unbindAllEvents();
            }

            $("span.mega-indicator", $menu).on("click.megamenu", function(e) {
                e.preventDefault();
                e.stopPropagation();

                if ( $(this).parent().parent().hasClass("mega-toggle-on") ) {
                    if ( ! $(this).parent().parent().parent().parent().hasClass("mega-menu-tabbed") || plugin.isMobileView() ) {
                        plugin.hidePanel($(this).parent(), false);
                    }
                } else {
                    plugin.showPanel($(this).parent(), false);
                }
            });

            $(window).on("load", function() {
                plugin.calculateDynamicSubmenuWidths($("> li.themehunk-megamenu-is-megamenu > a.themehunk-megamenu-menu-link", $menu));
            });

            plugin.bindMegaMenuEvents();
            plugin.monitorView();
            $menu.triggerHandler("after_mega_menu_init");
        };

plugin.init();

}


      $.fn.themehunk_megamen = function(options) {
        return this.each(function() {
            if (undefined === $(this).data("themehunk_megamen")) {
                var plugin = new $.themehunk_megamen(this, options);
                $(this).data("themehunk_megamen", plugin);
            }
        });
    };

    $(function() {
        $(".themehunk-megamenu").themehunk_megamen();
    });
    // mena-menu height
     $(function(){
        // var y = $('.themehunk-megamenu-is-megamenu > ul')[0].scrollHeight + 317.23;
        if($('.themehunk-megamenu li.themehunk-megamenu-is-megamenu').length!=''){

       
        var y = $('.themehunk-megamenu-is-megamenu > ul')[0].scrollHeight
        if(y <= 550){
        $("head").append('<style>.themehunk-megamenu-is-megamenu .mega-sub-menu-themehunk-megamenu.depth-0:before{height:100%;}</style>');
        }else{
        var z = y; 
         $("head").append('<style>.themehunk-megamenu-is-megamenu .mega-sub-menu-themehunk-megamenu.depth-0:before{height:'+
             z + 'px;}</style>');   
        }

         }
    });

}( jQuery ));