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

        $statusRepository = $manager->getRepository(Status::class);
        $allStatus = $statusRepository->findAll();

        $manager->flush();

        // Tableau qui regroupe les numéros des départements du grand ouest
        $westernRegions = [14, 22, 29, 35, 44, 49, 50, 56, 61, 72, 85];

        // Génèration de données aléatoires pour l'entité City
        for ($i = 0; $i < 50; $i++) {
            $city = new City();
            $city->setName($faker->city);
            $city->setPostCode($faker->randomElement($westernRegions) . $faker->numberBetween(10, 99) * 10);
            $manager->persist($city);
        }

        $cityRepository = $manager->getRepository(City::class);
        $allCity = $cityRepository->findAll();

        $manager->flush();

        // Génèration de données aléatoires pour l'entité Place
        for ($i = 0; $i < 50; $i++) {
            $place = new Place();
            $place->setName($faker->title);
            $place->setStreet($faker->streetAddress);
            $place->setLatitude($faker->latitude);
            $place->setLongitude($faker->longitude);
            $place->setCity($faker->randomElement($allCity));
            $manager->persist($place);
        }

        $placeRepository = $manager->getRepository(Place::class);
        $allPlaces = $placeRepository->findAll();

        $manager->flush();

        // Génèration de données aléatoires pour l'entité Campus
        for ($i = 0; $i < 5; $i++) {
            $campus = new Campus();
            $campus->setName($faker->city);
            $manager->persist($campus);
        }

        $campusRepository = $manager->getRepository(Campus::class);
        $allCampus = $campusRepository->findAll();

        $manager->flush();

        // Génèration de données aléatoires pour l'entité user
        for ($i = 0; $i < 100; $i++) {
            $user = new User();
            $user->setFirstName($faker->lastName);
            $user->setEmail($faker->email);
            $user->setLastName($faker->firstName);
            $user->setCampus($faker->randomElement($allCampus));
            $user->setPhoneNumber($faker->numberBetween(33600000000, 33700000000));
            $user->setPassword($faker->password);
            $user->setRoles(["ROLE_USER"]);
            $user->setIsActive(true);
            $user->setIsAdmin(false);
            $manager->persist($user);
        }

        $userRepository = $manager->getRepository(User::class);
        $allUsers = $userRepository->findAll();

        $manager->flush();

        // Génèration de données aléatoires pour l'entité Event
        for ($i = 0; $i < 100; $i++) {
            $event = new Event();
            $event->setName($faker->title);
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


    }
}
