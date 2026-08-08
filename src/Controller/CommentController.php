<?php

/*
 * Présentation : contrôleur chargé de la publication des commentaires.
 * Rôle : valider la saisie, associer l'auteur et l'article, puis enregistrer avec Doctrine.
 */

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Form\CommentFormType;
use App\Repository\ArticleLikeRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CommentController extends AbstractController
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------
    // Aucun attribut : les dépendances sont injectées dans les méthodes.

    // -----------------------
    // METHODES
    // -----------------------

    #[Route(
        '/articles/{id}/commentaires',
        name: 'app_comment_create',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    #[IsGranted('ROLE_USER')]
    /**
     * Rôle : Valide et publie un commentaire sur un article publié.
     * Paramètre : `$id` (int) : l’identifiant de la ressource demandée ; `$request` (Request) : la requête HTTP et les données envoyées ; `$articleRepository` (ArticleRepository) : le repository utilisé pour interroger les articles ; `$likeRepository` (ArticleLikeRepository) : le repository utilisé pour interroger les J’aime ; `$entityManager` (EntityManagerInterface) : le gestionnaire Doctrine chargé de la persistance.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
    public function create(
        int $id,
        Request $request,
        ArticleRepository $articleRepository,
        ArticleLikeRepository $likeRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $article = $articleRepository->findOneBy([
            'id' => $id,
            'isPublished' => true,
        ]);

        if ($article === null) {
            throw $this->createNotFoundException(
                'Article introuvable.'
            );
        }

        $comment = new Comment();

        $form = $this->createForm(
            CommentFormType::class,
            $comment
        );

        $form->handleRequest($request);

        /** @var User $user */
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            // L'auteur et l'article ne viennent pas du formulaire : ils sont
            // imposés côté serveur pour empêcher leur falsification.
            $comment->setArticle($article);
            $comment->setAuthor($user);

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Votre commentaire a bien été publié.'
            );

            return $this->redirectToRoute(
                'app_article_show',
                ['id' => $article->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        // En cas d'erreur, on réaffiche la vue avec le même objet Form. Symfony
        // peut ainsi restituer le texte saisi et les messages de validation.
        $hasLiked = $likeRepository->count([
            'user' => $user,
            'article' => $article,
        ]) > 0;

        return $this->render(
            'article/show.html.twig',
            [
                'article' => $article,
                'commentForm' => $form->createView(),
                'hasLiked' => $hasLiked,
            ]
        );
    }
}
