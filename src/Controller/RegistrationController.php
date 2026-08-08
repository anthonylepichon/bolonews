<?php

/*
 * Présentation : contrôleur de création d'un compte Bolonews.
 * Rôle : traiter l'inscription, l'avatar et le hachage du mot de passe avant persistance.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------
    // Aucun attribut : les dépendances sont injectées dans les méthodes.

    // -----------------------
    // METHODES
    // -----------------------

    #[Route(
        '/register',
        name: 'app_register',
        methods: ['GET', 'POST']
    )]
    /**
     * Rôle : Affiche et traite la création d’un compte utilisateur.
     * Paramètre : `$request` (Request) : la requête HTTP et les données envoyées ; `$passwordHasher` (UserPasswordHasherInterface) : le service Symfony de hachage des mots de passe ; `$entityManager` (EntityManagerInterface) : le gestionnaire Doctrine chargé de la persistance ; `$slugger` (SluggerInterface) : le service qui sécurise les noms de fichiers.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
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
            // Le formulaire valide le fichier, puis le contrôleur construit un
            // nom sûr et unique avant de le déplacer dans le dossier public.
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

            // plainPassword n'est pas une propriété de User : seule sa version
            // hachée est affectée à l'entité puis enregistrée par Doctrine.
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

            return $this->redirectToRoute(
                'app_login',
                [],
                Response::HTTP_SEE_OTHER
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
