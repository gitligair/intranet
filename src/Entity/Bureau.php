<?php

namespace App\Entity;

use App\Repository\BureauRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: BureauRepository::class)]
class Bureau
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'bureau')]
    private Collection $occupant;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Materiel>
     */
    #[ORM\OneToMany(targetEntity: Materiel::class, mappedBy: 'localisation')]
    private Collection $materiels;


    public function __construct()
    {
        $this->occupant = new ArrayCollection();
        $this->materiels = new ArrayCollection();
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

    /**
     * @return Collection<int, User>
     */
    public function getOccupant(): Collection
    {
        return $this->occupant;
    }

    public function addOccupant(User $occupant): static
    {
        if (!$this->occupant->contains($occupant)) {
            $this->occupant->add($occupant);
            $occupant->setBureau($this);
        }

        return $this;
    }

    public function removeOccupant(User $occupant): static
    {
        if ($this->occupant->removeElement($occupant)) {
            // set the owning side to null (unless already changed)
            if ($occupant->getBureau() === $this) {
                $occupant->setBureau(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Materiel>
     */
    public function getMateriels(): Collection
    {
        return $this->materiels;
    }

    public function addMateriel(Materiel $materiel): static
    {
        if (!$this->materiels->contains($materiel)) {
            $this->materiels->add($materiel);
            $materiel->setLocalisation($this);
        }

        return $this;
    }

    public function removeMateriel(Materiel $materiel): static
    {
        if ($this->materiels->removeElement($materiel)) {
            // set the owning side to null (unless already changed)
            if ($materiel->getLocalisation() === $this) {
                $materiel->setLocalisation(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom;
    }
    public function getOccupantCount(): int
    {
        return $this->occupant->count();
    }
    public function getMaterielCount(): int
    {
        return $this->materiels->count();
    }
}