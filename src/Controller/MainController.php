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
        $searchEventCriterias = new SearchEventCriterias();
        $searchEventCriterias->setUser($user);

        $searchForm = $this->createForm(SearchEventsType::class,$searchEventCriterias);

        $searchForm->handleRequest($request);

        var_dump('Test');
        $eventsList = $eventRepository->findBySearchFormCriteria($searchEventCriterias);

      return $this->render('main/index.html.twig', [
            'searchForm' => $searchForm->createView(),
            'eventsList' => $eventsList
        ]);
    }
}
