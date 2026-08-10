<?php

/*
 * Présentation : contrôleur de l'espace personnel d'un utilisateur connecté.
 * Rôle : afficher ses articles et traiter les modifications de son profil.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileFormType;
use App\Repository\ArticleRepository;
use App\Service\ImageUploader;
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

#[IsGranted('ROLE_USER')]
final class ProfileController extends AbstractController
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------
    // Aucun attribut : les dépendances sont injectées dans les méthodes.

    // -----------------------
    // METHODES
    // -----------------------

    #[Route(
        '/mon-espace',
        name: 'app_profile_index',
        methods: ['GET']
    )]
    /**
     * Rôle : Affiche le profil du membre connecté et ses articles.
     * Paramètre : `$articleRepository` (ArticleRepository) : le repository utilisé pour interroger les articles.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
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
    /**
     * Rôle : Affiche et traite la modification des informations du profil.
     * Paramètre : `$request` (Request) : la requête HTTP et les données envoyées ; `$entityManager` (EntityManagerInterface) : le gestionnaire Doctrine chargé de la persistance ; `$passwordHasher` (UserPasswordHasherInterface) : le service Symfony de hachage des mots de passe ; `$imageUploader` (ImageUploader) : le service générique qui enregistre les images.
     * Retour : Une réponse HTTP contenant la page ou la redirection.
     */
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ImageUploader $imageUploader
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(
            ProfileFormType::class,
            $user
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Le champ avatar est volontairement non mappé : le fichier est
            // déplacé dans public/, puis seul son nom est enregistré en base.
            /** @var UploadedFile|null $avatarFile */
            $avatarFile = $form->get('avatar')->getData();

            if ($avatarFile !== null) {
                try {
                    $avatarFilename = $imageUploader->upload(
                        $avatarFile,
                        (string) $this->getParameter(
                            'avatars_directory'
                        )
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
                // Le nouveau mot de passe est facultatif et n'est jamais stocké
                // en clair : le hasher Symfony produit la valeur persistée.
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $newPassword
                    )
                );
            }

            // L'utilisateur existe déjà et reste suivi par Doctrine : flush()
            // suffit, aucun nouvel persist() n'est nécessaire.
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
