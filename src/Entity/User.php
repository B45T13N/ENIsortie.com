<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=50)
     *  @Assert\Regex (pattern="/\d/",
     *     match=false,
     *     message="Votre nom ne peut pas contenir de nombre.")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\Regex (pattern="/\d/",
     *     match=false,
     *     message="Votre prénom ne peut pas contenir de nombre.")
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\Regex ("/[^0-9]/",
     *     match=false,
     *     message="Votre numéro de téléphone ne peut contenir que des chiffres.")
     *
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=70, unique=true)
     * @Assert\Email(message="Vous ne respectez pas le format email.")
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $admin;

    /**
     * @ORM\Column(type="boolean")
     */
    private $actif;

    /**
     * @ORM\OneToMany(targetEntity=Sortie::class, mappedBy="organisateur", cascade={"persist","remove"})
     */
    private $sorties;

    /**
     * @ORM\ManyToMany(targetEntity=Sortie::class, mappedBy="participant")
     */
    private $mesSorties;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="estRattache")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
        $this->mesSorties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        if ($this->admin==true){
            $roles[] = 'ROLE_ADMIN';
        }
        else {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;


        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAdmin(): ?bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * @return Collection|Sortie[]
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSorty(Sortie $sorty): self
    {
        if (!$this->sorties->contains($sorty)) {
            $this->sorties[] = $sorty;
            $sorty->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSorty(Sortie $sorty): self
    {
        if ($this->sorties->removeElement($sorty)) {
            // set the owning side to null (unless already changed)
            if ($sorty->getOrganisateur() === $this) {
                $sorty->setOrganisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Sortie[]
     */
    public function getMesSorties(): Collection
    {
        return $this->mesSorties;
    }

    public function addMesSorty(Sortie $mesSorty): self
    {
        if (!$this->mesSorties->contains($mesSorty)) {
            $this->mesSorties[] = $mesSorty;
            $mesSorty->addParticipant($this);
        }

        return $this;
    }

    public function removeMesSorty(Sortie $mesSorty): self
    {
        if ($this->mesSorties->removeElement($mesSorty)) {
            $mesSorty->removeParticipant($this);
        }

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

}
