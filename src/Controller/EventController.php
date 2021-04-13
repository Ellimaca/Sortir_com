<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use App\Utils\Constantes;
use App\Utils\FunctionsStatus;
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
        /** @var User $user */
        $user = $this->getUser();
        $foundEvent = $eventRepository->findOneBy(['id' => $id]);

        if (!$foundEvent || ($foundEvent->getStatus()->getName() == Constantes::CREATED and $foundEvent->getOrganiser() !== $user || $foundEvent->getStatus()->getName() == Constantes::ARCHIVED)) {
            throw $this->createNotFoundException("Cet évenement n'existe pas");
        } else {
            $foundParticipants = $foundEvent->getParticipants();
            return $this->render('event/view.html.twig', ['foundEvent' => $foundEvent, 'foundParticipants' => $foundParticipants]);
        }
    }

    /**
     * Permet de créer un évènement
     * @Route ("/evenement/creer/", name="event_creation")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     */
    public function create (\Symfony\Component\HttpFoundation\Request $request, EntityManagerInterface $entityManager, StatusRepository $statusRepository)
    {

        // Creation d'un nouvel event
        $event = new Event();

        // Récupération de l'utilisateur connecté
        /** @var User $user */
        $user = $this->getUser();

        // L'utilisateur connecté est défini comme organisateur
        $event->setOrganiser($user);

        // On utilise le campus de l'utilisateur connecté dans le formulaire
        $event->setCampus($user->getCampus());

        // Création du formulaire
        $eventForm = $this->createForm(EventType::class, $event);

        $eventForm->handleRequest($request);

        if($eventForm->isSubmitted() && $eventForm->isValid()) {

            // Calcul de la date de fin de l'évènement
            /** @var \DateTime $startDate */
            $startDate = $event->getDateTimeStart();
            $intervalDuration = $event->getDuration();
            $dateTimeEnd = DateTimeHandler::dateAddMinutes($startDate, $intervalDuration);
            $event->setDateTimeEnd($dateTimeEnd);

            $place = $eventForm->get('place')->getData();
            $event->setPlace($place);

            // La sortie est "ouverte" si l'utilisateur clique sur "publier la sortie"
            $status = $statusRepository->findOneBy(["name" => Constantes::OPENED]);
            $event->setStatus($status);

            // La sortie est "crée" si l'utilisateur clique sur "enregistrer"
            if ($eventForm->get('save')->isClicked()) {
                $status = $statusRepository->findOneBy(["name" => Constantes::CREATED]);
                $event->setStatus($status);
            }

            $entityManager->persist($event);
            $entityManager->flush();


            $this->addFlash('success', 'Votre évènement a bien été ajouté !');
            return $this->redirectToRoute('main');
        }

        // Bloc à décommenter au moment de l'ajout de la fonctionnalité 'ajouter un lieu'
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
     * @Route("/evenement/inscription/{id}", name="event_registration")
     */
    public function registration($id,
                                 EventRepository $eventRepository,
                                 EntityManagerInterface $manager,
                                 FunctionsStatus $functionsStatus): Response
    {

        /** @var User $user */
        $user = $this->getUser();

        //Je récupère l'évenement choisi par mon utilisateur via l'id récupérée dans l'URL
        $eventChoosen = $eventRepository->find($id);

        //Les inscrits à la sortie
        $foundParticipants = $eventChoosen->getParticipants();

        //Mettre à jour le statut au moment où la personne s'inscrit
        $functionsStatus->UpdateEventStatus($eventChoosen);

        $eventStatusName = $eventChoosen->getStatus()->getName();

        switch ($eventStatusName) {

            case Constantes::CREATED :
                $this->addFlash('warning', "Cette sortie n'est pas encore publiée!");
                $this->redirectToRoute('main');
                break;
            case Constantes::ARCHIVED :
                $this->addFlash('warning', "Cette sortie est archivée!");
                $this->redirectToRoute('main');
                break;
            //Si sorties annulées...
            case Constantes::CANCELLED :
                $this->addFlash('warning', "Cette sortie a été annulée!");
                $this->redirectToRoute('main');
                break;
            //Si sorties fermées à l'inscription...
            case Constantes::CLOSED :
                $this->addFlash('warning', "La date limite d'inscription est dépassée");
                $this->redirectToRoute('main');
                break;
            //Si sorties se déroulent au moment de l'inscription...
            case Constantes::ONGOING :
                $this->addFlash('warning', "Impossible de s'inscrire, l'activité se déroule en ce moment-même!");
                $this->redirectToRoute('main');
                break;
            //Si la sortie est déjà passée...
            case Constantes::FINISHED :
                $this->addFlash('warning', "La sortie est finie!");
                $this->redirectToRoute('main');
                break;
            //Si le statut de la sortie est "Ouverte"...
            case Constantes::OPENED :
            //Si l'utilisateur est l'organisateur de la sortie...
                if($eventChoosen->getOrganiser() === $user) {
                    $this->addFlash('warning', 'Vous avez crée la sortie, vous ne pouvez pas vous inscrire!');
                }
                //Si l'utilisateur est déjà inscrit à la sortie...
                elseif($foundParticipants->contains($user)) {
                    $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie!');
                }

                //... On vérifie que le nombre max de participants n'est pas atteint
                elseif ($eventChoosen->getParticipants()->count() >= $eventChoosen->getMaxNumberParticipants()) {
                    $this->addFlash('warning', 'Le nombre maximum de participants a été atteint!');
                }

                else {
                    //Si toutes les conditions sont remplies, on ajoute notre user à la sortie
                    $eventChoosen->addParticipant($user);

                    $manager->persist($eventChoosen);
                    $manager->flush();

                    $this->addFlash('success', "Vous êtes bien inscrit à la sortie!");
                }

                $this->redirectToRoute('main');
                break;

        }

        return $this->render("event/view.html.twig", [
            "foundEvent" => $eventChoosen,
            "foundParticipants" => $foundParticipants
        ]);
    }

    /**
     * @Route("/evenement/desistement{id}", name="event_abandonned")
     */
    public function abandon ($id, EventRepository $eventRepository,
                          EntityManagerInterface $manager): Response
    {



        return $this->render("", [
        ]);
    }

    /**
     * @Route("/evenement/annuler{id}", name="event_cancelled")
     */
    public function cancel ($id, EventRepository $eventRepository,
                             EntityManagerInterface $manager): Response
    {



        return $this->render("", [
        ]);
    }

    /**
     * @Route("/evenement/modifier{id}", name="event_modified")
     */
    public function modify ($id, EventRepository $eventRepository,
                             EntityManagerInterface $manager): Response
    {



        return $this->render("", [
        ]);
    }

    /**
     * @Route("/evenement/publier{id}", name="event_published")
     */
    public function publish ($id, EventRepository $eventRepository,
                            EntityManagerInterface $manager): Response
    {
        return $this->render("", [
        ]);
    }
}
