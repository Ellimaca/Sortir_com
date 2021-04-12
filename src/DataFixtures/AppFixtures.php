<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Event;
use App\Entity\Place;
use App\Entity\Status;
use App\Entity\User;
use App\Utils\Constantes;
use App\Utils\DateTimeHandler;
use App\Utils\FunctionsStatus;
use DateInterval;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture

{
    private UserPasswordEncoderInterface $encoder;
    private FunctionsStatus $functionStatus;
    Const CREATED=0;
    Const CANCELLED=2;
    Const OPENED=1;


    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder, FunctionsStatus $functionsStatus)
    {
        $this->encoder = $userPasswordEncoder;
        $this->functionStatus = $functionsStatus;
        //date_default_timezone_set ( Constantes::TIME_ZONE);
    }

    /**
     * @param ObjectManager $manager
     * Permet de charger des données dans la base de données
     * @throws Exception
     */
    public function load(ObjectManager $manager)
    {
        // On utilise Faker pour générer des données aléatoires en français
        $faker = Factory::create("fr_FR");

        // Génération du nom des différents états possibles pour une sortie pour l'entité Status
        $statusName = [Constantes::CREATED, Constantes::OPENED, Constantes::CLOSED, Constantes::ONGOING, Constantes::FINISHED, Constantes::CANCELLED, Constantes::ARCHIVED];
        foreach ($statusName as $name) {
            $status = new Status();
            $status->setName($name);
            $manager->persist($status);
        }
        $manager->flush();

        $statusRepository = $manager->getRepository(Status::class);
        $statusCreated = $statusRepository->findOneBy(['name'=>'Créée']);
        $statusOpen = $statusRepository->findOneBy((['name'=>'Ouverte']));
        $statusCancelled = $statusRepository->findOneBy((['name'=>'Annulée']));
        $statusArray = [$statusCreated, $statusOpen, $statusCancelled];

        // Tableau qui regroupe les numéros des départements du grand ouest
        $westernRegions = [14, 22, 29, 35, 44, 49, 50, 56, 61, 72, 85];

        // Génèration de données aléatoires pour l'entité City
        for ($i = 0; $i < 50; $i++) {
            $city = new City();
            $city->setName($faker->city);
            $city->setPostCode($faker->randomElement($westernRegions) . $faker->numberBetween(10, 99) * 10);
            $manager->persist($city);
        }
        $manager->flush();

        $cityRepository = $manager->getRepository(City::class);
        $allCity = $cityRepository->findAll();

        // Génèration de données aléatoires pour l'entité Place
        for ($i = 0; $i < 50; $i++) {
            $place = new Place();
            $place->setName($faker->sentence(3, true));
            $place->setStreet($faker->streetAddress);
            $place->setLatitude($faker->latitude);
            $place->setLongitude($faker->longitude);
            $place->setCity($faker->randomElement($allCity));
            $manager->persist($place);
        }
        $manager->flush();

        $placeRepository = $manager->getRepository(Place::class);
        $allPlaces = $placeRepository->findAll();

        // Génèration de données aléatoires pour l'entité Campus
        $campusName = ["Quimper", "Saint-Herblain", "Rennes", "Niort", "Le Mans", "La Roche-Sur-Yon", "Laval" ];
        foreach ($campusName as $name) {
            $campus = new Campus();
            $campus->setName($name);
            $manager->persist($campus);
        }
        $manager->flush();

        $campusRepository = $manager->getRepository(Campus::class);
        $allCampus = $campusRepository->findAll();

        // Génèration de données aléatoires pour l'entité User
        for ($i = 0; $i < 80; $i++) {
            $user = new User();
            $user->setFirstName($faker->firstName);
            $user->setEmail($faker->email);
            $user->setPseudo($faker->userName);
            $user->setLastName($faker->lastName);
            $user->setCampus($faker->randomElement($allCampus));
            $user->setPhoneNumber($faker->phoneNumber);
            $password = $this->encoder->encodePassword($user, "test");
            $user->setPassword($password);
            $user->setRoles(["ROLE_USER"]);
            $user->setIsActive(true);
            $user->setIsAdmin(false);

            $manager->persist($user);
        }
        $manager->flush();

        $userRepository = $manager->getRepository(User::class);
        $allUsers = $userRepository->findAll();

        // Génèration de données aléatoires pour l'entité Event
        for ($i = 0; $i < 50; $i++) {
            $event = new Event();
            $event->setName($faker->sentence(4));
            $event->setDateTimeStart($faker->dateTimeBetween(" -1 month", " + 1 month "));
            $fakeDuration = [60, 90, 120, 180, 240, 300];
            $event->setDuration($faker->randomElement($fakeDuration));

            $startDate = $event->getDateTimeStart();

            // Date de fin d'inscription
            /** @var DateTime $registrationDeadline */
            $registrationDeadline = clone $startDate;
            $interval = new DateInterval("P1D");
            $registrationDeadline->sub($interval);
            $event->setRegistrationDeadline($registrationDeadline);

            // Date de fin de la sortie
            $dateTimeEnd = clone $startDate;
            $intervalDuration = $event->getDuration();
            $dateTimeEnd->add(new DateInterval('PT' . $intervalDuration . 'M'));
            $event->setDateTimeEnd($dateTimeEnd);


            // Les 3 statuts à injecter $statusArray = [$statusCreated, $statusOpen, $statusCancelled];
            $today = new DateTime('now');

            $statusIsCancelled = $faker->boolean(10);

            if ($statusIsCancelled) {
                $event->setStatus($statusArray[self::CANCELLED]);
            }
            elseif ($startDate > $today) {
                $event->setStatus($statusArray[self::OPENED]);
            }
            else {
                $statusIsCreated = $faker->boolean(10);
                if ($statusIsCreated) {
                    $event->setStatus($statusArray[self::CREATED]);
                } else {
                    $event->setStatus($statusArray[self::OPENED]);
                }
            }

            //Description
            $event->setDescription($faker->paragraphs($faker->numberBetween(0, 3), true));

            //Campus
            $event->setCampus($faker->randomElement($allCampus));

            //Max participants
            $event->setMaxNumberParticipants($faker->numberBetween(2, 6));

            //Place
            $event->setPlace($faker->randomElement($allPlaces));

            //Organisateur de la sortie
            $event->setOrganiser($faker->randomElement($allUsers));

            $manager->persist($event);

        }

        $manager->flush();

        $eventRepository = $manager->getRepository(Event::class);
        $allEvents = $eventRepository->findAll();

        foreach ($allEvents as $event) {
            $randomParticipants = $faker->numberBetween(0, $event->getMaxNumberParticipants());
            for ($i = 0; $i < $randomParticipants; $i++) {
                $event->addParticipant($faker->randomElement($allUsers));
             }
        }

        // Création des données statiques en base de données
        $this->createStaticData($manager);

        // Mise à jour des statuts
        $this->functionStatus->UpdateEventsStatus($allEvents);

        $manager->persist($event);
        $manager->flush();

    }

    /**
     * @param $manager
     * Permet de charger des données statiques en BDD
     */
    private function createStaticData($manager)
    {
        $campus1 = $manager->getRepository(Campus::class)->findOneBy(['name' => "Saint-Herblain"]);
        $campus2 = $manager->getRepository(Campus::class)->findOneBy(['name' => "Rennes"]);

        $status = $manager->getRepository(Status::class)->findAll();
        $statusCreated = FunctionsStatus::getStatusByName(Constantes::CREATED,$status);
        $statusOpened = FunctionsStatus::getStatusByName(Constantes::OPENED,$status);
        $statusCancelled = FunctionsStatus::getStatusByName(Constantes::CANCELLED,$status);

        //data = [pseudo,firstName,LastName,Campus,email,isActif,telephone,password,[roles],isAdmin)

        //Création de l'utilisateur 1
        $dataUser = ["test","test","test",$campus1,"test@test.com",true,"06 06 06 06 06","test",["ROLE_USER"],false];
        $staticUser1 = $this->createUser($dataUser);
        $manager->persist($staticUser1);

        //Création de l'utilisateur 2
        $dataUser = ["Batman","Bruce","Wayne",$campus1,"batman@test.com",true,"+33 6 44 61 13 44","test",["ROLE_USER"],false];
        $staticUser2 = $this->createUser($dataUser);
        $manager->persist($staticUser2);

        //Création de l'utilisateur 3
        $dataUser = ["desactive","desactive","desactive",$campus2,"desactive@test.com",true,"+33 6 44 61 13 44","test",["ROLE_USER"],false];
        $staticUser3 = $this->createUser($dataUser);
        $manager->persist($staticUser3);

        //dataCity(name,postcode)

        //Création de la ville 1
        $dataCity = ['Saint-Herblain',44800];
        $city = $this->createCity($dataCity);
        $manager->persist($city);

        //Création de la ville 2
        $dataCity = ['Rennes',35000];
        $city2 = $this->createCity($dataCity);
        $manager->persist($city2);

        //dataPlace(name,street,longitude,lattitude,City)

        //Création du lieu 1
        $dataPlace = ['Etang du ter','rue de la Poste',47.226,-1.741,$city];
        $place1 = $this->createPlace($dataPlace);
        $manager->persist($place1);


        $userRepository = $manager->getRepository(User::class);
        $allUsers = $userRepository->findAll();

        //dataEvent(name,description,Campus,Organiser,maxNumberParticipants,duration,dateTimeStart,
        //          registrationDeadline,Status,Place,nbParticipants)


        $deadlineDuration = 1440;

        /** Static Event 1
         *      statut : créée
         *      date de début: futur
         *      deadline : futur
         *      fin activite : futur
         *      nb Participants inscrits : incomplet
         *      organiser : Test
         */
        $dateStart = new DateTime("+5 days");
        $duration = 240;
        $registrationDeadline = DateTimeHandler::dateSubMinutes($dateStart,$deadlineDuration);

        $dataEvent = ['Test 1',
                      'statut : créée; date de début: futur ; deadline : futur ; fin activite : futur ; nb Participants inscrits : incomplet ; organiser : Test  ',
                      $campus1,$staticUser1,6,$duration,$dateStart,$registrationDeadline,$statusCreated,$place1,0];
        $staticEvent1 = $this->createEvent($dataEvent,$allUsers);
        $manager->persist($staticEvent1);

        /** Static Event 2
         *      statut : ouvert
         *      date de début : futur
         *      deadline : futur
         *      fin activite : futur
         *      nb Participants inscrits : incomplet
         *      organiser : Test
         */

        $dateStart = new DateTime("+3 days");
        $duration = 90;
        $registrationDeadline = DateTimeHandler::dateSubMinutes($dateStart,$deadlineDuration);

        $dataEvent = ['Test 2',
                      'statut : ouvert ; date de début : futur ; deadline : futur ; fin activite : futur ; nb Participants inscrits : incomplet ; organiser : Test',
                       $campus1,$staticUser1,6,$duration,$dateStart,$registrationDeadline,$statusOpened,$place1,4];
        $staticEvent2 = $this->createEvent($dataEvent,$allUsers);
        $manager->persist($staticEvent2);

        /** Static Event 3
         *      statut : ouvert
         *      date de début : futur
         *      deadline : futur
         *      fin activite : futur
         *      nb Participants inscrits : complet
         *      organiser : Batman
         */

        $dateStart = new DateTime("+3 days");
        $duration = 90;
        $registrationDeadline = DateTimeHandler::dateSubMinutes($dateStart,$deadlineDuration);

        $dataEvent = ['Test 3',
                      'statut : ouvert ; date de début : futur ; deadline : futur ; fin activite : futur ; nb Participants inscrits : complet;
                      organiser : Batman',
            $campus1,$staticUser2,6,$duration,$dateStart,$registrationDeadline,$statusOpened,$place1,6];
        $staticEvent3 = $this->createEvent($dataEvent,$allUsers);
        $manager->persist($staticEvent3);

        /** Static Event 4
         *      statut : cloturé (à vérifier)
         *      date de début : futur
         *      deadline : passé
         *      fin activite : futur
         *      nb Participants inscrits : complet
         *      organiser : Test
         */

        $dateStart = new DateTime("+1 days");
        $duration = 90;
        $registrationDeadline = DateTimeHandler::dateSubMinutes($dateStart,$deadlineDuration);

        $dataEvent = ['Test 4',
                      'statut : cloturé (à vérifier); date de début : futur ; deadline : passé ; fin activite : futur; nb Participants inscrits : incomplet; organiser : Test',
                      $campus1,$staticUser1,6,$duration,$dateStart,$registrationDeadline,$statusOpened,$place1,3];
        $staticEvent4 = $this->createEvent($dataEvent,$allUsers);
        $manager->persist($staticEvent4);

        /** Static Event 5
         *      statut : En cours (à vérifier)
         *      date de début : passé de moins d 1 mois
         *      deadline : passé
         *      fin activite : futur
         *      nb Participants inscrits : incomplet
         *      organiser : Test
         */

        $dateStart = new DateTime('now');
        $duration = 90;
        $registrationDeadline = DateTimeHandler::dateSubMinutes($dateStart,$deadlineDuration);

        $dataEvent = ['Test 5',
            'statut : En cours (à vérifier) ; date de début : passé de moins d 1 mois ; deadline : passé ; fin activite : futur ; nb Participants inscrits : incomplet ;organiser : Test',
            $campus1,$staticUser1,6,$duration,$dateStart,$registrationDeadline,$statusOpened,$place1,4];
        $staticEvent5 = $this->createEvent($dataEvent,$allUsers);
        $manager->persist($staticEvent5);

        /** Static Event 6
         *      statut : Passé (à vérifier)
         *      date de début : passé de moins d 1 mois
         *      deadline : passé
         *      fin activite : passé
         *      nb Participants inscrits : incomplet
         *      organiser : Test
         */

        $dateStart = new DateTime('- 1 week');
        $duration = 90;
        $registrationDeadline = DateTimeHandler::dateSubMinutes($dateStart,$deadlineDuration);

        $dataEvent = ['Test 6',
            'statut : Passé (à vérifier) ; date de début : passé de moins d 1 mois ; deadline : passé ; fin activite : passé nb Participants inscrits : incomplet ;organiser : Test',
            $campus1,$staticUser1,6,$duration,$dateStart,$registrationDeadline,$statusOpened,$place1,4];
        $staticEvent6 = $this->createEvent($dataEvent,$allUsers);
        $manager->persist($staticEvent6);

        /** Static Event 7
         *      statut : Annulé (à vérifier)
         *      date de début : futur
         *      deadline : futur
         *      fin activite : futur
         *      nb Participants inscrits : incomplet
         *      organiser : Test
         */

        $dateStart = new DateTime('+ 1 week');
        $duration = 90;
        $registrationDeadline = DateTimeHandler::dateSubMinutes($dateStart,$deadlineDuration);

        $dataEvent = ['Test 7',
            'statut : Annulé (à vérifier) ; date de début : futur ; deadline : futur ; fin activite : futur nb Participants inscrits : incomplet ;organiser : Test',
            $campus1,$staticUser1,6,$duration,$dateStart,$registrationDeadline,$statusCancelled,$place1,4];
        $staticEvent7 = $this->createEvent($dataEvent,$allUsers);
        $manager->persist($staticEvent7);

        /** Static Event 8
         *      statut : Archivé (à vérifier)
         *      date de début : passé de plus d 1 mois
         *      deadline : passé
         *      fin activite : passé
         *      nb Participants inscrits : incomplet
         *      organiser : Test
         */

        $dateStart = new DateTime('- 2 month');
        $duration = 90;
        $registrationDeadline = DateTimeHandler::dateSubMinutes($dateStart,$deadlineDuration);

        $dataEvent = ['Test 9',
            'statut : Archivé (à vérifier) ; date de début : passé de plus d 1 mois ; deadline : passé ; fin activite : passé nb Participants inscrits : incomplet ;organiser : Test',
            $campus1,$staticUser1,6,$duration,$dateStart,$registrationDeadline,$statusOpened,$place1,4];
        $staticEvent8 = $this->createEvent($dataEvent,$allUsers);
        $manager->persist($staticEvent8);

        $manager->flush();

    }

    const PSEUDO = 0;
    const FIRSTNAME = 1;
    const LASTNAME = 2;
    const CAMPUS = 3;
    const EMAIL = 4;
    const IS_ACTIF = 5;
    const TELEPHONE = 6;
    const PASSWORD = 7;
    const ROLES = 8;
    const IS_ADMIN = 9;

    /**
     * @param array $dataUser
     * @return User
     */
    private function createUser(array $dataUser): User
    {
        //data = [pseudo,firstName,LastName,Campus,email,isActif,telephone,password,[roles],isAdmin)

        $staticUser = new User();

        $staticUser->setPseudo($dataUser[self::PSEUDO]);
        $staticUser->setFirstName($dataUser[self::FIRSTNAME]);
        $staticUser->setLastName($dataUser[self::LASTNAME]);
        $staticUser->setCampus($dataUser[self::CAMPUS]);
        $staticUser->setEmail($dataUser[self::EMAIL]);
        $staticUser->setIsActive($dataUser[self::IS_ACTIF]);
        $staticUser->setPhoneNumber($dataUser[self::TELEPHONE]);
        $password = $this->encoder->encodePassword($staticUser, $dataUser[self::PASSWORD]);
        $staticUser->setPassword($password);
        $staticUser->setRoles($dataUser[self::ROLES]);
        $staticUser->setIsAdmin($dataUser[self::IS_ADMIN]);

        return $staticUser;
    }

    const NAME_CITY = 0;
    const POST_CODE = 1;

    private function createCity(array $dataCity) : City
    {
        $staticCity = new City();

        $staticCity->setName($dataCity[self::NAME_CITY]);
        $staticCity->setPostCode($dataCity[self::POST_CODE]);

        return $staticCity;
    }

    const NAME_PLACE = 0;
    const STREET = 1;
    const LONGITUDE = 2;
    const LATTITUDE = 3;
    const CITY = 4;

    private function createPlace(array $dataPlace) : Place
    {
        //dataCity(name,street,longitude,lattitude,city)
        $staticPlace = new Place();

        $staticPlace->setName($dataPlace[self::NAME_PLACE]);
        $staticPlace->setStreet($dataPlace[self::STREET]);
        $staticPlace->setLongitude($dataPlace[self::LONGITUDE]);
        $staticPlace->setLatitude($dataPlace[self::LATTITUDE]);
        $staticPlace->setCity($dataPlace[self::CITY]);

        return $staticPlace;
    }

    const EVENT_NAME = 0;
    const DESCRIPTION = 1;
    const EVENT_CAMPUS = 2;
    const ORGANISER = 3;
    const MAX_NB_PARTICIPANTS = 4;
    const DURATION = 5;
    const DATETIME_START = 6;
    const REGISTRATION_DEADLINE = 7;
    const STATUS = 8;
    const PLACE = 9;
    const NB_PARTICIPANTS = 10;

    private function createEvent(array $dataEvent, $allUsers) : Event
    {
        //dataEvent(name,description,Campus,Organiser,maxNumberParticipants,duration,dateTimeStart,
        //          registrationDeadline,Status,Place,nbParticipants)
        $faker = Factory::create();
        $staticEvent = new Event();

        $staticEvent->setName($dataEvent[self::EVENT_NAME]);
        $staticEvent->setDescription($dataEvent[self::DESCRIPTION]);
        $staticEvent->setCampus($dataEvent[self::EVENT_CAMPUS]);
        $staticEvent->setOrganiser($dataEvent[self::ORGANISER]);
        $staticEvent->setMaxNumberParticipants($dataEvent[self::MAX_NB_PARTICIPANTS]);
        $staticEvent->setDuration($dataEvent[self::DURATION]);
        $staticEvent->setDateTimeStart($dataEvent[self::DATETIME_START]);
        $staticEvent->setRegistrationDeadline($dataEvent[self::REGISTRATION_DEADLINE]);
        $staticEvent->setDateTimeEnd(DateTimeHandler::dateAddMinutes($dataEvent[self::DATETIME_START],$dataEvent[self::DURATION]));
        $staticEvent->setStatus($dataEvent[self::STATUS]);
        $staticEvent->setPlace($dataEvent[self::PLACE]);

        for($i = 0 ; $i < $dataEvent[self::NB_PARTICIPANTS];$i++ ){
            $staticEvent->addParticipant($faker->randomElement($allUsers));
        }

        return $staticEvent;

    }
}
