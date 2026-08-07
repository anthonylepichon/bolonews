<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(
        UserInterface $user
    ): void {
        // Aucun contrôle avant la vérification du mot de passe.
    }

    public function checkPostAuth(
        UserInterface $user
    ): void {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isBanned()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte a été suspendu.'
            );
        }
    }
}
