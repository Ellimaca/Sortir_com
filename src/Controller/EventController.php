<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Status;
use App\Entity\User;
use App\Form\EventCancellationType;
use App\Form\EventType;
use App\Repository\CityRepository;
use App\Repository\EventRepository;
use App\Repository\PlaceRepository;
use App\Repository\StatusRepository;
use App\Utils\Constantes;
use App\Utils\FunctionsStatus;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\DateTimeHandler;

class EventController extends AbstractController
{
    const WARNING_EVENT_NOT_AUTHORIZED = "Vous n'êtes pas autorisé à réaliser cette action";
    const WARNING_EVENT_WRONG_STATUS = "Action impossible, statut de la sortie incohérent";
    const SUCCESS_EVENT_PUBLISHED = "La sortie a été publiée avec succès";
    const WARNING_EVENT_WRONG_PLACE = "Le lieu doit être renseigné.";


    /**
     * Permet de visualiser les informations liées à un évenement
     * @Route ("/evenement/consulter/{id}", name="event_view")
     * @param int $id
     * @param EventRepository $eventRepository
     * @return Response
     */
    public function view(int $id,
                         EventRepository $eventRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $foundEvent = $eventRepository->find($id);
        $foundEventStatusName = $foundEvent->getStatus()->getName();

        if (!$foundEvent ||
            ($foundEventStatusName == Constantes::CREATED and
                $foundEvent->getOrganiser() !== $user ||
                $foundEventStatusName == Constantes::ARCHIVED
            )
        ) {
            throw $this->createNotFoundException("Cet évenement n'existe pas");
        } else {
            $foundParticipants = $foundEvent->getParticipants();
            return $this->render('event/view.html.twig', [
                'foundEvent' => $foundEvent,
                'foundParticipants' => $foundParticipants
            ]);
        }
    }

    /**
     * Permet de créer un évènement
     * @Route ("/evenement/creer/", name="event_creation")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param StatusRepository $statusRepository
     * @return RedirectResponse|Response
     */
    public function create(Request $request, EntityManagerInterface $entityManager, StatusRepository $statusRepository)
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

        if ($eventForm->isSubmitted()) {
            $this->havePlace($eventForm);
            if ($eventForm->isValid()) {

                // Calcul de la date de fin de l'évènement
                /** @var DateTime $startDate */
                $startDate = $event->getDateTimeStart();
                $intervalDuration = abs($event->getDuration());
                $dateTimeEnd = DateTimeHandler::dateAddMinutes($startDate, $intervalDuration);
                $event->setDateTimeEnd($dateTimeEnd);

                $place = $eventForm->get('place')->getData();
                $event->setPlace($place);

                $status = $statusRepository->findOneBy(["name" => Constantes::CREATED]);
                $event->setStatus($status);

                $entityManager->persist($event);
                $entityManager->flush();

                $this->addFlash('success', 'Votre sortie a bien été ajoutée !');

                // La sortie est "crée" si l'utilisateur clique sur "enregistrer"
                if ($eventForm->get('save')->isClicked()) {
                    return $this->redirectToRoute('main');
                }

                return $this->redirectToRoute("event_published", ['id' => $event->getId()]);
            }
        }

        return $this->render("event/create.html.twig", [
            'eventForm' => $eventForm->createView(),
            //'placeForm' => $placeForm->createView()
        ]);
    }

    /**
     * Permet de s'inscrire à un évènement
     * @Route("/evenement/inscription/{id}", name="event_registration")
     * @param $id
     * @param EventRepository $eventRepository
     * @param EntityManagerInterface $manager
     * @param FunctionsStatus $functionsStatus
     * @return Response
     */
    public function registration($id,
                                 EventRepository $eventRepository,
                                 EntityManagerInterface $manager,
                                 FunctionsStatus $functionsStatus): Response
    {

        /** @var User $user */
        $user = $this->getUser();

        //Récupération de l'évenement choisi par mon utilisateur via l'id récupérée dans l'URL
        $eventChoosen = $eventRepository->find($id);

        //Les inscrits à la sortie
        $foundParticipants = $eventChoosen->getParticipants();

        //Mise à jour du statut au moment où la personne s'inscrit
        $functionsStatus->UpdateEventStatus($eventChoosen);

        $eventStatusName = $eventChoosen->getStatus()->getName();

        switch ($eventStatusName) {

            case Constantes::CREATED :
                $this->addFlash('warning', "Cette sortie n'est pas encore publiée!");
                return $this->redirectToRoute('main');
            case Constantes::ARCHIVED :
                $this->addFlash('warning', "Cette sortie est archivée!");
                return $this->redirectToRoute('main');
            //Si sorties annulées...
            case Constantes::CANCELLED :
                $this->addFlash('warning', "Cette sortie a été annulée!");
                return $this->redirectToRoute('main');
            //Si sorties fermées à l'inscription...
            case Constantes::CLOSED :
                $this->addFlash('warning', "La date limite d'inscription est dépassée");
                return $this->redirectToRoute('main');
            //Si sorties se déroulent au moment de l'inscription...
            case Constantes::ONGOING :
                $this->addFlash('warning', "Impossible de s'inscrire, l'activité se déroule en ce moment-même!");
                return $this->redirectToRoute('main');
            //Si la sortie est déjà passée...
            case Constantes::FINISHED :
                $this->addFlash('warning', "La sortie est finie!");
                return $this->redirectToRoute('main');
            //Si le statut de la sortie est "Ouverte"...
            case Constantes::OPENED :
                //Si l'utilisateur est l'organisateur de la sortie...
                if ($eventChoosen->getOrganiser() === $user) {

                    $this->addFlash('warning', 'Vous avez crée la sortie, vous ne pouvez pas vous inscrire!');

                } //Si l'utilisateur est déjà inscrit à la sortie...
                elseif ($foundParticipants->contains($user)) {
                    $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie!');
                } //... On vérifie que le nombre max de participants n'est pas atteint
                elseif ($eventChoosen->getParticipants()->count() >= $eventChoosen->getMaxNumberParticipants()) {
                    $this->addFlash('warning', 'Le nombre maximum de participants a été atteint!');
                } else {
                    //Si toutes les conditions sont remplies, on ajoute notre user à la sortie
                    $eventChoosen->addParticipant($user);

                    $manager->persist($eventChoosen);
                    $manager->flush();

                    $this->addFlash('success', "Vous êtes bien inscrit à la sortie!");
                }

                return $this->redirectToRoute('main');
        }

        return $this->render("event/view.html.twig", [
            "foundEvent" => $eventChoosen,
            "foundParticipants" => $foundParticipants
        ]);
    }

    /**
     * @Route("/evenement/desistement/{id}", name="event_abandonned")
     * @param $id
     * @param EventRepository $eventRepository
     * @param EntityManagerInterface $manager
     * @param FunctionsStatus $functionsStatus
     * @return Response
     */
    public function abandon($id, EventRepository $eventRepository,
                            EntityManagerInterface $manager, FunctionsStatus $functionsStatus): Response
    {
        // Récupération de l'utilisateur connecté
        /** @var User $user */
        $user = $this->getUser();

        //Récupération de l'évenement choisi par mon utilisateur via l'id récupérée dans l'URL
        $eventChoosen = $eventRepository->find($id);

        //Récupération des inscrits à la sortie
        $foundParticipants = $eventChoosen->getParticipants();

        //Mise à jour le statut de l'évenement
        $functionsStatus->UpdateEventStatus($eventChoosen);

        // Récupération du statut de l'évenement
        $eventStatusName = $eventChoosen->getStatus()->getName();

        // Si l'utilisateur est bien dans les participants et que le statut de la sortie est "ouvert
        if (($foundParticipants->contains($user) and ($eventStatusName === Constantes::OPENED or $eventStatusName === Constantes::CLOSED))) {

            $eventChoosen->removeParticipant($user);

            $manager->persist($eventChoosen);
            $manager->flush();

            $this->addFlash('success', "Vous êtes bien désinscrit de la sortie!");

            return $this->redirectToRoute('main');
        } // Si le statut de l'event est "en cours"
        elseif ((($foundParticipants->contains($user) && $eventStatusName == Constantes::ONGOING))) {
            $this->addFlash('warning', 'La sortie est en cours, vous ne pouvez pas vous désinscrire  !');

        } else {
            throw $this->createNotFoundException("Erreur de route");
        }

        return $this->render("event/view.html.twig", [
            "foundEvent" => $eventChoosen,
            "foundParticipants" => $foundParticipants
        ]);
    }

    /**
     * @Route("/evenement/annuler/{id}", name="event_cancelled", methods={"GET", "POST"})
     * @param Request $request
     * @param $id
     * @param EventRepository $eventRepository
     * @param EntityManagerInterface $entityManager
     * @param FunctionsStatus $functionsStatus
     * @param StatusRepository $statusRepository
     * @return Response
     */
    public function cancel($id, Request $request, EventRepository $eventRepository,
                           EntityManagerInterface $entityManager, FunctionsStatus $functionsStatus, StatusRepository $statusRepository): Response
    {
        // Récupération de l'utilisateur connecté
        /** @var User $user */
        $user = $this->getUser();

        //Récupèration l'évenement choisi par mon utilisateur via l'id récupérée dans l'URL
        $eventChoosen = $eventRepository->find($id);

        if (!$eventChoosen) {
            throw $this->createNotFoundException("Cette sortie n'existe pas");
        }

        //Récupération de l'organisateur de la sortie
        $eventOrganiser = $eventChoosen->getOrganiser();

        //Vérification que l'utilisateur est bien l'organisateur de la sortie
        if ($eventOrganiser !== $user) {
            throw $this->createNotFoundException(self::WARNING_EVENT_NOT_AUTHORIZED);
        }

        //Mise à jour du statut de l'évenement
        $functionsStatus->UpdateEventStatus($eventChoosen);

        //Récupération du statut de la sortie
        $eventStatus = $eventChoosen->getStatus();

        if ($eventStatus === Constantes::ONGOING or $eventStatus === Constantes::FINISHED or $eventStatus === Constantes::ARCHIVED or $eventStatus === Constantes::CANCELLED) {
            throw $this->createNotFoundException("Erreur de statut");
        }

        // Création du formulaire
        $eventCancellationForm = $this->createForm(EventCancellationType::class, $eventChoosen);

        $eventCancellationForm->handleRequest($request);

        if ($eventCancellationForm->isSubmitted()) {

            $eventChoosen->setRegistrationDeadline(new DateTime('now'));

            if ($eventCancellationForm->isValid()) {

                // Si l'utilisateur est bien l'organisateur et que le statut de la sortie est bien ouvert
                $statusCancelled = $statusRepository->findOneBy(["name" => Constantes::CANCELLED]);

                // Changement du statut de la sortie en annulée
                $eventChoosen->setStatus($statusCancelled);

                // Récupération du motif de l'annulation
                $cancellationReason = $eventCancellationForm->get('cancellation_reason')->getData();

                $eventChoosen->setCancellationReason($cancellationReason);

                $entityManager->persist($eventChoosen);
                $entityManager->flush();

                $this->addFlash('success', "Votre sortie est bien annulée!");
                return $this->redirectToRoute('main');

            }
        }

        return $this->render("event/cancel.html.twig", [
            "foundEvent" => $eventChoosen,
            'eventCancellationForm' => $eventCancellationForm->createView(),
        ]);

    }

    /**
     * @Route("/evenement/modifier/{id}", name="event_modified")
     * @param $id
     * @param EventRepository $eventRepository
     * @param Request $request
     * @param FunctionsStatus $functionsStatus
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function modify($id,
                           EventRepository $eventRepository,
                           Request $request,
                           FunctionsStatus $functionsStatus,
                           EntityManagerInterface $manager
    ): Response
    {

        //Récupération de mon user
        $user = $this->getUser();

        //Récupération de l'evenement à modifier
        $eventToModify = $eventRepository->find($id);

        if (!$eventToModify) {
            throw $this->createNotFoundException("Cette sortie n'existe pas");
        }

        //Mise à jour le statut de l'évenement
        $functionsStatus->UpdateEventStatus($eventToModify);

        //Récupération du statut de l'évènement
        $eventStatusName = $eventToModify->getStatus()->getName();

        $eventForm = null;

        //Vérification si mon user est bien l'organisateur de l'évènement
        if ($eventToModify->getOrganiser() != $user) {
            $this->addFlash('warning', "Vous devez être l'organisateur pour pouvoir modifier une sortie");
            return $this->redirectToRoute('main');

            //Vérification du statut de l'évènement
        } elseif (!($eventStatusName == Constantes::OPENED or
            $eventStatusName == Constantes::CREATED or
            $eventStatusName == Constantes::CLOSED)) {
            $this->addFlash("warning", "Impossible de modifier cette sortie!");

        } else {

            $eventToModifyCity = $eventToModify->getPlace()->getCity();

            $eventForm = $this->createForm(EventType::class, $eventToModify);
            $eventForm->get('city')->setData($eventToModifyCity);

            $eventForm->handleRequest($request);

            //Test de soumission du formulaire
            if ($eventForm->isSubmitted()) {

                //Test de la condition de l'endroit
                $this->havePlace($eventForm);

                //Test de validation du formulaire
                if ($eventForm->isValid()) {
                    if ($this->checkEvent($eventToModify)) {

                        /** @var DateTime $eventDateStart */
                        $eventDuration = $eventToModify->getDuration();
                        $eventDateStart = $eventToModify->getDateTimeStart();

                        //Transformer la durée en nombre positif si besoin
                        $eventToModify->setDuration(abs($eventToModify->getDuration()));
                        $dateTimeEnd = DateTimeHandler::dateAddMinutes($eventDateStart, $eventDuration);
                        $eventToModify->setDateTimeEnd($dateTimeEnd);
                        $functionsStatus->UpdateEventStatus($eventToModify);

                        $manager->persist($eventToModify);
                        $manager->flush();

                        $this->addFlash('success', 'Sortie bien modifiée!');

                        // La sortie est "crée" si l'utilisateur clique sur "enregistrer"
                        if ($eventForm->get('save')->isClicked()) {
                            return $this->redirectToRoute('main');
                        }

                        return $this->redirectToRoute("event_published", ['id' => $eventToModify->getId()]);
                    }
                }
            }
        }

        return $this->render("event/create.html.twig", [
            'eventForm' => $eventForm->createView(),
            'event' => $eventToModify,
            'modif' => true
        ]);
    }

    public function checkEvent($eventToCheck): bool
    {
        $isChecked = true;

        $numberOfParticipants = $eventToCheck->getParticipants()->count();

        //Vérifier que le nombre max de participants ne soit pas inférieur aux nombre d'inscrits.
        if ($eventToCheck->getMaxNumberParticipants() < $numberOfParticipants) {
            $this->addFlash("warning", "Impossible de réduire le nombre de participants!");
            $isChecked = false;
        }

        //Vérifier que la date de sortie ne soit pas passée
        if ($eventToCheck->getDateTimeStart() < new DateTime('now')) {
            $this->addFlash("warning", "Impossible de créer une sortie avec une date passée!");
            $isChecked = false;
        }

        //Vérifier que la date limite d'inscription est supérieur à la date du jour
        if ($eventToCheck->getRegistrationDeadline() <= new DateTime('now')) {
            $this->addFlash("warning", "Impossible de clôturer la sortie à une date passée");
            $isChecked = false;
        }

        //Vérifier que la date limite d'inscription soit inférieure à la date du début de la sortie
        if ($eventToCheck->getRegistrationDeadline() >= $eventToCheck->getDateTimeStart()) {
            $this->addFlash("warning", "Impossible de clôturer la sortie à une date passée");
            $isChecked = false;
        }
        return $isChecked;
    }

    /**
     * @Route("/evenement/publier/{id}", name="event_published")
     * @param $id
     * @param EntityManagerInterface $manager
     * @param EventRepository $eventRepository
     * @param StatusRepository $statusRepository
     * @return Response
     */
    public function publish($id,
                            EntityManagerInterface $manager,
                            EventRepository $eventRepository,
                            StatusRepository $statusRepository): Response
    {
        /** @var Event $event */
        $event = $eventRepository->find($id);
        /** @var Status $eventStatus */
        $eventStatus = $event->getStatus();

        // Test si l'utilisateur est l'organisateur
        if ($event->getOrganiser() === $this->getUser()) {
            // Test si la sortie a le statue créé
            if ($eventStatus->getName() == Constantes::CREATED) {


                //Modification du statut et persistance
                $eventStatus = $statusRepository->findOneBy(['name' => Constantes::OPENED]);
                $event->setStatus($eventStatus);
                $manager->persist($event);
                $manager->flush();

                $this->addFlash("success", self::SUCCESS_EVENT_PUBLISHED);
            } else {
                $this->addFlash("warning", self::WARNING_EVENT_WRONG_STATUS);
            }
        } else {
            $this->addFlash("warning", self::WARNING_EVENT_NOT_AUTHORIZED);
        }
        return $this->redirectToRoute('main');
    }

    /**
     * @Route ("/ajaxCity", name="ajaxCity")
     * @param Request $request
     * @param CityRepository $cityRepository
     * @return JsonResponse
     */
    public function updatePlace(Request $request,
                                CityRepository $cityRepository): JsonResponse
    {

        //me ramène le contenu de ma requête, qui est mon JSON à l'intérieur
        $data = json_decode($request->getContent());

        //je peux donc ensuite accèder aux attributs de mon objet
        $cityId = $data->eventCity;

        $city = $cityRepository->find($cityId);

        //je récupère ma série qui est bdd, avec l'id
        $places = $city->getPlaces();

        $placesObject = [];

        foreach ($places as $place) {
            $placesObject[$place->getId()]['name'] = $place->getName();
        }

        return new JsonResponse([
            'places' => $placesObject,
        ]);
    }

    /**
     * @Route("/ajaxPlace", name="ajaxPlace")
     * @param Request $request
     * @param PlaceRepository $placeRepository
     * @return Response
     */
    public function updatePlaceInformation(Request $request,
                                           PlaceRepository $placeRepository): Response
    {
        //me ramène le contenu de ma requête, qui est mon JSON à l'intérieur
        $data = json_decode($request->getContent());

        $placeId = $data->placeId;

        $placeInformation = $placeRepository->find($placeId);

        $placeStreet = $placeInformation->getStreet();
        $placeLatitude = $placeInformation->getLatitude();
        $placeLongitude = $placeInformation->getLongitude();

        return new JsonResponse([
            'placeStreet' => $placeStreet,
            'placeLatitude' => $placeLatitude,
            'placeLongitude' => $placeLongitude
        ]);

    }

    private function havePlace(FormInterface $form):bool
    {
        $placeID = $form->get('place')->getViewData();
        if ($placeID != "") {
            return true;
        }
        $form->get('place')->addError(new FormError(self::WARNING_EVENT_WRONG_PLACE));
        return false;
    }
}


