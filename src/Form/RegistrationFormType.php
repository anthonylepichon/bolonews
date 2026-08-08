<?php

/*
 * Présentation : définition Symfony Form de l'inscription.
 * Rôle : recueillir le compte, l'avatar, le mot de passe confirmé et l'acceptation des conditions.
 */

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
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
                // Le fichier n'est pas directement enregistré dans l'entité :
                // RegistrationController stocke seulement son nom final.
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
            ->add('plainPassword', RepeatedType::class, [
                // Le mot de passe en clair ne doit jamais être stocké. Le champ
                // non mappé est haché dans le contrôleur avant la persistance.
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmation du mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir un mot de passe.'
                    ),
                    new Length(
                        min: 8,
                        max: 4096,
                        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'
                    ),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                // Cette case sert uniquement à la validation du formulaire ;
                // elle ne correspond donc à aucune colonne de la table user.
                'mapped' => false,
                'constraints' => [
                    new IsTrue(
                        message: 'Vous devez accepter les conditions d’utilisation.'
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
