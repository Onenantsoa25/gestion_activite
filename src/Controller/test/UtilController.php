<?php
namespace App\Controller\test;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\test\Util;
use App\Service\test\UtilService;

class UtilController extends AbstractController {
    private UtilService $utilService;

    public function __construct(UtilService $utilService) {
        $this->utilService = $utilService;
    }

    #[Route('/utils', name: 'util_list', methods: ['GET'])]
    public function list_util(): Response {
        $utils = $this->utilService->findAllUtil();
        return $this->render('util_list.html.twig', ['utils' => $utils]);
    }

    #[Route("/util", name: "insert_util", methods: ['POST'])]
    public function insert_util(Request $request): Response {
        $nom = $request->request->get("nom");
        $util = new Util();
        $util->setName($nom);
        $this->utilService->insert($util);

        return $this->render('reussi.html.twig');
    }

    #[Route("/util", name: "insert_util_page", methods: ['GET'])]
    public function insert_util_page(): Response {
        return $this->render('insert_util.html.twig');
    }
}
