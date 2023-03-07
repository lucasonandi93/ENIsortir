<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\CampusRepository;
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

    public function load(ObjectManager $manager): void
    {
        $this->addUsers(50);
    }


    private function addUsers(int $number)
    {
        $campusrepo = new CampusRepository($this->registry);
        $campus = $campusrepo->findAll();

        for ($i = 0; $i < $number; $i++){

            $user = new User();

            $user
                ->setNom(implode(" ", $this->faker->words(3)))
                ->setPrenom(implode(" ", $this->faker->words(3)))
                ->setEmail($this->faker->email)
                ->setTelephone($this->faker->phoneNumber)
                ->setUsername($this->faker->userName)
                ->setCampus($this->faker->randomElement($campus));

            $password = $this->passwordHasher->hashPassword($user, '123');
            $user->setPassword($password);

            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();

    }
}
