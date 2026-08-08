<?php

/*
 * Présentation : définition Symfony Form des champs d'un article.
 * Rôle : construire le formulaire commun à la création/modification et appliquer ses validations.
 */

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ArticleFormType extends AbstractType
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
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir un titre.'
                    ),
                    new Length(
                        max: 255,
                        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
                    ),
                ],
            ])
            ->add('category', EntityType::class, [
                // EntityType transforme les objets Category en choix de liste ;
                // choice_label indique la propriété affichée à l'utilisateur.
                'class' => Category::class,
                'choice_label' => 'label',
                'placeholder' => 'Choisissez une catégorie',
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez choisir une catégorie.'
                    ),
                ],
            ])
            ->add('chapeau', TextareaType::class, [
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir un chapeau.'
                    ),
                    new Length(
                        max: 500,
                        maxMessage: 'Le chapeau ne peut pas dépasser {{ limit }} caractères.'
                    ),
                ],
            ])
            ->add('content', TextareaType::class, [
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir le contenu de l’article.'
                    ),
                ],
            ])
            ->add('image', FileType::class, [
                // mapped=false : l'entité stocke le nom, pas l'objet fichier.
                // Le contrôleur prend donc en charge le déplacement du fichier.
                'mapped' => false,
                'required' => $options['image_required'],
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp',
                ],
                'constraints' => [
                    new Image(
                        maxSize: '5M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        mimeTypesMessage: 'Choisissez une image JPG, PNG ou WebP.'
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
            'data_class' => Article::class,

            // Cette option personnalisée rend l'image obligatoire à la création,
            // mais facultative lorsque l'article possède déjà une illustration.
            'image_required' => true,
        ]);

        $resolver->setAllowedTypes(
            'image_required',
            'bool'
        );
    }
}
