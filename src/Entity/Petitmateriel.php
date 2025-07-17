<?php

namespace App\Entity;

use App\Repository\PetitmaterielRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PetitmaterielRepository::class)]
class Petitmateriel extends Materiel
{

    #[ORM\Column(length: 255)]
    private ?string $denominatif = null;

    #[ORM\Column(length: 255)]
    private ?string $intitule = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields: ['denominatif'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remarques = null;


    public function getDenominatif(): ?string
    {
        return $this->denominatif;
    }

    public function setDenominatif(string $denominatif): static
    {
        $this->denominatif = $denominatif;

        return $this;
    }

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule(string $intitule): static
    {
        $this->intitule = $intitule;

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

    public function getRemarques(): ?string
    {
        return $this->remarques;
    }

    public function setRemarques(?string $remarques): static
    {
        $this->remarques = $remarques;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getType()->getNom() . ' - ' . $this->getDenominatif() . ' ' . $this->getIntitule();
    }
}