<?php

namespace App\Entity;

use App\Repository\OrdinateurRepository;
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

    #[ORM\Column(length: 255)]
    private ?string $os = null;

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

    public function getOs(): ?string
    {
        return $this->os;
    }

    public function setOs(string $os): static
    {
        $this->os = $os;

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
        return $this->getType()->getNom() . ' - ' . $this->getModele() . ' ' . $this->getIdentifiant();
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
}
