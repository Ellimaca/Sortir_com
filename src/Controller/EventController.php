<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    /**
     * @Route("/event", name="event")
     */
    public function index(): Response
    {
        return $this->render('event/index.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }

    /**
     * Permet de visualiser les informations liées à un évenement
     * @Route ("/evenement/consulter/{id}", name="event_view")
     * @param int $id
     * @param EventRepository $eventRepository
     * @return Response
     */
    public function view(int $id, EventRepository $eventRepository): Response
    {
        $foundEvent = $eventRepository->findOneBy(['id' => $id]);
        if (!$foundEvent) {
            throw $this->createNotFoundException("Cet évenement n'existe pas");
        }
        $foundParticipants = $foundEvent->getParticipants();
        return $this->render('event/view.html.twig', ['foundEvent' => $foundEvent, 'foundParticipants' => $foundParticipants]);
    }

    /**
     * Permet de créer un évènement
     * @Route ("/evenement/creer/", name="event_creation")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     */
    public function create (\Symfony\Component\HttpFoundation\Request $request, EntityManagerInterface $entityManager) {

        $event = new Event();

        $user =$this->getUser();
        $event->setOrganiser($user);

        $eventForm = $this->createForm(EventType::class, $event);

        $eventForm->handleRequest($request);

        if($eventForm->isSubmitted() && $eventForm->isValid()) {

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Votre évènement a bien été ajouté');

            return $this->redirectToRoute('main');
        }

        return $this->render("event/create.html.twig", [
            'eventForm' => $eventForm->createView()
        ]);
    }
}
