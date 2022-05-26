import List from 'list.js';
import initializeFilters from './filter/filterGroups';
import PlaceholderDialog from './placeholder/placeholderDialog';

/**
 * @param {Array<Object>} placeholders
 */
function initializePlaceholders(placeholders) {
  const container = document.getElementById('placeholders');
  const options = {
    searchClass: 'search',
    valueNames: [
      'placeholder',
      { name: 'dialog_slug', attr: 'data-dialog-slug' },
    ],
    item: '<li><span class="placeholder title dialog_slug" data-dialog-slug=""></span></li>',
  };
  container.querySelector('.list').innerHTML = '';

  // eslint-disable-next-line no-new
  new List('placeholders', options, placeholders);

  container.querySelectorAll('li').forEach((item) => {
    item.addEventListener('click', async (e) => {
      e.preventDefault();
      const slug = item.querySelector('.dialog_slug').dataset.dialogSlug;
      const dialog = new PlaceholderDialog(slug);
      await dialog.show();
    });
  });
  jQuery('#shopmagic_placeholders_metabox').show();
}

function initializeManualActionsMetabox() { // check InformSMToShowManualMetaboxTrait trait
  const isManualEvent = (jQuery('#event-config-area [name=event\\[manual\\]]').val() === 'manual');
  jQuery('#shopmagic_manual_actions_metabox').toggle(isManualEvent);
}

async function getNewEventParameters(newEvent) {
  return fetch(ShopMagic.ajaxurl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
      action: 'shopmagic_load_event_params',
      event_slug: newEvent,
      post: jQuery('#post_ID').val(),
      paramProcessNonce: ShopMagic.paramProcessNonce,
    }),
  });
}

/**
 * @type {Object} params
 * @property {string} description
 * @property {?Object<Object<FilterData>>} existing_filters
 * @property {Array<Object>} placeholders
 * @property {Array<Object>} filters
 * @property {string} event_box
 */
function initializeNewEventParameters(params) {
  jQuery('#event-config-area').html(params.event_box);
  jQuery('#event-desc-area .content').html(params.description);

  initializePlaceholders(params.placeholders);
  initializeManualActionsMetabox();
  initializeFilters(params.filters, params.existing_filters);
}

export default async function loadNewEventParameters() {
  /** @type {HTMLSelectElement} */
  const eventSelector = document.getElementById('_event');
  /** @type {HTMLDivElement} */
  const eventHandler = document.getElementById('shopmagic_event_metabox');
  if (eventSelector.value.length) {
    eventHandler.querySelector('.spinner').classList.add('is-active');
    eventHandler.querySelector('.error-icon').classList.remove('error-icon-visible');

    try {
      const response = await getNewEventParameters(eventSelector.value);
      if (response.ok) {
        initializeNewEventParameters(await response.json());
      } else {
        jQuery('#shopmagic_placeholders_metabox').hide();
      }
    } catch (e) {
      eventHandler.querySelector('.error-icon').classList.add('error-icon-visible');
    } finally {
      eventHandler.querySelector('.spinner').classList.remove('is-active');
    }
  }
}
