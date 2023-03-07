<?php

namespace App\Controller;

use App\DataFixtures\AppFixtures;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main_home')]
    public function index(EntityManagerInterface $manager , AppFixtures $appFixtures): Response
    {

        //$appFixtures->load($manager);
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
}
