// Présentation : contrôleur Stimulus d'exemple généré lors de l'installation.
// Rôle : montrer comment relier un data-controller HTML à une classe JavaScript.
import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    // -----------------------
    // ATTRIBUTS
    // -----------------------
    // Aucun attribut propre : this.element est fourni par Stimulus.

    // -----------------------
    // METHODES
    // -----------------------

    connect() {
        this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/hello_controller.js';
    }
}
