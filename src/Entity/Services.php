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
    #[ORM\OneToMany(targetEntity: Poles::class, mappedBy: 'services', cascade: ['persist'], orphanRemoval: true)]
    private Collection $poles_service;

    #[ORM\ManyToOne(inversedBy: 'services')]
    private ?Processus $processus = null;

    #[ORM\ManyToOne(inversedBy: 'services')]
    private ?User $responsable = null;

    public function __construct()
    {
        $this->poles_service = new ArrayCollection();
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

    /**
     * @return Collection<int, Poles>
     */
    public function getPolesService(): Collection
    {
        return $this->poles_service;
    }

    public function addPolesService(Poles $polesService): static
    {
        if (!$this->poles_service->contains($polesService)) {
            $this->poles_service->add($polesService);
            $polesService->setServices($this);
        }

        return $this;
    }

    public function removePolesService(Poles $polesService): static
    {
        if ($this->poles_service->removeElement($polesService)) {
            // set the owning side to null (unless already changed)
            if ($polesService->getServices() === $this) {
                $polesService->setServices(null);
            }
        }

        return $this;
    }

    public function getProcessus(): ?Processus
    {
        return $this->processus;
    }

    public function setProcessus(?Processus $processus): static
    {
        $this->processus = $processus;

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