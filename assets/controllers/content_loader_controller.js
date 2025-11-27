import {Controller} from '@hotwired/stimulus';

export default class ContentLoader extends Controller {

    static values = {
        url: String
    }

    connect() {
        fetch(this.urlValue)
            .then(response => response.text())
            .then(html => {
                this.element.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading content:', error);
            });
    }
}
