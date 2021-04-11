<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Veuillez donner un nom à votre sortie")
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="Le nom de l'évènement doit être d'au moins 2 caractères",
     *     maxMessage="Le nom de l'évènement pas excéder 100 caractères")
     * @Assert\Regex(pattern="^[a-zA-ZàáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçčšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆČŠŽ∂ð ,.'-]+$^",
     * message="Le format du nom de votre sortie n'est pas valide")
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner la date et l'heure de votre sortie")
     * @ORM\Column(type="datetime")
     */
    private $dateTimeStart;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateTimeEnd;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner une date limite d'inscription")
     * @ORM\Column(type="datetime")
     */
    private $registrationDeadline;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner un nombre maximum de participants")
     * @ORM\Column(type="integer")
     */
    private $maxNumberParticipants;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner une description à votre sortie")
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=Place::class, inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private $place;

    /**
     * @ORM\ManyToOne(targetEntity=Status::class, inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campus;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="events")
     */
    private $participants;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="organisedEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organiser;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner une durée pour votre sortie")
     * @ORM\Column(type="integer")
     */
    private $duration;


    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDateTimeStart(): ?\DateTimeInterface
    {
        return $this->dateTimeStart;
    }

    public function setDateTimeStart(?\DateTimeInterface $dateTimeStart): self
    {
        $this->dateTimeStart = $dateTimeStart;

        return $this;
    }

    public function getDateTimeEnd(): ?\DateTimeInterface
    {
        return $this->dateTimeEnd;
    }

    public function setDateTimeEnd(\DateTimeInterface $dateTimeEnd): self
    {
        $this->dateTimeEnd = $dateTimeEnd;

        return $this;
    }

    public function getRegistrationDeadline(): ?\DateTimeInterface
    {
        return $this->registrationDeadline;
    }

    public function setRegistrationDeadline(?\DateTimeInterface $RegistrationDeadline): self
    {
        $this->registrationDeadline = $RegistrationDeadline;

        return $this;
    }

    public function getMaxNumberParticipants(): ?int
    {
        return $this->maxNumberParticipants;
    }

    public function setMaxNumberParticipants(int $maxNumberParticipants): self
    {
        $this->maxNumberParticipants = $maxNumberParticipants;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

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
     * @return Collection|User[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function removeParticipant(User $participant): self
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getOrganiser(): ?User
    {
        return $this->organiser;
    }

    public function setOrganiser(?User $organiser): self
    {
        $this->organiser = $organiser;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

}
