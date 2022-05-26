import { filterGroupTemplate, useTemplate } from '../../templates';
import FilterElement from './filterElement';

const { __ } = wp.i18n;

/**
 * @typedef ExistingFiltersAndGroup
 * @type {Object}
 * @property {ExistingFiltersOrGroup}
 */

/**
 * @typedef ExistingFiltersOrGroup
 * @type {Object}
 * @property {FilterData}
 */

/**
 * @typedef FilterData
 * @type {Object}
 * @property {string} filter_slug
 * @property {Object} data
 */

/**
 * Renders nested groups of filters.
 * @private
 */
class FilterGroups {
  static existingFilters = null;

  ADD_GROUP_BUTTON_ID = '#add-filter-group';

  FILTER_GROUP_CONTAINER_ID = '#filter-group-area';

  UNIQUE_ID_COUNTER = 0;

  /**
   * @param {?ExistingFiltersAndGroup} existingFilters
   */
  static cacheExistingFilters(existingFilters) {
    if (Object.keys(existingFilters).length > 0) {
      this.existingFilters = existingFilters;
    }
  }

  /**
   * @param {Array<Object>} possibleFilters
   * @param translations
   */
  constructor(possibleFilters, translations = {}) {
    this.possibleFilters = possibleFilters;
    this.translations = translations;
    this.possibleFilters.unshift({
      id: '',
      name: __('Select filter', 'shopmagic-for-woocommerce'),
    });
  }

  attachEvents() {
    jQuery(this.FILTER_GROUP_CONTAINER_ID).empty();
    jQuery(this.ADD_GROUP_BUTTON_ID)
      .toggle(this.possibleFilters.length > 1)
      .unbind()
      .click((e) => {
        e.preventDefault();
        this.renderNewGroup();
      });
  }

  /**
   * @param {?ExistingFiltersAndGroup} existingFilters
   */
  renderExistingFilters(existingFilters) {
    if (!existingFilters) return;

    let lastOrId;
    let $template;
    const has = Object.prototype.hasOwnProperty;
    Object.entries(existingFilters).forEach(([orGroupId, orFiltersGroup]) => {
      if (has.call(existingFilters, orGroupId)) {
        Object.entries(orFiltersGroup).forEach(([andGroupId, filterData]) => {
          if (has.call(orFiltersGroup, andGroupId) && this.isSupportedFilter(filterData.filter_slug)) {
            if (lastOrId !== orGroupId) {
              lastOrId = orGroupId;
              $template = this.renderNewGroup(filterData);
            } else {
              this.renderInnerGroup($template, filterData);
            }
          }
        });
      }
    });
  }

  /**
   * @param {?FilterData} filterData
   * @returns {jQuery|HTMLElement}
   */
  renderNewGroup(filterData = null) {
    const $template = jQuery(this.prepareFilterGroupTemplate());
    this.UNIQUE_ID_COUNTER += 1;
    $template.data('groupId', this.UNIQUE_ID_COUNTER);
    const $container = jQuery(this.FILTER_GROUP_CONTAINER_ID);

    if ($container.find('.filters-group').length > 0) {
      $container.append(`<div class="filters-group-or"><span>${__('or', 'shopmagic-for-woocommerce')}</span></div>`);
    }
    $container.append($template);

    this.UNIQUE_ID_COUNTER += 1;
    this.renderFilterElement($template, $template.data('groupId'), this.UNIQUE_ID_COUNTER, filterData);

    return $template;
  }

  /**
   * @returns {ChildNode}
   */
  prepareFilterGroupTemplate() {
    const htmlTemplate = useTemplate(filterGroupTemplate);

    htmlTemplate.querySelector('.filter-remove').addEventListener('click', (e) => {
      e.preventDefault();
      /** @type Element */
      const target = e.currentTarget;
      FilterGroups.removeGroup(target.closest('.filters-group'));
    });

    htmlTemplate.querySelector('.filter-add-and').addEventListener('click', (e) => {
      e.preventDefault();
      /** @type Element */
      const target = e.currentTarget;
      this.renderInnerGroup(jQuery(target.closest('.filters-group')));
    });

    return htmlTemplate;
  }

  /**
   * @param {jQuery} $groupHandle
   * @param {?FilterData} filterData
   */
  renderInnerGroup($groupHandle, filterData = null) {
    const $template = jQuery(this.prepareFilterGroupTemplate());
    $template.data('groupId', $groupHandle.data('groupId'));
    $groupHandle.after($template);
    this.UNIQUE_ID_COUNTER += 1;
    this.renderFilterElement($template, $template.data('groupId'), this.UNIQUE_ID_COUNTER, filterData);
  }

  /**
   * @param {jQuery} $template
   * @param {Number} groupNumber
   * @param {Number} ruleNumber
   * @param {?FilterData} filterData
   */
  renderFilterElement($template, groupNumber, ruleNumber, filterData = null) {
    const filterElement = new FilterElement($template, groupNumber, ruleNumber, this.translations);
    filterElement.renderSelectFilterField(this.possibleFilters);
    if (filterData) {
      filterElement.setFilterData(filterData);
    }
  }

  /**
   * @param {HTMLElement} groupHandle
   */
  static removeGroup(groupHandle) {
    const { nextElementSibling } = groupHandle;
    if (nextElementSibling && nextElementSibling.matches('.filters-group-or')) {
      nextElementSibling.remove();
    }
    groupHandle.remove();
  }

  /**
   * @param {string} filterSlug
   * @returns {boolean}
   */
  isSupportedFilter(filterSlug) {
    return Object.values(this.possibleFilters).find((filter) => filter.id === filterSlug);
  }
}

/**
 * @param {Array<Object>} possibleFilters
 * @param {?ExistingFiltersAndGroup} existingFilters
 * @returns {FilterGroups}
 */
export default function initializeFilters(possibleFilters, existingFilters) {
  FilterGroups.cacheExistingFilters(existingFilters);
  const filtersGroups = new FilterGroups(possibleFilters, ShopMagic);
  filtersGroups.attachEvents();
  filtersGroups.renderExistingFilters(FilterGroups.existingFilters);
  return filtersGroups;
}
