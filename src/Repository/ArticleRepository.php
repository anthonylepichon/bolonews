<?php

/*
 * Présentation : repository Doctrine consacré aux requêtes sur les articles.
 * Rôle : centraliser notamment la recherche publique par texte et catégorie.
 */

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
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
        parent::__construct($registry, Article::class);
    }

//    /**
//     * @return Article[] Returns an array of Article objects
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

//    public function findOneBySomeField($value): ?Article
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * Rôle : Recherche les articles publiés correspondant au texte et à la catégorie.
     * Paramètre : `$search` (?string) : le texte de recherche facultatif ; `$categoryId` (?int) : l’identifiant facultatif de la catégorie.
     * Retour : Un tableau contenant les données demandées.
     *
     * Recherche les articles publiés avec un filtre facultatif.
     *
     * @return Article[]
     */
    public function findPublishedByFilters(
        ?string $search,
        ?int $categoryId
    ): array {
        // Le QueryBuilder construit progressivement une seule requête Doctrine.
        // Le filtre publié est permanent car ce repository alimente le public.
        $queryBuilder = $this->createQueryBuilder('article')
            ->andWhere('article.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('article.createdAt', 'DESC');

        $search = trim((string) $search);

        if ($search !== '') {
            // LOWER rend la comparaison indépendante des majuscules. La valeur
            // est liée par setParameter() et n'est jamais concaténée dans le DQL.
            $queryBuilder
                ->andWhere(
                    'LOWER(article.title) LIKE LOWER(:search)
                    OR LOWER(article.chapeau) LIKE LOWER(:search)
                    OR LOWER(article.content) LIKE LOWER(:search)'
                )
                ->setParameter(
                    'search',
                    '%'.$search.'%'
                );
        }

        if ($categoryId !== null) {
            // IDENTITY lit directement la clé étrangère de la relation category.
            $queryBuilder
                ->andWhere(
                    'IDENTITY(article.category) = :categoryId'
                )
                ->setParameter(
                    'categoryId',
                    $categoryId
                );
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}
