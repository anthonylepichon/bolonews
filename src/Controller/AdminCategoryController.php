<?php

/*
 * Présentation : contrôleur d'administration des catégories d'articles.
 * Rôle : créer, renommer et supprimer une catégorie tout en protégeant ses relations.
 */

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    '/admin/categories',
    name: 'app_admin_category_'
)]
#[IsGranted('ROLE_ADMIN')]
final class AdminCategoryController extends AbstractController
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------
    // Aucun attribut : les dépendances sont injectées dans les méthodes.

    // -----------------------
    // METHODES
    // -----------------------

    #[Route(
        '',
        name: 'index',
        methods: ['GET', 'POST']
    )]
    /**
     * Rôle : Affiche les catégories et traite leur ajout rapide.
     * Paramètre : `$request` (Request) : la requête HTTP et les données envoyées ; `$categoryRepository` (CategoryRepository) : le repository utilisé pour interroger les catégories ; `$entityManager` (EntityManagerInterface) : le gestionnaire Doctrine chargé de la persistance ; `$formFactory` (FormFactoryInterface) : la fabrique Symfony utilisée pour nommer les formulaires.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
    public function index(
        Request $request,
        CategoryRepository $categoryRepository,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory
    ): Response {
        $newCategory = new Category();

        // createNamed() donne un nom HTML distinct au formulaire d'ajout. Cela
        // évite une collision avec les formulaires de modification de la page.
        $newCategoryForm = $formFactory->createNamed(
            'new_category',
            CategoryType::class,
            $newCategory,
            [
                'action' => $this->generateUrl(
                    'app_admin_category_index'
                ),
            ]
        );

        $newCategoryForm->handleRequest($request);

        if (
            $newCategoryForm->isSubmitted()
            && $newCategoryForm->isValid()
        ) {
            $entityManager->persist($newCategory);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'La catégorie a bien été ajoutée.'
            );

            // La redirection après le POST applique le schéma PRG : actualiser
            // la page ne soumet pas une seconde fois la même catégorie.
            return $this->redirectToRoute(
                'app_admin_category_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        $categories = $categoryRepository->findBy(
            [],
            ['label' => 'ASC']
        );

        $editForms = [];

        // Chaque ligne reçoit un formulaire au nom unique. Twig et JavaScript
        // peuvent ainsi ouvrir et envoyer uniquement la catégorie concernée.
        foreach ($categories as $category) {
            $formName = 'category_'.$category->getId();

            $editForms[$category->getId()] = $formFactory
                ->createNamed(
                    $formName,
                    CategoryType::class,
                    $category,
                    [
                        'action' => $this->generateUrl(
                            'app_admin_category_edit',
                            ['id' => $category->getId()]
                        ),
                    ]
                )
                ->createView();
        }

        return $this->render(
            'admin_category/index.html.twig',
            [
                'categories' => $categories,
                'newCategoryForm' => $newCategoryForm,
                'editForms' => $editForms,
            ]
        );
    }

    #[Route(
        '/nouvelle',
        name: 'new',
        methods: ['GET', 'POST']
    )]
    /**
     * Rôle : Affiche et traite la page dédiée à la création d’une catégorie.
     * Paramètre : `$request` (Request) : la requête HTTP et les données envoyées ; `$entityManager` (EntityManagerInterface) : le gestionnaire Doctrine chargé de la persistance.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $category = new Category();

        $form = $this->createForm(
            CategoryType::class,
            $category
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'La catégorie a bien été ajoutée.'
            );

            return $this->redirectToRoute(
                'app_admin_category_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render(
            'admin_category/new.html.twig',
            [
                'category' => $category,
                'form' => $form,
            ]
        );
    }

    #[Route(
        '/{id}/modifier',
        name: 'edit',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST']
    )]
    /**
     * Rôle : Affiche et traite la modification d’une catégorie.
     * Paramètre : `$request` (Request) : la requête HTTP et les données envoyées ; `$category` (Category) : la catégorie concernée par l’action ; `$entityManager` (EntityManagerInterface) : le gestionnaire Doctrine chargé de la persistance ; `$formFactory` (FormFactoryInterface) : la fabrique Symfony utilisée pour nommer les formulaires.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
    public function edit(
        Request $request,
        Category $category,
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory
    ): Response {
        // Le nom doit être identique à celui construit dans index() pour que
        // Symfony retrouve correctement les données du formulaire soumis.
        $form = $formFactory->createNamed(
            'category_'.$category->getId(),
            CategoryType::class,
            $category
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'success',
                'La catégorie a bien été modifiée.'
            );

            return $this->redirectToRoute(
                'app_admin_category_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render(
            'admin_category/edit.html.twig',
            [
                'category' => $category,
                'form' => $form,
            ]
        );
    }

    #[Route(
        '/{id}',
        name: 'delete',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    /**
     * Rôle : Supprime une catégorie inutilisée après les contrôles de sécurité.
     * Paramètre : `$request` (Request) : la requête HTTP et les données envoyées ; `$category` (Category) : la catégorie concernée par l’action ; `$entityManager` (EntityManagerInterface) : le gestionnaire Doctrine chargé de la persistance.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
    public function delete(
        Request $request,
        Category $category,
        EntityManagerInterface $entityManager
    ): Response {
        $tokenIsValid = $this->isCsrfTokenValid(
            'delete'.$category->getId(),
            $request->getPayload()->getString('_token')
        );

        if (!$tokenIsValid) {
            $this->addFlash(
                'error',
                'La demande de suppression est invalide.'
            );

            return $this->redirectToRoute(
                'app_admin_category_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        // Une catégorie encore reliée à des articles n'est pas supprimée : la
        // règle protège l'intégrité référentielle définie dans le MPD.
        if (!$category->getArticles()->isEmpty()) {
            $this->addFlash(
                'error',
                'Cette catégorie est utilisée par un ou plusieurs articles.'
            );

            return $this->redirectToRoute(
                'app_admin_category_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        $entityManager->remove($category);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'La catégorie a bien été supprimée.'
        );

        return $this->redirectToRoute(
            'app_admin_category_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }
}
