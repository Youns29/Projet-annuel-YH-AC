<?php

namespace App\Controller;

use App\Repository\InvoiceRepository; 
use App\Entity\Invoice;
use App\Form\InvoiceType;
use App\Service\PdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Cabinet;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface; // Ajout de l'import

class InvoiceController extends AbstractController
{
    #[Route('/invoice', name: 'app_invoice')]
    public function index(EntityManagerInterface $entityManager, InvoiceRepository $invoiceRepository): Response
    {
        // Récupérer l'utilisateur actuellement connecté
        $user = $this->getUser();
        
        // Vérifiez si un utilisateur est connecté
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à vos factures.');
        }
        
        // Récupérer le référentiel (repository) des factures
        $invoice = $entityManager->getRepository(invoice::class)->findOneBy([]); // Remplacez par votre logique
        
        // Récupérer toutes les factures de l'utilisateur actuel
        $userInvoices = $invoiceRepository->findBy(['user' => $user]);
        
        // Récupérer la dernière facture de l'utilisateur actuel (si votre entité a une date de création ou une autre manière d'identifier la dernière facture)
        $latestUserInvoice = $invoiceRepository->findOneBy(['user' => $user], ['created_at' => 'DESC']);
        
        return $this->render('invoice/index.html.twig', [
            'latestInvoice' => $latestUserInvoice,
            'allInvoices' => $userInvoices,
        ]);
    }

#[Route('/invoice/{id}', name: 'invoice_pdf')]
public function generatePdfPersonne(Invoice $invoice = null, PdfService $domPdf, EntityManagerInterface $entityManager): Response
{
    if (!$invoice) {
        throw $this->createNotFoundException('Facture non trouvée');
    }

    $cabinet = $entityManager->getRepository(Cabinet::class)->findOneBy([]);

    if (!$cabinet) {
        throw $this->createNotFoundException('Aucune entreprise trouvée');
    }

    // Get the current user
    $user = $this->getUser();

    if (!$user) {
        throw $this->createNotFoundException('Utilisateur non trouvé');
    }

    $html = $this->renderView('invoice/show.html.twig', [
        'invoice' => $invoice,
        'cabinet' => $cabinet,
        'user' => $user,
    ]);

    // Generate the PDF content using your PdfService
    $domPdf->showPdfFile($html);

    // Create a response with the PDF content
    $response = new Response();
    $response->headers->set('Content-Type', 'application/pdf');

    // Optional: Set the filename for the downloaded PDF
    $response->headers->set('Content-Disposition', 'inline; filename="invoice.pdf"');

    return $response;
}

    // Action pour afficher le formulaire de création de facture
    #[Route('/invoice/create', name: 'invoice_create')]
    public function create(Request $request, Security $security): Response
    {
        $invoice = new Invoice();
        
        // Récupérer l'utilisateur actuel
        $user = $security->getUser();
        $cabinet = $security->getCabinet();

        // Récupérer les informations de l'entreprise (vous devez implémenter cette partie)
        $cabinet = $this->getDoctrine()->getRepository(Cabinet::class)->findOneBy([]); // À adapter à votre logique

        if (!$cabinet) {
            throw $this->createNotFoundException('Aucune entreprise trouvée');
        }

        // Remplir la facture avec les informations de l'utilisateur et de l'entreprise
        $invoice->setUser($user);       // Assurez-vous que votre entité Invoice a une relation avec User
        $invoice->setCabinet($cabinet); // Assurez-vous que votre entité Invoice a une relation avec Cabinet

        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($invoice);
            $entityManager->flush();

            return $this->redirectToRoute('invoice_show', ['id' => $invoice->getId()]);
        }

        return $this->render('invoice/create.html.twig', [
            'form' => $form->createView(),
            'invoice' => $invoice,
            'cabinet' => $cabinet // Passez l'entreprise à Twig pour afficher les informations de l'entreprise dans le formulaire
        ]);
    }

    
// Méthode pour créer une facture après le premier paiement
#[Route('/create_invoice_after_payment', name: 'create_invoice_after_payment')]
public function createInvoiceAfterPayment(Request $request, Security $security, EntityManagerInterface $entityManager, PdfService $pdfService): Response
{
    // Récupérez l'utilisateur actuel
    $user = $security->getUser();
    $cabinet = $security->getCabinet();

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
    // Faites tout ce dont vous avez besoin ici, comme envoyer un e-mail

    $htmlContent = $this->renderView('invoice/show.html.twig', [
        'user' => $user,       // Passez l'utilisateur
        'invoice' => $invoice, // Passez la facture
        'cabinet' => $cabinet, // Passez l'entreprise
        // Ajoutez ici les variables que vous souhaitez passer au template Twig
    ]);

    // Rediriger vers la page de détails de la facture nouvellement créée
    return $this->redirectToRoute('invoice_show', ['id' => $invoice->getId()]);
}
    // Action pour afficher les détails d'une facture spécifique
    #[Route('/invoice/{id}', name: 'invoice_show')]
    public function show(Invoice $invoice = null): Response
    {
        if (!$invoice) {
            throw $this->createNotFoundException('Facture non trouvée');
        }

        return $this->render('invoice/show.html.twig', ['invoice' => $invoice]);
    }

    // Action pour afficher le formulaire de mise à jour d'une facture
    #[Route('/invoice/{id}/edit', name: 'invoice_edit')]
    public function edit(Request $request, Invoice $invoice = null): Response
    {
        if (!$invoice) {
            throw $this->createNotFoundException('Facture non trouvée');
        }

        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettez à jour la facture dans la base de données
            $this->getDoctrine()->getManager()->flush();

            // Redirigez vers la page de détails de la facture mise à jour
            return $this->redirectToRoute('invoice_show', ['id' => $invoice->getId()]);
        }

        return $this->render('invoice/edit.html.twig', [
            'invoice' => $invoice,
            'form' => $form->createView(),
        ]);
    }

    // Action pour supprimer une facture
    #[Route('/invoice/{id}/delete', name: 'invoice_delete')]
    public function delete(Request $request, Invoice $invoice = null): Response
    {
        if (!$invoice) {
            throw $this->createNotFoundException('Facture non trouvée');
        }

        // Supprimez la facture de la base de données
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($invoice);
        $entityManager->flush();

        // Redirigez vers la liste des factures après la suppression
        return $this->redirectToRoute('app_invoice');
    }
}