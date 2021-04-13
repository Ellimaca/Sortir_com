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
     */
    public function UpdateEventStatus(Event $event){
        $events[] = $event;
        $this->UpdateEventsStatus($events);
    }

    /**
     * Optimisation d'UpdateStatus pour limiter les appels à la base en traitant une collection d'events
     * @param $events
     */
    public function UpdateEventsStatus($events){
        $statusList = $this->repository->findAll();

        foreach ($events as $event){
            $this->UpdateStatus($event,$statusList);
        }
    }

    /**
     * fonction permettant de mettre à jour le status d'un event
     * @param Event $event
     * @param $statusList
     */
    public function UpdateStatus(Event $event,$statusList){

        /** @var DateTime $dateStart */
        $dateStart = $event->getDateTimeStart();
        /** @var DateTime $dateEnd */
        $dateEnd = $event->getDateTimeEnd();
        /** @var DateTime $deadline */
        $deadline = $event->getRegistrationDeadline();
        /** @var string $status */
        $status = $event->getStatus()->getName();

        var_dump($dateStart);
        var_dump($dateEnd);
        var_dump($deadline);
        var_dump($status);

        if($status == Constantes::OPENED){
            if($deadline >= new DateTime('now')){
                $event->setStatus(self::getStatusByName(Constantes::CLOSED,$statusList));
            }
        }

        if($status == Constantes::CLOSED) {
            if($dateStart >= new DateTime('now')){
                $event->setStatus(self::getStatusByName(Constantes::ONGOING, $statusList));
            }
        }

        if($status == Constantes::ONGOING) {
            if($dateEnd >= new DateTime('now')){
                $event->setStatus(self::getStatusByName(Constantes::FINISHED, $statusList));
            }
        }

        if($status == Constantes::FINISHED) {
            if(date_diff($dateEnd,new DateTime('now'))->m >= 1){
                $event->setStatus(self::getStatusByName(Constantes::ARCHIVED, $statusList));
            }
        }

        if($status == Constantes::CANCELLED) {
            if(date_diff($dateEnd,new DateTime('now'))->m >= 1){
                $event->setStatus(self::getStatusByName(Constantes::ARCHIVED, $statusList));
            }
        }

        $this->entityManager->persist($event);
        $this->entityManager->flush();

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