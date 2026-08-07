<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    '/admin/categories',
    name: 'app_admin_category_'
)]
#[IsGranted('ROLE_ADMIN')]
final class AdminCategoryController extends AbstractController
{
    #[Route(
        '',
        name: 'index',
        methods: ['GET']
    )]
    public function index(
        CategoryRepository $categoryRepository
    ): Response {
        $categories = $categoryRepository->findBy(
            [],
            ['label' => 'ASC']
        );

        return $this->render(
            'admin_category/index.html.twig',
            [
                'categories' => $categories,
            ]
        );
    }

    #[Route(
        '/nouvelle',
        name: 'new',
        methods: ['GET', 'POST']
    )]
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
    public function edit(
        Request $request,
        Category $category,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(
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
