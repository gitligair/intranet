<?php

namespace App\Entity;

use App\Repository\CotechVacarmMaterielDetailRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CotechVacarmMaterielDetailRepository::class)]
class CotechVacarmMaterielDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1023)]
    private ?string $sourceVacarm = null;

    #[ORM\OneToOne(inversedBy: 'cotechVacarmMaterielDetail', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?CotechVacarmMateriel $materiel = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSourceVacarm(): ?string
    {
        return $this->sourceVacarm;
    }

    public function setSourceVacarm(string $sourceVacarm): static
    {
        $this->sourceVacarm = $sourceVacarm;

        return $this;
    }

    public function getMateriel(): ?CotechVacarmMateriel
    {
        return $this->materiel;
    }

    public function setMateriel(CotechVacarmMateriel $materiel): static
    {
        $this->materiel = $materiel;

        return $this;
    }

    public function __toString(): string
    {
        return $this->sourceVacarm;
    }
}
