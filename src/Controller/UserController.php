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
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
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
    public function edit(SluggerInterface $slugger,Request $request, User $user, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher, Uploader $uploader): Response
 {
     $form = $this->createForm(UserType::class, $user);
     $form->handleRequest($request);

     if ($form->isSubmitted() && $form->isValid()) {
         if ($form->get('plainPassword')->getData() !== null) {
             $user->setPassword(
                 $userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData())
             );
         }

         /**
          * @var UploadedFile $file
          */

             $file = $form->get('photo')->getData();
         if ($file) {
             $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
             $safeFileName = $slugger->slug($originalFileName);
             $newFileName = $safeFileName . '-' . uniqid() . '.' . $file->guessExtension();
             try {
                 $file->move(
                     $this->getParameter('upload_photo'),
                     $newFileName
                 );
             } catch (FileException $e) {

             }
             $user->setPhoto($newFileName);
//            $newFileName = $uploader->upload(
//                $file,
//                $this->getParameter('upload_photo'),
//                $user->getNom()
//            );
             $user->setPhoto($newFileName);
         }

             $userRepository->save($user, true);

             return $this->redirectToRoute('profile_list', [], Response::HTTP_SEE_OTHER);
     }
     return $this->renderForm('profile/edit.html.twig', [
         'user' => $user,
         'userUpdateForm' => $form,
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
