<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Form\EtatType;
use App\Repository\EtatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/etat')]
class EtatController extends AbstractController
{
    // Action pour afficher la liste des états
    #[Route('/', name: 'app_etat_index', methods: ['GET'])]
    public function index(EtatRepository $etatRepository): Response
    {
        // Récupération de tous les états depuis le repository
        $etats = $etatRepository->findAll();

        // Rendu de la vue avec la liste des états
        return $this->render('etat/index.html.twig', [
            'etats' => $etats,
        ]);
    }

    // Action pour créer un nouvel état
    #[Route('/new', name: 'app_etat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EtatRepository $etatRepository): Response
    {
        // Création d'une nouvelle instance d'Etat
        $etat = new Etat();

        // Création du formulaire pour créer un nouvel état
        $form = $this->createForm(EtatType::class, $etat);

        // Traitement du formulaire s'il a été soumis et est valide
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Sauvegarde du nouvel état dans le repository
            $etatRepository->save($etat, true);

            // Redirection vers la liste des états
            return $this->redirectToRoute('app_etat_index', [], Response::HTTP_SEE_OTHER);
        }

        // Rendu de la vue pour créer un nouvel état
        return $this->renderForm('etat/new.html.twig', [
            'etat' => $etat,
            'form' => $form,
        ]);
    }

    // Action pour afficher les détails d'un état
    #[Route('/{id}', name: 'app_etat_show', methods: ['GET'])]
    public function show(Etat $etat): Response
    {
        // Rendu de la vue avec les détails de l'état
        return $this->render('etat/show.html.twig', [
            'etat' => $etat,
        ]);
    }

    // Action pour éditer un état existant
    #[Route('/{id}/edit', name: 'app_etat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Etat $etat, EtatRepository $etatRepository): Response
    {
        // Création du formulaire pour éditer un état existant
        $form = $this->createForm(EtatType::class, $etat);

        // Traitement du formulaire s'il a été soumis et est valide
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Sauvegarde de l'état modifié dans le repository
            $etatRepository->save($etat, true);

            // Redirection vers la liste des états
            return $this->redirectToRoute('app_etat_index', [], Response::HTTP_SEE_OTHER);
        }

        // Rendu de la vue pour éditer un état existant
        return $this->renderForm('etat/edit.html.twig', [
            'etat' => $etat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_etat_delete', methods: ['POST'])]
    public function delete(Request $request, Etat $etat, EtatRepository $etatRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$etat->getId(), $request->request->get('_token'))) {
            $etatRepository->remove($etat, true);
        }

        return $this->redirectToRoute('app_etat_index', [], Response::HTTP_SEE_OTHER);
    }
}
