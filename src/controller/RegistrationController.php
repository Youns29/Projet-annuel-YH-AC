<?php

namespace App\Controller;

use App\Entity\Cabinet;
use App\Entity\User;
use App\Entity\Invoice;
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

            // Récupérez l'identifiant de l'entreprise (Cabinet) depuis la base de données ou ailleurs
            $cabinetId = 1; // Remplacez par la logique appropriée pour obtenir l'identifiant de l'entreprise

            // Récupérez l'entité Cabinet à partir de la base de données
            $cabinet = $entityManager->getRepository(Cabinet::class)->find($cabinetId);

            if (!$cabinet) {
                throw $this->createNotFoundException('Aucune entreprise trouvée');
            }

            // Créez la facture
            $invoice = new Invoice();
            $invoiceNumber = date('YmdHis') . '_' . uniqid();
            $invoice->setInvoiceNumber($invoiceNumber);
            $invoice->setInvoiceDate(new \DateTime());
            $invoice->setTotalAmount("20.00");
            $invoice->setTotalWithoutTaxes("16.00");
            $invoice->setTaxeAmount("4.00");
            $invoice->setCreatedAt(new \DateTimeImmutable());
            $invoice->setUser($user);
            $invoice->setCabinet($cabinet);

            $entityManager->persist($invoice);
            $entityManager->flush();
            // do anything else you need here, like send an email

            $htmlContent = $this->renderView('invoice/show.html.twig', [
                'user' => $user,       // Passez l'utilisateur
                'invoice' => $invoice, // Passez la facture
                'cabinet' => $cabinet, // Passez l'entreprise
                // Ajoutez ici les variables que vous souhaitez passer au template Twig
            ]);

            $email = (new TemplatedEmail())
                ->from('cabinet@architect.com')
                ->to($user->getEmail())
                ->subject('Bienvenue dans FileOrganisation')
                ->htmlTemplate('email/welcome.html.twig')
                ->context([
                    'user' => $user
                ]);


            $mailer->send($email);

        
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
