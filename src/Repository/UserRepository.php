<?php

/*
 * Présentation : repository Doctrine des comptes utilisateurs.
 * Rôle : charger les membres, rechercher dans l'administration et actualiser les mots de passe.
 */

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
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
        parent::__construct($registry, User::class);
    }

    /**
     * Rôle : Remplace automatiquement un ancien hachage de mot de passe.
     * Paramètre : `$user` (PasswordAuthenticatedUserInterface) : le compte utilisateur concerné ; `$newHashedPassword` (string) : le nouveau mot de passe déjà haché.
     * Retour : Aucun (`void`).
     *
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * Rôle : Recherche les comptes destinés au tableau d’administration.
     * Paramètre : `$search` (?string) : le texte de recherche facultatif.
     * Retour : Un tableau contenant les données demandées.
     *
     * Recherche les utilisateurs par pseudo ou adresse e-mail.
     *
     * @return User[]
     */
    public function findForAdmin(
        ?string $search
    ): array {
        // La requête de base renvoie tous les comptes ; le filtre n'est ajouté
        // que lorsque l'administrateur a réellement saisi une recherche.
        $queryBuilder = $this->createQueryBuilder('user')
            ->orderBy('user.pseudo', 'ASC');

        $search = trim((string) $search);

        if ($search !== '') {
            // Le même paramètre sécurisé recherche dans le pseudo et l'e-mail.
            $queryBuilder
                ->andWhere(
                    'LOWER(user.pseudo) LIKE LOWER(:search)
                    OR LOWER(user.email) LIKE LOWER(:search)'
                )
                ->setParameter(
                    'search',
                    '%'.$search.'%'
                );
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}
