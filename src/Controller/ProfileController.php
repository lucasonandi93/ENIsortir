<?php

namespace App\Controller;


use App\Form\RegistrationFormType;
use App\Repository\UserRepository;

use Cassandra\Type\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profile', name: 'profile_')]
class ProfileController extends AbstractController
{
    #[Route('/list', name: 'list')]
    public function profile(UserRepository $userRepository): Response
    {
        $user= $userRepository->findAll();
        return $this->render('profile/profile.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function profileshow(int $id,UserRepository $userRepository): Response
    {
        //récupération d'une série par son id
        $user = $userRepository->find($id);

        if(!$user){
            //lance une erreur 404 si la série n'existe pas
            throw $this->createNotFoundException("Oops ! user not found !");
        }

        return $this->render('profile/show.html.twig', [
            'user' => $user
        ]);
    }
    #[Route('/edit/{id}', name: 'update', requirements: ['id' => '\d+'])]
    public function update(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException("Oops ! Profile not found !");
        }

        $userForm = $this->createForm(RegistrationFormType::class, $user);


        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'userUpdateForm' => $userForm->createView()
        ]);
    }
}
