<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    #[Route(
        '/register',
        name: 'app_register',
        methods: ['GET', 'POST']
    )]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        Security $security,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $user = new User();

        $form = $this->createForm(
            RegistrationFormType::class,
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
                    $this->addFlash(
                        'error',
                        'L’image de profil n’a pas pu être enregistrée.'
                    );

                    return $this->render(
                        'registration/register.html.twig',
                        [
                            'registrationForm' => $form,
                        ]
                    );
                }

                $user->setAvatarFilename($avatarFilename);
            }

            /** @var string $plainPassword */
            $plainPassword = $form
                ->get('plainPassword')
                ->getData();

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plainPassword
            );

            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Votre compte a bien été créé.'
            );

            return $security->login(
                $user,
                'form_login',
                'main'
            );
        }

        return $this->render(
            'registration/register.html.twig',
            [
                'registrationForm' => $form,
            ]
        );
    }
}
