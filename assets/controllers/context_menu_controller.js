import {Controller} from '@hotwired/stimulus';

export default class ContextMenuController extends Controller {

    connect() {
        // Delegated click handler for ALL <a> inside the menu
        this._onClick = (event) => this.handleClick(event);
        this.element.addEventListener('click', this._onClick);
    }

    disconnect() {
        this.element.removeEventListener('click', this._onClick);
    }

    open(event) {
        // Right click: stop the browser context menu
        event.preventDefault();

        // Position at cursor; anchor=… is fine, but we’re forcing explicit coords
        this.element.style.position = 'fixed';
        this.element.style.left = `${event.clientX}px`;
        this.element.style.top = `${event.clientY}px`;
        this.element.showPopover();
    }

    close(event) {
        // If it's not open, nothing to do
        if (!this.element.matches(':popover-open')) return;

        // Click inside the menu? Don't close.
        if (this.element.contains(event.target)) return;

        this.element.hidePopover();
    }

    handleClick(event) {
        const link = event.target.closest('a[data-modifier-key]');
        if (!link) {
            // No special modifier logic, allow default behaviour
            return;
        }

        const requiredModifier = link.dataset.modifierKey;

        const modifierPressed =
            (requiredModifier === 'meta' && event.metaKey) ||
            (requiredModifier === 'alt' && event.altKey) ||
            (requiredModifier === 'ctrl' && event.ctrlKey) ||
            (requiredModifier === 'shift' && event.shiftKey);
        debugger;
        if (!modifierPressed) {
            // Wrong modifier → BLOCK click entirely
            event.preventDefault();
            return;
        }

        // Correct modifier → BLOCK browser default (prevents download)
        event.preventDefault();

        // Follow link manually (Turbo users: Turbo.visit(link.href) instead)
        window.location.assign(link.href);

        // OPTIONAL: close menu after click
        this.element.hidePopover();
    }
}
