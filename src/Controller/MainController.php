<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SearchEventsType;
use App\Repository\EventRepository;
use App\Utils\FunctionsStatus;
use App\Utils\SearchEventCriterias;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{


    /**
     * @Route("/main", name="main")
     */
    public function index(Request $request,
                          EventRepository $eventRepository,
                          SearchEventCriterias $searchEventCriterias): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $searchEventCriterias = new SearchEventCriterias();

        $searchEventCriterias->setUser($user);
        //$searchEventCriterias->setCampus($user->getCampus());

        $searchForm = $this->createForm(SearchEventsType::class,$searchEventCriterias);

        $searchForm->handleRequest($request);

        $eventsList = $eventRepository->findBySearchFormCriteria($searchEventCriterias);

        /*//Création de la map associant le nombre de participant à l'event
        $mapNbParticipantsByEvent = new Map();

        foreach ($eventsList as $event){
            $mapNbParticipantsByEvent->set($event->getId(),count($event->getParticipants()));
        }*/

        $functionsStatus = FunctionsStatus::getInstance();
        $functionsStatus->UpdateEventsStatus($eventsList);

      return $this->render('main/index.html.twig', [
            'searchForm' => $searchForm->createView(),
            'eventsList' => $eventsList,
            //'mapNbParticipantsByEvent' => $mapNbParticipantsByEvent
        ]);
    }
}
