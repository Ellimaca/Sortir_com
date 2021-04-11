<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Event;
use App\Entity\Place;
use App\Entity\Status;
use App\Entity\User;
use DateInterval;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture

{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->encoder = $userPasswordEncoder;
    }

    /**
     * @param ObjectManager $manager
     * Permet de charger des données dans la base de données
     */
    public function load(ObjectManager $manager)
    {
        // On utilise Faker pour générer des données aléatoires en français
        $faker = \Faker\Factory::create("fr_FR");

        // Génération du nom des différents états possibles pour une sortie pour l'entité Status
        $statusName = ["Créée", "Ouverte", "Clôturée", "Activité en cours", "Passée", "Annulée"];
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

        // TODO vérification date et la durée
        // Génèration de données aléatoires pour l'entité Event
        for ($i = 0; $i < 50; $i++) {
            $event = new Event();
            $event->setName($faker->sentence(4));
            $event->setDateTimeStart($faker->dateTimeBetween(" -1 month", " + 1 month "));

            $interval = new DateInterval("P1D");
            $event->setRegistrationDeadline(date_sub($event->getDateTimeStart(), $interval));
            $fakeDuration = [60, 90, 120, 180, 240, 300];
            $event->setDuration($faker->randomElement($fakeDuration));

            $dateFin = $event->getDateTimeStart()->add(new DateInterval('PT' . $event->getDuration() . 'M'));
            $event->setDateTimeEnd($dateFin);

            //$duration = date_diff($event->getDateTimeStart(), $event->getDateTimeEnd());
            //$durationMinutes = $duration->d*1440 + $duration->h*60 + $duration->i;
            //$event->setDuration($durationMinutes);


            $event->setDescription($faker->paragraphs($faker->numberBetween(0, 3), true));
            $event->setCampus($faker->randomElement($allCampus));
            $event->setMaxNumberParticipants($faker->numberBetween(2, 6));

            $event->setStatus($faker->randomElement($statusArray));
            $event->setPlace($faker->randomElement($allPlaces));
            $event->setOrganiser($faker->randomElement($allUsers));

            $manager->persist($event);
            $manager->flush();
        }

        $eventRepository = $manager->getRepository(Event::class);
        $allEvents = $eventRepository->findAll();

        foreach ($allEvents as $event) {
            $randomParticipants = $faker->numberBetween(0, $event->getMaxNumberParticipants());
            for ($i = 0; $i < $randomParticipants; $i++) {
                $event->addParticipant($faker->randomElement($allUsers));
             }
        }

        $manager->persist($event);
        $manager->flush();

        // On appèle la fonction pour charger des données statiques en BDD
        $this->createStaticData($manager);

    }

    /**
     * @param $manager
     * Permet de charger des données statiques en BDD
     */
    private function createStaticData($manager)
    {

        $faker = \Faker\Factory::create("fr_FR");

        $user = new User();

        $user->setPseudo("test");
        $user->setFirstName("test");
        $user->setLastName("test");
        $user->setCampus($manager->getRepository(Campus::class)->findOneBy(['name' => "Saint-Herblain"]));
        $user->setEmail("test@test.com");
        $user->setIsActive(1);
        $user->setPhoneNumber("0606060606");
        $password = $this->encoder->encodePassword($user, "test");
        $user->setPassword($password);
        $user->setRoles(["ROLE_USER"]);
        $user->setIsActive(true);
        $user->setIsAdmin(false);

        $manager->persist($user);

        $user2 = new User();

        $user2->setPseudo("Batman");
        $user2->setFirstName("Bruce");
        $user2->setLastName("Wayne");

        $user2->setCampus($manager->getRepository(Campus::class)->findOneBy(['name' => "Saint-Herblain"]));

        $user2->setEmail("batman@test.com");
        $user2->setIsActive(1);
        $user2->setPhoneNumber("+33 6 44 61 13 44");
        $password = $this->encoder->encodePassword($user, "test");
        $user2->setPassword($password);
        $user2->setRoles(["ROLE_USER"]);
        $user2->setIsActive(true);
        $user2->setIsAdmin(false);

        $manager->persist($user2);


        $city = new City;
        $city->setName('Saint-Herblain');
        $city->setPostCode(	44800);

        $manager->persist($city);

        $place = new Place;
        $place->setName('Etang du ter');
        $place->setStreet('rue de la Poste');
        $place->setLongitude(47.226);
        $place->setLatitude(-1.741);
        $place->setCity($city);

        $manager->persist($place);

        $userRepository = $manager->getRepository(User::class);
        $allUsers = $userRepository->findAll();

        $staticEvent = new Event();

        $staticEvent->setName('Sortie Kayak à Saint Herblain');
        $staticEvent->setDescription('Sortie Kayak à Saint Herblain, pensez à prendre un pique nique, de la crème solaire et un chapeau!');
        $staticEvent->setCampus($manager->getRepository(Campus::class)->findOneBy(['name' => "Saint-Herblain"]));
        $staticEvent->setOrganiser($user);
        $staticEvent->setMaxNumberParticipants(6);
        $staticEvent->setDuration(90);
        $staticEvent->setDateTimeStart($faker->dateTimeBetween(" -1 month", " + 1 month "));
        $interval = new DateInterval("P1D");
        $staticEvent->setRegistrationDeadline(date_sub($staticEvent->getDateTimeStart(), $interval));
        $statusCreated = $manager->getRepository(Status::class)->findOneBy(['name'=>'Ouverte']);
        $staticEvent->setStatus($statusCreated);
        $staticEvent->setPlace($place);

        $manager->persist($staticEvent);

       $staticEvent2 = new Event();
        $staticEvent2->setName('Apprendre Symfony en s\'amusant');
        $staticEvent2->setDescription('Apprendre Symfony dans la bonne humeur avec Taharqa, Camille et Benjamin. Prevoir un goûter et des dolipranes ! ');
        $staticEvent2->setCampus($manager->getRepository(Campus::class)->findOneBy(['name' => "Saint-Herblain"]));
        $staticEvent2->setOrganiser($user);
        $staticEvent2->setMaxNumberParticipants(6);
        $staticEvent2->setDuration(240);
        $staticEvent2->setDateTimeStart($faker->dateTimeBetween(" -1 month", " + 1 month "));
        $interval = new DateInterval("P1D");
        $staticEvent2->setRegistrationDeadline(date_sub($staticEvent2->getDateTimeStart(), $interval));
        $statusCreated = $manager->getRepository(Status::class)->findOneBy(['name'=>'Annulée']);
        $staticEvent2->setStatus($statusCreated);
        $staticEvent2->setPlace($place);

        $manager->persist($staticEvent2);

        $staticEvent3 = new Event();
        $staticEvent3->setName('Manger des cailloux');
        $staticEvent3->setDescription('Quoi de meilleur que de manger des cailloux? Rejoins-nous pour manger des cailloux');
        $staticEvent3->setCampus($manager->getRepository(Campus::class)->findOneBy(['name' => "Saint-Herblain"]));
        $staticEvent3->setOrganiser($user);
        $staticEvent3->setMaxNumberParticipants(2);
        $staticEvent3->setDuration(240);
        $staticEvent3->setDateTimeStart($faker->dateTimeBetween(" -1 month", " + 1 month "));
        $interval = new DateInterval("P1D");
        $staticEvent3->setRegistrationDeadline(date_sub($staticEvent3->getDateTimeStart(), $interval));
        $statusCreated = $manager->getRepository(Status::class)->findOneBy(['name'=>'Créée']);
        $staticEvent3->setStatus($statusCreated);
        $staticEvent3->setPlace($place);

        $manager->persist($staticEvent3);

        $staticEvent4 = new Event();
        $staticEvent4->setName('Apprendre le ping-pong avec Taharqa');
        $staticEvent4->setDescription('Taharqa, Champion du monde de Ping-pong vous propose de partager ses talents avec vous. Prévoir une raquette et de l\'eau');
        $staticEvent4->setCampus($manager->getRepository(Campus::class)->findOneBy(['name' => "Saint-Herblain"]));
        $staticEvent4->setOrganiser($faker->randomElement($allUsers));
        $staticEvent4->setOrganiser($user);
        $staticEvent4->setMaxNumberParticipants(4);
        $staticEvent4->setDuration(120);
        $staticEvent4->setDateTimeStart($faker->dateTimeBetween(" -1 month", " + 1 month "));
        $interval = new DateInterval("P1D");
        $staticEvent4->setRegistrationDeadline(date_sub($staticEvent4->getDateTimeStart(), $interval));
        $dateFin = $staticEvent4->getDateTimeStart()->add(new DateInterval('PT' . $staticEvent4->getDuration() . 'M'));
        $staticEvent4->setDateTimeEnd($dateFin);
        $statusCreated = $manager->getRepository(Status::class)->findOneBy(['name'=>'Créée']);
        $staticEvent4->setStatus($statusCreated);
        $staticEvent4->setPlace($place);

        $manager->persist($staticEvent4);

        $manager->flush();

    }
}
