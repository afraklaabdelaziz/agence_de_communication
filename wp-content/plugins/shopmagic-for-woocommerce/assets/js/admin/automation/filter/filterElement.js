/**
 * Single filter element with options.
 */
export default class FilterElement {
  filter = null;

  $container= null;

  constructor($container, groupNumber = '', ruleNumber = 0, translations = {}) {
    this.$container = $container;
    this.translations = translations;

    this.groupNumber = groupNumber;
    this.ruleNumber = ruleNumber;
  }

  afterRenderHook() {}

  /**
   *
   * @param {Object} field
   * @returns {jQuery|HTMLElement}
   */
  prepareHtmlField(field) {
    /**
     * @param {jQuery} $select
     * @param {Object} options
     */
    const setSelectOptions = function ($select, options) {
      for (const key in options) {
        if (options.hasOwnProperty(key)) {
          const val = options[key];
          $select.append(`<option value="${key}">${val}</option>`);
        }
      }
    };

    let $element;
    switch (field.template) {
      case 'select':
        $element = jQuery('<select />');
        setSelectOptions($element, field.options);
        break;
      case 'woo-select':
        $element = jQuery('<select />');
        $element.prop('multiple', true);
        $element.addClass('wc-enhanced-select');
        setSelectOptions($element, field.options);

        const old_hook = this.afterRenderHook;
        this.afterRenderHook = function () {
          jQuery(document.body).trigger('wc-enhanced-select-init');
          old_hook();
        };
        break;
      case 'product-select':
        $element = jQuery('<select />')
          .addClass('wc-product-search')
          .data('action', 'woocommerce_json_search_products_and_variations')
          .data('placeholder', field.placeholder);
        setSelectOptions($element, field.options);

        const old = this.afterRenderHook;
        this.afterRenderHook = (function ($element) {
          return function () {
            // this type of select has no options, so must put previous values as special options
            const prevVal = $element.data('preval');
            if (prevVal) {
              for (const name in prevVal) {
                if (prevVal.hasOwnProperty(name)) {
                  const $option = jQuery('<option />');
                  $option
                    .attr('selected', 'selected')
                    .val(prevVal[name])
                    .html(name);
                  $element.append($option);
                }
              }
            }

            jQuery(document.body).trigger('wc-enhanced-select-init');
            old();
          };
        }($element));
        break;
      case 'input-date-picker':
        $element = jQuery('<input type="text" />')
          .addClass('date-picker')
          .datepicker({
            dateFormat: 'yy-mm-dd',
            showOtherMonths: true,
            selectOtherMonths: true,
            showButtonPanel: true,
          });
        break;
      case 'paragraph':
        return jQuery(`<p>${field.description}</p>`);
      case 'pro-event-info':
        $element = jQuery(`${field.description}`);
        $element.addClass(`${field.class}`);
        return $element;
      default:
        $element = jQuery('<input type="text" />');
    }

    if (field.disabled) {
      $element.prop('disabled', true);
    }
    if (field.readonly) {
      $element.prop('readonly', true);
    }
    if (field.multiple) {
      $element.attr('name', `${this.generateFieldName(field.name)}[]`);
      $element.prop('multiple', true);
    } else {
      $element.attr('name', this.generateFieldName(field.name));
    }
    if (field.placeholder) {
      $element.prop('placeholder', field.placeholder);
    }

    if (field.label) {
      $element.prepend(jQuery(`<label>${field.label}</label>`));
    }

    return $element;
  }

  /**
   * @param {string} fieldName
   * @returns {string}
   * @access private
   */
  generateFieldName(fieldName) {
    return `_filters[${[this.groupNumber, this.ruleNumber, fieldName].join('][')}]`;
  }

  /**
   * @param {Array<Object>} possibleFilters
   * @access private
   */
  renderSelectFilterField(possibleFilters) {
    const $select = jQuery('<select />');
    $select.attr('name', this.generateFieldName('filter_slug'));

    let groupName = null;
    let $group = null;

    possibleFilters.forEach((filter) => {
      const $option = jQuery('<option />')
        .val(filter.id)
        .html(filter.name)
        .data('filter', filter);

      if (filter.group !== groupName) {
        if ($group !== null) {
          $select.append($group);
        }
        groupName = filter.group;
        $group = jQuery('<optgroup />').attr('label', groupName);
      }

      $group.append($option);
    });
    if ($group !== null) {
      $select.append($group);
    }

    $select.change((e) => {
      const selectedFilter = jQuery(e.target).find(':selected').data('filter');

      this.renderFields(selectedFilter);
    });

    this.$container.find('.filter-field-select').append($select);
  }

  /**
   * @param {Object} filterData
   * @access public
   */
  setFilterData(filterData = null) {
    const $option = this.$container.find(`.filter-field-select select option[value=${filterData.filter_slug}]`);
    if ($option.length > 0) {
      $option.attr('selected', 'selected');
      const selectedFilter = $option.data('filter');
      this.renderFields(selectedFilter, filterData.data);
    }
  }

  /**
   * @param {Object} selectedFilter
   * @param {Object} fieldsData
   * @access private
   */
  renderFields(selectedFilter, fieldsData = null) {
    this.filter = selectedFilter;

    this.afterRenderHook = function () {
    };

    const $fieldsContainer = this.$container.find('.filter-fields');
    $fieldsContainer.empty();

    if (this.filter.fields) {
      this.filter.fields.forEach((field) => {
        const $htmlField = this.prepareHtmlField(field);
        if (fieldsData && fieldsData.hasOwnProperty(field.name)) {
          $htmlField.val(fieldsData[field.name]);
          $htmlField.data('preval', fieldsData[field.name]);
        }
        $fieldsContainer.append($htmlField);
        $htmlField.wrap('<div class="field"></div>');
      });
    }

    this.afterRenderHook();
  }
}
