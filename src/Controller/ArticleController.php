<?php

/*
 * Présentation : contrôleur principal du cycle de vie des articles.
 * Rôle : lister, filtrer, afficher, créer, modifier, publier et supprimer selon les droits.
 */

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
    // -----------------------
    // ATTRIBUTS
    // -----------------------
    // Aucun attribut : les dépendances sont injectées dans les méthodes.

    // -----------------------
    // METHODES
    // -----------------------

    #[Route(
        '/articles',
        name: 'app_article_index',
        methods: ['GET']
    )]
    /**
     * Rôle : Affiche les articles publiés selon les filtres, en page complète ou en AJAX.
     * Paramètre : `$request` (Request) : la requête HTTP et les données envoyées ; `$articleRepository` (ArticleRepository) : le repository utilisé pour interroger les articles ; `$categoryRepository` (CategoryRepository) : le repository utilisé pour interroger les catégories.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
    public function index(
        Request $request,
        ArticleRepository $articleRepository,
        CategoryRepository $categoryRepository
    ): Response {
        // Les filtres arrivent dans l'URL sous forme de paramètres GET afin que
        // la recherche reste partageable et fonctionne même sans JavaScript.
        $search = $request
            ->query
            ->getString('recherche');

        $categoryId = $request
            ->query
            ->getInt('categorie');

        if ($categoryId === 0) {
            // getInt() renvoie 0 lorsque le paramètre est absent : le repository
            // attend null pour comprendre qu'aucune catégorie n'est sélectionnée.
            $categoryId = null;
        }

        $articles = $articleRepository
            ->findPublishedByFilters(
                $search,
                $categoryId
            );

        if ($request->isXmlHttpRequest()) {
            // Une requête AJAX ne remplace que la liste des résultats. La page
            // complète reste rendue lors d'une navigation HTML classique.
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
    /**
     * Rôle : Affiche et traite la création d’un article ou d’un brouillon.
     * Paramètre : `$request` (Request) : la requête HTTP et les données envoyées ; `$entityManager` (EntityManagerInterface) : le gestionnaire Doctrine chargé de la persistance ; `$slugger` (SluggerInterface) : le service qui sécurise les noms de fichiers.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
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
            // Le champ image n'est pas mappé sur l'entité : le contrôleur traite
            // le fichier puis conserve uniquement son nom dans la base de données.
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
                // Les deux boutons partagent le formulaire, mais leur valeur
                // décide entre l'enregistrement d'un brouillon et la publication.
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
    /**
     * Rôle : Affiche et traite la modification et l’état de publication d’un article.
     * Paramètre : `$id` (int) : l’identifiant de la ressource demandée ; `$request` (Request) : la requête HTTP et les données envoyées ; `$articleRepository` (ArticleRepository) : le repository utilisé pour interroger les articles ; `$entityManager` (EntityManagerInterface) : le gestionnaire Doctrine chargé de la persistance ; `$slugger` (SluggerInterface) : le service qui sécurise les noms de fichiers.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
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

        // Un auteur ne modifie que ses articles ; le rôle administrateur garde
        // un droit de modération sur tous les articles.
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
                // En modification, l'image actuelle peut être conservée.
                'image_required' => false,
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile instanceof UploadedFile) {
                // Le nom enregistré n'est remplacé que si un nouveau fichier a
                // réellement été choisi par l'utilisateur.
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
    /**
     * Rôle : Affiche un article et prépare ses interactions selon le visiteur.
     * Paramètre : `$id` (int) : l’identifiant de la ressource demandée ; `$articleRepository` (ArticleRepository) : le repository utilisé pour interroger les articles ; `$likeRepository` (ArticleLikeRepository) : le repository utilisé pour interroger les J’aime.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
    public function show(
        int $id,
        ArticleRepository $articleRepository,
        ArticleLikeRepository $likeRepository
    ): Response {
        $article = $articleRepository->find($id);

        if ($article === null) {
            throw $this->createNotFoundException(
                'Article introuvable.'
            );
        }

        $user = $this->getUser();
        $canEdit = $user instanceof User
            && (
                $article->getAuthor()?->getId() === $user->getId()
                || $this->isGranted('ROLE_ADMIN')
            );

        // Répondre « introuvable » évite de révéler l'existence d'un brouillon
        // à une personne qui n'est ni son auteur ni administratrice.
        if (!$article->isPublished() && !$canEdit) {
            throw $this->createNotFoundException(
                'Article introuvable.'
            );
        }

        $hasLiked = false;
        $commentForm = null;

        if ($article->isPublished() && $user instanceof User) {
            // Ces données interactives ne sont préparées que pour un membre
            // connecté et uniquement sur un article effectivement publié.
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
                'canEdit' => $canEdit,
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
    /**
     * Rôle : Supprime définitivement un article après contrôle du rôle et du jeton CSRF.
     * Paramètre : `$request` (Request) : la requête HTTP et les données envoyées ; `$article` (Article) : l’article concerné par l’action ; `$entityManager` (EntityManagerInterface) : le gestionnaire Doctrine chargé de la persistance.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
    public function delete(
        Request $request,
        Article $article,
        EntityManagerInterface $entityManager
    ): Response {
        // Le rôle est vérifié par IsGranted ; le jeton CSRF protège en plus la
        // requête POST contre une suppression déclenchée depuis un autre site.
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
        // Le nom fourni par le navigateur est nettoyé, puis rendu unique pour
        // éviter les caractères dangereux et l'écrasement d'une image existante.
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
