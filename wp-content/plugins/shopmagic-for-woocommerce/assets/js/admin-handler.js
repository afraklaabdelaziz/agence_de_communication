import { bindAutomationActionsListeners, registerGlobalFunctions } from './admin/admin-actions';
import initializeAttachments from './admin/automation/action/actionAttachment';
import loadNewEventParameters from './admin/automation/admin';
import ImportAutomations from './admin/importAutomations';
import displayShortcodeMetabox from './admin/marketingLists/shortocodeMetabox';
import SubscribersExport from './admin/marketingLists/subscribersExport';
import SubscribersImport from './admin/marketingLists/subscribersImport';
import {
  isAutomationEditPage,
  isAutomationListPage,
  isMarketingListsEditPage,
  isMarketingListsListPage,
  isSubscribersExportPage,
  isSubscribersPage,
} from './admin/utils/adminPage';
import appendTab from './admin/utils/appendTab';

const { __ } = wp.i18n;

/**
 * Wyswig ajax management.
 *
 * @type {{init: ShopMagic.wyswig.init, init_buttons: ShopMagic.wyswig.init_buttons}}
 */
ShopMagic.wyswig = {
  /**
   * Initialize wyswig with a given unique id. There must be one wp editor with 'shopmagic_editor' id on the page.
   * @param id
   */
  init(id) {
    const $ = jQuery;

    if (typeof tinymce === 'undefined' || typeof tinyMCEPreInit.mceInit.shopmagic_editor === 'undefined') {
      return;
    }

    let qtags;

    const mceInit = $.extend({}, tinyMCEPreInit.mceInit.shopmagic_editor);
    const qtInit = $.extend({}, tinyMCEPreInit.qtInit.shopmagic_editor);

    mceInit.selector = `#${id}`;
    mceInit.id = id;
    mceInit.wp_autoresize_on = false;

    tinyMCEPreInit.mceInit[mceInit.id] = mceInit;

    qtInit.id = id;

    const $wrap = tinymce.$(`#wp-${id}-wrap`);

    if (($wrap.hasClass('tmce-active') || !tinyMCEPreInit.qtInit.hasOwnProperty(id))) {
      try {
        tinymce.init(mceInit);
      } catch (e) {
        console.log(e);
      }
    }

    try {
      qtags = quicktags(qtInit);
      this.init_buttons(qtags);
    } catch (e) {
      console.log(e);
    }
  },

  /**
   * Initialize quicktags button on wyswig instance.
   *
   * @param qtags
   */
  init_buttons(qtags) {
    const defaults = ',strong,em,link,block,del,ins,img,ul,ol,li,code,more,close,';

    const { name } = qtags;
    const { settings } = qtags;
    let html = '';
    const theButtons = {};
    let use = '';

    // set buttons
    if (settings.buttons) {
      use = `,${settings.buttons},`;
    }

    for (const i in edButtons) {
      if (!edButtons[i]) {
        continue;
      }

      id = edButtons[i].id;
      if (use && defaults.indexOf(`,${id},`) !== -1 && use.indexOf(`,${id},`) === -1) {
        continue;
      }

      if (!edButtons[i].instance || edButtons[i].instance === inst) {
        theButtons[id] = edButtons[i];

        if (edButtons[i].html) {
          html += edButtons[i].html(`${name}_`);
        }
      }
    }

    if (use && use.indexOf(',fullscreen,') !== -1) {
      theButtons.fullscreen = new qt.FullscreenButton();
      html += theButtons.fullscreen.html(`${name}_`);
    }

    if (document.getElementsByTagName('html')[0].dir === 'rtl') {
      theButtons.textdirection = new qt.TextDirectionButton();
      html += theButtons.textdirection.html(`${name}_`);
    }

    qtags.toolbar.innerHTML = html;
    qtags.theButtons = theButtons;
  },
};

ShopMagic.media = (mediaElId, valueIdentifier) => {
  initializeAttachments(mediaElId, valueIdentifier);
};

function appendRecipesTab() {
  const $pageWrap = jQuery(document.querySelector('div.wrap'));
  if ($pageWrap.length > 0) {
    const automationTabId = 'automations_tab';
    const recipesTabId = 'recipes_tab';

    const automationTab = `<div id="${automationTabId}"></div>`;
    const tabHeader = jQuery(`<div class="tabs nav-tab-wrapper"><ul><li class="nav-tab-li"><a class="nav-tab" href="#${automationTabId}">${__('Automations', 'shopmagic-for-woocommerce')}</a></li><li class="nav-tab-li nav-tab-recipes"><a class="nav-tab" href="#${recipesTabId}">${__('Ready-to-use Recipes', 'shopmagic-for-woocommerce')} <span class="ribbon new">New</span></a></li></ul></div>`);

    jQuery.get(`${ShopMagic.ajaxurl}?action=shopmagic_recipes_tab`, (result) => {
      $pageWrap.append(jQuery(`<div id="${recipesTabId}">${result}</div>`).hide());
      $pageWrap.tabs('refresh');
    });

    $pageWrap
      .wrapInner(automationTab)
      .prepend(tabHeader)
      .tabs();
  }
}

if (isAutomationListPage()) {
  appendRecipesTab();
  (new ImportAutomations()).prepareTemplate();
}

if (isAutomationEditPage()) {
  const eventHandler = document.getElementById('_event');
  eventHandler.addEventListener('change', loadNewEventParameters);
  eventHandler.dispatchEvent(new Event('change'));

  if (jQuery('#shopmagic_placeholders_metabox').length) { // if placeholders metabox present
    jQuery('#shopmagic_placeholders_metabox').hide();
  }

  bindAutomationActionsListeners();
  registerGlobalFunctions();
}

if (isMarketingListsListPage() || isSubscribersPage() || isSubscribersExportPage()) {
  const url = new URL(document.URL).pathname
  appendTab(
    document.querySelector('div.wrap'),
    [
      {
        name: __('Marketing Lists', 'shopmagic-for-woocommerce'),
        link: `${url}?post_type=shopmagic_list`,
      },
      {
        name: __('Subscribers', 'shopmagic-for-woocommerce'),
        link: `${url}?post_type=shopmagic_automation&page=optins`,
      },
      {
        name: __('Import/Export', 'shopmagic-for-woocommerce'),
        link: `${url}?post_type=shopmagic_automation&page=import-export`,
      },
    ],
  );
}

if (isSubscribersExportPage()) {
  new SubscribersImport(document.forms.namedItem('import')).init();
  new SubscribersExport(document.forms.namedItem('export')).init();
}

if (isMarketingListsEditPage()) {
  displayShortcodeMetabox();
}

jQuery(($) => {
  /**
   * @param {HTMLElement} item
   */
  window.makeNoticeDismissible = (item) => {
    const $el = jQuery(item).parent();
    $el.fadeTo(100, 0, () => {
      $el.slideUp(100, () => {
        $el.remove();
      });
    });
  };

  const bindTipToSelector = function (selector, options) {
    const runTip = function () {
      $(selector).tipTip(options);
    };
    // tip on enter and after each ajax request
    runTip();
    $(document).ajaxComplete(() => {
      runTip();
    });
  };
  bindTipToSelector('.shopmagic-help-tip, .woocommerce-help-tip', {
    attribute: 'data-tip',
    fadeIn: 50,
    fadeOut: 50,
    delay: 200,
  });

  if (isAutomationEditPage()) {
    // load email template and put it in the editor
    window.loadEmailTemplate = function (editorId) {
      const templateName = $(`#predefined_block_${editorId}`).val();

      $.ajax({
        url: ShopMagic.ajaxurl,
        method: 'POST',
        data: {
          action: 'sm_sea_load_email_template',
          template_slug: templateName,
          paramProcessNonce: ShopMagic.paramProcessNonce,
        },
        beforeSend() {
          $(`.email_templates_${editorId} .spinner`).addClass('is-active');
          $(`.email_templates_${editorId} .error-icon`).removeClass('error-icon-visible');
        },
      }).done((data) => {
        tinymce.execCommand('mceFocus', false, editorId);
        tinymce.activeEditor.execCommand('mceInsertContent', false, data);
      }).fail(() => {
        $(`.email_templates_${editorId} .error-icon`).addClass('error-icon-visible');
      }).always(() => {
        $(`.email_templates_${editorId} .spinner`).removeClass('is-active');
      });

      return false;
    };
  }

  (function adminSendMailTestDialog($) {
    $(() => {
      $('.send_test_email').click(function (e) {
        e.preventDefault();
        const $actionFormArea = $(this).parents('.action-form-table');
        const dialogHandleName = $(`#${$(this).data('dialog-id')}`);
        const $dialog = $(dialogHandleName).clone().show().dialog({
          modal: true,
          draggable: false,
          resizable: false,
          width: 560,
          classes: {
            'ui-dialog': 'shopmagic-dialog',
          },
          close() {
            jQuery($dialog)
              .empty()
              .remove();
          },
        });
        $dialog.find('.test_email_button').click(function () {
          $(this).attr('disabled', true);
          jQuery.post(ajaxurl, {
            action: `shopmagic_${$(this).data('hook-name')}`,
            event: $('#_event').val(),
            email: $dialog.find('input.email_to_test').val(),
            action_data: $('<form />').append($actionFormArea.clone()).serialize(),
          }, (result) => {
            if (result.response) {
              $dialog.find('.dialog-result').html(result.response);
              $dialog.find('.test_email_button').attr('disabled', false);
            }
            $dialog.find('.close-dialog').click((e) => {
              e.preventDefault();
              $dialog.dialog('close');
            });
          });
        });
      });
    });
  }(jQuery));

  (function adminAjaxCancelQueue($) {
    $(() => {
      $('.cancel_queue').click(function (e) {
        const self = this;
        e.preventDefault();
        if (window.confirm($(this).data('sure'))) {
          $.post($(this).attr('href'), (result) => {
            if (result.result === 'OK') {
              $(self).parents('tr').fadeOut();
            }
          });
        }
      });
    });
  }(jQuery));
  (function manualActionAjaxQueue($) {
    $('#manual-items-queue-match').each((index, item) => {
      const $item = $(item);
      const $listContainer = $item.find('.item-list');
      const maxCount = parseInt($item.data('count'), 10);
      const automationId = parseInt($item.data('automation-id'), 10);
      const pageSize = parseInt($item.data('default-pagesize'), 10);
      let page = 1;
      let listOfIds = [];

      const $progressBar = $item.find('.queued-progressbar').progressbar({
        value: 0,
      });

      function processPage(page) {
        return $.ajax({
          url: $item.data('page-match-url'),
          data: {
            page,
            automation_id: automationId,
            page_size: pageSize,
            method: 'GET',
          },
          success(result) {
            const proc = Math.floor(result.data.page * pageSize / maxCount * 100);
            $progressBar.progressbar({ value: proc });
            result.data.items.forEach((arrayElement) => {
              $listContainer.append($(arrayElement));
            });
            listOfIds = listOfIds.concat(result.data.ids);
          },
          error(xhr, textStatus, error) {
            $item.find('.item-list-counter').text(error);
          },
        });
      }

      (function processNextPage() {
        const pageMax = Math.ceil(maxCount / pageSize);
        processPage(page++).then(() => {
          if (page <= pageMax) {
            processNextPage();
          } else {
            matchQueueDone();
          }
        });
      }());

      function matchQueueDone() {
        if ($listContainer.find('li').length === listOfIds.length) {
          $item.find('.item-list-counter').text($listContainer.find('li').length);
          if (listOfIds.length > 0) {
            $('.confirm-footer')
              .show()
              .find('[name=ids]').val(listOfIds.join(','));
          }
        } else {
          console.error('SM: items and ids has different counts');
        }
      }
    });
  }(jQuery));
});
