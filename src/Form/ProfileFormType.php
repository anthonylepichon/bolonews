<?php

/*
 * Présentation : définition Symfony Form de la modification du profil.
 * Rôle : gérer pseudo, e-mail, avatar et changement facultatif du mot de passe.
 */

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileFormType extends AbstractType
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------
    // Aucun attribut : la structure est construite à partir des arguments reçus.

    // -----------------------
    // METHODES
    // -----------------------

    /**
     * Rôle : Déclare les champs, widgets et contraintes du formulaire Symfony.
     * Paramètre : `$builder` (FormBuilderInterface) : le constructeur du formulaire Symfony ; `$options` (array) : les options disponibles pour configurer le formulaire.
     * Retour : Aucun (`void`).
     */
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('pseudo', TextType::class, [
                'attr' => [
                    'autocomplete' => 'nickname',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir un pseudonyme.'
                    ),
                    new Length(
                        min: 3,
                        max: 100,
                        minMessage: 'Le pseudonyme doit contenir au moins {{ limit }} caractères.',
                        maxMessage: 'Le pseudonyme ne peut pas dépasser {{ limit }} caractères.'
                    ),
                ],
            ])
            ->add('avatar', FileType::class, [
                // Le fichier est traité manuellement par ProfileController ;
                // mapped=false évite de l'affecter à avatarFilename.
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => 'image/jpeg,image/png',
                ],
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                        ],
                        mimeTypesMessage: 'Choisissez une image JPG ou PNG.'
                    ),
                ],
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'autocomplete' => 'email',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir une adresse e-mail.'
                    ),
                    new Email(
                        message: 'Veuillez saisir une adresse e-mail valide.'
                    ),
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                // RepeatedType génère deux champs et vérifie leur égalité. Le
                // champ non mappé ne remplace le mot de passe que s'il est rempli.
                'mapped' => false,
                'required' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'first_options' => [
                    'label' => 'Nouveau mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmation du nouveau mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'constraints' => [
                    new Length(
                        min: 8,
                        max: 4096,
                        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'
                    ),
                ],
            ]);
    }

    /**
     * Rôle : Définit la classe de données et les options acceptées par le formulaire.
     * Paramètre : `$resolver` (OptionsResolver) : le résolveur chargé des options du formulaire.
     * Retour : Aucun (`void`).
     */
    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
