<?php
// src/Controller/Manager/DashboardController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ActiviteService;
use App\Service\TypeActiviteService;
use App\Service\TacheService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Activite;
use App\Entity\TypeActivite;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class ActiviteController extends AbstractController
{
    private ActiviteService $activiteService;
    private TypeActiviteService $typeActiviteService;
    private TacheService $tacheService;

    public function __construct(ActiviteService $activiteService, TypeActiviteService $typeActiviteService,TacheService $tacheService) {
        $this->activiteService = $activiteService;
        $this->typeActiviteService = $typeActiviteService;
        $this->tacheService = $tacheService;
    }


    #[Route('/manager/activite', name: 'insert_activite_manager', methods: ['GET'])]
    public function manager(): Response {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');
        
        // @var TypeActiviteService[] $types
        $types = $this->typeActiviteService->findAll();

        return $this->render('manager/creation-activite.html.twig', [
            'types' => $types
        ]);
    }

    #[Route('/manager/activite', name: 'insertion_activite_manager', methods: ['POST'])]
    public function insert_manager(Request $request, SessionInterface $session): Response {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');
        $nom = $request->get('nom');
        $type_id = $request->get('type');
        $date = $request->get('date');

        $session->set('active_activite', "creation");

        $activite = new Activite();
        $activite->setActivite($nom);
        $activite->setDateEcheance(new \DateTime($date));
        $activite->setEstValide(true);
        $activite->setTypeActivite($this->typeActiviteService->findById($type_id));
        $activite->setUtilisateurAuteur($session->get('utilisateur'));

        $this->activiteService->insertion_manager($activite);

        $session->set('activite', $activite);

        // $taches = $this->tacheService->findAllByActivite($activite);
        return $this->redirectToRoute('listes_tache_ajout_manager');

        // return $this->render('manager/tache-activites-ajout.html.twig', ['taches' => $taches]);
    }

    // #[Route('/manager/activite/taches', name: 'listes_tache_ajout_manager', methods: ['GET'])]
    // public function getAllTache(SessionInterface $session) {
    //     $activite = $session->get('activite');
    //     $taches = $this->tacheService->findAllByActivite($activite);
    //     return $this->render('manager/tache-activites-ajout.html.twig', ['taches' => $taches]);
    // }

    #[Route('/manager/activite/taches', name: 'listes_tache_ajout_manager', methods: ['GET'])]
    public function getAllTache(SessionInterface $session): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        $activite = $session->get('activite');

        if (!$activite || !$activite instanceof Activite) {
            $this->addFlash('error', 'Aucune activité trouvée dans la session.');
            return $this->redirectToRoute('insert_activite_manager'); // ou une autre page d’accueil
        }

        $taches = $this->tacheService->findAllByActivite($activite);

        return $this->render('manager/taches-activite-ajout.html.twig', [
            'taches' => $taches,
            'activite' => $activite
        ]);
    }

    #[Route('/collaborateur/activites', name: 'liste_activites_collab', methods: ['GET'])]
    public function activites_collab(): Response {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

        $utilisateur = $this->getUser();
        return $this->render('collaborateur/liste-activites.html.twig', [
            'activites' => $this->activiteService->findAllByUser($utilisateur)
        ]);
    }

    #[Route('/manager/activites', name: 'liste_activite_manager', methods: ['GET'])]
    public function activites_manager(): Response {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        $activites = $this->activiteService->findAllNonTerminee();
        return $this->render('manager/activite-non-terminee.html.twig', [
            'activites' => $activites,
        ]);

    }

    #[Route('/manager/activite/taches/{id}', name: 'taches_activite_manager', methods: ['GET'])]
    public function taches_activite(int $id): Response {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');
        $activite = $this->activiteService->findById($id);
        $taches = $this->tacheService->findAllByActivite($activite);
        return $this->render('manager/taches-activite.html.twig', [
            'taches' => $taches,
        ]);
    }

    #[Route('/collaborateur/activite', name: 'creer_activite_collab_page', methods: ['GET'])]
    public function activite_collab_page(): Response {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');
        return $this->render('collaborateur/creation-activite.html.twig', [
            'types' => $this->typeActiviteService->findAll(),
        ]);
    }

    #[Route('/collaborateur/activite', name: 'insertion_activite_collab', methods: ['POST'])]
    public function insert_coolaborateur(Request $request, SessionInterface $session): Response {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');
        $nom = $request->get('nom');
        $type_id = $request->get('type');
        $date = $request->get('date');

        $session->set('active_activite', "creation");

        $activite = new Activite();
        $activite->setActivite($nom);
        $activite->setDateEcheance(new \DateTime($date));
        $activite->setEstValide(false);
        $activite->setTypeActivite($this->typeActiviteService->findById($type_id));
        $activite->setUtilisateurAuteur($this->getUser());

        $this->activiteService->insertion_manager($activite);

        $session->set('activite', $activite);

        // $taches = $this->tacheService->findAllByActivite($activite);
        return $this->redirectToRoute('listes_tache_ajout_collab');

        // return $this->render('manager/tache-activites-ajout.html.twig', ['taches' => $taches]);
    }

    #[Route('/collaborateur/activite/taches', name: 'listes_tache_ajout_collab', methods: ['GET'])]
    public function ajout_tache_collab(SessionInterface $session): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

        $activite = $session->get('activite');

        if (!$activite || !$activite instanceof Activite) {
            $this->addFlash('error', 'Aucune activité trouvée dans la session.');
            return $this->redirectToRoute('insert_activite_manager'); // ou une autre page d’accueil
        }

        $taches = $this->tacheService->findAllByActivite($activite);

        return $this->render('collaborateur/ajout-taches.html.twig', [
            'taches' => $taches,
            'activite' => $activite
        ]);
    }

    #[Route('/manager/activites/soumises', name: 'liste_non_validees', methods: ['GET'])]
    public function liste_non_validees(): Response {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        return $this->render('manager/activites-soumises.html.twig', [
            'activites' => $this->activiteService->findAllNonValidees(),
        ]);
    }

    #[Route('/manager/valider/activite/{id}', name: 'valider_activite', methods: ['GET'])]
    public function valider_activite(int $id): Response{
        $this->denyAccessUnlessGranted('ROLE_MANAGER');
        $this->activiteService->valider($id);
        return $this->redirectToRoute('liste_non_validees');
    }

    #[Route('/manager/activite/supprimer/{id}', name: 'supprimer_activite', methods: ['GET'])]
    public function supprimer_activite(int $id): Response{
        $this->denyAccessUnlessGranted('ROLE_MANAGER');
        $this->activiteService->supprimer($id);
        return $this->redirectToRoute('liste_non_validees');
    }

    #[Route('/manager/activite/commencer/{id}', name: 'commencer_activite', methods: ['GET'])]
    public function commencer_activite(int $id): Response{
        $this->denyAccessUnlessGranted('ROLE_MANAGER');
        $this->activiteService->commencer($id);
        return $this->redirectToRoute('liste_activite_manager');
    }

}