<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{
    #[Route('/invoice', name: 'app_invoice')]
    public function index(): Response
    {
        return $this->render('invoice/index.html.twig', [
            'controller_name' => 'InvoiceController',
        ]);
    }

    #[Route('/invoice/{id}', name: 'invoice_pdf')]
    public function generatePdfPersonne(Invoice $invoice = null, PdfService $domPdf) {
        $html = $this->render('invoice/index.html.twig', ['invoice' => $invoice]);
        $domPdf->showPdfFile($html);
    }
}
