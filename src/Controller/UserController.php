<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Utils\Uploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/profile', name: 'profile_')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
    #[Route('/list', name: 'list')]
    public function profile(UserRepository $userRepository): Response
    {
        $user= $userRepository->findAll();
        return $this->render('profile/profile.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_register', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(int $id,UserRepository $userRepository): Response
    {

        $user = $userRepository->find($id);

        if(!$user){
            throw $this->createNotFoundException("Oops ! user not found !");
        }

        return $this->render('profile/show.html.twig', [
            'user' => $user
        ]);
    }

 #[Route('/edit/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
 public function edit(SluggerInterface $slugger, Request $request, User $user, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, Uploader $uploader, TokenStorageInterface $tokenStorage): Response
 {
     // Récupère l'utilisateur authentifié
     $authenticatedUser = $tokenStorage->getToken()->getUser();

     // Vérifie si l'utilisateur authentifié est le même que celui qui tente de mettre à jour le profil
     if ($authenticatedUser->getId() !== $user->getId()) {
         $this->addFlash('error', 'Vous n\'avez pas la permission de mettre à jour ce profil');
         return $this->redirectToRoute('sortie_list');
         // Redirige l'utilisateur vers une page d'erreur ou refuse l'accès

     }

     // Crée le formulaire de mise à jour de profil pour l'utilisateur
     $form = $this->createForm(UserType::class, $user);
     $form->handleRequest($request);

     // Vérifie si le formulaire a été soumis et s'il est valide
     if ($form->isSubmitted() && $form->isValid()) {
         // Récupère le mot de passe en clair entré par l'utilisateur
         $plainPassword = $form->get('plainPassword')->getData();

         if ($plainPassword !== null) {
             // Hache le mot de passe en clair avant de le stocker dans la base de données
             $user->setPassword(
                 $userPasswordHasher->hashPassword($user, $plainPassword)
             );
         }

         // Récupère le fichier photo téléchargé par l'utilisateur
         $file = $form->get('photo')->getData();

         if ($file) {
             // Récupère le nom de fichier d'origine sans l'extension
             $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
             // Génère un nom de fichier sûr à partir du nom de fichier d'origine
             $safeFileName = $slugger->slug($originalFileName);
             // Ajoute un identifiant unique pour éviter les collisions de noms de fichiers
             $newFileName = $safeFileName . '-' . uniqid() . '.' . $file->guessExtension();

             try {
                 // Déplace le fichier téléchargé vers le dossier de téléchargement spécifié
                 $file->move(
                     $this->getParameter('upload_photo'),
                     $newFileName
                 );
             } catch (FileException $e) {
                 // Gère l'exception si le téléchargement de fichier échoue
             }

             // Met à jour le nom du fichier photo de l'utilisateur avec le nouveau nom de fichier généré
             $user->setPhoto($newFileName);
         }

         // Sauvegarde les modifications apportées à l'utilisateur dans la base de données
         $userRepository->save($user, true);

         // Redirige l'utilisateur vers la page de profil mise à jour
         return $this->redirectToRoute('profile_show', ['id' => $user->getId()]);
     }

     // Affiche le formulaire de mise à jour de profil pour l'utilisateur
     return $this->render('profile/edit.html.twig', [
         'userUpdateForm' => $form->createView(),
         'user' => $user,
     ]);
 }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('profile_list', [], Response::HTTP_SEE_OTHER);
    }
}
