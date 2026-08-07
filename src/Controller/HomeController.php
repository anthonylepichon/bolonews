<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ArticleRepository;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        // Recherche les cinq articles publiés les plus récents.
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
