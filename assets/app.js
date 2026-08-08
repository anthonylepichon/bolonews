// Présentation : point d'entrée JavaScript global de l'application.
// Rôle : charger Stimulus, les composants Bootstrap et les modules propres à Bolonews.
import './stimulus_bootstrap.js';

// L'import du paquet active le JavaScript officiel de Bootstrap : Collapse pour
// la navigation, Modal pour les confirmations et Alert pour les messages flash.
import 'bootstrap';

// Ces modules sont propres à Bolonews. Chacun vérifie la présence de ses
// attributs data-* avant d'agir, donc ils peuvent être chargés sur toutes les pages.
import './js/like.js';

import './js/article-search.js';

import './js/image-preview.js';

import './js/category-management.js';

import './js/form-validation.js';
