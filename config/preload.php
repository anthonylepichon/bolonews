<?php

/*
 * Présentation : point de préchargement PHP utilisé en production.
 * Rôle : charger le fichier optimisé généré dans le cache lorsqu'il existe.
 */

if (file_exists(dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php')) {
    require dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php';
}
