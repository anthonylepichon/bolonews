<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileFormType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_USER')]
final class ProfileController extends AbstractController
{
    #[Route(
        '/mon-espace',
        name: 'app_profile_index',
        methods: ['GET']
    )]
    public function index(
        ArticleRepository $articleRepository
    ): Response {
        $user = $this->getUser();

        $articles = $articleRepository->findBy(
            ['author' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'articles' => $articles,
        ]);
    }

    #[Route(
        '/mon-espace/modifier',
        name: 'app_profile_edit',
        methods: ['GET', 'POST']
    )]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(
            ProfileFormType::class,
            $user
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $avatarFile */
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile !== null) {
                $originalFilename = pathinfo(
                    $avatarFile->getClientOriginalName(),
                    PATHINFO_FILENAME
                );

                $safeFilename = $slugger
                    ->slug($originalFilename)
                    ->lower();

                $avatarFilename = sprintf(
                    '%s-%s.%s',
                    $safeFilename,
                    uniqid(),
                    $avatarFile->guessExtension()
                );

                try {
                    $avatarFile->move(
                        (string) $this->getParameter(
                            'avatars_directory'
                        ),
                        $avatarFilename
                    );
                } catch (FileException) {
                    $form->get('avatar')->addError(
                        new FormError(
                            'L’image de profil n’a pas pu être enregistrée.'
                        )
                    );

                    return $this->render(
                        'profile/edit.html.twig',
                        [
                            'profileForm' => $form,
                            'user' => $user,
                        ]
                    );
                }

                $user->setAvatarFilename($avatarFilename);
            }

            $newPassword = $form
                ->get('newPassword')
                ->getData();

            if (
                is_string($newPassword)
                && $newPassword !== ''
            ) {
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $newPassword
                    )
                );
            }

            // L’utilisateur existe déjà : persist() n’est pas nécessaire.
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Votre profil a bien été modifié.'
            );

            return $this->redirectToRoute(
                'app_profile_index'
            );
        }

        return $this->render(
            'profile/edit.html.twig',
            [
                'profileForm' => $form,
                'user' => $user,
            ]
        );
    }
}
