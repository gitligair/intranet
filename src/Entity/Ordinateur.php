<?php

namespace App\Entity;

use App\Repository\OrdinateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrdinateurRepository::class)]
class Ordinateur extends Materiel
{

    #[ORM\Column(length: 255)]
    private ?string $modele = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $processeur = null;

    #[ORM\Column]
    private ?float $ram = null;

    #[ORM\Column]
    private ?float $stockage = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $logiciels = null;

    #[ORM\Column(length: 255)]
    private ?string $identifiant = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields: ['identifiant'])]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'ordinateurs')]
    private ?TaillePouce $taillePouce = null;

    #[ORM\ManyToOne(inversedBy: 'ordinateurs')]
    private ?Categorie $types = null;

    #[ORM\ManyToOne(inversedBy: 'ordinateurs')]
    private ?SousCategorie $sousCatPoste = null;

    #[ORM\ManyToOne(inversedBy: 'ordinateurs')]
    private ?Os $systemeExploitation = null;

    /**
     * @var Collection<int, BaseDeDonnees>
     */
    #[ORM\OneToMany(targetEntity: BaseDeDonnees::class, mappedBy: 'localisation')]
    private Collection $baseDeDonnees;

    public function __construct()
    {
        $this->baseDeDonnees = new ArrayCollection();
    }


    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(string $modele): static
    {
        $this->modele = $modele;

        return $this;
    }


    public function getProcesseur(): ?string
    {
        return $this->processeur;
    }

    public function setProcesseur(?string $processeur): static
    {
        $this->processeur = $processeur;

        return $this;
    }

    public function getRam(): ?float
    {
        return $this->ram;
    }

    public function setRam(float $ram): static
    {
        $this->ram = $ram;

        return $this;
    }

    public function getStockage(): ?float
    {
        return $this->stockage;
    }

    public function setStockage(float $stockage): static
    {
        $this->stockage = $stockage;

        return $this;
    }

    public function getLogiciels(): ?array
    {
        return $this->logiciels;
    }

    public function setLogiciels(?array $logiciels): static
    {
        $this->logiciels = $logiciels;

        return $this;
    }

    public function getIdentifiant(): ?string
    {
        return $this->identifiant;
    }

    public function setIdentifiant(string $identifiant): static
    {
        $this->identifiant = $identifiant;

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
    public function __toString(): string
    {
        return $this->getNom() . ' - ' . $this->getModele() . ' ' . $this->getIdentifiant();
    }

    public function getTaillePouce(): ?TaillePouce
    {
        return $this->taillePouce;
    }

    public function setTaillePouce(?TaillePouce $taillePouce): static
    {
        $this->taillePouce = $taillePouce;

        return $this;
    }

    public function getTypes(): ?Categorie
    {
        return $this->types;
    }

    public function setTypes(?Categorie $types): static
    {
        $this->types = $types;

        return $this;
    }

    public function getSousCatPoste(): ?SousCategorie
    {
        return $this->sousCatPoste;
    }

    public function setSousCatPoste(?SousCategorie $sousCatPoste): static
    {
        $this->sousCatPoste = $sousCatPoste;

        return $this;
    }

    public function getSystemeExploitation(): ?Os
    {
        return $this->systemeExploitation;
    }

    public function setSystemeExploitation(?Os $systemeExploitation): static
    {
        $this->systemeExploitation = $systemeExploitation;

        return $this;
    }

    /**
     * @return Collection<int, BaseDeDonnees>
     */
    public function getBaseDeDonnees(): Collection
    {
        return $this->baseDeDonnees;
    }

    public function addBaseDeDonnee(BaseDeDonnees $baseDeDonnee): static
    {
        if (!$this->baseDeDonnees->contains($baseDeDonnee)) {
            $this->baseDeDonnees->add($baseDeDonnee);
            $baseDeDonnee->setLocalisation($this);
        }

        return $this;
    }

    public function removeBaseDeDonnee(BaseDeDonnees $baseDeDonnee): static
    {
        if ($this->baseDeDonnees->removeElement($baseDeDonnee)) {
            // set the owning side to null (unless already changed)
            if ($baseDeDonnee->getLocalisation() === $this) {
                $baseDeDonnee->setLocalisation(null);
            }
        }

        return $this;
    }
}
