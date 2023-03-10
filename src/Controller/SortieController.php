<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\modele\ModeleFiltres;
use App\Repository\EtatRepository;
use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\FiltreType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;


#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/list', name: 'list')]
    public function profile(SortieRepository $sortieRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Mettre à jour les sorties qui datent de plus de 1 mois

        $date = new \DateTime();
        $date->sub(new \DateInterval('P1M')); // soustraire 1 mois

        $sorties = $sortieRepository->findOldSorties($date);

        foreach ($sorties as $sortie) {
            $etatHistorise = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'Historisée']);

            if (!$etatHistorise) {
                $etatHistorise = new Etat();
                $etatHistorise->setLibelle('Historisée');
                $entityManager->persist($etatHistorise);
            }

            $sortie->setEtat($etatHistorise);
            $entityManager->flush();
        }

        // Debut des filtes

        $filtres = new ModeleFiltres();
        $filtreForm = $this->createForm(FiltreType::class, $filtres);
        $filtreForm->handleRequest($request);

        $sortieFiltre = $sortieRepository->findFiltered($filtres);

//        dd($sortieFiltre);
        //$sortie = $sortieRepository->findAll();
        return $this->render('sortie/list.html.twig', [
            'sortieFiltre'=>$sortieFiltre, 'filtre' => $filtreForm->createView(),

        ]);
    }


    #[Route('/new', name: 'new')]
    public function new(Request $request, SortieRepository $sortieRepository, UserInterface $user): Response
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

            $sortie->setUser($user); // set the connected user as the organizer

            $sortieRepository->save($sortie, true);

            $this->addFlash('success', 'Sortie créée avec succès !');

            return $this->redirectToRoute('sortie_details', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'sortieForm' => $sortieForm->createView()
        ]);
    }


    #[Route('/{id}', name: 'details')]
    public function show(int $id, SortieRepository $sortieRepository): Response
    {
        //récupération d'une série par son id
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            //lance une erreur 404 si la série n'existe pas
            throw $this->createNotFoundException("Oops ! Serie not found !");
        }

        return $this->render('sortie/details.html.twig', [
            'sortie' => $sortie
        ]);
    }




    #[Route('edit/{id}', name: 'edit')]
    public function edit(Request $request, int $id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $sortieRepository->save($sortie, true);
            $this->addFlash('success', 'Sortie modifiée avec succès.');

            return $this->redirectToRoute('sortie_details', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/inscription/{id}', name: 'inscription')]
    public function inscriptionSortie(int $id, SortieRepository $sortieRepository): Response
    {

        // Récupération de la sortie
        $sortie = $sortieRepository->find($id);

        // Récupération de l'utilisateur
        $user = $this->getUser();

        // Inscription de l'utilisateur
        $sortie->addUser($user);


        $sortieRepository->save($sortie, true);

        // Retour de la réponse/la route

        return $this->redirectToRoute('sortie_list');
        /*return new Response('Utilisateur inscrit');*/
    }

    #[Route('cancel/{id}', name: 'cancel')]
    public function cancelSortie(int $id, SortieRepository $sortieRepository, EtatRepository $etatRepository): Response
    {
        // Récupération de la sortie
        $sortie = $sortieRepository->find($id);


        $etat = $etatRepository->findOneBy(["libelle" => "Annulée"]);



        $sortie->setEtat($etat);

        $sortieRepository->save($sortie, true);


        // Retour de la réponse
        return $this->render('sortie/details.html.twig', [
            'sortie' => $sortie
        ]);
    }




}