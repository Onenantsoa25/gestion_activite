<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Util;
use App\Service\UtilService;

class UtilController extends AbstractController {
    private UtilService $utilService;

    public function __construct(UtilService $utilService) {
        $this->utilService = $utilService;
    }

    #[Route('/utils', name: 'util_list', methods: 'GET')]
    public function list_util(): Response {
        $utils = $this->utilService->findAllUtil();

        ob_start();
        extract(['utils' => $utils]);

        include __DIR__ . '/../../templates/util_list.php';

        $contenu = ob_get_clean();
        return new Response($contenu);
    }

    #[Route("/util", name: "insert_util", methods: 'POST')]
    public function insert_util(Request $request): Response {
        $nom = $request->request->get("nom");
        $util = new Util();
        $util->setName($nom);
        $this->utilService->insert($util);

        ob_start();
        include __DIR__ . '/../../templates/reussi.php';
        $contenu = ob_get_clean();

        return new Response($contenu);
    }

    #[Route("/util", name: "insert_util_page", methods: 'GET')]
    public function insert_util_page(): Response {

        ob_start();
        include __DIR__ . '/../../templates/insert_util.php';
        $contenu = ob_get_clean();

        return new Response($contenu);
    }

}