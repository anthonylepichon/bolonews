<?php

/*
 * Présentation : repository Doctrine des catégories éditoriales.
 * Rôle : fournir l'accès aux catégories persistées pour les contrôleurs et formulaires.
 */

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------
    // Aucun attribut déclaré : l'accès à Doctrine est hérité du repository parent.

    // -----------------------
    // METHODES
    // -----------------------

    /**
     * Rôle : Initialise le repository pour l’entité Doctrine correspondante.
     * Paramètre : `$registry` (ManagerRegistry) : le registre Doctrine donnant accès au gestionnaire de l’entité.
     * Retour : Aucun : un constructeur initialise l’objet.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

//    /**
//     * @return Category[] Returns an array of Category objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Category
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
