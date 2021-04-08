<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SearchEventsType;
use App\Repository\EventRepository;
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
    public function index(Request $request,EventRepository $eventRepository,SearchEventCriterias $searchEventCriterias): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $searchEventCriterias->setUser($user);
        $searchEventCriterias = new SearchEventCriterias($user);

        $searchForm = $this->createForm(SearchEventsType::class,$searchEventCriterias);

        //Recherche par defaut
        if(!$searchForm->isSubmitted()){
            $searchEventCriterias->setCampus($user->getCampus());
        }

        //var_dump($user);

        $searchForm->handleRequest($request);

        $eventsList = $eventRepository->findBySearchFormCriteria($searchEventCriterias);

        return $this->render('main/index.html.twig', [
            'searchForm' => $searchForm->createView(),
            'eventsList' => $eventsList
        ]);
    }
}
