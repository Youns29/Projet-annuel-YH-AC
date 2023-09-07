<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Stripe;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class StripeController extends AbstractController
{
    #[Route('/stripe', name: 'app_stripe')]
    public function index(): Response
    {
        return $this->render('stripe/index.html.twig', [
            'stripe_key' => $_ENV["STRIPE_KEY"],
        ]);
    }

    #[Route('/stripe/create-charge', name: 'app_stripe_charge', methods: ['POST'])]
    public function createCharge(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        Stripe\Stripe::setApiKey($_ENV["STRIPE_SECRET"]);

        try {
            $charge = Stripe\Charge::create([
                "amount" => 20 * 100,
                "currency" => "eur",
                "source" => $request->request->get('stripeToken'),
                "description" => "Binaryboxtuts Payment Test"
            ]);

            $userId = $request->getSession()->get('recently_registered_user_id');

            if ($userId) {
                // Clear the session variable after use
                $request->getSession()->remove('recently_registered_user_id');

                // Update the total_space for this user
                $repository = $entityManager->getRepository(User::class);
                $user = $repository->find($userId);

                if ($user) {
                    $currentStockageSpace = $user->getStockageSpace();
                    $user->setStockageSpace($currentStockageSpace + 20);
                    $entityManager->persist($user);
                    $entityManager->flush();
                }
            }

            // Envoi de l'e-mail de confirmation
            $userEmail = $user->getEmail();

            $email = (new TemplatedEmail())
                ->from('cabinet@architect.com')
                ->to($userEmail)
                ->subject('Confirmation de paiement pour 20Go d\'espace de stockage')
                ->htmlTemplate('email/paiement.html.twig')
                ->context([
                    'user' => $user
                ]);

            $mailer->send($email);

            $this->addFlash(
                'success',
                'Payment Successful!'
            );

            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        } catch (\Exception $e) {
            $this->addFlash(
                'error',
                'Payment failed: ' . $e->getMessage()
            );

            return $this->redirectToRoute('app_stripe', [], Response::HTTP_SEE_OTHER);
        }
    }
}
