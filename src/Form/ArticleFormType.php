<?php

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
                // L’entité stocke le nom du fichier, pas le fichier lui-même.
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

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => Article::class,

            // Obligatoire à la création, facultative en modification.
            'image_required' => true,
        ]);

        $resolver->setAllowedTypes(
            'image_required',
            'bool'
        );
    }
}
