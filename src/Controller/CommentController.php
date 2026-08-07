<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Form\CommentFormType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CommentController extends AbstractController
{
    #[Route(
        '/articles/{id}/commentaires',
        name: 'app_comment_create',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    #[IsGranted('ROLE_USER')]
    public function create(
        int $id,
        Request $request,
        ArticleRepository $articleRepository,
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

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();

            $comment->setArticle($article);
            $comment->setAuthor($user);

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Votre commentaire a bien été publié.'
            );
        } else {
            $this->addFlash(
                'error',
                'Le commentaire n’a pas pu être publié.'
            );
        }

        return $this->redirectToRoute(
            'app_article_show',
            ['id' => $article->getId()],
            Response::HTTP_SEE_OTHER
        );
    }
}
