<?php

namespace App\Entity;

use App\Repository\FormPointsclesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormPointsclesRepository::class)]
class FormPointscles
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'formPointscles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Poles $pole = null;

    #[ORM\Column(length: 1023)]
    private ?string $texte = null;

    #[ORM\ManyToOne(inversedBy: 'pointsCles')]
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
