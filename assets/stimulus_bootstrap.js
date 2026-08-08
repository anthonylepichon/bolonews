// Présentation : initialisation du système de contrôleurs Stimulus de Symfony UX.
// Rôle : démarrer Stimulus et offrir un emplacement pour enregistrer des contrôleurs personnalisés.
import { startStimulusApp } from '@symfony/stimulus-bundle';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
