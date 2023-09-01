<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(MailerInterface $mailer, Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UserAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            
            $request->getSession()->set('recently_registered_user_id', $user->getId());
            // do anything else you need here, like send an email
            $email = (new TemplatedEmail())
                ->from('cabinet@architect.com')
                ->to($user->getEmail())
                ->subject('Bienvenue dans FileOrganisation')
                ->htmlTemplate('email/welcome.html.twig')
                ->context([
                    'user' => $user
                ]);



            $mailer->send($email);

            $email2 = (new TemplatedEmail())
            ->from('cabinet@architect.com')
            ->to($user->getEmail())
            ->subject('Confirmation de paiement pour 20Go d\'espace de stockage')
            ->htmlTemplate('email/paiement.html.twig')
            ->context([
                'user' => $user
            ]);



        $mailer->send($email2);

        
            // return $userAuthenticator->authenticateUser(
            //      $user,
            //      $authenticator,
            //      $request
            // );

            return $this->redirectToRoute('app_stripe');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
