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
 * @UniqueEntity("pseudo", message="Le pseudo est déjà utilisé")
 * @UniqueEntity("email", message="Le mail est déjà utilisé")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @Assert\NotBlank
     * @Assert\Email(message="Veuillez insérer un format d'email valide. ")
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private ?string $email;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var ?string The hashed password
     * @ORM\Column(type="string")
     */
    private ?string $password;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner votre prénom")
     * @Assert\Length(
     *     min=2,
     *     max=40,
     *     minMessage="Le prénom doit être d'au moins 2 caractères",
     *     maxMessage="Le prénom ne doit pas excéder 40 caractères"
     * )
     * @Assert\Regex(pattern="^[A-Za-zàâçéèêëîïôûùüÿñæœ' -]*$",
     * message="Le format du prénom n'est pas valide. Caractères autorisés : a-z, ', -, ")
     * @ORM\Column(type="string", length=40)
     */
    private ?string $firstName;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner votre nom")
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="Le nom doit être d'au moins 2 caractères",
     *     maxMessage="Le nom ne doit pas excéder 50 caractères"
     * )
     * @Assert\Regex(pattern="^[A-Za-zàâçéèêëîïôûùüÿñæœ' -]*$",
     * message="Le format du prénom n'est pas valide. Caractères autorisés : a-z, ', -, ")
     * @ORM\Column(type="string", length=50)
     */
    private ?string $lastName;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner votre numéro de téléphone")
     * @Assert\Regex(pattern="^(?:(?:(?:\+|00)33[ ]?(?:\(0\)[ ]?)?)|0){1}[1-9]{1}([ .-]?)(?:\d{2}\1?){3}\d{2}$^",
     * message="Veuillez entrer un format de téléphone valide")
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $phoneNumber;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $isAdmin;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $isActive;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     */
    private ?Campus $campus;

    /**
     * @ORM\ManyToMany(targetEntity=Event::class, mappedBy="participants")
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity=Event::class, mappedBy="organiser")
     */
    private $organisedEvents;

    /**
     * @Assert\NotBlank(message="Veuillez choisir un pseudo")
     * @Assert\Length(
     *     min=4,
     *     max=20,
     *     minMessage="Le pseudo doit contenir au moins 4 caractères",
     *     maxMessage="Le pseudo ne peut pas excéder 20 caractères"
     * )
     * @Assert\Regex(pattern="^[a-zA-Z0-9]+$^",
     * message="Seulement les lettres et les chiffres sont acceptés, pas d'accents ou de caractères spéciaux autorisés")
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private ?string $pseudo;

    /**
     * @ORM\OneToOne(targetEntity=ProfilePicture::class, inversedBy="user", cascade={"persist", "remove"})
     */
    private ?ProfilePicture $profilePicture;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->organisedEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

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
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getIsAdmin(): ?bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

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

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        $this->events->removeElement($event);

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getOrganisedEvents(): Collection
    {
        return $this->organisedEvents;
    }

    public function addOrganisedEvent(Event $organisedEvent): self
    {
        if (!$this->organisedEvents->contains($organisedEvent)) {
            $this->organisedEvents[] = $organisedEvent;
            $organisedEvent->setOrganiser($this);
        }

        return $this;
    }

    public function removeOrganisedEvent(Event $organisedEvent): self
    {
        if ($this->organisedEvents->removeElement($organisedEvent)) {
            // set the owning side to null (unless already changed)
            if ($organisedEvent->getOrganiser() === $this) {
                $organisedEvent->setOrganiser(null);
            }
        }

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getProfilePicture(): ?ProfilePicture
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?ProfilePicture $profilePicture): self
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }
}
