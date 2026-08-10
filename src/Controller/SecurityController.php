<?php

/*
 * Présentation : contrôleur des pages d'authentification.
 * Rôle : fournir la vue de connexion ; Symfony intercepte la vérification et la déconnexion.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------
    // Aucun attribut : l'état de connexion est géré par le composant Security.

    // -----------------------
    // METHODES
    // -----------------------

    #[Route(path: '/login', name: 'app_login', methods: ['GET', 'POST'])]
    /**
     * Rôle : Affiche la connexion et les informations de la dernière tentative.
     * Paramètre : `$authenticationUtils` (AuthenticationUtils) : le service donnant accès à la dernière tentative de connexion.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout', methods: ['GET'])]
    /**
     * Rôle : Déclare la route interceptée par le firewall pour fermer la session.
     * Paramètre : Aucun.
     * Retour : Aucun (`void`).
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');

    }
}
