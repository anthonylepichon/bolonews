<?php

/*
 * Présentation : noyau technique de l'application Symfony.
 * Rôle : démarrer le framework et assembler sa configuration grâce au MicroKernelTrait.
 */

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------
    // Aucun attribut déclaré dans le Kernel du projet.

    // -----------------------
    // METHODES
    // -----------------------
    // Les méthodes nécessaires sont fournies par ce trait Symfony.
    use MicroKernelTrait;
}
