<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
