<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Place;
use App\Entity\User;
use App\Form\EventType;
use App\Form\PlaceType;
use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use App\Utils\Constantes;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\DateTimeHandler;

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
    public function create (\Symfony\Component\HttpFoundation\Request $request, EntityManagerInterface $entityManager, StatusRepository $statusRepository) {

        $event = new Event();

        /** @var User $user */
        $user = $this->getUser();

        // L'utilisateur connecté est l'organisateur
        $event->setOrganiser($user);

        $eventForm = $this->createForm(EventType::class, $event);

        $eventForm->handleRequest($request);

        if($eventForm->isSubmitted() && $eventForm->isValid()) {

            $startDate = $event->getDateTimeStart();
            $dateTimeEnd = clone $startDate;
            $intervalDuration = $event->getDuration();

            $dateTimeEnd->add(new DateInterval('PT' . $intervalDuration . 'M'));
            $event->setDateTimeEnd($dateTimeEnd);

            $status = $statusRepository->findOneBy(["name" => Constantes::OPENED]);
            $event->setStatus($status);

            if ($eventForm->get('save')->isClicked()) {
                $status = $statusRepository->findOneBy(["name" => Constantes::CREATED]);
                $event->setStatus($status);
            }

            $entityManager->persist($event);
            $entityManager->flush();


            $this->addFlash('success', 'Votre évènement a bien été ajouté !');
            return $this->redirectToRoute('main');
        }

        /**
        $place = new Place();
        $placeForm = $this->createForm(PlaceType::class, $place);
        $placeForm->handleRequest($request);
        if($placeForm->isSubmitted() && $placeForm->isValid()) {
            $placeCity = $placeForm->get('city')->getData();
            $place->setCity($placeCity);

            $entityManager->persist($place);
            $entityManager->flush();

            $this->addFlash('success', 'Votre lieu a bien été ajouté !');

        }
        $event->setPlace($place);

         */

        return $this->render("event/create.html.twig", [
            'eventForm' => $eventForm->createView(),
            //'placeForm' => $placeForm->createView()
        ]);
    }

    /**
     * Permet de s'inscrire à un évènement
     * @Route("/evenement{id}/inscription", name="event_registration")
     */
    public function registration($id,
                                 EventRepository $eventRepository,
                                 EntityManagerInterface $manager): Response
    {

        /** @var User $user */
        $user = $this->getUser();

        //Je récupère l'évenement choisi par mon utilisateur via l'id récupérée dans l'URL
        $eventChoosen = $eventRepository->find($id);

        //Je récupère tous les participants à cet event et vérifie que mon user n'est pas déjà inscrit !
        $participantsTothisEvent = $eventChoosen->getParticipants();

        //TODO: Ne pas persister si le user est déjà inscrit !!!

        foreach($participantsTothisEvent as $participant) {
            if ($participant->getId() == $user->getId()) {
                $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie!');
                $this->redirectToRoute('main');
            }
        }

        //et le nombre d'inscrits à la sortie
        $foundParticipants = $eventChoosen->getParticipants();

        //Vérifier que le statut de l'event soit "Ouverte"
        if($eventChoosen->getStatus()->getName() != 'Ouverte') {
            $this->addFlash('warning', "Cette sortie n'est plus ouverte à l'inscription");
            $this->redirectToRoute('main');

            //Vérifier que la RegistrationDeadLine ne soit pas dépassée
        } if ($eventChoosen->getRegistrationDeadline() <= new \DateTime('now')) {
            $this->addFlash('warning', "L'inscription à cette sortie est terminée");
            $this->redirectToRoute('main');


        //Vérifier si il reste des places libres
        } if ($eventChoosen->getParticipants()->count() == $eventChoosen->getMaxNumberParticipants()) {
            $this->addFlash('warning', "Le nombre maximum de participants a été atteint !");
            $this->redirectToRoute('main');

        } else {
        //Si toutes les conditions sont remplies pour que l'inscription puisse être faite,
        // on inscrit notre user à la sortie

        $eventChoosen->addParticipant($user);

        $manager->persist($eventChoosen);
        $manager->flush();

        $this->addFlash('success', "Vous êtes bien inscrit à la sortie!");
        $this->redirectToRoute('main');

    }

        return $this->render("event/view.html.twig", [
            "foundEvent" => $eventChoosen,
            "foundParticipants" => $foundParticipants
        ]);
    }

    /**
     * @Route("/evenement{id}/desistement", name="event_abandonned")
     */
    public function abandon ($id, EventRepository $eventRepository,
                          EntityManagerInterface $manager): Response
    {



        return $this->render("", [
        ]);
    }

    /**
     * @Route("/evenement{id}/annuler", name="event_cancelled")
     */
    public function cancel ($id, EventRepository $eventRepository,
                             EntityManagerInterface $manager): Response
    {



        return $this->render("", [
        ]);
    }

    /**
     * @Route("/evenement{id}/modifier", name="event_modified")
     */
    public function modify ($id, EventRepository $eventRepository,
                             EntityManagerInterface $manager): Response
    {



        return $this->render("", [
        ]);
    }

    /**
     * @Route("/evenement{id}/publier", name="event_published")
     */
    public function publish ($id, EventRepository $eventRepository,
                            EntityManagerInterface $manager): Response
    {
        return $this->render("", [
        ]);
    }
}
