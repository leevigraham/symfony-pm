import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
    handleClick(event) {
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) {
            return;
        }
        debugger;
    }
}
