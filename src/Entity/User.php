<?php

/*
 * Présentation : entité Doctrine représentant un compte Bolonews.
 * Rôle : porter l'identité, l'authentification, le profil, les rôles et les relations du membre.
 */

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(
    fields: ['email'],
    message: 'Un compte existe déjà avec cette adresse e-mail.'
)]
#[UniqueEntity(
    fields: ['pseudo'],
    message: 'Ce pseudonyme est déjà utilisé.'
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $pseudo = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isBanned = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatarFilename = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'author')]
    private Collection $articles;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'author')]
    private Collection $comments;

    /**
     * @var Collection<int, ArticleLike>
     */
    #[ORM\OneToMany(targetEntity: ArticleLike::class, mappedBy: 'user')]
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
        $this->articles = new ArrayCollection();
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
     * Rôle : Renvoie l’adresse e-mail.
     * Paramètre : Aucun.
     * Retour : Une chaîne de caractères ou `null` lorsqu’aucune valeur n’existe.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Rôle : Modifie l’adresse e-mail.
     * Paramètre : `$email` (string) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Rôle : Renvoie l’adresse e-mail utilisée comme identifiant de connexion.
     * Paramètre : Aucun.
     * Retour : Une chaîne de caractères.
     *
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Rôle : Renvoie les rôles du compte en garantissant le rôle utilisateur minimal.
     * Paramètre : Aucun.
     * Retour : Un tableau contenant les données demandées.
     *
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Rôle : Modifie les rôles.
     * Paramètre : `$roles` (array) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     *
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Rôle : Renvoie le mot de passe haché.
     * Paramètre : Aucun.
     * Retour : Une chaîne de caractères ou `null` lorsqu’aucune valeur n’existe.
     *
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Rôle : Modifie le mot de passe haché.
     * Paramètre : `$password` (string) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Rôle : Prépare les données du compte conservées pendant la sérialisation de la session.
     * Paramètre : Aucun.
     * Retour : Un tableau contenant les données demandées.
     *
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    /**
     * Rôle : Efface les éventuelles données sensibles temporaires du compte.
     * Paramètre : Aucun.
     * Retour : Aucun (`void`).
     */
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    /**
     * Rôle : Renvoie le pseudonyme.
     * Paramètre : Aucun.
     * Retour : Une chaîne de caractères ou `null` lorsqu’aucune valeur n’existe.
     */
    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    /**
     * Rôle : Modifie le pseudonyme.
     * Paramètre : `$pseudo` (string) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * Rôle : Indique si le bannissement est actif.
     * Paramètre : Aucun.
     * Retour : Un booléen indiquant l’état demandé.
     */
    public function isBanned(): bool
    {
        return $this->isBanned;
    }

    /**
     * Rôle : Modifie l’état de bannissement.
     * Paramètre : `$isBanned` (bool) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setIsBanned(bool $isBanned): static
    {
        $this->isBanned = $isBanned;

        return $this;
    }

    /**
     * Rôle : Renvoie le nom du fichier avatar.
     * Paramètre : Aucun.
     * Retour : Une chaîne de caractères ou `null` lorsqu’aucune valeur n’existe.
     */
    public function getAvatarFilename(): ?string
    {
        return $this->avatarFilename;
    }

    /**
     * Rôle : Modifie le nom du fichier avatar.
     * Paramètre : `$avatarFilename` (?string) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setAvatarFilename(?string $avatarFilename): static
    {
        $this->avatarFilename = $avatarFilename;

        return $this;
    }

    /**
     * Rôle : Renvoie les articles associés.
     * Paramètre : Aucun.
     * Retour : La collection Doctrine des objets associés.
     *
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    /**
     * Rôle : Ajoute l’article associé à la relation.
     * Paramètre : `$article` (Article) : l’article concerné par l’action.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setAuthor($this);
        }

        return $this;
    }

    /**
     * Rôle : Retire l’article associé de la relation.
     * Paramètre : `$article` (Article) : l’article concerné par l’action.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getAuthor() === $this) {
                $article->setAuthor(null);
            }
        }

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
            $comment->setAuthor($this);
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
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
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
            $like->setUser($this);
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
            if ($like->getUser() === $this) {
                $like->setUser(null);
            }
        }

        return $this;
    }
}
