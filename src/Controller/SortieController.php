<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\FiltreType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{

    #[Route('/list', name: 'list')]
    public function list(SortieRepository $sortieRepository, Request $request): Response
    {
        $filterForm = $this->createForm(FiltreType::class, null, ['csrf_protection' => false]);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filters = $filterForm->getData();
            $sorties =$sortieRepository-> findFiltered($filters);
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
        $sortie = new Sortie();

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortieRepository->save($sortie);

            $this->addFlash('success', 'Sortie créée avec succès !');
            return $this->redirectToRoute('sortie_list');
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
