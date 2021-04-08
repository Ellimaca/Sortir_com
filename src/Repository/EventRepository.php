<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Status;
use App\Utils\SearchEventCriterias;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Type;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    private $status_published;

    public function __construct(ManagerRegistry $registry,StatusRepository $statusRepository)
    {
        parent::__construct($registry, Event::class);

        $this->status_published = $statusRepository->findOneBy(["name"=>"Créée"]);
    }

    public function findBySearchFormCriteria(SearchEventCriterias $criterias){
        $queryBuilder = $this->createQueryBuilder('events');


        //test sur le critère du campus
        if(!is_null($criterias->getCampus())){
            $campus = $criterias->getCampus()->getId();
            $queryBuilder
                ->andWhere('events.campus = :campusEvent')
                ->setParameter('campusEvent',$campus,Type::INTEGER);
        }

        //test sur le critère de date de début
        if(!is_null($criterias->getDateStart())){
            $queryBuilder
                ->andWhere('events.dateTimeStart >= :dateStart')
                ->setParameter('dateStart',$criterias->getDateStart());
        }

        //test sur le critère de date de fin
        if(!is_null($criterias->getDateEnd())){
            $queryBuilder
                ->andWhere('events.dateTimeStart <= :dateEnd')
                ->setParameter('dateEnd',$criterias->getDateEnd());
        }

        //test sur le critère d'evenement organiser par l'utilisateur
        if($criterias->getIsOrganisedByMe() == true){
            $queryBuilder
                ->andWhere('events.organiser = :user')
                ->setParameter('user',$criterias->getUser());
        }else{
            $status =$this->status_published->getId();
            $queryBuilder
                ->andWhere('events.status != :status')
                ->setParameter('status',$status,Type::INTEGER);
        }

        //test sur le critère d'evenement où l'utilisateur est inscrit
        if($criterias->getIsAttendedByMe() == true){
            $queryBuilder
                ->andWhere(':user in ( events.participants )')
                ->setParameter('user',$criterias->getUser());
        }

        //test sur le critère d'evenement où l'utilisateur n'est pas inscrit
        if($criterias->getIsNotAttendedByMe() == true){
            $queryBuilder
                ->andWhere(':user ! in ( events.participants )')
                ->setParameter('user',$criterias->getUser());
        }
        $query= $queryBuilder->getQuery();
        return $query->getResult();
    }

/**
    public function findUsersForEvent (Event $event):array{

        $qb = $this->createQueryBuilder('e');



    }/*
    // /**
    //  * @return Event[] Returns an array of Event objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Event
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
