/**
 * When the action is changed on frontend, trigger UI update.
 */
export default class LoadAction {
  queue = new Set();

  /**
   * @param {HTMLSelectElement} actionEl
   */
  putInQueue(actionEl) {
    if (this.getId(actionEl) < 0) return;

    document.querySelector(`#action-area-${this.getId(actionEl)} .spinner`).classList.add('is-active');
    document.querySelector(`#action-area-${this.getId(actionEl)} .error-icon`).classList.remove('error-icon-visible');

    this.queue.add(actionEl);
  }

  // eslint-disable-next-line class-methods-use-this
  getId(actionEl) {
    return actionEl.parentElement.querySelector('.action_number').innerText - 1;
  }

  async checkQueue() {
    for (const item of this.queue) {
      // eslint-disable-next-line no-await-in-loop
      await this.loadActionParams(item);
    }
  }

  /**
   * @param {HTMLSelectElement} control
   * @returns {Promise<void>}
   */
  async loadActionParams(control) {
    if (!control.value.length) return;

    const response = await fetch(ShopMagic.ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'shopmagic_load_action_params',
        action_slug: control.value,
        action_id: this.getId(control),
        post: document.getElementById('post_ID').value,
        editor_initialized: window.SM_EditorInitialized === true,
        paramProcessNonce: ShopMagic.paramProcessNonce,
      }),
    });

    if (response.ok) {
      const params = await response.json();
      jQuery(`#action-config-area-${this.getId(control)}`).html(params.action_box);// .tinymce_textareas();
    } else {
      document.querySelector(`#action-area-${this.getId(control)} .error-icon`).classList.add('error-icon-visible');
    }
    document.querySelector(`#action-area-${this.getId(control)} .spinner`).classList.remove('is-active');
  }
}
