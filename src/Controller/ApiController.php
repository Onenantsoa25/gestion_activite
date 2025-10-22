<?php
// src/Controller/Manager/DashboardController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\TacheService;
use App\Service\ActiviteService;
use App\Service\UtilisateurService;
use App\Entity\Tache;
use App\Entity\TacheTerminee;
use App\Entity\Activite;
use App\Entity\Utilisateur;
use App\Repository\TacheTermineeRepository;
use App\Service\AnomalieService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mime\MimeTypes;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ApiController extends AbstractController
{
    private TacheService $tacheService;
    private ActiviteService $activiteService;
    private UtilisateurService $utilisateurService;
    private TacheTermineeRepository $tacheTermineeRepository;
    private AnomalieService $anomalieService;

    public function __construct(TacheService $tacheService, ActiviteService $activiteService, UtilisateurService $utilisateurService, TacheTermineeRepository $tacheTermineeRepository, AnomalieService $anomalieService)
    {
        $this->tacheService = $tacheService;
        $this->activiteService = $activiteService;
        $this->utilisateurService = $utilisateurService;
        $this->tacheTermineeRepository = $tacheTermineeRepository;
        $this->anomalieService = $anomalieService;
    }

    #[Route("/api/collaborateur/dashboard", name: "temps_passe_collab", methods: ["GET"])]
    public function tempsPasse(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

        $utilisateur = $this->getUser();
        return $this->json([
            'jours' => $this->tacheService->tempsPasseAujourdHui($utilisateur),
            'semaine' => $this->tacheService->tempsPasseSemaine($utilisateur),
            'nonTermine' => $this->tacheService->countTachesTermineesAujourdHui($utilisateur),
            'encours' => $this->tacheService->countTachesEnCours($utilisateur),
            'afaire' => $this->tacheService->countTachesAFaire($utilisateur),
            'charges' => $this->tacheService->charges_semaine($utilisateur),
        ]);
    }

    // #[Route("/terminer", name: "terminer_tache_g", methods: ["GET"])]
    // public function terminer_page(Request $request): Response {
    //     $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

    //     $tache = $this->tacheService->findById($request->get("id_tache"));
    //     return $this->render('collaborateur/temps-passe-tache.html.twig', [
    //         'tache' => $tache,
    //     ]);
    // }

    #[Route('/api/manager/dashboard', name: 'dashboard_manager', methods: ['GET'])]
    public function dashboard_manager(): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        return $this->json([
            'taches_a_faire' => $this->activiteService->tache_a_faires(),
// vaovao
            'jours' => $this->tacheService->tempsPasseAujourdHui_equipe(),
            'semaine' => $this->tacheService->tempsPasseSemaine_equipe(),
            'nonTermine' => $this->tacheService->countTachesTermineesAujourdHui_equipe(),
            'encours' => $this->tacheService->countTachesEnCours_equipe(),
            'afaire' => $this->tacheService->countTachesAFaire_equipe(),
            'retard_jours' => $this->tacheService->en_retard_jour(),
            'retard_semaine' => $this->tacheService->en_retard_semaine(),
            'evolution' => $this->tacheService->evolution_retard(),
        ]);

    }

//     #[Route('/{id}/justificatif', name: 'api_tache_justificatif', methods: ['GET'])]
//     public function getJustificatif(int $id): Response
//     {
// $tacheTerminee = $this->tacheTermineeRepository->find($id);
// $contenu = $tacheTerminee->getJustificatif(); // string binaire
// if (!$contenu) {
//     return $this->json(['error' => 'Aucun justificatif trouvé.'], 404);
// }

// // Détecter le type réel
// $finfo = new \finfo(FILEINFO_MIME_TYPE);
// $typeMime = $finfo->buffer($contenu) ?: 'application/octet-stream';
// $ext = match($typeMime) {
//     'application/pdf' => 'pdf',
//     'image/jpeg' => 'jpg',
//     'image/png' => 'png',
//     'image/gif' => 'gif',
//     default => 'bin'
// };
// $response = new StreamedResponse(function () use ($contenu) {
//     echo $contenu;
// });

// $response->headers->set('Content-Type', $typeMime);
// $response->headers->set('Content-Disposition', 'inline; filename="justificatif_tache_' . $tacheTerminee->getId() . '.' . $ext . '"');

// return $response;
//     }


// #[Route('/{id}/justificatif', name: 'api_tache_justificatif', methods: ['GET'])]
// public function getJustificatif(int $id): Response
// {
//     $tacheTerminee = $this->tacheTermineeRepository->find($id);

//     if (!$tacheTerminee || !$tacheTerminee->getJustificatif()) {
//         throw $this->createNotFoundException('Justificatif non trouvé.');
//     }

//     $justificatifContent = $tacheTerminee->getJustificatif();

//     return new Response($justificatifContent, 200, [
//         'Content-Type' => 'application/octet-stream',
//         'Content-Disposition' => 'attachment; filename="justificatif_'.$id.'.pdf"',
//     ]);
// }

    #[Route('/{id}/justificatif', name: 'api_tache_justificatif', methods: ['GET'])]
    public function getJustificatif(int $id): Response
    {
        $tacheTerminee = $this->tacheTermineeRepository->find($id);

        if (!$tacheTerminee || !$tacheTerminee->getJustificatif()) {
            throw $this->createNotFoundException('Justificatif non trouvé.');
        }

        // Chemin complet vers le fichier
        $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $tacheTerminee->getJustificatif();

        if (!file_exists($filePath)) {
            $this->addFlash('error', 'Fichier introuvable sur le serveur.');
            return $this->redirectToRoute('liste_taches_collab');
        }

        // 🔎 Détecter le vrai type MIME à partir du contenu
        $finfo = new \finfo(FILEINFO_MIME_TYPE); 
        $mimeType = $finfo->file($filePath) ?: 'application/octet-stream';

        // Récupérer le nom réel du fichier
        $originalFileName = basename($filePath);

        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', $mimeType);

        // ✅ Forcer le téléchargement avec le nom original du fichier
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $originalFileName
        );

        return $response;
    }

    #[Route('/rapport/export-pdf', name: 'rapport_export_pdf')]
    public function exportPdf(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

        $debut = $request->get('debut');
        $fin = $request->get('fin');

        // ⚡ Transformer "null" en null
        $debut = ($debut === 'null') ? null : new \DateTimeImmutable($debut);
        $fin   = ($fin === 'null') ? null : new \DateTimeImmutable($fin);

        $tacheTerminee = $this->tacheService->rapportDates($this->getUser(), $debut, $fin);

        // ⚡ Déterminer la période pour l'affichage
        if ($debut === null && $fin === null) {
            $periode = "Semaine de " . (new \DateTimeImmutable('today'))->format('d/m/Y');
        } elseif ($debut !== null && $fin !== null) {
            $periode = $debut->format('d/m/Y') . " à " . $fin->format('d/m/Y');
        } else {
            $dateNonNull = $debut ?? $fin;
            $periode = "Semaine de " . $dateNonNull->format('d/m/Y');
        }

        // ⚡ Options de Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // ⚡ Rendu du HTML avec Twig
        $html = $this->renderView('rapport/pdf.html.twig', [
            'taches' => $tacheTerminee,
            'periode' => $periode
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->stream('rapport.pdf', ['Attachment' => true]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );
    }

    #[Route('/rapport/export-excel', name: 'rapport_export_excel')]
    public function exportExcel(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

        $debut = $request->get('debut');
        $fin   = $request->get('fin');

        $debut = ($debut === 'null') ? null : new \DateTimeImmutable($debut);
        $fin   = ($fin === 'null') ? null : new \DateTimeImmutable($fin);

        $tacheTerminee = $this->tacheService->rapportDates($this->getUser(), $debut, $fin);

        // Déterminer la période
        if ($debut === null && $fin === null) {
            $periode = "Semaine de " . (new \DateTimeImmutable('today'))->format('d/m/Y');
        } elseif ($debut !== null && $fin !== null) {
            $periode = $debut->format('d/m/Y') . " à " . $fin->format('d/m/Y');
        } else {
            $dateNonNull = $debut ?? $fin;
            $periode = "Semaine de " . $dateNonNull->format('d/m/Y');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rapport');

        // Titre
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'Rapport et récapitulatif d\'activité');
        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', $periode);

        // En-tête du tableau
        $sheet->setCellValue('A4', 'Date')
            ->setCellValue('B4', 'Tâche')
            ->setCellValue('C4', 'Activite du tâche')
            ->setCellValue('D4', 'Durée')
            ->setCellValue('E4', 'Commentaire');

        $sheet->getStyle('A4:D4')->getFont()->setBold(true);
        $sheet->getStyle('A1:D2')->getFont()->setBold(true);

        $row = 5;
        foreach ($tacheTerminee['details'] as $groupe) {
            foreach ($groupe['tache_terminee'] as $index => $tache) {
                $sheet->setCellValue('A'.$row, $index === 0 ? $groupe['date'] : '');
                $sheet->setCellValue('B'.$row, $tache->getTache()?->getTache() ?? '');
                $sheet->setCellValue('C'.$row, $tache->getTache()?->getActivite()?->getActivite() ?? '');
                $sheet->setCellValue('D'.$row, $tache->getTempsPasse());
                $sheet->setCellValue('E'.$row, $tache->getCommentaire());
                $row++;
            }
            // Total du jour
            $sheet->setCellValue('A'.$row, 'Total');
            $sheet->setCellValue('C'.$row, $groupe['total_duree']);
            $row++;
        }

        // Total semaine
        $sheet->setCellValue('A'.$row, 'Total semaine');
        $sheet->setCellValue('C'.$row, $tacheTerminee['total']);

        // Ajuster largeur automatique
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'rapport.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);

        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    #[Route('/api/anomalies/update', name: 'api_update_anomalies', methods: ['POST'])]
    public function updateAnomalies(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER'); // ou ROLE_ADMIN si nécessaire

        try {
            $this->anomalieService->updateAnomalies();
            return $this->json([
                'status' => 'success',
                'message' => 'Anomalies mises à jour avec succès.'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour des anomalies : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/anomalies/count', name: 'api_update_anomalies', methods: ['GET'])]
    public function countAnomalies(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');
        $this->anomalieService->analyse();
        $count = $this->anomalieService->countNonResolues();

        return $this->json(['count' => $count]);
    }

}
