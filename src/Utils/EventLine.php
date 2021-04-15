<?php


namespace App\Utils;


use App\Entity\Event;
use App\Entity\User;

class EventLine
{
    private const EVENT_SHOW = ['afficher', 'event_view'];
    private const EVENT_MODIFY = ['modifier', 'event_modified'];
    private const EVENT_CANCEL = ['annuler', 'event_cancelled'];
    private const EVENT_ABANDON =["se désinscrire", 'event_abandonned'];
    private const EVENT_REGISTER =["s'inscrire", 'event_registration'];
    private const EVENT_PUBLISH =['publier', 'event_published'];

    private ?Event $event;
    private ?int $nbRegistered;
    private ?bool $isRegistered;
    private array $links = [];
    private ?bool $full;

    /**
     * @return bool|null
     */
    public function getFull(): ?bool
    {
        if(count($this->event->getParticipants()) >= $this->event->getMaxNumberParticipants()){
            $this->setFull(true);
        }else{
            $this->setFull(false);
        }

        return $this->full;
    }

    /**
     * @param bool|null $full
     */
    public function setFull(?bool $full): void
    {
        $this->full = $full;
    }

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
     * @return bool|null
     */
    public function getIsRegistered(): ?bool
    {
        return $this->isRegistered;
    }

    /**
     * @param bool|null $isRegistered
     */
    public function setIsRegistered(?bool $isRegistered): void
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
                $this->links[] = self::EVENT_MODIFY;
                $this->links[] = self::EVENT_PUBLISH;
                break;
            case Constantes::OPENED:
                $this->links[] = self::EVENT_SHOW;

                if ($this->event->getOrganiser() === $user) {
                    $this->links[] = self::EVENT_MODIFY;
                    $this->links[] = self::EVENT_CANCEL;
                } elseif ($this->nbRegistered < $this->event->getMaxNumberParticipants() &&
                    !$this->event->getParticipants()->contains($user)) {
                    $this->links[] = self::EVENT_REGISTER;
                }

                if ($this->event->getParticipants()->contains($user)) {
                    $this->links[] = self::EVENT_ABANDON;
                }
                break;
            case Constantes::CLOSED:
                $this->links[] = self::EVENT_SHOW;

                if ($this->event->getOrganiser() === $user) {
                    $this->links[] = self::EVENT_MODIFY;
                    $this->links[] = self::EVENT_CANCEL;
                }

                if ($this->event->getParticipants()->contains($user)) {
                    $this->links[] = self::EVENT_ABANDON;
                }
                break;
            case Constantes::ONGOING:
            case Constantes::FINISHED:
            case Constantes::CANCELLED:
            case Constantes::ARCHIVED:
                $this->links[] = self::EVENT_SHOW;
                break;
        }

    }
}