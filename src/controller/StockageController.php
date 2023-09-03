<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StockageController extends AbstractController
{
    #[Route('/stockage', name: 'app_stockage')]
    public function index(): Response
    {
    // Récupérer l'utilisateur connecté
    $user = $this->getUser();

    // Récupérer les informations de stockage de l'utilisateur
    $stockageSpace = $user->getStockageSpace();
    $useSpace = $user->getUseSpace();

    // Calculer le pourcentage d'utilisation de l'espace
    $percentageUsed = ($useSpace / $stockageSpace) * 100;

    return $this->render('stockage/stockage.html.twig', [
        'user' => $user,
        'stockageSpace' => $stockageSpace,
        'useSpace' => $useSpace,
        'percentageUsed' => $percentageUsed,
    ]);
    }

    #[Route('/stripeConnected', name: 'app_stripe_connected')]
    public function stripeConnected(): Response
    {
        return $this->render('stockage/add.stockage.html.twig', [
            'stripe_key' => $_ENV["STRIPE_KEY"],
        ]);
    }
 
 
    #[Route('/stripeConnected/addStockageConnected', name: 'app_add_stockage', methods: ['POST'])]
    public function addStockageConnected(Request $request, EntityManagerInterface $entityManager)
    {
        Stripe\Stripe::setApiKey($_ENV["STRIPE_SECRET"]);
        Stripe\Charge::create ([
                "amount" => 20 * 100,
                "currency" => "eur",
                "source" => $request->request->get('stripeToken'),
                "description" => "Achat supplémentaire"
        ]);

        $user = $this->getUser();
        $currentStockageSpace = $user->getstockageSpace();
        $user->setstockageSpace($currentStockageSpace + 20);

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Payment Successful!'
        );
        return $this->redirectToRoute('app_stockage', [], Response::HTTP_SEE_OTHER);
    }
}
