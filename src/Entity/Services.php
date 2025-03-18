<?php

namespace App\Entity;

use App\Repository\ServicesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ServicesRepository::class)]
class Services
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields: ['nom'])]
    private ?string $identifiant = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $addedAt = null;

    #[ORM\Column]
    private ?bool $isOnline = null;

    /**
     * @var Collection<int, Poles>
     */
    #[ORM\OneToMany(targetEntity: Poles::class, mappedBy: 'services', cascade: ['persist', 'remove'])]
    private Collection $poles_service;

    #[ORM\ManyToMany(targetEntity: Processus::class, inversedBy: 'services')]
    private ?Collection $processus = null;

    #[ORM\ManyToOne(inversedBy: 'services')]
    private ?User $responsable = null;

    public function __construct()
    {
        $this->poles_service = new ArrayCollection();
        $this->processus = new ArrayCollection();
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

    public function setIdentifiant(string $identifiant): static
    {
        $this->identifiant = $identifiant;

        return $this;
    }

    public function getIdentifiant(): ?string
    {
        return $this->identifiant;
    }


    public function getAddedAt(): ?\DateTimeImmutable
    {
        return $this->addedAt;
    }


    public function isOnline(): ?bool
    {
        return $this->isOnline;
    }

    public function setIsOnline(bool $isOnline): static
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    public function getPolesService(): Collection
    {
        return $this->poles_service;
    }

    public function addPoleService(Poles $pole): self
    {
        if (!$this->poles_service->contains($pole)) {
            $this->poles_service->add($pole);
            $pole->setServices($this); // Mise à jour de l'autre côté
        }

        return $this;
    }

    public function removePoleService(Poles $pole): self
    {
        if ($this->poles_service->removeElement($pole)) {
            // Vérifie si le Pole appartient bien à ce Service avant de l'enlever
            if ($pole->getServices() === $this) {
                $pole->setServices(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Poles>
     */
    public function getProcessus(): Collection
    {
        return $this->processus;
    }

    public function addProcessus(Processus $processus): static
    {
        if (!$this->processus->contains($processus)) {
            $this->processus->add($processus);
            $processus->addService($this);
        }

        return $this;
    }

    public function removeProcessus(Processus $processus): static
    {
        if ($this->poles_service->removeElement($processus)) {
            $processus->removeService($this);
        }

        return $this;
    }

    public function getResponsable(): ?User
    {
        return $this->responsable;
    }

    public function setResponsable(?User $responsable): static
    {
        $this->responsable = $responsable;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}