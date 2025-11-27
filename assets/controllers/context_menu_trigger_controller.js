import {Controller} from '@hotwired/stimulus';

export default class ContextMenuTriggerController extends Controller {
    static values = {
        targetId: String,
    };

    get contextMenuController() {
        if (!this.targetIdValue) return null;
        const contextMenu = document.getElementById(this.targetIdValue);
        return this.application.getControllerForElementAndIdentifier(contextMenu, 'context-menu');
    }

    open(event) {
        // get the controller's target element
        /** @type {ContextMenuController|null} */
        const contextMenuController = this.contextMenuController;
        if (!contextMenuController) return;

        // prevent the default context menu from appearing
        event.preventDefault();
        contextMenuController.open(event);
    }
}
