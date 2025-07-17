<?php

namespace App\Entity;

use App\Entity\Ecran;
use App\Entity\Ordinateur;
use App\Entity\Petitmateriel;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MaterielRepository;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: MaterielRepository::class)]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name: "typage", type: "string")]
#[ORM\DiscriminatorMap([
    "ecran" => Ecran::class,
    "ordinateur" => Ordinateur::class,
    "petit_materiel" => Petitmateriel::class,
    // tu pourras rajouter d'autres types ici
])]

abstract class Materiel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'materiels')]
    private ?TypeMateriel $type = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'materiels')]
    private ?User $createdBy = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'materiels')]
    private ?Bureau $localisation = null;

    #[ORM\ManyToOne(inversedBy: 'materielsAlloues')]
    private ?User $utilisateur = null;

    #[ORM\Column]
    private ?bool $isStock = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $buyAt = null;

    #[ORM\Column(nullable: true)]
    private ?float $prix = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?TypeMateriel
    {
        return $this->type;
    }

    public function setType(?TypeMateriel $type): static
    {
        $this->type = $type;

        return $this;
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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getLocalisation(): ?Bureau
    {
        return $this->localisation;
    }

    public function setLocalisation(?Bureau $localisation): static
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function isStock(): ?bool
    {
        return $this->isStock;
    }

    public function setIsStock(bool $isStock): static
    {
        $this->isStock = $isStock;

        return $this;
    }

    public function getBuyAt(): ?\DateTimeImmutable
    {
        return $this->buyAt;
    }

    public function setBuyAt(?\DateTimeImmutable $buyAt): static
    {
        $this->buyAt = $buyAt;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }
}