<?php
namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\FiltreType;
use App\Form\SortieType;
use App\Form\modele\ModeleFiltres;
use App\Repository\EtatRepository;
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
    public function list(SortieRepository $sortieRepository, Request $request): Response
    {
        $filtres = new ModeleFiltres();
        $filtreForm = $this->createForm(FiltreType::class, $filtres);
        $filtreForm->handleRequest($request);

        $sortieFiltre = $sortieRepository->findFiltered($filtres);

        return $this->render('sortie/list.html.twig', [
            'sortieFiltre' => $sortieFiltre,
            'filtre' => $filtreForm->createView(),
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
    public function details(int $id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException("Oops ! Sortie not found !");
        }

        return $this->render('sortie/details.html.twig', [
            'sortie' => $sortie
        ]);
    }

    #[Route('/{id}/inscrire', name: 'inscrire')]
    public function inscrire(int $id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        // Vérifie que la sortie existe et qu'elle est ouverte aux inscriptions
        if (!$sortie || !$sortie->isOuverte()) {
            throw $this->createNotFoundException("Oops ! Sortie not found !");
        }

        // Vérifie que la date limite d'inscription n'est pas dépassée
        if ($sortie->getDateLimiteInscription() < new \DateTime()) {
            throw $this->createNotFoundException("Oops ! La date limite d'inscription est dépassée !");
        }

        // Vérifie qu'il reste des places libres
        if ($sortie->getNbInscriptionsMax() <= $sortie->getNbInscriptions()) {
            throw $this->createNotFoundException("Oops ! Il n'y a plus de place disponible !");
        }

        // Ajoute le participant à la sortie et sauvegarde en BDD
        $user = $this->getUser();
        $sortie->addUser($user);
        $this->entityManager->persist($sortie);
        $this->entityManager->flush();

        $this->addFlash('success', 'Vous êtes inscrit à la sortie ' . $sortie->getNom() . ' !');

        return $this->redirectToRoute('sortie_details', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/desister', name: 'desister')]
    public function desister(int $id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        // Vérifie que la sortie existe et qu'elle n'a pas encore commencé
        if (!$sortie || !$sortie->isOuverte()) {
            throw $this->createNotFoundException("Oops ! Sortie not found !");
        }

        // Vérifie que le participant est bien inscrit à la sortie
        $user = $this->getUser();
        if (!$sortie->getUsers()) {
            throw $this->createNotFoundException("Oops ! Vous n'êtes pas inscrit à cette sortie !");
        }

        // Vérifie que la sortie n'a pas encore commencé
        if ($sortie->getDateHeureDebut() < new \DateTime()) {
            throw $this->createNotFoundException("Oops ! La sortie a déjà commencé !");
        }

        // Retire le participant de la sortie et sauvegarde en BDD
        $sortie->removeUser($user);
        $this->entityManager->persist($sortie);
        $this->entityManager->flush();

        $this->addFlash('success', 'Vous vous êtes désisté de la sortie ' . $sortie->getNom() . ' !');

        return $this->redirectToRoute('sortie_details', ['id' => $sortie->getId()]);
    }
}