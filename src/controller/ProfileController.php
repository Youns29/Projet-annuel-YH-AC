<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\ProfileFormType;
use App\Form\DeleteAccountFormType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profil', name: 'app_profile')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté (vous pouvez utiliser $this->getUser())
        $user = $this->getUser();

        // Créer un formulaire pour éditer le profil
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
        }

        // dd($user->getFirstName());

        return $this->render('profile/index.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }

    private $tokenStorage;
    private $mailer;


    public function __construct(TokenStorageInterface $tokenStorage, MailerInterface $mailer)
    {
        $this->tokenStorage = $tokenStorage;
        $this->mailer = $mailer;
    }


    #[Route('/profile/delete', name: 'app_delete_account')]
    public function deleteAccount(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Créez le formulaire de confirmation
        $form = $this->createForm(DeleteAccountFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifiez si l'utilisateur a coché la case de confirmation
            $confirmDelete = $form->get('confirmDelete')->getData();

            if ($confirmDelete) {
                // Supprimez le compte et les données de l'utilisateur
                $entityManager->remove($user);
                $entityManager->flush();

                // Envoyez un e-mail de confirmation
                $this->sendDeleteConfirmationEmail($user->getEmail());

                // Déconnectez automatiquement l'utilisateur après la 
                $this->tokenStorage->setToken(null);

                // Ajoutez un message de succès
                $this->addFlash('success', 'Votre compte a été supprimé avec succès.');

                // Redirigez l'utilisateur vers une page d'accueil ou une page de confirmation
                return $this->redirectToRoute('app_login');
            }
        }


        // Affichez le formulaire de confirmation
        return $this->render('profile/delete_account.html.twig', [
            'deleteAccountForm' => $form->createView(),
        ]);
    }

    private function sendDeleteConfirmationEmail($userEmail)
    {
        $email3 = (new TemplatedEmail())
            ->from('cabinet@architect.com')
            ->to($userEmail)
            ->subject('Confirmation de suppression de compte')
            ->htmlTemplate('email/delete.html.twig');

        $this->mailer->send($email3);
    }
}

