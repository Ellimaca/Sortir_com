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
            case Constantes::CREATED:
                $this->links[] = Constantes::EVENT_MODIFY;
                $this->links[] = Constantes::EVENT_PUBLISH;
                break;
            case Constantes::OPENED:
                $this->links[] = Constantes::EVENT_SHOW;

                if ($this->event->getOrganiser() === $user) {
                    $this->links[] = Constantes::EVENT_MODIFY;
                    $this->links[] = Constantes::EVENT_CANCEL;
                } elseif ($this->nbRegistered < $this->event->getMaxNumberParticipants() &&
                    !$this->event->getParticipants()->contains($user)) {
                    $this->links[] = Constantes::EVENT_REGISTER;
                }

                if ($this->event->getParticipants()->contains($user)) {
                    $this->links[] = Constantes::EVENT_ABANDON;
                }
                break;
            case Constantes::CLOSED:
                $this->links[] = Constantes::EVENT_SHOW;

                if ($this->event->getOrganiser() === $user) {
                    $this->links[] = Constantes::EVENT_MODIFY;
                    $this->links[] = Constantes::EVENT_CANCEL;
                }

                if ($this->event->getParticipants()->contains($user)) {
                    $this->links[] = Constantes::EVENT_ABANDON;
                }
                break;
            case Constantes::ONGOING:
            case Constantes::FINISHED:
            case Constantes::CANCELLED:
            case Constantes::ARCHIVED:
                $this->links[] = Constantes::EVENT_SHOW;
                break;
        }

    }
}