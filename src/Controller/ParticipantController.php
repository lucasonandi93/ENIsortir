<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ModifierType;
use App\Repository\ParticipantRepository;
use App\Utils\Uploader;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    #[Route('/participant/modifier/{id}', name: 'modifier-profil', requirements: ['id' => '\d+'])]
    public function modifierprofil(int $id,EntityManager $entityManager, ParticipantRepository $participantRepository, Request $request, Uploader $uploader): Response
    {
       $user = $this->

       $userForm = $this->createForm(ModifierType::class, $user);

       $userForm->handleRequest($request);

       if($userForm->isSubmitted() && $userForm->isValid()){

           //upload photo
           /**
            * @var UploadedFile $file
            */
           $file = $userForm->get('photo')->getData();
           //appel de l'uploader
           $uploader->upload($file);

       }

        return $this->render('participant/modifier.html.twig');
    }
}
