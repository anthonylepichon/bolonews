<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
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
     * Recherche les articles publiés avec un filtre facultatif.
     *
     * @return Article[]
     */
    public function findPublishedByFilters(
        ?string $search,
        ?int $categoryId
    ): array {
        $queryBuilder = $this->createQueryBuilder('article')
            ->andWhere('article.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('article.createdAt', 'DESC');

        $search = trim((string) $search);

        if ($search !== '') {
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
