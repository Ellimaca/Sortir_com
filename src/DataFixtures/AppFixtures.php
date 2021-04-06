<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Event;
use App\Entity\Place;
use App\Entity\Status;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // On utilise Faker avec des données aléatoires en français
        $faker = \Faker\Factory::create("fr_FR");

        // Le nom des différents états possibles pour une sortie
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
            $place->setName($faker->sentence);
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
        for ($i = 0; $i < 5; $i++) {
            $campus = new Campus();
            $campus->setName($faker->city);
            $manager->persist($campus);
        }
        $manager->flush();

        $campusRepository = $manager->getRepository(Campus::class);
        $allCampus = $campusRepository->findAll();

        // Génèration de données aléatoires pour l'entité user
        for ($i = 0; $i < 100; $i++) {
            $user = new User();
            $user->setFirstName($faker->firstName);
            $user->setEmail($faker->email);
            $user->setLastName($faker->lastName);
            $user->setCampus($faker->randomElement($allCampus));
            $user->setPhoneNumber($faker->numberBetween(33600000, 33700000));
            $user->setPassword($faker->password);
            $user->setRoles(["ROLE_USER"]);
            $user->setIsActive(true);
            $user->setIsAdmin(false);

            $manager->persist($user);
        }
        $manager->flush();

        $userRepository = $manager->getRepository(User::class);
        $allUsers = $userRepository->findAll();


        // TODO vérification date

        // Génèration de données aléatoires pour l'entité Event
        for ($i = 0; $i < 100; $i++) {
            $event = new Event();
            $event->setName($faker->sentence);
            $event->setDateTimeStart($faker->dateTimeBetween(" - 1 month"));
            $event->setDateTimeEnd($faker->dateTimeBetween($event->getDateTimeStart(), "+ 2 days"));
            $event->setRegistrationDeadline($faker->dateTimeBetween($event->getDateTimeStart(), "+ 7 days"));
            $event->setDescription($faker->paragraphs($faker->numberBetween(0, 3), true));
            $event->setCampus($faker->randomElement($allCampus));
            $event->setMaxNumberParticipants($faker->numberBetween(2, 100));
            $event->setStatus($faker->randomElement($allStatus));
            $event->setPlace($faker->randomElement($allPlaces));
            $event->setOrganiser($faker->randomElement($allUsers));
            $manager->persist($event);
        }
        $manager->flush();

        // TODO event_user

    }
}
