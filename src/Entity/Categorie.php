<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields: ['nom'])]
    private ?string $slug = null;

    /**
     * @var Collection<int, Ordinateur>
     */
    #[ORM\OneToMany(targetEntity: Ordinateur::class, mappedBy: 'types')]
    private Collection $ordinateurs;

    public function __construct()
    {
        $this->ordinateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
    public function __toString(): string
    {
        return $this->nom;
    }

    /**
     * @return Collection<int, Ordinateur>
     */
    public function getOrdinateurs(): Collection
    {
        return $this->ordinateurs;
    }

    public function addOrdinateur(Ordinateur $ordinateur): static
    {
        if (!$this->ordinateurs->contains($ordinateur)) {
            $this->ordinateurs->add($ordinateur);
            $ordinateur->setTypes($this);
        }

        return $this;
    }

    public function removeOrdinateur(Ordinateur $ordinateur): static
    {
        if ($this->ordinateurs->removeElement($ordinateur)) {
            // set the owning side to null (unless already changed)
            if ($ordinateur->getTypes() === $this) {
                $ordinateur->setTypes(null);
            }
        }

        return $this;
    }
}
