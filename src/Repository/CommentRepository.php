<?php

/*
 * Présentation : repository Doctrine des commentaires.
 * Rôle : fournir les opérations de lecture liées aux commentaires persistés.
 */

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
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
        parent::__construct($registry, Comment::class);
    }

//    /**
//     * @return Comment[] Returns an array of Comment objects
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

//    public function findOneBySomeField($value): ?Comment
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
