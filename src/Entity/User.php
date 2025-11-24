<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(groups: ['create'])]
    #[Assert\Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins 8 caractères')]
    private ?string $password = null;


    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields: ['prenom', 'nom'])]
    private ?string $identifiant = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private ?bool $isOnline = null;

    /**
     * @var Collection<int, Poles>
     */
    #[ORM\ManyToMany(targetEntity: Poles::class, mappedBy: 'personnel')]
    private Collection $poles;

    /**
     * @var Collection<int, Services>
     */
    #[ORM\OneToMany(targetEntity: Services::class, mappedBy: 'responsable')]
    private Collection $services;



    #[ORM\ManyToOne(inversedBy: 'occupant')]
    private ?Bureau $bureau = null;

    /**
     * @var Collection<int, Materiel>
     */
    #[ORM\OneToMany(targetEntity: Materiel::class, mappedBy: 'createdBy')]
    private Collection $materiels;

    /**
     * @var Collection<int, Materiel>
     */
    #[ORM\OneToMany(targetEntity: Materiel::class, mappedBy: 'utilisateur')]
    private Collection $materielsAlloues;

    /**
     * @var Collection<int, Poles>
     */
    #[ORM\OneToMany(targetEntity: Poles::class, mappedBy: 'responsable')]
    private Collection $responsable_pole;

    /**
     * @var Collection<int, Tache>
     */
    #[ORM\ManyToMany(targetEntity: Tache::class, mappedBy: 'assignerA')]
    private Collection $taches;

    /**
     * @var Collection<int, Processus>
     */
    #[ORM\ManyToMany(targetEntity: Processus::class, mappedBy: 'pilotes')]
    private Collection $processuses;

    /**
     * @var Collection<int, Tache>
     */
    #[ORM\OneToMany(targetEntity: Tache::class, mappedBy: 'createdBy')]
    private Collection $tachesCrees;


    public function __construct()
    {
        $this->poles = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->materiels = new ArrayCollection();
        $this->materielsAlloues = new ArrayCollection();
        $this->responsable_pole = new ArrayCollection();
        $this->taches = new ArrayCollection();
        $this->processuses = new ArrayCollection();
        $this->tachesCrees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_LIGAIR
        $roles[] = 'ROLE_LIGAIR';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }



    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }


    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
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

    public function __toString(): string
    {
        return $this->prenom . ' ' . strtoupper($this->nom);
    }

    /**
     * @return Collection<int, Poles>
     */
    public function getPoles(): Collection
    {
        return $this->poles;
    }

    public function addPole(Poles $pole): static
    {
        if (!$this->poles->contains($pole)) {
            $this->poles->add($pole);
            $pole->addPersonnel($this);
        }

        return $this;
    }

    public function removePole(Poles $pole): static
    {
        if ($this->poles->removeElement($pole)) {
            $pole->removePersonnel($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Services>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Services $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setResponsable($this);
        }

        return $this;
    }

    public function removeService(Services $service): static
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getResponsable() === $this) {
                $service->setResponsable(null);
            }
        }

        return $this;
    }



    public function getBureau(): ?Bureau
    {
        return $this->bureau;
    }

    public function setBureau(?Bureau $bureau): static
    {
        $this->bureau = $bureau;

        return $this;
    }

    /**
     * @return Collection<int, Materiel>
     */
    public function getMateriels(): Collection
    {
        return $this->materiels;
    }

    public function addMateriel(Materiel $materiel): static
    {
        if (!$this->materiels->contains($materiel)) {
            $this->materiels->add($materiel);
            $materiel->setCreatedBy($this);
        }

        return $this;
    }

    public function removeMateriel(Materiel $materiel): static
    {
        if ($this->materiels->removeElement($materiel)) {
            // set the owning side to null (unless already changed)
            if ($materiel->getCreatedBy() === $this) {
                $materiel->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Materiel>
     */
    public function getMaterielsAlloues(): Collection
    {
        return $this->materielsAlloues;
    }

    public function addMaterielsAlloue(Materiel $materielsAlloue): static
    {
        if (!$this->materielsAlloues->contains($materielsAlloue)) {
            $this->materielsAlloues->add($materielsAlloue);
            $materielsAlloue->setUtilisateur($this);
        }

        return $this;
    }

    public function removeMaterielsAlloue(Materiel $materielsAlloue): static
    {
        if ($this->materielsAlloues->removeElement($materielsAlloue)) {
            // set the owning side to null (unless already changed)
            if ($materielsAlloue->getUtilisateur() === $this) {
                $materielsAlloue->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Poles>
     */
    public function getResponsablePole(): Collection
    {
        return $this->responsable_pole;
    }

    public function addResponsablePole(Poles $responsablePole): static
    {
        if (!$this->responsable_pole->contains($responsablePole)) {
            $this->responsable_pole->add($responsablePole);
            $responsablePole->setResponsable($this);
        }

        return $this;
    }

    public function removeResponsablePole(Poles $responsablePole): static
    {
        if ($this->responsable_pole->removeElement($responsablePole)) {
            // set the owning side to null (unless already changed)
            if ($responsablePole->getResponsable() === $this) {
                $responsablePole->setResponsable(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tache>
     */
    public function getTaches(): Collection
    {
        return $this->taches;
    }

    public function addTach(Tache $tach): static
    {
        if (!$this->taches->contains($tach)) {
            $this->taches->add($tach);
            $tach->addAssignerA($this);
        }

        return $this;
    }

    public function removeTach(Tache $tach): static
    {
        if ($this->taches->removeElement($tach)) {
            $tach->removeAssignerA($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Processus>
     */
    public function getProcessuses(): Collection
    {
        return $this->processuses;
    }

    public function addProcessus(Processus $processus): static
    {
        if (!$this->processuses->contains($processus)) {
            $this->processuses->add($processus);
            $processus->addPilote($this);
        }

        return $this;
    }

    public function removeProcessus(Processus $processus): static
    {
        if ($this->processuses->removeElement($processus)) {
            $processus->removePilote($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Tache>
     */
    public function getTachesCrees(): Collection
    {
        return $this->tachesCrees;
    }

    public function addTachesCree(Tache $tachesCree): static
    {
        if (!$this->tachesCrees->contains($tachesCree)) {
            $this->tachesCrees->add($tachesCree);
            $tachesCree->setCreatedBy($this);
        }

        return $this;
    }

    public function removeTachesCree(Tache $tachesCree): static
    {
        if ($this->tachesCrees->removeElement($tachesCree)) {
            // set the owning side to null (unless already changed)
            if ($tachesCree->getCreatedBy() === $this) {
                $tachesCree->setCreatedBy(null);
            }
        }

        return $this;
    }
}