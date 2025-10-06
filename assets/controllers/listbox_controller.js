import {Controller} from '@hotwired/stimulus';

/**
 * A Stimulus controller for managing a listbox component.
 * It implements the ARIA Authoring Practices (APG) for listbox.
 * @see https://www.w3.org/WAI/ARIA/apg/patterns/listbox/
 *
 * @property {HTMLDivElement} listboxTarget - The listbox element.
 * @property {boolean} hasListboxTarget - Whether the listbox target is present.
 * @property {HTMLInputElement} filterTarget - The input element for filtering targets.
 * @property {boolean} hasFilterTarget - Whether the filter target is present.
 * @property {HTMLElement} noResultsTarget - The no results message element.
 * @property {boolean} hasNoResultsTarget - Whether the no results target is present.
 * @property {HTMLElement[]} optionTargets - The list of option elements in the listbox.
 * @property {Boolean} selectionFollowFocusValue - Selection follow focus.
 * @property {Boolean} setAriaActiveDescendantValue - Sets `aria-activedescendant` attribute on the listbox.
 *
 * stimulusFetch: 'lazy'
 */
export default class Listbox_controller extends Controller {
    static targets = ['listbox', 'option', 'filter', 'noResults'];
    static values = {

        /**
         * Selection follow focus.
         * If true, the currently focused option will be selected.
         * @see: https://www.w3.org/WAI/ARIA/apg/practices/keyboard-interface/#decidingwhentomakeselectionautomaticallyfollowfocus
         */
        selectionFollowFocus: {
            type: Boolean, default: false
        },

        /**
         * Sets `aria-activedescendant` attribute on the listbox.
         * Disable if listbox is used in a combobox or similar component where the active descendant is managed by the parent component.
         * @see: https://www.w3.org/WAI/ARIA/apg/practices/keyboard-interface/#kbd_focus_activedescendant
         */
        setAriaActiveDescendant: {
            type: Boolean, default: true
        }
    }

    /**
     * String of keys pressed so far for character navigation.
     * @type {string}
     */
    #keysSoFar = '';

    /**
     * Timeout ID for clearing the keysSoFar after a delay.
     * @type {number|undefined}
     */
    #keyClearTimeout;

    /**
     * Whether this listbox is part of a combobox component.
     */
    #isCombobox = false;

    /**
     * Returns the HTMLElement that this controller is attached to.
     * @returns {HTMLElement}
     */
    get element() {
        return super.element;
    }

    /**
     * The currently active descendant of the listbox.
     * @prop {HTMLElement|null} currentActiveDescendant
     */
    #currentActiveDescendant = null;

    /**
     * Returns the currently active descendant of the listbox.
     * @returns {HTMLElement|null}
     */
    get currentActiveDescendant() {
        return this.#currentActiveDescendant;
    }

    /**
     * Sets the currently active descendant of the listbox.
     * This updates the aria-activedescendant attribute and adds a focused class to the option.
     * @param {HTMLElement|null} option
     */
    set currentActiveDescendant(option) {
        if (option === this.#currentActiveDescendant) {
            return;
        }
        this.#currentActiveDescendant = option;
        this.optionTargets.forEach(optionTarget => {
            optionTarget.classList.toggle('focused', optionTarget === option);
        });

        const targetElement = this.#isCombobox ? this.filterTarget : this.listboxTarget;
        if (option) {
            targetElement.setAttribute('aria-activedescendant', option.id);
        } else {
            targetElement.removeAttribute('aria-activedescendant');
        }

        this.dispatch("focuschange", {
            detail: {option: option}
        });

        if (this.selectionFollowFocusValue) {
            this.#selectOption(option);
        }

        this.updateScrollPosition();
    }

    get visibleOptionTargets() {
        return this.optionTargets.filter(option => !option.classList.contains('hidden'));
    }

    connect() {
        // this.listboxTarget.tabIndex = -1;
        this.element.addEventListener('focus', this.#handleFocus.bind(this));
        this.element.addEventListener('click', this.#handleClick.bind(this));
        this.element.addEventListener('keydown', this.#handleKeyPress.bind(this));
        // this.element.addEventListener('mousemove', this.#handleMouseMove.bind(this));
        // this.element.addEventListener('mouseout', this.#handleMouseOut.bind(this));
        if (this.hasFilterTarget) {
            this.#isCombobox = true;
            // this.filterTarget.tabIndex = 0;
            this.selectionFollowFocusValue = false;
            this.filterTarget.addEventListener('input', this.#handleFilterInput.bind(this));
        } else {
            this.#isCombobox = false;
            this.selectionFollowFocusValue = true;
            // this.element.tabIndex = 0;
        }
        super.connect();
    }

    disconnect() {
        this.element.removeEventListener('focus', this.#handleFocus.bind(this));
        this.element.removeEventListener('click', this.#handleClick.bind(this));
        this.element.removeEventListener('keydown', this.#handleKeyPress.bind(this));
        // this.element.removeEventListener('mousemove', this.#handleMouseMove.bind(this));
        // this.element.removeEventListener('mouseout', this.#handleMouseOut.bind(this));
        if (this.hasFilterTarget) {
            this.filterTarget.removeEventListener('input', this.#handleFilterInput.bind(this));
        }
        super.disconnect();
    }

    #handleFocus() {
        if (this.currentActiveDescendant) {
            this.updateScrollPosition();
        } else {
            this.focusFirstOption(false);
        }
    }

    #handleClick(event) {
        const option = event.target.closest('[role="option"]');
        if (option && this.element.contains(option)) {
            this.currentActiveDescendant = option;
            this.#selectOption(option);
            this.element.focus();
        }
    }

    #handleMouseMove(event) {
        this.currentActiveDescendant = event.target.closest('[role="option"]');
    }

    #handleMouseOut(event) {
        this.currentActiveDescendant = event.target.closest('[role="option"]');
    }

    /**
     * Handles key presses for navigation and character input.
     * @param {KeyboardEvent} event
     */
    #handleKeyPress(event) {
        const key = event.key;
        if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(key)) {
            event.preventDefault();
            switch (key) {
                case 'ArrowDown':
                    this.focusNextOption();
                    break;
                case 'ArrowUp':
                    this.focusPreviousOption();
                    break;
                case 'Home':
                    this.focusFirstOption();
                    break;
                case 'End':
                    this.focusLastOption();
                    break;
            }
            return;
        }

        const selectFocusKeys = this.#isCombobox ? ['Enter'] : ['Enter', ' '];
        if (!this.selectionFollowFocusValue && selectFocusKeys.includes(key)) {
            event.preventDefault();
            this.#selectOption(this.currentActiveDescendant);
            return;
        }

        if (!this.#isCombobox) {
            // Printable character support for character navigation
            if (key.length === 1 && !event.ctrlKey && !event.metaKey) {
                this.#handlePrintableCharacter(key.toLowerCase());
            }
        }
    }

    /**
     * Handles input from the filter target.
     * @param {InputEvent} event
     */
    #handleFilterInput(event) {
        if (!this.hasFilterTarget) {
            return;
        }
        // Get the current value of the filter input
        const query = this.filterTarget.value.toLowerCase();
        // Toggle visibility of options based on the filter input
        let hasVisibleOptions = false;
        this.optionTargets.forEach(option => {
            const text = (option.dataset.listboxOptionLabel || option.textContent).toLowerCase();
            const isVisible = text.includes(query);
            option.classList.toggle('hidden', !isVisible);
            if (isVisible) {
                hasVisibleOptions = true;
            }
        });
        // If the current active descendant is not visible update focus
        if (this.currentActiveDescendant?.classList.contains('hidden')) {
            this.currentActiveDescendant = null;
        }

        this.noResultsTarget.classList.toggle('hidden', hasVisibleOptions);
        this.listboxTarget.classList.toggle('hidden', !hasVisibleOptions);

        // Additionally hide groups if none of their options are visible
        this.element.querySelectorAll('[role="group"]').forEach(group => {
            const options = Array.from(group.querySelectorAll('[role="option"]'));
            const hasVisibleOption = options.some(option => !option.classList.contains('hidden'));
            group.classList.toggle('hidden', !hasVisibleOption);
        });

        // Update scroll position to the currently focused option
        this.updateScrollPosition();

        this.dispatch('filter', {
            detail: { query }
        })
    }

    /**
     * Handles printable characters for character navigation.
     * Type a character: focus moves to the next item with a name that starts with the typed character.
     * Type multiple characters in rapid succession: focus moves to the next item with a name that starts with the string of characters typed.
     * @param character
     */
    #handlePrintableCharacter(character) {
        this.#keysSoFar += character;
        clearTimeout(this.#keyClearTimeout);
        this.#keyClearTimeout = setTimeout(() => {
            this.#keysSoFar = '';
        }, 500);

        const optionToFocus = this.#findOptionStartingWith(this.#keysSoFar);
        if (optionToFocus) {
            this.focusOption(optionToFocus);
        }
    }

    /**
     * Finds the first option that starts with the given prefix.
     * @param prefix
     * @returns {HTMLElement|null}
     */
    #findOptionStartingWith(prefix) {
        if (!prefix) {
            return null;
        }

        const options = this.optionTargets;
        const startIndex = this.currentActiveDescendant ? options.indexOf(this.currentActiveDescendant) : -1;

        // use spread to merge slices into a single wrapped array
        const wrapped = [...options.slice(startIndex + 1), ...options.slice(0, startIndex)];

        const option = wrapped.find(option => option.textContent.trim().toLowerCase().startsWith(prefix));

        return option ?? null;
    }

    updateScrollPosition() {
        if(this.currentActiveDescendant){
            this.currentActiveDescendant.scrollIntoView({
                block: 'nearest', inline: 'nearest'
            });
        } else {
            this.listboxTarget.scrollTop = 0;
        }
    }

    focusFirstOption(updateScroll = true) {
        this.focusOption(this.visibleOptionTargets.at(0), updateScroll);
    }

    focusLastOption() {
        this.focusOption(this.visibleOptionTargets.at(-1));
    }

    focusNextOption() {
        const options = this.visibleOptionTargets;
        if (options.length === 0) return;

        const currentIndex = options.indexOf(this.currentActiveDescendant);

        // if nothing focused yet, focus first
        if (currentIndex === -1) {
            this.focusOption(options[0]);
            return;
        }

        // if we can go down, focus next
        if (currentIndex + 1 < options.length) {
            this.focusOption(options[currentIndex + 1]);
        }
    }

    focusPreviousOption() {
        const options = this.visibleOptionTargets;
        if (options.length === 0) return;

        const currentIndex = options.indexOf(this.currentActiveDescendant);

        // if nothing focused yet, focus last
        if (currentIndex === -1) {
            this.focusOption(options[options.length - 1]);
            return;
        }

        // if we can go up, focus previous
        if (currentIndex - 1 >= 0) {
            this.focusOption(options[currentIndex - 1]);
        }
    }

    /**
     * @param {HTMLElement|null} option
     * @param {boolean} updateScroll - Whether to update the scroll position of the listbox.
     */
    focusOption(option, updateScroll = true) {
        this.currentActiveDescendant = option;
        if (option?.classList.contains('hidden')) {
            return;
        }
        if (updateScroll) {
            this.updateScrollPosition();
        }
    }

    /**
     * Selects the given option and updates the aria-selected attribute.
     * @param {HTMLElement|null} option
     */
    #selectOption(option) {
        this.optionTargets.forEach(optionTarget => {
            if(optionTarget === option) {
                optionTarget.setAttribute('aria-selected', 'true');
            } else {
                optionTarget.removeAttribute('aria-selected');
            }
        });
        this.dispatch('change', {
            detail: {option}
        });
    }

    /**
     * Dispatches an event with the given name and data.
     * Adds debug logging for the event.
     * @param eventName
     * @param data
     */
    dispatch(eventName, data) {
        this.context.logDebugActivity(`dispatch:${eventName}`, data);
        super.dispatch(eventName, data);
    }
}
