<?php

/*
 * Présentation : entité Doctrine représentant une catégorie éditoriale.
 * Rôle : classer plusieurs articles sous un libellé unique.
 */

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(
    fields: ['label'],
    message: 'Cette catégorie existe déjà.'
)]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    // -----------------------
    // ATTRIBUTS
    // -----------------------

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $label = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'category')]
    private Collection $articles;

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
     * Rôle : Renvoie le libellé.
     * Paramètre : Aucun.
     * Retour : Une chaîne de caractères ou `null` lorsqu’aucune valeur n’existe.
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Rôle : Modifie le libellé.
     * Paramètre : `$label` (string) : la valeur transmise à la méthode.
     * Retour : L’instance courante afin de permettre l’enchaînement des appels.
     */
    public function setLabel(string $label): static
    {
        $this->label = $label;

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
            $article->setCategory($this);
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
            if ($article->getCategory() === $this) {
                $article->setCategory(null);
            }
        }

        return $this;
    }
}
