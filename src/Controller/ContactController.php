<?php

/*
 * Présentation : contrôleur de la page de contact prévue dans la maquette.
 * Rôle : afficher uniquement la vue, le traitement du message étant hors périmètre initial.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------
    // Aucun attribut : ce contrôleur affiche uniquement un template.

    // -----------------------
    // METHODES
    // -----------------------

    #[Route('/contact', name: 'app_contact', methods: ['GET'])]
    /**
     * Rôle : Affiche la vue de contact sans traitement serveur.
     * Paramètre : Aucun.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
    public function index(): Response
    {
        return $this->render('contact/index.html.twig');
    }
}
