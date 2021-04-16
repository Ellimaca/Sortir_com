<?php

namespace App\Entity;

use App\Repository\EventRepository;
use DateTimeInterface;
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
    private ?int $id;

    /**
     * @Assert\NotBlank(message="Veuillez donner un nom à votre sortie")
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="Le nom de l'évènement doit être d'au moins 2 caractères",
     *     maxMessage="Le nom de l'évènement pas excéder 50 caractères")
     * @ORM\Column(type="string", length=100)
     */
    private ?string $name;

    /**
     * @Assert\GreaterThan("today", message="Veuillez saisir une date postérieure à la date actuelle")
     * @Assert\GreaterThan("+1 hour", message="Votre sortie doit se dérouler au minimum 1h après la date actuelle")
     * @Assert\NotBlank(message="Veuillez renseigner la date et l'heure de votre sortie")
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $dateTimeStart;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $dateTimeEnd;

    /**
     * @Assert\LessThan(propertyPath="dateTimeStart", message="Veuillez saisir une date de fin d'inscription avant la date et l'heure de début de votre sortie")
     * @Assert\GreaterThan("now", message="La date de limite d'inscription ne peut pas être inférieure à la date d'aujourd'hui")
     * @Assert\NotBlank(message="Veuillez renseigner une date limite d'inscription")
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $registrationDeadline;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner un nombre maximum de participants")
     * @Assert\GreaterThan(2, message="Le minimum de participants est de 2")
     * @Assert\LessThan(7, message=" Le nombre maximum de 6 participants doit être respecté")
     *
     * @ORM\Column(type="integer")
     */
    private ?int $maxNumberParticipants;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner une description à votre sortie")
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description;

    /**
     * @ORM\ManyToOne(targetEntity=Place::class, inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Place $place;

    /**
     * @ORM\ManyToOne(targetEntity=Status::class, inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Status $status;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Campus $campus;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="events")
     */
    private ArrayCollection $participants;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="organisedEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $organiser;

    //TO DO mettre une durée minimum et maximum
    /**
     * @Assert\NotBlank(message="Veuillez renseigner une durée pour votre sortie")
     * @Assert\GreaterThan(15, message="La sortie doit au moins durer 15 minutes")
     * @Assert\LessThan(1440, message="La sortie ne peut pas excéder 24h")
     * @ORM\Column(type="integer")
     */
    private ?int $duration;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $cancellation_reason;


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

    public function getDateTimeStart(): ?DateTimeInterface
    {
        return $this->dateTimeStart;
    }

    public function setDateTimeStart(?DateTimeInterface $dateTimeStart): self
    {
        $this->dateTimeStart = $dateTimeStart;

        return $this;
    }

    public function getDateTimeEnd(): ?DateTimeInterface
    {
        return $this->dateTimeEnd;
    }

    public function setDateTimeEnd(DateTimeInterface $dateTimeEnd): self
    {
        $this->dateTimeEnd = $dateTimeEnd;

        return $this;
    }

    public function getRegistrationDeadline(): ?DateTimeInterface
    {
        return $this->registrationDeadline;
    }

    public function setRegistrationDeadline(?DateTimeInterface $RegistrationDeadline): self
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

    public function getCancellationReason(): ?string
    {
        return $this->cancellation_reason;
    }

    public function setCancellationReason(?string $cancellation_reason): self
    {
        $this->cancellation_reason = $cancellation_reason;

        return $this;
    }

}
