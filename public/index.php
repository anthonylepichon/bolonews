<?php

/*
 * Présentation : contrôleur frontal appelé par le serveur web.
 * Rôle : charger l'autoload Composer puis démarrer le Kernel pour chaque requête HTTP.
 */

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return static function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
