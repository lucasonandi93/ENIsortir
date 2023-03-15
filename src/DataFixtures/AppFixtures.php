<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
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




    public function addEtat(ObjectManager $manager)
    {
        $etatLibelle = ['En cours', 'Ouverte', 'Archivée', 'Créée', 'Terminée', 'Annulée', 'Complet'];

        foreach ($etatLibelle as $name) {
            $etat = new Etat();
            $etat->setLibelle($name);

            $manager->persist($etat);
        }

        $manager->flush();
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

    public function addAdmin ()
    {


        $campus = $this->entityManager->getRepository(Campus::class)->findAll();


        $user = new User();
        $user
            ->setNom('Admin')
            ->setPrenom('Admin')
            ->setEmail('admin@admin.admin')
            ->setTelephone($this->faker->phoneNumber)
            ->setUsername('adminator')
            ->setRoles(['ROLE_ADMIN']);



        $user->setCampus($this->faker->randomElement($campus));

        $password = $this->passwordHasher->hashPassword($user, '123');
        $user->setPassword($password);

        $this->entityManager->persist($user);

        $this->entityManager->flush();

    }


    public function addUser (int $number)
    {


        $campus = $this->entityManager->getRepository(Campus::class)->findAll();


        for ($i = 0; $i < $number; $i++) {
            $user = new User();
            $user
                ->setNom($this->faker->name)
                ->setPrenom($this->faker->name)
                ->setEmail($this->faker->email)
                ->setTelephone($this->faker->phoneNumber)
                ->setUsername($this->faker->userName)
                ->setRoles(['ROLE_USER']);



            $user->setCampus($this->faker->randomElement($campus));

            $password = $this->passwordHasher->hashPassword($user, '123');
            $user->setPassword($password);

            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();

    }

    private function addVille(int $number)
    {
        for ($i = 0; $i < $number; $i++){
            $ville = new Ville();

            $ville
                ->setNom($this->faker->city)
                ->setCodePostal($this->faker->postcode);

            $this->entityManager->persist($ville);
        }

        $this->entityManager->flush();

    }

    private function addLieu(int $number)
    {
        $villes = $this->entityManager->getRepository(Ville::class)->findAll();


        for ($i = 0; $i < $number; $i++){
            $lieu = new Lieu();

            $lieu
                ->setNom( $this->faker->address)
                ->setRue( $this->faker->streetName);
                $lieu->setVille($this->faker->randomElement($villes));
            $this->entityManager->persist($lieu);
        }

        $this->entityManager->flush();
    }


    private function addSortie(int $number)
    {
        $etat = $this->entityManager->getRepository(Etat::class)->findAll();
        $campus = $this->entityManager->getRepository(Campus::class)->findAll();
        $lieu = $this->entityManager->getRepository(Lieu::class)->findAll();
        $user = $this->entityManager->getRepository(User::class)->findAll();

        for ($i = 0; $i < $number; $i++){

            $sortie = new Sortie();

            $sortie
                ->setNom(implode(" ", $this->faker->words(3)))
                ->setInfosSortie(implode(" ",$this->faker->words(15)))
                ->setDuree($this->faker->numberBetween(30, 240))
                ->setDateHeureDebut($this->faker->dateTimeBetween('-3 month','+1 month'));
                $dateHeureDebut = clone  $sortie->getDateHeureDebut();
                $sortie->setDateLimiteInscription($this->faker->dateTimeBetween($dateHeureDebut->modify('-1 week'), $dateHeureDebut))
                ->setNbInscriptionMax($this->faker->numberBetween(10, 50))
                ->setCampus($this->faker->randomElement($campus))
                ->setLieu($this->faker->randomElement($lieu));
                $ville = clone  $sortie->getLieu();
                $sortie->setVille($ville->getVille())
                ->setUser($this->faker->randomElement($user))
                ->setEtat($this->faker->randomElement($etat));

            $this->entityManager->persist($sortie);
        }

        $this->entityManager->flush();

    }



    public function load(ObjectManager $manager): void
    {

        $this->addVille(4);
        $this->addLieu(10);
        $this->addCampus($manager);
        $this->addEtat($manager);
        $this->addUser(50);
        $this->addSortie(50);
        $this->addAdmin();
    }
}