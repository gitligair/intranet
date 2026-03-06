<?php

namespace App\Entity;

use App\Repository\FormPartageinfosRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormPartageinfosRepository::class)]
class FormPartageinfos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'formPartageinfos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Poles $pole = null;

    #[ORM\Column(length: 1023)]
    private ?string $texte = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $important = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $aRelancer = null;

    #[ORM\ManyToOne(inversedBy: 'partageInfos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formulaire $formulaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPole(): ?Poles
    {
        return $this->pole;
    }

    public function setPole(?Poles $pole): static
    {
        $this->pole = $pole;

        return $this;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(string $texte): static
    {
        $this->texte = $texte;

        return $this;
    }

    public function isImportant(): ?bool
    {
        return $this->important;
    }

    public function setImportant(bool $important): static
    {
        $this->important = $important;

        return $this;
    }

    public function isARelancer(): ?bool
    {
        return $this->aRelancer;
    }

    public function setARelancer(bool $aRelancer): static
    {
        $this->aRelancer = $aRelancer;

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
