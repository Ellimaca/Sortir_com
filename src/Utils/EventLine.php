<?php


namespace App\Utils;


use App\Entity\Event;
use App\Entity\User;


define('CREATED', 'Créée');
define('OPENED' , 'Ouverte');
define('CLOSED' , 'Clôturée');
define('ONGOING' , 'Activité en cours');
define('FINISHED' , 'Passée');
define('CANCELLED' , 'Annulée');
define('ARCHIVED' , 'Archivée');

define('EVENT_SHOW',['afficher', 'event_view']);
define('EVENT_MODIFY',['modifier', 'event_modified']);
define('EVENT_CANCEL',['annuler', 'event_cancelled']);
define('EVENT_ABANDON',["se désinscrire", 'event_abandonned']);
define('EVENT_REGISTER',["s'inscrire", 'event_registration']);
define('EVENT_PUBLISH',['publier', 'event_published']);

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
                $this->links[] = EVENT_MODIFY;
                $this->links[] = EVENT_PUBLISH;
                break;
            case OPENED:
                $this->links[] = EVENT_SHOW;

                if ($this->event->getOrganiser() == $user) {
                    $this->links[] = EVENT_MODIFY;
                    $this->links[] = EVENT_CANCEL;
                } elseif ($this->nbRegistered < $this->event->getMaxNumberParticipants() &&
                    !$this->event->getParticipants()->contains($user)) {
                    $this->links[] = EVENT_REGISTER;
                }

                if ($this->event->getParticipants()->contains($user)) {
                    $this->links[] = EVENT_ABANDON;
                }
                break;
            case CLOSED:
                $this->links[] = EVENT_SHOW;

                if ($this->event->getOrganiser() == $user) {
                    $this->links[] = EVENT_MODIFY;
                    $this->links[] = EVENT_CANCEL;
                }

                if ($this->event->getParticipants()->contains($user)) {
                    $this->links[] = EVENT_ABANDON;
                }
                break;
            case ONGOING:
            case FINISHED:
            case CANCELLED:
            case ARCHIVED:
                $this->links[] = EVENT_SHOW;
                break;
        }

    }
}