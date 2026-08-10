<?php

/*
 * Présentation : contrôleur principal de l’espace d’administration.
 * Rôle : afficher les articles personnels de l’administrateur et gérer la modération de tous les articles.
 */

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------
    // Aucun attribut : les dépendances sont injectées dans les méthodes.

    // -----------------------
    // METHODES
    // -----------------------

    #[Route(
        '/admin',
        name: 'app_admin',
        methods: ['GET']
    )]
    /**
     * Rôle : Affiche les publications et les brouillons appartenant uniquement à l’administrateur connecté.
     * Paramètre : `$articleRepository` (ArticleRepository) : le repository utilisé pour interroger les articles.
     * Retour : Une réponse HTTP contenant le tableau de bord personnel de l’administrateur.
     */
    public function index(
        ArticleRepository $articleRepository
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException(
                'Vous devez être connecté pour accéder à cet espace.'
            );
        }

        // Le filtre author sépare le tableau de bord personnel de la page de
        // modération globale : l’administrateur ne voit ici que ses articles.
        $articles = $articleRepository->findBy(
            ['author' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('admin/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route(
        '/admin/articles',
        name: 'app_admin_article_index',
        methods: ['GET']
    )]
    /**
     * Rôle : Affiche tous les articles afin que l’administrateur puisse les modérer.
     * Paramètre : `$articleRepository` (ArticleRepository) : le repository utilisé pour interroger les articles.
     * Retour : Une réponse HTTP contenant le tableau de gestion de tous les articles.
     */
    public function articles(
        ArticleRepository $articleRepository
    ): Response {
        // Contrairement au tableau de bord, aucun auteur ni état de publication
        // n’est filtré : cette page constitue la vue globale de modération.
        $articles = $articleRepository->findBy(
            [],
            ['createdAt' => 'DESC']
        );

        return $this->render('admin/articles.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route(
        '/admin/articles/{id}/depublier',
        name: 'app_admin_article_unpublish',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    /**
     * Rôle : Transforme un article publié en brouillon après vérification du jeton CSRF.
     * Paramètre : `$request` (Request) : la requête contenant le jeton CSRF ; `$article` (Article) : l’article à dépublier ; `$entityManager` (EntityManagerInterface) : le gestionnaire Doctrine chargé de la persistance.
     * Retour : Une redirection vers la liste de gestion des articles.
     */
    public function unpublish(
        Request $request,
        Article $article,
        EntityManagerInterface $entityManager
    ): Response {
        $tokenIsValid = $this->isCsrfTokenValid(
            'unpublish-article'.$article->getId(),
            $request->getPayload()->getString('_token')
        );

        if (!$tokenIsValid) {
            $this->addFlash(
                'error',
                'La demande de dépublication est invalide.'
            );

            return $this->redirectToRoute(
                'app_admin_article_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        if (!$article->isPublished()) {
            $this->addFlash(
                'info',
                'Cet article est déjà enregistré comme brouillon.'
            );

            return $this->redirectToRoute(
                'app_admin_article_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        $article->setIsPublished(false);
        $article->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        $this->addFlash(
            'success',
            'L’article a bien été dépublié.'
        );

        return $this->redirectToRoute(
            'app_admin_article_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }

}
