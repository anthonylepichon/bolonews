<?php

namespace App\Controller;

use App\Entity\ArticleLike;
use App\Entity\User;
use App\Repository\ArticleLikeRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class LikeController extends AbstractController
{
    #[Route(
        '/articles/{id}/like',
        name: 'app_like_toggle',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    #[IsGranted('ROLE_USER')]
    public function toggle(
        int $id,
        Request $request,
        ArticleRepository $articleRepository,
        ArticleLikeRepository $likeRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $article = $articleRepository->findOneBy([
            'id' => $id,
            'isPublished' => true,
        ]);

        if ($article === null) {
            return $this->json(
                ['error' => 'Article introuvable.'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $tokenIsValid = $this->isCsrfTokenValid(
            'like-article'.$article->getId(),
            $request->getPayload()->getString('_token')
        );

        if (!$tokenIsValid) {
            return $this->json(
                ['error' => 'Jeton de sécurité invalide.'],
                JsonResponse::HTTP_FORBIDDEN
            );
        }

        /** @var User $user */
        $user = $this->getUser();

        try {
            $existingLike = $likeRepository->findOneBy([
                'user' => $user,
                'article' => $article,
            ]);

            if ($existingLike === null) {
                $like = new ArticleLike();
                $like->setUser($user);
                $like->setArticle($article);

                $entityManager->persist($like);

                $liked = true;
            } else {
                $entityManager->remove($existingLike);

                $liked = false;
            }

            $entityManager->flush();

            $likeCount = $likeRepository->count([
                'article' => $article,
            ]);
        } catch (\Throwable) {
            // Aucun détail technique n’est exposé dans la réponse JSON.
            return $this->json(
                ['error' => 'Une erreur est survenue.'],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->json([
            'liked' => $liked,
            'likeCount' => $likeCount,
        ]);
    }
}
