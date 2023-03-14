<?php

namespace App\Utils;

use App\Form\modele\ModeleFiltres;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;

class Uploader
{

    public function upload(UploadedFile $file, string $directory, string $nom = ""){

        //création d'un nouveau nom
        $newFileName = $nom . "-" . uniqid() . "." . $file->guessExtension();
        //copy du fichier dans le répertoire de sauvegarde en le renommant
        $file->move($directory, $newFileName);

        return $newFileName;
    }

    public function majEtat(EtatRepository $etatRepository, SortieRepository $sortieRepository, ModeleFiltres $filtres, UserInterface $user, EntityManagerInterface $entityManager)
    {
        $sorties = $sortieRepository->findFiltered($filtres);

        $enCours = $etatRepository->findOneByLibelle("En cours");
        $passee = $etatRepository->findOneByLibelle("Passée");
        $cloturee = $etatRepository->findOneByLibelle("Clôturée");
        $ouverte = $etatRepository->findOneByLibelle("Ouverte");

        foreach ($sorties as $sortie) {
            if ($sortie->getEtat()->getLibelle() !== 'Créee') {
                $dateHeureDebut = clone $sortie->getDateHeureDebut();
                $limiteAnt = clone $dateHeureDebut;
                $limiteAnt->modify('+' . $sortie->getDuree() . 'minute');
                $limitePost = clone $limiteAnt;
                $limitePost->modify('+1month');
                $dateAuj = new \DateTime();

                if ($dateAuj > $limitePost) {
                    $sortie->setEtat($cloturee);
                } elseif ($dateAuj > $limiteAnt) {
                    $sortie->setEtat($passee);
                } elseif ($dateAuj > $dateHeureDebut) {
                    $sortie->setEtat($enCours);
                } else {
                    $sortie->setEtat($ouverte);
                }

                $entityManager->persist($sortie);
            }
        }

        $entityManager->flush();
    }

}