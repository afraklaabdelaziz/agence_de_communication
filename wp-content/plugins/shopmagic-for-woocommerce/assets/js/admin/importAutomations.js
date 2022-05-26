import { useTemplate, importTemplate, noticeTemplate } from './templates';

const { __ } = wp.i18n;

export default class ImportAutomations {
  importContainer;

  constructor() {
    // eslint-disable-next-line prefer-destructuring
    this.titleAction = document.getElementsByClassName('page-title-action')[0];
  }

  prepareTemplate = () => {
    this.appendToDom(this.createActionLink(), this.createImportForm());
  }

  createActionLink = () => {
    /** @type HTMLAnchorElement */
    const importAction = this.titleAction.cloneNode(false);
    importAction.href = '#';
    importAction.innerText = __('Import', 'shopmagic-for-woocommerce');
    importAction.addEventListener('click', this.toggleImportForm);
    return importAction;
  }

  createImportForm = () => {
    const importContainer = document.createElement('div');
    importContainer.appendChild(useTemplate(importTemplate));
    importContainer.querySelector('#automation-import-form').addEventListener('submit', this.processFormSubmit);
    this.importContainer = importContainer;
    return importContainer;
  }

  appendToDom = (...nodes) => {
    const dom = new DocumentFragment();
    dom.append(...nodes);
    this.titleAction.parentNode.insertBefore(dom, this.titleAction.nextSibling);
  }

  /** @param {Event} e */
  processFormSubmit = async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    formData.set('action', 'shopmagic_import_automation');
    formData.set('security', ShopMagic.importNonce);

    try {
      const response = await fetch(ShopMagic.ajaxurl, {
        method: 'POST',
        body: formData,
      });
      const content = await response.json();
      if (content.success) {
        this.showSuccessNotice(content.data);
        setTimeout(() => {
          window.location.reload();
        }, 2000);
      } else {
        this.showErrorNotice(content.data);
      }
    } catch (err) {
      this.showErrorNotice(err.message);
    }
  }

  /** @param {string} message */
  showSuccessNotice = (message) => {
    this.importContainer.insertAdjacentElement('beforebegin', useTemplate(noticeTemplate('success', message)));
  }

  /** @param {string} message */
  showErrorNotice = (message) => {
    this.importContainer.insertAdjacentElement('beforebegin', useTemplate(noticeTemplate('error', message)));
  }

  /** @param {Event} e */
  toggleImportForm = (e) => {
    e.preventDefault();
    this.importContainer.firstElementChild.classList.toggle('show-import-form');
  }
}
