<?php

namespace App\Entity;

use App\Repository\FormTachesprioritairesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormTachesprioritairesRepository::class)]
class FormTachesprioritaires
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 2055)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'tachesPilotees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $pilote = null;

    #[ORM\ManyToOne(inversedBy: 'tachesDoublon')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $doublon = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $delai = null;

    #[ORM\ManyToOne(inversedBy: 'tachesPrioritaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formulaire $formulaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPilote(): ?User
    {
        return $this->pilote;
    }

    public function setPilote(?User $pilote): static
    {
        $this->pilote = $pilote;

        return $this;
    }

    public function getDoublon(): ?User
    {
        return $this->doublon;
    }

    public function setDoublon(?User $doublon): static
    {
        $this->doublon = $doublon;

        return $this;
    }

    public function getDelai(): ?\DateTime
    {
        return $this->delai;
    }

    public function setDelai(\DateTime $delai): static
    {
        $this->delai = $delai;

        return $this;
    }

    public function getFormulaire(): ?Formulaire
    {
        return $this->formulaire;
    }

    public function setFormulaire(?Formulaire $formulaire): static
    {
        $this->formulaire = $formulaire;

        return $this;
    }
}
