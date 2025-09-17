<?php
// src/Controller/Manager/DashboardController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UtilisateurService;
// use App\Service\TypeActiviteService;
use App\Service\TacheService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
// use App\Entity\Activite;
use App\Entity\Utilisateur;
use App\Entity\Role;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class UtilisateurController extends AbstractController
{
    private TacheService $tacheService;
    // private TypeActiviteService $typeActiviteService;
    private UtilisateurService $utilisateurService;

    public function __construct(UtilisateurService $utilisateurService, TacheService $tacheService)
    {
        $this->utilisateurService = $utilisateurService;
        $this->tacheService = $tacheService;
    }

    #[Route('/manager/collaborateurs', name: 'liste_collaborateur', methods: ['GET'])]
    public function liste_manager(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        // @var TypeActiviteService[] $types
        $role = new Role();
        $role->setId(2);
        $utilisateurs = $this->utilisateurService->findAllByRole($role);
        // $this->utilisateurService->charges_travail($utilisateurs);

        $tache = $this->tacheService->findById($request->get('id_tache'));

        return $this->render('manager/liste-collaborateur.html.twig', [
            'utilisateurs' => $utilisateurs,
            'tache' => $tache
        ]);
    }

    #[Route('/manager/charges_travail', name: 'charge_de_travail', methods: ['GET'])]
    public function charges_travail(Request $request, SessionInterface $session): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        if($session->get('activite') !== null){
            $session->remove('activite');
        }

        // @var TypeActiviteService[] $types
        $role = new Role();
        $role->setId(2);
        $utilisateurs = $this->utilisateurService->findAllByRole($role);
        // $this->utilisateurService->charges_travail($utilisateurs);

        // $tache = $this->tacheService->findById($request->get('id_tache'));

        return $this->render('manager/charge-travail.html.twig', [
            'utilisateurs' => $utilisateurs
            // 'tache' => $tache
        ]);
    }

    #[Route('/collaborateur/rapport', name: 'rapport_collab', methods: ['GET'])]
    public function get_rapport(Request $request): Response 
    {
        $this->denyAccessUnlessGranted('ROLE_COLLABORATEUR');

        // Récupérer les valeurs brutes
        $debutRaw = $request->get('debut');
        $finRaw = $request->get('fin');

        // ⚡ Transformer en DateTimeImmutable ou null si invalide
        $debut = \DateTimeImmutable::createFromFormat('Y-m-d', $debutRaw) ?: null;
        $fin = \DateTimeImmutable::createFromFormat('Y-m-d', $finRaw) ?: null;

        $tacheTerminee = $this->tacheService->rapportDates($this->getUser(), $debut, $fin);

        return $this->render('collaborateur/rapport-recapitulatif.html.twig', [
            'taches' => $tacheTerminee,
            'debut' => $debut ? $debut->format('Y-m-d') : null,
            'fin' => $fin ? $fin->format('Y-m-d') : null,
        ]);
        // On suppose que $debut et $fin sont peut-être null au départ

    // if ($debut === null && $fin === null) {
    //     // Cas 1 : début et fin null → on prend la semaine en cours
    //     $debut = new \DateTimeImmutable('monday this week');
    //     $fin   = new \DateTimeImmutable('friday this week');

    // } elseif ($debut === null && $fin !== null) {
    //     // Cas 2 : seulement début null → semaine de fin
    //     $debut = $fin->modify('monday this week');
    //     $fin   = $fin->modify('friday this week');

    // } elseif ($debut !== null && $fin === null) {
    //     // Cas 3 : seulement fin null → semaine de début
    //     $debut = $debut->modify('monday this week');
    //     $fin   = $debut->modify('friday this week');
    // }

    // // Cas 4 : si les deux sont remplis → on ne touche à rien

    // return $this->render('collaborateur/rapport-recapitulatif.html.twig', [
    //     'taches' => $tacheTerminee,
    //     'debut'  => $debut ? $debut->format('Y-m-d') : null,
    //     'fin'    => $fin ? $fin->format('Y-m-d') : null,
    // ]);

    }

}
