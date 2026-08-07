<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use App\Form\ArticleFormType;
use App\Form\CommentFormType;
use App\Repository\ArticleLikeRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\CategoryRepository;

final class ArticleController extends AbstractController
{
    #[Route(
        '/articles',
        name: 'app_article_index',
        methods: ['GET']
    )]
    public function index(
        Request $request,
        ArticleRepository $articleRepository,
        CategoryRepository $categoryRepository
    ): Response {
        $search = $request
            ->query
            ->getString('recherche');

        $categoryId = $request
            ->query
            ->getInt('categorie');

        if ($categoryId === 0) {
            $categoryId = null;
        }

        $articles = $articleRepository
            ->findPublishedByFilters(
                $search,
                $categoryId
            );

        if ($request->isXmlHttpRequest()) {
            return $this->render(
                'article/_list.html.twig',
                [
                    'articles' => $articles,
                ]
            );
        }

        $categories = $categoryRepository->findBy(
            [],
            ['label' => 'ASC']
        );

        return $this->render(
            'article/index.html.twig',
            [
                'articles' => $articles,
                'categories' => $categories,
                'search' => $search,
                'selectedCategoryId' => $categoryId,
            ]
        );
    }

    #[Route(
        '/articles/nouveau',
        name: 'app_article_new',
        methods: ['GET', 'POST']
    )]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $article = new Article();

        $form = $this->createForm(
            ArticleFormType::class,
            $article
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('image')->getData();

            if (!$imageFile instanceof UploadedFile) {
                $form->get('image')->addError(
                    new FormError(
                        'Veuillez sélectionner une image.'
                    )
                );

                return $this->render(
                    'article/form.html.twig',
                    [
                        'articleForm' => $form,
                        'article' => $article,
                    ]
                );
            }

            try {
                $imageFilename = $this->uploadArticleImage(
                    $imageFile,
                    $slugger
                );
            } catch (FileException) {
                $form->get('image')->addError(
                    new FormError(
                        'L’image de l’article n’a pas pu être enregistrée.'
                    )
                );

                return $this->render(
                    'article/form.html.twig',
                    [
                        'articleForm' => $form,
                        'article' => $article,
                    ]
                );
            }

            /** @var User $user */
            $user = $this->getUser();

            $article->setAuthor($user);
            $article->setImageFilename($imageFilename);

            $publicationAction = $request
                ->request
                ->get('publication_action');

            $article->setIsPublished(
                $publicationAction === 'publish'
            );

            $entityManager->persist($article);
            $entityManager->flush();

            $message = $article->isPublished()
                ? 'L’article a bien été publié.'
                : 'Le brouillon a bien été enregistré.';

            $this->addFlash('success', $message);

            return $this->redirectToRoute(
                'app_profile_index'
            );
        }

        return $this->render(
            'article/form.html.twig',
            [
                'articleForm' => $form,
                'article' => $article,
            ]
        );
    }

    #[Route(
        '/articles/{id}/modifier',
        name: 'app_article_edit',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST']
    )]
    #[IsGranted('ROLE_USER')]
    public function edit(
        int $id,
        Request $request,
        ArticleRepository $articleRepository,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $article = $articleRepository->find($id);

        if ($article === null) {
            throw $this->createNotFoundException(
                'Article introuvable.'
            );
        }

        /** @var User $user */
        $user = $this->getUser();

        $isAuthor = $article->getAuthor()?->getId()
            === $user->getId();

        if (
            !$isAuthor
            && !$this->isGranted('ROLE_ADMIN')
        ) {
            throw $this->createAccessDeniedException(
                'Vous ne pouvez pas modifier cet article.'
            );
        }

        $form = $this->createForm(
            ArticleFormType::class,
            $article,
            [
                // L’image actuelle peut être conservée.
                'image_required' => false,
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile instanceof UploadedFile) {
                try {
                    $imageFilename =
                        $this->uploadArticleImage(
                            $imageFile,
                            $slugger
                        );
                } catch (FileException) {
                    $form->get('image')->addError(
                        new FormError(
                            'L’image de l’article n’a pas pu être enregistrée.'
                        )
                    );

                    return $this->render(
                        'article/form.html.twig',
                        [
                            'articleForm' => $form,
                            'article' => $article,
                        ]
                    );
                }

                $article->setImageFilename(
                    $imageFilename
                );
            }

            $publicationAction = $request
                ->request
                ->get('publication_action');

            if ($publicationAction === 'publish') {
                $article->setIsPublished(true);
            }

            if ($publicationAction === 'unpublish') {
                $article->setIsPublished(false);
            }

            $article->setUpdatedAt(
                new \DateTimeImmutable()
            );

            $entityManager->flush();

            $message = match ($publicationAction) {
                'publish' =>
                    'L’article a bien été publié.',

                'unpublish' =>
                    'L’article a bien été dépublié.',

                default =>
                    'Les modifications ont bien été enregistrées.',
            };

            $this->addFlash('success', $message);

            return $this->redirectToRoute(
                'app_profile_index'
            );
        }

        return $this->render(
            'article/form.html.twig',
            [
                'articleForm' => $form,
                'article' => $article,
            ]
        );
    }

    #[Route(
        '/articles/{id}',
        name: 'app_article_show',
        requirements: ['id' => '\d+'],
        methods: ['GET']
    )]
    public function show(
        int $id,
        ArticleRepository $articleRepository,
        ArticleLikeRepository $likeRepository
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

        $user = $this->getUser();
        $hasLiked = false;
        $commentForm = null;

        if ($user instanceof User) {
            $hasLiked = $likeRepository->count([
                'user' => $user,
                'article' => $article,
            ]) > 0;

            $comment = new Comment();

            $commentForm = $this->createForm(
                CommentFormType::class,
                $comment,
                [
                    'action' => $this->generateUrl(
                        'app_comment_create',
                        ['id' => $article->getId()]
                    ),
                    'method' => 'POST',
                ]
            )->createView();
        }

        return $this->render(
            'article/show.html.twig',
            [
                'article' => $article,
                'commentForm' => $commentForm,
                'hasLiked' => $hasLiked,
            ]
        );
    }

    #[Route(
        '/articles/{id}/supprimer',
        name: 'app_article_delete',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        Request $request,
        Article $article,
        EntityManagerInterface $entityManager
    ): Response {
        $tokenIsValid = $this->isCsrfTokenValid(
            'delete-article'.$article->getId(),
            $request->getPayload()->getString(
                '_token'
            )
        );

        if (!$tokenIsValid) {
            $this->addFlash(
                'error',
                'La demande de suppression est invalide.'
            );

            return $this->redirectToRoute(
                'app_admin',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        $entityManager->remove($article);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'L’article a bien été supprimé.'
        );

        return $this->redirectToRoute(
            'app_admin',
            [],
            Response::HTTP_SEE_OTHER
        );
    }

    private function uploadArticleImage(
        UploadedFile $imageFile,
        SluggerInterface $slugger
    ): string {
        $originalFilename = pathinfo(
            $imageFile->getClientOriginalName(),
            PATHINFO_FILENAME
        );

        $safeFilename = $slugger
            ->slug($originalFilename)
            ->lower();

        $imageFilename = sprintf(
            '%s-%s.%s',
            $safeFilename,
            uniqid(),
            $imageFile->guessExtension()
        );

        $imageFile->move(
            (string) $this->getParameter(
                'articles_directory'
            ),
            $imageFilename
        );

        return $imageFilename;
    }
}
