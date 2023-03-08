<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/forgot/password', name: 'app_forgot_password')]
class ForgotPasswordController extends AbstractController
{
    /**
     * @Route("/forgot-password", name="forgot_password")
     */
    public function forgotPassword(Request $request, \Swift_Mailer $mailer, PasswordEncoderInterface $encoder): string
    {
        $email = $request->get('email');

        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $this->addFlash('danger', 'Aucun utilisateur trouvé avec cet email');
            return $this->redirectToRoute('login');
        }

        $resetToken = md5(uniqid());
        $user->setResetToken($resetToken);
        $entityManager->flush();

        $message = (new \Swift_Message('Réinitialisation de mot de passe'))
            ->setFrom('noreply@waytolearnx.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'emails/reset_password.html.twig',
                    ['resetToken' => $resetToken]
                ),
                'text/html'
            );

        $mailer->send($message);

        $this->addFlash('success', 'Un email de réinitialisation de mot de passe vous a été envoyé');
        return $this->redirectToRoute('login');

    }
}
