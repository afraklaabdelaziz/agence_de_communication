// eslint-disable-next-line max-classes-per-file
const { __ } = wp.i18n;

/** Private error class. */
class AttachmentMimeError extends Error {
  constructor(message) {
    super(message);
    this.message = message;
    this.name = 'AttachmentMimeError';
  }
}

class ActionAttachment {
  SUPPORTED_MIMES = ShopMagic.supportedMimes

  /**
   * @param {string} containerId
   * @param {string} valueIdentifier
   */
  constructor(containerId, valueIdentifier) {
    this.mediaContainerEl = document.getElementById(containerId);
    this.valueIdentifier = valueIdentifier;
    this.addAttachmentEl = this.mediaContainerEl.querySelector('.js-add-attachment');
    this.attachmentsList = this.mediaContainerEl.querySelector('.js-attachments-container');
  }

  bindEvents = () => {
    this.addAttachmentEl.addEventListener('click', this.selectMedia);
    this.attachmentsList.querySelectorAll('.js-remove-attachment').forEach((attachment) => {
      attachment.addEventListener('click', this.removeAttachment);
    });
  }

  /** @param {Event} e */
  selectMedia = (e) => {
    e.preventDefault();
    this.maybeRemoveErrorMessage();
    if (!this.frame) {
      this.initializeFrame();
    }

    this.frame.open();
  }

  initializeFrame = () => {
    this.frame = wp.media({
      multiple: true,
    });

    this.frame.on('select', () => {
      try {
        this.selectAttachment();
      } catch (e) {
        if (e instanceof AttachmentMimeError) {
          this.showErrorMessage(e);
        } else {
          throw e;
        }
      }
    });
  }

  selectAttachment = () => {
    const attachments = this.frame.state().get('selection').toJSON();
    attachments.forEach((attachment) => {
      const { mime, url, filename } = attachment;
      if (!this.SUPPORTED_MIMES.includes(mime)) throw new AttachmentMimeError(__('Currently, only PDF attachments are supported.', 'shopmagic-for-woocommerce'));
      this.mediaContainerEl.insertAdjacentElement('afterbegin', this.createInputElement(url));
      this.attachmentsList.insertAdjacentElement('beforeend', this.createAttachmentElement(url, filename));
    });
  }

  /**
   * @param {string} url
   * @param {string} filename
   * @returns {HTMLParagraphElement}
   */
  createAttachmentElement(url, filename) {
    const wrapper = document.createElement('p');

    const attachmentRemoval = document.createElement('a');
    attachmentRemoval.setAttribute('href', '#');
    attachmentRemoval.classList.add('js-remove-attachment', 'shopmagic-remove-attachment-button');
    attachmentRemoval.insertAdjacentText('afterbegin', '✕');

    attachmentRemoval.addEventListener('click', this.removeAttachment);

    const attachment = document.createElement('a');
    const attachmentText = document.createTextNode(filename);
    attachment.setAttribute('href', url);
    attachment.setAttribute('target', '_blank');
    attachment.appendChild(attachmentText);

    wrapper.appendChild(attachmentRemoval);
    wrapper.appendChild(attachment);
    return wrapper;
  }

  /**
   * @param {string} value
   * @returns {HTMLInputElement}
   */
  createInputElement = (value) => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.value = value;
    input.name = this.valueIdentifier;

    return input;
  }

  /** @param {Event} e */
  removeAttachment = (e) => {
    e.preventDefault();
    /** @type {Element} */
    const attachmentRemoval = e.currentTarget;
    const url = attachmentRemoval.nextElementSibling.getAttribute('href');
    attachmentRemoval.parentElement.remove();
    this.mediaContainerEl.querySelector(`[value="${url}"]`).remove();
  }

  /** @param {AttachmentMimeError} e */
  showErrorMessage = (e) => {
    const errorElement = document.createElement('p');
    const message = document.createTextNode(e.message);
    errorElement.appendChild(message);

    const errorNotice = document.createElement('div');
    errorNotice.classList.add('notice', 'notice-error', 'is-dismissible');

    const button = document.createElement('button');
    button.classList.add('notice-dismiss');
    button.addEventListener('click', (evt) => {
      evt.preventDefault();
      window.makeNoticeDismissible(button);
    });

    errorNotice.appendChild(errorElement);
    errorNotice.appendChild(button);
    this.mediaContainerEl.insertAdjacentElement('beforeend', errorNotice);
  }

  maybeRemoveErrorMessage = () => {
    const errorEl = this.mediaContainerEl.querySelector('.shopmagic-error');
    if (errorEl) {
      errorEl.remove();
    }
  }
}

/**
 * @param {string} containerId
 * @param {string} valueIdentifier
 */
export default (containerId, valueIdentifier) => (new ActionAttachment(containerId, valueIdentifier)).bindEvents();
