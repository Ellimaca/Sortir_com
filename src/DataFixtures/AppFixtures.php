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

            $interval1 = new DateInterval("PT6H");
            $dateTimeEnd = $event->getDateTimeStart()->add($interval1);
            $event->setDateTimeEnd($faker->dateTimeBetween($event->getDateTimeStart(), $dateTimeEnd));

            //$duration = date_diff($event->getDateTimeStart(), $event->getDateTimeEnd());
            //$durationMinutes = $duration->d*1440 + $duration->h*60 + $duration->i;
            //$event->setDuration($durationMinutes);

            $interval = new DateInterval("P1D");
            $event->setRegistrationDeadline(date_sub($event->getDateTimeStart(), $interval));
            $event->setDescription($faker->paragraphs($faker->numberBetween(0, 3), true));
            $event->setCampus($faker->randomElement($allCampus));
            $event->setMaxNumberParticipants($faker->numberBetween(2, 6));
            $fakeDuration = [60, 90, 120, 180, 240, 300];
            $event->setDuration($faker->randomElement($fakeDuration));
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

        $this->createStaticUser($manager);

    }

    private function createStaticUser($manager)
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

        $event = new Event();

        $event->setName('Sortie Kayak à Saint Herblain');
        $event->setDescription('Sortie Kayak à Saint Herblain, pensez à prendre un pique nique, de la crème solaire et un chapeau!');
        $event->setCampus($manager->getRepository(Campus::class)->findOneBy(['name' => "Saint-Herblain"]));
        $event->setOrganiser($user);
        $event->setMaxNumberParticipants(6);
        $event->setDuration(90);
        $event->setDateTimeStart($faker->dateTimeBetween(" -1 month", " + 1 month "));
        $interval = new DateInterval("P1D");
        $event->setRegistrationDeadline(date_sub($event->getDateTimeStart(), $interval));
        $statusCreated = $manager->getRepository(Status::class)->findOneBy(['name'=>'Ouverte']);
        $event->setStatus($statusCreated);
        $event->setPlace($place);

        $manager->persist($event);

        $event2 = new Event();
        $event2->setName('Apprendre Symfony en s\'amusant');
        $event2->setDescription('Apprendre Symfony dans la bonne humeur avec Taharqa, Camille et Benjamin. Prevoir un goûter et des dolipranes ! ');
        $event2->setCampus($manager->getRepository(Campus::class)->findOneBy(['name' => "Saint-Herblain"]));
        $event2->setOrganiser($user);
        $event2->setMaxNumberParticipants(6);
        $event2->setDuration(240);
        $event2->setDateTimeStart($faker->dateTimeBetween(" -1 month", " + 1 month "));
        $interval = new DateInterval("P1D");
        $event2->setRegistrationDeadline(date_sub($event->getDateTimeStart(), $interval));
        $statusCreated = $manager->getRepository(Status::class)->findOneBy(['name'=>'Annulée']);
        $event2->setStatus($statusCreated);
        $event2->setPlace($place);

        $manager->persist($event2);

        $event3 = new Event();
        $event3->setName('Manger des cailloux');
        $event3->setDescription('Quoi de meilleur que de manger des cailloux? Rejoins-nous pour manger des cailloux');
        $event3->setCampus($manager->getRepository(Campus::class)->findOneBy(['name' => "Saint-Herblain"]));
        $event3->setOrganiser($user);
        $event3->setMaxNumberParticipants(2);
        $event3->setDuration(240);
        $event3->setDateTimeStart($faker->dateTimeBetween(" -1 month", " + 1 month "));
        $interval = new DateInterval("P1D");
        $event3->setRegistrationDeadline(date_sub($event->getDateTimeStart(), $interval));
        $statusCreated = $manager->getRepository(Status::class)->findOneBy(['name'=>'Créée']);
        $event3->setStatus($statusCreated);
        $event3->setPlace($place);

        $manager->persist($event3);

        $userRepository = $manager->getRepository(User::class);
        $allUsers = $userRepository->findAll();

        $event4 = new Event();
        $event4->setName('Apprendre le ping-pong avec Taharqa');
        $event4->setDescription('Taharqa, Champion du monde de Ping-pong vous propose de partager ses talents avec vous. Prévoir une raquette et de l\'eau');
        $event4->setCampus($manager->getRepository(Campus::class)->findOneBy(['name' => "Saint-Herblain"]));
        $event4->setOrganiser($faker->randomElement($allUsers));
        $event4->setOrganiser($user);
        $event4->setMaxNumberParticipants(4);
        $event4->setDuration(120);
        $event4->setDateTimeStart($faker->dateTimeBetween(" -1 month", " + 1 month "));
        $interval = new DateInterval("P1D");
        $event4->setRegistrationDeadline(date_sub($event->getDateTimeStart(), $interval));
        $statusCreated = $manager->getRepository(Status::class)->findOneBy(['name'=>'Créée']);
        $event4->setStatus($statusCreated);
        $event4->setPlace($place);

        $manager->persist($event4);

        $manager->flush();

    }
}
