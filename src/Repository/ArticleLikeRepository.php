<?php

/*
 * Présentation : repository Doctrine de l'association ArticleLike.
 * Rôle : rechercher et compter les « J'aime » utilisés par le contrôleur AJAX.
 */

namespace App\Repository;

use App\Entity\ArticleLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleLike>
 */
class ArticleLikeRepository extends ServiceEntityRepository
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
        parent::__construct($registry, ArticleLike::class);
    }

//    /**
//     * @return ArticleLike[] Returns an array of ArticleLike objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ArticleLike
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
