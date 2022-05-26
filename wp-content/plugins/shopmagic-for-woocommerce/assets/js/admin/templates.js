const { __ } = wp.i18n;

export const importTemplate = `
<div class="import-automation">
  <p class="import-help">${__('If you have an automation in JSON format, you can import it by submitting the form below.', 'shopmagic-for-woocommerce')}</p>
  <form id="automation-import-form" method="POST" class="import-upload-form" enctype="multipart/form-data">
    <label class="screen-reader-text" for="importfile">${__('JSON file to import', 'shopmagic-for-woocommerce')}</label>
    <input id="importfile" name="automations-json" type="file" accept="application/json">
    <input class="button" type="submit" value="${__('Import', 'shopmagic-for-woocommerce')}">
  </form>
</div>`;

export const filterGroupTemplate = `
<div class="filters-group">
    <div class="filter-field-select"></div>
    <div class="filter-fields"></div>
    <div class="filter-buttons">
        <button class="button filter-add-and">${__('and', 'shopmagic-for-woocommerce')}</button>
        <button class="filter-remove" title="${__('remove', 'shopmagic-for-woocommerce')}">&ndash;</button>
    </div>
</div>`;

export const noticeTemplate = (type, message) => `
<div class="notice notice-${type} is-dismissible">
  <p>${message}</p>
  <button type="button" class="notice-dismiss" onclick="window.makeNoticeDismissible(this)"><span class="screen-reader-text">${__('Hide message', 'shopmagic-for-woocommerce')}</span></button>
</div>`;

/**
 * @param {string} template
 * @returns {Node}
 */
export function useTemplate(template) {
  const { body: htmlTemplate } = (new DOMParser()).parseFromString(template, 'text/html');
  return htmlTemplate.firstChild.cloneNode(true);
}
