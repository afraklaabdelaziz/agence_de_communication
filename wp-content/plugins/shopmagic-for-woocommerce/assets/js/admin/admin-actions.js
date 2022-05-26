import LoadAction from './automation/action/loadAction';

const actionLoader = new LoadAction();
/**
 * @param {HTMLElement} container
 * @param {string} previous
 * @param {string} current
 * @param {string[]} attributes
 */
const updateActionOrder = (container, previous, current, attributes = ['id', 'class', 'name', 'for']) => {
  function updateAttribute(el, attribute) {
    const updatedAttribute = el.getAttribute(attribute).replace(previous, current);
    el.setAttribute(attribute, updatedAttribute);
  }

  attributes.forEach((attribute) => {
    if (container.getAttribute(attribute)) {
      updateAttribute(container, attribute);
    }
    container.querySelectorAll(`[${attribute}*='${previous}']`).forEach((el) => {
      updateAttribute(el, attribute);
    });
  });
};

/**
 * @param {Event} e
 */
const titleChange = (e) => {
  const id = e.currentTarget.getAttribute('id').split('_')[3];
  jQuery(`#_action_title_${id}`).text(e.currentTarget.value);
};

/**
 * @param {Event} e
 */
const actionSelClick = (e) => {
  e.stopPropagation();
};

/**
 * @param {HTMLElement} containerToRemove
 *
 * @returns boolean
 */
const removeAction = (containerToRemove) => {
  const actionsContainer = containerToRemove.closest('.sm-actions-wrap');

  containerToRemove.closest('.action-form-table').remove();

  actionsContainer.querySelectorAll('[id^="action-area"]').forEach((container, index) => {
    updateActionOrder(container, index, index - 1);
    container.querySelector('.action_number').innerText = index;
  });

  return false;
};

/** @returns {false} Always returns false to prevent processing event. */
const addNewAction = () => {
  /** @returns {number} */
  function nextIndex() {
    return document.querySelectorAll('[id^="action-area"]').length - 1;
  }

  /** @type HTMLDivElement */
  const actionContainer = document.getElementById('action-area-stub').cloneNode(true);
  actionContainer.id = `action-area-${nextIndex()}`;

  actionContainer
      .querySelector('#action-config-area-stub')
      .id = `action-config-area-${nextIndex()}`;

  const actionStub = actionContainer.querySelector('#_action_stub');
  actionStub.id = `_actions_${nextIndex()}_action`;
  actionStub.name = `actions[${nextIndex()}][_action]`;

  const actionSelector = actionContainer.querySelector('.action_main_select');
  actionSelector.addEventListener('change', (e) => {
    e.stopPropagation();
    actionLoader.loadActionParams(e.currentTarget);
  });
  actionSelector.addEventListener('click', actionSelClick);

  actionContainer.querySelector('.action_number').innerHTML = nextIndex() + 1;

  actionContainer.querySelector('#_action_title_stub').id = `_action_title_${nextIndex()}`;

  const actionTitleLabel = actionContainer.querySelector('#action_title_label_stub');
  actionTitleLabel.id = `action_title_label${nextIndex()}_action`;
  actionTitleLabel.for = `action_title_input_${nextIndex()}`;

  const actionTitle = actionContainer.querySelector('#action_title_stub');
  actionTitle.id = `action_title_input_${nextIndex()}`;
  actionTitle.name = `actions[${nextIndex()}][_action_title]`;

  actionTitle.addEventListener('input', titleChange);

  updateActionOrder(actionContainer, 'occ', nextIndex());

  jQuery(actionContainer).insertAfter('.action-form-table:last');

  document.dispatchEvent(new CustomEvent('sm:actionadded'))

  return false;
};

export const bindAutomationActionsListeners = () => {
  document.querySelectorAll('.action_main_select').forEach((el) => {
    actionLoader.putInQueue(el);
    el.addEventListener('input', actionSelClick);
  });
  actionLoader.checkQueue();
  document.querySelectorAll('.action_title_input')
      .forEach((el) => {
        el.addEventListener('input', titleChange);
      });
};


const attachAddAndRemoveEvent = (row) => {
  const duplicateRow = (currentRow) => {
    const clone = document.getElementById('field-multiple-row').content.firstElementChild.cloneNode(true)
    attachAddAndRemoveEvent(clone)

    currentRow.parentNode.insertBefore(clone, currentRow.nextSibling)
  }
  const removeRow = (row) => row.remove()
  const getParent = (el) => {
    return el.closest('.js-clone-wrapper')
  }
  const renameFields = () => {
    const rows = document.querySelectorAll('table .shopmagic-field--multiple label')
    requestAnimationFrame(() => {
      rows.forEach((label, index) => {
        label.innerText = String.fromCharCode('A'.charCodeAt() + index)
      })
    })
  }

  // It may happen, that following selectors were removed by JS before running the function, don't add event handler then.
  row.querySelector('.add-field')?.addEventListener('click', (e) => {
    duplicateRow(getParent(e.currentTarget))
    renameFields()
  })

  row.querySelector('.remove-field')?.addEventListener('click', (e) => {
    removeRow(getParent(e.currentTarget))
    renameFields()
  })
}

export const registerGlobalFunctions = () => {
  window.addNewAction = addNewAction;
  window.removeAction = removeAction;
  window.attachAddAndRemove = attachAddAndRemoveEvent;
};

