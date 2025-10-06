import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
/**
 * A Stimulus controller that wraps a button + popover + listbox pattern,
 * behaving like a custom select element.
 *
 * HTML structure example:
 * <div data-controller="select">
 *   <button data-select-target="button" popovertarget="popover-id">Select something</button>
 *   <input type="hidden" data-select-target="input">
 *   <div id="popover-id" popover data-select-target="popover">
 *     <!-- your listbox goes here -->
 *   </div>
 * </div>
 *
 * @property {HTMLButtonElement} buttonTarget - The button that triggers the popover.
 * @property {HTMLInputElement} inputTarget - The hidden input that holds the value.
 * @property {HTMLElement} popoverTarget - The popover element containing the listbox.
 * @property {HTMLElement} listboxTarget - The listbox element that contains the options.
 */
export default class extends Controller {
    static targets = ['button', 'input', 'popover', 'listbox'];

    connect() {
        this.element.addEventListener('listbox:change', this.#onListboxChange.bind(this));
        this.popoverTarget.addEventListener('toggle', this.#onPopoverToggle.bind(this));
    }

    disconnect() {
        this.element.removeEventListener('listbox:change', this.#onListboxChange.bind(this));
        this.popoverTarget.removeEventListener('toggle', this.#onPopoverToggle.bind(this));
    }

    /**
     * Handles the listbox:change event.
     * Updates the button label, hidden input value, closes the popover, and dispatches a select event.
     * @param {CustomEvent} event
     */
    #onListboxChange(event) {
        const option = event.detail.option;
        if (!option) return;

        const label = option.dataset.listboxOptionLabel || option.textContent.trim();
        const value = option.dataset.listboxOptionValue || option.textContent.trim();

        this.buttonTarget.innerHTML = label;
        this.inputTarget.value = value;

        if (this.hasPopoverTarget) {
            this.popoverTarget.hidePopover();
        }

        this.dispatch('select', {
            detail: { option, label, value }
        });
    }

    /**
     *
     * @param {ToggleEvent} event
     */
    #onPopoverToggle(event) {
        if(event.newState !== 'open') {
            return;
        }
        /** @type Listbox_controller */
        const listBoxController = this.application.getControllerForElementAndIdentifier(this.listboxTarget, 'listbox');
        if (listBoxController) {
            listBoxController.updateScrollPosition();
        }
    }

    /**
     * Dispatches an event with the given name and data.
     * Adds debug logging for easier development.
     * @param {string} eventName
     * @param {Object} data
     */
    dispatch(eventName, data) {
        this.context.logDebugActivity?.(`dispatch:${eventName}`, data);
        super.dispatch(eventName, data);
    }
}
