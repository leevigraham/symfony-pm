import {Controller} from '@hotwired/stimulus';

export default class DrawerManagerController extends Controller {

    static targets = ['drawer', 'drawerContainer', "drawerTemplate"];
    static values = {
        drawerCount: Number
    }

    openUrlInDrawer(event) {
        event.preventDefault();
        const targetUrl = event.params.url || event.currentTarget.href;
        // parse url and add param drawer=1
        const url = new URL(targetUrl);
        url.searchParams.set('X-Layer', 'sheet');

        const drawerTemplate = `
        <div class="drawer" data-drawer-manager-target="drawer" data-controller="drawer">
            <div class="drawer-backdrop" data-drawer-target="backdrop"></div>
            <div class="drawer-body" role="dialog" aria-modal="true" data-drawer-target="body">
                <div data-controller="content-loader" data-content-loader-url-value="${url.toString()}">
                    Loading…
                </div>
            </div>
        </div>
        `;

        // Close all popovers
        document.querySelectorAll('[popover]:popover-open')
            .forEach(el => el.hidePopover());

        this.drawerContainerTarget.insertAdjacentHTML("beforeend", drawerTemplate);
    }

    #updateDrawerCount() {
        const count = this.drawerTargets.length;
        this.drawerContainerTarget.style.setProperty('--drawer-count', count);
        this.drawerTargets.forEach((drawer, i) => {
            const index = i + 1;
            drawer.inert = count < index;
            drawer.style.setProperty('--drawer-index', index);
        });
        // document.getElementById('app').inert = count > 0;
        this.drawerContainerTarget.hidden = count === 0;
        this.drawerCountValue = count;
    }

    drawerTargetConnected(element) {
        this.dispatch("drawer-connected", { detail: { element } });
        this.#updateDrawerCount();
    }

    drawerTargetDisconnected(element) {
        this.#updateDrawerCount();
    }
}
