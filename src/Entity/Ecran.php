<?php

namespace App\Entity;

use App\Repository\EcranRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EcranRepository::class)]
class Ecran extends Materiel
{
    // #[ORM\Id]
    // #[ORM\GeneratedValue]
    // #[ORM\Column]
    // private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $marque = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numSerie = null;

    #[ORM\Column(nullable: true)]
    private ?float $taille = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $connecteurs = null;


    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }

    public function getNumSerie(): ?string
    {
        return $this->numSerie;
    }

    public function setNumSerie(?string $numSerie): static
    {
        $this->numSerie = $numSerie;

        return $this;
    }

    public function getTaille(): ?float
    {
        return $this->taille;
    }

    public function setTaille(?float $taille): static
    {
        $this->taille = $taille;

        return $this;
    }

    public function getConnecteurs(): ?array
    {
        return $this->connecteurs;
    }

    public function setConnecteurs(?array $connecteurs): static
    {
        $this->connecteurs = $connecteurs;

        return $this;
    }
}