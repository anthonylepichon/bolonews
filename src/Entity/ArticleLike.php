<?php

/*
 * Présentation : entité d'association entre un utilisateur et un article aimé.
 * Rôle : matérialiser un « J'aime » unique dans la relation plusieurs-à-plusieurs.
 */

namespace App\Entity;

use App\Repository\ArticleLikeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleLikeRepository::class)]
#[ORM\UniqueConstraint(
    name: 'UNIQ_ARTICLE_LIKE_USER_ARTICLE',
    fields: ['user', 'article']
)]
class ArticleLike
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'likes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'likes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Article $article = null;

    // -----------------------
    // METHODES
    // -----------------------

    /**
     * Rôle : Renvoie l’identifiant.
     * Paramètre : Aucun.
     * Retour : Un entier ou `null` avant la persistance de l’entité.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Rôle : Renvoie l’utilisateur associé.
     * Paramètre : Aucun.
     * Retour : Une instance de User ou `null` si la relation est absente.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Rôle : Modifie l’utilisateur associé.
     * Paramètre : `$user` (?User) : le compte utilisateur concerné.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Rôle : Renvoie l’article associé.
     * Paramètre : Aucun.
     * Retour : Une instance de Article ou `null` si la relation est absente.
     */
    public function getArticle(): ?Article
    {
        return $this->article;
    }

    /**
     * Rôle : Modifie l’article associé.
     * Paramètre : `$article` (?Article) : l’article concerné par l’action.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }
}
