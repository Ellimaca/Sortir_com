<?php


namespace App\Utils;


use App\Entity\Event;
use App\Entity\User;

class EventLine
{
    private ?Event $event;
    private ?int $nbRegistered;
    private ?string $isRegistered;
    private array $links = [];

    /**
     * @return Event|null
     */
    public function getEvent(): ?Event
    {
        return $this->event;
    }

    /**
     * @param Event|null $event
     */
    public function setEvent(?Event $event): void
    {
        $this->event = $event;
    }

    /**
     * @return int|null
     */
    public function getNbRegistered(): ?int
    {
        return $this->nbRegistered;
    }

    /**
     * @param int|null $nbRegistered
     */
    public function setNbRegistered(?int $nbRegistered): void
    {
        $this->nbRegistered = $nbRegistered;
    }

    /**
     * @return string|null
     */
    public function getIsRegistered(): ?string
    {
        return $this->isRegistered;
    }

    /**
     * @param string|null $isRegistered
     */
    public function setIsRegistered(?string $isRegistered): void
    {
        $this->isRegistered = $isRegistered;
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param array $links
     */
    public function setLinks(array $links): void
    {
        $this->links = $links;
    }

    public function updateLinks(?User $user)
    {
        $this->links = [];

        switch ($this->event->getStatus()->getName()) {
            case CREATED:
                $this->links[] = ['modifier', 'event_modified'];
                $this->links[] = ['publier', 'event_published'];
                break;
            case OPENED:
                $this->links[] = ['afficher', 'event_view'];

                if ($this->event->getOrganiser() == $user) {
                    $this->links[] = ['modifier', 'event_modified'];
                    $this->links[] = ['annuler', 'event_cancelled'];
                } elseif ($this->nbRegistered < $this->event->getMaxNumberParticipants() &&
                    !$this->event->getParticipants()->contains($user)) {
                    $this->links[] = ["s'inscrire", 'event_registration'];
                }

                if ($this->event->getParticipants()->contains($user)) {
                    $this->links[] = ["se désinscrire", 'event_abandonned'];
                }
                break;
            case CLOSED:
                $this->links[] = ['afficher', 'event_view'];

                if ($this->event->getOrganiser() == $user) {
                    $this->links[] = ['modifier', 'event_modified'];
                    $this->links[] = ['annuler', 'event_cancelled'];
                }

                if ($this->event->getParticipants()->contains($user)) {
                    $this->links[] = ["se désinscrire", 'event_abandonned'];
                }
                break;
            case ONGOING:
            case FINISHED:
            case CANCELLED:
            case ARCHIVED:
                $this->links[] = ['afficher', 'event_view'];
                break;
        }

    }
}