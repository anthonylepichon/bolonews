<?php

/*
 * Présentation : tableau de bord général de l'administration.
 * Rôle : réunir publications et brouillons afin de permettre leur modération.
 */

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * Rôle : Affiche le tableau de bord de modération des articles.
     * Paramètre : `$articleRepository` (ArticleRepository) : le repository utilisé pour interroger les articles.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
    public function index(
        ArticleRepository $articleRepository
    ): Response {
        // Cette requête ne filtre pas isPublished : l'administrateur doit voir
        // les brouillons comme les publications pour pouvoir les modérer.
        $articles = $articleRepository->findBy(
            [],
            ['createdAt' => 'DESC']
        );

        return $this->render('admin/index.html.twig', [
            'articles' => $articles,
        ]);
    }
}
