<?php
namespace App\Controller;

use App\Service\AnomalieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnomalieController extends AbstractController 
{
    private AnomalieService $anomalieService;

    public function __construct(AnomalieService $anomalieService)
    {
        $this->anomalieService = $anomalieService;
    }

    #[Route('/manager/anomalies', name: 'liste_anomalie', methods: ['GET'])]
    public function liste(): Response {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');

        $anomalies = $this->anomalieService->findNonResolue();

        return $this->render('manager/liste-anomalie.html.twig', [
            'anomalies' =>$anomalies,
        ]);
    }
    
    #[Route('/manager/anomalie/resoudre/{id}', name: 'resoudre_anomalie', methods: ['GET'])]
    public function resoudre(int $id): Response {
        $this->denyAccessUnlessGranted('ROLE_MANAGER');
        $this->anomalieService->resoudre($id);
        return $this->redirectToRoute("liste_anomalie");
    }

}