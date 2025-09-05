<?php

namespace App\Entity;

use App\Repository\CotechVacarmMaterielRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: CotechVacarmMaterielRepository::class)]
class CotechVacarmMateriel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 24)]
    #[Assert\Ip(message: 'L\'adresse IP doit être une adresse IPv4 valide')]
    private ?string $adresseIp = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: 'Le numéro de port doit être un entier positif')]
    private ?int $port = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        min: 4,
        minMessage: 'le nom d\'utilisateur doit faire au moins {{ limit }} caractères',
    )]
    private ?string $identifiant = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        min: 4,
        minMessage: 'le nom d\'utilisateur doit faire au moins {{ limit }} caractères',
    )]
    private ?string $motdepasse = null;

    #[ORM\Column(length: 127)]
    #[Assert\Choice(['Windows', 'Linux', 'macOs', 'Autre'], message: 'Le système d\'exploitation doit être Windows, Linux, macOs ou Autre')]
    private ?string $os = null;

    #[ORM\Column(length: 511)]
    private ?string $detailOs = null;

    #[ORM\OneToOne(inversedBy: 'cotechVacarmMateriel', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?CotechVacarm $adherent = null;

    #[ORM\OneToOne(mappedBy: 'materiel', cascade: ['persist', 'remove'])]
    private ?CotechVacarmMaterielDetail $cotechVacarmMaterielDetail = null;

    /**
     * @var Collection<int, CotechVacarmMaterielBd>
     */
    #[ORM\OneToMany(targetEntity: CotechVacarmMaterielBd::class, mappedBy: 'materiel', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $bds;

    public function __construct()
    {
        $this->bds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdresseIp(): ?string
    {
        return $this->adresseIp;
    }

    public function setAdresseIp(string $adresseIp): static
    {
        $this->adresseIp = $adresseIp;

        return $this;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(?int $port): static
    {
        $this->port = $port;

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

    public function getMotdepasse(): ?string
    {
        return $this->motdepasse;
    }

    public function setMotdepasse(string $motdepasse): static
    {
        $this->motdepasse = $motdepasse;

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

    public function getDetailOs(): ?string
    {
        return $this->detailOs;
    }

    public function setDetailOs(string $detailOs): static
    {
        $this->detailOs = $detailOs;

        return $this;
    }

    public function getAdherent(): ?CotechVacarm
    {
        return $this->adherent;
    }

    public function setAdherent(CotechVacarm $adherent): static
    {
        $this->adherent = $adherent;

        return $this;
    }

    public function getCotechVacarmMaterielDetail(): ?CotechVacarmMaterielDetail
    {
        return $this->cotechVacarmMaterielDetail;
    }

    public function setCotechVacarmMaterielDetail(CotechVacarmMaterielDetail $cotechVacarmMaterielDetail): static
    {
        // set the owning side of the relation if necessary
        if ($cotechVacarmMaterielDetail->getMateriel() !== $this) {
            $cotechVacarmMaterielDetail->setMateriel($this);
        }

        $this->cotechVacarmMaterielDetail = $cotechVacarmMaterielDetail;

        return $this;
    }

    /**
     * @return Collection<int, CotechVacarmMaterielBd>
     */
    public function getBds(): Collection
    {
        return $this->bds;
    }

    public function addBd(CotechVacarmMaterielBd $bd): static
    {
        if (!$this->bds->contains($bd)) {
            $this->bds->add($bd);
            $bd->setMateriel($this);
        }

        return $this;
    }

    public function removeBd(CotechVacarmMaterielBd $bd): static
    {
        if ($this->bds->removeElement($bd)) {
            // set the owning side to null (unless already changed)
            if ($bd->getMateriel() === $this) {
                $bd->setMateriel(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return 'Caracteristiques serveur ' . $this->adherent->getNomAdherent();
    }
}