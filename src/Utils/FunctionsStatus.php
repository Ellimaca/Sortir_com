<?php


namespace App\Utils;


use App\Entity\Event;
use App\Entity\Status;
use App\Repository\StatusRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;


class FunctionsStatus
{
    private EntityManagerInterface $entityManager;
    private StatusRepository $repository;
    
    public function __construct(StatusRepository $repository,EntityManagerInterface $entityManager)
    {
        $this->repository = $repository;
        $this->entityManager=$entityManager;
    }

    /**
     * fonction permettant de mettre à jour le status d'un event
     * @param Event $event
     * @return Event
     */
    public function UpdateEventStatus(Event $event): Event{
        $events[] = $event;
        $events = $this->UpdateEventsStatus($events);
        $event = $events[0];
        return $event;
    }

    /**
     * Optimisation d'UpdateStatus pour limiter les appels à la base en traitant une collection d'events
     * @param $events
     * @return array
     */
    public function UpdateEventsStatus(array $events): array
    {
        $statusList = $this->repository->findAll();

        foreach ($events as $event){
            $this->UpdateStatus($event,$statusList);
        }

        return $events;
    }

    /**
     * fonction permettant de mettre à jour le status d'un event
     * @param Event $event
     * @param $statusList
     * @return Event
     */
    private function UpdateStatus(Event $event,$statusList): Event{

        //date_default_timezone_set ( Constantes::TIME_ZONE);

        /** @var DateTime $dateStart */
        $dateStart = $event->getDateTimeStart();
        /** @var DateTime $dateEnd */
        $dateEnd = $event->getDateTimeEnd();
        /** @var DateTime $deadline */
        $deadline = $event->getRegistrationDeadline();


        if($event->getStatus()->getName() == Constantes::OPENED){
            if($deadline <= new DateTime('now')){
                $event->setStatus(self::getStatusByName(Constantes::CLOSED,$statusList));
            }
        }

        if($event->getStatus()->getName() == Constantes::CLOSED) {
            if($dateStart <= new DateTime('now')){
                $event->setStatus(self::getStatusByName(Constantes::ONGOING, $statusList));
            }elseif ($deadline > new DateTime('now')){
                $event->setStatus(self::getStatusByName(Constantes::OPENED,$statusList));
            }
        }

        if($event->getStatus()->getName() == Constantes::ONGOING) {
            if($dateEnd <= new DateTime('now')){
                $event->setStatus(self::getStatusByName(Constantes::FINISHED, $statusList));
            }
        }

        if($event->getStatus()->getName() == Constantes::FINISHED || $event->getStatus()->getName() == Constantes::CANCELLED) {
            if(date_diff($dateEnd,new DateTime('now'))->m >= 1){
                $event->setStatus(self::getStatusByName(Constantes::ARCHIVED, $statusList));
            }
        }


        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $event;
    }

    /**
     * Fonction permettant de récupérer l'objet Status dans une liste de Statuts en fonction du name
     * ou Null s'il n'existe pas
     *
     * @param string $statusName
     * @param $statusList
     * @return Status|null
     */
    public static function getStatusByName(string $statusName,$statusList):?Status{
        foreach($statusList as $status){
            if($status->getName() == $statusName){
                return $status;
            }
        }
        return null;
    }

}