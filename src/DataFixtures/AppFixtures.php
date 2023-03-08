<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Sortie;
use App\Entity\User;
use App\Entity\Ville;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{ private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private Generator $faker;
    private ManagerRegistry $registry;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ManagerRegistry $registry)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->faker = Factory::create('fr_FR');
        $this->registry = $registry;
    }

    public function addCampus(ObjectManager $manager)
    {
        $campusNames = ['Rennes', 'Quimper', 'Niort', 'Nantes'];

        foreach ($campusNames as $name) {
            $campus = new Campus();
            $campus->setNom($name);

            $manager->persist($campus);
        }

        $manager->flush();
    }

    public function addUser (ObjectManager $manager)
    {
        $campuses = $manager->getRepository(Campus::class)->findAll();
        $defaultCampus = $this->registry->getRepository(Campus::class)->find(rand(1, 4));

        for ($i = 0; $i < 50; $i++) {
            $user = new User();
            $user
                ->setNom(implode(" ", $this->faker->words(3)))
                ->setPrenom(implode(" ", $this->faker->words(3)))
                ->setEmail($this->faker->email)
                ->setTelephone($this->faker->phoneNumber)
                ->setUsername($this->faker->userName);

            $campus = !empty($campuses) ? $this->faker->randomElement($campuses) : $defaultCampus;



            $user->setCampus($campus);

            $password = $this->passwordHasher->hashPassword($user, '123');
            $user->setPassword($password);

            $manager->persist($user);
        }

        $manager->flush();

    }

    private function addVille(int $number)
    {
        for ($i = 0; $i < $number; $i++){
            $ville = new Ville();

            $ville
                ->setNom(implode(" ", $this->faker->words(1)))
                ->setCodePostal($this->faker->numberBetween(10000, 40000));

            $this->entityManager->persist($ville);
        }

        $this->entityManager->flush();

    }


    private function addSortie(int $number)
    {
        $etatrepo = new EtatRepository($this->registry);
        $etat = $etatrepo->findAll();

        $lieurepo = new LieuRepository($this->registry);
        $lieu = $lieurepo->findAll();

        for ($i = 0; $i < $number; $i++){

            $sortie = new Sortie();

            $sortie
                ->setNom(implode(" ", $this->faker->words(3)))
                ->setInfosSortie(implode(" ",$this->faker->text(40)))
                ->setDuree($this->faker->numberBetween(30, 240))
                ->setDateHeureDebut($this->faker->dateTime);
            $date = clone  $sortie->getDateHeureDebut();
            $sortie->setDateLimiteInscription($this->faker->dateTimeBetween($date->modify('-1 week'), ($date->modify('+4 day'))))
                ->setNbInscriptionMax($this->faker->numberBetween(10, 50))
                ->setLieu($this->faker->)


        }


    }



    public function load(ObjectManager $manager): void
    {
        $this->addCampus($manager);
        $this->addUser($manager);
    }
}