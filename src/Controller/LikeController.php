<?php

/*
 * Présentation : point d'entrée AJAX du bouton « J'aime ».
 * Rôle : ajouter ou retirer la relation utilisateur-article et renvoyer son état en JSON.
 */

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
    // -----------------------
    // ATTRIBUTS
    // -----------------------
    // Aucun attribut : les dépendances sont injectées dans les méthodes.

    // -----------------------
    // METHODES
    // -----------------------

    #[Route(
        '/articles/{id}/like',
        name: 'app_like_toggle',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    #[IsGranted('ROLE_USER')]
    /**
     * Rôle : Ajoute ou retire un J’aime et renvoie le nouvel état en JSON.
     * Paramètre : `$id` (int) : l’identifiant de la ressource demandée ; `$request` (Request) : la requête HTTP et les données envoyées ; `$articleRepository` (ArticleRepository) : le repository utilisé pour interroger les articles ; `$likeRepository` (ArticleLikeRepository) : le repository utilisé pour interroger les J’aime ; `$entityManager` (EntityManagerInterface) : le gestionnaire Doctrine chargé de la persistance.
     * Retour : Une réponse JSON destinée au JavaScript de la page.
     */
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
            // Une seule route réalise les deux actions : l'absence de relation
            // crée le like, sa présence le retire de la table d'association.
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
            // Aucun détail technique n'est exposé dans la réponse JSON : il
            // pourrait révéler la structure interne de l'application.
            return $this->json(
                ['error' => 'Une erreur est survenue.'],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // Le JavaScript met à jour l'icône et le compteur sans recharger la page.
        return $this->json([
            'liked' => $liked,
            'likeCount' => $likeCount,
        ]);
    }
}
