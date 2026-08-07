<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    '/admin/utilisateurs',
    name: 'app_admin_user_'
)]
#[IsGranted('ROLE_ADMIN')]
final class AdminUserController extends AbstractController
{
    #[Route(
        '',
        name: 'index',
        methods: ['GET']
    )]
    public function index(
        Request $request,
        UserRepository $userRepository
    ): Response {
        $search = $request
            ->query
            ->getString('recherche');

        $users = $userRepository->findForAdmin(
            $search
        );

        return $this->render(
            'admin_user/index.html.twig',
            [
                'users' => $users,
                'search' => $search,
            ]
        );
    }

    #[Route(
        '/{id}/bannissement',
        name: 'toggle_ban',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    public function toggleBan(
        Request $request,
        User $targetUser,
        EntityManagerInterface $entityManager
    ): Response {
        $tokenIsValid = $this->isCsrfTokenValid(
            'toggle-ban'.$targetUser->getId(),
            $request->getPayload()->getString('_token')
        );

        if (!$tokenIsValid) {
            $this->addFlash(
                'error',
                'La demande de bannissement est invalide.'
            );

            return $this->redirectToRoute(
                'app_admin_user_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        if ($this->isProtectedAccount($targetUser)) {
            $this->addFlash(
                'error',
                'Un compte administrateur ne peut pas être banni.'
            );

            return $this->redirectToRoute(
                'app_admin_user_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        $targetUser->setIsBanned(
            !$targetUser->isBanned()
        );

        $entityManager->flush();

        $message = $targetUser->isBanned()
            ? 'Le compte a bien été banni.'
            : 'Le compte a bien été réactivé.';

        $this->addFlash('success', $message);

        return $this->redirectToRoute(
            'app_admin_user_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }

    #[Route(
        '/{id}',
        name: 'delete',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    public function delete(
        Request $request,
        User $targetUser,
        EntityManagerInterface $entityManager
    ): Response {
        $tokenIsValid = $this->isCsrfTokenValid(
            'delete-user'.$targetUser->getId(),
            $request->getPayload()->getString('_token')
        );

        if (!$tokenIsValid) {
            $this->addFlash(
                'error',
                'La demande de suppression est invalide.'
            );

            return $this->redirectToRoute(
                'app_admin_user_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        if ($this->isProtectedAccount($targetUser)) {
            $this->addFlash(
                'error',
                'Un compte administrateur ne peut pas être supprimé.'
            );

            return $this->redirectToRoute(
                'app_admin_user_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        $entityManager->remove($targetUser);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Le compte utilisateur a bien été supprimé.'
        );

        return $this->redirectToRoute(
            'app_admin_user_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }

    private function isProtectedAccount(
        User $user
    ): bool {
        return in_array(
            'ROLE_ADMIN',
            $user->getRoles(),
            true
        );
    }
}
