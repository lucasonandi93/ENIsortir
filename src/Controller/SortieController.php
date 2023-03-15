<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\modele\ModeleFiltres;
use App\Repository\EtatRepository;
use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\FiltreType;
use App\Form\SortieType;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Utils\Uploader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;


#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{
    private $entityManager;
    private $etatRepository;

    public function __construct(EntityManagerInterface $entityManager, EtatRepository $etatRepository)
    {
        $this->entityManager = $entityManager;
        $this->etatRepository = $etatRepository;
    }

    #[Route('/list', name: 'list')]
    public function profile(EntityManagerInterface $entityManager, EtatRepository $etatRepository, Uploader $etatSorties, SortieRepository $sortieRepository, Request $request): Response
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
        if ($filtreForm->isSubmitted() && $filtreForm->isValid()) {
            $sortieFiltre = $sortieRepository->findFiltered($filtres);
        } else {
            $etatSorties->majEtat($etatRepository, $sortieRepository, $filtres, $this->getUser(), $entityManager);
            $sortieFiltre = $sortieRepository->findFiltered($filtres);
        }




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
    #[Route('/lieu_by_ville', name: 'lieux_by_ville', methods: ['GET', 'POST'])]
    public function getLieuxByVille(Request $request, LieuRepository $lieuRepository)
    {
        $villeId = $request->request->get('ville_id');

        $lieux = $lieuRepository->findBy(['ville' => $villeId]);

        $lieuxArray = array();
        foreach ($lieux as $lieu) {
            $lieuData = array(
                'id' => $lieu->getId(),
                'nom' => $lieu->getNom()
            );
            array_push($lieuxArray, $lieuData);
        }

        $response = new JsonResponse();
        $response->setData($lieuxArray);

        return $response;
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

        // Vérification si la sortie est complète
        if ($sortie->getUsers()->count() >= $sortie->getNbInscriptionMax()) {
            $this->addFlash('error', 'La sortie est complète.');
            return $this->redirectToRoute('sortie_list');
        }
        if (!($sortie->getEtat()->getLibelle() == "Ouverte")) {
            $this->addFlash('error', "L'inscription n'est pas possible.");
            return $this->redirectToRoute('sortie_list');
        }

        // Vérification si l'utilisateur est déjà inscrit
        $user = $this->getUser();
        if ($sortie->getUsers()->contains($user)) {
            $this->addFlash('error', 'Vous êtes déjà inscrit à cette sortie.');
            return $this->redirectToRoute('sortie_list');
        }

        // Inscription de l'utilisateur
        $sortie->addUser($user);

        $sortieRepository->save($sortie, true);

        // Retour de la réponse/la route
        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/desinscription/{id}', name: 'desinscription')]
    public function desinscriptionSortie(int $id, SortieRepository $sortieRepository): Response
    {
        // Récupération de la sortie
        $sortie = $sortieRepository->find($id); // Récupération de l'utilisateur

        $user = $this->getUser(); // Désinscription de l'utilisateur
        $sortie->removeUser($user);
        $sortieRepository->save($sortie, true); // Retour de la réponse

    return $this->redirectToRoute('sortie_list');
    }


    #[Route('cancel/{id}', name: 'cancel')]
    public function annulerSortie(int $id, Request $request, SortieRepository $sortieRepository, EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        $sortie = $sortieRepository->find($id);

        // Vérifier que l'utilisateur est bien l'organisateur de la sortie
        if ($sortie->getUser() !== $user) {
            throw new AccessDeniedHttpException("Vous n'êtes pas autorisé à annuler cette sortie");
        }

        // Vérifier que la sortie n'a pas encore commencé
        $dateHeureDebut = $sortie->getDateHeureDebut();
        $dateAuj = new \DateTime();
        if ($dateAuj >= $dateHeureDebut) {
            throw new \Exception("Impossible d'annuler une sortie déjà commencée");
        }

        $form = $this->createFormBuilder()
            ->add('motif', TextareaType::class, [
                'label' => "Motif d'annulation",
                'attr' => [
                    'placeholder' => "Indiquez le motif d'annulation de la sortie",
                    'rows' => 4,
                ],
            ])
            ->add('annuler', SubmitType::class, [
                'label' => "Annuler la sortie",
                'attr' => [
                    'class' => 'btn btn-danger',
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Mettre à jour la sortie et la sauvegarder
            $sortie->setEtat($this->etatRepository->findOneByLibelle('Annulée'));
            $sortie->setInfosSortie($sortie->getInfosSortie() . "\nMotif d'annulation : " . $data['motif']);

            $entityManager->flush();

            $this->addFlash('success', 'La sortie a été annulée');

            return $this->redirectToRoute('sortie_details', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/annuler.html.twig', [
            'form' => $form->createView(),
            'sortie' => $sortie,
        ]);
    }




}