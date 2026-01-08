<?php

namespace App\Entity;

use App\Repository\AccessoireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: AccessoireRepository::class)]
class Accessoire extends Materiel
{
    #[ORM\Column(length: 255)]
    private ?string $typeAccessoire = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column]
    private ?int $stockDisponible = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remarques = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields: ['typeAccessoire'])]
    private ?string $slug = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'accessoires')]
    private Collection $listeAlloues;

    public function __construct()
    {
        $this->listeAlloues = new ArrayCollection();
    }


    public function getTypeAccessoire(): ?string
    {
        return $this->typeAccessoire;
    }

    public function setTypeAccessoire(string $typeAccessoire): static
    {
        $this->typeAccessoire = $typeAccessoire;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getStockDisponible(): ?int
    {
        return $this->stockDisponible;
    }

    public function setStockDisponible(int $stockDisponible): static
    {
        $this->stockDisponible = $stockDisponible;

        return $this;
    }

    public function getRemarques(): ?string
    {
        return $this->remarques;
    }

    public function setRemarques(?string $remarques): static
    {
        $this->remarques = $remarques;

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

    /**
     * @return Collection<int, User>
     */
    public function getListeAlloues(): Collection
    {
        return $this->listeAlloues;
    }

    public function addListeAlloue(User $listeAlloue): static
    {
        if (!$this->listeAlloues->contains($listeAlloue)) {
            $this->listeAlloues->add($listeAlloue);
        }

        return $this;
    }

    public function removeListeAlloue(User $listeAlloue): static
    {
        $this->listeAlloues->removeElement($listeAlloue);

        return $this;
    }
}
