<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use App\Entity\Files;
use App\Form\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

class AdminController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('/admin', name: 'app_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminPage(): Response
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $users = $userRepository->findAll();
        $totalUsers = $userRepository->count([]);

        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'totalUsers' => $totalUsers,
        ]);
    }

    #[Route('/admin/files', name: 'admin_files')]
    public function listAllFiles(EntityManagerInterface $entityManager): Response
    {
        // Récupérez la liste de tous les utilisateurs et leurs fichiers
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('admin/list_files.html.twig', [
            'users' => $users,
        ]);
    }

    // Route pour la visualisation de la liste des fichier
    #[Route('/file/view/{id}', name: 'app_view_listfile')]
    public function viewFile(int $id, EntityManagerInterface $entityManager): Response
    {
        $file = $entityManager->getRepository(Files::class)->find($id);
    
        if (!$file) {
            throw $this->createNotFoundException('Fichier introuvable');
        }
    
        $filePath = $this->getParameter('upload_directory') . '/' . $file->getFileName();
    
        return new BinaryFileResponse($filePath);
    }

    // Route pour la suppression du fichier
    #[Route('/file/delete/{id}', name: 'app_delete_listfile')]
    public function deleteFile(int $id, EntityManagerInterface $entityManager): Response
    {
        $file = $entityManager->getRepository(Files::class)->find($id);

        if (!$file) {
            throw $this->createNotFoundException('Fichier introuvable');
        }

        // Supprimez le fichier du répertoire de téléchargement
        $filePath = $this->getParameter('upload_directory') . '/' . $file->getFileName();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Supprimez l'entité de la base de données
        $entityManager->remove($file);
        $entityManager->flush();

        // Ajoutez un message flash de succès
        $this->addFlash('success', 'Le fichier a été supprimé avec succès.');

        return $this->redirectToRoute('admin_files');
    }

    #[Route('/admin/profile', name: 'admin_profile')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminProfile(Request $request, FormFactoryInterface $formFactory, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur administrateur connecté
        $admin = $this->getUser();

        // Créer un formulaire pour mettre à jour le profil de l'administrateur
        $form = $formFactory->create(ProfileFormType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($admin);
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
        }

        return $this->render('admin/profile.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }

    #[Route('/admin/stats', name: 'admin_stats')]
    public function index(): Response
    {
        // Récupérez le nombre total de fichiers téléchargés par tous les utilisateurs
        $totalFiles = $this->entityManager->getRepository(Files::class)->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Récupérez le nombre de fichiers téléchargés aujourd'hui
        $today = new DateTime();
        $today->setTime(0, 0, 0);

        $filesToday = $this->entityManager->getRepository(Files::class)->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.uploadDate >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();

        // Récupérez la répartition du nombre de fichiers par client
        $userFiles = $this->entityManager->getRepository(User::class)->createQueryBuilder('u')
        ->select('u.firstname, u.lastName, COUNT(f.id) as fileCount')
        ->leftJoin('u.files', 'f')
        ->groupBy('u.firstname, u.lastName')
        ->getQuery()
        ->getResult();

    // Récupérez les données pour le graphique du mois dernier
    $lastMonthFiles = $this->getLastMonthFiles();

    return $this->render('admin/statistiques.html.twig', [
        'totalFiles' => $totalFiles,
        'filesToday' => $filesToday,
        'userFiles' => $userFiles,
        'lastMonthFiles' => $lastMonthFiles,
    ]);
}

// Méthode pour récupérer les données le graphique
private function getLastMonthFiles()
{
     $qb = $this->entityManager->getRepository(Files::class)->createQueryBuilder('f')
        ->select("DATE_FORMAT(f.uploadDate, '%Y-%m-%d') as date, COUNT(1) as count")
         ->groupBy('date');

     $results = $qb->getQuery()->getResult();

     $data = [];

     foreach ($results as $result) {
         $data[] = [
             'label' => $result['date'], // Format de la date
             'count' => $result['count'],
         ];
     }

     return $data;
 }
}
