import {Controller} from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    scrollToTop() {
        /** @var {HTMLElement} element */
        // Scroll to the top the element this controller is attached to
        this.element.scrollIntoView({behavior: 'auto', block: 'start'});
    }

    handleRowClick(event) {
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) {
            return;
        }

        const interactiveSelector = `
          .Chip,
          a[href],
          button:not(:disabled),
          input:not(:disabled),
          select:not(:disabled),
          textarea:not(:disabled),
          [contenteditable="true"],
          [tabindex]:not([tabindex="-1"]),
          [role="button"],
          [role="link"],
          [role="checkbox"],
          [role="menuitem"],
          [role="option"],
          [role="radio"],
          [role="switch"]
        `;
        const row = event.target.closest('tbody tr');
        if (!row) {
            return;
        }
        if (event.target.closest(interactiveSelector)) {
            return;
        }

        row.querySelector('[data-action="view"]')?.click();
    }
}
