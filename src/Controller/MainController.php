<?php

namespace App\Controller;

use App\DataFixtures\AppFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    #[Route('/', name: 'main_home')]
    public function index(AppFixtures $appFixtures, EntityManagerInterface $entityManager): Response
    {
    //$appFixtures->load($entityManager); pour la fause donne
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
}
