import {Controller} from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
/**
 * @property {boolean} hasExampleTarget
 * @property {HTMLInputElement} inputTarget
 * @property {HTMLUListElement} listboxTarget
 * @property {HTMLElement[]} optionTargets
 */
export default class extends Controller {

    static targets = ['input', 'listbox', 'option'];
    filterOptions() {
        const query = this.inputTarget.value.toLowerCase();
        this.optionTargets.forEach(option => {
            const text = option.textContent.toLowerCase();
            if (text.includes(query)) {
                option.classList.remove('hidden');
            } else {
                option.classList.add('hidden');
            }
        });
    }
}
