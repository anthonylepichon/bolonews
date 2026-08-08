<?php

/*
 * Présentation : contrôleur de la page d'accueil publique.
 * Rôle : sélectionner les dernières publications et les transmettre à Twig.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ArticleRepository;

final class HomeController extends AbstractController
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------
    // Aucun attribut : les dépendances sont injectées dans les méthodes.

    // -----------------------
    // METHODES
    // -----------------------

    #[Route('/', name: 'app_home', methods: ['GET'])]
    /**
     * Rôle : Prépare et affiche la page d’accueil avec les dernières publications.
     * Paramètre : `$articleRepository` (ArticleRepository) : le repository utilisé pour interroger les articles.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
    public function index(ArticleRepository $articleRepository): Response
    {
        // Cette sélection alimente une mise en page précise : le premier résultat
        // devient l'article à la une et les quatre suivants deviennent des cartes.
        // La condition exclut les brouillons de la page publique.
        $articles = $articleRepository->findBy(
            ['isPublished' => true],
            ['createdAt' => 'DESC'],
            5
        );

        return $this->render('home/index.html.twig', [
            'articles' => $articles,
        ]);
    }
}
