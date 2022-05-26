import UtmCache from './utmCache';

/**
 * Show placeholder dialog.
 */
export default class PlaceholderDialog {
  WP_ACTION_DIALOG_RENDER = 'shopmagic_placeholder_dialog';

  STRING_RESULT_SELECTOR = '#placeholder_result';

  STRING_COPY_SELECTOR = '#copy_placeholder_result';

  $DIALOG_HANDLE = null;

  /**
   * @param {string} slug
   */
  constructor(slug) {
    this.slug = slug;
  }

  async show() {
    if (this.$DIALOG_HANDLE === null) {
      const placeholderGetUrlParams = new URLSearchParams({
        action: this.WP_ACTION_DIALOG_RENDER,
        slug: this.slug,
        event_data: jQuery("#shopmagic_event_metabox [name^='event']").serialize(),
      });
      const placeholderGetUrl = `${ajaxurl}?${placeholderGetUrlParams}`;
      const response = await fetch(placeholderGetUrl);
      const html = await response.text();
      await this.render(html);
    }
  }

  /**
   * @param {string} html
   * @private
   */
  async render(html) {
    this.$DIALOG_HANDLE = jQuery(html).dialog({
      modal: true,
      draggable: false,
      resizable: false,
      width: 560,
      autoOpen: false,
      closeOnEscape: true,
      classes: {
        'ui-dialog': 'placeholder-dialog',
      },
      close: this.destroy.bind(this),
    });
    const utmCache = new UtmCache(this.$DIALOG_HANDLE);
    this.$DIALOG_HANDLE.on('dialogopen', () => utmCache.loadAll());
    this.$DIALOG_HANDLE.on('dialogbeforeclose', () => utmCache.cacheAll());
    this.$DIALOG_HANDLE.dialog('open');
    this.runStringGenerator();
  }

  /**
   * Create result string and put it as input val.
   * @param {Array<jQuery>} $fields Array of values
   * @private
   */
  generatePlaceholderString($fields) {
    const paramsString = $fields.map((index, item) => {
      const name = jQuery(item).attr('name').toString().replace(/[\[\]]/g, '');
      const value = jQuery(item).val() ? jQuery(item).val().toString() : '';
      if (value.length > 0) {
        return `${name}: '${value}'`;
      }
      return '';
    }).toArray().filter((item) => item.length > 0).join(', ');

    let result = `{{ ${this.slug} }}`;
    if (paramsString.length > 0) {
      result = `{{ ${this.slug} | ${paramsString} }}`;
    }

    jQuery(this.$DIALOG_HANDLE).find(this.STRING_RESULT_SELECTOR).val(result);
  }

  /**
   * Refresh result string and attach all result string behaviours.
   *
   * @private
   */
  runStringGenerator() {
    const $fields = jQuery(this.$DIALOG_HANDLE).find('select, input').not('input[type="checkbox"]').not(this.STRING_RESULT_SELECTOR);

    this.generatePlaceholderString($fields);

    $fields
      .keyup((e) => {
        e.preventDefault();
        this.generatePlaceholderString($fields);
      })
      .change((e) => {
        e.preventDefault();
        this.generatePlaceholderString($fields);
      });

    jQuery(this.$DIALOG_HANDLE).find(this.STRING_COPY_SELECTOR).click((e) => {
      e.preventDefault();
      this.copyResultToClipboard();
      this.$DIALOG_HANDLE.dialog('close');
      this.destroy();
    });
  }

  /**
   * @private
   */
  copyResultToClipboard() {
    const resultField = jQuery(this.$DIALOG_HANDLE).find(this.STRING_RESULT_SELECTOR).get(0);
    resultField.select();
    resultField.setSelectionRange(0, 99999);
    document.execCommand('copy');
  }

  /**
   * Destroys dialog. After this method the dialog is unavailable and not exists in DOM.
   *
   * @private
   */
  destroy() {
    jQuery(this.$DIALOG_HANDLE)
      .empty()
      .remove();
  }
}
