<?php

/*
 * Présentation : entité Doctrine représentant un commentaire publié.
 * Rôle : conserver son contenu, sa date, son auteur et l'article auquel il appartient.
 */

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Article $article = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $author = null;

    // -----------------------
    // METHODES
    // -----------------------

    /**
     * Rôle : Initialise une nouvelle instance et ses valeurs ou collections par défaut.
     * Paramètre : Aucun.
     * Retour : Aucun : un constructeur initialise l’objet.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

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
     * Rôle : Renvoie le contenu.
     * Paramètre : Aucun.
     * Retour : Une chaîne de caractères ou `null` lorsqu’aucune valeur n’existe.
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Rôle : Modifie le contenu.
     * Paramètre : `$content` (string) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Rôle : Renvoie la date de création.
     * Paramètre : Aucun.
     * Retour : La date et l’heure enregistrées.
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Rôle : Modifie la date de création.
     * Paramètre : `$createdAt` (\DateTimeImmutable) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    /**
     * Rôle : Renvoie l’auteur associé.
     * Paramètre : Aucun.
     * Retour : Une instance de User ou `null` si la relation est absente.
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Rôle : Modifie l’auteur associé.
     * Paramètre : `$author` (?User) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }
}
