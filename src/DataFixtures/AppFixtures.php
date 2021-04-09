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
        $allStatus = $statusRepository->findAll();

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

        //TODO Phone number ne fonctionne pas en integer

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

            $duration = date_diff($event->getDateTimeStart(), $event->getDateTimeEnd());
            $durationMinutes = $duration->d*1440 + $duration->h*60 + $duration->i;
            $event->setDuration($durationMinutes);

            $interval = new DateInterval("P1D");
            $event->setRegistrationDeadline(date_sub($event->getDateTimeStart(), $interval));
            $event->setDescription($faker->paragraphs($faker->numberBetween(0, 3), true));
            $event->setCampus($faker->randomElement($allCampus));
            $event->setMaxNumberParticipants($faker->numberBetween(2, 6));
            $fakeDuration = [60, 90, 120, 180, 240, 300];
            $event->setDuration($faker->randomElement($fakeDuration));
            $event->setStatus($faker->randomElement($allStatus));
            $event->setPlace($faker->randomElement($allPlaces));
            $event->setOrganiser($faker->randomElement($allUsers));



            $manager->persist($event);
            $manager->flush();
        }

        $eventRepository = $manager->getRepository(Event::class);
        $allEvents = $eventRepository->findAll();

        foreach ($allEvents as $event) {
            for ($i = 0; $i < $event->getMaxNumberParticipants(); $i++) {
                $event->addParticipant($faker->randomElement($allUsers));
             }
        }

        $manager->persist($event);
        $manager->flush();
    }
}
