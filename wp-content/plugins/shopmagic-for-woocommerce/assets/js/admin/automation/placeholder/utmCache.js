/**
 * Cache UTM parameters in localStorage for use between placeholder parameters.
 */
export default class UtmCache {
  /*
   * Coupled with php values for UTM.
   * @see PlaceholderUtmBuilder::UTM_PARAMETER_KEYS
   */
  UTM_PARAMETERS = [
    'utm_source',
    'utm_medium',
    'utm_campaign',
    'utm_content',
    'utm_term',
  ]

  /**
   * @param {jQuery} dialogHandler
   */
  constructor(dialogHandler) {
    this.fields = dialogHandler[0].querySelectorAll('input');
  }

  cacheAll() {
    this.fields.forEach((field) => {
      if (this.isUtmParameter(field)) {
        this.cache(field);
      }
    });
  }

  loadAll() {
    this.fields.forEach((field) => {
      if (this.isUtmParameter(field)) {
        this.load(field);
      }
    });
  }

  /**
   * @param {HTMLInputElement} field
   */
  cache(field) {
    localStorage.setItem(field.id, field.value);
  }

  /**
   * @param {HTMLInputElement} field
   */
  load(field) {
    field.value = localStorage.getItem(field.id);
  }

  /**
   * @param {HTMLInputElement} field
   */
  isUtmParameter(field) {
    return this.UTM_PARAMETERS.indexOf(field.id) !== -1;
  }
}
