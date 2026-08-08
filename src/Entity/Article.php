<?php

/*
 * Présentation : entité Doctrine représentant une publication.
 * Rôle : stocker son contenu, son état, ses dates et ses relations avec auteur et catégorie.
 */

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 500)]
    private ?string $chapeau = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    private ?string $imageFilename = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isPublished = false;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?Category $category = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'article')]
    private Collection $comments;

    /**
     * @var Collection<int, ArticleLike>
     */
    #[ORM\OneToMany(targetEntity: ArticleLike::class, mappedBy: 'article')]
    private Collection $likes;

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
        $this->comments = new ArrayCollection();
        $this->likes = new ArrayCollection();
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
     * Rôle : Renvoie le titre.
     * Paramètre : Aucun.
     * Retour : Une chaîne de caractères ou `null` lorsqu’aucune valeur n’existe.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Rôle : Modifie le titre.
     * Paramètre : `$title` (string) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Rôle : Renvoie le chapeau.
     * Paramètre : Aucun.
     * Retour : Une chaîne de caractères ou `null` lorsqu’aucune valeur n’existe.
     */
    public function getChapeau(): ?string
    {
        return $this->chapeau;
    }

    /**
     * Rôle : Modifie le chapeau.
     * Paramètre : `$chapeau` (string) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setChapeau(string $chapeau): static
    {
        $this->chapeau = $chapeau;

        return $this;
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
     * Rôle : Renvoie le nom du fichier image.
     * Paramètre : Aucun.
     * Retour : Une chaîne de caractères ou `null` lorsqu’aucune valeur n’existe.
     */
    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    /**
     * Rôle : Modifie le nom du fichier image.
     * Paramètre : `$imageFilename` (string) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setImageFilename(string $imageFilename): static
    {
        $this->imageFilename = $imageFilename;

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
     * Rôle : Renvoie la date de modification.
     * Paramètre : Aucun.
     * Retour : La date et l’heure enregistrées, ou `null` si elles sont absentes.
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Rôle : Modifie la date de modification.
     * Paramètre : `$updatedAt` (?\DateTimeImmutable) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Rôle : Indique si la publication est actif.
     * Paramètre : Aucun.
     * Retour : Un booléen indiquant l’état demandé.
     */
    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    /**
     * Rôle : Modifie l’état de publication.
     * Paramètre : `$isPublished` (bool) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

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

    /**
     * Rôle : Renvoie la catégorie associée.
     * Paramètre : Aucun.
     * Retour : Une instance de Category ou `null` si la relation est absente.
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Rôle : Modifie la catégorie associée.
     * Paramètre : `$category` (?Category) : la catégorie concernée par l’action.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Rôle : Renvoie les commentaires associés.
     * Paramètre : Aucun.
     * Retour : La collection Doctrine des objets associés.
     *
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * Rôle : Ajoute le commentaire associé à la relation.
     * Paramètre : `$comment` (Comment) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setArticle($this);
        }

        return $this;
    }

    /**
     * Rôle : Retire le commentaire associé de la relation.
     * Paramètre : `$comment` (Comment) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getArticle() === $this) {
                $comment->setArticle(null);
            }
        }

        return $this;
    }

    /**
     * Rôle : Renvoie les J’aime associés.
     * Paramètre : Aucun.
     * Retour : La collection Doctrine des objets associés.
     *
     * @return Collection<int, ArticleLike>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    /**
     * Rôle : Ajoute le J’aime associé à la relation.
     * Paramètre : `$like` (ArticleLike) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function addLike(ArticleLike $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setArticle($this);
        }

        return $this;
    }

    /**
     * Rôle : Retire le J’aime associé de la relation.
     * Paramètre : `$like` (ArticleLike) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function removeLike(ArticleLike $like): static
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getArticle() === $this) {
                $like->setArticle(null);
            }
        }

        return $this;
    }
}
