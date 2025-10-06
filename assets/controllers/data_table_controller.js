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
}
