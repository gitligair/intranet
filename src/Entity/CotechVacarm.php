<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Repository\CotechVacarmRepository;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CotechVacarmRepository::class)]
#[UniqueEntity('nomAdherent', message: 'Ce nom d\'adherent existe déjà')]
#[Vich\Uploadable]
class CotechVacarm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomAdherent = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields: ['nomAdherent'])]
    private ?string $slugAdherent = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $addedAt = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(mappedBy: 'adherent', cascade: ['persist', 'remove'])]
    private ?CotechVacarmMateriel $cotechVacarmMateriel = null;

    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/jpg'],
        mimeTypesMessage: 'Veuillez uploader une image valide (jpeg, jpg, png)',
    )]
    #[Vich\UploadableField(mapping: 'cotech_vacarm', fileNameProperty: 'image')]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomAdherent(): ?string
    {
        return $this->nomAdherent;
    }

    public function setNomAdherent(string $nomAdherent): static
    {
        $this->nomAdherent = $nomAdherent;

        return $this;
    }

    public function getSlugAdherent(): ?string
    {
        return $this->slugAdherent;
    }

    public function setSlugAdherent(string $slugAdherent): static
    {
        $this->slugAdherent = $slugAdherent;

        return $this;
    }

    public function getAddedAt(): ?\DateTime
    {
        return $this->addedAt;
    }

    public function setAddedAt(\DateTime $addedAt): static
    {
        $this->addedAt = $addedAt;

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

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }


    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCotechVacarmMateriel(): ?CotechVacarmMateriel
    {
        return $this->cotechVacarmMateriel;
    }

    public function setCotechVacarmMateriel(CotechVacarmMateriel $cotechVacarmMateriel): static
    {
        // set the owning side of the relation if necessary
        if ($cotechVacarmMateriel->getAdherent() !== $this) {
            $cotechVacarmMateriel->setAdherent($this);
        }

        $this->cotechVacarmMateriel = $cotechVacarmMateriel;

        return $this;
    }

    public function __toString(): string
    {
        return strtoupper($this->nomAdherent);
    }
}
