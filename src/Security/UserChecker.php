<?php

/*
 * Présentation : contrôle métier complémentaire à l'authentification Symfony.
 * Rôle : empêcher un compte banni d'ouvrir une session malgré des identifiants valides.
 */

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserChecker implements UserCheckerInterface
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------
    // Aucun attribut : la vérification utilise directement l'utilisateur reçu.

    // -----------------------
    // METHODES
    // -----------------------

    /**
     * Rôle : Exécute les contrôles de compte prévus avant l’authentification.
     * Paramètre : `$user` (UserInterface) : le compte utilisateur concerné.
     * Retour : Aucun (`void`).
     */
    public function checkPreAuth(
        UserInterface $user
    ): void {
        // Aucun contrôle avant la vérification du mot de passe.
    }

    /**
     * Rôle : Refuse la connexion lorsqu’un compte authentifié est banni.
     * Paramètre : `$user` (UserInterface) : le compte utilisateur concerné.
     * Retour : Aucun (`void`).
     */
    public function checkPostAuth(
        UserInterface $user
    ): void {
        if (!$user instanceof User) {
            return;
        }

        // Cette règle métier complète l'authentification Symfony : des
        // identifiants valides ne suffisent pas si le compte est suspendu.
        if ($user->isBanned()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte a été suspendu.'
            );
        }
    }
}
