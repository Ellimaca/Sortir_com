<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SearchEventsType;
use App\Repository\CampusRepository;
use App\Repository\EventRepository;
use App\Utils\SearchEventCriterias;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    private User $user;

    /**
     * @Route("/main", name="main")
     */
    public function index(Request $request,EventRepository $eventRepository,CampusRepository $campusRepository,SearchEventCriterias $searchEventCriterias): Response
    {
        $listCampus = $campusRepository->findBy([],["name"=>"ASC"]);

        $searchEventCriterias = new SearchEventCriterias();

        //TODO trouver un meilleur cast
        $this->user = $this->getUser();
        $userTest = $this->user;

        $searchEventCriterias->setUser($this->user);
        $searchForm = $this->createForm(SearchEventsType::class,$searchEventCriterias);

        $searchForm->handleRequest($request);

        if($searchForm->isSubmitted()){

        }else{
            $searchEventCriterias->setCampus($userTest->getCampus());
        }

        $eventsList = $eventRepository->findBySearchFormCriteria($searchEventCriterias);

        return $this->render('main/index.html.twig', [
            'campusList' => $listCampus,
            'searchForm' => $searchForm->createView(),
            'eventsList' => $eventsList
        ]);
    }
}
