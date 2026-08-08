<?php

/*
 * Présentation : définition Symfony Form d'une catégorie.
 * Rôle : fournir le champ de libellé et vérifier les règles de saisie côté serveur.
 */

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CategoryType extends AbstractType
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
            ->add('label', TextType::class, [
                // Ces contraintes ajoutées au formulaire appliquent la règle côté
                // serveur, y compris si la validation JavaScript est contournée.
                'constraints' => [
                    new NotBlank(
                        message: 'Veuillez saisir un libellé.'
                    ),
                    new Length(
                        max: 100,
                        maxMessage: 'Le libellé ne peut pas dépasser {{ limit }} caractères.'
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
            'data_class' => Category::class,
        ]);
    }
}
