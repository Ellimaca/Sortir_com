<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SearchEventsType;
use App\Repository\EventRepository;
use App\Utils\EventLine;
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
                          FunctionsStatus $functionsStatus): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $searchEventCriterias = new SearchEventCriterias();

        $searchEventCriterias->setUser($user);
        $searchEventCriterias->setCampus($user->getCampus());

        $searchForm = $this->createForm(SearchEventsType::class,$searchEventCriterias);

        $searchForm->handleRequest($request);

        $eventsList = $eventRepository->findBySearchFormCriteria($searchEventCriterias);

        //Mis à jour des status
        $functionsStatus->UpdateEventsStatus($eventsList);

        //Création du tableau d'objet EventLine
        $eventLines = [];

        //Hydratation du tableau
        foreach ($eventsList as $event){
            $eventLine = new EventLine();
            $eventLine->setEvent($event);
            $eventLine->setNbRegistered(count($event->getParticipants()));
            //$mapNbParticipantsByEvent[$event->getId()] = count($event->getParticipants());
            if ($event->getParticipants()->contains($user)){
                //$mapIsRegisteredByEvent[$event->getId()] = 'X';
                $eventLine->setIsRegistered('X');
            }else{
                //$mapIsRegisteredByEvent[$event->getId()] = '';
                $eventLine->setIsRegistered('');
            }
            $eventLine->updateLinks($user);

            $eventLines[] = $eventLine;
        }

      return $this->render('main/index.html.twig', [
            'searchForm' => $searchForm->createView(),
            'eventLines' => $eventLines,
        ]);
    }


}
