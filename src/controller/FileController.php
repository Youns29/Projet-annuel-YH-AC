<?php

namespace App\Controller;

use App\Entity\Files;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends AbstractController
{
    #[Route('/file', name: 'app_file')]
    public function uploadFile(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        if ($request->isMethod('POST')) {
            $uploadedFile = $request->files->get('file');

            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

                // Obtenir le format du fichier en utilisant pathinfo
                $fileInfo = pathinfo($newFilename);
                $format = $fileInfo['extension'];

                $uploadDirectory = $this->getParameter('upload_directory');
                $uploadedFile->move($uploadDirectory, $newFilename);

                // Obtenir la taille du fichier en utilisant la fonction filesize()
                $tempFilePath = $uploadDirectory . '/' . $newFilename;
                $size = filesize($tempFilePath);
                

                // Récupérer l'utilisateur actuellement authentifié
                $user = $security->getUser();

                // Obtenir le format du fichier
                $format = $uploadedFile->getClientOriginalExtension();

                // Calculez l'espace actuellement utilisé par l'utilisateur
                $useSpace = 0;
                foreach ($user->getFiles() as $file) {
                    $useSpace += $file->getSize();
                }

                $useSpace = ($useSpace + $size) / (1024 * 1024 * 1024); // Convertir en gigaoctets

                // Limite d'espace autorisée pour l'utilisateur
                $stockageSpace = $user->getStockageSpace(); // Vous devez avoir cette valeur définie dans votre entité User
                // dd($useSpace.">".$stockageSpace);

                
                // Vérifiez si l'espace utilisé après l'ajout du nouveau fichier dépasse la limite
                if ($useSpace > $stockageSpace) {
                    // Redirigez l'utilisateur vers la page d'achat de stockage supplémentaire
                    $this->addFlash('error', 'L\'espace de stockage est insuffisant. Veuillez acheter du stockage supplémentaire.');
                    return $this->redirectToRoute('app_stockage');
                }
                // dd($size);

                // Créez une nouvelle instance de l'entité Files
                $file = new Files();
                $file->setFileName($newFilename);
                $file->setSize($size);// Définissez la taille du fichier
                $file->setUser($user);
                $file->setFormat($format); // Définir le format du fichier
                // Définissez d'autres attributs si nécessaire...

                // Persistez l'entité dans la base de données
                $entityManager->persist($file);
                $entityManager->flush();

                // Ajouter un message flash de succès
                $this->addFlash('success', 'Le fichier a été téléchargé avec succès.');

                return $this->redirectToRoute('app_file');
            }
        }
        // Fetch the list of uploaded files
        $user = $security->getUser();
        $uploadedFiles = $entityManager->getRepository(Files::class)->findBy(['user' => $user]);;

        // Calculez l'espace utilisé en ajoutant la taille de chaque fichier
        $useSpace = 0;

        foreach ($uploadedFiles as $file) {
            $useSpace += $file->getSize();
        }

        $useSpace = $useSpace / (1024 * 1024 * 1024); // Convertir en gigaoctets



        $user = $security->getUser();
        $user->setUseSpace($useSpace); // Mettez à jour la valeur useSpace pour l'utilisateur
        $entityManager->persist($user); // Persistez les modifications de l'utilisateur
        $entityManager->flush();


        // Passez l'espace utilisé et la limite d'espace au modèle
        return $this->render('file/index.html.twig', [
            'uploadedFiles' => $uploadedFiles,
            'useSpace' => $useSpace,
            'stockageSpace' => $user->getStockageSpace(), // Limite d'espace depuis l'entité User
        ]);
    }

    // Route pour la visualisation du fichier
    #[Route('/file/view/{id}', name: 'app_view_file')]
    public function viewFile(int $id, EntityManagerInterface $entityManager): Response
    {
        $file = $entityManager->getRepository(Files::class)->find($id);

        if (!$file) {
            throw $this->createNotFoundException('Fichier introuvable');
        }

        $filePath = $this->getParameter('upload_directory') . '/' . $file->getFileName();

        return new BinaryFileResponse($filePath);
    }


    // Route pour le téléchargement du fichier
    #[Route('/file/download/{id}', name: 'app_download_file')]
    public function downloadFile(int $id, EntityManagerInterface $entityManager): Response
    {
        $file = $entityManager->getRepository(Files::class)->find($id);

        if (!$file) {
            throw $this->createNotFoundException('Fichier introuvable');
        }

        $filePath = $this->getParameter('upload_directory') . '/' . $file->getFileName();

        return $this->file($filePath);
    }


    // Route pour la suppression du fichier
    #[Route('/file/delete/{id}', name: 'app_delete_file')]
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

        return $this->redirectToRoute('app_file');
    }
}
