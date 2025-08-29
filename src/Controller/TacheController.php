<?php
// src/Controller/Manager/DashboardController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\TacheService;
use App\Service\ActiviteService;
use App\Service\UtilisateurService;
use App\Entity\Tache;
use App\Entity\Activite;
use App\Entity\Utilisateur;
use App\Service\TypeActiviteService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TacheController extends AbstractController
{
    private TacheService $tacheService;
    private ActiviteService $activiteService;
    private UtilisateurService $utilisateurService;
    private TypeActiviteService $typeActiviteService;

    public function __construct(TacheService $tacheService, ActiviteService $activiteService, UtilisateurService $utilisateurService, TypeActiviteService $typeActiviteService)
    {
        $this->tacheService = $tacheService;
        $this->activiteService = $activiteService;
        $this->utilisateurService = $utilisateurService;
        $this->typeActiviteService = $typeActiviteService;
    }

    #[Route('/manager/tache/ajouter', name: 'ajouter', methods: ['GET'])]
    public function getAllTache(SessionInterface $session): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        $activite = $session->get('activite');

        if (!$activite || !$activite instanceof Activite) {
            $this->addFlash('error', 'Aucune activité trouvée dans la session.');
            return $this->redirectToRoute('listes_tache_ajout_manager'); // ou une autre page d’accueil
        }

        $taches = $this->tacheService->findAllByActivite($activite);

        return $this->render('manager/creation-tache.html.twig');
    }

    #[Route('/manager/tache/ajouter', name: 'ajouter_tache_manager', methods: ['POST'])]
    public function insertTache(SessionInterface $session, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');
        // $activite = $session->get('activite');
        $activiteId = $session->get('activite')?->getId();

        $nom = $request->request->get('nom');
        $date = new \DateTime($request->request->get('date'));

        $estimation = $request->request->get('estimation'); // string
        $estimation = floatval($estimation);

        $tache = new Tache();
        $tache->setTache($nom);
        $tache->setDateEcheance($date);
        $tache->setUtilisateur($session->get('utilisateur'));
        $tache->setEstimation($estimation);

        // if (!$activite || !$activite instanceof Activite) {
        //     $this->addFlash('error', 'Aucune activité trouvée dans la session.');
        //     return $this->redirectToRoute('insert_activite_manager'); // ou une autre page d’accueil
        // }
        if (!$activiteId) {
            $this->addFlash('error', 'Aucune activité trouvée dans la session.');
            return $this->redirectToRoute('insert_activite_manager');
        }

        $activite = $this->activiteService->findById($activiteId);
        if (!$activite) {
            $this->addFlash('error', 'Activité introuvable.');
            return $this->redirectToRoute('insert_activite_manager');
        }

        $tache->setActivite($activite);

        $this->tacheService->insertion_manager($tache);

        // $taches = $this->tacheService->findAllByActivite($activite);
        return $this->redirectToRoute('listes_tache_ajout_manager');
    }

    #[Route('/manager/tache/attribuer', name: 'attribuer', methods: ['GET'])]
    public function attribuer(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        $utilisateur = $this->utilisateurService->findById($request->get('id_utilisateur'));
        $tache = $this->tacheService->findById($request->get('id_tache'));

        $this->tacheService->attribuer($tache, $utilisateur);

        return $this->redirectToRoute('listes_tache_ajout_manager');
    }

    #[Route('/collaborateur/taches', name: 'liste_taches_collab', methods: ['GET'])]
    public function activites_collaborateur(SessionInterface $session): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');
        // $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

        // $utilisateurSession = $session->get('utilisateur');
        // if (!$utilisateurSession) {
        //     throw new \RuntimeException('Utilisateur non trouvé en session');
        // }

        $utilisateur = $this->getUser(); //$this->utilisateurService->findById($utilisateurSession->getId());

        // $activites = $this->activiteService->findAllByUser($utilisateur);
        $activites = $this->typeActiviteService->findAll();

        // $taches = $this->tacheService->findAllByUser($utilisateur);
        $taches = $this->tacheService->tachesNonTerminees($utilisateur);

        return $this->render('collaborateur/liste-taches.html.twig', [
            'taches' => $taches,
            'activites' => $activites,
        ]);
    }

    #[Route('/collaborateur/taches/activite/filtre', name: 'liste_taches_collab_filtre', methods: ['GET'])]
    public function activites_collaborateur_filtre(Request $request, SessionInterface $session): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');
        // $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

        // $utilisateurSession = $session->get('utilisateur');
        // if (!$utilisateurSession) {
        //     throw new \RuntimeException('Utilisateur non trouvé en session');
        // }
        $activite = $this->activiteService->findById($request->get('id_activite'));

        $utilisateur = $this->getUser(); //$this->utilisateurService->findById($utilisateurSession->getId());

        $activites = $this->typeActiviteService->findAll();

        // $taches = $this->tacheService->findAllByUser($utilisateur);
        $taches = $this->tacheService->tachesNonTermineesActivite($utilisateur, $activite);

        return $this->render('collaborateur/liste-taches.html.twig', [
            'taches' => $taches,
            'activites' => $activites,
        ]);
    }

    #[Route('/collaborateur/taches/activite', name: 'liste_taches_activite_collab', methods: ['GET'])]
    public function activites_taches_collab(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');
        $id_activite = $request->get('id_activite');
        $activite = $this->activiteService->findById($id_activite);
        $taches = $this->tacheService->getAllActivite_collab($this->getUser(), $activite);
        return $this->render('collaborateur/liste-taches-activite.html.twig', [
            'taches' => $taches,
        ]);
    }

    #[Route("/collaborateur/tache/terminer/{id_tache}", name: "saisie_terminer_tache_collab", methods: ["GET"])]
    public function saisie_temps_tache(int $id_tache): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

        $tache = $this->tacheService->findById($id_tache);

        if (!$tache) {
            $this->addFlash('error', 'Tâche introuvable.');
            // return $this->redirectToRoute('liste_taches_collab');
        }

        return $this->render('collaborateur/temps-passe-tache.html.twig', [
            'tache' => $tache,
        ]);
    }

    // #[Route("/collaborateur/tache/terminer", name: "terminer_tache_collab", methods: ["POST"])]
    // public function terminer_tache(Request $request): Response
    // {
    //     $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

    //     $id_tache = $request->get('id_tache');
    //     // $date = new \DateTime($request->get('date'));
    //     $date = new \DateTime(); // Date et heure actuelles

    //     $temps_passe = floatval($request->get('temps_passe'));

    //     if (!$id_tache || !$date || !$temps_passe) {
    //         $this->addFlash('error', 'Données manquantes pour terminer la tâche.');
    //         return $this->redirectToRoute('liste_taches_collab');
    //     }

    //     $this->tacheService->terminer($id_tache, $date, $temps_passe);
    //     $tache = $this->tacheService->findById($id_tache);

    //     return $this->redirectToRoute('liste_taches_activite_collab', ['id_activite' => $tache->getActivite()->getId()]);
    // }

// #[Route("/collaborateur/tache/terminer", name: "terminer_tache_collab", methods: ["POST"])]
// public function terminer_tache(Request $request): Response
// {
//     $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

//     $id_tache = $request->get('id_tache');
//     $date = new \DateTime();
//     $temps_passe = floatval($request->get('temps_passe'));
//     $commentaire = $request->get('commentaire');

//     if (!$id_tache || !$date || !$temps_passe) {
//         $this->addFlash('error', 'Données manquantes pour terminer la tâche.');
//         return $this->redirectToRoute('liste_taches_collab');
//     }

//     // Gestion du fichier justificatif
//     $file = $request->files->get('justificatif');
//     if ($file) {
//         $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
//         if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
//             $this->addFlash('error', 'Le fichier doit être un PDF ou une image.');
//             return $this->redirectToRoute('liste_taches_collab');
//         }

//         $content = file_get_contents($file->getPathname());

//         $stream = fopen('php://memory', 'r+');
//         fwrite($stream, $content);
//         rewind($stream);

//         $justificatifData = $stream;
//     }

//     // Appel du service pour terminer la tâche
//     $this->tacheService->terminerTache(
//         $id_tache,
//         $date,
//         $temps_passe,
//         $commentaire,
//         $justificatifData
//     );

//     $tache = $this->tacheService->findById($id_tache);

//     return $this->redirectToRoute('liste_taches_activite_collab', [
//         'id_activite' => $tache->getActivite()->getId()
//     ]);
// }

#[Route("/collaborateur/tache/terminer", name: "terminer_tache_collab", methods: ["POST"])]
public function terminer_tache(Request $request): Response
{
    $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

    $id_tache = $request->get('id_tache');
    $date = new \DateTime(); // Date actuelle
    $temps_passe = floatval($request->get('temps_passe'));
    $commentaire = $request->get('commentaire');

    // Vérification des champs obligatoires
    if (!$id_tache || !$date || !$temps_passe) {
        $this->addFlash('error', 'Données manquantes pour terminer la tâche.');
        return $this->redirectToRoute('liste_taches_collab');
    }

    // Gestion du fichier justificatif
    $justificatifPath = null;
    /** @var UploadedFile $file */
    $file = $request->files->get('justificatif');
    if ($file) {
        // Vérification du type de fichier (PDF ou image)
        $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            $this->addFlash('error', 'Le fichier justificatif doit être un PDF ou une image (JPG, PNG, GIF).');
            return $this->redirectToRoute('liste_taches_collab');
        }

        // Définir le dossier de stockage (ex: public/uploads/justificatifs)
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/justificatifs';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Générer un nom de fichier unique
        $newFilename = uniqid('justif_') . '.' . $file->guessExtension();

        // Déplacer le fichier
        $file->move($uploadDir, $newFilename);

        // Chemin à stocker en base (relatif au dossier public)
        $justificatifPath = 'uploads/justificatifs/' . $newFilename;
    }

    // Appel du service pour terminer la tâche
    $this->tacheService->terminerTache($id_tache, $date, $temps_passe, $commentaire, $justificatifPath);

    $tache = $this->tacheService->findById($id_tache);

    return $this->redirectToRoute('liste_taches_activite_collab', [
        'id_activite' => $tache->getActivite()->getId()
    ]);
}

    #[Route("/tache/planifier/{id_tache}", name: "planifier_tache", methods: ["GET"])]
    public function planifier_tache(int $id_tache): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

        $tache = $this->tacheService->findById($id_tache);

        if (!$tache) {
            $this->addFlash('error', 'Tâche introuvable.');
            return $this->redirectToRoute('liste_taches_collab');
        }

        return $this->render('collaborateur/planifier-tache.html.twig', [
            'tache' => $tache,
        ]);
    }

    #[Route("/tache/planifier", name: "planifier_tache_collab", methods: ["POST"])]
    public function planifier_tache_post(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

        $id_tache = $request->get('id_tache');
        $datePrevue = new \DateTime($request->get('datePrevue'));

        if (!$id_tache || !$datePrevue) {
            $this->addFlash('error', 'Données manquantes pour replanifier la tâche.');
            return $this->redirectToRoute('liste_taches_collab');
        }

        $tache = $this->tacheService->findById($id_tache);
        if (!$tache) {
            $this->addFlash('error', 'Tâche introuvable.');
            return $this->redirectToRoute('liste_taches_collab');
        }

        $tache->setDateEcheance($datePrevue);
        // $this->tacheService->insertion_manager($tache);
        $this->tacheService->planifier($tache, $datePrevue);

        return $this->redirectToRoute('liste_taches_activite_collab', ['id_activite' => $tache->getActivite()->getId()]);
    }

    #[Route("/collaborateur/calendrier-tache", name: "calendrier_tache_collab", methods: ["GET"])]
    public function calendrier(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');
        $utilisateur = $this->getUser();
        return $this->render('collaborateur/calendrier-tache.html.twig', [
            'taches' => $this->tacheService->findTachesDuJourParUtilisateur($utilisateur),
        ]);
    }

    #[Route("/collaborateur/planning-tache", name: "planning_jour_tache_collab", methods: ["GET"])]
    public function planning_jour(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');
        $utilisateur = $this->getUser();
        $date = new \DateTime($request->get('date', 'now'));
        return $this->render('collaborateur/calendrier-tache.html.twig', [
            'taches' => $this->tacheService->findPlanningJour($date, $utilisateur),
        ]);
    }

    #[Route("/collaborateur/planning/semaine", name: "planning_semaine_collab", methods: ["GET"])]
    public function planningSemaine(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

        $utilisateur = $this->getUser();

        $startParam = $request->get('start');
        $endParam = $request->get('end');

        // Cas 1 : aucune date fournie -> redirection vers calendrier-tache
        if (!$startParam && !$endParam) {
            return $this->redirectToRoute('calendrier_tache_collab');
        }

        // Cas 2 : une seule date fournie -> redirection vers planning-tache avec la date
        if ($startParam && !$endParam) {
            $date = new \DateTime($startParam);
            return $this->redirectToRoute('planning_jour_tache_collab', [
                'date' => $date->format('Y-m-d'),
            ]);
        }

        if (!$startParam && $endParam) {
            $date = new \DateTime($endParam);
            return $this->redirectToRoute('planning_jour_tache_collab', [
                'date' => $date->format('Y-m-d'),
            ]);
        }

        // Cas 3 : les deux dates sont fournies -> afficher la semaine
        $startDate = new \DateTime($startParam);
        $endDate = new \DateTime($endParam);

        $planning = $this->tacheService->findPlanningDates($startDate, $endDate, $utilisateur);

        return $this->render('collaborateur/planning-semaine.html.twig', [
            'planning' => $planning,
        ]);
    }
    
    #[Route('/collaborateur/tache/ajouter', name: 'ajouter_collab', methods: ['GET'])]
    public function page_ajout_tache_collab(SessionInterface $session): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

        $activite = $session->get('activite');

        if (!$activite || !$activite instanceof Activite) {
            $this->addFlash('error', 'Aucune activité trouvée dans la session.');
            return $this->redirectToRoute('listes_tache_ajout_collab'); // ou une autre page d’accueil
        }

        $taches = $this->tacheService->findAllByActivite($activite);

        return $this->render('collaborateur/creation-tache.html.twig');
    }

    #[Route('/collaborateur/tache/ajouter', name: 'ajouter_tache_collab', methods: ['POST'])]
    public function insert_tache_ajout_collaborateur(SessionInterface $session, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');
        // $activite = $session->get('activite');
        $activiteId = $session->get('activite')?->getId();

        $nom = $request->request->get('nom');
        $date = new \DateTime($request->request->get('date'));

        $estimation = $request->request->get('estimation'); // string
        $estimation = floatval($estimation);

        $tache = new Tache();
        $tache->setTache($nom);
        $tache->setDateEcheance($date);
        $tache->setUtilisateur($session->get('utilisateur'));
        $tache->setEstimation($estimation);

        // if (!$activite || !$activite instanceof Activite) {
        //     $this->addFlash('error', 'Aucune activité trouvée dans la session.');
        //     return $this->redirectToRoute('insert_activite_manager'); // ou une autre page d’accueil
        // }
        if (!$activiteId) {
            $this->addFlash('error', 'Aucune activité trouvée dans la session.');
            return $this->redirectToRoute('insert_activite_manager');
        }

        $activite = $this->activiteService->findById($activiteId);
        if (!$activite) {
            $this->addFlash('error', 'Activité introuvable.');
            return $this->redirectToRoute('insert_activite_manager');
        }

        $tache->setActivite($activite);

        $this->tacheService->insertion_manager($tache);

        // $taches = $this->tacheService->findAllByActivite($activite);
        return $this->redirectToRoute('listes_tache_ajout_collab');
    }

    #[Route('/taches/activite/soumise/{id}', name: 'liste_taches_activite_soumise', methods: ['GET'])]
    public function taches_activite_soumise(int $id): Response {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');
        $activite = $this->activiteService->findById($id);
        $taches = $this->tacheService->findAllByActivite($activite);
        return $this->render('manager/taches-activite-soumise.html.twig', [
            'taches' => $taches,
            'activite' => $activite,
        ]);
    }

    #[Route('/collaborateur/replanifier/tache/{id}', name: 'replanifier_tache_page', methods: ['GET'])]
    public function page_replanification(int $id): Response {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

        $tache = $this->tacheService->findById($id);

        return $this->render('collaborateur/replanification.html.twig', [
            'tache' => $tache,
        ]);
    }

    #[Route('/collaborateur/replanifier/tache', name: 'replanifier_tache', methods: ['POST'])]
    public function replanifier_tache(Request $request): Response {
        $date = new \DateTime($request->get('dateDebut'));
        $id = $request->get('id_tache');
        $this->tacheService->replanifier($id, $date);
        return $this->redirectToRoute('calendrier_tache_collab');
    }

}
