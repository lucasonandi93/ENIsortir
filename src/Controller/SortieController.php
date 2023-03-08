<?php

namespace App\Controller;

use App\Repository\EtatRepository;
use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\FiltreType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/list', name: 'list')]
    public function list(Request $request, SortieRepository $sortieRepository, EtatRepository $etatRepository): Response
    {
        $filterForm = $this->createForm(FiltreType::class, null, ['csrf_protection' => false]);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filters = $filterForm->getData();
            $sorties = $sortieRepository->findFiltered($filters);
        } else {
            //$sorties = $sortieRepository->findAllOrderedBySites();
            //$sorties = $sortieRepository->findByNom('');
            $sorties = $sortieRepository->findAll();
        }

        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
            'filterForm' => $filterForm->createView(),
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, SortieRepository $sortieRepository): Response
    {
        $etatRepository = $this->entityManager->getRepository(Etat::class);

        $sortie = new Sortie();

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $etatCree = $etatRepository->findOneBy(['libelle' => 'Créée']);
            if (!$etatCree) {
                $etatCree = new Etat();
                $etatCree->setLibelle('Créée');
                $this->entityManager->persist($etatCree);
            }
            $sortie->setEtat($etatCree);

            $sortieRepository->save($sortie, true);

            $this->addFlash('success', 'Sortie créée avec succès !');

            // récupérer la liste de sorties actualisée
            $sorties = $sortieRepository->findAll();

            return $this->render('sortie/list.html.twig', [
                'sorties' => $sorties,
                'filterForm' => $this->createForm(FiltreType::class)->createView()
            ]);
        }

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'sortieForm' => $sortieForm->createView()
        ]);
    }


    #[Route('/{id}', name: 'details')]
    public function details(Sortie $sortie): Response
    {
        return $this->render('sortie/details.html.twig', [
            'sortie' => $sortie
        ]);
    }
}
