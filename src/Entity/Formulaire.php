<?php

namespace App\Entity;

use App\Repository\FormulaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: FormulaireRepository::class)]
class Formulaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateJour = null;

    #[ORM\ManyToOne(inversedBy: 'formulaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $animateur = null;

    #[ORM\Column]
    private ?bool $reunionTenue = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'formulairesPresents')]
    private Collection $presents;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $prochaineReunionAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $prochainAnimateur = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, FormPointscles>
     */
    #[ORM\OneToMany(targetEntity: FormPointscles::class, mappedBy: 'formulaire', orphanRemoval: true)]
    private Collection $pointsCles;

    /**
     * @var Collection<int, FormPartageinfos>
     */
    #[ORM\OneToMany(targetEntity: FormPartageinfos::class, mappedBy: 'formulaire', orphanRemoval: true)]
    private Collection $partageInfos;

    /**
     * @var Collection<int, FormTachesprioritaires>
     */
    #[ORM\OneToMany(targetEntity: FormTachesprioritaires::class, mappedBy: 'formulaire', orphanRemoval: true)]
    private Collection $tachesPrioritaires;

    #[ORM\Column(nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->presents = new ArrayCollection();
        $this->pointsCles = new ArrayCollection();
        $this->partageInfos = new ArrayCollection();
        $this->tachesPrioritaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateJour(): ?\DateTime
    {
        return $this->dateJour;
    }

    public function setDateJour(\DateTime $dateJour): static
    {
        $this->dateJour = $dateJour;

        return $this;
    }

    public function getAnimateur(): ?User
    {
        return $this->animateur;
    }

    public function setAnimateur(?User $animateur): static
    {
        $this->animateur = $animateur;

        return $this;
    }

    public function isReunionTenue(): ?bool
    {
        return $this->reunionTenue;
    }

    public function setReunionTenue(bool $reunionTenue): static
    {
        $this->reunionTenue = $reunionTenue;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getPresents(): Collection
    {
        return $this->presents;
    }

    public function addPresent(User $present): static
    {
        if (!$this->presents->contains($present)) {
            $this->presents->add($present);
        }

        return $this;
    }

    public function removePresent(User $present): static
    {
        $this->presents->removeElement($present);

        return $this;
    }

    public function getProchaineReunionAt(): ?\DateTimeImmutable
    {
        return $this->prochaineReunionAt;
    }

    public function setProchaineReunionAt(?\DateTimeImmutable $prochaineReunionAt): static
    {
        $this->prochaineReunionAt = $prochaineReunionAt;

        return $this;
    }

    public function getProchainAnimateur(): ?User
    {
        return $this->prochainAnimateur;
    }

    public function setProchainAnimateur(?User $prochainAnimateur): static
    {
        $this->prochainAnimateur = $prochainAnimateur;

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

    /**
     * @return Collection<int, FormPointscles>
     */
    public function getPointsCles(): Collection
    {
        return $this->pointsCles;
    }

    public function addPointsCle(FormPointscles $pointsCle): static
    {
        if (!$this->pointsCles->contains($pointsCle)) {
            $this->pointsCles->add($pointsCle);
            $pointsCle->setFormulaire($this);
        }

        return $this;
    }

    public function removePointsCle(FormPointscles $pointsCle): static
    {
        if ($this->pointsCles->removeElement($pointsCle)) {
            // set the owning side to null (unless already changed)
            if ($pointsCle->getFormulaire() === $this) {
                $pointsCle->setFormulaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FormPartageinfos>
     */
    public function getPartageInfos(): Collection
    {
        return $this->partageInfos;
    }

    public function addPartageInfo(FormPartageinfos $partageInfo): static
    {
        if (!$this->partageInfos->contains($partageInfo)) {
            $this->partageInfos->add($partageInfo);
            $partageInfo->setFormulaire($this);
        }

        return $this;
    }

    public function removePartageInfo(FormPartageinfos $partageInfo): static
    {
        if ($this->partageInfos->removeElement($partageInfo)) {
            // set the owning side to null (unless already changed)
            if ($partageInfo->getFormulaire() === $this) {
                $partageInfo->setFormulaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FormTachesprioritaires>
     */
    public function getTachesPrioritaires(): Collection
    {
        return $this->tachesPrioritaires;
    }

    public function addTachesPrioritaire(FormTachesprioritaires $tachesPrioritaire): static
    {
        if (!$this->tachesPrioritaires->contains($tachesPrioritaire)) {
            $this->tachesPrioritaires->add($tachesPrioritaire);
            $tachesPrioritaire->setFormulaire($this);
        }

        return $this;
    }

    public function removeTachesPrioritaire(FormTachesprioritaires $tachesPrioritaire): static
    {
        if ($this->tachesPrioritaires->removeElement($tachesPrioritaire)) {
            // set the owning side to null (unless already changed)
            if ($tachesPrioritaire->getFormulaire() === $this) {
                $tachesPrioritaire->setFormulaire(null);
            }
        }

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
}
