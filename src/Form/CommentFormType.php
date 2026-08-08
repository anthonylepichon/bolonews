<?php

/*
 * Présentation : définition Symfony Form d'un commentaire.
 * Rôle : construire la zone de texte et valider son contenu avant publication.
 */

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommentFormType extends AbstractType
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
            ->add('content', TextareaType::class, [
                // La validation reste côté Symfony : Twig peut ensuite afficher
                // ces erreurs tout en conservant le commentaire saisi.
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir un commentaire.'
                    ),
                    new Length(
                        max: 2000,
                        maxMessage: 'Le commentaire ne peut pas dépasser {{ limit }} caractères.'
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
            'data_class' => Comment::class,
        ]);
    }
}
