import {Controller} from '@hotwired/stimulus';

export default class extends Controller {

    connect() {
        document.addEventListener('keydown', (event) => {
            if (event.altKey) {
                document.documentElement.classList.add('altkey')
            }
        });
        document.addEventListener('keyup', (event) => {
            document.documentElement.classList.remove('altkey')
        });
    }
}
