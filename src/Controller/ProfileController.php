<?php

namespace App\Controller;


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

    #[Route('/{id}', name: 'show')]
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
            throw $this->createNotFoundException("Oops ! Wish not found !");
        }

        $userForm = $this->createForm(UserType::class, $user);

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'userUpdateForm' => $userForm->createView()
        ]);
    }
//    //1 nouvel instance user
//$user = new User();
//
//
//    //2qui prend commme valeur d'attribut les valeurs renseignés
//
//$form = $this->createForm(RegistrationFormType::class, $user);
//$form->handleRequest($request);
//
//if ($form->isSubmitted() && $form->isValid()) {
//    // encode the plain password
//$user->setPassword(
//$userPasswordHasher->hashPassword(
//$user,
//$form->get('plainPassword')->getData()
//)
//
//
//    //je trouve l'objet user pr afficher ces info dans le formulaire
//
//
//
//
//    //3la nouvelle instance de user vient update celle existente de l'user en question
}
